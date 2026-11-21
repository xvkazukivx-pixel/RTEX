@extends('layouts.app')

@section('title', 'Persetujuan Surat')
@section('page_title', 'Persetujuan Surat')
@section('page_subtitle', 'Tinjau surat keterangan warga yang telah diverifikasi Bendahara')

@section('content')
<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Daftar Surat untuk Ditinjau ({{ $daftar->count() }})</div>
    @if ($daftar->isEmpty())
        <div class="empty-state">Belum ada surat yang memerlukan persetujuan. 👍</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Warga</th><th>Jenis &amp; Keperluan</th><th>No. Surat</th><th>Status</th><th style="min-width:320px;">Keputusan Ketua RT</th></tr>
                </thead>
                <tbody>
                @foreach ($daftar as $surat)
                    <tr>
                        <td>
                            <strong>{{ $surat->warga->nama ?? '-' }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">Rumah: {{ $surat->warga->no_rumah ?: '-' }}</div>
                        </td>
                        <td>
                            <strong>{{ $surat->jenis_surat }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">Keperluan: {{ $surat->keperluan }}</div>
                            @if ($surat->catatan_admin)
                                <div class="text-muted-rt" style="font-size:12px;">Catatan Admin: {{ $surat->catatan_admin }}</div>
                            @endif
                        </td>
                        <td><code>{{ $surat->nomor_surat ?: '-' }}</code></td>
                        <td>{!! badge_status($surat->status) !!}</td>
                        <td>
                            @if ($surat->status === 'Menunggu Persetujuan')
                                <form action="{{ route('ketua.surat.action', $surat->id) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-center">
                                    @csrf
                                    <input type="text" name="catatan_ketua" placeholder="Catatan/Alasan" class="form-control-rt" style="font-size:12px; width:150px;">
                                    <button name="aksi" value="setujui" class="btn-rt-primary" style="padding:6px 12px; font-size:12px;">Setujui</button>
                                    <button name="aksi" value="tolak" class="btn-rt-outline" style="padding:6px 10px; font-size:12px; color:var(--color-danger); border-color:#eecfcd;" onclick="return confirm('Tolak permohonan surat ini?');">Tolak</button>
                                </form>
                            @elseif ($surat->status === 'Disetujui')
                                <a href="{{ route('surat.cetak', $surat->id) }}" target="_blank" class="btn-rt-outline" style="font-size:12px; padding:6px 12px;">
                                    🖨️ Cetak Surat
                                </a>
                            @else
                                <span class="text-muted-rt" style="font-size:12px;">{{ $surat->catatan_ketua ?: '-' }}</span>
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
