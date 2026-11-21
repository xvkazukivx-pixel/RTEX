@extends('layouts.app')

@section('title', 'Riwayat Pembayaran')
@section('page_title', 'Riwayat Pembayaran')
@section('page_subtitle', 'Histori transaksi pembayaran iuran Anda')

@section('content')
<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Riwayat Transaksi Saya ({{ $riwayat->count() }})</div>
    @if ($riwayat->isEmpty())
        <div class="empty-state">Belum ada riwayat pembayaran.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Periode</th><th>Jumlah</th><th>Metode</th><th>Tanggal Bayar</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($riwayat as $r)
                    <tr>
                        <td><strong>{{ periode_label($r->tagihan->periode ?? '-') }}</strong></td>
                        <td>{{ format_rupiah($r->jumlah_bayar) }}</td>
                        <td><span class="badge text-bg-light border">{{ $r->metode }}</span></td>
                        <td>{{ format_tanggal($r->tanggal_bayar, true) }}</td>
                        <td>
                            {!! badge_status($r->status_konfirmasi) !!}
                            @if ($r->status_konfirmasi === 'Ditolak' && $r->catatan_admin)
                                <div class="text-muted-rt" style="font-size:11.5px; margin-top:4px;">Catatan: {{ $r->catatan_admin }}</div>
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
