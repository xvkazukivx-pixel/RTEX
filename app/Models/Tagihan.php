<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihan';

    protected $fillable = [
        'warga_id',
        'periode',
        'total_tagihan',
        'status',
    ];

    protected $casts = [
        'total_tagihan' => 'decimal:2',
    ];

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'tagihan_id');
    }
}
