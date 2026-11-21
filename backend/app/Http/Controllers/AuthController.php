<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Warga;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route(Auth::user()->dashboardRouteName());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('warga')->where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withInput()->with('error', 'Username atau password salah.');
        }

        if ($user->role === 'warga' && $user->warga) {
            if ($user->warga->status_pendaftaran === 'Menunggu Persetujuan') {
                return back()->with('error', 'Pendaftaran Anda masih menunggu persetujuan Admin/RT. Silakan coba lagi nanti.');
            }
            if ($user->warga->status_pendaftaran === 'Ditolak') {
                return back()->with('error', 'Pendaftaran Anda tidak disetujui. Silakan hubungi Admin/Bendahara RT.');
            }
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route($user->dashboardRouteName()));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nik' => [
                'required',
                'digits:16',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (Warga::where('nik_hash', Warga::nikHash((string) $value))->exists()) {
                        $fail('NIK ini sudah terdaftar.');
                    }
                },
            ],
            'no_rumah' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:150',
            'no_hp' => 'nullable|string|max:20',
            'status_hunian' => 'required|in:Tetap,Kontrak',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 angka.',
        ]);

        $kepala = ! empty($validated['no_rumah'])
            ? Warga::where('no_rumah', $validated['no_rumah'])
                ->whereNull('kepala_keluarga_id')
                ->first()
            : null;

        if ($kepala && $kepala->anggotaKeluarga()->count() >= 4) {
            return back()
                ->withErrors(['no_rumah' => 'Rumah ini sudah memiliki 4 anggota tambahan.'])
                ->withInput()
                ->with('tab', 'daftar');
        }

        DB::transaction(function () use ($validated, $kepala) {
            $warga = Warga::create([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'no_rumah' => $kepala?->no_rumah ?? ($validated['no_rumah'] ?? null),
                'alamat' => $kepala?->alamat ?? ($validated['alamat'] ?? null),
                'no_hp' => $validated['no_hp'] ?? null,
                'status_hunian' => $kepala?->status_hunian ?? $validated['status_hunian'],
                'status_aktif' => false,
                'status_pendaftaran' => 'Menunggu Persetujuan',
                'kepala_keluarga_id' => $kepala?->id,
            ]);
            $warga->nik_hash = Warga::nikHash($validated['nik']);
            $warga->save();

            User::create([
                'nama' => $validated['nama'],
                'username' => $validated['username'],
                'password' => $validated['password'],
                'role' => 'warga',
                'warga_id' => $warga->id,
            ]);
        });

        return redirect()->route('login')
            ->with('success', 'Pendaftaran berhasil! Akun Anda akan aktif setelah disetujui oleh Admin/Bendahara RT.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
