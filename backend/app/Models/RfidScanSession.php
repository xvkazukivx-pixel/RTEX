<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidScanSession extends Model
{
    use HasFactory;

    protected $table = 'rfid_scan_sessions';

    protected $fillable = [
        'user_id',
        'device_id',
        'status',
        'scanned_uid',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at ? $this->expires_at->isPast() : true;
    }
}
