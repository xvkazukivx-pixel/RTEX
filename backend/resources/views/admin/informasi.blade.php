@extends('layouts.app')

@section('title', 'Informasi & Kegiatan')
@section('page_title', 'Informasi & Kegiatan')
@section('page_subtitle', 'Publikasikan informasi dan kelola agenda kegiatan warga')

@section('content')
<div class="row g-4 mb-4">
    <!-- Form Tambah Pengumuman -->
    <div class="col-lg-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Buat Pengumuman Baru</div>
            <form action="{{ route('admin.pengumuman.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Judul Pengumuman</label>
                    <input name="judul" class="form-control-rt" placeholder="Contoh: Jadwal Kerja Bakti Bersama" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Isi Pengumuman</label>
                    <textarea name="isi" class="form-control-rt" rows="4" placeholder="Tulis rincian pengumuman untuk seluruh warga..." required></textarea>
                </div>
                <button type="submit" class="btn-rt-primary">Publikasikan Pengumuman</button>
            </form>
        </div>
    </div>

    <!-- Form Tambah Kegiatan -->
    <div class="col-lg-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Buat Agenda Kegiatan</div>
            <form action="{{ route('admin.kegiatan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Nama Kegiatan</label>
                    <input name="judul" class="form-control-rt" placeholder="Contoh: Senam Sehat Minggu Pagi" required>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label class="form-label-rt">Waktu Mulai</label>
                        <input type="datetime-local" name="mulai_pada" class="form-control-rt" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label-rt">Waktu Selesai (Opsional)</label>
                        <input type="datetime-local" name="selesai_pada" class="form-control-rt">
                    </div>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <label class="form-label-rt">Lokasi</label>
                        <input name="lokasi" class="form-control-rt" placeholder="Contoh: Lapangan RT 05" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label-rt">Kapasitas Peserta</label>
                        <input type="number" min="1" name="kapasitas" class="form-control-rt" placeholder="Kosong = bebas">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Deskripsi Kegiatan</label>
                    <textarea name="deskripsi" class="form-control-rt" rows="2" placeholder="Jelaskan tujuan dan perlengkapan yang perlu dibawa" required></textarea>
                </div>
                <button type="submit" class="btn-rt-accent">Simpan Draft Kegiatan</button>
            </form>
        </div>
    </div>
</div>

<!-- Daftar Pengumuman -->
<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Daftar Pengumuman Warga</div>
    @if ($pengumuman->isEmpty())
        <div class="empty-state">Belum ada pengumuman yang dibuat.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Judul</th><th>Isi</th><th>Tanggal</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach ($pengumuman as $p)
                    <tr>
                        <td><strong>{{ $p->judul }}</strong></td>
                        <td style="max-width:320px;">{{ Str::limit($p->isi, 100) }}</td>
                        <td>{{ format_tanggal($p->dipublikasikan_pada, true) }}</td>
                        <td>{!! badge_status($p->status) !!}</td>
                        <td>
                            @if ($p->status === 'Dipublikasikan')
                                <form action="{{ route('admin.pengumuman.arsip', $p->id) }}" method="POST">
                                    @csrf
                                    <button class="btn-rt-outline" style="font-size:12px; padding:4px 8px;">Arsipkan</button>
                                </form>
                            @else
                                <span class="text-muted-rt">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<!-- Daftar Agenda Kegiatan -->
<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Agenda Kegiatan RT</div>
    @if ($kegiatan->isEmpty())
        <div class="empty-state">Belum ada agenda kegiatan yang dibuat.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Kegiatan</th><th>Waktu &amp; Lokasi</th><th>Peserta</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                @foreach ($kegiatan as $k)
                    <tr>
                        <td>
                            <strong>{{ $k->judul }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">{{ Str::limit($k->deskripsi, 80) }}</div>
                        </td>
                        <td>
                            <div>{{ format_tanggal($k->mulai_pada, true) }}</div>
                            <div class="text-muted-rt" style="font-size:12px;">📍 {{ $k->lokasi }}</div>
                        </td>
                        <td>
                            <b>{{ $k->jumlah_peserta }}</b>
                            @if ($k->kapasitas) / {{ $k->kapasitas }} @endif orang
                        </td>
                        <td>{!! badge_status($k->status) !!}</td>
                        <td>
                            @if ($k->status === 'Draft')
                                <form action="{{ route('admin.kegiatan.kirim', $k->id) }}" method="POST">
                                    @csrf
                                    <button class="btn-rt-primary" style="font-size:12px; padding:5px 10px;">Minta ACC Ketua</button>
                                </form>
                            @elseif ($k->status === 'Dipublikasikan')
                                <form action="{{ route('admin.kegiatan.selesai', $k->id) }}" method="POST">
                                    @csrf
                                    <button class="btn-rt-outline" style="font-size:12px; padding:5px 10px;">Tandai Selesai</button>
                                </form>
                            @else
                                <span class="text-muted-rt">-</span>
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
