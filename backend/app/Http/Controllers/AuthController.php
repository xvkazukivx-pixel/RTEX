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
        ], [
            'username.required' => 'Silakan masukkan Nama Pengguna (Username) Anda.',
            'password.required' => 'Silakan masukkan Kata Sandi (Password) Anda.',
        ]);

        $user = User::with('warga')->where('username', $credentials['username'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return back()->withInput()->with('error', 'Username atau password yang Anda masukkan belum cocok. Silakan periksa kembali.');
        }

        if ($user->role === 'warga' && $user->warga) {
            if ($user->warga->status_pendaftaran === 'Menunggu Persetujuan') {
                return back()->with('error', 'Pendaftaran Anda sedang menunggu persetujuan pengurus RT. Mohon kesediaannya menunggu konfirmasi.');
            }
            if ($user->warga->status_pendaftaran === 'Ditolak') {
                return back()->with('error', 'Pendaftaran akun Anda belum dapat disetujui. Silakan hubungi Ketua atau Bendahara RT.');
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
                        $fail('Nomor NIK ini sudah pernah terdaftar di sistem warga.');
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
            'nama.required' => 'Mohon isi Nama Lengkap Anda sesuai KTP.',
            'nama.max' => 'Nama Lengkap maksimal 100 karakter.',
            'nik.required' => 'Nomor NIK (16 digit angka) wajib diisi.',
            'nik.digits' => 'Nomor NIK harus terdiri dari 16 digit angka sesuai KTP.',
            'status_hunian.required' => 'Pilih Status Tempat Tinggal (Tetap atau Kontrak).',
            'status_hunian.in' => 'Status tempat tinggal harus dipilih Tetap atau Kontrak.',
            'username.required' => 'Mohon buat Username (nama panggilan untuk masuk ke aplikasi).',
            'username.unique' => 'Username ini sudah dipakai warga lain. Silakan pilih nama lain.',
            'username.max' => 'Username maksimal 50 karakter.',
            'password.required' => 'Kata sandi (password) wajib diisi.',
            'password.min' => 'Kata sandi minimal harus 6 karakter agar akun Anda aman.',
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
