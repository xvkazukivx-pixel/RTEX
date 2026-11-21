<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarisPeminjaman extends Model
{
    use HasFactory;

    protected $table = 'inventaris_peminjaman';

    protected $fillable = [
        'barang_id',
        'warga_id',
        'nama_peminjam',
        'jumlah',
        'tanggal_pinjam',
        'rencana_kembali',
        'tanggal_kembali',
        'status',
        'keperluan',
        'catatan',
        'foto_pinjam',
        'foto_kembali',
        'diproses_oleh',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'tanggal_pinjam' => 'date',
        'rencana_kembali' => 'date',
        'tanggal_kembali' => 'date',
    ];

    public function barang(): BelongsTo
    {
        return $this->belongsTo(InventarisBarang::class, 'barang_id');
    }

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
