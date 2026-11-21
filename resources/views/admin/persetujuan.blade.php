@extends('layouts.app')

@section('title', 'Persetujuan Warga Baru')
@section('page_title', 'Persetujuan Warga Baru')
@section('page_subtitle', 'Tinjau pendaftaran akun warga yang masuk lewat halaman registrasi')

@section('content')
<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Menunggu Persetujuan ({{ $menunggu->count() }})</div>

    @if ($menunggu->isEmpty())
        <div class="empty-state">Tidak ada pendaftaran warga baru yang perlu ditinjau saat ini. 👍</div>
    @else
        @foreach ($menunggu as $w)
            <div class="card-rt mb-3" style="border-color: var(--color-accent);">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                    <div>
                        <div style="font-weight:700; font-size:16px;">{{ $w->nama }}</div>
                        <div class="text-muted-rt" style="font-size:13px; margin-top:4px;">
                            Username: <b>{{ $w->user->username ?? '-' }}</b> ·
                            No. Rumah: {{ $w->no_rumah ?: '-' }} ·
                            Status Hunian: {{ $w->status_hunian }}
                        </div>
                        <div class="text-muted-rt" style="font-size:13px;">
                            Alamat: {{ $w->alamat ?: '-' }} ·
                            WhatsApp: {{ $w->no_hp ?: '-' }} ·
                            Daftar: {{ format_tanggal($w->created_at, true) }}
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <form action="{{ route('admin.persetujuan.action', $w->id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="aksi" value="setujui">
                            <button type="submit" class="btn-rt-primary" style="padding:7px 16px;">Setujui</button>
                        </form>
                        <form action="{{ route('admin.persetujuan.action', $w->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menolak pendaftaran warga ini?');">
                            @csrf
                            <input type="hidden" name="aksi" value="tolak">
                            <button type="submit" class="btn-rt-outline" style="padding:7px 16px; color:var(--color-danger); border-color:#eecfcd;">Tolak</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Riwayat Keputusan Terakhir</div>
    @if ($riwayat->isEmpty())
        <div class="empty-state">Belum ada riwayat persetujuan.</div>
    @else
        <table class="table-rt">
            <thead>
                <tr><th>Nama</th><th>Username</th><th>No. Rumah</th><th>Status</th><th>Tanggal Update</th></tr>
            </thead>
            <tbody>
            @foreach ($riwayat as $r)
                <tr>
                    <td><strong>{{ $r->nama }}</strong></td>
                    <td>{{ $r->user->username ?? '-' }}</td>
                    <td>{{ $r->no_rumah ?: '-' }}</td>
                    <td>{!! badge_status($r->status_pendaftaran) !!}</td>
                    <td>{{ format_tanggal($r->updated_at, true) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
