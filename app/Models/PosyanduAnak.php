<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosyanduAnak extends Model
{
    use HasFactory;

    protected $table = 'posyandu_anak';

    protected $fillable = [
        'warga_id',
        'nama',
        'tanggal_lahir',
        'jenis_kelamin',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function pemeriksaan()
    {
        return $this->hasMany(PosyanduPemeriksaan::class, 'anak_id');
    }
}
