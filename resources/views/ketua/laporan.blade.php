@extends('layouts.app')

@section('title', 'Laporan & Validasi')
@section('page_title', 'Laporan & Validasi')
@section('page_subtitle', 'Tinjau dan validasi laporan keuangan bulanan RT')

@section('content')
<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Laporan Bulanan</div>
    @if (empty($rows))
        <div class="empty-state">Belum ada laporan yang tersedia.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Periode</th><th>Warga Lunas</th><th>Total Pemasukan</th><th>Status Validasi</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @foreach ($rows as $r)
                    <tr>
                        <td><strong>{{ periode_label($r['periode']) }}</strong></td>
                        <td>{{ $r['warga_lunas'] }} / {{ $r['total_warga'] }} warga</td>
                        <td><strong>{{ format_rupiah($r['total_masuk']) }}</strong></td>
                        <td>{!! badge_status($r['status_validasi']) !!}</td>
                        <td>
                            @if ($r['status_validasi'] !== 'Tervalidasi')
                                <form action="{{ route('ketua.laporan.validasi') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="periode" value="{{ $r['periode'] }}">
                                    <button type="submit" class="btn-rt-primary" style="font-size:12px; padding:6px 14px;">Validasi Laporan</button>
                                </form>
                            @else
                                <span class="text-success" style="font-size:12.5px;">✓ Tervalidasi pada {{ format_tanggal($r['tanggal_validasi']) }}</span>
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
