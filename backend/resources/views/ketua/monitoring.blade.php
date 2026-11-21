@extends('layouts.app')

@section('title', 'Monitoring Warga')
@section('page_title', 'Monitoring Warga')
@section('page_subtitle', 'Daftar warga & status kepatuhan iuran (hanya baca)')

@section('content')
<div class="card-rt">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Daftar Warga ({{ $daftarWarga->count() }})</div>
        <div class="d-flex gap-2">
            <a href="{{ route('ketua.monitoring') }}" class="btn-rt-outline {{ !$filterTunggakan ? 'active' : '' }}" style="{{ !$filterTunggakan ? 'border-color:var(--color-primary); color:var(--color-primary);' : '' }}">Semua Warga</a>
            <a href="{{ route('ketua.monitoring', ['tunggakan' => '3']) }}" class="btn-rt-outline {{ $filterTunggakan ? 'active' : '' }}" style="{{ $filterTunggakan ? 'border-color:var(--color-danger); color:var(--color-danger);' : '' }}">⚠ Tunggakan &gt; 3 Bulan</a>
        </div>
    </div>

    @if ($daftarWarga->isEmpty())
        <div class="empty-state">Tidak ada data warga yang sesuai filter.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Nama</th><th>No. Rumah</th><th>Status Hunian</th><th>Status Aktif</th><th>Jumlah Tunggakan</th></tr></thead>
                <tbody>
                @foreach ($daftarWarga as $w)
                    <tr>
                        <td><strong>{{ $w->nama }}</strong></td>
                        <td>{{ $w->no_rumah ?: '-' }}</td>
                        <td>{{ $w->status_hunian }}</td>
                        <td>
                            @if ($w->status_aktif)
                                <span class="badge-status text-bg-success">Aktif</span>
                            @else
                                <span class="badge-status text-bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            @if ($w->jumlah_tunggakan > 3)
                                <span class="badge-status text-bg-danger">{{ $w->jumlah_tunggakan }} bulan</span>
                            @elseif ($w->jumlah_tunggakan > 0)
                                <span class="badge-status text-bg-warning">{{ $w->jumlah_tunggakan }} bulan</span>
                            @else
                                <span class="badge-status text-bg-success">Lunas</span>
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
