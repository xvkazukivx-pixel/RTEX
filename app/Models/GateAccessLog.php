<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GateAccessLog extends Model
{
    use HasFactory;

    protected $table = 'gate_access_logs';

    protected $fillable = [
        'rfid_uid',
        'user_id',
        'warga_nama',
        'no_rumah',
        'device_id',
        'access_status',
        'denial_reason',
        'gate_action',
        'ip_address',
        'tapped_at',
    ];

    protected $casts = [
        'tapped_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isGranted(): bool
    {
        return $this->access_status === 'granted';
    }
}
