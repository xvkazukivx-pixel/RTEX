<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — RTEX (Sistem Iuran RT &amp; RW)</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/rtex-logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}?v={{ file_exists(public_path('assets/css/style.css')) ? filemtime(public_path('assets/css/style.css')) : '2.1.0' }}">
</head>
<body>
@php
    $registrationErrors = $errors->hasAny([
        'nama', 'nik', 'no_rumah', 'alamat', 'no_hp', 'status_hunian', 'username', 'password',
    ]);
@endphp
<div class="auth-shell">
    <div class="auth-card">
        <img class="auth-seal" src="{{ asset('assets/images/rtex-logo.png') }}" alt="Logo RTEX">
        <h1 class="text-center mb-1" style="font-family:var(--font-display); font-size:22px; font-weight:700;">RTEX</h1>
        <p class="text-center text-muted-rt mb-4" style="font-size:13px;">Sistem Iuran, Kas &amp; Layanan Warga RT Digital</p>

        <div class="auth-tabs">
            <button type="button" class="auth-tab-btn {{ $registrationErrors || session('tab') === 'daftar' ? '' : 'active' }}" data-tab="panelMasuk">Masuk Akun</button>
            <button type="button" class="auth-tab-btn {{ $registrationErrors || session('tab') === 'daftar' ? 'active' : '' }}" data-tab="panelDaftar">Daftar Akun</button>
        </div>

        @if(session('error'))
            <div class="alert alert-danger" style="border-radius:8px; font-size:13.5px;">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success" style="border-radius:8px; font-size:13.5px;">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" style="border-radius:8px; font-size:13.5px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- PANEL MASUK AKUN -->
        <div id="panelMasuk" class="auth-panel {{ $registrationErrors || session('tab') === 'daftar' ? '' : 'active' }}">
            <form action="{{ route('login.post') }}" method="POST" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Username</label>
                    <input type="text" name="username" class="form-control-rt" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Password</label>
                    <input type="password" name="password" class="form-control-rt" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-rt-primary w-100" style="padding:11px;">Masuk</button>
            </form>
        </div>

        <!-- PANEL DAFTAR AKUN (WARGA) -->
        <div id="panelDaftar" class="auth-panel {{ $registrationErrors || session('tab') === 'daftar' ? 'active' : '' }}">
            <form action="{{ route('register.post') }}" method="POST" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control-rt" placeholder="Nama sesuai data kependudukan" value="{{ old('nama') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">NIK (16 digit)</label>
                    <input type="text" name="nik" class="form-control-rt" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                </div>
                <div class="auth-grid-2 mb-3">
                    <div>
                        <label class="form-label-rt">No. Rumah</label>
                        <input type="text" name="no_rumah" class="form-control-rt" placeholder="Contoh: B-12" value="{{ old('no_rumah') }}">
                        <div class="text-muted-rt mt-1" style="font-size:11px;">Jika nomor rumah sudah terdaftar, akun akan menjadi anggota keluarga.</div>
                    </div>
                    <div>
                        <label class="form-label-rt">Status Hunian</label>
                        <select name="status_hunian" class="form-control-rt">
                            <option value="Tetap" {{ old('status_hunian') === 'Tetap' ? 'selected' : '' }}>Tetap</option>
                            <option value="Kontrak" {{ old('status_hunian') === 'Kontrak' ? 'selected' : '' }}>Kontrak</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Alamat</label>
                    <input type="text" name="alamat" class="form-control-rt" placeholder="Alamat lengkap" value="{{ old('alamat') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">No. WhatsApp</label>
                    <input type="text" name="no_hp" class="form-control-rt" placeholder="08xxxxxxxxxx" value="{{ old('no_hp') }}">
                </div>
                <div class="auth-grid-2 mb-4">
                    <div>
                        <label class="form-label-rt">Username</label>
                        <input type="text" name="username" class="form-control-rt" placeholder="Username baru" value="{{ old('username') }}" required>
                    </div>
                    <div>
                        <label class="form-label-rt">Password</label>
                        <input type="password" name="password" class="form-control-rt" placeholder="Min. 6 karakter" required>
                    </div>
                </div>
                <button type="submit" class="btn-rt-accent w-100" style="padding:11px;">Daftar — Menunggu Persetujuan</button>
                <p class="text-center text-muted-rt mt-3" style="font-size:11.5px;">Akun Anda baru bisa dipakai setelah disetujui oleh Admin/Bendahara RT.</p>
            </form>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.auth-tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.auth-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
</script>
</body>
</html>
