@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page_title', 'Laporan Keuangan')
@section('page_subtitle', 'Rekap pemasukan kas per periode')

@section('content')
<div class="card-rt mb-4">
    <form method="GET" action="{{ route('admin.laporan') }}" class="d-flex align-items-end gap-3 flex-wrap">
        <div>
            <label class="form-label-rt">Periode</label>
            <select name="periode" class="form-control-rt" onchange="this.form.submit()">
                @foreach ($daftarPeriode as $p)
                    <option value="{{ $p }}" {{ $p === $periode ? 'selected' : '' }}>{{ periode_label($p) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button type="submit" class="btn-rt-primary">Tampilkan</button>
            <a href="{{ route('admin.laporan', ['periode' => $periode, 'export' => 'csv']) }}" class="btn-rt-outline ms-2">📥 Export CSV</a>
        </div>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Masuk Periode Ini</div>
            <div class="stat-value">{{ format_rupiah($totalMasuk) }}</div>
            <div class="stat-hint">Transaksi terkonfirmasi</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card accent">
            <div class="stat-label">Warga Lunas</div>
            <div class="stat-value">{{ $totalLunas }} / {{ $totalTagihan }}</div>
            <div class="stat-hint">Dari total tagihan yang diterbitkan</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card warn">
            <div class="stat-label">Belum Lunas</div>
            <div class="stat-value">{{ $totalTagihan - $totalLunas }}</div>
            <div class="stat-hint">Warga yang masih menunggak</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Tren Pemasukan 6 Bulan Terakhir</div>
            @php
                $maksPemasukan = max(array_merge($pemasukanGrafik, [1]));
            @endphp
            <div class="d-flex align-items-end gap-2 overflow-auto px-1" style="height:230px;">
                @foreach ($pemasukanGrafik as $index => $nilai)
                    @php
                        $tinggi = round(($nilai / $maksPemasukan) * 180);
                    @endphp
                    <div class="d-flex flex-column align-items-center justify-content-end flex-grow-1" style="min-width:72px; height:210px;">
                        <div class="text-muted-rt mb-1" style="font-size:11px; white-space:nowrap;">{{ format_rupiah($nilai) }}</div>
                        <div title="{{ $periodeGrafik[$index] }}: {{ format_rupiah($nilai) }}"
                            style="width:52%; max-width:58px; height:{{ max($tinggi, 4) }}px; border-radius:8px 8px 3px 3px; background:#3f7c65;"></div>
                        <div class="text-muted-rt mt-2" style="font-size:11px; text-align:center;">{{ $periodeGrafik[$index] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Persentase Kepatuhan</div>
            @php
                $persenLunas = $totalTagihan > 0 ? round(($totalLunas / $totalTagihan) * 100) : 0;
            @endphp
            <div class="d-flex flex-column align-items-center justify-content-center" style="min-height:230px;">
                <div class="d-flex align-items-center justify-content-center rounded-circle"
                    style="width:150px; height:150px; background:conic-gradient(#3f7c65 0 {{ $persenLunas }}%, #e8edeb {{ $persenLunas }}% 100%);">
                    <div class="d-flex align-items-center justify-content-center rounded-circle bg-white flex-column"
                        style="width:112px; height:112px;">
                        <strong style="font-size:26px; color:#2f5f4d;">{{ $persenLunas }}%</strong>
                        <span class="text-muted-rt" style="font-size:11px;">Lunas</span>
                    </div>
                </div>
                <div class="d-flex gap-3 mt-3" style="font-size:12px;">
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#3f7c65;"></span> Lunas {{ $totalLunas }}</span>
                    <span><span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:#e8edeb;"></span> Belum {{ $totalTagihan - $totalLunas }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Rincian Transaksi Masuk — {{ periode_label($periode) }}</div>
    @if ($transaksiPeriode->isEmpty())
        <div class="empty-state">Belum ada transaksi terkonfirmasi untuk periode ini.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Nama Warga</th><th>No. Rumah</th><th>Jumlah</th><th>Metode</th><th>Tanggal Bayar</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                @foreach ($transaksiPeriode as $t)
                    <tr>
                        <td><strong>{{ $t->warga->nama ?? '-' }}</strong></td>
                        <td>{{ $t->warga->no_rumah ?: '-' }}</td>
                        <td>{{ format_rupiah($t->jumlah_bayar) }}</td>
                        <td><span class="badge text-bg-light border">{{ $t->metode }}</span></td>
                        <td>{{ format_tanggal($t->tanggal_bayar, true) }}</td>
                        <td>{{ $t->keterangan ?: '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
