<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankSampahJenis extends Model
{
    use HasFactory;

    protected $table = 'bank_sampah_jenis';

    protected $fillable = [
        'nama',
        'kategori',
        'contoh_sampah',
        'harga_per_kg',
        'status_aktif',
    ];

    protected $casts = [
        'harga_per_kg' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];

    public function setoran()
    {
        return $this->hasMany(BankSampahSetoran::class, 'jenis_id');
    }
}
