@extends('layouts.app')

@section('title', 'Kelola Warga')
@section('page_title', 'Kelola Warga')
@section('page_subtitle', 'Data warga per kartu keluarga, termasuk warga kontrak/tidak tetap')

@section('content')
@php
    $totalWargaDalamKeluarga = $daftarWarga->sum(fn ($kepala) => 1 + $kepala->anggotaKeluarga->count());
@endphp
<div class="card-rt family-management">
    <div class="family-management__heading">
        <div>
            <div class="section-title mb-1"><span class="dot"></span>Data Kartu Keluarga</div>
            <p class="text-muted-rt mb-0">Kelola warga tetap dan kontrak/tidak tetap dalam satu daftar per rumah.</p>
        </div>
        <button class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">+ Tambah Keluarga</button>
    </div>

    <div class="family-summary-grid">
        <article class="family-summary-card family-summary-card--households">
            <span class="family-summary-card__icon" aria-hidden="true">⌂</span>
            <div><strong>{{ $daftarWarga->count() }}</strong><span>Total KK</span></div>
        </article>
        <article class="family-summary-card family-summary-card--residents">
            <span class="family-summary-card__icon" aria-hidden="true">♧</span>
            <div><strong>{{ $totalWargaDalamKeluarga }}</strong><span>Total warga dalam KK</span></div>
        </article>
    </div>

    <div class="family-toolbar">
        <select id="filterStatusHunian" class="form-control-rt" aria-label="Filter status hunian">
            <option value="">Semua Status Hunian</option>
            <option value="Tetap">Tetap</option>
            <option value="Kontrak">Kontrak / Tidak Tetap</option>
        </select>
        <input id="cariKeluarga" type="search" class="form-control-rt" placeholder="Cari nama, NIK, atau nomor rumah..." aria-label="Cari warga atau rumah">
    </div>

    @if ($daftarWarga->isEmpty())
        <div class="empty-state">Belum ada data keluarga. Klik "Tambah Keluarga" untuk mulai mendata.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt family-directory-table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>NIK</th>
                        <th>Kepala Keluarga</th>
                        <th>Rumah / Blok</th>
                        <th>Status Rumah</th>
                        <th>Jumlah Warga</th>
                        <th>Status Warga</th>
                        <th>Tgl Input</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($daftarWarga as $kepala)
                        @php
                            $wargaRumah = collect([$kepala])->concat($kepala->anggotaKeluarga);
                            $dataPencarian = $wargaRumah
                                ->map(fn ($warga) => implode(' ', [$warga->nama, $warga->nik, $warga->no_rumah, $warga->alamat]))
                                ->implode(' ');
                            $jumlahWargaRumah = 1 + $kepala->anggotaKeluarga->count();
                            $detailId = 'family-details-'.$kepala->id;
                        @endphp
                        <tr
                            data-household-row
                            data-household-id="{{ $kepala->id }}"
                            data-status-hunian="{{ $kepala->status_hunian }}"
                            data-search="{{ $dataPencarian }}"
                        >
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $kepala->nik ?: 'Belum diisi' }}</td>
                            <td><strong>{{ $kepala->nama }}</strong></td>
                            <td>{{ $kepala->no_rumah ?: 'Belum ditentukan' }}</td>
                            <td>
                                @if ($kepala->status_hunian === 'Kontrak')
                                    <span class="family-badge family-badge--contract">Kontrak / Tidak Tetap</span>
                                @else
                                    <span class="family-badge family-badge--permanent">Tetap</span>
                                @endif
                            </td>
                            <td><span class="family-count-badge">{{ $jumlahWargaRumah }} orang</span></td>
                            <td>
                                @if ($kepala->status_aktif)
                                    <span class="family-badge family-badge--active">Aktif</span>
                                @else
                                    <span class="family-badge family-badge--inactive">Nonaktif</span>
                                @endif
                            </td>
                            <td>{{ $kepala->created_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-nowrap">
                                <button
                                    type="button"
                                    class="btn-rt-outline family-toggle"
                                    data-family-toggle
                                    data-target="{{ $detailId }}"
                                    aria-expanded="false"
                                    aria-controls="{{ $detailId }}"
                                >Anggota ({{ $jumlahWargaRumah }})</button>
                                <button type="button" class="btn-rt-outline btn-sm-edit btn-edit-warga"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit"
                                    data-id="{{ $kepala->id }}"
                                    data-nama="{{ $kepala->nama }}"
                                    data-nik="{{ $kepala->nik }}"
                                    data-norumah="{{ $kepala->no_rumah }}"
                                    data-alamat="{{ $kepala->alamat }}"
                                    data-nohp="{{ $kepala->no_hp }}"
                                    data-hunian="{{ $kepala->status_hunian }}"
                                    data-aktif="{{ $kepala->status_aktif ? '1' : '0' }}"
                                    data-ishead="1">Ubah</button>
                                <form action="{{ route('admin.warga.destroy', $kepala->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data warga ini?');">
                                    @csrf
                                    <button type="submit" class="btn-rt-outline btn-sm-edit family-delete">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <tr data-family-detail="{{ $kepala->id }}" id="{{ $detailId }}" hidden>
                            <td colspan="9" class="family-details-cell">
                                <div class="family-details-heading">
                                    <strong>Anggota di rumah {{ $kepala->no_rumah ?: 'belum ditentukan' }}</strong>
                                    <button type="button" class="btn-rt-outline btn-tambah-anggota"
                                        data-bs-toggle="modal" data-bs-target="#modalTambahAnggota"
                                        data-id="{{ $kepala->id }}"
                                        {{ !$kepala->no_rumah || $kepala->anggotaKeluarga->count() >= 4 ? 'disabled' : '' }}>
                                        + Tambah Anggota
                                    </button>
                                </div>
                                @if ($kepala->anggotaKeluarga->isEmpty())
                                    <p class="text-muted-rt mb-0">Belum ada anggota tambahan di rumah ini.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table-rt family-member-table">
                                            <thead>
                                                <tr><th>Nama Warga</th><th>NIK</th><th>No. HP</th><th>Status</th><th>Aksi</th></tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($kepala->anggotaKeluarga as $anggota)
                                                    <tr>
                                                        <td><strong>{{ $anggota->nama }}</strong></td>
                                                        <td>{{ $anggota->nik ?: 'Belum diisi' }}</td>
                                                        <td>{{ $anggota->no_hp ?: '-' }}</td>
                                                        <td>
                                                            @if ($anggota->status_aktif)
                                                                <span class="family-badge family-badge--active">Aktif</span>
                                                            @else
                                                                <span class="family-badge family-badge--inactive">Nonaktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-nowrap">
                                                            <button type="button" class="btn-rt-outline btn-sm-edit btn-edit-warga"
                                                                data-bs-toggle="modal" data-bs-target="#modalEdit"
                                                                data-id="{{ $anggota->id }}"
                                                                data-nama="{{ $anggota->nama }}"
                                                                data-nik="{{ $anggota->nik }}"
                                                                data-norumah="{{ $anggota->no_rumah }}"
                                                                data-alamat="{{ $anggota->alamat }}"
                                                                data-nohp="{{ $anggota->no_hp }}"
                                                                data-hunian="{{ $anggota->status_hunian }}"
                                                                data-aktif="{{ $anggota->status_aktif ? '1' : '0' }}"
                                                                data-ishead="0">Ubah</button>
                                                            <form action="{{ route('admin.warga.destroy', $anggota->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus data warga ini?');">
                                                                @csrf
                                                                <button type="submit" class="btn-rt-outline btn-sm-edit family-delete">Hapus</button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="keluargaTidakDitemukan" class="empty-state" hidden>Tidak ada keluarga yang cocok dengan pencarian atau filter.</div>
    @endif
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:14px;">
            <form action="{{ route('admin.warga.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="font-family:var(--font-display);">Tambah Keluarga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <h6 class="fw-semibold mb-3">Data Kepala Keluarga</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-rt">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control-rt" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-rt">NIK (16 digit)</label>
                            <input type="text" name="nik" class="form-control-rt" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-rt">No. Rumah</label>
                            <input type="text" name="no_rumah" class="form-control-rt" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-rt">No. HP</label>
                            <input type="text" name="no_hp" class="form-control-rt">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label-rt">Alamat</label>
                            <input type="text" name="alamat" class="form-control-rt">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label-rt">Status Hunian</label>
                            <select name="status_hunian" class="form-control-rt" required>
                                <option value="Tetap">Tetap</option>
                                <option value="Kontrak">Kontrak / Tidak Tetap</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-check mt-3">
                        <input class="form-check-input" type="checkbox" name="status_aktif" id="aktifTambah" checked>
                        <label class="form-check-label" for="aktifTambah">Keluarga aktif</label>
                    </div>

                    <hr class="my-4">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h6 class="fw-semibold mb-1">Anggota Keluarga</h6>
                            <div class="text-muted-rt" style="font-size:12px;">Minimal 1 anggota tambahan agar satu rumah berisi 2 warga. Maksimal 4 anggota; NIK wajib 16 digit.</div>
                        </div>
                        <button type="button" class="btn-rt-outline" id="tambahBarisAnggota">+ Anggota</button>
                    </div>
                    <div id="anggotaContainer" class="d-grid gap-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-rt-primary">Simpan Keluarga</button>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="templateAnggota">
    <div class="anggota-row row g-2 align-items-end border rounded-3 p-2 mx-0">
        <div class="col-md-5">
            <label class="form-label-rt">Nama Anggota</label>
            <input type="text" data-name="nama" class="form-control-rt" required>
        </div>
        <div class="col-md-5">
            <label class="form-label-rt">NIK (16 digit)</label>
            <input type="text" data-name="nik" class="form-control-rt" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
        </div>
        <div class="col-md-2 d-grid">
            <button type="button" class="btn-rt-outline hapus-anggota" aria-label="Hapus anggota">Hapus</button>
        </div>
    </div>
</template>

<div class="modal fade" id="modalTambahAnggota" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <form id="formTambahAnggota" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="font-family:var(--font-display);">Tambah Anggota Keluarga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-rt">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control-rt" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-rt">NIK (16 digit)</label>
                        <input type="text" name="nik" class="form-control-rt" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                    </div>
                    <div>
                        <label class="form-label-rt">No. HP</label>
                        <input type="text" name="no_hp" class="form-control-rt">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-rt-primary">Simpan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:14px;">
            <form id="formEditWarga" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" style="font-family:var(--font-display);">Ubah Data Warga</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label-rt">Nama Lengkap</label>
                        <input type="text" name="nama" id="editNama" class="form-control-rt" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-rt">NIK (16 digit)</label>
                        <input type="text" name="nik" id="editNik" class="form-control-rt" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" required>
                    </div>
                    <div id="editDataKepala">
                        <div class="mb-3">
                            <label class="form-label-rt">No. Rumah</label>
                            <input type="text" name="no_rumah" id="editNoRumah" class="form-control-rt">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-rt">Alamat</label>
                            <input type="text" name="alamat" id="editAlamat" class="form-control-rt">
                        </div>
                        <div class="mb-3">
                            <label class="form-label-rt">Status Hunian</label>
                            <select name="status_hunian" id="editHunian" class="form-control-rt">
                                <option value="Tetap">Tetap</option>
                                <option value="Kontrak">Kontrak / Tidak Tetap</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-rt">No. HP</label>
                        <input type="text" name="no_hp" id="editNoHp" class="form-control-rt">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="status_aktif" id="editAktif">
                        <label class="form-check-label" for="editAktif">Warga aktif</label>
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
const anggotaContainer = document.getElementById('anggotaContainer');
const templateAnggota = document.getElementById('templateAnggota');

function tambahBarisAnggota() {
    const rowCount = anggotaContainer.querySelectorAll('.anggota-row').length;
    if (rowCount >= 4) {
        return;
    }

    const row = templateAnggota.content.firstElementChild.cloneNode(true);
    row.querySelectorAll('[data-name]').forEach((input) => {
        input.name = `anggota[${rowCount}][${input.dataset.name}]`;
    });
    row.querySelector('.hapus-anggota').addEventListener('click', () => row.remove());
    anggotaContainer.appendChild(row);
}

document.getElementById('tambahBarisAnggota').addEventListener('click', tambahBarisAnggota);
tambahBarisAnggota();

document.querySelectorAll('[data-family-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const details = document.getElementById(button.dataset.target);
        const isExpanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', String(!isExpanded));
        details.hidden = isExpanded;
    });
});

const filterStatusHunian = document.getElementById('filterStatusHunian');
const cariKeluarga = document.getElementById('cariKeluarga');
const keluargaRows = document.querySelectorAll('[data-household-row]');

function filterKeluarga() {
    const statusHunian = filterStatusHunian.value;
    const query = cariKeluarga.value.trim().toLocaleLowerCase('id');
    let jumlahTerlihat = 0;

    keluargaRows.forEach((row) => {
        const cocokStatus = !statusHunian || row.dataset.statusHunian === statusHunian;
        const cocokPencarian = !query || row.dataset.search.toLocaleLowerCase('id').includes(query);
        const terlihat = cocokStatus && cocokPencarian;
        const details = document.querySelector(`[data-family-detail="${row.dataset.householdId}"]`);
        const toggle = row.querySelector('[data-family-toggle]');

        row.hidden = !terlihat;
        details.hidden = !terlihat || (!query && toggle.getAttribute('aria-expanded') !== 'true');
        jumlahTerlihat += terlihat ? 1 : 0;
    });

    const emptyResult = document.getElementById('keluargaTidakDitemukan');
    if (emptyResult) {
        emptyResult.hidden = jumlahTerlihat > 0;
    }
}

filterStatusHunian.addEventListener('change', filterKeluarga);
cariKeluarga.addEventListener('input', filterKeluarga);

document.querySelectorAll('.btn-tambah-anggota').forEach((button) => {
    button.addEventListener('click', () => {
        document.getElementById('formTambahAnggota').action =
            `{{ url('admin/warga') }}/${button.dataset.id}/anggota`;
    });
});

document.querySelectorAll('.btn-edit-warga').forEach((button) => {
    button.addEventListener('click', () => {
        const form = document.getElementById('formEditWarga');
        form.action = `{{ url('admin/warga/update') }}/${button.dataset.id}`;
        document.getElementById('editNama').value = button.dataset.nama || '';
        document.getElementById('editNik').value = button.dataset.nik || '';
        document.getElementById('editNoRumah').value = button.dataset.norumah || '';
        document.getElementById('editAlamat').value = button.dataset.alamat || '';
        document.getElementById('editNoHp').value = button.dataset.nohp || '';
        document.getElementById('editHunian').value = button.dataset.hunian || 'Tetap';
        document.getElementById('editAktif').checked = button.dataset.aktif === '1';
        document.getElementById('editDataKepala').hidden = button.dataset.ishead !== '1';
    });
});
</script>
@endpush
