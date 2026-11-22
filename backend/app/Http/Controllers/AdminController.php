<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveSuratRequest;
use App\Models\BankSampahJenis;
use App\Models\BankSampahSetoran;
use App\Models\IuranSetting;
use App\Models\InventarisBarang;
use App\Models\InventarisPeminjaman;
use App\Models\Kegiatan;
use App\Models\Pengaduan;
use App\Models\PengajuanSurat;
use App\Models\PengaturanSurat;
use App\Models\Pengumuman;
use App\Models\PosyanduAnak;
use App\Models\PosyanduJadwal;
use App\Models\PosyanduPemeriksaan;
use App\Models\Tagihan;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\Warga;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // ==========================================
    // 1. DASHBOARD
    // ==========================================
    public function dashboard()
    {
        $periode = periode_sekarang();

        $periodeGrafik = [];
        $kasGrafik = [];
        for ($i = 5; $i >= 0; $i--) {
            $periodeItem = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $periodeGrafik[] = periode_label($periodeItem);
            $kasGrafik[] = (float) Transaksi::where('status_konfirmasi', 'Diterima')
                ->whereHas('tagihan', fn ($query) => $query->where('periode', $periodeItem))
                ->sum('jumlah_bayar');
        }

        $grafikKepatuhan = [];
        for ($i = 5; $i >= 0; $i--) {
            $periodeItem = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $total = Tagihan::where('periode', $periodeItem)->count();
            $lunas = Tagihan::where('periode', $periodeItem)->where('status', 'Lunas')->count();
            $grafikKepatuhan[] = [
                'periode' => periode_label($periodeItem),
                'persen' => $total > 0 ? round(($lunas / $total) * 100) : 0,
            ];
        }

        $kasBulanIni = Transaksi::where('status_konfirmasi', 'Diterima')
            ->whereHas('tagihan', fn ($q) => $q->where('periode', $periode))
            ->sum('jumlah_bayar');

        $totalWargaAktif = Warga::where('status_aktif', true)
            ->whereNull('kepala_keluarga_id')
            ->count();
        $wargaLunas = Tagihan::where('periode', $periode)->where('status', 'Lunas')->count();
        $totalTagihanPeriode = Tagihan::where('periode', $periode)->count();
        $menunggu = Transaksi::where('status_konfirmasi', 'Menunggu')->count();
        $saldoKas = Transaksi::where('status_konfirmasi', 'Diterima')->sum('jumlah_bayar');
        $suratMenunggu = PengajuanSurat::where('status', 'Menunggu Persetujuan')->count();
        $kegiatanMenunggu = Kegiatan::where('status', 'Menunggu Persetujuan')->count();

        $antrianKonfirmasi = Transaksi::with(['warga', 'tagihan'])
            ->where('status_konfirmasi', 'Menunggu')
            ->orderBy('tanggal_bayar', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'periode', 'kasBulanIni', 'totalWargaAktif', 'wargaLunas',
            'totalTagihanPeriode', 'menunggu', 'antrianKonfirmasi',
            'periodeGrafik', 'kasGrafik', 'grafikKepatuhan',
            'saldoKas', 'suratMenunggu', 'kegiatanMenunggu'
        ));
    }

    public function generateTagihan(Request $request)
    {
        $periode = periode_sekarang();
        $totalIuran = IuranSetting::where('status_aktif', true)->sum('nominal');

        if ($totalIuran <= 0) {
            return back()->with('error', 'Tidak ada jenis iuran yang aktif saat ini.');
        }

        $wargaAktif = Warga::where('status_aktif', true)
            ->whereNull('kepala_keluarga_id')
            ->get();
        $dibuat = DB::transaction(function () use ($wargaAktif, $periode, $totalIuran) {
            $count = 0;
            foreach ($wargaAktif as $w) {
                $ada = Tagihan::where('warga_id', $w->id)->where('periode', $periode)->exists();
                if (! $ada) {
                    Tagihan::create([
                        'warga_id' => $w->id,
                        'periode' => $periode,
                        'total_tagihan' => $totalIuran,
                        'status' => 'Belum Bayar',
                    ]);
                    $count++;
                }
            }

            return $count;
        });

        return back()->with('success', $dibuat > 0
            ? "Berhasil membuat $dibuat tagihan baru untuk periode ".periode_label($periode).'.'
            : 'Tagihan periode '.periode_label($periode).' sudah lengkap untuk seluruh warga aktif.'
        );
    }

    // ==========================================
    // 2. KELOLA WARGA
    // ==========================================
    public function warga()
    {
        $daftarWarga = Warga::with('anggotaKeluarga')
            ->whereNull('kepala_keluarga_id')
            ->orderBy('no_rumah')
            ->orderBy('nama')
            ->get();

        return view('admin.warga', compact('daftarWarga'));
    }

    public function wargaStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nik' => $this->nikRules(),
            'no_rumah' => [
                'required',
                'string',
                'max:20',
                Rule::unique('warga', 'no_rumah')
                    ->where(fn ($query) => $query->whereNull('kepala_keluarga_id')),
            ],
            'alamat' => 'nullable|string|max:150',
            'no_hp' => 'nullable|string|max:20',
            'status_hunian' => 'required|in:Tetap,Kontrak',
            'status_aktif' => 'nullable',
            'anggota' => ['required', 'array', 'min:1', 'max:4'],
            'anggota.*' => 'array:nama,nik',
            'anggota.*.nama' => ['required', 'string', 'max:100'],
            'anggota.*.nik' => [
                'required',
                'digits:16',
                'distinct',
                function (string $attribute, mixed $value, Closure $fail) use ($request): void {
                    if ($value === $request->input('nik')) {
                        $fail('NIK anggota tidak boleh sama dengan NIK kepala keluarga.');
                    }
                },
                ...$this->nikRules(),
            ],
        ], [
            'no_rumah.required' => 'Nomor rumah wajib diisi untuk membuat keluarga.',
            'no_rumah.unique' => 'Nomor rumah ini sudah memiliki kepala keluarga.',
            'nik.required' => 'NIK kepala keluarga wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 angka.',
            'anggota.max' => 'Maksimal 4 anggota keluarga tambahan.',
            'anggota.required' => 'Setiap rumah minimal terdiri dari kepala keluarga dan satu anggota.',
            'anggota.min' => 'Setiap rumah minimal terdiri dari kepala keluarga dan satu anggota.',
            'anggota.*.nama.required' => 'Nama anggota wajib diisi.',
            'anggota.*.nik.required' => 'NIK anggota wajib diisi.',
            'anggota.*.nik.digits' => 'NIK anggota harus terdiri dari 16 angka.',
            'anggota.*.nik.distinct' => 'NIK anggota tidak boleh sama.',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $kepala = Warga::create([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'no_rumah' => $validated['no_rumah'],
                'alamat' => $validated['alamat'] ?? null,
                'no_hp' => $validated['no_hp'] ?? null,
                'status_hunian' => $validated['status_hunian'],
                'status_aktif' => $request->boolean('status_aktif'),
                'status_pendaftaran' => 'Disetujui',
            ]);
            $this->saveNikHash($kepala, $validated['nik']);
            self::ensureUserForWarga($kepala);

            foreach ($validated['anggota'] ?? [] as $anggota) {
                $warga = Warga::create([
                    'nama' => $anggota['nama'],
                    'nik' => $anggota['nik'],
                    'no_rumah' => $kepala->no_rumah,
                    'alamat' => $kepala->alamat,
                    'no_hp' => $anggota['no_hp'] ?? null,
                    'status_hunian' => $kepala->status_hunian,
                    'status_aktif' => $kepala->status_aktif,
                    'status_pendaftaran' => 'Disetujui',
                    'kepala_keluarga_id' => $kepala->id,
                ]);
                $this->saveNikHash($warga, $anggota['nik']);
                self::ensureUserForWarga($warga);
            }
        });

        return back()->with('success', 'Data keluarga berhasil ditambahkan.');
    }

    public function wargaAnggotaStore(Request $request, $id)
    {
        $kepala = Warga::whereNull('kepala_keluarga_id')->findOrFail($id);

        if (! $kepala->no_rumah) {
            return back()->with('error', 'Lengkapi nomor rumah kepala keluarga sebelum menambahkan anggota.');
        }

        if ($kepala->anggotaKeluarga()->count() >= 4) {
            return back()->with('error', 'Setiap keluarga maksimal memiliki 4 anggota tambahan.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'nik' => $this->nikRules(),
            'no_hp' => 'nullable|string|max:20',
        ], [
            'nik.required' => 'NIK anggota keluarga wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 angka.',
        ]);

        DB::transaction(function () use ($validated, $kepala) {
            $anggota = Warga::create([
                'nama' => $validated['nama'],
                'nik' => $validated['nik'],
                'no_rumah' => $kepala->no_rumah,
                'alamat' => $kepala->alamat,
                'no_hp' => $validated['no_hp'] ?? null,
                'status_hunian' => $kepala->status_hunian,
                'status_aktif' => $kepala->status_aktif,
                'status_pendaftaran' => 'Disetujui',
                'kepala_keluarga_id' => $kepala->id,
            ]);
            $this->saveNikHash($anggota, $validated['nik']);
            self::ensureUserForWarga($anggota);
        });

        return back()->with('success', 'Anggota keluarga berhasil ditambahkan.');
    }

    public static function ensureUserForWarga(Warga $warga): User
    {
        if ($warga->user) {
            return $warga->user;
        }

        $cleanName = trim($warga->nama);
        $base = \Illuminate\Support\Str::slug($cleanName, '_');
        if (empty($base)) {
            $base = 'warga_' . $warga->id;
        }
        $username = $base;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }

        return User::create([
            'nama' => $warga->nama,
            'username' => $username,
            'password' => \Illuminate\Support\Facades\Hash::make('warga123'),
            'role' => 'warga',
            'warga_id' => $warga->id,
            'rfid_is_active' => true,
        ]);
    }

    public function wargaUpdate(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);

        $rules = [
            'nama' => 'required|string|max:100',
            'nik' => $this->nikRules($warga),
            'no_hp' => 'nullable|string|max:20',
            'status_aktif' => 'nullable|boolean',
        ];

        if (! $warga->kepala_keluarga_id) {
            $rules += [
                'no_rumah' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('warga', 'no_rumah')
                        ->where(fn ($query) => $query->whereNull('kepala_keluarga_id'))
                        ->ignore($warga),
                ],
                'alamat' => 'nullable|string|max:150',
                'status_hunian' => 'required|in:Tetap,Kontrak',
            ];
        }

        $validated = $request->validate($rules, [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus terdiri dari 16 angka.',
            'no_rumah.unique' => 'Nomor rumah ini sudah memiliki kepala keluarga.',
        ]);

        DB::transaction(function () use ($request, $validated, $warga) {
            $warga->nama = $validated['nama'];
            $warga->nik = $validated['nik'];
            $warga->no_hp = $validated['no_hp'] ?? null;
            $warga->status_aktif = $request->boolean('status_aktif');

            if (! $warga->kepala_keluarga_id) {
                $warga->no_rumah = $validated['no_rumah'] ?? null;
                $warga->alamat = $validated['alamat'] ?? null;
                $warga->status_hunian = $validated['status_hunian'];
            }

            $this->saveNikHash($warga, $validated['nik']);

            if (! $warga->kepala_keluarga_id) {
                $warga->anggotaKeluarga()->update([
                    'no_rumah' => $warga->no_rumah,
                    'alamat' => $warga->alamat,
                    'status_hunian' => $warga->status_hunian,
                ]);
            }
        });

        return back()->with('success', 'Data warga berhasil diperbarui.');
    }

    public function wargaDestroy($id)
    {
        $warga = Warga::findOrFail($id);

        $kepalaKeluarga = $warga->kepalaKeluarga;
        if ($kepalaKeluarga && $kepalaKeluarga->anggotaKeluarga()->count() === 1) {
            return back()->with('error', 'Setiap rumah harus memiliki minimal 2 warga. Tambahkan anggota lain sebelum menghapus warga ini.');
        }

        if ($warga->tagihan()->exists()) {
            return back()->with('error', 'Warga tidak bisa dihapus karena sudah memiliki riwayat tagihan. Nonaktifkan saja statusnya.');
        }

        if ($warga->anggotaKeluarga()->exists()) {
            return back()->with('error', 'Kepala keluarga tidak bisa dihapus selama masih memiliki anggota keluarga.');
        }

        $warga->delete();

        return back()->with('success', 'Data warga berhasil dihapus.');
    }

    /**
     * @return array<int, string|Closure>
     */
    private function nikRules(?Warga $warga = null, bool $required = true): array
    {
        return [
            $required ? 'required' : 'nullable',
            'digits:16',
            function (string $attribute, mixed $value, Closure $fail) use ($warga): void {
                $query = Warga::where('nik_hash', Warga::nikHash((string) $value));

                if ($warga) {
                    $query->where('id', '!=', $warga->id);
                }

                if ($query->exists()) {
                    $fail('NIK ini sudah terdaftar.');
                }
            },
        ];
    }

    private function saveNikHash(Warga $warga, string $nik): void
    {
        $warga->nik_hash = Warga::nikHash($nik);
        $warga->save();
    }

    // ==========================================
    // 3. PERSETUJUAN WARGA BARU
    // ==========================================
    public function persetujuan()
    {
        $menunggu = Warga::with('user')
            ->where('status_pendaftaran', 'Menunggu Persetujuan')
            ->orderBy('created_at', 'asc')
            ->get();

        $riwayat = Warga::with('user')
            ->whereIn('status_pendaftaran', ['Disetujui', 'Ditolak'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.persetujuan', compact('menunggu', 'riwayat'));
    }

    public function persetujuanAction(Request $request, $id)
    {
        $warga = Warga::findOrFail($id);
        $aksi = $request->input('aksi');

        if ($aksi === 'setujui') {
            $warga->update([
                'status_pendaftaran' => 'Disetujui',
                'status_aktif' => true,
            ]);

            return back()->with('success', 'Pendaftaran warga berhasil disetujui. Akun sudah dapat digunakan untuk login.');
        } elseif ($aksi === 'tolak') {
            $warga->update([
                'status_pendaftaran' => 'Ditolak',
                'status_aktif' => false,
            ]);

            return back()->with('success', 'Pendaftaran warga ditolak.');
        }

        return back();
    }

    // ==========================================
    // 4. MANAJEMEN IURAN
    // ==========================================
    public function iuran()
    {
        $daftarIuran = IuranSetting::orderBy('status_aktif', 'desc')->orderBy('nama_iuran', 'asc')->get();
        $totalAktif = $daftarIuran->where('status_aktif', true)->sum('nominal');
        $grafikIuran = $daftarIuran->map(fn ($iuran) => [
            'nama' => $iuran->nama_iuran,
            'nominal' => (float) $iuran->nominal,
            'aktif' => (bool) $iuran->status_aktif,
        ]);

        return view('admin.iuran', compact('daftarIuran', 'totalAktif', 'grafikIuran'));
    }

    public function iuranStore(Request $request)
    {
        $validated = $request->validate([
            'nama_iuran' => 'required|string|max:50',
            'nominal' => 'required',
            'status_aktif' => 'nullable',
        ]);

        $nominal = (float) str_replace(['.', ','], ['', '.'], $validated['nominal']);

        IuranSetting::create([
            'nama_iuran' => $validated['nama_iuran'],
            'nominal' => $nominal,
            'status_aktif' => $request->has('status_aktif'),
        ]);

        return back()->with('success', 'Jenis iuran baru berhasil ditambahkan.');
    }

    public function iuranUpdate(Request $request, $id)
    {
        $iuran = IuranSetting::findOrFail($id);

        $validated = $request->validate([
            'nama_iuran' => 'required|string|max:50',
            'nominal' => 'required',
            'status_aktif' => 'nullable',
        ]);

        $nominal = (float) str_replace(['.', ','], ['', '.'], $validated['nominal']);

        $iuran->update([
            'nama_iuran' => $validated['nama_iuran'],
            'nominal' => $nominal,
            'status_aktif' => $request->has('status_aktif'),
        ]);

        return back()->with('success', 'Jenis iuran berhasil diperbarui.');
    }

    public function iuranDestroy($id)
    {
        IuranSetting::findOrFail($id)->delete();

        return back()->with('success', 'Jenis iuran berhasil dihapus.');
    }

    // ==========================================
    // 5. TRANSAKSI MANUAL (TUNAI)
    // ==========================================
    public function transaksi(Request $request)
    {
        $daftarWargaBelumLunas = Warga::whereHas('tagihan', fn ($q) => $q->where('status', '!=', 'Lunas'))
            ->orderBy('nama', 'asc')
            ->get();

        $wargaId = $request->input('warga_id');
        $tagihanBelumLunas = [];
        $wargaTerpilih = null;

        if ($wargaId) {
            $wargaTerpilih = Warga::find($wargaId);
            if ($wargaTerpilih) {
                $tagihanBelumLunas = Tagihan::where('warga_id', $wargaId)
                    ->where('status', '!=', 'Lunas')
                    ->orderBy('periode', 'asc')
                    ->get();
            }
        }

        return view('admin.transaksi', compact('daftarWargaBelumLunas', 'wargaTerpilih', 'tagihanBelumLunas', 'wargaId'));
    }

    public function transaksiStore(Request $request)
    {
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihan,id',
            'jumlah_bayar' => 'required',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tagihan = Tagihan::findOrFail($validated['tagihan_id']);
        $jumlah = (float) str_replace(['.', ','], ['', '.'], $validated['jumlah_bayar']);

        DB::transaction(function () use ($tagihan, $jumlah, $validated) {
            Transaksi::create([
                'tagihan_id' => $tagihan->id,
                'warga_id' => $tagihan->warga_id,
                'jumlah_bayar' => $jumlah,
                'metode' => 'Tunai',
                'keterangan' => $validated['keterangan'] ?? null,
                'status_konfirmasi' => 'Diterima',
                'dikonfirmasi_oleh' => auth()->id(),
                'tanggal_konfirmasi' => now(),
            ]);

            $tagihan->update(['status' => 'Lunas']);
        });

        return redirect()->route('admin.transaksi', ['warga_id' => $tagihan->warga_id])
            ->with('success', 'Pembayaran tunai berhasil dicatat dan tagihan berstatus Lunas.');
    }

    // ==========================================
    // 6. KONFIRMASI PEMBAYARAN TRANSFER
    // ==========================================
    public function konfirmasi()
    {
        $antrian = Transaksi::with(['warga', 'tagihan'])
            ->where('status_konfirmasi', 'Menunggu')
            ->orderBy('tanggal_bayar', 'asc')
            ->get();

        $riwayat = Transaksi::with(['warga', 'tagihan'])
            ->where('status_konfirmasi', '!=', 'Menunggu')
            ->where('metode', 'Transfer')
            ->orderBy('tanggal_konfirmasi', 'desc')
            ->limit(10)
            ->get();

        return view('admin.konfirmasi', compact('antrian', 'riwayat'));
    }

    public function konfirmasiAction(Request $request, $id)
    {
        $trx = Transaksi::findOrFail($id);
        $aksi = $request->input('aksi');

        DB::transaction(function () use ($trx, $aksi, $request) {
            if ($aksi === 'terima') {
                $trx->update([
                    'status_konfirmasi' => 'Diterima',
                    'dikonfirmasi_oleh' => auth()->id(),
                    'tanggal_konfirmasi' => now(),
                ]);
                $trx->tagihan->update(['status' => 'Lunas']);
            } elseif ($aksi === 'tolak') {
                $trx->update([
                    'status_konfirmasi' => 'Ditolak',
                    'dikonfirmasi_oleh' => auth()->id(),
                    'tanggal_konfirmasi' => now(),
                    'catatan_admin' => $request->input('catatan'),
                ]);
                $trx->tagihan->update(['status' => 'Belum Bayar']);
            }
        });

        return back()->with('success', $aksi === 'terima'
            ? 'Pembayaran berhasil dikonfirmasi. Tagihan berstatus Lunas.'
            : 'Pembayaran ditolak. Warga dapat mengunggah ulang bukti transfer.');
    }

    // ==========================================
    // 7. LAPORAN KEUANGAN
    // ==========================================
    public function laporan(Request $request)
    {
        $periode = $request->input('periode', periode_sekarang());

        $transaksiPeriode = Transaksi::with(['warga', 'tagihan'])
            ->where('status_konfirmasi', 'Diterima')
            ->whereHas('tagihan', fn ($q) => $q->where('periode', $periode))
            ->orderBy('tanggal_bayar', 'asc')
            ->get();

        if ($request->input('export') === 'csv') {
            $filename = 'laporan_iuran_'.$periode.'.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];
            $callback = function () use ($transaksiPeriode) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Nama Warga', 'No. Rumah', 'Jumlah Bayar', 'Metode', 'Tanggal Bayar', 'Keterangan']);
                foreach ($transaksiPeriode as $t) {
                    fputcsv($file, [
                        $t->warga->nama ?? '-',
                        $t->warga->no_rumah ?? '-',
                        $t->jumlah_bayar,
                        $t->metode,
                        format_tanggal($t->tanggal_bayar, true),
                        $t->keterangan ?? '-',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        $totalMasuk = $transaksiPeriode->sum('jumlah_bayar');
        $totalTagihan = Tagihan::where('periode', $periode)->count();
        $totalLunas = Tagihan::where('periode', $periode)->where('status', 'Lunas')->count();

        $daftarPeriode = Tagihan::select('periode')->distinct()->orderBy('periode', 'desc')->pluck('periode')->toArray();
        if (! in_array($periode, $daftarPeriode)) {
            array_unshift($daftarPeriode, $periode);
        }

        $periodeGrafik = [];
        $pemasukanGrafik = [];
        $kepatuhanGrafik = [];
        for ($i = 5; $i >= 0; $i--) {
            $periodeItem = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $periodeGrafik[] = periode_label($periodeItem);
            $pemasukanGrafik[] = (float) Transaksi::where('status_konfirmasi', 'Diterima')
                ->whereHas('tagihan', fn ($query) => $query->where('periode', $periodeItem))
                ->sum('jumlah_bayar');

            $totalPeriode = Tagihan::where('periode', $periodeItem)->count();
            $lunasPeriode = Tagihan::where('periode', $periodeItem)->where('status', 'Lunas')->count();
            $kepatuhanGrafik[] = $totalPeriode > 0 ? round(($lunasPeriode / $totalPeriode) * 100) : 0;
        }

        return view('admin.laporan', compact(
            'periode', 'transaksiPeriode', 'totalMasuk', 'totalTagihan', 'totalLunas',
            'daftarPeriode', 'periodeGrafik', 'pemasukanGrafik', 'kepatuhanGrafik'
        ));
    }

    // ==========================================
    // 8. KELOLA USERS
    // ==========================================
    public function users()
    {
        $daftarUser = User::with('warga')
            ->orderByRaw("CASE role 
                WHEN 'super_admin' THEN 1 
                WHEN 'rt' THEN 2 
                WHEN 'ketua' THEN 2 
                WHEN 'bendahara_rt' THEN 3 
                WHEN 'bendahara' THEN 3 
                WHEN 'admin' THEN 3 
                WHEN 'sekretaris_rt' THEN 4 
                WHEN 'rw' THEN 5 
                WHEN 'bendahara_rw' THEN 6 
                WHEN 'sekretaris_rw' THEN 7 
                WHEN 'warga' THEN 8 
                ELSE 9 END")
            ->orderBy('nama')
            ->get();
        $wargaTanpaAkun = Warga::doesntHave('user')->where('status_aktif', true)->orderBy('nama')->get();

        return view('admin.users', compact('daftarUser', 'wargaTanpaAkun'));
    }

    public function usersStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'role' => 'required|in:super_admin,rt,ketua,bendahara_rt,bendahara,admin,sekretaris_rt,rw,bendahara_rw,sekretaris_rw,warga',
            'warga_id' => 'nullable|exists:warga,id',
        ]);

        if ($validated['role'] === 'warga' && empty($validated['warga_id'])) {
            return back()->with('error', 'Pilih data warga yang akan dihubungkan dengan akun warga.');
        }

        User::create([
            'nama' => $validated['nama'],
            'username' => $validated['username'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'warga_id' => $validated['role'] === 'warga' ? $validated['warga_id'] : null,
        ]);

        return back()->with('success', 'Akun baru berhasil dibuat.');
    }

    public function usersResetPassword(Request $request, $id)
    {
        $request->validate(['password_baru' => 'required|string|min:6']);
        $user = User::findOrFail($id);
        $user->update(['password' => $request->password_baru]);

        return back()->with('success', 'Password akun berhasil direset.');
    }

    public function usersDestroy($id)
    {
        if ((int) $id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        User::findOrFail($id)->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }

    // ==========================================
    // 9. BANK SAMPAH
    // ==========================================
    public function bankSampah()
    {
        $jenis = BankSampahJenis::orderBy('nama')->get();
        $antrian = BankSampahSetoran::with(['warga', 'jenis'])
            ->where('status', 'Menunggu Validasi')
            ->orderBy('created_at')
            ->get();
        $riwayat = BankSampahSetoran::with(['warga', 'jenis'])
            ->orderBy('created_at', 'desc')
            ->get();
        $daftarWarga = Warga::where('status_aktif', true)
            ->orderBy('nama')
            ->get();
        $totalBerat = $riwayat
            ->where('status', 'Diterima')
            ->sum(fn ($setoran) => (float) ($setoran->berat_final ?? $setoran->berat_estimasi));
        $totalNilai = $riwayat
            ->where('status', 'Diterima')
            ->sum('nilai_setoran');
        $kategoriSampah = [
            'Organik' => 'Sisa makanan, daun, kulit buah',
            'Plastik' => 'Botol PET, Gelas plastik, Kemasan',
            'Kertas' => 'Kardus, Koran, Buku bekas',
            'Logam' => 'Kaleng, Besi, Aluminium',
            'Kaca' => 'Botol kaca, Pecahan kaca',
            'Tekstil' => 'Pakaian, Kain bekas',
            'Elektronik' => 'Kabel, HP rusak, Perangkat kecil',
            'Lainnya' => 'Sampah yang belum masuk kategori',
        ];

        return view('admin.bank_sampah', compact(
            'jenis',
            'antrian',
            'riwayat',
            'daftarWarga',
            'totalBerat',
            'totalNilai',
            'kategoriSampah'
        ));
    }

    public function bankSampahHarga(): View
    {
        $jenis = BankSampahJenis::orderBy('kategori')->orderBy('nama')->get();
        $kategoriSampah = [
            'Organik' => 'Sisa makanan, Daun, Kulit buah',
            'Plastik' => 'Botol PET, Gelas plastik, Kemasan',
            'Kertas' => 'Kardus, Koran, Buku bekas',
            'Logam' => 'Kaleng, Besi, Aluminium',
            'Kaca' => 'Botol kaca, Pecahan kaca',
            'Tekstil' => 'Pakaian, Kain bekas',
            'Elektronik' => 'Kabel, HP rusak, Perangkat kecil',
            'Lainnya' => 'Sampah yang belum masuk kategori',
        ];

        return view('admin.bank_sampah_harga', compact('jenis', 'kategoriSampah'));
    }

    public function bankSampahHargaStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'required|string|in:Organik,Plastik,Kertas,Logam,Kaca,Tekstil,Elektronik,Lainnya',
            'contoh_sampah' => 'nullable|string|max:255',
            'harga_per_kg' => 'required|numeric|min:0',
        ]);

        BankSampahJenis::create([
            ...$validated,
            'status_aktif' => true,
        ]);

        return back()->with('success', 'Jenis sampah dan tarif berhasil ditambahkan.');
    }

    public function bankSampahHargaUpdate(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'harga_per_kg' => 'required|numeric|min:0',
        ]);

        $jenis = BankSampahJenis::findOrFail($id);
        $jenis->update(['harga_per_kg' => $validated['harga_per_kg']]);

        return back()->with('success', 'Tarif sampah berhasil diperbarui.');
    }

    public function bankSampahSetoranStore(Request $request)
    {
        $validated = $request->validate([
            'warga_id' => 'required|exists:warga,id',
            'jenis_id' => 'required|exists:bank_sampah_jenis,id',
            'berat' => 'required|numeric|min:0.01',
            'catatan' => 'nullable|string|max:255',
        ]);

        $jenis = BankSampahJenis::findOrFail($validated['jenis_id']);
        $berat = (float) $validated['berat'];

        BankSampahSetoran::create([
            'warga_id' => $validated['warga_id'],
            'jenis_id' => $jenis->id,
            'berat_estimasi' => $berat,
            'berat_final' => $berat,
            'harga_per_kg' => $jenis->harga_per_kg,
            'nilai_setoran' => $berat * $jenis->harga_per_kg,
            'status' => 'Diterima',
            'catatan' => $validated['catatan'] ?? null,
            'diproses_oleh' => auth()->id(),
            'diproses_pada' => now(),
        ]);

        return back()->with('success', 'Setoran bank sampah berhasil dicatat.');
    }

    public function bankSampahJenisStore(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'required|string|in:Organik,Plastik,Kertas,Logam,Kaca,Tekstil,Elektronik,Lainnya',
            'contoh_sampah' => 'nullable|string|max:255',
            'harga_per_kg' => 'required|numeric|min:0',
        ]);

        BankSampahJenis::create($validated);

        return back()->with('success', 'Jenis sampah berhasil ditambahkan.');
    }

    public function bankSampahSetoranAction(Request $request, $id)
    {
        $setoran = BankSampahSetoran::with('jenis')->findOrFail($id);
        $aksi = $request->input('aksi');

        if ($aksi === 'terima') {
            $beratFinal = (float) $request->input('berat_final');
            if ($beratFinal <= 0) {
                return back()->with('error', 'Berat final harus lebih dari 0.');
            }
            $hargaPerKg = (float) ($setoran->harga_per_kg ?? $setoran->jenis->harga_per_kg);
            $nilai = $beratFinal * $hargaPerKg;

            $setoran->update([
                'status' => 'Diterima',
                'berat_final' => $beratFinal,
                'nilai_setoran' => $nilai,
                'diproses_oleh' => auth()->id(),
                'diproses_pada' => now(),
            ]);

            return back()->with('success', 'Setoran bank sampah diterima.');
        } elseif ($aksi === 'tolak') {
            $setoran->update([
                'status' => 'Ditolak',
                'catatan' => $request->input('catatan'),
                'diproses_oleh' => auth()->id(),
                'diproses_pada' => now(),
            ]);

            return back()->with('success', 'Setoran bank sampah ditolak.');
        }

        return back();
    }

    // ==========================================
    // 10. PENGADUAN
    // ==========================================
    public function pengaduan(): View
    {
        $pengaduan = Pengaduan::with(['warga', 'pemroses'])
            ->latest()
            ->get();

        return view('admin.pengaduan', compact('pengaduan'));
    }

    public function pengaduanAction(Request $request, Pengaduan $pengaduan): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:Diajukan,Diproses,Diterima,Ditolak,Selesai'],
            'tanggapan' => ['nullable', 'string', 'max:1000'],
        ]);

        $pengaduan->update([
            'status' => $validated['status'],
            'tanggapan' => $validated['tanggapan'] ?? null,
            'diproses_oleh' => auth()->id(),
            'diproses_pada' => now(),
        ]);

        return back()->with('success', 'Status pengaduan berhasil diperbarui.');
    }

    public function inventaris(): View
    {
        $barang = InventarisBarang::withCount([
            'peminjaman as peminjaman_aktif_count' => fn (Builder $query) => $query->whereIn('status', ['Diajukan', 'Dipinjam']),
        ])
            ->orderBy('nama')
            ->get();
        $peminjaman = InventarisPeminjaman::with(['barang', 'warga', 'pemroses'])
            ->latest()
            ->get();

        $ringkasan = [
            'jenis' => $barang->count(),
            'baik' => $barang->where('kondisi', 'Baik')->count(),
            'perbaikan' => $barang->where('kondisi', 'Perlu Perbaikan')->sum('jumlah_perbaikan'),
            'dipinjam' => $peminjaman->where('status', 'Dipinjam')->sum('jumlah'),
        ];

        return view('admin.inventaris', compact('barang', 'peminjaman', 'ringkasan'));
    }

    public function inventarisBarangStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'jumlah_total' => ['required', 'integer', 'min:1'],
            'kondisi' => ['required', 'in:Baik,Perlu Perbaikan,Rusak'],
        ]);

        InventarisBarang::create([
            ...$validated,
            'jumlah_tersedia' => $validated['kondisi'] === 'Baik' ? $validated['jumlah_total'] : 0,
            'jumlah_perbaikan' => $validated['kondisi'] === 'Perlu Perbaikan' ? $validated['jumlah_total'] : 0,
        ]);

        return back()->with('success', 'Barang inventaris berhasil ditambahkan.');
    }

    public function inventarisBarangUpdate(Request $request, InventarisBarang $barang): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'jumlah_total' => ['required', 'integer', 'min:1'],
            'kondisi' => ['required', 'in:Baik,Perlu Perbaikan,Rusak'],
        ]);

        $dipinjam = $barang->peminjaman()->where('status', 'Dipinjam')->sum('jumlah');
        $perbaikan = $validated['kondisi'] === 'Perlu Perbaikan' ? $validated['jumlah_total'] : 0;
        $tersedia = max(0, $validated['jumlah_total'] - $dipinjam - $perbaikan);

        $barang->update([
            ...$validated,
            'jumlah_tersedia' => $tersedia,
            'jumlah_perbaikan' => $perbaikan,
        ]);

        return back()->with('success', 'Data barang inventaris berhasil diperbarui.');
    }

    public function inventarisBarangDestroy(InventarisBarang $barang): RedirectResponse
    {
        if ($barang->peminjaman()->where('status', 'Dipinjam')->exists()) {
            return back()->with('error', 'Barang tidak dapat dihapus karena sedang dipinjam.');
        }

        $barang->delete();

        return back()->with('success', 'Barang inventaris berhasil dihapus.');
    }

    public function inventarisPeminjamanAction(Request $request, InventarisPeminjaman $peminjaman): RedirectResponse
    {
        $validated = $request->validate([
            'aksi' => ['required', 'in:setujui,tolak,kembalikan'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'foto_kembali' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $barang = InventarisBarang::findOrFail($peminjaman->barang_id);

        if ($validated['aksi'] === 'setujui' && $peminjaman->status === 'Diajukan') {
            if ($barang->jumlah_tersedia < $peminjaman->jumlah) {
                return back()->with('error', 'Jumlah barang tersedia tidak mencukupi.');
            }

            DB::transaction(function () use ($peminjaman, $barang, $validated) {
                $barang->decrement('jumlah_tersedia', $peminjaman->jumlah);
                $peminjaman->update([
                    'status' => 'Dipinjam',
                    'catatan' => $validated['catatan'] ?? $peminjaman->catatan,
                    'diproses_oleh' => auth()->id(),
                ]);
            });

            return back()->with('success', 'Peminjaman disetujui.');
        }

        if ($validated['aksi'] === 'tolak' && $peminjaman->status === 'Diajukan') {
            $peminjaman->update([
                'status' => 'Ditolak',
                'catatan' => $validated['catatan'] ?? null,
                'diproses_oleh' => auth()->id(),
            ]);

            return back()->with('success', 'Peminjaman ditolak.');
        }

        if ($validated['aksi'] === 'kembalikan' && $peminjaman->status === 'Dipinjam') {
            $fotoKembali = null;
            if ($request->hasFile('foto_kembali')) {
                $fotoKembali = $request->file('foto_kembali')->store('inventaris/pengembalian', 'public');
            }

            DB::transaction(function () use ($peminjaman, $barang, $validated, $fotoKembali) {
                $barang->increment('jumlah_tersedia', $peminjaman->jumlah);
                $peminjaman->update([
                    'status' => 'Dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                    'catatan' => $validated['catatan'] ?? $peminjaman->catatan,
                    'foto_kembali' => $fotoKembali,
                    'diproses_oleh' => auth()->id(),
                ]);
            });

            return back()->with('success', 'Pengembalian berhasil dicatat.');
        }

        return back();
    }

    // ==========================================
    // 11. POSYANDU
    // ==========================================
    public function posyandu()
    {
        $jadwal = PosyanduJadwal::where('status', 'Terjadwal')->orderBy('tanggal', 'desc')->get();
        $anak = PosyanduAnak::with('warga')->orderBy('nama')->get();

        return view('admin.posyandu', compact('jadwal', 'anak'));
    }

    public function posyanduJadwalStore(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:150',
            'tanggal' => 'required|date',
            'lokasi' => 'required|string|max:150',
            'catatan' => 'nullable|string',
        ]);

        PosyanduJadwal::create([
            'judul' => $validated['judul'],
            'tanggal' => $validated['tanggal'],
            'lokasi' => $validated['lokasi'],
            'catatan' => $validated['catatan'] ?? null,
            'dibuat_oleh' => auth()->id(),
        ]);

        return back()->with('success', 'Jadwal posyandu berhasil dibuat.');
    }

    public function posyanduPemeriksaanStore(Request $request)
    {
        $validated = $request->validate([
            'jadwal_id' => 'required|exists:posyandu_jadwal,id',
            'anak_id' => 'required|exists:posyandu_anak,id',
            'berat_kg' => 'nullable|numeric',
            'tinggi_cm' => 'nullable|numeric',
            'lingkar_kepala_cm' => 'nullable|numeric',
            'imunisasi' => 'nullable|string|max:150',
            'catatan' => 'nullable|string',
        ]);

        PosyanduPemeriksaan::updateOrCreate(
            ['jadwal_id' => $validated['jadwal_id'], 'anak_id' => $validated['anak_id']],
            [
                'berat_kg' => $validated['berat_kg'] ?? null,
                'tinggi_cm' => $validated['tinggi_cm'] ?? null,
                'lingkar_kepala_cm' => $validated['lingkar_kepala_cm'] ?? null,
                'imunisasi' => $validated['imunisasi'] ?? null,
                'catatan' => $validated['catatan'] ?? null,
                'dicatat_oleh' => auth()->id(),
            ]
        );

        return back()->with('success', 'Hasil pemeriksaan posyandu anak berhasil disimpan.');
    }

    // ==========================================
    // 12. LAYANAN SURAT
    // ==========================================
    public function surat(Request $request): View
    {
        $statusOptions = [
            PengajuanSurat::STATUS_DIAJUKAN,
            PengajuanSurat::STATUS_DIPROSES,
            PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN,
            PengajuanSurat::STATUS_DISETUJUI,
            PengajuanSurat::STATUS_DITOLAK,
        ];
        $search = trim($request->string('q')->toString());
        $status = $request->string('status')->toString();
        $perPageInput = $request->string('per_page', '10')->toString();
        $perPage = in_array($perPageInput, ['10', '25', '50'], true) ? (int) $perPageInput : 10;

        if (! in_array($status, $statusOptions, true)) {
            $status = '';
        }

        $query = PengajuanSurat::query()
            ->with('warga:id,nama,no_rumah,alamat')
            ->when($search !== '', function (Builder $query) use ($search): void {
                $pattern = '%'.$search.'%';

                $query->where(function (Builder $query) use ($pattern): void {
                    $query->where('nama_pemohon', 'like', $pattern)
                        ->orWhere('jenis_surat', 'like', $pattern)
                        ->orWhere('keperluan', 'like', $pattern)
                        ->orWhere('nomor_surat', 'like', $pattern)
                        ->orWhereHas('warga', function (Builder $wargaQuery) use ($pattern): void {
                            $wargaQuery->where('nama', 'like', $pattern);
                        });
                });
            });

        if ($status !== '') {
            $query->where('status', $status);
        }

        $daftar = $query->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        $ringkasan = [
            'total' => PengajuanSurat::count(),
            'menunggu' => PengajuanSurat::whereIn('status', [
                PengajuanSurat::STATUS_DIAJUKAN,
                PengajuanSurat::STATUS_DIPROSES,
                PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN,
            ])->count(),
            'disetujui' => PengajuanSurat::where('status', PengajuanSurat::STATUS_DISETUJUI)->count(),
            'ditolak' => PengajuanSurat::where('status', PengajuanSurat::STATUS_DITOLAK)->count(),
        ];
        $wargaTersedia = Warga::query()
            ->where('status_aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'no_rumah']);
        $pengaturanSurat = PengaturanSurat::query()->find(1);

        return view('admin.surat', compact(
            'daftar',
            'ringkasan',
            'wargaTersedia',
            'pengaturanSurat',
            'statusOptions',
            'search',
            'status',
            'perPage'
        ));
    }

    public function suratStore(SaveSuratRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        PengajuanSurat::create([
            ...$this->suratApplicantData($validated),
            'jenis_surat' => $validated['jenis_surat'],
            'keperluan' => $validated['keperluan'],
            'catatan_warga' => $validated['catatan_warga'] ?? null,
            'status' => PengajuanSurat::STATUS_DIAJUKAN,
        ]);

        return redirect()->route('admin.surat')->with('success', 'Pengajuan surat berhasil dibuat.');
    }

    public function suratData(PengajuanSurat $surat): JsonResponse
    {
        abort_unless(in_array($surat->status, [
            PengajuanSurat::STATUS_DIAJUKAN,
            PengajuanSurat::STATUS_DIPROSES,
        ], true), 404);

        $surat->loadMissing('warga');

        return response()->json([
            'jenis_pemohon' => $surat->jenis_pemohon ?: 'warga',
            'warga_id' => $surat->warga_id,
            'nama_pemohon' => $surat->nama_pemohon ?: $surat->warga?->nama,
            'nik_pemohon' => $surat->nik_pemohon ?: $surat->warga?->nik,
            'jenis_kelamin' => $surat->jenis_kelamin,
            'tempat_lahir' => $surat->tempat_lahir,
            'tanggal_lahir' => $surat->tanggal_lahir?->format('Y-m-d'),
            'agama' => $surat->agama,
            'pekerjaan' => $surat->pekerjaan,
            'status_perkawinan' => $surat->status_perkawinan,
            'kewarganegaraan' => $surat->kewarganegaraan ?: 'Indonesia',
            'alamat_ktp' => $surat->alamat_ktp,
            'alamat_domisili' => $surat->alamat_domisili ?: $surat->warga?->alamat,
            'jenis_surat' => $surat->jenis_surat,
            'keperluan' => $surat->keperluan,
            'catatan_warga' => $surat->catatan_warga,
        ]);
    }

    public function suratUpdate(SaveSuratRequest $request, PengajuanSurat $surat): RedirectResponse
    {
        if (! in_array($surat->status, [
            PengajuanSurat::STATUS_DIAJUKAN,
            PengajuanSurat::STATUS_DIPROSES,
        ], true)) {
            return back()->with('error', 'Pengajuan yang sudah dikirim ke Ketua RT tidak dapat diubah.');
        }

        $validated = $request->validated();
        $surat->update([
            ...$this->suratApplicantData($validated),
            'jenis_surat' => $validated['jenis_surat'],
            'keperluan' => $validated['keperluan'],
            'catatan_warga' => $validated['catatan_warga'] ?? null,
            'status' => PengajuanSurat::STATUS_DIAJUKAN,
            'nomor_surat' => null,
            'catatan_admin' => null,
            'catatan_ketua' => null,
            'diproses_oleh' => null,
            'diproses_pada' => null,
            'disetujui_oleh' => null,
            'disetujui_pada' => null,
        ]);

        return redirect()->route('admin.surat')->with('success', 'Pengajuan surat diperbarui dan dikembalikan ke status diajukan.');
    }

    public function suratDestroy(PengajuanSurat $surat): RedirectResponse
    {
        if (in_array($surat->status, [
            PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN,
            PengajuanSurat::STATUS_DISETUJUI,
        ], true)) {
            return back()->with('error', 'Surat yang menunggu persetujuan atau sudah disetujui tidak dapat dihapus.');
        }

        $surat->delete();

        return back()->with('success', 'Pengajuan surat berhasil dihapus.');
    }

    public function suratAction(Request $request, int $id): RedirectResponse
    {
        $surat = PengajuanSurat::findOrFail($id);
        $validated = $request->validate([
            'aksi' => ['required', Rule::in(['proses', 'kirim_persetujuan'])],
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
            'nomor_surat' => [
                'exclude_unless:aksi,kirim_persetujuan',
                'required',
                'string',
                'max:100',
                Rule::unique('pengajuan_surat', 'nomor_surat')->ignore($surat->id),
            ],
        ]);
        $aksi = $validated['aksi'];

        if ($aksi === 'proses' && $surat->status === PengajuanSurat::STATUS_DIAJUKAN) {
            $surat->update([
                'status' => PengajuanSurat::STATUS_DIPROSES,
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'diproses_oleh' => auth()->id(),
                'diproses_pada' => now(),
            ]);

            return back()->with('success', 'Pengajuan surat ditandai sedang diproses.');
        } elseif ($aksi === 'kirim_persetujuan' && $surat->status === PengajuanSurat::STATUS_DIPROSES) {
            $surat->update([
                'status' => PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN,
                'nomor_surat' => $validated['nomor_surat'],
                'catatan_admin' => $validated['catatan_admin'] ?? null,
            ]);

            return back()->with('success', 'Surat diteruskan ke Ketua RT untuk persetujuan.');
        }

        return back()->with('error', 'Aksi tidak valid untuk status surat ini.');
    }

    public function suratKopStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'surat_id' => [
                'required',
                'integer',
                Rule::exists('pengajuan_surat', 'id')->where('status', PengajuanSurat::STATUS_DISETUJUI),
            ],
            'nama_pemerintahan' => ['required', 'string', 'max:160'],
            'nama_kecamatan' => ['nullable', 'string', 'max:160'],
            'nama_rt_rw' => ['required', 'string', 'max:160'],
            'alamat_sekretariat' => ['nullable', 'string', 'max:255'],
            'nama_ketua' => ['nullable', 'string', 'max:120'],
            'jabatan_penanda_tangan' => ['required', 'string', 'max:100'],
            'kota_ttd' => ['nullable', 'string', 'max:100'],
            'masa_berlaku' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'hapus_logo' => ['nullable', 'boolean'],
        ]);

        $surat = PengajuanSurat::query()
            ->where('status', PengajuanSurat::STATUS_DISETUJUI)
            ->findOrFail($validated['surat_id']);
        $pengaturan = PengaturanSurat::query()->find(1) ?? new PengaturanSurat;
        $pengaturan->setAttribute('id', 1);
        $logoSebelumnya = $pengaturan->logo_path;
        $logoPath = $logoSebelumnya;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('kop-surat', 'public');

            if ($logoPath === false) {
                return back()->withErrors(['logo' => 'Logo tidak dapat disimpan.'])->withInput();
            }
        } elseif ($request->boolean('hapus_logo')) {
            $logoPath = null;
        }

        $pengaturan->fill(Arr::only($validated, [
            'nama_pemerintahan',
            'nama_kecamatan',
            'nama_rt_rw',
            'alamat_sekretariat',
            'nama_ketua',
            'jabatan_penanda_tangan',
            'kota_ttd',
            'masa_berlaku',
        ]));
        $pengaturan->logo_path = $logoPath;

        if (! $pengaturan->save()) {
            if ($logoPath !== $logoSebelumnya && $logoPath !== null) {
                Storage::disk('public')->delete($logoPath);
            }

            return back()->withErrors(['kop' => 'Pengaturan kop surat tidak dapat disimpan.'])->withInput();
        }

        if ($logoSebelumnya !== null && $logoSebelumnya !== $logoPath) {
            Storage::disk('public')->delete($logoSebelumnya);
        }

        return redirect()->route('surat.cetak', $surat->id)->with('success', 'Pengaturan kop surat berhasil disimpan.');
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function suratApplicantData(array $validated): array
    {
        if ($validated['jenis_pemohon'] === 'warga') {
            $warga = Warga::query()
                ->where('status_aktif', true)
                ->findOrFail($validated['warga_id']);

            return [
                'jenis_pemohon' => 'warga',
                'warga_id' => $warga->id,
                'nama_pemohon' => $warga->nama,
                'nik_pemohon' => $warga->nik,
                'jenis_kelamin' => null,
                'tempat_lahir' => null,
                'tanggal_lahir' => null,
                'agama' => null,
                'pekerjaan' => null,
                'status_perkawinan' => null,
                'kewarganegaraan' => null,
                'alamat_ktp' => null,
                'alamat_domisili' => $warga->alamat,
            ];
        }

        return [
            'jenis_pemohon' => 'non_warga',
            'warga_id' => null,
            ...Arr::only($validated, [
                'nama_pemohon',
                'nik_pemohon',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'agama',
                'pekerjaan',
                'status_perkawinan',
                'kewarganegaraan',
                'alamat_ktp',
                'alamat_domisili',
            ]),
        ];
    }

    // ==========================================
    // 13. INFORMASI & KEGIATAN
    // ==========================================
    public function informasi()
    {
        $pengumuman = Pengumuman::orderBy('dipublikasikan_pada', 'desc')->limit(10)->get();
        $kegiatan = Kegiatan::withCount(['peserta as jumlah_peserta' => fn ($q) => $q->where('status', 'Terdaftar')])
            ->orderByRaw("CASE status WHEN 'Draft' THEN 1 WHEN 'Menunggu Persetujuan' THEN 2 WHEN 'Dipublikasikan' THEN 3 WHEN 'Selesai' THEN 4 WHEN 'Dibatalkan' THEN 5 ELSE 6 END")
            ->orderBy('mulai_pada', 'asc')
            ->get();

        return view('admin.informasi', compact('pengumuman', 'kegiatan'));
    }

    public function pengumumanStore(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:150',
            'isi' => 'required|string',
        ]);

        Pengumuman::create([
            'judul' => $validated['judul'],
            'isi' => $validated['isi'],
            'dibuat_oleh' => auth()->id(),
        ]);

        return back()->with('success', 'Pengumuman berhasil dipublikasikan untuk warga.');
    }

    public function pengumumanArsip($id)
    {
        $p = Pengumuman::findOrFail($id);
        $p->update(['status' => 'Arsip']);

        return back()->with('success', 'Pengumuman dipindahkan ke arsip.');
    }

    public function kegiatanStore(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'lokasi' => 'required|string|max:150',
            'mulai_pada' => 'required|date',
            'selesai_pada' => 'nullable|date|after_or_equal:mulai_pada',
            'kapasitas' => 'nullable|integer|min:1',
        ]);

        Kegiatan::create([
            'judul' => $validated['judul'],
            'deskripsi' => $validated['deskripsi'],
            'lokasi' => $validated['lokasi'],
            'mulai_pada' => $validated['mulai_pada'],
            'selesai_pada' => $validated['selesai_pada'] ?? null,
            'kapasitas' => $validated['kapasitas'] ?? null,
            'status' => 'Draft',
            'dibuat_oleh' => auth()->id(),
        ]);

        return back()->with('success', 'Kegiatan disimpan sebagai draft. Kirim ke Ketua RT saat sudah siap.');
    }

    public function kegiatanKirim($id)
    {
        $kegiatan = Kegiatan::where('id', $id)->where('status', 'Draft')->firstOrFail();
        $kegiatan->update(['status' => 'Menunggu Persetujuan']);

        return back()->with('success', 'Kegiatan dikirim ke Ketua RT untuk disetujui.');
    }

    public function kegiatanSelesai($id)
    {
        $kegiatan = Kegiatan::where('id', $id)->where('status', 'Dipublikasikan')->firstOrFail();
        $kegiatan->update(['status' => 'Selesai']);

        return back()->with('success', 'Kegiatan ditandai selesai.');
    }
}
