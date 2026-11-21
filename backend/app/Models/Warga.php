<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warga extends Model
{
    use HasFactory;

    protected $table = 'warga';

    protected $fillable = [
        'nama',
        'nik',
        'no_rumah',
        'alamat',
        'no_hp',
        'status_hunian',
        'status_aktif',
        'status_pendaftaran',
        'kepala_keluarga_id',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'nik' => 'encrypted',
    ];

    protected $hidden = [
        'nik',
        'nik_hash',
    ];

    public static function nikHash(string $nik): string
    {
        return hash_hmac('sha256', $nik, (string) config('app.key'));
    }

    public function kepalaKeluarga(): BelongsTo
    {
        return $this->belongsTo(self::class, 'kepala_keluarga_id');
    }

    public function anggotaKeluarga(): HasMany
    {
        return $this->hasMany(self::class, 'kepala_keluarga_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'warga_id');
    }

    public function tagihan()
    {
        return $this->hasMany(Tagihan::class, 'warga_id');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'warga_id');
    }

    public function pengajuanSurat()
    {
        return $this->hasMany(PengajuanSurat::class, 'warga_id');
    }

    public function posyanduAnak()
    {
        return $this->hasMany(PosyanduAnak::class, 'warga_id');
    }

    public function setoranBankSampah()
    {
        return $this->hasMany(BankSampahSetoran::class, 'warga_id');
    }

    public function pengaduan()
    {
        return $this->hasMany(Pengaduan::class, 'warga_id');
    }
}
