@extends('layouts.app')

@section('title', 'Pengaduan Warga')
@section('page_title', 'Pengaduan')
@section('page_subtitle', 'Kelola pengaduan dan tindak lanjut dari warga')

@section('content')
<div class="card-rt">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="section-title mb-0"><span class="dot"></span> Pengaduan Warga ({{ $pengaduan->count() }})</div>
    </div>

    @if ($pengaduan->isEmpty())
        <div class="empty-state">Belum ada pengaduan dari warga.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama Warga</th>
                        <th>Kategori</th>
                        <th>Judul</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($pengaduan as $item)
                    <tr>
                        <td>{{ format_tanggal($item->created_at, true) }}</td>
                        <td><strong>{{ $item->warga->nama ?? '-' }}</strong></td>
                        <td>{{ $item->kategori }}</td>
                        <td>{{ $item->judul }}</td>
                        <td>{!! badge_status($item->status) !!}</td>
                        <td>
                            <button type="button" class="btn-rt-primary" style="padding:5px 10px; font-size:12px;" data-bs-toggle="modal" data-bs-target="#pengaduan-{{ $item->id }}">
                                Detail
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@foreach ($pengaduan as $item)
    <div class="modal fade" id="pengaduan-{{ $item->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content" style="border:0; border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background:var(--color-primary); color:#fff;">
                    <h5 class="modal-title">Detail Pengaduan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex justify-content-between gap-3 mb-3">
                        <div>
                            <h5 class="mb-1">{{ $item->judul }}</h5>
                            <div class="text-muted-rt">{{ $item->warga->nama ?? '-' }} &middot; {{ $item->kategori }} &middot; {{ format_tanggal($item->created_at, true) }}</div>
                        </div>
                        {!! badge_status($item->status) !!}
                    </div>

                    <p class="mb-3">{{ $item->deskripsi }}</p>

                    @if ($item->foto)
                        <div class="mb-3">
                            <strong style="font-size:13px;">Foto Bukti Pengaduan</strong>
                            <div class="mt-2">
                                <a href="{{ asset('storage/'.$item->foto) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$item->foto) }}" alt="Foto pengaduan" style="max-width:180px; max-height:220px; object-fit:cover; border-radius:8px; border:1px solid #e6e6e6;">
                                </a>
                            </div>
                        </div>
                    @endif

                    <hr>
                    <strong style="font-size:13px;">Riwayat Proses Pengaduan</strong>
                    <div class="mt-3">
                        <div class="border-start border-2 ps-3 mb-3">
                            <strong>{{ $item->status }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">{{ format_tanggal($item->updated_at, true) }}</div>
                            @if ($item->tanggapan)
                                <div class="mt-1">{{ $item->tanggapan }}</div>
                            @else
                                <div class="text-muted-rt mt-1">Belum ada tanggapan.</div>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('admin.pengaduan.action', $item) }}" method="POST" class="mt-3">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label-rt">Status</label>
                                <select name="status" class="form-control-rt" required>
                                    @foreach (['Diajukan', 'Diproses', 'Diterima', 'Ditolak', 'Selesai'] as $status)
                                        <option value="{{ $status }}" @selected($item->status === $status)>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label-rt">Tanggapan / Catatan</label>
                                <textarea name="tanggapan" class="form-control-rt" rows="2" maxlength="1000">{{ $item->tanggapan }}</textarea>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn-rt-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
@endsection
