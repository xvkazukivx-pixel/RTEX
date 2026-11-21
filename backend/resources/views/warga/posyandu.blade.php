@extends('layouts.app')

@section('title', 'Posyandu Keluarga')
@section('page_title', 'Posyandu Keluarga')
@section('page_subtitle', 'Jadwal dan riwayat pemeriksaan tumbuh kembang anak')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card-rt mb-4">
            <div class="section-title"><span class="dot"></span> Tambah Data Anak</div>
            <form action="{{ route('warga.posyandu.anak.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Nama Lengkap Anak</label>
                    <input name="nama" class="form-control-rt" placeholder="Nama balita" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control-rt" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control-rt" required>
                        <option value="">— Pilih —</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <button type="submit" class="btn-rt-primary w-100">Simpan Data Anak</button>
            </form>
        </div>

        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Anak Terdaftar ({{ $anak->count() }})</div>
            @if ($anak->isEmpty())
                <div class="empty-state">Belum mendaftarkan anak.</div>
            @else
                <ul class="list-unstyled mb-0">
                @foreach ($anak as $a)
                    <li class="d-flex justify-content-between align-items-center mb-2 p-2 rounded border" style="background:#f8fafc;">
                        <div>
                            <strong>{{ $a->nama }}</strong>
                            <div class="text-muted-rt" style="font-size:11.5px;">Lahir: {{ format_tanggal($a->tanggal_lahir) }}</div>
                        </div>
                        <span class="badge text-bg-light border">{{ $a->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                    </li>
                @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card-rt mb-4">
            <div class="section-title"><span class="dot"></span> Jadwal Posyandu Mendatang</div>
            @if ($jadwal->isEmpty())
                <div class="empty-state">Belum ada agenda jadwal posyandu mendatang.</div>
            @else
                <div class="table-responsive">
                    <table class="table-rt">
                        <thead><tr><th>Agenda Kegiatan</th><th>Waktu</th><th>Lokasi</th></tr></thead>
                        <tbody>
                        @foreach ($jadwal as $j)
                            <tr>
                                <td>
                                    <strong>{{ $j->judul }}</strong>
                                    <div class="text-muted-rt" style="font-size:12px;">{{ $j->catatan ?: '-' }}</div>
                                </td>
                                <td>{{ format_tanggal($j->tanggal, true) }}</td>
                                <td>📍 {{ $j->lokasi }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Riwayat Pemeriksaan Anak Saya</div>
            @if ($riwayat->isEmpty())
                <div class="empty-state">Belum ada riwayat hasil pemeriksaan posyandu.</div>
            @else
                <div class="table-responsive">
                    <table class="table-rt">
                        <thead><tr><th>Anak</th><th>Tanggal Posyandu</th><th>Berat / Tinggi</th><th>Imunisasi</th></tr></thead>
                        <tbody>
                        @foreach ($riwayat as $p)
                            <tr>
                                <td><strong>{{ $p->anak->nama ?? '-' }}</strong></td>
                                <td>{{ format_tanggal($p->jadwal->tanggal ?? $p->created_at) }}</td>
                                <td>{{ $p->berat_kg ?: '-' }} kg / {{ $p->tinggi_cm ?: '-' }} cm</td>
                                <td>{{ $p->imunisasi ?: '-' }}</td>
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
