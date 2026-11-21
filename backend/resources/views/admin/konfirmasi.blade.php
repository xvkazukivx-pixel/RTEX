@extends('layouts.app')

@section('title', 'Konfirmasi Pembayaran')
@section('page_title', 'Konfirmasi Pembayaran')
@section('page_subtitle', 'Validasi bukti transfer yang diunggah warga')

@section('content')
<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Antrian Validasi ({{ $antrian->count() }})</div>

    @if ($antrian->isEmpty())
        <div class="empty-state">Tidak ada antrian pembayaran menunggu konfirmasi saat ini. 👍</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr>
                        <th>Warga</th><th>Periode</th><th>Jumlah</th><th>Tanggal Bayar</th>
                        <th>Bukti Transfer</th><th style="min-width:280px;">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($antrian as $tr)
                    <tr>
                        <td>
                            <strong>{{ $tr->warga->nama ?? '-' }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">Rumah: {{ $tr->warga->no_rumah ?: '-' }}</div>
                        </td>
                        <td>{{ periode_label($tr->tagihan->periode ?? '-') }}</td>
                        <td>{{ format_rupiah($tr->jumlah_bayar) }}</td>
                        <td>{{ format_tanggal($tr->tanggal_bayar, true) }}</td>
                        <td>
                            @if ($tr->bukti_transfer)
                                <a href="{{ asset('storage/bukti_transfer/' . $tr->bukti_transfer) }}" target="_blank" class="btn-rt-outline" style="font-size:12px; padding:4px 8px;">
                                    📎 Lihat Bukti
                                </a>
                            @else
                                <span class="text-muted-rt">-</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <form action="{{ route('admin.konfirmasi.action', $tr->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="aksi" value="terima">
                                    <button type="submit" class="btn-rt-primary" style="padding:6px 14px;">Terima</button>
                                </form>
                                <form action="{{ route('admin.konfirmasi.action', $tr->id) }}" method="POST" class="d-flex gap-1" onsubmit="return confirm('Tolak pembayaran ini?');">
                                    @csrf
                                    <input type="hidden" name="aksi" value="tolak">
                                    <input type="text" name="catatan" placeholder="Alasan penolakan" class="form-control-rt" style="width:140px; font-size:12px;" required>
                                    <button type="submit" class="btn-rt-outline" style="padding:6px 10px; color:var(--color-danger); border-color:#eecfcd;">Tolak</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Riwayat Konfirmasi Terakhir</div>
    @if ($riwayat->isEmpty())
        <div class="empty-state">Belum ada riwayat konfirmasi.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Warga</th><th>Periode</th><th>Jumlah</th><th>Status</th><th>Tanggal Konfirmasi</th><th>Catatan</th></tr>
                </thead>
                <tbody>
                @foreach ($riwayat as $r)
                    <tr>
                        <td>{{ $r->warga->nama ?? '-' }}</td>
                        <td>{{ periode_label($r->tagihan->periode ?? '-') }}</td>
                        <td>{{ format_rupiah($r->jumlah_bayar) }}</td>
                        <td>{!! badge_status($r->status_konfirmasi) !!}</td>
                        <td>{{ format_tanggal($r->tanggal_konfirmasi, true) }}</td>
                        <td>{{ $r->catatan_admin ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
