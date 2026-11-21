<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSurat extends Model
{
    protected $table = 'pengaturan_surat';

    protected $fillable = [
        'nama_pemerintahan',
        'nama_kecamatan',
        'nama_rt_rw',
        'alamat_sekretariat',
        'nama_ketua',
        'jabatan_penanda_tangan',
        'kota_ttd',
        'masa_berlaku',
        'logo_path',
    ];

    protected $attributes = [
        'nama_pemerintahan' => 'RTEX — Administrasi Warga',
        'nama_rt_rw' => 'RT 05 / RW 01',
        'jabatan_penanda_tangan' => 'Ketua RT',
    ];
}
