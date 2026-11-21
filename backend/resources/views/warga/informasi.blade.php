@extends('layouts.app')

@section('title', 'Informasi & Agenda')
@section('page_title', 'Informasi & Kegiatan')
@section('page_subtitle', 'Pengumuman warga dan agenda kegiatan lingkungan RT')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Pengumuman Terbaru</div>
            @if ($pengumuman->isEmpty())
                <div class="empty-state">Belum ada pengumuman warga saat ini.</div>
            @else
                <div class="d-flex flex-column gap-3">
                @foreach ($pengumuman as $p)
                    <div class="p-3 rounded border" style="background:#f8fafc;">
                        <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2 mb-1">
                            <strong style="font-size:15px;">{{ $p->judul }}</strong>
                            <span class="text-muted-rt" style="font-size:11.5px;">{{ format_tanggal($p->dipublikasikan_pada, true) }}</span>
                        </div>
                        <div style="font-size:13.5px; line-height:1.6; white-space:pre-line;">{{ $p->isi }}</div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card-rt h-100">
            <div class="section-title"><span class="dot"></span> Agenda Kegiatan Mendatang</div>
            @if ($kegiatan->isEmpty())
                <div class="empty-state">Belum ada agenda kegiatan mendatang.</div>
            @else
                <div class="d-flex flex-column gap-3">
                @foreach ($kegiatan as $k)
                    @php
                        $sudahDaftar = in_array($k->id, $sayaTerdaftar, true);
                        $penuh = $k->kapasitas && $k->jumlah_peserta >= $k->kapasitas;
                    @endphp
                    <div class="p-3 rounded border" style="background:#fff;">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                            <div>
                                <strong style="font-size:15px;">{{ $k->judul }}</strong>
                                <div class="text-muted-rt" style="font-size:12px;">📍 {{ $k->lokasi }} · 📅 {{ format_tanggal($k->mulai_pada, true) }}</div>
                            </div>
                            <span class="badge text-bg-light border">
                                {{ $k->jumlah_peserta }}@if($k->kapasitas)/{{ $k->kapasitas }}@endif peserta
                            </span>
                        </div>
                        <div class="text-muted-rt mb-3" style="font-size:13px;">{{ $k->deskripsi }}</div>
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            @if ($sudahDaftar)
                                <span class="badge text-bg-success">✓ Anda Sudah Terdaftar</span>
                                <form action="{{ route('warga.kegiatan.batal', $k->id) }}" method="POST">
                                    @csrf
                                    <button class="btn-rt-outline" style="font-size:12px; padding:4px 10px; color:var(--color-danger); border-color:#eecfcd;">Batalkan</button>
                                </form>
                            @elseif ($penuh)
                                <span class="badge text-bg-secondary">Kuota Penuh</span>
                            @else
                                <form action="{{ route('warga.kegiatan.daftar', $k->id) }}" method="POST">
                                    @csrf
                                    <button class="btn-rt-primary" style="font-size:12px; padding:6px 14px;">Daftar Ikut Serta</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
