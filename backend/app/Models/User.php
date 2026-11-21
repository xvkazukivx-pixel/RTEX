<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'nama',
        'username',
        'email',
        'password',
        'role',
        'warga_id',
        'rfid_uid',
        'rfid_label',
        'rfid_registered_at',
        'rfid_is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'rfid_registered_at' => 'datetime',
            'rfid_is_active' => 'boolean',
        ];
    }

    public function getNameAttribute(): string
    {
        return $this->nama;
    }

    public function warga()
    {
        return $this->belongsTo(Warga::class, 'warga_id');
    }

    public function gateAccessLogs()
    {
        return $this->hasMany(GateAccessLog::class, 'user_id');
    }

    public function rfidScanSessions()
    {
        return $this->hasMany(RfidScanSession::class, 'user_id');
    }

    public function hasRfidRegistered(): bool
    {
        return !empty($this->rfid_uid);
    }

    public function getMaskedRfidAttribute(): string
    {
        if (!$this->rfid_uid) {
            return '-';
        }
        $parts = explode(':', $this->rfid_uid);
        if (count($parts) >= 4) {
            return $parts[0] . ':' . $parts[1] . ':••:••';
        }
        return substr($this->rfid_uid, 0, 4) . '••••';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'bendahara', 'bendahara_rt'], true);
    }

    public function isKetua(): bool
    {
        return in_array($this->role, ['ketua', 'rt'], true);
    }

    public function isWarga(): bool
    {
        return $this->role === 'warga';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isBendahara(): bool
    {
        return in_array($this->role, ['bendahara', 'bendahara_rt', 'admin', 'bendahara_rw'], true);
    }

    public function isBendaharaRt(): bool
    {
        return in_array($this->role, ['bendahara_rt', 'bendahara', 'admin'], true);
    }

    public function isSekretarisRt(): bool
    {
        return $this->role === 'sekretaris_rt';
    }

    public function isRt(): bool
    {
        return in_array($this->role, ['rt', 'ketua'], true);
    }

    public function isRw(): bool
    {
        return $this->role === 'rw';
    }

    public function isBendaharaRw(): bool
    {
        return $this->role === 'bendahara_rw';
    }

    public function isSekretarisRw(): bool
    {
        return $this->role === 'sekretaris_rw';
    }

    public function isSekretaris(): bool
    {
        return in_array($this->role, ['sekretaris_rt', 'sekretaris_rw'], true);
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'super_admin' => 'admin.dashboard',
            'bendahara_rt', 'bendahara', 'admin' => 'admin.dashboard',
            'sekretaris_rt' => 'admin.surat',
            'rt', 'ketua' => 'ketua.surat',
            'rw' => 'ketua.surat',
            'bendahara_rw' => 'ketua.rekap',
            'sekretaris_rw' => 'ketua.surat',
            default => 'warga.dashboard',
        };
    }
}
