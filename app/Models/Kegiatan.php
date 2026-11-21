<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $fillable = [
        'judul',
        'deskripsi',
        'lokasi',
        'mulai_pada',
        'selesai_pada',
        'kapasitas',
        'status',
        'dibuat_oleh',
        'disetujui_oleh',
        'disetujui_pada',
        'catatan_ketua',
    ];

    protected $casts = [
        'mulai_pada' => 'datetime',
        'selesai_pada' => 'datetime',
        'disetujui_pada' => 'datetime',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function penyetuju()
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function peserta()
    {
        return $this->hasMany(KegiatanPeserta::class, 'kegiatan_id');
    }
}
