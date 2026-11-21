<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosyanduJadwal extends Model
{
    use HasFactory;

    protected $table = 'posyandu_jadwal';

    protected $fillable = [
        'judul',
        'tanggal',
        'lokasi',
        'catatan',
        'status',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PosyanduPemeriksaan::class, 'jadwal_id');
    }
}
