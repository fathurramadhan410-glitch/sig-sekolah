@extends('layouts.public')
@section('title', 'Wilayah')

@section('content')
<div class="toolbar">
    <label>Jenjang
        <select id="filter-jenjang">
            <option value="">Semua jenjang</option>
            <option value="SD">SD</option>
            <option value="SMP">SMP</option>
            <option value="SMA">SMA</option>
            <option value="SMK">SMK</option>
        </select>
    </label>
    <span id="jumlah"></span>
    <span id="sumber-batas" style="color: #6b7280; font-size: 13px;"></span>
</div>

<div id="peta" class="peta"></div>

<div class="panel" style="right: 12px; bottom: 24px; font-size: 13px;">
    <strong>Jumlah sekolah</strong>
    <div><span style="color: #dc2626;">&#9632;</span> Terbanyak</div>
    <div><span style="color: #ea580c;">&#9632;</span> Banyak</div>
    <div><span style="color: #f59e0b;">&#9632;</span> Sedang</div>
    <div><span style="color: #3b82f6;">&#9632;</span> Sedikit</div>
    <small style="color: #6b7280;">Relatif terhadap kecamatan terbanyak</small>
</div>
@endsection

@push('scripts')
<script>
    const map = Peta.buat('peta');
    let lapisan = null;

    function warna(rasio) {
        if (rasio >= 0.8) return '#dc2626';
        if (rasio >= 0.6) return '#ea580c';
        if (rasio >= 0.4) return '#f59e0b';
        return '#3b82f6';
    }

    async function muat(jenjang = '') {
        let data = null;
        let sumberTeks = '';

        try {
            // Mencoba memuat berkas batas kecamatan resmi dari public/data/kecamatan_banjarmasin.geojson
            const resResmi = await fetch('/data/kecamatan_banjarmasin.geojson');
            if (!resResmi.ok) throw new Error('File resmi belum ada');

            const batasResmi = await resResmi.json();
            const sekolah = await Peta.ambilSekolah(jenjang);

            const rekap = {};
            sekolah.forEach((f) => {
                const kec = (f.properties.kecamatan || '').trim().toLowerCase();
                if (!rekap[kec]) rekap[kec] = { sekolah: 0, siswa: 0 };
                rekap[kec].sekolah += 1;
                rekap[kec].siswa += (f.properties.jumlah_siswa ?? 0);
            });

            batasResmi.features.forEach((f) => {
                const namaAsli = f.properties.kecamatan || f.properties.NAMOBJ || f.properties.WADMKC || 'Tanpa Nama';
                const kunci = namaAsli.trim().toLowerCase();
                f.properties.kecamatan = namaAsli;
                f.properties.jumlah_sekolah = rekap[kunci]?.sekolah ?? 0;
                f.properties.jumlah_siswa = rekap[kunci]?.siswa ?? 0;
            });

            data = batasResmi;
            sumberTeks = '(Batas Administrasi Resmi)';
        } catch (e) {
            // Cadangan otomatis: menggunakan poligon convex hull dari backend jika file GeoJSON resmi belum ditaruh
            const url = `${window.PETA_URL.wilayah}?jenjang=${encodeURIComponent(jenjang)}`;
            data = await (await fetch(url)).json();
            sumberTeks = '(Poligon Sebaran Convex Hull)';
        }

        if (lapisan) {
            map.removeLayer(lapisan);
        }

        const maks = Math.max(1, ...data.features.map((f) => f.properties.jumlah_sekolah));

        lapisan = L.geoJSON(data, {
            style: (f) => {
                const w = warna(f.properties.jumlah_sekolah / maks);
                return { color: w, fillColor: w, fillOpacity: 0.35, weight: 2.5 };
            },
            onEachFeature: (f, layer) => {
                const p = f.properties;
                layer.bindTooltip(Peta.esc(p.kecamatan), { sticky: true });
                layer.bindPopup(`
                    <strong>${Peta.esc(p.kecamatan)}</strong><br>
                    Jumlah sekolah: ${p.jumlah_sekolah}<br>
                    Jumlah siswa: ${p.jumlah_siswa}
                `);
            },
        }).addTo(map);

        document.getElementById('jumlah').textContent = `${data.features.length} wilayah`;
        document.getElementById('sumber-batas').textContent = sumberTeks;

        if (data.features.length > 0) {
            map.fitBounds(lapisan.getBounds().pad(0.1));
        }
    }

    document.getElementById('filter-jenjang')
        .addEventListener('change', (e) => muat(e.target.value));

    muat();
</script>
@endpush