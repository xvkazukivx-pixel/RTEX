@extends('layouts.app')

@section('title', 'Manajemen Iuran Warga')
@section('page_title', 'Manajemen Iuran')
@section('page_subtitle', 'Pengelolaan iuran bulanan, riwayat pembayaran, serta pengaturan tarif warga')

@push('styles')
<style>
/* 1. Header Topbar dengan Gradasi RTEX (Memindahkan latar banner ke topbar) */
.topbar {
    background: linear-gradient(135deg, #173452 0%, #1e5282 45%, #247cbb 85%, #2ba5e2 100%) !important;
    border-radius: 16px !important;
    padding: 18px 24px !important;
    color: #ffffff !important;
    box-shadow: 0 8px 22px -4px rgba(23, 80, 134, 0.22) !important;
    border: none !important;
    margin-bottom: 22px !important;
    position: relative !important;
    overflow: hidden !important;
}
.topbar::after {
    content: '';
    position: absolute;
    right: -30px;
    top: -30px;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0) 70%);
    pointer-events: none;
}
.topbar .page-title {
    color: #ffffff !important;
    font-family: var(--font-display) !important;
    font-weight: 700 !important;
    font-size: 22px !important;
    margin: 0 !important;
}
.topbar .page-subtitle {
    color: rgba(255, 255, 255, 0.88) !important;
    font-size: 13.5px !important;
    margin-top: 3px !important;
}
.topbar .sidebar-toggle {
    background: rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    border-radius: 10px !important;
    padding: 7px 14px !important;
    backdrop-filter: blur(4px);
}
.topbar .sidebar-toggle:hover {
    background: rgba(255, 255, 255, 0.28) !important;
}
.topbar .topbar-date {
    background: rgba(255, 255, 255, 0.18) !important;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    border-radius: 20px !important;
    padding: 7px 16px !important;
    font-size: 13px !important;
    backdrop-filter: blur(4px);
}

/* 2. Kartu Metrik Ringkasan */
.iuran-stat-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    padding: 20px 22px;
    box-shadow: 0 4px 12px rgba(23, 52, 82, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}
.iuran-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(23, 52, 82, 0.08);
}
.iuran-stat-label {
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: 0.6px;
    color: var(--color-text-muted);
    text-transform: uppercase;
    margin-bottom: 6px;
}
.iuran-stat-val {
    font-family: var(--font-display);
    font-size: 26px;
    font-weight: 700;
    color: var(--color-primary-dark);
    line-height: 1.2;
    margin-bottom: 8px;
}
.iuran-stat-val.primary {
    color: var(--color-primary);
}
.iuran-stat-val.success {
    color: var(--color-success);
}
.iuran-stat-val.warning {
    color: #d97706;
}
.iuran-stat-hint {
    font-size: 12px;
    color: var(--color-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* 3. Tab Navigasi */
.nav-iuran-tabs {
    border-bottom: none;
    gap: 8px;
    margin-bottom: 18px;
}
.nav-iuran-tabs .nav-link {
    border: 1px solid var(--color-border);
    background: #ffffff;
    color: var(--color-text-muted);
    border-radius: 999px;
    padding: 8px 18px;
    font-size: 13.5px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.nav-iuran-tabs .nav-link:hover {
    border-color: var(--color-primary);
    color: var(--color-primary);
}
.nav-iuran-tabs .nav-link.active {
    background: linear-gradient(135deg, #1e5282 0%, #247cbb 100%);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(36, 124, 187, 0.25);
}

/* 4. Tabel Card */
.card-iuran-table {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid rgba(71, 146, 202, 0.16);
    box-shadow: 0 4px 12px rgba(23, 52, 82, 0.04);
    overflow: hidden;
}
.card-iuran-table .table-header {
    padding: 18px 24px;
    border-bottom: 1px solid rgba(71, 146, 202, 0.12);
}
.tag-house {
    display: inline-block;
    background: #eef6fc;
    color: #175086;
    padding: 2px 8px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}
.badge-metode-tunai {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #c8e6c9;
    font-weight: 600;
}
.badge-metode-transfer {
    background: #f3e8ff;
    color: #6b21a8;
    border: 1px solid #e9d5ff;
    font-weight: 600;
}
</style>
@endpush

@section('content')

<!-- METRIC STATS CARDS -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="iuran-stat-card">
            <div class="iuran-stat-label">TOTAL IURAN TERVERIFIKASI</div>
            <div class="iuran-stat-val success">{{ format_rupiah($totalTerverifikasi) }}</div>
            <div class="iuran-stat-hint">
                <span class="badge text-bg-success px-2 py-1" style="font-size:11px;">✓ Kas Masuk Valid</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="iuran-stat-card">
            <div class="iuran-stat-label">TOTAL TRANSAKSI IURAN</div>
            <div class="iuran-stat-val primary">{{ $totalTransaksi }} Pembayaran</div>
            <div class="iuran-stat-hint">
                <span>{{ $riwayatTransaksi->total() }} transaksi tercatat</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="iuran-stat-card">
            <div class="iuran-stat-label">TAGIHAN MENUNGGU / TUNGGAKAN</div>
            <div class="iuran-stat-val warning">{{ format_rupiah($totalTunggakan) }}</div>
            <div class="iuran-stat-hint">
                <span class="badge text-bg-warning px-2 py-1" style="font-size:11px;">{{ $jumlahTunggakan }} tagihan belum lunas</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="iuran-stat-card">
            <div class="iuran-stat-label">TARIF IURAN BULANAN</div>
            <div class="iuran-stat-val">{{ format_rupiah($totalAktif) }}</div>
            <div class="iuran-stat-hint">
                <span>{{ $daftarIuran->where('status_aktif', true)->count() }} komponen iuran aktif</span>
            </div>
        </div>
    </div>
</div>

<!-- NAVIGATION TABS -->
<ul class="nav nav-pills nav-iuran-tabs" id="iuranTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab-riwayat-btn" data-bs-toggle="pill" data-bs-target="#tab-riwayat" type="button" role="tab">
            💳 Daftar Pembayaran Iuran ({{ $totalTransaksi }})
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-tagihan-btn" data-bs-toggle="pill" data-bs-target="#tab-tagihan" type="button" role="tab">
            👥 Monitoring Tagihan Warga (Per KK)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab-tarif-btn" data-bs-toggle="pill" data-bs-target="#tab-tarif" type="button" role="tab">
            ⚙️ Tarif & Komponen Iuran ({{ $daftarIuran->count() }})
        </button>
    </li>
</ul>

<div class="tab-content" id="iuranTabsContent">

    <!-- ========================================================================= -->
    <!-- TAB 1: DAFTAR PEMBAYARAN IURAN WARGA                                      -->
    <!-- ========================================================================= -->
    <div class="tab-pane fade show active" id="tab-riwayat" role="tabpanel">
        <div class="card-iuran-table mb-4">
            <!-- Header Card & Search Filter & Action Button -->
            <div class="table-header">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div class="section-title mb-0" style="font-size:16px;">
                                <span class="dot"></span> Daftar Pembayaran Iuran Warga
                            </div>
                            <!-- Tombol + Catat Bayar Iuran (Dipindahkan ke sini sesuai tanda panah kuning) -->
                            <button type="button" class="btn-rt-primary fw-bold d-inline-flex align-items-center gap-1 ms-1" data-bs-toggle="modal" data-bs-target="#modalCatatBayar" style="padding:6px 16px; font-size:13px; border-radius:999px; box-shadow:0 2px 8px rgba(36,124,187,0.25);">
                                <span style="font-size:16px; font-weight:800; line-height:1;">+</span> Catat Bayar Iuran
                            </button>
                            @if (!$tagihanBulanIniAda)
                                <form action="{{ route('admin.dashboard.generate') }}" method="POST" class="d-inline" onsubmit="return confirm('Terbitkan tagihan baru untuk seluruh kepala keluarga aktif periode {{ periode_label($periodeBerjalan) }}?');">
                                    @csrf
                                    <button type="submit" class="btn-rt-outline fw-semibold d-inline-flex align-items-center gap-1" style="padding:6px 14px; font-size:12.5px; border-radius:999px;">
                                        ⚡ Terbitkan Tagihan {{ periode_label($periodeBerjalan) }}
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="text-muted-rt mt-1" style="font-size:12.5px;">
                            Riwayat pembayaran iuran warga melalui tunai maupun transfer QRIS.
                        </div>
                    </div>

                    <!-- Quick Filters Form -->
                    <form method="GET" action="{{ route('admin.iuran') }}" class="d-flex flex-wrap align-items-center gap-2">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control-rt" placeholder="Cari nama / rumah..." style="width:175px; font-size:13px; padding:6px 12px;">
                        
                        <select name="status" class="form-select-rt" style="width:145px; font-size:13px; padding:6px 10px;">
                            <option value="all">Semua Status</option>
                            <option value="Diterima" {{ request('status') == 'Diterima' ? 'selected' : '' }}>Diterima / Lunas</option>
                            <option value="Menunggu" {{ request('status') == 'Menunggu' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                            <option value="Ditolak" {{ request('status') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>

                        <select name="metode" class="form-select-rt" style="width:135px; font-size:13px; padding:6px 10px;">
                            <option value="all">Semua Metode</option>
                            <option value="Tunai" {{ request('metode') == 'Tunai' ? 'selected' : '' }}>Tunai</option>
                            <option value="Transfer" {{ request('metode') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>

                        <button type="submit" class="btn-rt-primary" style="padding:7px 14px; font-size:13px;">Filter</button>
                        @if(request('q') || (request('status') && request('status') != 'all') || (request('metode') && request('metode') != 'all') || (request('periode') && request('periode') != 'all'))
                            <a href="{{ route('admin.iuran') }}" class="btn-rt-outline" style="padding:7px 12px; font-size:13px;">Reset</a>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Table Riwayat Pembayaran -->
            @if ($riwayatTransaksi->isEmpty())
                <div class="empty-state py-5 text-center">
                    <div style="font-size:36px; margin-bottom:10px;">🧾</div>
                    <div class="fw-semibold text-muted-rt">Belum ada riwayat pembayaran iuran warga.</div>
                    <div style="font-size:12.5px; color:#94a3b8;" class="mt-1">
                        Gunakan tombol "+ Catat Bayar Iuran" di atas untuk mencatat pembayaran tunai secara cepat.
                    </div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-rt mb-0">
                        <thead>
                            <tr>
                                <th>Warga</th>
                                <th>Periode</th>
                                <th>Nominal</th>
                                <th>Metode</th>
                                <th>Status</th>
                                <th>Tanggal Bayar</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayatTransaksi as $trx)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:34px; height:34px; font-size:12px; background:linear-gradient(135deg, #1e5282, #247cbb); flex-shrink:0;">
                                                {{ strtoupper(substr($trx->warga ? $trx->warga->nama : 'W', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold" style="color:var(--color-primary-dark); font-size:13.5px;">
                                                    {{ $trx->warga ? $trx->warga->nama : 'Warga Nonaktif / Dihapus' }}
                                                </div>
                                                <div class="d-flex align-items-center gap-1 mt-1">
                                                    <span class="tag-house">Rumah: {{ $trx->warga && $trx->warga->no_rumah ? $trx->warga->no_rumah : '-' }}</span>
                                                    @if($trx->keterangan)
                                                        <span class="text-muted-rt" style="font-size:11.5px;">• {{ Str::limit($trx->keterangan, 25) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold" style="font-size:13px;">
                                            {{ $trx->tagihan ? periode_label($trx->tagihan->periode) : '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-bold" style="font-size:14px; color:var(--color-primary-dark);">
                                            {{ format_rupiah($trx->jumlah_bayar) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($trx->metode === 'Tunai')
                                            <span class="badge badge-metode-tunai px-2 py-1 rounded-pill">
                                                💵 Tunai
                                            </span>
                                        @else
                                            <span class="badge badge-metode-transfer px-2 py-1 rounded-pill">
                                                📱 Transfer QRIS
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($trx->status_konfirmasi === 'Diterima')
                                            <span class="badge text-bg-success px-2 py-1 rounded-pill" style="font-size:11.5px;">
                                                ✓ Terverifikasi
                                            </span>
                                        @elseif ($trx->status_konfirmasi === 'Menunggu')
                                            <span class="badge text-bg-warning px-2 py-1 rounded-pill" style="font-size:11.5px;">
                                                ⏳ Menunggu
                                            </span>
                                        @else
                                            <span class="badge text-bg-danger px-2 py-1 rounded-pill" style="font-size:11.5px;">
                                                ✕ Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-size:12.5px; color:var(--color-text-muted);">
                                            {{ format_tanggal($trx->tanggal_bayar ?: $trx->created_at, true) }}
                                        </div>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <button type="button" class="btn-rt-outline btn-sm-edit btn-detail-trx"
                                            data-bs-toggle="modal" data-bs-target="#modalDetailTrx"
                                            data-id="{{ $trx->id }}"
                                            data-nama="{{ $trx->warga ? $trx->warga->nama : '-' }}"
                                            data-rumah="{{ $trx->warga ? $trx->warga->no_rumah : '-' }}"
                                            data-nik="{{ $trx->warga ? $trx->warga->nik : '-' }}"
                                            data-periode="{{ $trx->tagihan ? periode_label($trx->tagihan->periode) : '-' }}"
                                            data-nominal="{{ format_rupiah($trx->jumlah_bayar) }}"
                                            data-metode="{{ $trx->metode }}"
                                            data-status="{{ $trx->status_konfirmasi }}"
                                            data-tanggal="{{ format_tanggal($trx->tanggal_bayar ?: $trx->created_at, true) }}"
                                            data-keterangan="{{ $trx->keterangan ?: 'Tidak ada catatan' }}"
                                            data-bukti="{{ $trx->bukti_transfer ? asset('storage/' . $trx->bukti_transfer) : '' }}">
                                            Detail
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($riwayatTransaksi->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $riwayatTransaksi->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 2: MONITORING TAGIHAN PER KK                                          -->
    <!-- ========================================================================= -->
    <div class="tab-pane fade" id="tab-tagihan" role="tabpanel">
        <div class="card-iuran-table mb-4">
            <div class="table-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="section-title mb-1" style="font-size:16px;">
                        <span class="dot"></span> Monitoring Status Tagihan Warga
                    </div>
                    <div class="text-muted-rt" style="font-size:12.5px;">
                        Status kewajiban iuran seluruh kepala keluarga aktif berdasarkan periode penagihan.
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <form method="GET" action="{{ route('admin.iuran') }}" class="d-flex align-items-center gap-2">
                        <select name="periode" class="form-select-rt" style="width:170px; font-size:13px; padding:6px 10px;" onchange="this.form.submit()">
                            <option value="all">Semua Periode</option>
                            @foreach($daftarPeriode as $p)
                                <option value="{{ $p }}" {{ request('periode') == $p ? 'selected' : '' }}>
                                    {{ periode_label($p) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>

            @if ($daftarTagihan->isEmpty())
                <div class="empty-state py-5 text-center">
                    <div style="font-size:36px; margin-bottom:10px;">📋</div>
                    <div class="fw-semibold text-muted-rt">Belum ada tagihan warga untuk periode ini.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-rt mb-0">
                        <thead>
                            <tr>
                                <th>Kepala Keluarga</th>
                                <th>Periode</th>
                                <th>Total Tagihan</th>
                                <th>Status Tagihan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($daftarTagihan as $tagihan)
                                <tr>
                                    <td>
                                        <div class="fw-bold" style="color:var(--color-primary-dark); font-size:13.5px;">
                                            {{ $tagihan->warga ? $tagihan->warga->nama : 'Warga Nonaktif' }}
                                        </div>
                                        <div class="text-muted-rt" style="font-size:11.5px;">
                                            No. Rumah: <span class="tag-house">{{ $tagihan->warga && $tagihan->warga->no_rumah ? $tagihan->warga->no_rumah : '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold" style="font-size:13px;">{{ periode_label($tagihan->periode) }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold" style="color:var(--color-primary-dark); font-size:14px;">
                                            {{ format_rupiah($tagihan->total_tagihan) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($tagihan->status === 'Lunas')
                                            <span class="badge text-bg-success px-2 py-1 rounded-pill" style="font-size:11.5px;">✓ Lunas</span>
                                        @else
                                            <span class="badge text-bg-warning px-2 py-1 rounded-pill" style="font-size:11.5px;">Belum Bayar</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($tagihan->status !== 'Lunas')
                                            <button type="button" class="btn-rt-primary btn-sm-edit btn-bayar-langsung"
                                                data-bs-toggle="modal" data-bs-target="#modalCatatBayar"
                                                data-warga-id="{{ $tagihan->warga_id }}"
                                                data-tagihan-id="{{ $tagihan->id }}"
                                                data-nominal="{{ (int)$tagihan->total_tagihan }}">
                                                + Catat Bayar
                                            </button>
                                        @else
                                            <span class="text-muted-rt" style="font-size:12px;">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($daftarTagihan->hasPages())
                    <div class="p-3 border-top d-flex justify-content-end">
                        {{ $daftarTagihan->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 3: PENGATURAN TARIF & KOMPONEN IURAN                                  -->
    <!-- ========================================================================= -->
    <div class="tab-pane fade" id="tab-tarif" role="tabpanel">
        
        <!-- Grafik & Proporsi Iuran -->
        <div class="card-rt mb-4">
            <div class="section-title"><span class="dot"></span> Proporsi Nominal Komponen Iuran</div>
            @php
                $maksIuran = max($grafikIuran->max('nominal'), 1);
            @endphp
            @if ($grafikIuran->isEmpty())
                <div class="empty-state">Belum ada data komponen iuran.</div>
            @else
                <div class="d-grid gap-3">
                    @foreach ($grafikIuran as $item)
                        @php
                            $lebar = $maksIuran > 0 ? round(($item['nominal'] / $maksIuran) * 100) : 0;
                        @endphp
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:13px;">
                                <span>
                                    <strong>{{ $item['nama'] }}</strong>
                                    @if (! $item['aktif'])
                                        <span class="text-muted-rt ms-1">(Nonaktif)</span>
                                    @endif
                                </span>
                                <strong>{{ format_rupiah($item['nominal']) }}</strong>
                            </div>
                            <div class="rounded-pill overflow-hidden" style="height:12px; background:#edf1ee;">
                                <div style="width:{{ $item['aktif'] ? max($lebar, 4) : 0 }}%; height:100%; background:{{ $item['aktif'] ? 'linear-gradient(90deg, #1e5282, #247cbb)' : '#adb5b0' }}; border-radius:999px;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Tabel Komponen Iuran -->
        <div class="card-rt">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="section-title mb-0"><span class="dot"></span> Daftar Komponen Iuran Bulanan</div>
                <button class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Jenis Iuran</button>
            </div>

            @if ($daftarIuran->isEmpty())
                <div class="empty-state">Belum ada jenis iuran. Klik "Tambah Jenis Iuran" untuk membuat.</div>
            @else
            <div class="table-responsive">
                <table class="table-rt mb-0">
                    <thead>
                        <tr>
                            <th>Nama Iuran</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($daftarIuran as $i)
                        <tr>
                            <td><strong>{{ $i->nama_iuran }}</strong></td>
                            <td><span class="fw-bold" style="color:var(--color-primary-dark);">{{ format_rupiah($i->nominal) }}</span></td>
                            <td>
                                @if ($i->status_aktif)
                                    <span class="badge text-bg-success px-2 py-1 rounded-pill">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary px-2 py-1 rounded-pill">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-nowrap text-end">
                                <button class="btn-rt-outline btn-sm-edit btn-edit-iuran"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit"
                                    data-id="{{ $i->id }}"
                                    data-nama="{{ $i->nama_iuran }}"
                                    data-nominal="{{ (int)$i->nominal }}"
                                    data-aktif="{{ $i->status_aktif ? '1' : '0' }}">Ubah</button>
                                <form action="{{ route('admin.iuran.destroy', $i->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jenis iuran ini?');">
                                    @csrf
                                    <button type="submit" class="btn-rt-outline btn-sm-edit" style="color:var(--color-danger); border-color:#eecfcd;">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: + CATAT BAYAR IURAN                                                -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalCatatBayar" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; border:none; box-shadow:0 15px 35px rgba(0,0,0,0.15);">
            <form action="{{ route('admin.transaksi.store') }}" method="POST">
                @csrf
                <input type="hidden" name="source" value="iuran">
                <input type="hidden" name="redirect_to" value="iuran">

                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge" style="background:#e8f4fc; color:#175086; font-size:12px;">PENCATATAN MANUAL</span>
                        </div>
                        <h5 class="modal-title fw-bold" style="font-family:var(--font-display); color:var(--color-primary-dark);">Catat Bayar Iuran Warga</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    @if ($daftarWargaBelumLunas->isEmpty())
                        <div class="alert alert-info border-0 rounded-3 mb-0" style="background:#e8f4fc; color:#175086;">
                            <div class="fw-bold mb-1">🎉 Seluruh Tagihan Lunas!</div>
                            <div style="font-size:13px;">Saat ini tidak ada warga yang memiliki tagihan iuran tertunggak.</div>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label-rt fw-semibold">Pilih Warga / Kepala Keluarga <span class="text-danger">*</span></label>
                            <select id="selectWargaBayar" class="form-select-rt" required>
                                <option value="">-- Pilih Warga yang Belum Lunas --</option>
                                @foreach($daftarWargaBelumLunas as $w)
                                    <option value="{{ $w->id }}" data-tagihan='@json($w->tagihan)'>
                                        {{ $w->nama }} (Rumah: {{ $w->no_rumah ?: '-' }}) — {{ $w->tagihan->count() }} Tagihan
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-rt fw-semibold">Pilih Tagihan Periode <span class="text-danger">*</span></label>
                            <select name="tagihan_id" id="selectTagihanId" class="form-select-rt" required>
                                <option value="">-- Pilih Warga terlebih dahulu --</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-rt fw-semibold">Nominal Pembayaran (Rp) <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah_bayar" id="inputJumlahBayar" class="form-control-rt" min="1000" placeholder="Contoh: 50000" required>
                            <div class="form-text" style="font-size:12px;">Otomatis disesuaikan dengan nominal tagihan yang dipilih.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label-rt fw-semibold">Keterangan / Catatan (Opsional)</label>
                            <input type="text" name="keterangan" class="form-control-rt" placeholder="Contoh: Pembayaran tunai via bendahara">
                        </div>

                        <div class="p-3 rounded-3" style="background:#f8fafc; border:1px dashed #cbd5e1; font-size:12.5px;">
                            <div class="d-flex align-items-center gap-2 text-muted-rt">
                                <span>ℹ️</span> Pembayaran ini otomatis diverifikasi berstatus <strong>Lunas</strong> dan tercatat ke laporan kas.
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0 px-4 pb-4">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Tutup</button>
                    @if (!$daftarWargaBelumLunas->isEmpty())
                        <button type="submit" class="btn-rt-primary px-4">Simpan Pembayaran</button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: DETAIL TRANSAKSI                                                    -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalDetailTrx" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius:18px; border:none; box-shadow:0 15px 35px rgba(0,0,0,0.15);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" style="font-family:var(--font-display); color:var(--color-primary-dark);">Rincian Transaksi Iuran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-3" style="background:#f4f8fc; border:1px solid #dce8f2;">
                    <div>
                        <div class="text-muted-rt" style="font-size:12px;">Total Pembayaran</div>
                        <div id="detailNominal" class="fw-bold" style="font-size:22px; color:var(--color-primary-dark);">-</div>
                    </div>
                    <div id="detailBadgeStatus"></div>
                </div>

                <div class="row g-2 mb-3" style="font-size:13px;">
                    <div class="col-5 text-muted-rt">Nama Warga</div>
                    <div class="col-7 fw-bold" id="detailNama">-</div>

                    <div class="col-5 text-muted-rt">No. Rumah</div>
                    <div class="col-7" id="detailRumah">-</div>

                    <div class="col-5 text-muted-rt">NIK</div>
                    <div class="col-7" id="detailNik">-</div>

                    <div class="col-5 text-muted-rt">Periode Tagihan</div>
                    <div class="col-7 fw-semibold" id="detailPeriode">-</div>

                    <div class="col-5 text-muted-rt">Metode Pembayaran</div>
                    <div class="col-7" id="detailMetode">-</div>

                    <div class="col-5 text-muted-rt">Waktu Pembayaran</div>
                    <div class="col-7" id="detailTanggal">-</div>

                    <div class="col-5 text-muted-rt">Catatan</div>
                    <div class="col-7" id="detailKeterangan">-</div>
                </div>

                <div id="wrapperBukti" style="display:none;" class="mt-3">
                    <label class="form-label-rt fw-semibold mb-1">Bukti Transfer</label>
                    <div class="text-center p-2 rounded-3 border">
                        <img id="detailBuktiImg" src="" alt="Bukti Transfer" class="img-fluid rounded" style="max-height:240px;">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TAMBAH JENIS IURAN                                                  -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;">
      <form action="{{ route('admin.iuran.store') }}" method="POST">
        @csrf
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold" style="font-family:var(--font-display); color:var(--color-primary-dark);">Tambah Komponen Iuran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label-rt">Nama Komponen Iuran</label>
                <input type="text" name="nama_iuran" class="form-control-rt" placeholder="Contoh: Iuran Kebersihan & Sampah" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Nominal per Bulan (Rp)</label>
                <input type="number" name="nominal" class="form-control-rt" placeholder="Contoh: 25000" min="0" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="status_aktif" id="aktifTambah" checked>
                <label class="form-check-label fw-semibold" for="aktifTambah">Aktifkan komponen ini sekarang</label>
            </div>
        </div>
        <div class="modal-footer border-0 pt-0 px-4 pb-4">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary px-4">Simpan Komponen</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: UBAH JENIS IURAN                                                    -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:18px;">
      <form id="formEditIuran" method="POST">
        @csrf
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold" style="font-family:var(--font-display); color:var(--color-primary-dark);">Ubah Komponen Iuran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
            <div class="mb-3">
                <label class="form-label-rt">Nama Komponen Iuran</label>
                <input type="text" name="nama_iuran" id="editNama" class="form-control-rt" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Nominal per Bulan (Rp)</label>
                <input type="number" name="nominal" id="editNominal" class="form-control-rt" min="0" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="status_aktif" id="editAktif">
                <label class="form-check-label fw-semibold" for="editAktif">Status Aktif</label>
            </div>
        </div>
        <div class="modal-footer border-0 pt-0 px-4 pb-4">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary px-4">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// 1. Script Modal Edit Iuran
document.querySelectorAll('.btn-edit-iuran').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('formEditIuran').action = "{{ url('admin/iuran/update') }}/" + id;
        document.getElementById('editNama').value = btn.dataset.nama || '';
        document.getElementById('editNominal').value = btn.dataset.nominal || '';
        document.getElementById('editAktif').checked = btn.dataset.aktif === '1';
    });
});

// 2. Script Modal Detail Transaksi
document.querySelectorAll('.btn-detail-trx').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('detailNama').textContent = btn.dataset.nama;
        document.getElementById('detailRumah').textContent = btn.dataset.rumah;
        document.getElementById('detailNik').textContent = btn.dataset.nik;
        document.getElementById('detailPeriode').textContent = btn.dataset.periode;
        document.getElementById('detailNominal').textContent = btn.dataset.nominal;
        document.getElementById('detailMetode').textContent = btn.dataset.metode;
        document.getElementById('detailTanggal').textContent = btn.dataset.tanggal;
        document.getElementById('detailKeterangan').textContent = btn.dataset.keterangan;

        const badgeBox = document.getElementById('detailBadgeStatus');
        const st = btn.dataset.status;
        if (st === 'Diterima') {
            badgeBox.innerHTML = '<span class="badge text-bg-success px-3 py-2 rounded-pill">✓ Terverifikasi</span>';
        } else if (st === 'Menunggu') {
            badgeBox.innerHTML = '<span class="badge text-bg-warning px-3 py-2 rounded-pill">⏳ Menunggu</span>';
        } else {
            badgeBox.innerHTML = '<span class="badge text-bg-danger px-3 py-2 rounded-pill">✕ Ditolak</span>';
        }

        const wrapperBukti = document.getElementById('wrapperBukti');
        const imgBukti = document.getElementById('detailBuktiImg');
        if (btn.dataset.bukti) {
            imgBukti.src = btn.dataset.bukti;
            wrapperBukti.style.display = 'block';
        } else {
            imgBukti.src = '';
            wrapperBukti.style.display = 'none';
        }
    });
});

// 3. Dynamic Dropdown di Modal Catat Bayar
const selectWarga = document.getElementById('selectWargaBayar');
const selectTagihan = document.getElementById('selectTagihanId');
const inputNominal = document.getElementById('inputJumlahBayar');

if (selectWarga && selectTagihan) {
    selectWarga.addEventListener('change', function() {
        selectTagihan.innerHTML = '';
        inputNominal.value = '';

        const selectedOpt = this.options[this.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            selectTagihan.innerHTML = '<option value="">-- Pilih Warga terlebih dahulu --</option>';
            return;
        }

        try {
            const tagihanList = JSON.parse(selectedOpt.dataset.tagihan || '[]');
            if (tagihanList.length === 0) {
                selectTagihan.innerHTML = '<option value="">Tidak ada tagihan tertunggak</option>';
                return;
            }

            tagihanList.forEach((tg, idx) => {
                const opt = document.createElement('option');
                opt.value = tg.id;
                opt.textContent = 'Periode ' + tg.periode + ' - Rp ' + Number(tg.total_tagihan).toLocaleString('id-ID');
                opt.dataset.nominal = tg.total_tagihan;
                selectTagihan.appendChild(opt);

                if (idx === 0) {
                    inputNominal.value = Math.round(Number(tg.total_tagihan));
                }
            });
        } catch (e) {
            console.error(e);
        }
    });

    selectTagihan.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        if (opt && opt.dataset.nominal) {
            inputNominal.value = Math.round(Number(opt.dataset.nominal));
        }
    });
}

// 4. Trigger Bayar Langsung dari Tab Tagihan
document.querySelectorAll('.btn-bayar-langsung').forEach(btn => {
    btn.addEventListener('click', () => {
        const wargaId = btn.dataset.wargaId;
        const tagihanId = btn.dataset.tagihanId;
        const nominal = btn.dataset.nominal;

        if (selectWarga) {
            selectWarga.value = wargaId;
            selectWarga.dispatchEvent(new Event('change'));

            setTimeout(() => {
                if (selectTagihan) {
                    selectTagihan.value = tagihanId;
                }
                if (inputNominal && nominal) {
                    inputNominal.value = nominal;
                }
            }, 100);
        }
    });
});
</script>
@endpush
