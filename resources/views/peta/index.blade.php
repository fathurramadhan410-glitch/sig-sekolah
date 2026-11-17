@extends('layouts.public')
@section('title', 'Peta Sekolah')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.4.1/dist/MarkerCluster.Default.css">
@endpush

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
    <label>Status
        <select id="filter-status">
            <option value="">Semua status</option>
            <option value="Negeri">Negeri</option>
            <option value="Swasta">Swasta</option>
        </select>
    </label>
    <label style="cursor: pointer;">
        <input type="checkbox" id="mode-bbox"> Muat area layar saja (BBox)
    </label>
    <a id="btn-unduh" href="{{ route('peta.data') }}" download="sekolah-banjarmasin.geojson"
       style="padding: 4px 10px; background: #1e3a8a; color: #fff; text-decoration: none; border-radius: 4px; font-size: 13px;">
        Unduh GeoJSON
    </a>
    <span id="jumlah"></span>
</div>

<div id="peta" class="peta"></div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet.markercluster@1.4.1/dist/leaflet.markercluster.js"></script>
<script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
<script>
    const map = Peta.buat('peta');

    // Tiga lapisan yang bisa dihidup-matikan secara terpisah maupun bersamaan
    const layerMarker = L.layerGroup().addTo(map);
    const layerKlaster = L.markerClusterGroup({ maxClusterRadius: 60 });
    const layerPanas = L.heatLayer([], { radius: 30, blur: 25, maxZoom: 17 });

    L.control.layers(null, {
        'Marker Satuan': layerMarker,
        'Klaster Sekolah': layerKlaster,
        'Peta Panas': layerPanas,
    }, { collapsed: false }).addTo(map);

    function perbaruiLinkUnduh(jenjang, status, bbox) {
        const params = new URLSearchParams();
        if (jenjang) params.append('jenjang', jenjang);
        if (status) params.append('status', status);
        if (bbox) params.append('bbox', bbox);
        const query = params.toString() ? `?${params.toString()}` : '';
        document.getElementById('btn-unduh').href = `${window.PETA_URL.data}${query}`;
    }

    async function muat(sesuaikanBatas = true) {
        const jenjang = document.getElementById('filter-jenjang').value;
        const status = document.getElementById('filter-status').value;
        const pakaiBbox = document.getElementById('mode-bbox').checked;

        let bbox = '';
        if (pakaiBbox) {
            const b = map.getBounds();
            bbox = [b.getWest(), b.getSouth(), b.getEast(), b.getNorth()].join(',');
        }

        perbaruiLinkUnduh(jenjang, status, bbox);

        const fitur = await Peta.ambilSekolah(jenjang, status, bbox);

        layerMarker.clearLayers();
        layerKlaster.clearLayers();
        const titikPanas = [];

        fitur.forEach((f) => {
            const latlng = Peta.latlng(f);
            const popup = Peta.popup(f);

            L.marker(latlng).bindPopup(popup).addTo(layerMarker);
            layerKlaster.addLayer(L.marker(latlng).bindPopup(popup));
            titikPanas.push([...latlng, 0.35]);
        });

        layerPanas.setLatLngs(titikPanas);

        document.getElementById('jumlah').textContent = `${fitur.length} sekolah`;

        if (sesuaikanBatas && !pakaiBbox && fitur.length > 0) {
            map.fitBounds(L.latLngBounds(fitur.map(Peta.latlng)).pad(0.1));
        }
    }

    document.getElementById('filter-jenjang').addEventListener('change', () => muat(true));
    document.getElementById('filter-status').addEventListener('change', () => muat(true));
    document.getElementById('mode-bbox').addEventListener('change', () => muat(false));

    map.on('moveend', () => {
        if (document.getElementById('mode-bbox').checked) {
            muat(false);
        }
    });

    muat(true);
</script>
@endpush