<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Auth;

class SekolahObserver
{
    public function created(Sekolah $sekolah): void
    {
        $this->catat('tambah', $sekolah);
    }

    public function updated(Sekolah $sekolah): void
    {
        $diff = [];
        foreach ($sekolah->getChanges() as $kolom => $baru) {
            if ($kolom === 'updated_at') {
                continue;
            }
            $diff[$kolom] = [
                'lama' => $sekolah->getOriginal($kolom),
                'baru' => $baru,
            ];
        }

        if ($diff) {
            $this->catat('ubah', $sekolah, $diff);
        }
    }

    public function deleted(Sekolah $sekolah): void
    {
        $this->catat('hapus', $sekolah);
    }

    private function catat(string $aksi, Sekolah $sekolah, ?array $diff = null): void
    {
        ActivityLog::create([
            'user_id'      => Auth::id(),
            'aksi'         => $aksi,
            'sekolah_id'   => $sekolah->id,
            'sekolah_nama' => $sekolah->nama,
            'perubahan'    => $diff,
        ]);
    }
}