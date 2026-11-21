@extends('layouts.app')

@section('title', 'Bank Sampah Warga')
@section('page_title', 'Bank Sampah')
@section('page_subtitle', 'Ajukan setoran dan pantau tabungan bank sampah Anda')

@section('content')
<div class="bank-summary-grid mb-4">
    <div class="bank-summary-card">
        <span>Saldo Tabungan Sampah</span>
        <strong>{{ format_rupiah($saldo) }}</strong>
        <small>Akumulasi setoran diterima</small>
    </div>
    <div class="bank-summary-card">
        <span>Total Berat Sampah</span>
        <strong>{{ number_format($totalBerat, 2, ',', '.') }} kg</strong>
        <small>Total setoran terkonfirmasi</small>
    </div>
</div>

<div class="d-flex justify-content-end mb-4">
    <button type="button" class="btn-rt-primary" data-bs-toggle="modal" data-bs-target="#modalAjukanSetoran">
        <span aria-hidden="true">＋</span> Ajukan Setoran Sampah
    </button>
</div>

<div class="card-rt">
    <div class="section-title"><span class="dot"></span> Riwayat Setoran Sampah</div>
    @if ($riwayat->isEmpty())
        <div class="empty-state">Belum ada riwayat setoran.</div>
    @else
        <div class="table-responsive">
            <table class="table-rt">
                <thead><tr><th>Jenis</th><th>Berat</th><th>Nilai</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($riwayat as $s)
                    <tr>
                        <td>
                            <strong>{{ $s->nama_sampah ?: ($s->jenis->nama ?? '-') }}</strong>
                            <div class="text-muted-rt" style="font-size:11.5px;">{{ format_tanggal($s->created_at, true) }}</div>
                        </td>
                        <td>{{ number_format((float) ($s->berat_final ?: $s->berat_estimasi), 2, ',', '.') }} kg</td>
                        <td><strong>{{ $s->nilai_setoran !== null ? format_rupiah($s->nilai_setoran) : '-' }}</strong></td>
                        <td>{!! badge_status($s->status) !!}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="modal fade" id="modalAjukanSetoran" tabindex="-1" aria-labelledby="modalAjukanSetoranLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content bank-modal">
      <form action="{{ route('warga.bank_sampah.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalAjukanSetoranLabel">Ajukan Setoran Sampah</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            <section id="stepPilihSampah">
                <span class="form-label-rt d-block" id="jenisSampahLabel">Pilih Kategori Sampah</span>
                <input type="hidden" name="jenis_id" id="jenis_id" value="{{ old('jenis_id') }}">
                <input type="hidden" name="pilihan_sampah" id="pilihan_sampah" value="{{ old('pilihan_sampah') }}">
                <div class="waste-pick-grid" aria-labelledby="jenisSampahLabel">
                    @foreach ($kategoriSampah as $kategori => $contoh)
                        @php($jenisKategori = $jenisPerKategori->get($kategori, collect()))
                        <fieldset class="waste-pick-card {{ $jenisKategori->isEmpty() ? 'is-unavailable' : '' }}">
                            <legend>{{ $kategori }}</legend>
                            <div class="waste-example-list">
                                @foreach ($pilihanSampah[$kategori]['items'] as $index => $pilihan)
                                    @php($choiceKey = 'sampah-'.$loop->parent->index.'-'.$index)
                                    <button
                                        type="button"
                                        class="waste-sample-choice {{ old('pilihan_sampah') === $choiceKey ? 'is-selected' : '' }}"
                                        data-waste-choice
                                        data-choice="{{ $choiceKey }}"
                                        data-nama="{{ $pilihan['nama'] }}"
                                        data-jenis-id="{{ $pilihan['jenis_id'] }}"
                                        data-harga="{{ $pilihan['harga'] ? $pilihan['harga'].'/kg' : '' }}"
                                        data-harga-numeric="{{ $pilihan['harga_numeric'] }}"
                                        @disabled(! $pilihan['jenis_id'])
                                        aria-pressed="{{ old('pilihan_sampah') === $choiceKey ? 'true' : 'false' }}"
                                    >
                                        <span>{{ $pilihan['nama'] }}</span>
                                        @if ($pilihan['harga'])
                                            <small>{{ $pilihan['harga'] }}/kg</small>
                                        @else
                                            <small>Tarif belum tersedia</small>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                            @if ($pilihanSampah[$kategori]['lainnya'])
                                @php($otherKey = 'lainnya-'.$loop->index)
                                <button
                                    type="button"
                                    class="waste-sample-choice waste-other-choice {{ old('pilihan_sampah') === $otherKey ? 'is-selected' : '' }}"
                                    data-waste-choice
                                    data-choice="{{ $otherKey }}"
                                    data-nama=""
                                    data-lainnya="true"
                                    data-jenis-id="{{ $pilihanSampah[$kategori]['lainnya']['jenis_id'] }}"
                                    data-harga="{{ $pilihanSampah[$kategori]['lainnya']['harga'] }}/kg"
                                    data-harga-numeric="{{ $pilihanSampah[$kategori]['lainnya']['harga_numeric'] }}"
                                    aria-pressed="{{ old('pilihan_sampah') === $otherKey ? 'true' : 'false' }}"
                                >
                                    <span>Lainnya</span>
                                    <small>Tarif {{ $pilihanSampah[$kategori]['lainnya']['tarif_jenis'] }}: {{ $pilihanSampah[$kategori]['lainnya']['harga'] }}/kg</small>
                                </button>
                            @else
                                <span class="waste-unavailable-label">Jenis dan tarif kategori ini belum tersedia.</span>
                                <button type="button" class="waste-sample-choice waste-other-choice" disabled>
                                    <span>Lainnya</span>
                                    <small>Tarif belum tersedia</small>
                                </button>
                            @endif
                        </fieldset>
                    @endforeach
                </div>
                <small class="waste-price-note">Pilih contoh sampah atau “Lainnya”. Tarif mengikuti harga per kg yang tertera.</small>
            </section>
            <section id="stepDetailSampah" hidden>
                <div class="bank-selected-waste mb-3">
                    <span>Jenis sampah dipilih</span>
                    <strong id="jenisSampahTerpilih"></strong>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="berat_estimasi" class="form-label-rt">Berat Sampah (kg)</label>
                        <input id="berat_estimasi" type="number" min="0.01" step="0.01" name="berat_estimasi" value="{{ old('berat_estimasi') }}" class="form-control-rt bank-weight-input" placeholder="Contoh: 2.5" required>
                    </div>
                    <div class="col-md-6">
                        <label for="nama_sampah" class="form-label-rt">Nama Sampah</label>
                        <input id="nama_sampah" type="text" name="nama_sampah" maxlength="100" value="{{ old('nama_sampah') }}" class="form-control-rt" placeholder="Pilih contoh atau isi nama sampah lainnya" required>
                    </div>
                    <div class="col-md-6">
                        <label for="harga_per_kg" class="form-label-rt">Harga per kg</label>
                        <input id="harga_per_kg" type="text" class="form-control-rt" value="" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="nilai_perkiraan" class="form-label-rt">Perkiraan Nilai Setoran</label>
                        <input id="nilai_perkiraan" type="text" class="form-control-rt" value="" placeholder="Masukkan berat untuk menghitung" readonly>
                    </div>
                </div>
                <small class="waste-price-note">Nilai akhir dapat berubah setelah berat setoran ditimbang petugas.</small>
            </section>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-rt-outline" data-bs-dismiss="modal">Batal</button>
          <button type="button" id="kembaliPilihSampah" class="btn-rt-outline" hidden>Kembali</button>
          <button type="submit" id="kirimSetoran" class="btn-rt-primary" hidden>Ajukan Setoran</button>
        </div>
      </form>
    </div>
  </div>
</div>

@if ($errors->any())
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAjukanSetoran')).show();
    });
</script>
@endpush
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('nama_sampah');
        const priceInput = document.getElementById('harga_per_kg');
        const jenisInput = document.getElementById('jenis_id');
        const pilihanInput = document.getElementById('pilihan_sampah');
        const weightInput = document.getElementById('berat_estimasi');
        const estimatedValueInput = document.getElementById('nilai_perkiraan');
        const selectedName = document.getElementById('jenisSampahTerpilih');
        const selectionStep = document.getElementById('stepPilihSampah');
        const detailStep = document.getElementById('stepDetailSampah');
        const backButton = document.getElementById('kembaliPilihSampah');
        const submitButton = document.getElementById('kirimSetoran');
        const otherPlaceholder = 'Isi nama sampah lainnya, misalnya kertas fotokopian usang';
        const defaultPlaceholder = 'Pilih contoh atau isi nama sampah lainnya';
        let unitPrice = 0;

        const updateEstimatedValue = function () {
            const weight = Number.parseFloat(weightInput.value);
            const total = weight > 0 ? weight * unitPrice : 0;

            estimatedValueInput.value = total > 0
                ? new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0,
                }).format(total)
                : '';
        };

        const showDetailStep = function () {
            selectionStep.hidden = true;
            detailStep.hidden = false;
            backButton.hidden = false;
            submitButton.hidden = false;
            nameInput.focus();
        };

        document.querySelectorAll('[data-waste-choice]:not(:disabled)').forEach(function (choice) {
            choice.addEventListener('click', function () {
                document.querySelectorAll('[data-waste-choice]').forEach(function (option) {
                    const isSelected = option === choice;
                    option.classList.toggle('is-selected', isSelected);
                    option.setAttribute('aria-pressed', String(isSelected));
                });

                jenisInput.value = choice.dataset.jenisId;
                pilihanInput.value = choice.dataset.choice;
                priceInput.value = choice.dataset.harga;
                unitPrice = Number.parseFloat(choice.dataset.hargaNumeric) || 0;
                selectedName.textContent = choice.dataset.nama || 'Lainnya';
                nameInput.readOnly = choice.dataset.lainnya !== 'true';
                nameInput.value = choice.dataset.lainnya === 'true' ? '' : choice.dataset.nama;
                nameInput.placeholder = choice.dataset.lainnya === 'true' ? otherPlaceholder : defaultPlaceholder;
                updateEstimatedValue();
                showDetailStep();

                if (choice.dataset.lainnya === 'true') {
                    nameInput.focus();
                }
            });
        });

        const selectedChoice = Array.from(document.querySelectorAll('[data-waste-choice]'))
            .find(function (choice) {
                return choice.dataset.choice === pilihanInput.value;
            });

        if (selectedChoice) {
            selectedChoice.classList.add('is-selected');
            selectedChoice.setAttribute('aria-pressed', 'true');
            priceInput.value = selectedChoice.dataset.harga;
            unitPrice = Number.parseFloat(selectedChoice.dataset.hargaNumeric) || 0;
            selectedName.textContent = selectedChoice.dataset.nama || 'Lainnya';
            nameInput.readOnly = selectedChoice.dataset.lainnya !== 'true';

            if (selectedChoice.dataset.lainnya === 'true') {
                nameInput.placeholder = otherPlaceholder;
            } else {
                nameInput.value = selectedChoice.dataset.nama;
            }

            updateEstimatedValue();
            showDetailStep();
        }

        weightInput.addEventListener('input', updateEstimatedValue);
        backButton.addEventListener('click', function () {
            selectionStep.hidden = false;
            detailStep.hidden = true;
            backButton.hidden = true;
            submitButton.hidden = true;
        });
    });
</script>
@endpush
@endsection
