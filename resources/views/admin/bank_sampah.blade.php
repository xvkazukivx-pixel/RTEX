@extends('layouts.app')

@section('title', 'Bank Sampah')
@section('page_title', 'Bank Sampah')
@section('page_subtitle', 'Kelola jenis sampah dan validasi setoran warga')

@section('content')
<div class="bank-summary-grid mb-4">
    <div class="bank-summary-card">
        <span>Total Berat Sampah Terkumpul</span>
        <strong>{{ number_format($totalBerat, 2, ',', '.') }} kg</strong>
    </div>
    <div class="bank-summary-card">
        <span>Total Nilai Setoran Diterima</span>
        <strong>{{ format_rupiah($totalNilai) }}</strong>
    </div>
</div>

<div class="d-flex gap-2 flex-wrap justify-content-end mb-4">
    <button type="button" class="btn-rt-outline" data-bs-toggle="modal" data-bs-target="#modalTambahJenis">
        <span aria-hidden="true">＋</span> Jenis Sampah
    </button>
    <button type="button" class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalCatatSetoran">
        <span aria-hidden="true">＋</span> Catat Setoran Sampah
    </button>
</div>

<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Daftar Kategori &amp; Contoh Sampah</div>
    <div class="waste-category-grid">
        @foreach ($kategoriSampah as $kategori => $contoh)
            <div class="waste-category-item">
                <strong>{{ $kategori }}</strong>
                <span>{{ $contoh }}</span>
            </div>
        @endforeach
    </div>
</div>

<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Antrian Validasi Setoran ({{ $antrian->count() }})</div>
    @if ($antrian->isEmpty())
        <div class="empty-state">Tidak ada setoran yang menunggu validasi.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Warga</th><th>Setoran</th><th>Berat &amp; Validasi</th></tr>
                </thead>
                <tbody>
                @foreach ($antrian as $s)
                    <tr>
                        <td>
                            <strong>{{ $s->warga->nama ?? '-' }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">{{ format_tanggal($s->created_at, true) }}</div>
                        </td>
                        <td>
                            <strong>{{ $s->nama_sampah ?: ($s->jenis->nama ?? '-') }}</strong>
                            <div class="text-muted-rt" style="font-size:12px;">Estimasi: {{ number_format((float) $s->berat_estimasi, 2, ',', '.') }} kg</div>
                        </td>
                        <td>
                            <form action="{{ route('admin.bank_sampah.setoran.action', $s->id) }}" method="POST" class="d-flex gap-2 flex-wrap align-items-center">
                                @csrf
                                <input type="number" step="0.01" min="0.01" name="berat_final" value="{{ $s->berat_estimasi }}" class="form-control-rt" style="width:90px;" title="Berat riil timbangan" required>
                                <button name="aksi" value="terima" class="btn-rt-primary" style="padding:6px 12px;">Terima</button>
                                <input name="catatan" class="form-control-rt" placeholder="Alasan tolak" style="width:130px; font-size:12px;">
                                <button name="aksi" value="tolak" class="btn-rt-outline" style="padding:6px 10px; color:var(--color-danger); border-color:#eecfcd;">Tolak</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="card-rt">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Riwayat Penimbangan &amp; Setoran Sampah Warga</div>
    </div>
    @if ($riwayat->isEmpty())
        <div class="empty-state">Belum ada data setoran bank sampah.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead>
                    <tr><th>Warga Penyetor</th><th>Jenis Sampah</th><th>Berat</th><th>Nilai</th><th>Waktu Timbang</th><th>Status</th></tr>
                </thead>
                <tbody>
                @foreach ($riwayat as $setoran)
                    <tr>
                        <td><strong>{{ $setoran->warga->nama ?? '-' }}</strong></td>
                        <td>{{ $setoran->nama_sampah ?: ($setoran->jenis->nama ?? '-') }}</td>
                        <td>{{ number_format((float) ($setoran->berat_final ?: $setoran->berat_estimasi), 2, ',', '.') }} kg</td>
                        <td>{{ $setoran->nilai_setoran !== null ? format_rupiah($setoran->nilai_setoran) : '-' }}</td>
                        <td>{{ $setoran->diproses_pada ? format_tanggal($setoran->diproses_pada, true) : format_tanggal($setoran->created_at, true) }}</td>
                        <td>{!! badge_status($setoran->status) !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="modal fade" id="modalTambahJenis" tabindex="-1" aria-labelledby="modalTambahJenisLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bank-modal">
      <form action="{{ route('admin.bank_sampah.jenis.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahJenisLabel">Tambah Jenis Sampah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label-rt">Nama Jenis Sampah</label>
                <input name="nama" class="form-control-rt" placeholder="Contoh: Kardus" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Kategori</label>
                <select name="kategori" class="form-control-rt" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($kategoriSampah as $kategori => $contoh)
                        <option value="{{ $kategori }}">{{ $kategori }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Contoh Sampah (Opsional)</label>
                <input name="contoh_sampah" maxlength="255" class="form-control-rt" placeholder="Contoh: Kardus, koran, buku bekas">
            </div>
            <div>
                <label class="form-label-rt">Harga per kg (Rp)</label>
                <input type="number" min="0" name="harga_per_kg" class="form-control-rt" placeholder="Contoh: 3000" required>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary">Simpan Jenis</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalCatatSetoran" tabindex="-1" aria-labelledby="modalCatatSetoranLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content bank-modal">
      <form action="{{ route('admin.bank_sampah.setoran.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalCatatSetoranLabel">Catat Setoran Bank Sampah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label-rt">Warga Penyetor</label>
                <select name="warga_id" class="form-control-rt" required>
                    <option value="">Pilih warga</option>
                    @foreach ($daftarWarga as $warga)
                        <option value="{{ $warga->id }}">{{ $warga->nama }}{{ $warga->nik ? ' — NIK tersedia' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Jenis Sampah</label>
                <select name="jenis_id" class="form-control-rt" required>
                    <option value="">Pilih jenis sampah</option>
                    @foreach ($jenis as $j)
                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Berat Sampah (kg)</label>
                <input type="number" min="0.01" step="0.01" name="berat" class="form-control-rt" placeholder="Contoh: 2.5" required>
            </div>
            <div>
                <label class="form-label-rt">Catatan (Opsional)</label>
                <input name="catatan" maxlength="255" class="form-control-rt" placeholder="Contoh: Sampah sudah dipilah">
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary">Simpan Setoran</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection
