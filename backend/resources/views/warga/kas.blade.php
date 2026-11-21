@extends('layouts.app')

@section('title', 'Info Kas Umum')
@section('page_title', 'Info Kas Umum')
@section('page_subtitle', 'Transparansi saldo & penggunaan kas lingkungan RT')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-label">Saldo Kas RT Saat Ini</div>
            <div class="stat-value">{{ format_rupiah($saldoKas) }}</div>
            <div class="stat-hint">Akumulasi seluruh iuran warga yang terkonfirmasi</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card accent">
            <div class="stat-label">Kas Masuk {{ periode_label($periode) }}</div>
            <div class="stat-value">{{ format_rupiah($kasBulanIni) }}</div>
            <div class="stat-hint">{{ $wargaLunas }} dari {{ $totalWargaAktif }} rumah sudah bayar bulan ini</div>
        </div>
    </div>
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Rincian Jenis Iuran Aktif</div>
    @if ($daftarIuranAktif->isEmpty())
        <div class="empty-state">Belum ada jenis iuran yang diatur.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Jenis Iuran</th><th>Nominal per Bulan</th></tr></thead>
                <tbody>
                @foreach ($daftarIuranAktif as $i)
                    <tr>
                        <td><strong>{{ $i->nama_iuran }}</strong></td>
                        <td>{{ format_rupiah($i->nominal) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
