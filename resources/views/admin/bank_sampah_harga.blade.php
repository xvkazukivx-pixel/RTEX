<div>
    <!-- We must ship. - Taylor Otwell -->
</div>
@extends('layouts.app')

@section('title', 'Tarif Bank Sampah')
@section('page_title', 'Tarif Bank Sampah')
@section('page_subtitle', 'Atur harga per kg dan contoh sampah yang diterima')

@section('content')
<div class="bank-hero mb-4">
    <div class="bank-hero-copy">
        <span class="bank-hero-badge">♻️ Pengaturan Bendahara</span>
        <h2>Tarif Sampah</h2>
        <p>Tarif yang diatur di sini dipakai untuk menghitung nilai setoran warga.</p>
    </div>
</div>

<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Tambah Jenis Sampah dan Tarif</div>
    <form action="{{ route('admin.bank_sampah.harga.store') }}" method="POST" class="row g-3">
        @csrf
        <div class="col-md-3">
            <label for="nama" class="form-label-rt">Nama Jenis</label>
            <input id="nama" name="nama" class="form-control-rt" placeholder="Contoh: Sampah organik" required>
        </div>
        <div class="col-md-3">
            <label for="kategori" class="form-label-rt">Kategori</label>
            <select id="kategori" name="kategori" class="form-control-rt" required>
                <option value="">Pilih kategori</option>
                @foreach ($kategoriSampah as $kategori => $contoh)
                    <option value="{{ $kategori }}">{{ $kategori }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="contoh_sampah" class="form-label-rt">Contoh Sampah</label>
            <input id="contoh_sampah" name="contoh_sampah" maxlength="255" class="form-control-rt" placeholder="Opsional">
        </div>
        <div class="col-md-2">
            <label for="harga_per_kg" class="form-label-rt">Harga per kg (Rp)</label>
            <input id="harga_per_kg" type="number" min="0" step="0.01" name="harga_per_kg" class="form-control-rt" placeholder="Contoh: 3000" required>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn-rt-primary w-100">Tambah</button>
        </div>
    </form>
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Daftar Tarif per Jenis Sampah</div>
    @if ($jenis->isEmpty())
        <div class="empty-state">Belum ada jenis sampah. Tambahkan jenis dan tarif di atas.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Kategori</th><th>Jenis Sampah</th><th>Contoh</th><th>Harga per kg</th><th></th></tr>
                </thead>
                <tbody>
                @foreach ($jenis as $item)
                    <tr>
                        <td>{{ $item->kategori ?: 'Lainnya' }}</td>
                        <td><strong>{{ $item->nama }}</strong></td>
                        <td>{{ $item->contoh_sampah ?: '-' }}</td>
                        <td colspan="2">
                            <form action="{{ route('admin.bank_sampah.harga.update', $item->id) }}" method="POST" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    name="harga_per_kg"
                                    value="{{ number_format((float) $item->harga_per_kg, 2, '.', '') }}"
                                    class="form-control-rt"
                                    style="max-width:180px;"
                                    aria-label="Harga per kg {{ $item->nama }}"
                                    required
                                >
                                <button type="submit" class="btn-rt-outline">Simpan Harga</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
