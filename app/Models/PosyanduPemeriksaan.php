<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosyanduPemeriksaan extends Model
{
    use HasFactory;

    protected $table = 'posyandu_pemeriksaan';

    protected $fillable = [
        'jadwal_id',
        'anak_id',
        'berat_kg',
        'tinggi_cm',
        'lingkar_kepala_cm',
        'imunisasi',
        'catatan',
        'dicatat_oleh',
    ];

    protected $casts = [
        'berat_kg' => 'decimal:2',
        'tinggi_cm' => 'decimal:2',
        'lingkar_kepala_cm' => 'decimal:2',
    ];

    public function jadwal()
    {
        return $this->belongsTo(PosyanduJadwal::class, 'jadwal_id');
    }

    public function anak()
    {
        return $this->belongsTo(PosyanduAnak::class, 'anak_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
