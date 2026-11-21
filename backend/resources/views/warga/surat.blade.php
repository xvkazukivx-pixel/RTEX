@extends('layouts.app')

@section('title', 'Pengajuan Surat')
@section('page_title', 'Pengajuan Surat')
@section('page_subtitle', 'Ajukan dan pantau permohonan surat administrasi RT')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Buat Pengajuan Baru</div>
            <form action="{{ route('warga.surat.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Jenis Surat</label>
                    <select name="jenis_surat" class="form-control-rt" required>
                        <option value="">— Pilih Jenis Surat —</option>
                        @foreach ($jenisTersedia as $j)
                            <option value="{{ $j }}">{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Keperluan</label>
                    <input name="keperluan" class="form-control-rt" placeholder="Contoh: Pengurusan KTP / Pembukaan Rekening" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan_warga" class="form-control-rt" rows="3" placeholder="Informasi tambahan untuk pengurus RT..."></textarea>
                </div>
                <button type="submit" class="btn-rt-primary w-100">Kirim Pengajuan Surat</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Riwayat Pengajuan Saya ({{ $pengajuan->count() }})</div>
            @if ($pengajuan->isEmpty())
                <div class="empty-state">Belum ada pengajuan surat.</div>
            @else
                <div class="table-responsive">
                    <table class="table-rt">
                        <thead><tr><th>Surat</th><th>Keperluan</th><th>Status</th><th>Aksi</th></tr></thead>
                        <tbody>
                        @foreach ($pengajuan as $s)
                            <tr>
                                <td>
                                    <strong>{{ $s->jenis_surat }}</strong>
                                    <div class="text-muted-rt" style="font-size:11.5px;">{{ format_tanggal($s->created_at, true) }}</div>
                                </td>
                                <td>
                                    <div>{{ $s->keperluan }}</div>
                                    @if ($s->nomor_surat)
                                        <div class="text-muted-rt" style="font-size:11.5px;">No: <code>{{ $s->nomor_surat }}</code></div>
                                    @endif
                                </td>
                                <td>
                                    {!! badge_status($s->status) !!}
                                    @if ($s->status === 'Ditolak' && $s->catatan_ketua)
                                        <div class="text-danger" style="font-size:11px; margin-top:3px;">Alasan: {{ $s->catatan_ketua }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if ($s->status === 'Disetujui')
                                        <a href="{{ route('surat.cetak', $s->id) }}" target="_blank" class="btn-rt-primary" style="font-size:12px; padding:5px 12px;">
                                            🖨️ Cetak
                                        </a>
                                    @else
                                        <span class="text-muted-rt" style="font-size:12px;">Menunggu</span>
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
