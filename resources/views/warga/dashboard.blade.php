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
