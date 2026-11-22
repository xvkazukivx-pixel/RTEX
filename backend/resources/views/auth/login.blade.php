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
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['src/css/app.css', 'src/js/app.js'])
    @endif
    <style>
        .secret-trigger {
            cursor: pointer;
            user-select: none;
            -webkit-user-select: none;
            display: block;
            margin: 0 auto;
            text-align: center;
            transition: transform 0.12s ease;
        }
        .secret-trigger:active {
            transform: scale(0.95);
        }
        @keyframes quickLoginSlideDown {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .quick-login-animate {
            animation: quickLoginSlideDown 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .secret-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(30px);
            background: rgba(15, 23, 42, 0.92);
            color: #ffffff;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 10px 22px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 500;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            z-index: 99999;
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            text-align: center;
        }
        .secret-toast.show {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    </style>
</head>
<body>
@php
    $registrationErrors = $errors->hasAny([
        'nama', 'nik', 'no_rumah', 'alamat', 'no_hp', 'status_hunian', 'username', 'password',
    ]);
@endphp
<div class="auth-shell">
    <div class="auth-card">
        <div id="rtexLogoTrigger" class="secret-trigger" title="RTEX">
            <img class="auth-seal" src="{{ asset('assets/images/rtex-logo.png') }}" alt="Logo RTEX" style="pointer-events:none;">
            <h1 class="text-center mb-1" style="font-family:var(--font-display); font-size:22px; font-weight:700; pointer-events:none;">RTEX</h1>
        </div>
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
            <form action="{{ route('login.post') }}" method="POST" id="formLogin" novalidate>
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Username</label>
                    <input type="text" name="username" id="loginUsername" class="form-control-rt" placeholder="Masukkan username" value="{{ old('username') }}" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Password</label>
                    <input type="password" name="password" id="loginPassword" class="form-control-rt" placeholder="Masukkan password" required>
                </div>
                <button type="submit" class="btn-rt-primary w-100" style="padding:11px;">Masuk</button>
            </form>

            <div class="quick-login-wrap mt-4 pt-3" id="quickLoginWrap" style="display: none;">
                <div class="quick-login-title">⚡ Akses Cepat Login</div>
                <div class="quick-login-grid">
                    <button type="button" class="btn-quick-role" data-u="superadmin" data-p="super123" title="Login Super Admin">
                        <span class="role-badge role-super">👑 Super Admin</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="ketua" data-p="ketua123" title="Login Ketua RT">
                        <span class="role-badge role-rt">🏛️ Ketua RT</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="admin" data-p="admin123" title="Login Bendahara RT">
                        <span class="role-badge role-bendahara">💵 Bendahara RT</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="sekretaris_rt" data-p="sekretaris123" title="Login Sekretaris RT">
                        <span class="role-badge role-sekretaris">📝 Sekretaris RT</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="rw" data-p="rw123" title="Login Ketua RW">
                        <span class="role-badge role-rw">🏢 Ketua RW</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="bendahara_rw" data-p="bendahararw123" title="Login Bendahara RW">
                        <span class="role-badge role-bendahara-rw">💰 Bendahara RW</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="sekretaris_rw" data-p="sekretarisrw123" title="Login Sekretaris RW">
                        <span class="role-badge role-sekretaris-rw">📋 Sekretaris RW</span>
                    </button>
                    <button type="button" class="btn-quick-role" data-u="hafis" data-p="warga123" title="Login Warga">
                        <span class="role-badge role-warga">👤 Warga</span>
                    </button>
                </div>
                <div class="quick-login-hint">Klik peran di atas untuk langsung masuk otomatis</div>
            </div>
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

document.querySelectorAll('.btn-quick-role').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const u = this.getAttribute('data-u');
        const p = this.getAttribute('data-p');
        const usernameInput = document.getElementById('loginUsername');
        const passwordInput = document.getElementById('loginPassword');
        const form = document.getElementById('formLogin');
        if (usernameInput && passwordInput && form) {
            usernameInput.value = u;
            passwordInput.value = p;
            form.submit();
        }
    });
});

// Secret Easter Egg: Klik 7x Logo RTEX untuk Menampilkan / Menyembunyikan Akses Cepat Login
(function () {
    const trigger = document.getElementById('rtexLogoTrigger');
    const wrap = document.getElementById('quickLoginWrap');
    if (!trigger || !wrap) return;

    let clickCount = 0;
    let resetTimer = null;

    // Elemen Toast Notifikasi Rahasia
    const toast = document.createElement('div');
    toast.className = 'secret-toast';
    document.body.appendChild(toast);

    function showToast(msg, duration = 2500) {
        toast.textContent = msg;
        toast.classList.add('show');
        clearTimeout(toast._timeout);
        toast._timeout = setTimeout(() => {
            toast.classList.remove('show');
        }, duration);
    }

    // Periksa apakah sebelumnya sudah dibuka (dalam sessionStorage) atau URL param ?quick=1
    const urlParams = new URLSearchParams(window.location.search);
    const isParamActive = urlParams.get('quick') === '1' || urlParams.get('dev') === '1';
    const isSessionActive = sessionStorage.getItem('rtex_quick_login_unlocked') === '1';

    if (isParamActive || isSessionActive) {
        wrap.style.display = 'block';
    }

    trigger.addEventListener('click', function (e) {
        clickCount++;

        // Haptic feedback getar halus di smartphone jika didukung
        if (navigator.vibrate) {
            try { navigator.vibrate(30); } catch (err) {}
        }

        // Animasi klik mikro pada logo
        trigger.style.transform = 'scale(0.93)';
        setTimeout(() => {
            trigger.style.transform = 'scale(1)';
        }, 120);

        // Reset hitungan jika jeda antar-klik > 2.5 detik
        clearTimeout(resetTimer);
        resetTimer = setTimeout(() => {
            clickCount = 0;
        }, 2500);

        // Jika klik sudah mencapai 7 kali
        if (clickCount >= 7) {
            clickCount = 0;
            const isHidden = (wrap.style.display === 'none' || getComputedStyle(wrap).display === 'none');

            if (isHidden) {
                // Pastikan tab "Masuk Akun" aktif jika user sedang di tab daftar
                const masukTabBtn = document.querySelector('.auth-tab-btn[data-tab="panelMasuk"]');
                if (masukTabBtn && !masukTabBtn.classList.contains('active')) {
                    masukTabBtn.click();
                }

                wrap.style.display = 'block';
                wrap.classList.remove('quick-login-animate');
                void wrap.offsetWidth; // trigger reflow untuk restart animasi
                wrap.classList.add('quick-login-animate');
                sessionStorage.setItem('rtex_quick_login_unlocked', '1');
                showToast('⚡ Akses Cepat Login Berhasil Ditampilkan!');
            } else {
                wrap.style.display = 'none';
                wrap.classList.remove('quick-login-animate');
                sessionStorage.removeItem('rtex_quick_login_unlocked');
                showToast('🔒 Akses Cepat Login Disembunyikan');
            }
        }
    });
})();
</script>
</body>
</html>
