@extends('layouts.app')

@section('title', 'Surat')
@section('page_title', 'Surat')
@section('page_subtitle', 'Pengajuan, pemrosesan, dan pencetakan surat administrasi warga')

@section('content')
<div class="letter-management">
    <div class="letter-management__heading">
        <div>
            <div class="letter-eyebrow">ADMINISTRASI WARGA</div>
            <h2>Pengajuan Surat</h2>
            <p>Kelola permohonan warga dan pemohon kontrak/tamu dalam satu alur.</p>
        </div>
        <button type="button" class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalSurat">
            + Ajukan Surat
        </button>
    </div>

    <div class="letter-summary-grid">
        <article class="letter-summary-card letter-summary-card--total">
            <span class="letter-summary-card__icon" aria-hidden="true">✉</span>
            <div><strong>{{ $ringkasan['total'] }}</strong><span>Total pengajuan</span></div>
        </article>
        <article class="letter-summary-card letter-summary-card--waiting">
            <span class="letter-summary-card__icon" aria-hidden="true">◷</span>
            <div><strong>{{ $ringkasan['menunggu'] }}</strong><span>Dalam proses</span></div>
        </article>
        <article class="letter-summary-card letter-summary-card--approved">
            <span class="letter-summary-card__icon" aria-hidden="true">✓</span>
            <div><strong>{{ $ringkasan['disetujui'] }}</strong><span>Disetujui RT</span></div>
        </article>
        <article class="letter-summary-card letter-summary-card--rejected">
            <span class="letter-summary-card__icon" aria-hidden="true">!</span>
            <div><strong>{{ $ringkasan['ditolak'] }}</strong><span>Ditolak</span></div>
        </article>
    </div>

    <section class="card-rt letter-table-card">
        <div class="letter-table-heading">
            <div>
                <h3>Daftar Pengajuan</h3>
                <p>Persetujuan akhir tetap mengikuti alur Ketua RT.</p>
            </div>
            <form method="GET" action="{{ route('admin.surat') }}" class="letter-filter-form">
                <select name="per_page" class="form-control-rt" aria-label="Jumlah data per halaman" onchange="this.form.submit()">
                    @foreach ([10, 25, 50] as $jumlah)
                        <option value="{{ $jumlah }}" @selected($perPage === $jumlah)>Tampilkan {{ $jumlah }}</option>
                    @endforeach
                </select>
                <select name="status" class="form-control-rt" aria-label="Filter status" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    @foreach ($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>
                <div class="letter-search">
                    <input type="search" name="q" value="{{ $search }}" class="form-control-rt" placeholder="Cari nama, nomor, jenis surat..." aria-label="Cari pengajuan surat">
                    <button type="submit" class="btn-rt-outline">Cari</button>
                </div>
            </form>
        </div>

        @if ($daftar->isEmpty())
            <div class="empty-state">
                {{ $search !== '' || $status !== '' ? 'Tidak ada pengajuan yang sesuai dengan pencarian.' : 'Belum ada pengajuan surat. Gunakan tombol Ajukan Surat untuk mencatat permohonan.' }}
            </div>
        @else
            <div class="table-responsive letter-table-wrap">
                <table class="table-rt letter-table">
                    <thead>
                        <tr>
                            <th>No. Surat</th>
                            <th>Tanggal</th>
                            <th>Nama Pemohon</th>
                            <th>Jenis Surat</th>
                            <th>Keperluan</th>
                            <th>Status</th>
                            <th>Catatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach ($daftar as $surat)
                        @php
                            $namaPemohon = $surat->nama_pemohon ?: ($surat->warga?->nama ?? '-');
                            $catatan = $surat->catatan_admin ?: ($surat->catatan_ketua ?: ($surat->catatan_warga ?: '-'));
                            $bisaDikelola = in_array($surat->status, [
                                \App\Models\PengajuanSurat::STATUS_DIAJUKAN,
                                \App\Models\PengajuanSurat::STATUS_DIPROSES,
                            ], true);
                            $bolehHapus = ! in_array($surat->status, [
                                \App\Models\PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN,
                                \App\Models\PengajuanSurat::STATUS_DISETUJUI,
                            ], true);
                        @endphp
                        <tr>
                            <td>
                                <strong class="letter-number">{{ $surat->nomor_surat ?: 'Belum bernomor' }}</strong>
                            </td>
                            <td class="text-nowrap">{{ format_tanggal($surat->created_at, true) }}</td>
                            <td>
                                <strong>{{ $namaPemohon }}</strong>
                                <span class="letter-applicant-type">{{ $surat->jenis_pemohon === 'non_warga' ? 'Kontrak / Tamu' : 'Warga RT' }}</span>
                                @if ($surat->warga?->no_rumah)
                                    <span class="letter-house">Rumah {{ $surat->warga->no_rumah }}</span>
                                @endif
                            </td>
                            <td>{{ $surat->jenis_surat }}</td>
                            <td class="letter-purpose">{{ $surat->keperluan }}</td>
                            <td>{!! badge_status($surat->status) !!}</td>
                            <td class="letter-note">{{ $catatan }}</td>
                            <td class="text-nowrap text-end">
                                <div class="letter-actions">
                                    @if ($bisaDikelola)
                                        <button type="button" class="btn-rt-outline letter-icon-button btn-edit-surat"
                                            data-id="{{ $surat->id }}"
                                            data-detail-url="{{ route('admin.surat.data', $surat) }}"
                                            title="Ubah pengajuan" aria-label="Ubah pengajuan {{ $namaPemohon }}">
                                            ✎
                                        </button>
                                        <button type="button" class="btn-rt-outline letter-icon-button btn-status-surat"
                                            data-id="{{ $surat->id }}"
                                            data-action="{{ $surat->status === \App\Models\PengajuanSurat::STATUS_DIAJUKAN ? 'proses' : 'kirim_persetujuan' }}"
                                            data-next-status="{{ $surat->status === \App\Models\PengajuanSurat::STATUS_DIAJUKAN ? \App\Models\PengajuanSurat::STATUS_DIPROSES : \App\Models\PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN }}"
                                            title="Perbarui status" aria-label="Perbarui status {{ $namaPemohon }}">
                                            ✓
                                        </button>
                                    @endif
                                    @if ($surat->status === \App\Models\PengajuanSurat::STATUS_DISETUJUI)
                                        <a href="{{ route('surat.cetak', $surat) }}" target="_blank" rel="noopener" class="btn-rt-outline letter-icon-button"
                                            title="Pratinjau dan cetak surat" aria-label="Pratinjau surat {{ $namaPemohon }}">
                                            ▣
                                        </a>
                                    @endif
                                    @if ($bolehHapus)
                                        <form action="{{ route('admin.surat.destroy', $surat) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus pengajuan surat ini?');">
                                            @csrf
                                            <button type="submit" class="btn-rt-outline letter-icon-button letter-icon-button--danger" title="Hapus pengajuan" aria-label="Hapus pengajuan {{ $namaPemohon }}">
                                                ×
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="letter-pagination">
                <span>Menampilkan {{ $daftar->firstItem() }}–{{ $daftar->lastItem() }} dari {{ $daftar->total() }} pengajuan</span>
                {{ $daftar->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </section>
</div>

<div class="modal fade" id="modalSurat" tabindex="-1" aria-labelledby="modalSuratTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content letter-modal">
            <form id="formSurat" method="POST" action="{{ route('admin.surat.store') }}"
                data-store-action="{{ route('admin.surat.store') }}"
                data-update-template="{{ route('admin.surat.update', '__ID__') }}"
                data-status-template="{{ route('admin.surat.action', '__ID__') }}">
                @csrf
                <div class="modal-header">
                    <div>
                        <div class="letter-eyebrow">FORMULIR ADMINISTRASI</div>
                        <h5 class="modal-title" id="modalSuratTitle">Ajukan Surat</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <section class="letter-form-section">
                        <h6>Jenis Pemohon</h6>
                        <div class="letter-applicant-options">
                            <label>
                                <input type="radio" name="jenis_pemohon" value="warga" checked>
                                <span>Warga RT terdaftar</span>
                            </label>
                            <label>
                                <input type="radio" name="jenis_pemohon" value="non_warga">
                                <span>Kontrak / Tamu (isi manual)</span>
                            </label>
                        </div>
                    </section>

                    <section class="letter-form-section" id="wargaFields">
                        <h6>Data Warga</h6>
                        <label class="form-label-rt" for="wargaId">Nama warga</label>
                        <select name="warga_id" id="wargaId" class="form-control-rt" required>
                            <option value="">Pilih warga terdaftar</option>
                            @foreach ($wargaTersedia as $warga)
                                <option value="{{ $warga->id }}">{{ $warga->nama }}{{ $warga->no_rumah ? ' — Rumah '.$warga->no_rumah : '' }}</option>
                            @endforeach
                        </select>
                        @if ($wargaTersedia->isEmpty())
                            <small class="letter-help">Belum ada warga aktif. Pilih pemohon kontrak/tamu untuk mengisi data secara manual.</small>
                        @endif
                    </section>

                    <section class="letter-form-section" id="manualFields" hidden>
                        <h6>Data Pemohon</h6>
                        <div class="letter-form-grid">
                            <div>
                                <label class="form-label-rt" for="namaPemohon">Nama lengkap</label>
                                <input name="nama_pemohon" id="namaPemohon" class="form-control-rt" maxlength="150" data-manual-field>
                            </div>
                            <div>
                                <label class="form-label-rt" for="nikPemohon">NIK (16 digit)</label>
                                <input name="nik_pemohon" id="nikPemohon" class="form-control-rt" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" data-manual-field>
                            </div>
                            <div>
                                <label class="form-label-rt" for="jenisKelamin">Jenis kelamin</label>
                                <select name="jenis_kelamin" id="jenisKelamin" class="form-control-rt" data-manual-field>
                                    <option value="">Pilih</option>
                                    <option>Laki-laki</option>
                                    <option>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label-rt" for="tempatLahir">Tempat lahir</label>
                                <input name="tempat_lahir" id="tempatLahir" class="form-control-rt" maxlength="100" data-manual-field>
                            </div>
                            <div>
                                <label class="form-label-rt" for="tanggalLahir">Tanggal lahir</label>
                                <input type="date" name="tanggal_lahir" id="tanggalLahir" class="form-control-rt" max="{{ now()->toDateString() }}" data-manual-field>
                            </div>
                            <div>
                                <label class="form-label-rt" for="agamaPemohon">Agama</label>
                                <select name="agama" id="agamaPemohon" class="form-control-rt" data-manual-field>
                                    <option value="">Pilih</option>
                                    @foreach (['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agama)
                                        <option>{{ $agama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label-rt" for="pekerjaanPemohon">Pekerjaan</label>
                                <input name="pekerjaan" id="pekerjaanPemohon" class="form-control-rt" maxlength="100" data-manual-field>
                            </div>
                            <div>
                                <label class="form-label-rt" for="statusPerkawinan">Status perkawinan</label>
                                <select name="status_perkawinan" id="statusPerkawinan" class="form-control-rt" data-manual-field>
                                    <option value="">Pilih</option>
                                    @foreach (['Belum Kawin', 'Kawin', 'Cerai Hidup', 'Cerai Mati'] as $statusPerkawinan)
                                        <option>{{ $statusPerkawinan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label-rt" for="kewarganegaraan">Kewarganegaraan</label>
                                <input name="kewarganegaraan" id="kewarganegaraan" class="form-control-rt" value="Indonesia" maxlength="80" data-manual-field>
                            </div>
                            <div>
                                <label class="form-label-rt" for="alamatKtp">Alamat KTP</label>
                                <textarea name="alamat_ktp" id="alamatKtp" class="form-control-rt" rows="2" maxlength="2000" data-manual-field></textarea>
                            </div>
                            <div>
                                <label class="form-label-rt" for="alamatDomisili">Alamat domisili saat ini</label>
                                <textarea name="alamat_domisili" id="alamatDomisili" class="form-control-rt" rows="2" maxlength="2000" data-manual-field></textarea>
                            </div>
                        </div>
                    </section>

                    <section class="letter-form-section">
                        <h6>Detail Surat</h6>
                        <div class="letter-form-grid">
                            <div>
                                <label class="form-label-rt" for="jenisSurat">Jenis surat</label>
                                <select name="jenis_surat" id="jenisSurat" class="form-control-rt" required>
                                    <option value="">Pilih jenis surat</option>
                                    @foreach (\App\Models\PengajuanSurat::JENIS_SURAT as $jenisSurat)
                                        <option value="{{ $jenisSurat }}">{{ $jenisSurat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="form-label-rt" for="keperluanSurat">Keperluan / tujuan surat</label>
                                <input name="keperluan" id="keperluanSurat" class="form-control-rt" maxlength="255" required>
                            </div>
                        </div>
                        <label class="form-label-rt mt-3" for="catatanWarga">Catatan tambahan (opsional)</label>
                        <textarea name="catatan_warga" id="catatanWarga" class="form-control-rt" rows="2" maxlength="2000"></textarea>
                    </section>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-rt-primary" id="submitSurat">Simpan Pengajuan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalStatusSurat" tabindex="-1" aria-labelledby="modalStatusSuratTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content letter-modal">
            <form id="formStatusSurat" method="POST" action="">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalStatusSuratTitle">Update Status Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label-rt" for="statusSuratBaru">Status</label>
                    <select name="aksi" id="statusSuratBaru" class="form-control-rt" required></select>
                    <div id="nomorSuratField" class="mt-3" hidden>
                        <label class="form-label-rt" for="nomorSurat">Nomor surat</label>
                        <input name="nomor_surat" id="nomorSurat" class="form-control-rt" maxlength="100" placeholder="Contoh: 001/RT05/XI/2026">
                    </div>
                    <label class="form-label-rt mt-3" for="catatanAdmin">Catatan</label>
                    <textarea name="catatan_admin" id="catatanAdmin" class="form-control-rt" rows="3" maxlength="2000"></textarea>
                    <small class="letter-help">Status Disetujui atau Ditolak ditetapkan oleh Ketua RT.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-rt-primary">Perbarui Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const formSurat = document.getElementById('formSurat');
    const modalSurat = document.getElementById('modalSurat');
    const modalSuratTitle = document.getElementById('modalSuratTitle');
    const submitSurat = document.getElementById('submitSurat');
    const wargaFields = document.getElementById('wargaFields');
    const manualFields = document.getElementById('manualFields');
    const wargaId = document.getElementById('wargaId');
    const formStatusSurat = document.getElementById('formStatusSurat');
    const statusSuratBaru = document.getElementById('statusSuratBaru');
    const nomorSuratField = document.getElementById('nomorSuratField');
    const nomorSurat = document.getElementById('nomorSurat');
    const updateTemplate = formSurat.dataset.updateTemplate;

    const setApplicantType = (type, clearFields = false) => {
        const isManual = type === 'non_warga';
        wargaFields.hidden = isManual;
        manualFields.hidden = !isManual;
        wargaId.disabled = isManual;
        wargaId.required = !isManual;
        manualFields.querySelectorAll('[data-manual-field]').forEach((field) => {
            field.disabled = !isManual;
            field.required = isManual;
            if (clearFields) {
                field.value = field.id === 'kewarganegaraan' && isManual ? 'Indonesia' : '';
            }
        });
        if (clearFields && isManual) {
            wargaId.value = '';
        }
    };

    document.querySelectorAll('input[name="jenis_pemohon"]').forEach((radio) => {
        radio.addEventListener('change', () => setApplicantType(radio.value, true));
    });

    modalSurat.addEventListener('hidden.bs.modal', () => {
        formSurat.reset();
        formSurat.action = formSurat.dataset.storeAction;
        modalSuratTitle.textContent = 'Ajukan Surat';
        submitSurat.textContent = 'Simpan Pengajuan';
        document.querySelector('input[name="jenis_pemohon"][value="warga"]').checked = true;
        setApplicantType('warga');
    });

    document.querySelectorAll('.btn-edit-surat').forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                const response = await fetch(button.dataset.detailUrl, {
                    headers: {Accept: 'application/json'},
                });
                if (!response.ok) {
                    throw new Error('Data pengajuan surat tidak dapat dimuat.');
                }

                const surat = await response.json();
                formSurat.action = updateTemplate.replace('__ID__', button.dataset.id);
                modalSuratTitle.textContent = 'Edit Pengajuan Surat';
                submitSurat.textContent = 'Simpan Perubahan';
                document.querySelectorAll('input[name="jenis_pemohon"]').forEach((radio) => {
                    radio.checked = radio.value === surat.jenis_pemohon;
                });
                setApplicantType(surat.jenis_pemohon, false);
                wargaId.value = surat.warga_id || '';

                const fields = {
                    namaPemohon: surat.nama_pemohon,
                    nikPemohon: surat.nik_pemohon,
                    jenisKelamin: surat.jenis_kelamin,
                    tempatLahir: surat.tempat_lahir,
                    tanggalLahir: surat.tanggal_lahir,
                    agamaPemohon: surat.agama,
                    pekerjaanPemohon: surat.pekerjaan,
                    statusPerkawinan: surat.status_perkawinan,
                    kewarganegaraan: surat.kewarganegaraan,
                    alamatKtp: surat.alamat_ktp,
                    alamatDomisili: surat.alamat_domisili,
                    jenisSurat: surat.jenis_surat,
                    keperluanSurat: surat.keperluan,
                    catatanWarga: surat.catatan_warga,
                };
                Object.entries(fields).forEach(([id, value]) => {
                    document.getElementById(id).value = value || '';
                });

                bootstrap.Modal.getOrCreateInstance(modalSurat).show();
            } catch (error) {
                console.error(error);
                window.alert('Data pengajuan tidak berhasil dimuat. Silakan muat ulang halaman dan coba kembali.');
            }
        });
    });

    document.querySelectorAll('.btn-status-surat').forEach((button) => {
        button.addEventListener('click', () => {
            formStatusSurat.action = formSurat.dataset.statusTemplate.replace('__ID__', button.dataset.id);
            statusSuratBaru.replaceChildren();
            const option = document.createElement('option');
            option.value = button.dataset.action;
            option.textContent = button.dataset.nextStatus;
            statusSuratBaru.append(option);
            formStatusSurat.reset();
            statusSuratBaru.value = button.dataset.action;
            nomorSuratField.hidden = button.dataset.action !== 'kirim_persetujuan';
            nomorSurat.required = button.dataset.action === 'kirim_persetujuan';
            bootstrap.Modal.getOrCreateInstance(document.getElementById('modalStatusSurat')).show();
        });
    });

    statusSuratBaru.addEventListener('change', () => {
        const needsNumber = statusSuratBaru.value === 'kirim_persetujuan';
        nomorSuratField.hidden = !needsNumber;
        nomorSurat.required = needsNumber;
    });

    setApplicantType('warga');
})();
</script>
@endpush
