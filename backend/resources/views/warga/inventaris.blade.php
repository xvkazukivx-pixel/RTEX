@extends('layouts.app')

@section('title', 'Inventaris Warga')
@section('page_title', 'Inventaris')
@section('page_subtitle', 'Ajukan peminjaman dan pantau barang milik RT')

@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-rt">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title mb-0"><span class="dot"></span> Barang Tersedia</div>
                <button type="button" class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#pinjamBarang">+ Ajukan Peminjaman</button>
            </div>
            @if ($barang->isEmpty())
                <div class="empty-state">Belum ada barang yang dapat dipinjam.</div>
            @else
                <div class="row g-3">
                    @foreach ($barang as $item)
                        <div class="col-md-6">
                            <div class="inventory-warga-card">
                                <div class="inventory-warga-card__icon">📦</div>
                                <div><strong>{{ $item->nama }}</strong><div class="text-muted-rt">{{ $item->kategori }}</div></div>
                                <span class="badge text-bg-success">{{ $item->jumlah_tersedia }} tersedia</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Peminjaman Saya</div>
            @if ($peminjaman->isEmpty())
                <div class="empty-state">Belum ada permintaan peminjaman.</div>
            @else
                @foreach ($peminjaman as $pinjam)
                    <div class="inventory-loan mb-3">
                        <div class="d-flex justify-content-between gap-2"><strong>{{ $pinjam->barang->nama ?? '-' }}</strong>{!! badge_status($pinjam->status) !!}</div>
                        <div class="text-muted-rt mt-1" style="font-size:12px;">{{ $pinjam->jumlah }} unit &middot; {{ format_tanggal($pinjam->tanggal_pinjam) }} s/d {{ format_tanggal($pinjam->rencana_kembali) }}</div>
                        @if ($pinjam->catatan)<div class="mt-2" style="font-size:12.5px;">Catatan: {{ $pinjam->catatan }}</div>@endif
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="pinjamBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('warga.inventaris.pinjam.store') }}" method="POST" enctype="multipart/form-data" class="modal-content inventory-modal">
            @csrf
            <div class="modal-header inventory-modal__header"><h5 class="modal-title">Ajukan Peminjaman Barang</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label-rt">Barang</label><select name="barang_id" class="form-control-rt" required><option value="">-- Pilih Barang --</option>@foreach($barang as $item)<option value="{{ $item->id }}">{{ $item->nama }} ({{ $item->jumlah_tersedia }} tersedia)</option>@endforeach</select></div>
                <div class="mb-3"><label class="form-label-rt">Jumlah Dipinjam</label><input type="number" name="jumlah" min="1" class="form-control-rt" value="1" required></div>
                <div class="row g-3 mb-3"><div class="col-6"><label class="form-label-rt">Tanggal Pinjam</label><input type="date" name="tanggal_pinjam" class="form-control-rt" value="{{ date('Y-m-d') }}" required></div><div class="col-6"><label class="form-label-rt">Rencana Kembali</label><input type="date" name="rencana_kembali" class="form-control-rt" required></div></div>
                <div class="mb-3"><label class="form-label-rt">Keperluan</label><textarea name="keperluan" class="form-control-rt" rows="3" maxlength="1000" required></textarea></div>
                <div><label class="form-label-rt">Foto Saat Dipinjam (Opsional)</label><input type="file" name="foto_pinjam" class="form-control-rt" accept="image/jpeg,image/png,image/webp"></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button><button class="btn-rt-primary">Kirim Permintaan</button></div>
        </form>
    </div>
</div>
@endsection
