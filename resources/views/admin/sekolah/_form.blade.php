@csrf

<div style="margin-bottom: 12px;">
    <label for="npsn" style="display: block; margin-bottom: 4px;">NPSN</label>
    <input type="text" id="npsn" name="npsn" value="{{ old('npsn', $sekolah->npsn ?? '') }}" required
           style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
    @error('npsn') <small style="color: #dc2626;">{{ $message }}</small> @enderror
</div>

<div style="margin-bottom: 12px;">
    <label for="nama" style="display: block; margin-bottom: 4px;">Nama Sekolah</label>
    <input type="text" id="nama" name="nama" value="{{ old('nama', $sekolah->nama ?? '') }}" required
           style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
    @error('nama') <small style="color: #dc2626;">{{ $message }}</small> @enderror
</div>

<div style="display: flex; gap: 12px; margin-bottom: 12px;">
    <div style="flex: 1;">
        <label for="jenjang" style="display: block; margin-bottom: 4px;">Jenjang</label>
        <select id="jenjang" name="jenjang" required
                style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            <option value="">-- Pilih Jenjang --</option>
            @foreach (['SD', 'SMP', 'SMA', 'SMK'] as $j)
                <option value="{{ $j }}" @selected(old('jenjang', $sekolah->jenjang ?? '') === $j)>{{ $j }}</option>
            @endforeach
        </select>
        @error('jenjang') <small style="color: #dc2626;">{{ $message }}</small> @enderror
    </div>

    <div style="flex: 1;">
        <label for="status" style="display: block; margin-bottom: 4px;">Status</label>
        <select id="status" name="status" required
                style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            <option value="">-- Pilih Status --</option>
            @foreach (['Negeri', 'Swasta'] as $st)
                <option value="{{ $st }}" @selected(old('status', $sekolah->status ?? '') === $st)>{{ $st }}</option>
            @endforeach
        </select>
        @error('status') <small style="color: #dc2626;">{{ $message }}</small> @enderror
    </div>
</div>

<div style="display: flex; gap: 12px; margin-bottom: 12px;">
    <div style="flex: 1;">
        <label for="kecamatan" style="display: block; margin-bottom: 4px;">Kecamatan</label>
        <select id="kecamatan" name="kecamatan" required
                style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            <option value="">-- Pilih Kecamatan --</option>
            @foreach (['Banjarmasin Utara', 'Banjarmasin Selatan', 'Banjarmasin Tengah', 'Banjarmasin Barat', 'Banjarmasin Timur'] as $kec)
                <option value="{{ $kec }}" @selected(old('kecamatan', $sekolah->kecamatan ?? '') === $kec)>{{ $kec }}</option>
            @endforeach
        </select>
        @error('kecamatan') <small style="color: #dc2626;">{{ $message }}</small> @enderror
    </div>

    <div style="flex: 1;">
        <label for="telepon" style="display: block; margin-bottom: 4px;">Telepon</label>
        <input type="text" id="telepon" name="telepon" value="{{ old('telepon', $sekolah->telepon ?? '') }}"
               style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
        @error('telepon') <small style="color: #dc2626;">{{ $message }}</small> @enderror
    </div>
</div>

<div style="margin-bottom: 12px;">
    <label style="display: block; margin-bottom: 4px;">Pilih Lokasi Sekolah pada Peta (Klik atau geser marker)</label>
    <div id="peta-form" style="height: 280px; border: 1px solid #d1d5db; border-radius: 6px; margin-bottom: 8px;"></div>

    <div style="display: flex; gap: 12px;">
        <div style="flex: 1;">
            <label for="latitude" style="display: block; margin-bottom: 4px;">Latitude</label>
            <input type="text" id="latitude" name="latitude"
                   value="{{ old('latitude', $sekolah->latitude ?? '') }}" required
                   style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            @error('latitude') <small style="color: #dc2626;">{{ $message }}</small> @enderror
        </div>
        <div style="flex: 1;">
            <label for="longitude" style="display: block; margin-bottom: 4px;">Longitude</label>
            <input type="text" id="longitude" name="longitude"
                   value="{{ old('longitude', $sekolah->longitude ?? '') }}" required
                   style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">
            @error('longitude') <small style="color: #dc2626;">{{ $message }}</small> @enderror
        </div>
    </div>
</div>

<div style="margin-bottom: 16px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
        <label for="alamat">Alamat Lengkap</label>
        <button type="button" id="btn-isi-alamat"
                style="padding: 5px 10px; background: #2563eb; color: #fff; border: 0; border-radius: 4px; font-size: 12px; cursor: pointer;">
            Isi alamat dari peta
        </button>
    </div>
    <textarea id="alamat" name="alamat" rows="2" required
              style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box;">{{ old('alamat', $sekolah->alamat ?? '') }}</textarea>
    @error('alamat') <small style="color: #dc2626;">{{ $message }}</small> @enderror
</div>

<div style="display: flex; gap: 8px;">
    <button type="submit"
            style="padding: 8px 16px; background: #1e3a8a; color: #fff; border: 0; border-radius: 6px; cursor: pointer;">
        Simpan
    </button>
    <a href="{{ route('dinas.sekolah.index') }}"
       style="padding: 8px 16px; background: #6b7280; color: #fff; text-decoration: none; border-radius: 6px;">
        Batal
    </a>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const inputLat = document.getElementById('latitude');
    const inputLng = document.getElementById('longitude');

    const latAwal = parseFloat(inputLat.value) || -3.3186;
    const lngAwal = parseFloat(inputLng.value) || 114.5944;

    const mapForm = L.map('peta-form').setView([latAwal, lngAwal], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(mapForm);

    let marker = null;

    function perbaruiKoordinat(lat, lng) {
        inputLat.value = lat.toFixed(6);
        inputLng.value = lng.toFixed(6);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(mapForm);
            marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                perbaruiKoordinat(pos.lat, pos.lng);
            });
        }
    }

    // Jika sedang mode edit (koordinat sudah ada), tampilkan marker awal
    if (inputLat.value && inputLng.value) {
        perbaruiKoordinat(parseFloat(inputLat.value), parseFloat(inputLng.value));
    }

    // Klik peta untuk memindahkan marker & mengisi koordinat
    mapForm.on('click', (e) => {
        perbaruiKoordinat(e.latlng.lat, e.latlng.lng);
    });

    // Tombol Isi alamat dari peta (Reverse Geocoding Nominatim)
    document.getElementById('btn-isi-alamat').addEventListener('click', async function () {
        const lat = inputLat.value.trim();
        const lng = inputLng.value.trim();

        if (!lat || !lng) {
            alert('Silakan klik lokasi sekolah pada peta terlebih dahulu.');
            return;
        }

        this.disabled = true;
        const teksAwal = this.textContent;
        this.textContent = 'Mengambil alamat...';

        try {
            const url = `https://nominatim.openstreetmap.org/reverse?format=jsonv2&accept-language=id&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}`;
            const respon = await fetch(url);
            if (!respon.ok) throw new Error('HTTP ' + respon.status);

            const data = await respon.json();
            if (data.display_name) {
                document.getElementById('alamat').value = data.display_name;
            } else {
                alert('Alamat tidak ditemukan untuk koordinat tersebut.');
            }
        } catch (err) {
            console.error(err);
            alert('Gagal mengambil alamat otomatis dari Nominatim.');
        } finally {
            this.disabled = false;
            this.textContent = teksAwal;
        }
    });
</script>