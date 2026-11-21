@extends('layouts.app')

@section('title', 'Rekapitulasi Kas')
@section('page_title', 'Rekapitulasi Kas')
@section('page_subtitle', 'Saldo & histori transaksi kas (hanya baca)')

@section('content')
<div class="stat-card mb-4" style="max-width:340px;">
    <div class="stat-label">Saldo Kas Total (Sejak Awal)</div>
    <div class="stat-value">{{ format_rupiah($saldoKas) }}</div>
    <div class="stat-hint">Akumulasi seluruh transaksi terkonfirmasi</div>
</div>

<div class="card-rt">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div class="section-title mb-0"><span class="dot"></span> Transaksi per Periode</div>
        <form method="GET" action="{{ route('ketua.rekap') }}">
            <select name="periode" class="form-control-rt" onchange="this.form.submit()">
                @foreach ($daftarPeriode as $p)
                    <option value="{{ $p }}" {{ $p === $periode ? 'selected' : '' }}>{{ periode_label($p) }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="mb-3 text-muted-rt" style="font-size:13.5px;">
        Total pemasukan periode ini: <b style="color:var(--color-primary);">{{ format_rupiah($totalPeriode) }}</b>
    </div>

    @if ($transaksiPeriode->isEmpty())
        <div class="empty-state">Belum ada transaksi pada periode ini.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Nama Warga</th><th>No. Rumah</th><th>Jumlah</th><th>Metode</th><th>Tanggal Bayar</th></tr></thead>
                <tbody>
                @foreach ($transaksiPeriode as $t)
                    <tr>
                        <td><strong>{{ $t->warga->nama ?? '-' }}</strong></td>
                        <td>{{ $t->warga->no_rumah ?: '-' }}</td>
                        <td>{{ format_rupiah($t->jumlah_bayar) }}</td>
                        <td><span class="badge text-bg-light border">{{ $t->metode }}</span></td>
                        <td>{{ format_tanggal($t->tanggal_bayar, true) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
