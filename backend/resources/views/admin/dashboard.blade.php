@extends('layouts.app')

@section('title', 'Dashboard Eksekutif RTEX')
@section('page_title', 'Dashboard Eksekutif')
@section('page_subtitle', 'Pusat kendali keuangan, layanan warga, dan monitoring IoT — ' . periode_label($periode))

@push('styles')
<style>
/* Executive Hero Banner */
.exec-hero {
    background: linear-gradient(135deg, #173452 0%, #1e5282 45%, #247cbb 85%, #2ba5e2 100%);
    border-radius: 18px;
    padding: 26px 30px;
    color: #ffffff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 25px -5px rgba(23, 80, 134, 0.22);
    margin-bottom: 24px;
}
.exec-hero::after {
    content: '';
    position: absolute;
    right: -40px;
    top: -40px;
    width: 260px;
    height: 260px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}
.exec-hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255, 255, 255, 0.16);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    padding: 4px 14px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    margin-bottom: 12px;
}
.exec-hero h2 {
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 700;
    color: #ffffff;
    margin-bottom: 6px;
}
.exec-hero-sub {
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.88);
    max-width: 680px;
    margin-bottom: 18px;
    line-height: 1.5;
}
.exec-hero-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}
.btn-exec-action {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 18px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
}
.btn-exec-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
.btn-exec-green { background: #10b981; color: #ffffff !important; }
.btn-exec-red { background: #ef4444; color: #ffffff !important; }
.btn-exec-blue { background: #0284c7; color: #ffffff !important; }
.btn-exec-glass { background: rgba(255, 255, 255, 0.18); color: #ffffff !important; border: 1px solid rgba(255, 255, 255, 0.28); backdrop-filter: blur(4px); }

.exec-hero-footer {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding-top: 14px;
    border-top: 1px solid rgba(255, 255, 255, 0.18);
    font-size: 12.5px;
    color: rgba(255, 255, 255, 0.9);
}

/* 4 Metric Cards with Top Accent */
.exec-metric-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 20px 22px;
    box-shadow: 0 4px 14px rgba(23, 52, 82, 0.04);
    height: 100%;
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}
.exec-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(23, 52, 82, 0.08);
}
.exec-metric-card.accent-green { border-top: 4px solid #10b981; }
.exec-metric-card.accent-red { border-top: 4px solid #ef4444; }
.exec-metric-card.accent-blue { border-top: 4px solid #0284c7; }
.exec-metric-card.accent-purple { border-top: 4px solid #8b5cf6; }

.exec-metric-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}
.exec-metric-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.6px;
    color: var(--color-text-muted);
    text-transform: uppercase;
}
.exec-metric-pill {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    letter-spacing: 0.5px;
}
.pill-green { background: #dcfce7; color: #15803d; }
.pill-red { background: #fee2e2; color: #b91c1c; }
.pill-blue { background: #e0f2fe; color: #0369a1; }
.pill-purple { background: #f3e8ff; color: #7e22ce; }

.exec-metric-val {
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 8px;
}
.exec-metric-val.green { color: #10b981; }
.exec-metric-val.red { color: #ef4444; }
.exec-metric-val.blue { color: #0284c7; }
.exec-metric-val.purple { color: #8b5cf6; }

.exec-metric-hint {
    font-size: 12px;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Pintasan Cepat Row */
.shortcuts-section {
    margin-bottom: 24px;
}
.shortcuts-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.shortcuts-title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: var(--color-text-muted);
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 6px;
}
.shortcut-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    color: var(--color-text);
    box-shadow: 0 2px 8px rgba(23, 52, 82, 0.03);
    transition: all 0.2s ease;
    height: 100%;
}
.shortcut-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(23, 52, 82, 0.08);
    border-color: var(--color-primary);
    color: var(--color-primary);
}
.shortcut-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.icon-pemasukan { background: #ecfdf5; color: #10b981; }
.icon-iuran { background: #f0fdf4; color: #16a34a; }
.icon-warga { background: #f0f9ff; color: #0284c7; }
.icon-verifikasi { background: #fefce8; color: #ca8a04; }
.icon-iot { background: #faf5ff; color: #9333ea; }

.shortcut-info h5 {
    font-size: 13.5px;
    font-weight: 700;
    margin: 0 0 2px;
}
.shortcut-info p {
    font-size: 11.5px;
    color: var(--color-text-muted);
    margin: 0;
}

/* Charts & Activity Cards */
.exec-panel {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 22px;
    box-shadow: 0 4px 14px rgba(23, 52, 82, 0.04);
    height: 100%;
}
.exec-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 18px;
}

/* Chart Bars */
.exec-chart-wrap {
    display: flex;
    align-items: flex-end;
    gap: 14px;
    height: 190px;
    padding: 10px 0;
    overflow-x: auto;
}
.exec-chart-col {
    flex: 1;
    min-width: 55px;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.exec-chart-amount {
    font-size: 10px;
    font-weight: 700;
    color: var(--color-text-muted);
    margin-bottom: 6px;
    white-space: nowrap;
}
.exec-chart-track {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    border-bottom: 2px solid rgba(71, 146, 202, 0.18);
    padding-bottom: 2px;
}
.exec-chart-bar {
    width: min(38px, 80%);
    border-radius: 8px 8px 2px 2px;
    background: linear-gradient(180deg, #38bdf8 0%, #247cbb 100%);
    box-shadow: 0 4px 10px rgba(36, 124, 187, 0.2);
    transition: height 0.3s ease, filter 0.2s ease;
}
.exec-chart-bar:hover {
    filter: brightness(1.1);
}
.exec-chart-period {
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-muted);
    margin-top: 8px;
    white-space: nowrap;
}

/* Dark Mode Overrides for Executive Dashboard */
[data-theme="dark"] .exec-metric-card,
[data-theme="dark"] .shortcut-card,
[data-theme="dark"] .exec-panel {
    background: #152238 !important;
    border-color: #22354f !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3) !important;
    color: #f1f5f9 !important;
}
[data-theme="dark"] .shortcut-card:hover {
    background: #1c2d47 !important;
    border-color: #38bdf8 !important;
}
[data-theme="dark"] .shortcut-info h5 {
    color: #f8fafc !important;
}
[data-theme="dark"] .pill-green { background: rgba(16, 185, 129, 0.18); color: #34d399; }
[data-theme="dark"] .pill-red { background: rgba(239, 68, 68, 0.18); color: #f87171; }
[data-theme="dark"] .pill-blue { background: rgba(2, 132, 199, 0.18); color: #38bdf8; }
[data-theme="dark"] .pill-purple { background: rgba(139, 92, 246, 0.18); color: #c084fc; }
[data-theme="dark"] .icon-pemasukan { background: rgba(16, 185, 129, 0.18); color: #34d399; }
[data-theme="dark"] .icon-iuran { background: rgba(22, 163, 74, 0.18); color: #4ade80; }
[data-theme="dark"] .icon-warga { background: rgba(2, 132, 199, 0.18); color: #38bdf8; }
[data-theme="dark"] .icon-verifikasi { background: rgba(202, 138, 4, 0.18); color: #facc15; }
[data-theme="dark"] .icon-iot { background: rgba(147, 51, 234, 0.18); color: #c084fc; }
[data-theme="dark"] .exec-chart-track { border-bottom-color: #263c5a; }
</style>
@endpush

@section('content')

<!-- 1. EXECUTIVE WELCOME BANNER (Identik referensi Foto 2) -->
<div class="exec-hero">
    <div class="exec-hero-kicker">
        <span>●</span> RT 005 / RW 012 • MEKARJAYA • {{ strtoupper(format_tanggal(now())) }}
    </div>
    <h2>Selamat {{ $salam }}, {{ auth()->user()->nama ?? 'Administrator' }}!</h2>
    <div class="exec-hero-sub">
        Kelola pemasukan kas, pembayaran iuran warga, verifikasi pendaftaran, dan pantau portal RFID dari satu kendali terpusat.
    </div>

    <!-- Quick Action Buttons inside banner -->
    <div class="exec-hero-actions">
        <a href="{{ route('admin.transaksi') }}" class="btn-exec-action btn-exec-green">
            <span>+</span> Catat Pemasukan
        </a>
        <a href="{{ route('admin.iuran') }}" class="btn-exec-action btn-exec-blue">
            <span>💵</span> Iuran Warga
        </a>
        <a href="{{ route('admin.laporan') }}" class="btn-exec-action btn-exec-glass">
            <span>📄</span> Laporan Kas
        </a>
        <a href="{{ route('admin.iot_gate') }}" class="btn-exec-action btn-exec-glass">
            <span>🚪</span> Portal IoT
        </a>
    </div>

    <!-- Live Status Strip -->
    <div class="exec-hero-footer">
        <div class="d-flex align-items-center gap-2">
            <span>🩺</span> <strong>Kesehatan Kas:</strong> {{ $persenLunas }}% Likuid / Terbayar
        </div>
        <div class="d-flex align-items-center gap-2">
            <span>📈</span> <strong>Penerimaan Bulan Ini:</strong> {{ format_rupiah($kasBulanIni) }}
        </div>
        <div class="d-flex align-items-center gap-1">
            <span class="badge text-bg-success px-2 py-1 rounded-pill" style="font-size:11px;">
                ● ESP32 RFID Standby
            </span>
        </div>
    </div>
</div>

<!-- 2. 4 METRIC CARDS WITH TOP ACCENT (Persis seperti Foto 2) -->
<div class="row g-3 mb-4">
    <!-- Card 1: Total Pemasukan Kas -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="exec-metric-card accent-green">
            <div class="exec-metric-header">
                <span class="exec-metric-label">TOTAL PEMASUKAN KAS</span>
                <span class="exec-metric-pill pill-green">AKUMULASI</span>
            </div>
            <div class="exec-metric-val green">{{ format_rupiah($saldoKas) }}</div>
            <div class="exec-metric-hint">
                <span style="color:#10b981; font-weight:600;">+ {{ format_rupiah($kasBulanIni) }}</span> bulan ini
            </div>
        </div>
    </div>

    <!-- Card 2: Total Tunggakan / Pengeluaran Alokasi -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="exec-metric-card accent-red">
            <div class="exec-metric-header">
                <span class="exec-metric-label">TOTAL TUNGGAKAN IURAN</span>
                <span class="exec-metric-pill pill-red">TERTUNGGAK</span>
            </div>
            <div class="exec-metric-val red">{{ format_rupiah($totalTunggakan) }}</div>
            <div class="exec-metric-hint">
                <span style="color:#ef4444; font-weight:600;">{{ $jumlahTunggakan }} tagihan</span> belum lunas
            </div>
        </div>
    </div>

    <!-- Card 3: Saldo Kas RT Saat Ini -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="exec-metric-card accent-blue">
            <div class="exec-metric-header">
                <span class="exec-metric-label">SALDO KAS RT SAAT INI</span>
                <span class="exec-metric-pill pill-blue">SURPLUS BERSIH</span>
            </div>
            <div class="exec-metric-val blue">{{ format_rupiah($saldoKas) }}</div>
            <div class="exec-metric-hint">
                <span>●</span> Likuiditas kas <strong>{{ $persenLunas }}%</strong> terjaga
            </div>
        </div>
    </div>

    <!-- Card 4: Total Warga Terdata -->
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="exec-metric-card accent-purple">
            <div class="exec-metric-header">
                <span class="exec-metric-label">TOTAL WARGA TERDATA</span>
                <span class="exec-metric-pill pill-purple">WARGA RT</span>
            </div>
            <div class="exec-metric-val purple">{{ $totalJiwa }} Jiwa</div>
            <div class="exec-metric-hint">
                <span>🏠</span> <strong>{{ $totalWargaAktif }} KK</strong> aktif • {{ $totalPosyandu }} Balita Posyandu
            </div>
        </div>
    </div>
</div>

<!-- 3. PINTASAN CEPAT LAYANAN UTAMA (1-KLIK) (Identik Foto 2) -->
<div class="shortcuts-section">
    <div class="shortcuts-header">
        <div class="shortcuts-title">
            <span>⚡</span> PINTASAN CEPAT LAYANAN UTAMA (1-KLIK)
        </div>
        <a href="{{ route('admin.iuran') }}" class="text-muted-rt text-decoration-none" style="font-size:12px; font-weight:600;">
            Kelola Iuran Lengkap →
        </a>
    </div>

    <div class="row g-2">
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ route('admin.transaksi') }}" class="shortcut-card">
                <div class="shortcut-icon icon-pemasukan">💵</div>
                <div class="shortcut-info">
                    <h5>Pemasukan Kas</h5>
                    <p>Catat kas masuk & kwitansi</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ route('admin.iuran') }}" class="shortcut-card">
                <div class="shortcut-icon icon-iuran">🧾</div>
                <div class="shortcut-info">
                    <h5>Iuran Warga</h5>
                    <p>Bayar cepat & tarif iuran</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ route('admin.konfirmasi') }}" class="shortcut-card">
                <div class="shortcut-icon icon-verifikasi">
                    @if($menunggu > 0)
                        <span class="position-relative">
                            ✅
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                        </span>
                    @else
                        ✅
                    @endif
                </div>
                <div class="shortcut-info">
                    <h5>Verifikasi Bayar</h5>
                    <p>{{ $menunggu }} menunggu konfirmasi</p>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <a href="{{ route('admin.warga') }}" class="shortcut-card">
                <div class="shortcut-icon icon-warga">👥</div>
                <div class="shortcut-info">
                    <h5>Kelola Warga</h5>
                    <p>Data kartu keluarga & NIK</p>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <a href="{{ route('admin.iot_gate') }}" class="shortcut-card">
                <div class="shortcut-icon icon-iot">🚪</div>
                <div class="shortcut-info">
                    <h5>Portal Gerbang IoT</h5>
                    <p>Monitoring tap e-KTP real-time</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- 4. INSIGHTS & TREN KEUANGAN DUA KOLOM (Padat & Tidak Kosong) -->
<div class="row g-3">
    <!-- Kolom Kiri: Tren Pemasukan Kas 6 Bulan -->
    <div class="col-12 col-lg-7">
        <div class="exec-panel">
            <div class="exec-panel-header">
                <div>
                    <div class="section-title mb-1" style="font-size:16px;">
                        <span class="dot"></span> Tren Pemasukan Kas 6 Periode
                    </div>
                    <div class="text-muted-rt" style="font-size:12.5px;">
                        Akumulasi kas warga yang terkonfirmasi lunas per bulan.
                    </div>
                </div>
                <span class="badge" style="background:#e8f4fc; color:#175086; font-size:11px;">6 Bulan Terakhir</span>
            </div>

            @php($maksKas = max(array_merge($kasGrafik, [1])))
            <div class="exec-chart-wrap">
                @foreach ($kasGrafik as $index => $nilai)
                    @php($persenTinggi = round(($nilai / $maksKas) * 125))
                    <div class="exec-chart-col">
                        <span class="exec-chart-amount">{{ format_rupiah($nilai) }}</span>
                        <div class="exec-chart-track">
                            <div class="exec-chart-bar" style="height: {{ max($persenTinggi, 8) }}px;" title="{{ $periodeGrafik[$index] }}: {{ format_rupiah($nilai) }}"></div>
                        </div>
                        <span class="exec-chart-period">{{ $periodeGrafik[$index] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Kepatuhan Iuran & Antrean Konfirmasi -->
    <div class="col-12 col-lg-5">
        <div class="exec-panel">
            <div class="exec-panel-header">
                <div>
                    <div class="section-title mb-1" style="font-size:16px;">
                        <span class="dot"></span> Kepatuhan Iuran Warga
                    </div>
                    <div class="text-muted-rt" style="font-size:12.5px;">
                        Persentase kepala keluarga yang telah melunasi tagihan.
                    </div>
                </div>
                @if($menunggu > 0)
                    <a href="{{ route('admin.konfirmasi') }}" class="badge text-bg-warning text-decoration-none">
                        {{ $menunggu }} Menunggu →
                    </a>
                @endif
            </div>

            <div class="d-grid gap-2">
                @foreach ($grafikKepatuhan as $item)
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:12.5px;">
                            <span class="fw-semibold">{{ $item['periode'] }}</span>
                            <strong style="color:var(--color-primary-dark);">{{ $item['persen'] }}% Lunas</strong>
                        </div>
                        <div class="rounded-pill overflow-hidden" style="height:8px; background:rgba(71, 146, 202, 0.16);">
                            <div style="width:{{ $item['persen'] }}%; height:100%; background:linear-gradient(90deg, #10b981, #247cbb); border-radius:999px;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Antrean Tindak Lanjut Mini List -->
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold" style="font-size:13px; color:var(--color-primary-dark);">Antrean Konfirmasi Transfer</span>
                    <a href="{{ route('admin.konfirmasi') }}" style="font-size:12px; font-weight:600;">Lihat Semua</a>
                </div>

                @if($antrianKonfirmasi->isEmpty())
                    <div class="p-3 text-center rounded-3" style="background:rgba(16, 185, 129, 0.08); border:1px dashed #10b981; font-size:12px; color:#10b981;">
                        ✓ Semua transfer sudah terverifikasi lunas.
                    </div>
                @else
                    <div class="d-grid gap-2">
                        @foreach($antrianKonfirmasi->take(3) as $trx)
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-2" style="background:rgba(71, 146, 202, 0.06); font-size:12px;">
                                <div>
                                    <strong style="color:var(--color-primary-dark);">{{ $trx->warga ? $trx->warga->nama : '-' }}</strong>
                                    <div class="text-muted-rt" style="font-size:11px;">{{ format_tanggal($trx->tanggal_bayar, true) }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold" style="color:var(--color-primary);">{{ format_rupiah($trx->jumlah_bayar) }}</div>
                                    <a href="{{ route('admin.konfirmasi') }}" class="badge text-bg-primary text-decoration-none" style="font-size:10px;">Periksa</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

@endsection
