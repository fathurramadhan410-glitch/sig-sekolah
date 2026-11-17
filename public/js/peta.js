window.Peta = {
    buat(id, pusat = [-3.3186, 114.5944], zoom = 13) {
        const map = L.map(id).setView(pusat, zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);
        return map;
    },

    latlng(fitur) {
        const [lng, lat] = fitur.geometry.coordinates;
        return [lat, lng];
    },

    esc(teks) {
        if (teks === null || teks === undefined) return '';
        return String(teks)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },

    popup(fitur) {
        const p = fitur.properties;
        const logo = p.logo
            ? `<img src="${this.esc(p.logo)}" alt="Logo" style="width:48px;height:48px;object-fit:contain;margin-bottom:4px;"><br>`
            : '';
        return `
            <div style="min-width: 200px;">
                ${logo}
                <strong>${this.esc(p.nama)}</strong><br>
                <small>${this.esc(p.jenjang)} &bull; ${this.esc(p.status)} &bull; NPSN ${this.esc(p.npsn)}</small>
                <hr style="margin: 6px 0; border: 0; border-top: 1px solid #e5e7eb;">
                <div>${this.esc(p.alamat)}</div>
                <div>Kec. ${this.esc(p.kecamatan)}</div>
                ${p.kepala_sekolah ? `<div>Kepsek: ${this.esc(p.kepala_sekolah)}</div>` : ''}
                ${p.akreditasi ? `<div>Akreditasi: <strong>${this.esc(p.akreditasi)}</strong></div>` : ''}
                ${p.jumlah_siswa !== null ? `<div>Siswa: ${p.jumlah_siswa} &bull; Guru: ${p.jumlah_guru ?? '-'}</div>` : ''}
                <div style="margin-top: 6px;">
                    <a href="/sekolah/${p.id}">Lihat profil lengkap &rarr;</a>
                </div>
            </div>
        `;
    },

    ambilSekolah(jenjang = '', status = '', bbox = '') {
        const params = new URLSearchParams();
        if (jenjang) params.append('jenjang', jenjang);
        if (status) params.append('status', status);
        if (bbox) params.append('bbox', bbox);

        const query = params.toString() ? `?${params.toString()}` : '';
        return fetch(`${window.PETA_URL.data}${query}`)
            .then((r) => r.json())
            .then((d) => d.features);
    },

    jarakKm([lat1, lon1], [lat2, lon2]) {
        const R = 6371;
        const dLat = ((lat2 - lat1) * Math.PI) / 180;
        const dLon = ((lon2 - lon1) * Math.PI) / 180;
        const a =
            Math.sin(dLat / 2) ** 2 +
            Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) ** 2;
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    },

    ikonAngka(angka, warna = '#2563eb') {
        return L.divIcon({
            className: '',
            html: `<div style="background:${warna};color:#fff;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:bold;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4);">${angka}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
        });
    },
};