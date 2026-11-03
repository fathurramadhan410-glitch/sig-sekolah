<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfilSekolah extends Model
{
    protected $fillable = [
    'sekolah_id', 'kepala_sekolah', 'email', 'website',
    'tahun_berdiri', 'akreditasi', 'jumlah_siswa',
    'jumlah_guru', 'deskripsi', 'logo_path',
    ];

    public function sekolah(): BelongsTo
    {
    return $this->belongsTo(Sekolah::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? asset('storage/' . $this->logo_path) : null;
    }

}
