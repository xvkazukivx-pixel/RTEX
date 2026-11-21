<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSampahSetoran extends Model
{
    use HasFactory;

    protected $table = 'bank_sampah_setoran';

    protected $fillable = [
        'warga_id',
        'jenis_id',
        'nama_sampah',
        'harga_per_kg',
        'berat_estimasi',
        'berat_final',
        'nilai_setoran',
        'status',
        'catatan',
        'diproses_oleh',
        'diproses_pada',
    ];

    protected $casts = [
        'berat_estimasi' => 'decimal:2',
        'berat_final' => 'decimal:2',
        'harga_per_kg' => 'decimal:2',
        'nilai_setoran' => 'decimal:2',
        'diproses_pada' => 'datetime',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function jenis()
    {
        return $this->belongsTo(BankSampahJenis::class, 'jenis_id');
    }

    public function pemroses()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
