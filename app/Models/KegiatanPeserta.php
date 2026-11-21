<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanPeserta extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_peserta';

    protected $fillable = [
        'kegiatan_id',
        'warga_id',
        'status',
        'terdaftar_pada',
    ];

    protected $casts = [
        'terdaftar_pada' => 'datetime',
    ];

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }
}
