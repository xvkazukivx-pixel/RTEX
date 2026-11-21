@extends('layouts.app')

@section('title', 'Rekap Posyandu')
@section('page_title', 'Rekap Posyandu')
@section('page_subtitle', 'Monitoring agenda dan data perkembangan posyandu anak')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-5">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Agenda Jadwal Posyandu</div>
            @if ($jadwal->isEmpty())
                <div class="empty-state">Belum ada agenda jadwal posyandu.</div>
            @else
                <div class="table-responsive">
                    <table class="table-rt">
                        <thead><tr><th>Agenda</th><th>Waktu</th><th>Pemeriksaan</th></tr></thead>
                        <tbody>
                        @foreach ($jadwal as $j)
                            <tr>
                                <td>
                                    <strong>{{ $j->judul }}</strong>
                                    <div class="text-muted-rt" style="font-size:12px;">📍 {{ $j->lokasi }}</div>
                                </td>
                                <td>{{ format_tanggal($j->tanggal, true) }}</td>
                                <td><span class="badge text-bg-light border">{{ $j->pemeriksaan_count }} anak</span></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Hasil Pemeriksaan Terbaru</div>
            @if ($riwayat->isEmpty())
                <div class="empty-state">Belum ada data pemeriksaan balita.</div>
            @else
                <div class="table-responsive">
                    <table class="table-rt">
                        <thead><tr><th>Anak</th><th>Jadwal</th><th>BB / TB</th><th>Imunisasi</th></tr></thead>
                        <tbody>
                        @foreach ($riwayat as $p)
                            <tr>
                                <td>
                                    <strong>{{ $p->anak->nama ?? '-' }}</strong>
                                    <div class="text-muted-rt" style="font-size:11.5px;">Wali: {{ $p->anak->warga->nama ?? '-' }}</div>
                                </td>
                                <td>{{ $p->jadwal->judul ?? '-' }}</td>
                                <td>{{ $p->berat_kg ?: '-' }} kg / {{ $p->tinggi_cm ?: '-' }} cm</td>
                                <td>{{ $p->imunisasi ?: '-' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
