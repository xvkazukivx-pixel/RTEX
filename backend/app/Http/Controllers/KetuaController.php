<?php

namespace App\Http\Controllers;

use App\Models\BankSampahSetoran;
use App\Models\Kegiatan;
use App\Models\LaporanValidasi;
use App\Models\PengajuanSurat;
use App\Models\Pengumuman;
use App\Models\PosyanduJadwal;
use App\Models\PosyanduPemeriksaan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\Warga;
use Illuminate\Http\Request;

class KetuaController extends Controller
{
    // ==========================================
    // 1. DASHBOARD EKSEKUTIF
    // ==========================================
    public function dashboard()
    {
        $periodeSekarang = periode_sekarang();

        // 6 Periode Terakhir untuk Tren
        $periodeList = [];
        $dataTren = [];
        for ($i = 5; $i >= 0; $i--) {
            $p = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $periodeList[] = $p;
            $dataTren[$p] = Transaksi::where('status_konfirmasi', 'Diterima')
                ->whereHas('tagihan', fn ($q) => $q->where('periode', $p))
                ->sum('jumlah_bayar');
        }

        $totalWargaAktif = Warga::where('status_aktif', true)
            ->whereNull('kepala_keluarga_id')
            ->count();
        $wargaLunas = Tagihan::where('periode', $periodeSekarang)->where('status', 'Lunas')->count();
        $persenKepatuhan = $totalWargaAktif > 0 ? round(($wargaLunas / $totalWargaAktif) * 100) : 0;

        $saldoKas = Transaksi::where('status_konfirmasi', 'Diterima')->sum('jumlah_bayar');

        // Tunggakan > 3 bulan
        $wargaTunggakan3 = Warga::whereNull('kepala_keluarga_id')
            ->whereHas('tagihan', function ($q) {
                $q->where('status', '!=', 'Lunas');
            }, '>', 3)->count();

        // Perlu Persetujuan
        $suratMenunggu = PengajuanSurat::where('status', 'Menunggu Persetujuan')->count();
        $kegiatanMenunggu = Kegiatan::where('status', 'Menunggu Persetujuan')->count();

        return view('ketua.dashboard', compact(
            'periodeSekarang', 'periodeList', 'dataTren', 'totalWargaAktif',
            'wargaLunas', 'persenKepatuhan', 'saldoKas', 'wargaTunggakan3',
            'suratMenunggu', 'kegiatanMenunggu'
        ));
    }

    // ==========================================
    // 2. MONITORING WARGA
    // ==========================================
    public function monitoring(Request $request)
    {
        $filterTunggakan = $request->query('tunggakan') === '3';

        $query = Warga::whereNull('kepala_keluarga_id')
            ->withCount(['tagihan as jumlah_tunggakan' => fn ($q) => $q->where('status', '!=', 'Lunas')]);

        if ($filterTunggakan) {
            $query->having('jumlah_tunggakan', '>', 3);
        }

        $daftarWarga = $query->orderBy('nama', 'asc')->get();

        return view('ketua.monitoring', compact('daftarWarga', 'filterTunggakan'));
    }

    // ==========================================
    // 3. REKAPITULASI KAS
    // ==========================================
    public function rekap(Request $request)
    {
        $saldoKas = Transaksi::where('status_konfirmasi', 'Diterima')->sum('jumlah_bayar');
        $periode = $request->input('periode', periode_sekarang());

        $transaksiPeriode = Transaksi::with(['warga', 'tagihan'])
            ->where('status_konfirmasi', 'Diterima')
            ->whereHas('tagihan', fn ($q) => $q->where('periode', $periode))
            ->orderBy('tanggal_bayar', 'desc')
            ->get();

        $totalPeriode = $transaksiPeriode->sum('jumlah_bayar');

        $daftarPeriode = Tagihan::select('periode')->distinct()->orderBy('periode', 'desc')->pluck('periode')->toArray();
        if (! in_array($periode, $daftarPeriode)) {
            array_unshift($daftarPeriode, $periode);
        }

        return view('ketua.rekap', compact('saldoKas', 'periode', 'transaksiPeriode', 'totalPeriode', 'daftarPeriode'));
    }

    // ==========================================
    // 4. LAPORAN & VALIDASI
    // ==========================================
    public function laporan()
    {
        $periodes = Tagihan::select('periode')
            ->distinct()
            ->orderBy('periode', 'desc')
            ->pluck('periode');

        $rows = [];
        foreach ($periodes as $p) {
            $totalWarga = Tagihan::where('periode', $p)->count();
            $wargaLunas = Tagihan::where('periode', $p)->where('status', 'Lunas')->count();
            $totalMasuk = Transaksi::where('status_konfirmasi', 'Diterima')
                ->whereHas('tagihan', fn ($q) => $q->where('periode', $p))
                ->sum('jumlah_bayar');

            $validasi = LaporanValidasi::where('periode', $p)->first();

            $rows[] = [
                'periode' => $p,
                'total_warga' => $totalWarga,
                'warga_lunas' => $wargaLunas,
                'total_masuk' => $totalMasuk,
                'status_validasi' => $validasi ? $validasi->status : 'Draft',
                'tanggal_validasi' => $validasi ? $validasi->tanggal_validasi : null,
            ];
        }

        return view('ketua.laporan', compact('rows'));
    }

    public function validasiLaporan(Request $request)
    {
        $validated = $request->validate([
            'periode' => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
        ]);
        $periode = $validated['periode'];

        LaporanValidasi::updateOrCreate(
            ['periode' => $periode],
            [
                'status' => 'Tervalidasi',
                'divalidasi_oleh' => auth()->id(),
                'tanggal_validasi' => now(),
            ]
        );

        return back()->with('success', 'Laporan periode '.periode_label($periode).' berhasil divalidasi.');
    }

    // ==========================================
    // 5. REKAP BANK SAMPAH
    // ==========================================
    public function bankSampah()
    {
        $riwayat = BankSampahSetoran::with(['warga', 'jenis'])
            ->orderBy('created_at', 'desc')
            ->get();
        $totalSetoran = $riwayat->where('status', 'Diterima')->sum('nilai_setoran');
        $totalBerat = $riwayat->where('status', 'Diterima')->sum('berat_final');

        return view('ketua.bank_sampah', compact('riwayat', 'totalSetoran', 'totalBerat'));
    }

    // ==========================================
    // ==========================================
    // 7. REKAP POSYANDU
    // ==========================================
    public function posyandu()
    {
        $jadwal = PosyanduJadwal::withCount('pemeriksaan')->orderBy('tanggal', 'desc')->get();
        $riwayat = PosyanduPemeriksaan::with(['anak.warga', 'jadwal'])->orderBy('created_at', 'desc')->limit(20)->get();

        return view('ketua.posyandu', compact('jadwal', 'riwayat'));
    }

    // ==========================================
    // 8. PERSETUJUAN SURAT
    // ==========================================
    public function surat()
    {
        $daftar = PengajuanSurat::with(['warga', 'pemroses'])
            ->whereIn('status', ['Menunggu Persetujuan', 'Disetujui', 'Ditolak'])
            ->orderByRaw("CASE status WHEN 'Menunggu Persetujuan' THEN 1 WHEN 'Ditolak' THEN 2 WHEN 'Disetujui' THEN 3 ELSE 4 END")
            ->orderBy('created_at', 'asc')
            ->get();

        return view('ketua.surat', compact('daftar'));
    }

    public function suratAction(Request $request, $id)
    {
        $surat = PengajuanSurat::where('id', $id)->where('status', 'Menunggu Persetujuan')->firstOrFail();
        $aksi = $request->input('aksi');
        $catatan = trim($request->input('catatan_ketua', ''));

        if ($aksi === 'setujui') {
            $surat->update([
                'status' => 'Disetujui',
                'catatan_ketua' => $catatan ?: null,
                'disetujui_oleh' => auth()->id(),
                'disetujui_pada' => now(),
            ]);

            return back()->with('success', 'Surat disetujui dan siap dicetak oleh warga maupun admin.');
        } elseif ($aksi === 'tolak') {
            if ($catatan === '') {
                return back()->with('error', 'Tuliskan catatan alasan penolakan.');
            }
            $surat->update([
                'status' => 'Ditolak',
                'catatan_ketua' => $catatan,
                'disetujui_oleh' => auth()->id(),
                'disetujui_pada' => now(),
            ]);

            return back()->with('success', 'Pengajuan surat ditolak.');
        }

        return back();
    }

    // ==========================================
    // 9. INFORMASI & KEGIATAN
    // ==========================================
    public function informasi()
    {
        $kegiatan = Kegiatan::withCount(['peserta as jumlah_peserta' => fn ($q) => $q->where('status', 'Terdaftar')])
            ->orderByRaw("CASE status WHEN 'Menunggu Persetujuan' THEN 1 WHEN 'Dipublikasikan' THEN 2 WHEN 'Draft' THEN 3 WHEN 'Selesai' THEN 4 WHEN 'Dibatalkan' THEN 5 ELSE 6 END")
            ->orderBy('mulai_pada', 'asc')
            ->get();

        $pengumuman = Pengumuman::orderBy('dipublikasikan_pada', 'desc')->limit(10)->get();

        return view('ketua.informasi', compact('kegiatan', 'pengumuman'));
    }

    public function kegiatanAction(Request $request, $id)
    {
        $kegiatan = Kegiatan::where('id', $id)->where('status', 'Menunggu Persetujuan')->firstOrFail();
        $aksi = $request->input('aksi');
        $catatan = trim($request->input('catatan_ketua', ''));

        if ($aksi === 'setujui') {
            $kegiatan->update([
                'status' => 'Dipublikasikan',
                'disetujui_oleh' => auth()->id(),
                'disetujui_pada' => now(),
                'catatan_ketua' => $catatan ?: null,
            ]);

            return back()->with('success', 'Kegiatan disetujui dan otomatis dipublikasikan ke seluruh warga.');
        } elseif ($aksi === 'tolak') {
            $kegiatan->update([
                'status' => 'Dibatalkan',
                'disetujui_oleh' => auth()->id(),
                'disetujui_pada' => now(),
                'catatan_ketua' => $catatan,
            ]);

            return back()->with('success', 'Pengajuan kegiatan ditolak.');
        }

        return back();
    }
}
