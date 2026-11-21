@extends('layouts.app')

@section('title', 'Inventaris')
@section('page_title', 'Inventaris')
@section('page_subtitle', 'Kelola aset bersama RT dan peminjaman warga')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card-rt stat-card-inventory stat-red">
            <div class="stat-card-inventory__icon">📦</div>
            <strong>{{ $ringkasan['jenis'] }}</strong>
            <span>Total Jenis Barang</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-rt stat-card-inventory stat-green">
            <div class="stat-card-inventory__icon">✓</div>
            <strong>{{ $ringkasan['baik'] }}</strong>
            <span>Kondisi Baik</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-rt stat-card-inventory stat-orange">
            <div class="stat-card-inventory__icon">!</div>
            <strong>{{ $ringkasan['perbaikan'] }}</strong>
            <span>Perlu Perbaikan</span>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-rt stat-card-inventory stat-blue">
            <div class="stat-card-inventory__icon">⇄</div>
            <strong>{{ $ringkasan['dipinjam'] }}</strong>
            <span>Sedang Dipinjam</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-rt h-100">
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                <div class="section-title mb-0"><span class="dot"></span> Daftar Inventaris</div>
                <button type="button" class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#tambahBarang">+ Tambah Barang</button>
            </div>

            <form method="GET" class="mb-3">
                <input type="search" id="inventarisSearch" class="form-control-rt" placeholder="Cari nama barang atau kategori...">
            </form>

            <div class="table-responsive">
                <table class="table-rt" id="inventarisTable">
                    <thead><tr><th>Nama Barang</th><th>Kategori</th><th>Jumlah</th><th>Tersedia</th><th>Kondisi</th><th>Aksi</th></tr></thead>
                    <tbody>
                    @foreach ($barang as $item)
                        <tr>
                            <td><strong>{{ $item->nama }}</strong></td>
                            <td>{{ $item->kategori }}</td>
                            <td>{{ $item->jumlah_total }} unit</td>
                            <td>{{ $item->jumlah_tersedia }} unit</td>
                            <td>{!! badge_status($item->kondisi) !!}</td>
                            <td>
                                <button type="button" class="btn-rt-outline btn-sm" data-bs-toggle="modal" data-bs-target="#editBarang{{ $item->id }}">✎</button>
                                <form action="{{ route('admin.inventaris.barang.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus barang ini dari inventaris?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-rt-outline btn-sm" style="color:var(--color-danger);">🗑</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @if ($barang->isEmpty())
                <div class="empty-state">Belum ada barang inventaris.</div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-rt h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title mb-0"><span class="dot"></span> Peminjaman</div>
                <a href="{{ route('warga.inventaris') }}" class="btn-rt-primary" style="padding:5px 9px; font-size:12px;">Buka Form Warga</a>
            </div>

            @if ($peminjaman->isEmpty())
                <div class="empty-state">Belum ada transaksi peminjaman.</div>
            @else
                <div class="inventory-loan-list">
                    @foreach ($peminjaman as $pinjam)
                        <div class="inventory-loan">
                            <div class="d-flex justify-content-between gap-2">
                                <strong>{{ $pinjam->nama_peminjam }}</strong>
                                {!! badge_status($pinjam->status) !!}
                            </div>
                            <div class="text-muted-rt mt-1" style="font-size:12px;">
                                {{ $pinjam->barang->nama ?? '-' }} &middot; {{ $pinjam->jumlah }} unit
                            </div>
                            <div class="text-muted-rt" style="font-size:11.5px;">
                                {{ format_tanggal($pinjam->tanggal_pinjam) }} - {{ format_tanggal($pinjam->rencana_kembali) }}
                            </div>

                            <button type="button" class="btn-rt-outline w-100 mt-2" style="padding:5px 8px; font-size:12px;" data-bs-toggle="modal" data-bs-target="#pinjam{{ $pinjam->id }}">
                                Buka Detail
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="tambahBarang" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.inventaris.barang.store') }}" method="POST" class="modal-content inventory-modal">
            @csrf
            <div class="modal-header inventory-modal__header">
                <h5 class="modal-title">Tambah Barang Inventaris</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label-rt">Nama Barang</label><input name="nama" class="form-control-rt" required></div>
                <div class="mb-3"><label class="form-label-rt">Kategori</label><input name="kategori" class="form-control-rt" placeholder="Contoh: Perlengkapan Kegiatan" required></div>
                <div class="row g-3 mb-3">
                    <div class="col-6"><label class="form-label-rt">Jumlah</label><input type="number" name="jumlah_total" min="1" class="form-control-rt" value="1" required></div>
                    <div class="col-6"><label class="form-label-rt">Kondisi</label><select name="kondisi" class="form-control-rt"><option>Baik</option><option>Perlu Perbaikan</option><option>Rusak</option></select></div>
                </div>
                <div><label class="form-label-rt">Deskripsi</label><textarea name="deskripsi" class="form-control-rt" rows="3"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button><button class="btn-rt-primary">Simpan Barang</button></div>
        </form>
    </div>
</div>

@foreach ($barang as $item)
    <div class="modal fade" id="editBarang{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('admin.inventaris.barang.update', $item) }}" method="POST" class="modal-content inventory-modal">
                @csrf
                <div class="modal-header inventory-modal__header"><h5 class="modal-title">Edit {{ $item->nama }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label-rt">Nama Barang</label><input name="nama" value="{{ $item->nama }}" class="form-control-rt" required></div>
                    <div class="mb-3"><label class="form-label-rt">Kategori</label><input name="kategori" value="{{ $item->kategori }}" class="form-control-rt" required></div>
                    <div class="row g-3 mb-3">
                        <div class="col-6"><label class="form-label-rt">Jumlah</label><input type="number" name="jumlah_total" min="1" value="{{ $item->jumlah_total }}" class="form-control-rt" required></div>
                        <div class="col-6"><label class="form-label-rt">Kondisi</label><select name="kondisi" class="form-control-rt">@foreach(['Baik','Perlu Perbaikan','Rusak'] as $k)<option @selected($item->kondisi === $k)>{{ $k }}</option>@endforeach</select></div>
                    </div>
                    <div><label class="form-label-rt">Deskripsi</label><textarea name="deskripsi" class="form-control-rt" rows="3">{{ $item->deskripsi }}</textarea></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button><button class="btn-rt-primary">Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
@endforeach

@foreach ($peminjaman as $pinjam)
    <div class="modal fade" id="pinjam{{ $pinjam->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content inventory-modal">
                <div class="modal-header inventory-modal__header"><h5 class="modal-title">Detail Peminjaman</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-start mb-3"><div><h5 class="mb-1">{{ $pinjam->barang->nama ?? '-' }}</h5><div class="text-muted-rt">{{ $pinjam->nama_peminjam }} &middot; {{ $pinjam->jumlah }} unit</div></div>{!! badge_status($pinjam->status) !!}</div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6"><div class="inventory-info"><span>Tanggal Pinjam</span><strong>{{ format_tanggal($pinjam->tanggal_pinjam) }}</strong></div></div>
                        <div class="col-md-6"><div class="inventory-info"><span>Rencana Kembali</span><strong>{{ format_tanggal($pinjam->rencana_kembali) }}</strong></div></div>
                    </div>
                    <div class="inventory-note"><strong>Keterangan Peminjaman</strong><div class="mt-2">{{ $pinjam->keperluan }}</div></div>
                    @if ($pinjam->foto_pinjam)<a href="{{ asset('storage/'.$pinjam->foto_pinjam) }}" target="_blank" class="d-block mt-3 text-primary">Lihat foto saat dipinjam</a>@endif
                    @if ($pinjam->foto_kembali)<a href="{{ asset('storage/'.$pinjam->foto_kembali) }}" target="_blank" class="d-block mt-2 text-primary">Lihat foto pengembalian</a>@endif

                    @if (in_array($pinjam->status, ['Diajukan', 'Dipinjam']))
                        <form action="{{ route('admin.inventaris.peminjaman.action', $pinjam) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <div class="mb-3"><label class="form-label-rt">Catatan</label><textarea name="catatan" class="form-control-rt" rows="2">{{ $pinjam->catatan }}</textarea></div>
                            @if ($pinjam->status === 'Dipinjam')<div class="mb-3"><label class="form-label-rt">Foto Pengembalian</label><input type="file" name="foto_kembali" class="form-control-rt" accept="image/jpeg,image/png,image/webp"></div>@endif
                            <div class="d-flex justify-content-end gap-2">
                                @if ($pinjam->status === 'Diajukan')
                                    <button name="aksi" value="tolak" class="btn-rt-outline" style="color:var(--color-danger);">Tolak</button>
                                    <button name="aksi" value="setujui" class="btn-rt-primary">Setujui Peminjaman</button>
                                @else
                                    <button name="aksi" value="kembalikan" class="btn-rt-primary">Catat Pengembalian</button>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
<script>
document.getElementById('inventarisSearch')?.addEventListener('input', function () {
    const query = this.value.toLowerCase();
    document.querySelectorAll('#inventarisTable tbody tr').forEach(function (row) {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
