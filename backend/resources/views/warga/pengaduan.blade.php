@extends('layouts.app')

@section('title', 'Pengaduan Warga')
@section('page_title', 'Pengaduan')
@section('page_subtitle', 'Sampaikan laporan, keluhan, atau usulan kepada pengurus RT')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Buat Pengaduan Baru</div>
            <form action="{{ route('warga.pengaduan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Kategori</label>
                    <select name="kategori" class="form-control-rt" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach (['Sarana & Prasarana', 'Ketertiban', 'Lingkungan', 'Pelayanan', 'Keamanan', 'Lainnya'] as $kategori)
                            <option value="{{ $kategori }}" @selected(old('kategori') === $kategori)>{{ $kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Judul Pengaduan</label>
                    <input name="judul" class="form-control-rt" value="{{ old('judul') }}" placeholder="Contoh: Pipa saluran air tersumbat" maxlength="150" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Isi Pengaduan</label>
                    <textarea name="deskripsi" class="form-control-rt" rows="5" maxlength="2000" placeholder="Tuliskan lokasi, kronologi, dan rincian pengaduan..." required>{{ old('deskripsi') }}</textarea>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Foto Bukti (Opsional)</label>
                    <input type="file" name="foto" class="form-control-rt" accept="image/jpeg,image/png,image/webp">
                    <div class="text-muted-rt mt-1" style="font-size:11.5px;">JPG, PNG, atau WEBP. Maksimal 5 MB.</div>
                </div>
                <button type="submit" class="btn-rt-primary w-100">Kirim Pengaduan</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Riwayat Pengaduan Saya ({{ $pengaduan->count() }})</div>
            @if ($pengaduan->isEmpty())
                <div class="empty-state">Belum ada pengaduan yang Anda kirim.</div>
            @else
                <div class="table-responsive">
                    <table class="table-rt">
                        <thead><tr><th>Pengaduan</th><th>Kategori</th><th>Status</th></tr></thead>
                        <tbody>
                        @foreach ($pengaduan as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->judul }}</strong>
                                    <div class="text-muted-rt" style="font-size:11.5px;">{{ format_tanggal($item->created_at, true) }}</div>
                                    @if ($item->foto)
                                        <a href="{{ asset('storage/'.$item->foto) }}" target="_blank" class="text-primary" style="font-size:11.5px;">Lihat foto bukti</a>
                                    @endif
                                </td>
                                <td>{{ $item->kategori }}</td>
                                <td>
                                    {!! badge_status($item->status) !!}
                                    @if ($item->tanggapan)
                                        <div class="text-muted-rt mt-1" style="font-size:11.5px;">{{ $item->tanggapan }}</div>
                                    @endif
                                </td>
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
