@extends('layouts.app')

@section('title', 'Dashboard Eksekutif')
@section('page_title', 'Dashboard Eksekutif')
@section('page_subtitle', 'Ringkasan tren kas & kepatuhan warga RT')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Saldo Kas Total</div>
            <div class="stat-value" style="font-size:22px;">{{ format_rupiah($saldoKas) }}</div>
            <div class="stat-hint">Akumulasi seluruh iuran diterima</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card accent">
            <div class="stat-label">Tingkat Kepatuhan</div>
            <div class="stat-value">{{ $persenKepatuhan }}%</div>
            <div class="stat-hint">{{ $wargaLunas }} dari {{ $totalWargaAktif }} rumah lunas</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card {{ $wargaTunggakan3 > 0 ? 'warn' : '' }}">
            <div class="stat-label">Tunggakan &gt; 3 Bulan</div>
            <div class="stat-value">{{ $wargaTunggakan3 }}</div>
            <div class="stat-hint">Perlu perhatian / pendekatan khusus</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-label">Persetujuan Menunggu</div>
            <div class="stat-value">{{ $suratMenunggu + $kegiatanMenunggu }}</div>
            <div class="stat-hint">{{ $suratMenunggu }} surat, {{ $kegiatanMenunggu }} kegiatan</div>
        </div>
    </div>
</div>

<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Tren Pemasukan Kas 6 Bulan Terakhir</div>
    <div class="table-responsive">
        <table class="table-rt">
            <thead>
                <tr>
                    @foreach ($periodeList as $p)
                        <th class="text-center">{{ periode_label($p) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                <tr>
                    @foreach ($periodeList as $p)
                        <td class="text-center">
                            <strong style="color:var(--color-primary);">{{ format_rupiah($dataTren[$p] ?? 0) }}</strong>
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card-rt">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="section-title mb-0"><span class="dot"></span> Permohonan Surat</div>
                <a href="{{ route('ketua.surat') }}" class="btn-rt-outline" style="font-size:12px; padding:3px 8px;">Tinjau</a>
            </div>
            <p class="text-muted-rt mb-0" style="font-size:13.5px;">
                Saat ini terdapat <b>{{ $suratMenunggu }}</b> surat yang menunggu persetujuan dan tanda tangan Ketua RT.
            </p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-rt">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="section-title mb-0"><span class="dot"></span> Usulan Kegiatan</div>
                <a href="{{ route('ketua.informasi') }}" class="btn-rt-outline" style="font-size:12px; padding:3px 8px;">Tinjau</a>
            </div>
            <p class="text-muted-rt mb-0" style="font-size:13.5px;">
                Saat ini terdapat <b>{{ $kegiatanMenunggu }}</b> usulan kegiatan RT yang menunggu persetujuan untuk dipublikasikan.
            </p>
        </div>
    </div>
</div>
@endsection
