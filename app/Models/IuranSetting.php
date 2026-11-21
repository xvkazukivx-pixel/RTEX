<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IuranSetting extends Model
{
    use HasFactory;

    protected $table = 'iuran_settings';

    protected $fillable = [
        'nama_iuran',
        'nominal',
        'status_aktif',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];
}
