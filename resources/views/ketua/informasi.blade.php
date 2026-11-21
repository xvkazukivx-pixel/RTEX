@extends('layouts.app')

@section('title', 'Informasi & Kegiatan')
@section('page_title', 'Informasi & Kegiatan')
@section('page_subtitle', 'Persetujuan kegiatan RT dan monitoring pengumuman warga')

@section('content')
<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Usulan Kegiatan RT ({{ $kegiatan->count() }})</div>
    @if ($kegiatan->isEmpty())
        <div class="empty-state">Belum ada usulan kegiatan.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Kegiatan</th><th>Waktu &amp; Lokasi</th><th>Peserta</th><th>Status</th><th style="min-width:280px;">Persetujuan Ketua RT</th></tr></thead>
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
                            @if ($k->status === 'Menunggu Persetujuan')
                                <form action="{{ route('ketua.kegiatan.action', $k->id) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-center">
                                    @csrf
                                    <input type="text" name="catatan_ketua" placeholder="Catatan persetujuan" class="form-control-rt" style="font-size:12px; width:140px;">
                                    <button name="aksi" value="setujui" class="btn-rt-primary" style="padding:6px 12px; font-size:12px;">Setujui &amp; Rilis</button>
                                    <button name="aksi" value="tolak" class="btn-rt-outline" style="padding:6px 10px; font-size:12px; color:var(--color-danger); border-color:#eecfcd;" onclick="return confirm('Tolak kegiatan ini?');">Tolak</button>
                                </form>
                            @else
                                <span class="text-muted-rt" style="font-size:12px;">{{ $k->catatan_ketua ?: '-' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Pengumuman Warga Aktif</div>
    @if ($pengumuman->isEmpty())
        <div class="empty-state">Belum ada pengumuman.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Judul</th><th>Isi</th><th>Tanggal Rilis</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($pengumuman as $p)
                    <tr>
                        <td><strong>{{ $p->judul }}</strong></td>
                        <td>{{ Str::limit($p->isi, 100) }}</td>
                        <td>{{ format_tanggal($p->dipublikasikan_pada, true) }}</td>
                        <td>{!! badge_status($p->status) !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
