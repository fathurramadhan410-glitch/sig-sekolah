<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use App\Support\Geometri;
use Illuminate\Http\Request;

class PetaController extends Controller
{
    public function index()
    {
        return view('peta.index');
    }

    public function cari()
    {
        return view('peta.cari');
    }

    public function klaster()
    {
        return view('peta.klaster');
    }

    public function panas()
    {
        return view('peta.panas');
    }

    public function wilayah()
    {
        return view('peta.wilayah');
    }

    public function rute()
    {
        return view('peta.rute');
    }

    public function alamat()
    {
        return view('peta.alamat');
    }

    public function geojson(Request $request)
    {
        $features = Sekolah::with('profil')
            ->when($request->jenjang, fn ($q, $j) => $q->where('jenjang', $j))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->bbox, function ($q, $bbox) {
                $koordinat = explode(',', $bbox);
                if (count($koordinat) === 4) {
                    [$minLng, $minLat, $maxLng, $maxLat] = array_map('floatval', $koordinat);
                    $q->whereBetween('longitude', [$minLng, $maxLng])
                      ->whereBetween('latitude', [$minLat, $maxLat]);
                }
            })
            ->get()
            ->map(fn ($s) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    // GeoJSON: urutan [longitude, latitude]
                    'coordinates' => [$s->longitude, $s->latitude],
                ],
                'properties' => [
                    'id'             => $s->id,
                    'npsn'           => $s->npsn,
                    'nama'           => $s->nama,
                    'jenjang'        => $s->jenjang,
                    'status'         => $s->status,
                    'alamat'         => $s->alamat,
                    'kecamatan'      => $s->kecamatan,
                    'telepon'        => $s->telepon,
                    'kepala_sekolah' => $s->profil?->kepala_sekolah,
                    'akreditasi'     => $s->profil?->akreditasi,
                    'jumlah_siswa'   => $s->profil?->jumlah_siswa,
                    'jumlah_guru'    => $s->profil?->jumlah_guru,
                    'logo'           => $s->profil?->logoUrl(),
                ],
            ]);

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function geojsonWilayah(Request $request)
    {
        $features = Sekolah::with('profil')
            ->when($request->jenjang, fn ($q, $j) => $q->where('jenjang', $j))
            ->get()
            ->groupBy('kecamatan')
            ->map(function ($kelompok, $kecamatan) {
                $titik = $kelompok
                    ->map(fn ($s) => [$s->longitude, $s->latitude])
                    ->all();

                $hull = Geometri::convexHull($titik);

                if (count($hull) < 3) {
                    return null;
                }

                $hull[] = $hull[0];

                return [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Polygon',
                        'coordinates' => [$hull],
                    ],
                    'properties' => [
                        'kecamatan'      => $kecamatan,
                        'jumlah_sekolah' => $kelompok->count(),
                        'jumlah_siswa'   => (int) $kelompok->sum(
                            fn ($s) => $s->profil?->jumlah_siswa ?? 0
                        ),
                    ],
                ];
            })
            ->filter()
            ->values();

        return response()->json([
            'type'     => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function sekitar(Request $request)
    {
        $lat = (float) $request->query('lat');
        $lng = (float) $request->query('lng');
        $radiusMeter = 1000; // 1 km

        $sekolahSekitar = Sekolah::whereRaw(
            'ST_DWithin(lokasi, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
            [$lng, $lat, $radiusMeter]
        )->get();

        return response()->json($sekolahSekitar);
    }
}