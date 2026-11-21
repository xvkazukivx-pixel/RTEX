<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanValidasi extends Model
{
    use HasFactory;

    protected $table = 'laporan_validasi';

    protected $fillable = [
        'periode',
        'status',
        'divalidasi_oleh',
        'tanggal_validasi',
    ];

    protected $casts = [
        'tanggal_validasi' => 'datetime',
    ];

    public function validator()
    {
        return $this->belongsTo(User::class, 'divalidasi_oleh');
    }
}
