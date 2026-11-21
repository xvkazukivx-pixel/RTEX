<?php

namespace App\Http\Controllers;

use App\Models\BankSampahJenis;
use App\Models\BankSampahSetoran;
use App\Models\IuranSetting;
use App\Models\InventarisBarang;
use App\Models\InventarisPeminjaman;
use App\Models\Kegiatan;
use App\Models\KegiatanPeserta;
use App\Models\Pengaduan;
use App\Models\PengajuanSurat;
use App\Models\Pengumuman;
use App\Models\PosyanduAnak;
use App\Models\PosyanduJadwal;
use App\Models\PosyanduPemeriksaan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\Warga;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class WargaController extends Controller
{
    private function getWarga()
    {
        $user = auth()->user();
        if (! $user->warga_id) {
            return null;
        }

        return Warga::find($user->warga_id);
    }

    private function getKepalaKeluarga(Warga $warga): Warga
    {
        return $warga->kepalaKeluarga ?? $warga;
    }

    // ==========================================
    // 1. DASHBOARD SAYA
    // ==========================================
    public function dashboard()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return view('warga.dashboard_unlinked');
        }

        $periode = periode_sekarang();
        $kepala = $this->getKepalaKeluarga($warga);
        $tagihanBulanIni = Tagihan::where('warga_id', $kepala->id)->where('periode', $periode)->first();
        $totalTunggakan = Tagihan::where('warga_id', $kepala->id)->where('status', '!=', 'Lunas')->sum('total_tagihan');
        $jumlahBulanTunggakan = Tagihan::where('warga_id', $kepala->id)->where('status', '!=', 'Lunas')->count();

        return view('warga.dashboard', compact('warga', 'periode', 'tagihanBulanIni', 'totalTunggakan', 'jumlahBulanTunggakan'));
    }

    // ==========================================
    // 2. BAYAR IURAN
    // ==========================================
    public function bayar()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $kepala = $this->getKepalaKeluarga($warga);
        $tagihanBelumBayar = Tagihan::where('warga_id', $kepala->id)
            ->where('status', 'Belum Bayar')
            ->orderBy('periode', 'asc')
            ->get();

        $tagihanMenunggu = Tagihan::where('warga_id', $kepala->id)
            ->where('status', 'Menunggu Konfirmasi')
            ->orderBy('periode', 'asc')
            ->get();

        return view('warga.bayar', compact('warga', 'tagihanBelumBayar', 'tagihanMenunggu'));
    }

    public function bayarStore(Request $request)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $kepala = $this->getKepalaKeluarga($warga);
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,id',
            'bukti_transfer' => 'required|file|mimes:jpg,jpeg,png,pdf|max:3072',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tagihan = Tagihan::where('id', $validated['tagihan_id'])
            ->where('warga_id', $kepala->id)
            ->where('status', 'Belum Bayar')
            ->firstOrFail();

        $file = $request->file('bukti_transfer');
        $filename = 'bukti_'.date('YmdHis').'_'.bin2hex(random_bytes(4)).'.'.$file->getClientOriginalExtension();
        $file->storeAs('bukti_transfer', $filename, 'public');

        DB::transaction(function () use ($tagihan, $kepala, $filename, $validated) {
            Transaksi::create([
                'tagihan_id' => $tagihan->id,
                'warga_id' => $kepala->id,
                'jumlah_bayar' => $tagihan->total_tagihan,
                'metode' => 'Transfer',
                'bukti_transfer' => $filename,
                'keterangan' => $validated['keterangan'] ?? null,
                'status_konfirmasi' => 'Menunggu',
            ]);

            $tagihan->update(['status' => 'Menunggu Konfirmasi']);
        });

        return back()->with('success', 'Bukti transfer berhasil diunggah. Menunggu konfirmasi dari Bendahara RT.');
    }

    // ==========================================
    // 3. RIWAYAT PEMBAYARAN
    // ==========================================
    public function riwayat()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $kepala = $this->getKepalaKeluarga($warga);
        $riwayat = Transaksi::with('tagihan')
            ->where('warga_id', $kepala->id)
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        return view('warga.riwayat', compact('riwayat'));
    }

    // ==========================================
    // 4. INFO KAS UMUM
    // ==========================================
    public function kas()
    {
        $saldoKas = Transaksi::where('status_konfirmasi', 'Diterima')->sum('jumlah_bayar');
        $periode = periode_sekarang();

        $kasBulanIni = Transaksi::where('status_konfirmasi', 'Diterima')
            ->whereHas('tagihan', fn ($q) => $q->where('periode', $periode))
            ->sum('jumlah_bayar');

        $totalWargaAktif = Warga::where('status_aktif', true)
            ->whereNull('kepala_keluarga_id')
            ->count();
        $wargaLunas = Tagihan::where('periode', $periode)->where('status', 'Lunas')->count();
        $daftarIuranAktif = IuranSetting::where('status_aktif', true)->orderBy('nama_iuran')->get();

        return view('warga.kas', compact('saldoKas', 'periode', 'kasBulanIni', 'totalWargaAktif', 'wargaLunas', 'daftarIuranAktif'));
    }

    // ==========================================
    // 5. BANK SAMPAH
    // ==========================================
    public function bankSampah()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $jenis = BankSampahJenis::where('status_aktif', true)->orderBy('nama')->get();
        $riwayat = BankSampahSetoran::with('jenis')
            ->where('warga_id', $warga->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $saldo = $riwayat->where('status', 'Diterima')->sum('nilai_setoran');
        $totalBerat = $riwayat
            ->where('status', 'Diterima')
            ->sum(fn ($setoran) => (float) ($setoran->berat_final ?? $setoran->berat_estimasi));
        $kategoriSampah = [
            'Organik' => 'Sisa makanan, daun, kulit buah',
            'Plastik' => 'Botol PET, Gelas plastik, Plastik kemasan',
            'Kertas' => 'Kardus, Koran, Buku bekas, Majalah',
            'Logam' => 'Kaleng aluminium, Kaleng besi, Besi bekas',
            'Kaca' => 'Botol kaca',
            'Tekstil' => 'Pakaian bekas',
            'Elektronik' => 'Kabel tembaga',
            'Lainnya' => 'Sampah yang belum masuk kategori',
        ];
        $jenisPerKategori = $jenis->groupBy(fn ($jenisItem) => $jenisItem->kategori ?: 'Lainnya');
        $contohDenganTarif = [
            'Plastik' => [
                'Botol PET' => 'Botol PET',
                'Gelas plastik' => 'Gelas plastik',
                'Plastik kemasan' => 'Plastik kemasan',
            ],
            'Kertas' => [
                'Kardus' => 'Kardus',
                'Koran' => 'Koran',
                'Buku bekas' => 'Buku bekas',
                'Majalah' => 'Majalah',
            ],
            'Logam' => [
                'Kaleng aluminium' => 'Kaleng aluminium',
                'Kaleng besi' => 'Kaleng besi',
                'Besi bekas' => 'Besi bekas',
            ],
            'Kaca' => [
                'Botol kaca' => 'Botol kaca',
            ],
            'Tekstil' => [
                'Pakaian bekas' => 'Pakaian bekas',
            ],
            'Elektronik' => [
                'Kabel tembaga' => 'Kabel tembaga',
            ],
        ];
        $tarifLainnyaDefault = [
            'Plastik' => 'Botol PET',
            'Kertas' => 'Kardus',
            'Logam' => 'Kaleng aluminium',
            'Kaca' => 'Botol kaca',
            'Tekstil' => 'Pakaian bekas',
            'Elektronik' => 'Kabel tembaga',
        ];
        $pilihanSampah = [];

        foreach ($kategoriSampah as $kategori => $contoh) {
            $jenisKategori = $jenisPerKategori->get($kategori, collect());
            $pilihan = [];
            $jenisContohTerpetakan = array_values($contohDenganTarif[$kategori] ?? []);

            foreach (array_map('trim', explode(',', $contoh)) as $namaContoh) {
                $namaJenisTarif = $contohDenganTarif[$kategori][$namaContoh] ?? null;
                $jenisTarif = $namaJenisTarif
                    ? $jenisKategori->firstWhere('nama', $namaJenisTarif)
                    : null;
                $jenisTarif ??= $jenisKategori->first();

                $pilihan[] = [
                    'nama' => $namaContoh,
                    'jenis_id' => $jenisTarif?->id,
                    'harga' => $jenisTarif ? format_rupiah($jenisTarif->harga_per_kg) : null,
                    'harga_numeric' => $jenisTarif ? (float) $jenisTarif->harga_per_kg : null,
                    'tarif_jenis' => $jenisTarif?->nama,
                ];
            }

            foreach ($jenisKategori as $jenisItem) {
                if (in_array($jenisItem->nama, $jenisContohTerpetakan, true)) {
                    continue;
                }

                $pilihan[] = [
                    'nama' => $jenisItem->nama,
                    'jenis_id' => $jenisItem->id,
                    'harga' => format_rupiah($jenisItem->harga_per_kg),
                    'tarif_jenis' => $jenisItem->nama,
                ];
            }

            $namaTarifLainnya = $tarifLainnyaDefault[$kategori] ?? null;
            $jenisTarifLainnya = $namaTarifLainnya
                ? $jenisKategori->firstWhere('nama', $namaTarifLainnya)
                : null;
            $jenisTarifLainnya ??= $jenisKategori->first();

            $pilihanSampah[$kategori] = [
                'items' => $pilihan,
                'lainnya' => $jenisTarifLainnya ? [
                    'jenis_id' => $jenisTarifLainnya->id,
                    'harga' => format_rupiah($jenisTarifLainnya->harga_per_kg),
                    'harga_numeric' => (float) $jenisTarifLainnya->harga_per_kg,
                    'tarif_jenis' => $jenisTarifLainnya->nama,
                ] : null,
            ];
        }

        return view('warga.bank_sampah', compact(
            'jenis',
            'jenisPerKategori',
            'pilihanSampah',
            'riwayat',
            'saldo',
            'totalBerat',
            'kategoriSampah'
        ));
    }

    public function bankSampahStore(Request $request)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $validated = $request->validate([
            'jenis_id' => 'required|exists:bank_sampah_jenis,id',
            'nama_sampah' => 'required|string|max:100',
            'berat_estimasi' => 'required|numeric|min:0.01',
            'catatan' => 'nullable|string|max:255',
        ]);
        $jenis = BankSampahJenis::where('status_aktif', true)->findOrFail($validated['jenis_id']);

        BankSampahSetoran::create([
            'warga_id' => $warga->id,
            'jenis_id' => $jenis->id,
            'nama_sampah' => $validated['nama_sampah'],
            'harga_per_kg' => $jenis->harga_per_kg,
            'berat_estimasi' => $validated['berat_estimasi'],
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'Menunggu Validasi',
        ]);

        return back()->with('success', 'Setoran sampah berhasil diajukan. Petugas akan menimbang dan memvalidasi nilainya.');
    }

    // ==========================================
    // 6. INVENTARIS
    // ==========================================
    public function inventaris()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $barang = InventarisBarang::where('kondisi', 'Baik')
            ->where('jumlah_tersedia', '>', 0)
            ->orderBy('nama')
            ->get();
        $peminjaman = InventarisPeminjaman::with('barang')
            ->where('warga_id', $warga->id)
            ->latest()
            ->get();

        return view('warga.inventaris', compact('barang', 'peminjaman'));
    }

    public function inventarisPinjamStore(Request $request)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $validated = $request->validate([
            'barang_id' => ['required', 'exists:inventaris_barang,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal_pinjam' => ['required', 'date'],
            'rencana_kembali' => ['required', 'date', 'after_or_equal:tanggal_pinjam'],
            'keperluan' => ['required', 'string', 'max:1000'],
            'foto_pinjam' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $barang = InventarisBarang::findOrFail($validated['barang_id']);
        if ($validated['jumlah'] > $barang->jumlah_tersedia) {
            return back()->with('error', 'Jumlah barang tersedia tidak mencukupi.');
        }

        $fotoPinjam = null;
        if ($request->hasFile('foto_pinjam')) {
            $fotoPinjam = $request->file('foto_pinjam')->store('inventaris/peminjaman', 'public');
        }

        InventarisPeminjaman::create([
            'barang_id' => $barang->id,
            'warga_id' => $warga->id,
            'nama_peminjam' => $warga->nama,
            'jumlah' => $validated['jumlah'],
            'tanggal_pinjam' => $validated['tanggal_pinjam'],
            'rencana_kembali' => $validated['rencana_kembali'],
            'status' => 'Diajukan',
            'keperluan' => $validated['keperluan'],
            'foto_pinjam' => $fotoPinjam,
        ]);

        return back()->with('success', 'Permintaan peminjaman berhasil dikirim.');
    }

    // ==========================================
    // 7. PENGADUAN
    // ==========================================
    public function pengaduan()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $pengaduan = Pengaduan::where('warga_id', $warga->id)
            ->latest()
            ->get();

        return view('warga.pengaduan', compact('pengaduan'));
    }

    public function pengaduanStore(Request $request)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $validated = $request->validate([
            'kategori' => ['required', 'string', 'max:100'],
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pengaduan', 'public');
        }

        Pengaduan::create([
            'warga_id' => $warga->id,
            'kategori' => $validated['kategori'],
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'foto' => $fotoPath,
            'status' => 'Diajukan',
        ]);

        return back()->with('success', 'Pengaduan berhasil dikirim.');
    }

    // ==========================================
    // 7. POSYANDU
    // ==========================================
    public function posyandu()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $anak = PosyanduAnak::where('warga_id', $warga->id)->orderBy('tanggal_lahir', 'desc')->get();
        $jadwal = PosyanduJadwal::where('status', 'Terjadwal')->where('tanggal', '>=', now())->orderBy('tanggal', 'asc')->get();

        $riwayat = PosyanduPemeriksaan::with(['anak', 'jadwal'])
            ->whereHas('anak', fn ($q) => $q->where('warga_id', $warga->id))
            ->orderBy('created_at', 'desc')
            ->get();

        return view('warga.posyandu', compact('anak', 'jadwal', 'riwayat'));
    }

    public function posyanduAnakStore(Request $request)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:L,P',
        ]);

        PosyanduAnak::create([
            'warga_id' => $warga->id,
            'nama' => $validated['nama'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ]);

        return back()->with('success', 'Data anak berhasil ditambahkan.');
    }

    // ==========================================
    // 8. LAYANAN SURAT
    // ==========================================
    public function surat()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $jenisTersedia = PengajuanSurat::JENIS_SURAT;

        $pengajuan = PengajuanSurat::where('warga_id', $warga->id)->orderBy('created_at', 'desc')->get();

        return view('warga.surat', compact('jenisTersedia', 'pengajuan'));
    }

    public function suratStore(Request $request)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $validated = $request->validate([
            'jenis_surat' => ['required', Rule::in(PengajuanSurat::JENIS_SURAT)],
            'keperluan' => ['required', 'string', 'max:255'],
            'catatan_warga' => ['nullable', 'string', 'max:2000'],
        ]);

        PengajuanSurat::create([
            'warga_id' => $warga->id,
            'jenis_surat' => $validated['jenis_surat'],
            'keperluan' => $validated['keperluan'],
            'catatan_warga' => $validated['catatan_warga'] ?? null,
            'status' => 'Diajukan',
        ]);

        return back()->with('success', 'Pengajuan surat berhasil dikirim. Anda dapat memantau statusnya di halaman ini.');
    }

    // ==========================================
    // 9. INFORMASI & KEGIATAN
    // ==========================================
    public function informasi()
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $pengumuman = Pengumuman::where('status', 'Dipublikasikan')->orderBy('dipublikasikan_pada', 'desc')->limit(10)->get();

        $kegiatan = Kegiatan::where('status', 'Dipublikasikan')
            ->withCount(['peserta as jumlah_peserta' => fn ($q) => $q->where('status', 'Terdaftar')])
            ->orderBy('mulai_pada', 'asc')
            ->get();

        $sayaTerdaftar = KegiatanPeserta::where('warga_id', $warga->id)
            ->where('status', 'Terdaftar')
            ->pluck('kegiatan_id')
            ->toArray();

        return view('warga.informasi', compact('pengumuman', 'kegiatan', 'sayaTerdaftar'));
    }

    public function kegiatanDaftar($id)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $kegiatan = Kegiatan::where('id', $id)
            ->where('status', 'Dipublikasikan')
            ->where('mulai_pada', '>', now())
            ->firstOrFail();

        $peserta = KegiatanPeserta::where('kegiatan_id', $id)->where('warga_id', $warga->id)->first();
        if ($peserta && $peserta->status === 'Terdaftar') {
            return back()->with('error', 'Anda sudah terdaftar pada kegiatan ini.');
        }

        $totalTerdaftar = KegiatanPeserta::where('kegiatan_id', $id)->where('status', 'Terdaftar')->count();
        if ($kegiatan->kapasitas && $totalTerdaftar >= $kegiatan->kapasitas) {
            return back()->with('error', 'Kapasitas peserta kegiatan sudah penuh.');
        }

        KegiatanPeserta::updateOrCreate(
            ['kegiatan_id' => $id, 'warga_id' => $warga->id],
            ['status' => 'Terdaftar', 'terdaftar_pada' => now()]
        );

        return back()->with('success', 'Anda berhasil terdaftar sebagai peserta kegiatan.');
    }

    public function kegiatanBatal($id)
    {
        $warga = $this->getWarga();
        if (! $warga) {
            return redirect()->route('warga.dashboard');
        }

        $peserta = KegiatanPeserta::where('kegiatan_id', $id)
            ->where('warga_id', $warga->id)
            ->where('status', 'Terdaftar')
            ->firstOrFail();

        $peserta->update(['status' => 'Batal']);

        return back()->with('success', 'Pendaftaran kegiatan dibatalkan.');
    }
}
