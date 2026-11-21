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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
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

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'bendahara'], true);
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
        return in_array($this->role, ['bendahara', 'admin'], true);
    }

    public function isRt(): bool
    {
        return in_array($this->role, ['rt', 'ketua'], true);
    }

    public function isRw(): bool
    {
        return $this->role === 'rw';
    }

    public function dashboardRouteName(): string
    {
        return match ($this->role) {
            'bendahara', 'admin' => 'admin.dashboard',
            'rt', 'ketua' => 'ketua.surat',
            'rw' => 'ketua.surat',
            'super_admin' => 'admin.dashboard',
            default => 'warga.dashboard',
        };
    }
}
