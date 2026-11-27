@extends('layouts.app')

@section('title', 'Pusat Komando & Operasional RTEX')
@section('page_title', 'Pusat Komando RTEX')
@section('page_subtitle', 'Monitoring real-time perputaran kas, iuran warga, dan otorisasi portal IoT')

@push('styles')
<style>
/* ==========================================================================
   RTEX BESPOKE EXECUTIVE COMMAND CENTER STYLES
   Designed specifically for RTEX - Digital Neighborhood Operating System
   ========================================================================== */

/* 1. Header Control Bar */
.rtex-cmd-header {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 20px 24px;
    box-shadow: 0 4px 16px rgba(23, 52, 82, 0.04);
    margin-bottom: 22px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}
.rtex-cmd-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--color-primary);
    background: rgba(36, 124, 187, 0.08);
    padding: 3px 10px;
    border-radius: 999px;
    margin-bottom: 6px;
}
.rtex-cmd-title {
    font-family: var(--font-display);
    font-size: 22px;
    font-weight: 700;
    color: var(--color-primary-dark);
    margin: 0 0 4px 0;
    line-height: 1.25;
}
.rtex-cmd-subtitle {
    font-size: 13px;
    color: var(--color-text-muted);
    margin: 0;
}
.rtex-cmd-actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
}
.btn-rtex-primary {
    background: linear-gradient(135deg, #173452 0%, #247cbb 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 10px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(36, 124, 187, 0.25);
    transition: all 0.2s ease;
}
.btn-rtex-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(36, 124, 187, 0.35);
}
.btn-rtex-secondary {
    background: #ffffff;
    color: var(--color-primary-dark) !important;
    border: 1px solid rgba(71, 146, 202, 0.25);
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
    transition: all 0.2s ease;
}
.btn-rtex-secondary:hover {
    background: rgba(36, 124, 187, 0.05);
    border-color: var(--color-primary);
    transform: translateY(-2px);
}
.live-pulse-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 11.5px;
    font-weight: 600;
    background: rgba(16, 185, 129, 0.1);
    color: #059669;
    border: 1px solid rgba(16, 185, 129, 0.2);
}
.pulse-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* 2. Asymmetric 3-Zone Grid */
.treasury-card {
    background: linear-gradient(140deg, #0e1e2f 0%, #173452 50%, #1f476e 100%);
    border-radius: 18px;
    padding: 24px 26px;
    color: #ffffff;
    height: 100%;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(14, 30, 47, 0.25);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.treasury-card::before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, rgba(56, 189, 248, 0) 70%);
    pointer-events: none;
}
.treasury-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.treasury-tag {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.75);
}
.treasury-badge {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.35);
    color: #34d399;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.treasury-amount {
    font-family: var(--font-display);
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin-bottom: 6px;
    color: #ffffff;
}
.treasury-sub {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 18px;
}
.treasury-split-box {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    padding: 12px 14px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 16px;
}
.split-item-label {
    font-size: 11px;
    color: rgba(255, 255, 255, 0.7);
    margin-bottom: 2px;
}
.split-item-val {
    font-size: 13.5px;
    font-weight: 700;
    color: #ffffff;
}
.treasury-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding-top: 12px;
    font-size: 12.5px;
}
.treasury-footer a {
    color: #38bdf8;
    text-decoration: none;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.2s;
}
.treasury-footer a:hover {
    color: #7dd3fc;
}

/* Zone B: Metric Cards */
.matrix-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 22px 24px;
    box-shadow: 0 4px 16px rgba(23, 52, 82, 0.04);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all 0.2s ease;
}
.matrix-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(23, 52, 82, 0.08);
}
.matrix-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
}
.matrix-title {
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}
.matrix-pill {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 999px;
}
.pill-active { background: #e0f2fe; color: #0284c7; }
.pill-verified { background: #dcfce7; color: #15803d; }
.pill-shield { background: #f3e8ff; color: #7e22ce; }

.matrix-main-val {
    font-family: var(--font-display);
    font-size: 28px;
    font-weight: 800;
    color: var(--color-primary-dark);
    line-height: 1.15;
    margin-bottom: 6px;
}
.matrix-desc {
    font-size: 13px;
    color: var(--color-text-muted);
    margin-bottom: 14px;
}
.progress-bar-wrap {
    background: rgba(71, 146, 202, 0.12);
    border-radius: 999px;
    height: 9px;
    overflow: hidden;
    margin-bottom: 14px;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 999px;
    background: linear-gradient(90deg, #10b981 0%, #0284c7 100%);
    transition: width 0.4s ease;
}
.matrix-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 12px;
    border-top: 1px solid rgba(71, 146, 202, 0.12);
    font-size: 12.5px;
}

/* 3. Operational Workflow Hub */
.workflow-section {
    margin-bottom: 24px;
}
.workflow-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.workflow-title {
    font-size: 12.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--color-primary-dark);
    display: flex;
    align-items: center;
    gap: 6px;
}
.workflow-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    text-decoration: none;
    color: var(--color-text);
    box-shadow: 0 3px 10px rgba(23, 52, 82, 0.03);
    transition: all 0.2s ease;
    height: 100%;
    position: relative;
}
.workflow-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(36, 124, 187, 0.12);
    border-color: var(--color-primary);
    color: var(--color-primary-dark);
}
.workflow-card-accent-green { border-left: 4px solid #10b981; }
.workflow-card-accent-blue { border-left: 4px solid #0284c7; }
.workflow-card-accent-amber { border-left: 4px solid #f59e0b; }
.workflow-card-accent-purple { border-left: 4px solid #8b5cf6; }

.workflow-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.wf-icon-green { background: rgba(16, 185, 129, 0.1); color: #059669; }
.wf-icon-blue { background: rgba(2, 132, 199, 0.1); color: #0284c7; }
.wf-icon-amber { background: rgba(245, 158, 11, 0.1); color: #d97706; }
.wf-icon-purple { background: rgba(139, 92, 246, 0.1); color: #7c3aed; }

.workflow-body h6 {
    font-size: 14px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: var(--color-primary-dark);
}
.workflow-body p {
    font-size: 11.5px;
    color: var(--color-text-muted);
    margin: 0;
    line-height: 1.35;
}

/* 4. Panels & Chart */
.rtex-panel {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 22px 24px;
    box-shadow: 0 4px 16px rgba(23, 52, 82, 0.04);
    height: 100%;
}
.rtex-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 1px solid rgba(71, 146, 202, 0.1);
}
.rtex-panel-title {
    font-size: 15px;
    font-weight: 700;
    color: var(--color-primary-dark);
    margin: 0 0 3px 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.rtex-panel-subtitle {
    font-size: 12px;
    color: var(--color-text-muted);
    margin: 0;
}

/* Chart Bars */
.rtex-chart-container {
    display: flex;
    align-items: flex-end;
    gap: 14px;
    height: 180px;
    padding: 10px 0;
    overflow-x: auto;
}
.rtex-chart-col {
    flex: 1;
    min-width: 55px;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}
.rtex-chart-val {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--color-text-muted);
    margin-bottom: 6px;
    white-space: nowrap;
}
.rtex-chart-track {
    flex: 1;
    width: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    border-bottom: 2px solid rgba(71, 146, 202, 0.18);
    padding-bottom: 2px;
}
.rtex-chart-bar {
    width: min(42px, 80%);
    border-radius: 8px 8px 2px 2px;
    background: linear-gradient(180deg, #38bdf8 0%, #175086 100%);
    box-shadow: 0 4px 12px rgba(23, 80, 134, 0.2);
    transition: all 0.3s ease;
}
.rtex-chart-bar:hover {
    filter: brightness(1.15);
    transform: scaleY(1.02);
}
.rtex-chart-label {
    font-size: 11px;
    font-weight: 600;
    color: var(--color-text-muted);
    margin-top: 8px;
    white-space: nowrap;
}
.rtex-chart-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 18px;
    padding-top: 14px;
    border-top: 1px solid rgba(71, 146, 202, 0.1);
}
.chart-stat-chip {
    background: rgba(71, 146, 202, 0.06);
    border-radius: 10px;
    padding: 8px 12px;
    text-align: center;
}
.chart-stat-label {
    font-size: 10.5px;
    color: var(--color-text-muted);
    margin-bottom: 2px;
}
.chart-stat-val {
    font-size: 13px;
    font-weight: 700;
    color: var(--color-primary-dark);
}

/* 5. Live Activity Table */
.rtex-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 12.5px;
}
.rtex-table th {
    background: rgba(71, 146, 202, 0.05);
    color: var(--color-text-muted);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 10.5px;
    letter-spacing: 0.6px;
    padding: 10px 14px;
    border-bottom: 1px solid rgba(71, 146, 202, 0.15);
}
.rtex-table td {
    padding: 12px 14px;
    border-bottom: 1px solid rgba(71, 146, 202, 0.08);
    color: var(--color-text);
    vertical-align: middle;
}
.rtex-table tr:last-child td {
    border-bottom: none;
}
.rtex-table tr:hover td {
    background: rgba(71, 146, 202, 0.03);
}

/* ==========================================================================
   DARK MODE OVERRIDES
   ========================================================================== */
[data-theme="dark"] .rtex-cmd-header,
[data-theme="dark"] .matrix-card,
[data-theme="dark"] .workflow-card,
[data-theme="dark"] .rtex-panel {
    background: #152238 !important;
    border-color: #22354f !important;
    color: #f1f5f9 !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .rtex-cmd-title,
[data-theme="dark"] .matrix-main-val,
[data-theme="dark"] .workflow-body h6,
[data-theme="dark"] .rtex-panel-title,
[data-theme="dark"] .chart-stat-val {
    color: #f8fafc !important;
}
[data-theme="dark"] .btn-rtex-secondary {
    background: #1c2d47 !important;
    color: #f8fafc !important;
    border-color: #2a4163 !important;
}
[data-theme="dark"] .btn-rtex-secondary:hover {
    background: #233959 !important;
}
[data-theme="dark"] .workflow-card:hover {
    background: #1c2d47 !important;
    border-color: #38bdf8 !important;
}
[data-theme="dark"] .chart-stat-chip {
    background: rgba(255, 255, 255, 0.05) !important;
}
[data-theme="dark"] .rtex-table th {
    background: rgba(255, 255, 255, 0.04) !important;
    border-bottom-color: #22354f !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .rtex-table td {
    border-bottom-color: #22354f !important;
    color: #e2e8f0 !important;
}
[data-theme="dark"] .rtex-chart-track {
    border-bottom-color: #263c5a !important;
}
[data-theme="dark"] .pill-active { background: rgba(2, 132, 199, 0.2); color: #38bdf8; }
[data-theme="dark"] .pill-verified { background: rgba(16, 185, 129, 0.2); color: #34d399; }
[data-theme="dark"] .pill-shield { background: rgba(139, 92, 246, 0.2); color: #c084fc; }
[data-theme="dark"] .live-pulse-badge {
    background: rgba(16, 185, 129, 0.15) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: #34d399 !important;
}
</style>
@endpush

@section('content')

<!-- 1. RTEX EXECUTIVE COMMAND HEADER STRIP (Original Bespoke RTEX Design) -->
<div class="rtex-cmd-header">
    <div>
        <div class="rtex-cmd-badge">
            <span class="pulse-dot"></span> RTEX OPERATING SYSTEM v2.5 • RT 005 / RW 012 MEKARJAYA
        </div>
        <h2 class="rtex-cmd-title">Pusat Komando & Operasional RT</h2>
        <p class="rtex-cmd-subtitle">
            Selamat {{ $salam }}, <strong>{{ auth()->user()->nama ?? 'Administrator' }}</strong>. Kendali terintegrasi kas warga, kepatuhan iuran, dan otorisasi portal IoT.
        </p>
    </div>
    <div class="rtex-cmd-actions">
        <div class="live-pulse-badge">
            <span class="pulse-dot"></span> Sistem Online & Sinkron
        </div>
        <a href="{{ route('admin.transaksi') }}" class="btn-rtex-primary">
            <span>➕</span> Catat Transaksi
        </a>
        <a href="{{ route('admin.iuran') }}" class="btn-rtex-secondary">
            <span>🏷️</span> Tagihan Iuran
        </a>
        <a href="{{ route('admin.iot_gate') }}" class="btn-rtex-secondary">
            <span>🚪</span> Portal RFID
        </a>
    </div>
</div>

<!-- 2. ASYMMETRIC 3-ZONE FINANCIAL & COMMUNITY MATRIX (Original 5-4-3 Column Grid) -->
<div class="row g-3 mb-4">
    <!-- Zone A: Treasury & Cash Reserves Card (5 Columns) -->
    <div class="col-12 col-lg-5">
        <div class="treasury-card">
            <div>
                <div class="treasury-header">
                    <span class="treasury-tag">Saldo Kas Utama RT • Periode {{ strtoupper(periode_label($periode)) }}</span>
                    <span class="treasury-badge">
                        <span>●</span> LIKUIDITAS PRIMA
                    </span>
                </div>
                <div class="treasury-amount">{{ format_rupiah($saldoKas) }}</div>
                <div class="treasury-sub">
                    Akumulasi saldo kas bersih terverifikasi • <span style="color:#38bdf8;">+ {{ format_rupiah($kasBulanIni) }}</span> masuk bulan ini.
                </div>

                <div class="treasury-split-box">
                    <div>
                        <div class="split-item-label">Tagihan Tertunggak</div>
                        <div class="split-item-val" style="color:#fca5a5;">{{ format_rupiah($totalTunggakan) }}</div>
                        <div style="font-size:10.5px; color:rgba(255,255,255,0.7);">{{ $jumlahTunggakan }} tagihan belum lunas</div>
                    </div>
                    <div>
                        <div class="split-item-label">Tingkat Kepatuhan</div>
                        <div class="split-item-val" style="color:#86efac;">{{ $persenLunas }}% KK Lunas</div>
                        <div style="font-size:10.5px; color:rgba(255,255,255,0.7);">{{ $wargaLunas }} dari {{ $totalWargaAktif }} KK</div>
                    </div>
                </div>
            </div>

            <div class="treasury-footer">
                <span style="color:rgba(255,255,255,0.75);">Cadangan Kas Operasional RT 100% Aman</span>
                <a href="{{ route('admin.laporan') }}">
                    Buku Besar Kas →
                </a>
            </div>
        </div>
    </div>

    <!-- Zone B: Dues Collection & Tagihan (4 Columns) -->
    <div class="col-12 col-md-6 col-lg-4">
        <div class="matrix-card">
            <div>
                <div class="matrix-header">
                    <span class="matrix-title">
                        <span>🏷️</span> KOLEKTIBILITAS IURAN
                    </span>
                    <span class="matrix-pill pill-active">
                        Periode {{ periode_label($periode) }}
                    </span>
                </div>
                <div class="matrix-main-val">{{ $wargaLunas }} / {{ $totalWargaAktif }} KK</div>
                <div class="matrix-desc">
                    Kepala Keluarga telah menyelesaikan iuran periode ini.
                </div>

                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" style="width: {{ $persenLunas }}%;"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center" style="font-size:12px; color:var(--color-text-muted);">
                    <span>Terverifikasi: <strong>{{ format_rupiah($kasBulanIni) }}</strong></span>
                    <span style="color:var(--color-primary); font-weight:700;">{{ $persenLunas }}% Terbayar</span>
                </div>
            </div>

            <div class="matrix-detail-row">
                <div>
                    @if($menunggu > 0)
                        <span class="badge text-bg-warning rounded-pill px-2" style="font-size:11px;">
                            {{ $menunggu }} Menunggu Verifikasi
                        </span>
                    @else
                        <span class="badge text-bg-success rounded-pill px-2" style="font-size:11px;">
                            ✓ Semua Transfer Diverifikasi
                        </span>
                    @endif
                </div>
                <a href="{{ route('admin.iuran') }}" class="text-decoration-none fw-bold" style="font-size:12px; color:var(--color-primary);">
                    Kelola Tagihan →
                </a>
            </div>
        </div>
    </div>

    <!-- Zone C: Demografi & IoT Security (3 Columns) -->
    <div class="col-12 col-md-6 col-lg-3">
        <div class="matrix-card">
            <div>
                <div class="matrix-header">
                    <span class="matrix-title">
                        <span>🛡️</span> WARGA & KEAMANAN
                    </span>
                    <span class="matrix-pill pill-shield">
                        Aktif
                    </span>
                </div>
                <div class="matrix-main-val">{{ $totalJiwa }} <span style="font-size:16px; font-weight:600; color:var(--color-text-muted);">Jiwa</span></div>
                <div class="matrix-desc">
                    Terdata dalam <strong>{{ $totalWargaAktif }} Kartu Keluarga</strong> aktif.
                </div>

                <div class="p-2 rounded-3 mb-3" style="background:rgba(71, 146, 202, 0.06); font-size:12px;">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted-rt">Posyandu Balita:</span>
                        <strong>{{ $totalPosyandu }} Anak</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted-rt">Portal RFID:</span>
                        <span id="dashboardEspStatus" class="badge {{ !empty($isDeviceOnline) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }}" style="font-size:11px;">
                            {{ !empty($isDeviceOnline) ? '● ESP32 Online' : '○ ESP32 Offline' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="matrix-detail-row">
                <span class="text-muted-rt" style="font-size:11.5px;">Sinkronisasi Realtime</span>
                <a href="{{ route('admin.warga') }}" class="text-decoration-none fw-bold" style="font-size:12px; color:var(--color-primary);">
                    Data Warga →
                </a>
            </div>
        </div>
    </div>
</div>

<!-- 3. ALUR KERJA PRIORITAS PENGURUS (Distinct Clean Workflow Cards, NOT 1-Klik Boxes) -->
<div class="workflow-section">
    <div class="workflow-heading">
        <div class="workflow-title">
            <span>⚡</span> ALUR KERJA PRIORITAS PENGURUS
        </div>
        <span class="text-muted-rt" style="font-size:12px;">Akses langsung modul administrasi utama RTEX</span>
    </div>

    <div class="row g-3">
        <!-- 1. Pencatatan Keuangan -->
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('admin.transaksi') }}" class="workflow-card workflow-card-accent-green">
                <div class="workflow-icon wf-icon-green">💰</div>
                <div class="workflow-body">
                    <h6>Pencatatan Keuangan</h6>
                    <p>Input kas masuk & belanja operasional RT</p>
                </div>
            </a>
        </div>

        <!-- 2. Verifikasi Iuran -->
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('admin.konfirmasi') }}" class="workflow-card workflow-card-accent-blue">
                <div class="workflow-icon wf-icon-blue">
                    @if($menunggu > 0)
                        <span class="position-relative">
                            ✅
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                        </span>
                    @else
                        ✅
                    @endif
                </div>
                <div class="workflow-body">
                    <h6>Verifikasi Iuran</h6>
                    <p>
                        @if($menunggu > 0)
                            <strong class="text-danger">{{ $menunggu }} transfer</strong> butuh validasi
                        @else
                            Nihil antrean pembayaran warga
                        @endif
                    </p>
                </div>
            </a>
        </div>

        <!-- 3. Administrasi Surat -->
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('admin.surat') }}" class="workflow-card workflow-card-accent-amber">
                <div class="workflow-icon wf-icon-amber">📑</div>
                <div class="workflow-body">
                    <h6>Layanan Surat</h6>
                    <p>
                        @if($suratMenunggu > 0)
                            <strong class="text-warning">{{ $suratMenunggu }} permohonan</strong> menunggu
                        @else
                            Otorisasi surat pengantar warga
                        @endif
                    </p>
                </div>
            </a>
        </div>

        <!-- 4. Portal Keamanan RFID -->
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('admin.iot_gate') }}" class="workflow-card workflow-card-accent-purple">
                <div class="workflow-icon wf-icon-purple">🚪</div>
                <div class="workflow-body">
                    <h6>Portal Gerbang RFID</h6>
                    <p>Monitoring tap e-KTP & palang pintu</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- 4. ANALYTICS & ACTION CENTER DUA KOLOM (7 Cols vs 5 Cols) -->
<div class="row g-3 mb-4">
    <!-- Kolom Kiri: Tren Perputaran Kas 6 Periode -->
    <div class="col-12 col-lg-7">
        <div class="rtex-panel">
            <div class="rtex-panel-header">
                <div>
                    <h5 class="rtex-panel-title">
                        <span>📊</span> Tren Penerimaan Kas 6 Periode
                    </h5>
                    <p class="rtex-panel-subtitle">
                        Perkembangan nominal kas masuk warga yang telah diverifikasi bendahara.
                    </p>
                </div>
                <span class="badge" style="background:rgba(36, 124, 187, 0.1); color:var(--color-primary); font-size:11px; padding:4px 10px;">
                    6 Periode Terakhir
                </span>
            </div>

            @php($maksKas = max(array_merge($kasGrafik, [1])))
            <div class="rtex-chart-container">
                @foreach ($kasGrafik as $index => $nilai)
                    @php($persenTinggi = round(($nilai / $maksKas) * 120))
                    <div class="rtex-chart-col">
                        <span class="rtex-chart-val">{{ format_rupiah($nilai) }}</span>
                        <div class="rtex-chart-track">
                            <div class="rtex-chart-bar" style="height: {{ max($persenTinggi, 10) }}px;" title="{{ $periodeGrafik[$index] }}: {{ format_rupiah($nilai) }}"></div>
                        </div>
                        <span class="rtex-chart-label">{{ $periodeGrafik[$index] }}</span>
                    </div>
                @endforeach
            </div>

            <div class="rtex-chart-stats">
                <div class="chart-stat-chip">
                    <div class="chart-stat-label">Rata-rata Penerimaan</div>
                    <div class="chart-stat-val">{{ format_rupiah($rataRataPenerimaan) }}</div>
                </div>
                <div class="chart-stat-chip">
                    <div class="chart-stat-label">Penerimaan Bulan Ini</div>
                    <div class="chart-stat-val">{{ format_rupiah($kasBulanIni) }}</div>
                </div>
                <div class="chart-stat-chip">
                    <div class="chart-stat-label">Tingkat Kolektibilitas</div>
                    <div class="chart-stat-val" style="color:#10b981;">{{ $persenLunas }}% KK</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Kepatuhan Iuran & Antrean Tindakan -->
    <div class="col-12 col-lg-5">
        <div class="rtex-panel">
            <div class="rtex-panel-header">
                <div>
                    <h5 class="rtex-panel-title">
                        <span>🎯</span> Kepatuhan Pembayaran Iuran
                    </h5>
                    <p class="rtex-panel-subtitle">
                        Rasio pelunasan tagihan per kepala keluarga.
                    </p>
                </div>
                <a href="{{ route('admin.iuran') }}" class="text-decoration-none fw-bold" style="font-size:11.5px; color:var(--color-primary);">
                    Rincian →
                </a>
            </div>

            <div class="d-grid gap-2 mb-3">
                @foreach ($grafikKepatuhan as $item)
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:12px;">
                            <span class="fw-semibold">{{ $item['periode'] }}</span>
                            <span class="fw-bold" style="color:var(--color-primary-dark);">{{ $item['persen'] }}% Lunas</span>
                        </div>
                        <div class="rounded-pill overflow-hidden" style="height:7px; background:rgba(71, 146, 202, 0.12);">
                            <div style="width:{{ $item['persen'] }}%; height:100%; background:linear-gradient(90deg, #10b981, #247cbb); border-radius:999px;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Antrean Konfirmasi Transfer Mini List -->
            <div class="pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="fw-bold" style="font-size:12.5px; color:var(--color-primary-dark);">
                        Antrean Pembayaran Warga
                    </span>
                    <a href="{{ route('admin.konfirmasi') }}" style="font-size:11.5px; font-weight:600;">Lihat Semua</a>
                </div>

                @if($antrianKonfirmasi->isEmpty())
                    <div class="p-3 text-center rounded-3" style="background:rgba(16, 185, 129, 0.08); border:1px dashed #10b981; font-size:12px; color:#10b981;">
                        ✓ Semua transfer iuran warga sudah terverifikasi lunas.
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

<!-- 5. LIVE ACTIVITY LEDGER: 5 TRANSAKSI TERAKHIR (Memberikan Nuansa Sistem Aktif & Autentik) -->
<div class="rtex-panel mb-4">
    <div class="rtex-panel-header">
        <div>
            <h5 class="rtex-panel-title">
                <span>📋</span> Riwayat Transaksi Kas Terkini
            </h5>
            <p class="rtex-panel-subtitle">
                5 transaksi terbaru yang tercatat dalam buku kas RT 005.
            </p>
        </div>
        <a href="{{ route('admin.transaksi') }}" class="btn-rtex-secondary" style="padding:5px 12px; font-size:12px;">
            Buka Semua Transaksi →
        </a>
    </div>

    @if($transaksiTerbaru->isEmpty())
        <div class="p-4 text-center text-muted-rt" style="font-size:13px;">
            Belum ada transaksi kas yang dicatat.
        </div>
    @else
        <div class="table-responsive">
            <table class="rtex-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Warga / Sumber</th>
                        <th>Kategori / Periode</th>
                        <th>Nominal</th>
                        <th>Metode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksiTerbaru as $trx)
                        <tr>
                            <td>
                                <strong>{{ format_tanggal($trx->tanggal_bayar) }}</strong>
                                <div class="text-muted-rt" style="font-size:11px;">{{ $trx->tanggal_bayar ? \Carbon\Carbon::parse($trx->tanggal_bayar)->format('H:i') : '' }} WIB</div>
                            </td>
                            <td>
                                <strong style="color:var(--color-primary-dark);">{{ $trx->warga ? $trx->warga->nama : 'Kas RT' }}</strong>
                                @if($trx->warga && $trx->warga->no_rumah)
                                    <div class="text-muted-rt" style="font-size:11px;">Rumah: {{ $trx->warga->no_rumah }}</div>
                                @endif
                            </td>
                            <td>
                                <div>{{ $trx->kategori ?? 'Iuran Bulanan' }}</div>
                                @if($trx->tagihan)
                                    <span class="badge" style="background:rgba(36,124,187,0.1); color:var(--color-primary); font-size:10.5px;">
                                        {{ periode_label($trx->tagihan->periode) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong style="color:#10b981; font-size:13.5px;">{{ format_rupiah($trx->jumlah_bayar) }}</strong>
                            </td>
                            <td>
                                <span class="badge text-bg-light border" style="font-size:11px;">
                                    {{ $trx->metode_pembayaran ?? 'Tunai' }}
                                </span>
                            </td>
                            <td>
                                @if($trx->status_konfirmasi == 'Diterima')
                                    <span class="badge text-bg-success rounded-pill px-2 py-1" style="font-size:11px;">
                                        ✓ Terverifikasi
                                    </span>
                                @elseif($trx->status_konfirmasi == 'Menunggu')
                                    <span class="badge text-bg-warning rounded-pill px-2 py-1" style="font-size:11px;">
                                        ⏳ Menunggu
                                    </span>
                                @else
                                    <span class="badge text-bg-secondary rounded-pill px-2 py-1" style="font-size:11px;">
                                        {{ $trx->status_konfirmasi }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function checkEspStatus() {
        fetch('{{ route("api.rfid.last_scanned") }}')
            .then(res => res.json())
            .then(data => {
                const el = document.getElementById('dashboardEspStatus');
                if (el) {
                    if (data.device_online) {
                        el.className = 'badge bg-success-subtle text-success border border-success-subtle';
                        el.innerHTML = '● ESP32 Online';
                    } else {
                        el.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                        el.innerHTML = '○ ESP32 Offline';
                    }
                }
            })
            .catch(() => {});
    }
    // Poll every 3 seconds to reflect real-time physical ESP32 status
    setInterval(checkEspStatus, 3000);
});
</script>
@endpush
