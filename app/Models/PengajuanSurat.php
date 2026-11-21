<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanSurat extends Model
{
    use HasFactory;

    public const JENIS_SURAT = [
        'Surat Keterangan Domisili',
        'Surat Pengantar SKCK',
        'Surat Keterangan Usaha',
        'Surat Keterangan Tidak Mampu',
        'Surat Keterangan Lainnya',
    ];

    public const STATUS_DIAJUKAN = 'Diajukan';

    public const STATUS_DIPROSES = 'Diproses';

    public const STATUS_MENUNGGU_PERSETUJUAN = 'Menunggu Persetujuan';

    public const STATUS_DISETUJUI = 'Disetujui';

    public const STATUS_DITOLAK = 'Ditolak';

    protected $table = 'pengajuan_surat';

    protected $fillable = [
        'warga_id',
        'jenis_pemohon',
        'nama_pemohon',
        'nik_pemohon',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'pekerjaan',
        'status_perkawinan',
        'kewarganegaraan',
        'alamat_ktp',
        'alamat_domisili',
        'jenis_surat',
        'keperluan',
        'catatan_warga',
        'status',
        'nomor_surat',
        'catatan_admin',
        'catatan_ketua',
        'diproses_oleh',
        'diproses_pada',
        'disetujui_oleh',
        'disetujui_pada',
    ];

    protected $casts = [
        'nik_pemohon' => 'encrypted',
        'tanggal_lahir' => 'date',
        'diproses_pada' => 'datetime',
        'disetujui_pada' => 'datetime',
    ];

    protected $hidden = [
        'nik_pemohon',
    ];

    public function warga(): BelongsTo
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function pemroses(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }

    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
