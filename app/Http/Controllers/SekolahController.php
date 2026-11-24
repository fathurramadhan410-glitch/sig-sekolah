<?php

namespace App\Http\Controllers;

use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SekolahController extends Controller
{
    private const BATAS_GESER_METER = 500;

    private function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $r = 6371000; // jari-jari bumi (meter)
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $r * asin(sqrt($a));
    }
    public function index(Request $request)
    {
        $sekolahs = Sekolah::query()
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->whereLike('nama',  "%{$q}%")
                      ->orWhereLike('npsn',  "%{$q}%")
                      ->orWhereLike('kecamatan', "%{$q}%");
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.sekolah.index', compact('sekolahs'));
    }

    public function create()
    {
        return view('admin.sekolah.create');
    }

    public function store(Request $request)
    {
        Sekolah::create($request->validate($this->rules()));

        return redirect()->route('dinas.sekolah.index')
            ->with('success', 'Data sekolah berhasil ditambahkan.');
    }

    public function edit(Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        return view('admin.sekolah.edit', compact('sekolah'));
    }

    public function update(Request $request, Sekolah $sekolah)
    {
        Gate::authorize('update', $sekolah);

        $data = $request->validate($this->rules($sekolah->id));

        // NPSN adalah identitas resmi: tidak boleh diubah oleh sekolah
        if ($request->user()->isSekolah()) {
            unset($data['npsn']);
        }

        // Koordinat tidak boleh bergeser terlalu jauh dari titik semula
        $jarak = $this->jarakMeter(
            (float) $sekolah->latitude, (float) $sekolah->longitude,
            (float) $data['latitude'], (float) $data['longitude']
        );

        if ($jarak > self::BATAS_GESER_METER) {
            return back()->withInput()->withErrors([
                'latitude' => 'Koordinat baru terlalu jauh dari titik semula ('
                    . round($jarak) . ' m, maksimal '
                    . self::BATAS_GESER_METER . ' m). Hubungi Dinas Pendidikan.',
            ]);
        }
    

    $sekolah->update($data);

        return $this->kembali($request)
            ->with('success', 'Data sekolah berhasil diperbarui.');
    }

    public function destroy(Sekolah $sekolah)
    {
        $sekolah->delete();

        return redirect()->route('dinas.sekolah.index')
            ->with('success', 'Data sekolah berhasil dihapus.');
    }

    private function kembali(Request $request)
    {
        return $request->user()->isDinas()
            ? redirect()->route('dinas.sekolah.index')
            : redirect()->route('dashboard');
    }

    private function rules(?int $id = null): array
    {
        return [
            'npsn'      => ['required', 'digits:8',
                            Rule::unique('sekolahs', 'npsn')->ignore($id)],
            'nama'      => ['required', 'string', 'max:255'],
            'jenjang'   => ['required', Rule::in(['SD', 'SMP', 'SMA', 'SMK'])],
            'status'    => ['required', Rule::in(['Negeri', 'Swasta'])],
            'alamat'    => ['required', 'string'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'telepon'   => ['nullable', 'string', 'max:20'],
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}