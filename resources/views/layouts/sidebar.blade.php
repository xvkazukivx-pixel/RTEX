@php
    $role = auth()->user()->role ?? 'warga';
    $roleLabel = [
        'super_admin' => 'Super Admin',
        'bendahara_rt' => 'Bendahara RT',
        'bendahara' => 'Bendahara RT',
        'admin' => 'Bendahara RT',
        'sekretaris_rt' => 'Sekretaris RT',
        'rt' => 'Ketua RT',
        'ketua' => 'Ketua RT',
        'rw' => 'Ketua RW',
        'bendahara_rw' => 'Bendahara RW',
        'sekretaris_rw' => 'Sekretaris RW',
        'warga' => 'Warga',
    ][$role] ?? 'Pengguna';

    $menus = [
        'super_admin' => [
            ['route' => 'admin.dashboard',   'icon' => '🏠', 'label' => 'Dashboard'],
            ['route' => 'admin.warga',       'icon' => '👪', 'label' => 'Kelola Warga'],
            ['route' => 'admin.persetujuan', 'icon' => '🆕', 'label' => 'Persetujuan Warga Baru'],
            ['route' => 'admin.iuran',       'icon' => '💵', 'label' => 'Manajemen Iuran'],
            ['route' => 'admin.transaksi',   'icon' => '🧾', 'label' => 'Input Transaksi'],
            ['route' => 'admin.konfirmasi',  'icon' => '✅', 'label' => 'Konfirmasi Pembayaran'],
            ['route' => 'admin.laporan',     'icon' => '📊', 'label' => 'Laporan Keuangan'],
            ['route' => 'admin.users',       'icon' => '🔑', 'label' => 'Kelola Akun'],
            ['route' => 'admin.bank_sampah', 'icon' => '♻️', 'label' => 'Bank Sampah'],
            ['route' => 'admin.pengaduan',   'icon' => '💬', 'label' => 'Pengaduan'],
            ['route' => 'admin.posyandu',    'icon' => '🍼', 'label' => 'Posyandu'],
            ['route' => 'admin.surat',       'icon' => '✉️', 'label' => 'Surat'],
            ['route' => 'admin.informasi',   'icon' => '📢', 'label' => 'Informasi & Kegiatan'],
            ['route' => 'admin.inventaris',  'icon' => '📦', 'label' => 'Inventaris'],
        ],
        'bendahara_rt' => [
            ['route' => 'admin.dashboard',   'icon' => '🏠', 'label' => 'Dashboard'],
            ['route' => 'admin.iuran',       'icon' => '💵', 'label' => 'Manajemen Iuran'],
            ['route' => 'admin.bank_sampah.harga', 'icon' => '♻️', 'label' => 'Tarif Bank Sampah'],
            ['route' => 'admin.transaksi',   'icon' => '🧾', 'label' => 'Input Transaksi'],
            ['route' => 'admin.konfirmasi',  'icon' => '✅', 'label' => 'Konfirmasi Pembayaran'],
            ['route' => 'admin.laporan',     'icon' => '📊', 'label' => 'Laporan Keuangan'],
        ],
        'bendahara' => [
            ['route' => 'admin.dashboard',   'icon' => '🏠', 'label' => 'Dashboard'],
            ['route' => 'admin.iuran',       'icon' => '💵', 'label' => 'Manajemen Iuran'],
            ['route' => 'admin.bank_sampah.harga', 'icon' => '♻️', 'label' => 'Tarif Bank Sampah'],
            ['route' => 'admin.transaksi',   'icon' => '🧾', 'label' => 'Input Transaksi'],
            ['route' => 'admin.konfirmasi',  'icon' => '✅', 'label' => 'Konfirmasi Pembayaran'],
            ['route' => 'admin.laporan',     'icon' => '📊', 'label' => 'Laporan Keuangan'],
        ],
        'admin' => [
            ['route' => 'admin.dashboard',   'icon' => '🏠', 'label' => 'Dashboard'],
            ['route' => 'admin.iuran',       'icon' => '💵', 'label' => 'Manajemen Iuran'],
            ['route' => 'admin.bank_sampah.harga', 'icon' => '♻️', 'label' => 'Tarif Bank Sampah'],
            ['route' => 'admin.transaksi',   'icon' => '🧾', 'label' => 'Input Transaksi'],
            ['route' => 'admin.konfirmasi',  'icon' => '✅', 'label' => 'Konfirmasi Pembayaran'],
            ['route' => 'admin.laporan',     'icon' => '📊', 'label' => 'Laporan Keuangan'],
        ],
        'sekretaris_rt' => [
            ['route' => 'admin.surat',       'icon' => '✉️', 'label' => 'Layanan Surat'],
            ['route' => 'admin.informasi',   'icon' => '📢', 'label' => 'Informasi & Kegiatan'],
            ['route' => 'admin.warga',       'icon' => '👪', 'label' => 'Data Warga'],
            ['route' => 'admin.pengaduan',   'icon' => '💬', 'label' => 'Pengaduan Warga'],
            ['route' => 'admin.posyandu',    'icon' => '🍼', 'label' => 'Posyandu'],
            ['route' => 'admin.inventaris',  'icon' => '📦', 'label' => 'Inventaris'],
        ],
        'rt' => [
            ['route' => 'ketua.dashboard',   'icon' => '🏠', 'label' => 'Dashboard RT'],
            ['route' => 'ketua.surat',       'icon' => '✉️', 'label' => 'Konfirmasi Surat'],
            ['route' => 'ketua.informasi',   'icon' => '📢', 'label' => 'Konfirmasi Kegiatan'],
            ['route' => 'ketua.laporan',     'icon' => '📄', 'label' => 'Validasi Laporan'],
            ['route' => 'ketua.monitoring',  'icon' => '👥', 'label' => 'Monitoring Warga'],
            ['route' => 'ketua.rekap',       'icon' => '📊', 'label' => 'Rekap Kas RT'],
            ['route' => 'ketua.bank_sampah', 'icon' => '♻️', 'label' => 'Rekap Bank Sampah'],
            ['route' => 'ketua.posyandu',    'icon' => '🍼', 'label' => 'Rekap Posyandu'],
        ],
        'ketua' => [
            ['route' => 'ketua.dashboard',   'icon' => '🏠', 'label' => 'Dashboard RT'],
            ['route' => 'ketua.surat',       'icon' => '✉️', 'label' => 'Konfirmasi Surat'],
            ['route' => 'ketua.informasi',   'icon' => '📢', 'label' => 'Konfirmasi Kegiatan'],
            ['route' => 'ketua.laporan',     'icon' => '📄', 'label' => 'Validasi Laporan'],
            ['route' => 'ketua.monitoring',  'icon' => '👥', 'label' => 'Monitoring Warga'],
            ['route' => 'ketua.rekap',       'icon' => '📊', 'label' => 'Rekap Kas RT'],
            ['route' => 'ketua.bank_sampah', 'icon' => '♻️', 'label' => 'Rekap Bank Sampah'],
            ['route' => 'ketua.posyandu',    'icon' => '🍼', 'label' => 'Rekap Posyandu'],
        ],
        'rw' => [
            ['route' => 'ketua.dashboard',   'icon' => '🏠', 'label' => 'Dashboard RW'],
            ['route' => 'ketua.surat',       'icon' => '✉️', 'label' => 'Konfirmasi Surat'],
            ['route' => 'ketua.informasi',   'icon' => '📢', 'label' => 'Konfirmasi Kegiatan'],
            ['route' => 'ketua.laporan',     'icon' => '📄', 'label' => 'Validasi Laporan RW'],
            ['route' => 'ketua.monitoring',  'icon' => '👥', 'label' => 'Monitoring Warga RW'],
            ['route' => 'ketua.rekap',       'icon' => '📊', 'label' => 'Rekap Kas RW'],
        ],
        'bendahara_rw' => [
            ['route' => 'ketua.dashboard',   'icon' => '🏠', 'label' => 'Dashboard Kas RW'],
            ['route' => 'ketua.rekap',       'icon' => '📊', 'label' => 'Rekap Kas RW'],
            ['route' => 'ketua.laporan',     'icon' => '📄', 'label' => 'Validasi Laporan'],
        ],
        'sekretaris_rw' => [
            ['route' => 'ketua.dashboard',   'icon' => '🏠', 'label' => 'Dashboard RW'],
            ['route' => 'ketua.surat',       'icon' => '✉️', 'label' => 'Konfirmasi Surat RW'],
            ['route' => 'ketua.informasi',   'icon' => '📢', 'label' => 'Informasi & Kegiatan RW'],
            ['route' => 'ketua.monitoring',  'icon' => '👥', 'label' => 'Monitoring Warga RW'],
        ],
        'warga' => [
            ['route' => 'warga.dashboard',   'icon' => '🏠', 'label' => 'Dashboard Saya'],
            ['route' => 'warga.bayar',       'icon' => '💳', 'label' => 'Bayar Iuran'],
            ['route' => 'warga.riwayat',     'icon' => '📜', 'label' => 'Riwayat Pembayaran'],
            ['route' => 'warga.kas',         'icon' => '📈', 'label' => 'Info Kas Umum'],
            ['route' => 'warga.bank_sampah', 'icon' => '♻️', 'label' => 'Bank Sampah'],
            ['route' => 'warga.pengaduan',   'icon' => '💬', 'label' => 'Pengaduan'],
            ['route' => 'warga.posyandu',    'icon' => '🍼', 'label' => 'Posyandu'],
            ['route' => 'warga.surat',       'icon' => '✉️', 'label' => 'Pengajuan Surat'],
            ['route' => 'warga.informasi',   'icon' => '📢', 'label' => 'Informasi & Kegiatan'],
            ['route' => 'warga.inventaris',  'icon' => '📦', 'label' => 'Inventaris'],
        ],
    ];

    $items = $menus[$role] ?? [];
@endphp
<aside class="sidebar">
    <div class="sidebar-brand">
        <img class="brand-seal" src="{{ asset('assets/images/rtex-logo.png') }}" alt="RTEX">
        <div class="brand-text">
            RTEX
            <small>Administrasi Warga</small>
        </div>
        <button type="button" class="sidebar-close" data-sidebar-close aria-label="Tutup menu">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="sidebar-profile">
        <span class="sidebar-profile__avatar">{{ strtoupper(substr(auth()->user()->nama ?? 'A', 0, 1)) }}</span>
        <div>
            <strong>{{ auth()->user()->nama ?? 'Pengguna' }}</strong>
            <small>{{ $roleLabel }}</small>
        </div>
    </div>
    <nav class="sidebar-nav" id="sidebar-nav">
        @if ($role === 'super_admin')
            <div class="sidebar-nav__section">Utama & Kelola</div>
        @elseif (in_array($role, ['bendahara', 'admin', 'bendahara_rt'], true))
            <div class="sidebar-nav__section">Keuangan & Kas</div>
        @elseif ($role === 'sekretaris_rt')
            <div class="sidebar-nav__section">Sekretariat & Dokumen</div>
        @elseif (in_array($role, ['rt', 'ketua', 'rw', 'bendahara_rw', 'sekretaris_rw'], true))
            <div class="sidebar-nav__section">Eksekutif & Validasi</div>
        @else
            <div class="sidebar-nav__section">Layanan Warga</div>
        @endif
        @foreach ($items as $item)
            @php
                $isActive = request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'active' : '' }}">
                <span class="nav-icon">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
        @foreach ($items as $item)
            @php
                $isActive = request()->routeIs($item['route']);
            @endphp
            <a href="{{ route($item['route']) }}" class="{{ $isActive ? 'active' : '' }}">
                <span class="nav-icon">{{ $item['icon'] }}</span>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="sidebar-foot">
        <div class="user-name">👤 {{ auth()->user()->nama ?? 'User' }}</div>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn-logout" style="background:none; border:none; color:inherit; cursor:pointer;">
                ↪ Keluar
            </button>
        </form>
    </div>
</aside>
