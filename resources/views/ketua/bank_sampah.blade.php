@extends('layouts.app')

@section('title', 'Rekap Bank Sampah')
@section('page_title', 'Rekap Bank Sampah')
@section('page_subtitle', 'Monitoring perputaran bank sampah warga RT (hanya baca)')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="stat-card">
            <div class="stat-label">Total Nilai Setoran Diterima</div>
            <div class="stat-value">{{ format_rupiah($totalSetoran) }}</div>
            <div class="stat-hint">Dana tabungan sampah warga</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-card accent">
            <div class="stat-label">Total Sampah Terkumpul</div>
            <div class="stat-value">{{ number_format($totalBerat, 2) }} kg</div>
            <div class="stat-hint">Volume sampah daur ulang warga</div>
        </div>
    </div>
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Riwayat Setoran Warga</div>
    @if ($riwayat->isEmpty())
        <div class="empty-state">Belum ada riwayat setoran bank sampah.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Warga</th><th>Jenis Sampah</th><th>Berat</th><th>Nilai</th><th>Status</th><th>Tanggal</th></tr></thead>
                <tbody>
                @foreach ($riwayat as $s)
                    <tr>
                        <td><strong>{{ $s->warga->nama ?? '-' }}</strong></td>
                        <td>{{ $s->nama_sampah ?: ($s->jenis->nama ?? '-') }}</td>
                        <td>{{ $s->berat_final ?: $s->berat_estimasi }} kg</td>
                        <td>{{ $s->nilai_setoran !== null ? format_rupiah($s->nilai_setoran) : '-' }}</td>
                        <td>{!! badge_status($s->status) !!}</td>
                        <td>{{ format_tanggal($s->created_at, true) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
