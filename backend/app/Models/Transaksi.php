<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';

    protected $fillable = [
        'tagihan_id',
        'warga_id',
        'jumlah_bayar',
        'metode',
        'bukti_transfer',
        'keterangan',
        'tanggal_bayar',
        'status_konfirmasi',
        'dikonfirmasi_oleh',
        'tanggal_konfirmasi',
        'catatan_admin',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'tanggal_bayar' => 'datetime',
        'tanggal_konfirmasi' => 'datetime',
    ];

    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function konfirmator()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }
}
