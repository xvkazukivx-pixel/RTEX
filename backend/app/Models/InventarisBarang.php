<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventarisBarang extends Model
{
    use HasFactory;

    protected $table = 'inventaris_barang';

    protected $fillable = [
        'nama',
        'kategori',
        'deskripsi',
        'jumlah_total',
        'jumlah_tersedia',
        'jumlah_perbaikan',
        'kondisi',
    ];

    protected $casts = [
        'jumlah_total' => 'integer',
        'jumlah_tersedia' => 'integer',
        'jumlah_perbaikan' => 'integer',
    ];

    public function peminjaman(): HasMany
    {
        return $this->hasMany(InventarisPeminjaman::class, 'barang_id');
    }
}
