@php
    $namaPemohon = $surat->nama_pemohon ?: ($surat->warga?->nama ?? '-');
    $nikPemohon = $surat->nik_pemohon ?: $surat->warga?->nik;
    $alamatDomisili = $surat->alamat_domisili ?: $surat->warga?->alamat;
    $namaKetua = $pengaturan->nama_ketua ?: ($surat->penyetuju?->nama ?? 'Ketua RT');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $surat->jenis_surat }} — RTEX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Source+Serif+4:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --letter-blue: #287cbb;
            --letter-cyan: #36b7d8;
            --letter-ink: #203248;
            --letter-muted: #64788b;
        }

        * { box-sizing: border-box; }
        body { margin: 0; background: #edf4fa; color: var(--letter-ink); font-family: Inter, Arial, sans-serif; }
        button, input, textarea { font: inherit; }
        .print-toolbar { max-width: 1180px; margin: 26px auto 20px; padding: 0 20px; }
        .toolbar-heading { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .toolbar-heading h1 { margin: 0; font-size: 20px; }
        .toolbar-heading p { margin: 5px 0 0; color: var(--letter-muted); font-size: 13px; }
        .config-panel { padding: 18px; border: 1px solid #d6e5f1; border-radius: 14px; background: #fff; box-shadow: 0 12px 30px rgba(38, 91, 131, .08); }
        .config-panel h2 { margin: 0 0 14px; font-size: 15px; }
        .config-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .config-field { display: grid; gap: 5px; }
        .config-field label { color: #536a7e; font-size: 11px; font-weight: 700; }
        .config-field input { width: 100%; min-height: 38px; padding: 8px 10px; border: 1px solid #d5e0e9; border-radius: 8px; background: #fff; color: var(--letter-ink); }
        .config-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 9px; margin-top: 14px; }
        .config-actions button, .config-actions .print-button { min-height: 38px; padding: 8px 14px; border: 0; border-radius: 8px; color: #fff; background: linear-gradient(110deg, var(--letter-blue), var(--letter-cyan)); font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; }
        .config-actions .save-button { color: #2672a8; border: 1px solid #bed7e9; background: #f7fbff; }
        .config-error, .config-success { margin-bottom: 12px; padding: 10px 12px; border-radius: 8px; font-size: 13px; }
        .config-error { color: #a43736; background: #fff0ef; }
        .config-success { color: #21744f; background: #eaf8f1; }
        .logo-preview { width: 54px; height: 54px; object-fit: contain; border: 1px solid #d5e0e9; border-radius: 8px; background: #fff; }
        .letter-paper { width: min(850px, calc(100% - 32px)); min-height: 1080px; margin: 0 auto 38px; padding: 58px 68px; background: #fff; color: #15202a; box-shadow: 0 18px 48px rgba(32, 63, 91, .14); font-family: "Source Serif 4", "Times New Roman", serif; font-size: 14px; line-height: 1.7; }
        .letterhead { display: grid; grid-template-columns: 78px 1fr 78px; align-items: center; gap: 12px; padding-bottom: 14px; border-bottom: 3px double #15202a; text-align: center; }
        .letterhead img { width: 66px; height: 66px; object-fit: contain; }
        .letterhead-copy strong, .letterhead-copy span { display: block; }
        .letterhead-copy strong { font-size: 18px; line-height: 1.35; }
        .letterhead-copy .letterhead-title { font-size: 16px; font-weight: 700; text-transform: uppercase; }
        .letterhead-copy small { display: block; font-size: 11px; line-height: 1.45; }
        .letter-title { margin: 28px 0 22px; text-align: center; }
        .letter-title h2 { margin: 0; font-size: 17px; text-decoration: underline; text-transform: uppercase; }
        .letter-title p { margin: 2px 0 0; }
        .letter-body { text-align: justify; }
        .letter-data { width: 100%; margin: 12px 0 18px; border-collapse: collapse; }
        .letter-data td { padding: 2px 6px 2px 0; vertical-align: top; }
        .letter-data td:first-child { width: 190px; }
        .letter-data td:nth-child(2) { width: 14px; }
        .letter-signature { width: 270px; margin: 44px 0 0 auto; text-align: center; }
        .signature-space { height: 78px; }
        .letter-footer { margin-top: 30px; color: #46596a; font-size: 11px; }
        .no-print { display: block; }

        @media (max-width: 767.98px) {
            .print-toolbar { margin-top: 14px; padding: 0 12px; }
            .toolbar-heading { align-items: flex-start; flex-direction: column; }
            .config-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .letter-paper { min-height: auto; padding: 32px 24px; }
            .letterhead { grid-template-columns: 50px 1fr 50px; gap: 6px; }
            .letterhead img { width: 46px; height: 46px; }
            .letterhead-copy strong { font-size: 14px; }
            .letterhead-copy .letterhead-title { font-size: 12px; }
            .letter-data td:first-child { width: 125px; }
        }

        @media print {
            @page { size: A4; margin: 18mm 17mm; }
            body { background: #fff; }
            .no-print { display: none !important; }
            .letter-paper { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
        }
    </style>
</head>
<body>
    @if (auth()->user()->isSuperAdmin())
        <section class="print-toolbar no-print">
            <div class="toolbar-heading">
                <div>
                    <h1>Pratinjau &amp; Cetak Surat</h1>
                    <p>Atur kop RTEX sekali, lalu gunakan pada cetakan surat berikutnya.</p>
                </div>
            </div>

            @if (session('success'))
                <div class="config-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="config-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('admin.surat.kop') }}" method="POST" enctype="multipart/form-data" class="config-panel">
                @csrf
                <input type="hidden" name="surat_id" value="{{ $surat->id }}">
                <h2>Pengaturan Kop Surat</h2>
                <div class="config-grid">
                    <div class="config-field">
                        <label for="namaPemerintahan">Nama pemerintahan / organisasi</label>
                        <input id="namaPemerintahan" name="nama_pemerintahan" value="{{ old('nama_pemerintahan', $pengaturan->nama_pemerintahan) }}" maxlength="160" required>
                    </div>
                    <div class="config-field">
                        <label for="namaKecamatan">Nama kelurahan / kecamatan</label>
                        <input id="namaKecamatan" name="nama_kecamatan" value="{{ old('nama_kecamatan', $pengaturan->nama_kecamatan) }}" maxlength="160">
                    </div>
                    <div class="config-field">
                        <label for="namaRtRw">Nama RT/RW/Kelurahan</label>
                        <input id="namaRtRw" name="nama_rt_rw" value="{{ old('nama_rt_rw', $pengaturan->nama_rt_rw) }}" maxlength="160" required>
                    </div>
                    <div class="config-field">
                        <label for="alamatSekretariat">Alamat sekretariat</label>
                        <input id="alamatSekretariat" name="alamat_sekretariat" value="{{ old('alamat_sekretariat', $pengaturan->alamat_sekretariat) }}" maxlength="255">
                    </div>
                    <div class="config-field">
                        <label for="namaKetua">Nama Ketua RT</label>
                        <input id="namaKetua" name="nama_ketua" value="{{ old('nama_ketua', $pengaturan->nama_ketua) }}" maxlength="120">
                    </div>
                    <div class="config-field">
                        <label for="jabatanPenandaTangan">Jabatan penanda tangan</label>
                        <input id="jabatanPenandaTangan" name="jabatan_penanda_tangan" value="{{ old('jabatan_penanda_tangan', $pengaturan->jabatan_penanda_tangan) }}" maxlength="100" required>
                    </div>
                    <div class="config-field">
                        <label for="kotaTtd">Kota/tempat TTD</label>
                        <input id="kotaTtd" name="kota_ttd" value="{{ old('kota_ttd', $pengaturan->kota_ttd) }}" maxlength="100">
                    </div>
                    <div class="config-field">
                        <label for="masaBerlaku">Masa berlaku (opsional)</label>
                        <input id="masaBerlaku" name="masa_berlaku" value="{{ old('masa_berlaku', $pengaturan->masa_berlaku) }}" maxlength="50">
                    </div>
                    <div class="config-field">
                        <label for="logoSurat">Logo / lambang (opsional, maks. 2 MB)</label>
                        <input id="logoSurat" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp">
                    </div>
                    @if ($pengaturan->logo_path)
                        <div class="config-field">
                            <label>Logo saat ini</label>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="{{ asset('storage/'.$pengaturan->logo_path) }}" alt="Logo kop surat" class="logo-preview">
                                <label style="display:flex;align-items:center;gap:6px;font-size:12px;">
                                    <input type="checkbox" name="hapus_logo" value="1"> Hapus logo
                                </label>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="config-actions">
                    <button type="submit" class="save-button">Simpan KOP</button>
                    <button type="button" class="print-button" data-print-letter>Cetak / Print PDF</button>
                </div>
            </form>
        </section>
    @else
        <div class="print-toolbar no-print">
            <button type="button" class="print-button" data-print-letter style="min-height:38px;padding:8px 14px;border:0;border-radius:8px;color:#fff;background:linear-gradient(110deg,var(--letter-blue),var(--letter-cyan));font-size:12px;font-weight:700;cursor:pointer;">
                Cetak / Print PDF
            </button>
        </div>
    @endif

    <main class="letter-paper">
        <header class="letterhead">
            <div>
                @if ($pengaturan->logo_path)
                    <img src="{{ asset('storage/'.$pengaturan->logo_path) }}" alt="Logo RTEX">
                @endif
            </div>
            <div class="letterhead-copy">
                <strong>{{ $pengaturan->nama_pemerintahan }}</strong>
                @if ($pengaturan->nama_kecamatan)
                    <span>{{ $pengaturan->nama_kecamatan }}</span>
                @endif
                <span class="letterhead-title">{{ $pengaturan->nama_rt_rw }}</span>
                @if ($pengaturan->alamat_sekretariat)
                    <small>{{ $pengaturan->alamat_sekretariat }}</small>
                @endif
            </div>
            <div></div>
        </header>

        <div class="letter-title">
            <h2>{{ $surat->jenis_surat }}</h2>
            <p>Nomor: {{ $surat->nomor_surat ?: '-' }}</p>
        </div>

        <section class="letter-body">
            <p>Yang bertanda tangan di bawah ini, {{ $pengaturan->jabatan_penanda_tangan }}, menerangkan bahwa:</p>
            <table class="letter-data">
                <tbody>
                    <tr><td>Nama lengkap</td><td>:</td><td>{{ $namaPemohon }}</td></tr>
                    @if ($nikPemohon)
                        <tr><td>NIK</td><td>:</td><td>{{ $nikPemohon }}</td></tr>
                    @endif
                    @if ($surat->jenis_kelamin)
                        <tr><td>Jenis kelamin</td><td>:</td><td>{{ $surat->jenis_kelamin }}</td></tr>
                    @endif
                    @if ($surat->tempat_lahir || $surat->tanggal_lahir)
                        <tr>
                            <td>Tempat, tanggal lahir</td><td>:</td>
                            <td>{{ $surat->tempat_lahir ?: '-' }}{{ $surat->tanggal_lahir ? ', '.format_tanggal($surat->tanggal_lahir) : '' }}</td>
                        </tr>
                    @endif
                    @if ($surat->agama)
                        <tr><td>Agama</td><td>:</td><td>{{ $surat->agama }}</td></tr>
                    @endif
                    @if ($surat->pekerjaan)
                        <tr><td>Pekerjaan</td><td>:</td><td>{{ $surat->pekerjaan }}</td></tr>
                    @endif
                    @if ($surat->status_perkawinan)
                        <tr><td>Status perkawinan</td><td>:</td><td>{{ $surat->status_perkawinan }}</td></tr>
                    @endif
                    @if ($surat->kewarganegaraan)
                        <tr><td>Kewarganegaraan</td><td>:</td><td>{{ $surat->kewarganegaraan }}</td></tr>
                    @endif
                    @if ($surat->warga?->no_rumah)
                        <tr><td>Nomor rumah</td><td>:</td><td>{{ $surat->warga->no_rumah }}</td></tr>
                    @endif
                    @if ($surat->alamat_ktp)
                        <tr><td>Alamat KTP</td><td>:</td><td>{{ $surat->alamat_ktp }}</td></tr>
                    @endif
                    @if ($alamatDomisili)
                        <tr><td>Alamat domisili</td><td>:</td><td>{{ $alamatDomisili }}</td></tr>
                    @endif
                </tbody>
            </table>
            <p>Surat ini dibuat untuk keperluan <strong>{{ $surat->keperluan }}</strong>.</p>
            @if ($pengaturan->masa_berlaku)
                <p>Masa berlaku surat: {{ $pengaturan->masa_berlaku }}.</p>
            @endif
            <p>Demikian surat keterangan ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>
        </section>

        <div class="letter-signature">
            <div>{{ $pengaturan->kota_ttd ? $pengaturan->kota_ttd.', ' : '' }}{{ format_tanggal($surat->disetujui_pada ?: now()) }}</div>
            <div>{{ $pengaturan->jabatan_penanda_tangan }}</div>
            <div class="signature-space"></div>
            <strong>{{ $namaKetua }}</strong>
        </div>

        @if (auth()->user()->isSuperAdmin())
            <footer class="letter-footer">
                Dicetak melalui RTEX — Administrasi Warga.
            </footer>
        @endif
    </main>

    <script>
        document.querySelectorAll('[data-print-letter]').forEach((button) => {
            button.addEventListener('click', () => window.print());
        });
    </script>
</body>
</html>
