@extends('layouts.app')

@section('title', 'Input Transaksi Manual')
@section('page_title', 'Input Transaksi Manual')
@section('page_subtitle', 'Catat pembayaran tunai langsung ke Bendahara')

@section('content')
<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Pilih Warga</div>
    <form method="GET" action="{{ route('admin.transaksi') }}" class="d-flex gap-2 flex-wrap align-items-end">
        <div class="flex-grow-1" style="max-width:380px;">
            <label class="form-label-rt">Warga yang Memiliki Tunggakan</label>
            <select name="warga_id" class="form-control-rt" onchange="this.form.submit()">
                <option value="">— Pilih Warga —</option>
                @foreach ($daftarWargaBelumLunas as $w)
                    <option value="{{ $w->id }}" {{ $wargaId == $w->id ? 'selected' : '' }}>
                        {{ $w->nama }} (Rumah: {{ $w->no_rumah ?: '-' }})
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-rt-primary">Pilih</button>
    </form>
</div>

@if ($wargaTerpilih)
<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Tagihan Belum Lunas: <b>{{ $wargaTerpilih->nama }}</b></div>

    @if ($tagihanBelumLunas->isEmpty())
        <div class="empty-state">Warga ini tidak memiliki tagihan yang belum lunas. 🎉</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Periode</th><th>Total Tagihan</th><th>Status</th><th style="min-width:320px;">Catat Pembayaran Tunai</th></tr>
                </thead>
                <tbody>
                @foreach ($tagihanBelumLunas as $t)
                    <tr>
                        <td><strong>{{ periode_label($t->periode) }}</strong></td>
                        <td>{{ format_rupiah($t->total_tagihan) }}</td>
                        <td>{!! badge_status($t->status) !!}</td>
                        <td>
                            <form action="{{ route('admin.transaksi.store') }}" method="POST" class="d-flex gap-2 flex-wrap align-items-center">
                                @csrf
                                <input type="hidden" name="tagihan_id" value="{{ $t->id }}">
                                <input type="number" name="jumlah_bayar" value="{{ (int)$t->total_tagihan }}" class="form-control-rt" style="width:130px;" required>
                                <input type="text" name="keterangan" class="form-control-rt" placeholder="Catatan opsional" style="width:140px;">
                                <button type="submit" class="btn-rt-primary" style="padding:7px 14px;">Bayar Tunai</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endif
@endsection
