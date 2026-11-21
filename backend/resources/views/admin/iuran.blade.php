@extends('layouts.app')

@section('title', 'Manajemen Iuran')
@section('page_title', 'Manajemen Iuran')
@section('page_subtitle', 'Atur jenis & nominal iuran bulanan warga')

@section('content')
<div class="stat-card accent mb-4" style="max-width:320px;">
    <div class="stat-label">Total Tagihan / Bulan / Warga</div>
    <div class="stat-value">{{ format_rupiah($totalAktif) }}</div>
    <div class="stat-hint">Jumlah seluruh jenis iuran yang berstatus aktif</div>
</div>

<div class="card-rt mb-4">
    <div class="section-title"><span class="dot"></span> Grafik Nominal Iuran</div>
    @php
        $maksIuran = max($grafikIuran->max('nominal'), 1);
    @endphp
    @if ($grafikIuran->isEmpty())
        <div class="empty-state">Belum ada data iuran untuk ditampilkan.</div>
    @else
        <div class="d-grid gap-3">
            @foreach ($grafikIuran as $item)
                @php
                    $lebar = $maksIuran > 0 ? round(($item['nominal'] / $maksIuran) * 100) : 0;
                @endphp
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:13px;">
                        <span>
                            {{ $item['nama'] }}
                            @if (! $item['aktif'])
                                <span class="text-muted-rt">(Nonaktif)</span>
                            @endif
                        </span>
                        <strong>{{ format_rupiah($item['nominal']) }}</strong>
                    </div>
                    <div class="rounded-pill overflow-hidden" style="height:12px; background:#edf1ee;">
                        <div style="width:{{ $item['aktif'] ? max($lebar, 3) : 0 }}%; height:100%; background:{{ $item['aktif'] ? '#3f7c65' : '#adb5b0' }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="card-rt">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div class="section-title mb-0"><span class="dot"></span> Jenis Iuran</div>
        <button class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Jenis Iuran</button>
    </div>

    @if ($daftarIuran->isEmpty())
        <div class="empty-state">Belum ada jenis iuran. Klik "Tambah Jenis Iuran" untuk membuat.</div>
    @else
    <table class="table-rt">
        <thead>
            <tr><th>Nama Iuran</th><th>Nominal</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        @foreach ($daftarIuran as $i)
            <tr>
                <td><strong>{{ $i->nama_iuran }}</strong></td>
                <td>{{ format_rupiah($i->nominal) }}</td>
                <td>
                    @if ($i->status_aktif)
                        <span class="badge-status text-bg-success">Aktif</span>
                    @else
                        <span class="badge-status text-bg-secondary">Nonaktif</span>
                    @endif
                </td>
                <td class="text-nowrap text-end">
                    <button class="btn-rt-outline btn-sm-edit btn-edit-iuran"
                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                        data-id="{{ $i->id }}"
                        data-nama="{{ $i->nama_iuran }}"
                        data-nominal="{{ (int)$i->nominal }}"
                        data-aktif="{{ $i->status_aktif ? '1' : '0' }}">Ubah</button>
                    <form action="{{ route('admin.iuran.destroy', $i->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus jenis iuran ini?');">
                        @csrf
                        <button type="submit" class="btn-rt-outline btn-sm-edit" style="color:var(--color-danger); border-color:#eecfcd;">Hapus</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:14px;">
      <form action="{{ route('admin.iuran.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" style="font-family:var(--font-display);">Tambah Jenis Iuran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label-rt">Nama Iuran</label>
                <input type="text" name="nama_iuran" class="form-control-rt" placeholder="Contoh: Iuran Kebersihan" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Nominal (Rp)</label>
                <input type="number" name="nominal" class="form-control-rt" placeholder="Contoh: 25000" min="0" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="status_aktif" id="aktifTambah" checked>
                <label class="form-check-label" for="aktifTambah">Aktifkan sekarang</label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:14px;">
      <form id="formEditIuran" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" style="font-family:var(--font-display);">Ubah Jenis Iuran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label-rt">Nama Iuran</label>
                <input type="text" name="nama_iuran" id="editNama" class="form-control-rt" required>
            </div>
            <div class="mb-3">
                <label class="form-label-rt">Nominal (Rp)</label>
                <input type="number" name="nominal" id="editNominal" class="form-control-rt" min="0" required>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="status_aktif" id="editAktif">
                <label class="form-check-label" for="editAktif">Status Aktif</label>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn-rt-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.btn-edit-iuran').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        document.getElementById('formEditIuran').action = "{{ url('admin/iuran/update') }}/" + id;
        document.getElementById('editNama').value = btn.dataset.nama || '';
        document.getElementById('editNominal').value = btn.dataset.nominal || '';
        document.getElementById('editAktif').checked = btn.dataset.aktif === '1';
    });
});
</script>
@endpush
