@extends('layouts.app')

@section('title', 'Posyandu')
@section('page_title', 'Posyandu')
@section('page_subtitle', 'Atur jadwal dan catat pemeriksaan anak balita')

@section('content')
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card-rt mb-4">
            <div class="section-title"><span class="dot"></span> Buat Jadwal Posyandu</div>
            <form action="{{ route('admin.posyandu.jadwal.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Nama Kegiatan / Judul</label>
                    <input name="judul" class="form-control-rt" placeholder="Contoh: Posyandu Balita & Imunisasi Rutin" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Waktu Pelaksanaan</label>
                    <input type="datetime-local" name="tanggal" class="form-control-rt" required>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Lokasi</label>
                    <input name="lokasi" class="form-control-rt" placeholder="Contoh: Balai Warga RT 05" required>
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Catatan Tambahan</label>
                    <textarea name="catatan" class="form-control-rt" rows="2" placeholder="Catatan untuk warga"></textarea>
                </div>
                <button type="submit" class="btn-rt-primary w-100">Simpan Jadwal</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card-rt">
            <div class="section-title"><span class="dot"></span> Catat Pemeriksaan Anak</div>
            <form action="{{ route('admin.posyandu.pemeriksaan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label-rt">Pilih Jadwal Posyandu</label>
                    <select name="jadwal_id" class="form-control-rt" required>
                        <option value="">— Pilih Jadwal —</option>
                        @foreach ($jadwal as $j)
                            <option value="{{ $j->id }}">{{ format_tanggal($j->tanggal, true) }} — {{ $j->judul }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Pilih Anak</label>
                    <select name="anak_id" class="form-control-rt" required>
                        <option value="">— Pilih Anak —</option>
                        @foreach ($anak as $a)
                            <option value="{{ $a->id }}">{{ $a->nama }} (Orang tua: {{ $a->warga->nama ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col">
                        <label class="form-label-rt">Berat (kg)</label>
                        <input type="number" step="0.01" name="berat_kg" class="form-control-rt" placeholder="Contoh: 12.5">
                    </div>
                    <div class="col">
                        <label class="form-label-rt">Tinggi (cm)</label>
                        <input type="number" step="0.01" name="tinggi_cm" class="form-control-rt" placeholder="Contoh: 85.0">
                    </div>
                    <div class="col">
                        <label class="form-label-rt">Lingkar Kepala (cm)</label>
                        <input type="number" step="0.01" name="lingkar_kepala_cm" class="form-control-rt" placeholder="Contoh: 46.5">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label-rt">Imunisasi yang Diberikan</label>
                    <input name="imunisasi" class="form-control-rt" placeholder="Contoh: Polio 3, Campak">
                </div>
                <div class="mb-4">
                    <label class="form-label-rt">Catatan / Saran Petugas</label>
                    <input name="catatan" class="form-control-rt" placeholder="Kondisi kesehatan balita">
                </div>
                <button type="submit" class="btn-rt-primary">Simpan Pemeriksaan</button>
            </form>
        </div>
    </div>
</div>
@endsection
