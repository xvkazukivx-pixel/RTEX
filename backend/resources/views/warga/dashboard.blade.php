@extends('layouts.app')

@section('title', 'Dashboard Saya')
@section('page_title', 'Dashboard Saya')
@section('page_subtitle', 'Halo, ' . $warga->nama . ' 👋')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card {{ (!$tagihanBulanIni || $tagihanBulanIni->status === 'Lunas') ? '' : 'warn' }}">
            <div class="stat-label">Status Iuran {{ periode_label($periode) }}</div>
            @if (!$tagihanBulanIni)
                <div class="stat-value" style="font-size:18px;">Tagihan belum diterbitkan</div>
                <div class="stat-hint">Bendahara belum menerbitkan tagihan bulan ini</div>
            @else
                <div class="stat-value">{!! badge_status($tagihanBulanIni->status) !!}</div>
                <div class="stat-hint">{{ format_rupiah($tagihanBulanIni->total_tagihan) }}</div>
            @endif
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card {{ $jumlahBulanTunggakan > 0 ? 'accent' : '' }}">
            <div class="stat-label">Total Belum Lunas</div>
            <div class="stat-value">{{ format_rupiah($totalTunggakan) }}</div>
            <div class="stat-hint">
                @if ($jumlahBulanTunggakan > 0)
                    Ada {{ $jumlahBulanTunggakan }} periode tagihan yang perlu diselesaikan
                @else
                    Semua tagihan Anda lunas, terima kasih! 🎉
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card-rt mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Pembayaran Iuran</div>
        @if ($jumlahBulanTunggakan > 0)
            <a href="{{ route('warga.bayar') }}" class="btn-rt-primary">💳 Bayar Iuran Sekarang</a>
        @endif
    </div>
    <p class="text-muted-rt mb-0" style="font-size:13.5px;">
        Gunakan menu <b>Bayar Iuran</b> untuk mengunggah bukti transfer, atau bayar langsung secara tunai ke Bendahara RT.
    </p>
</div>

<!-- Akses Gerbang IoT & Autentikasi e-KTP -->
@php
    $currentUser = auth()->user();
@endphp
<div class="card-rt mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Akses Portal Gerbang (IoT RFID/NFC)</div>
        <div class="d-flex gap-2">
            @if ($currentUser->hasRfidRegistered())
                <a href="{{ route('warga.rfid') }}" class="btn-rt-outline" style="border-color: #3b82f6; color: #2563eb;">
                    🔍 Kelola Akses & Riwayat Tap
                </a>
            @else
                <a href="{{ route('warga.rfid', ['scan' => 1]) }}" class="btn-rt-primary">
                    📡 Pindai e-KTP via ESP32
                </a>
            @endif
        </div>
    </div>
    
    <div class="p-3 rounded-3" style="background: {{ $currentUser->hasRfidRegistered() && $currentUser->rfid_is_active ? 'rgba(16, 185, 129, 0.08)' : 'rgba(239, 68, 68, 0.06)' }}; border: 1px solid {{ $currentUser->hasRfidRegistered() && $currentUser->rfid_is_active ? 'rgba(16, 185, 129, 0.25)' : 'rgba(239, 68, 68, 0.2)' }};">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <span style="font-size: 28px;">{{ $currentUser->hasRfidRegistered() ? '💳' : '📡' }}</span>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <strong style="font-size: 14.5px;">Status Hak Akses Gerbang:</strong>
                        @if ($currentUser->hasRfidRegistered())
                            @if ($currentUser->rfid_is_active)
                                <span class="badge bg-success text-white">● AKTIF (TERDAFTAR)</span>
                            @else
                                <span class="badge bg-danger text-white">● DINONAKTIFKAN</span>
                            @endif
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border">○ BELUM TERDAFTAR</span>
                        @endif
                    </div>
                    <div class="text-muted-rt mt-1" style="font-size: 13px;">
                        @if ($currentUser->hasRfidRegistered())
                            e-KTP fisik Anda dengan UID <code class="font-monospace fw-bold" style="color: #2563eb;">{{ $currentUser->rfid_uid }}</code> terdaftar untuk membuka portal perumahan otomatis.
                        @else
                            Akun Anda belum memiliki akses e-KTP. Klik <b>"Pindai e-KTP via ESP32"</b> untuk mulai menghubungkan kartu fisik e-KTP Anda ke akun ini.
                        @endif
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('warga.rfid') }}" class="btn btn-sm btn-outline-secondary">
                    Buka Menu Akses KTP &rarr;
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Data Kependudukan Saya</div>
    <div class="row g-3" style="font-size:14px;">
        <div class="col-md-3">
            <div class="text-muted-rt">Nama Lengkap</div>
            <div class="fw-semibold">{{ $warga->nama }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted-rt">No. Rumah</div>
            <div class="fw-semibold">{{ $warga->no_rumah ?: '-' }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted-rt">Status Hunian</div>
            <div class="fw-semibold">{{ $warga->status_hunian }}</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted-rt">No. WhatsApp</div>
            <div class="fw-semibold">{{ $warga->no_hp ?: '-' }}</div>
        </div>
    </div>
</div>
@endsection
