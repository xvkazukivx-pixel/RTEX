<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\IotGateController;
use App\Http\Controllers\KetuaController;
use App\Http\Controllers\SuratController;
use App\Http\Controllers\WargaController;
use Illuminate\Support\Facades\Route;

// Halaman utama: redirect sesuai login status
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->dashboardRouteName());
    }

    return redirect()->route('login');
});

// Autentikasi
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Cetak Surat (Bisa diakses Admin, Ketua, dan Warga yang memiliki surat terkait)
Route::middleware('auth')->group(function () {
    Route::get('/surat/cetak/{surat}', [SuratController::class, 'cetak'])->name('surat.cetak');
});

// Modul Bendahara (keuangan saja)
Route::middleware(['auth', 'role:bendahara'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/dashboard/generate-tagihan', [AdminController::class, 'generateTagihan'])->name('dashboard.generate');
    Route::get('/iuran', [AdminController::class, 'iuran'])->name('iuran');
    Route::post('/iuran/store', [AdminController::class, 'iuranStore'])->name('iuran.store');
    Route::post('/iuran/update/{id}', [AdminController::class, 'iuranUpdate'])->name('iuran.update');
    Route::post('/iuran/delete/{id}', [AdminController::class, 'iuranDestroy'])->name('iuran.destroy');
    Route::get('/transaksi', [AdminController::class, 'transaksi'])->name('transaksi');
    Route::post('/transaksi/store', [AdminController::class, 'transaksiStore'])->name('transaksi.store');
    Route::get('/konfirmasi', [AdminController::class, 'konfirmasi'])->name('konfirmasi');
    Route::post('/konfirmasi/{id}', [AdminController::class, 'konfirmasiAction'])->name('konfirmasi.action');
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
    Route::get('/bank-sampah/harga', [AdminController::class, 'bankSampahHarga'])->name('bank_sampah.harga');
    Route::post('/bank-sampah/harga/store', [AdminController::class, 'bankSampahHargaStore'])->name('bank_sampah.harga.store');
    Route::post('/bank-sampah/harga/{id}', [AdminController::class, 'bankSampahHargaUpdate'])->name('bank_sampah.harga.update')->whereNumber('id');
});

// Modul Sekretariat & Administrasi RT (Super Admin & Sekretaris RT)
Route::middleware(['auth', 'role:super_admin,sekretaris_rt'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/warga', [AdminController::class, 'warga'])->name('warga');
    Route::post('/warga/store', [AdminController::class, 'wargaStore'])->name('warga.store');
    Route::post('/warga/{id}/anggota', [AdminController::class, 'wargaAnggotaStore'])->name('warga.anggota.store');
    Route::post('/warga/update/{id}', [AdminController::class, 'wargaUpdate'])->name('warga.update');
    Route::post('/warga/delete/{id}', [AdminController::class, 'wargaDestroy'])->name('warga.destroy');

    Route::get('/pengaduan', [AdminController::class, 'pengaduan'])->name('pengaduan');
    Route::post('/pengaduan/{pengaduan}', [AdminController::class, 'pengaduanAction'])->name('pengaduan.action');

    Route::get('/inventaris', [AdminController::class, 'inventaris'])->name('inventaris');
    Route::post('/inventaris/barang', [AdminController::class, 'inventarisBarangStore'])->name('inventaris.barang.store');
    Route::post('/inventaris/barang/{barang}', [AdminController::class, 'inventarisBarangUpdate'])->name('inventaris.barang.update');
    Route::delete('/inventaris/barang/{barang}', [AdminController::class, 'inventarisBarangDestroy'])->name('inventaris.barang.destroy');
    Route::post('/inventaris/peminjaman/{peminjaman}', [AdminController::class, 'inventarisPeminjamanAction'])->name('inventaris.peminjaman.action');

    Route::get('/posyandu', [AdminController::class, 'posyandu'])->name('posyandu');
    Route::post('/posyandu/jadwal/store', [AdminController::class, 'posyanduJadwalStore'])->name('posyandu.jadwal.store');
    Route::post('/posyandu/pemeriksaan/store', [AdminController::class, 'posyanduPemeriksaanStore'])->name('posyandu.pemeriksaan.store');

    Route::get('/surat', [AdminController::class, 'surat'])->name('surat');
    Route::post('/surat/store', [AdminController::class, 'suratStore'])->name('surat.store');
    Route::post('/surat/kop', [AdminController::class, 'suratKopStore'])->name('surat.kop');
    Route::get('/surat/{surat}/data', [AdminController::class, 'suratData'])->name('surat.data');
    Route::post('/surat/update/{surat}', [AdminController::class, 'suratUpdate'])->name('surat.update');
    Route::post('/surat/delete/{surat}', [AdminController::class, 'suratDestroy'])->name('surat.destroy');
    Route::post('/surat/{id}', [AdminController::class, 'suratAction'])->name('surat.action');

    Route::get('/informasi', [AdminController::class, 'informasi'])->name('informasi');
    Route::post('/pengumuman/store', [AdminController::class, 'pengumumanStore'])->name('pengumuman.store');
    Route::post('/pengumuman/arsip/{id}', [AdminController::class, 'pengumumanArsip'])->name('pengumuman.arsip');
    Route::post('/kegiatan/store', [AdminController::class, 'kegiatanStore'])->name('kegiatan.store');
    Route::post('/kegiatan/kirim/{id}', [AdminController::class, 'kegiatanKirim'])->name('kegiatan.kirim');
    Route::post('/kegiatan/selesai/{id}', [AdminController::class, 'kegiatanSelesai'])->name('kegiatan.selesai');
});

// Modul Khusus Super Admin (Konfigurasi Akun, Persetujuan Warga Baru, Bank Sampah, Monitoring Portal IoT)
Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/persetujuan', [AdminController::class, 'persetujuan'])->name('persetujuan');
    Route::post('/persetujuan/{id}', [AdminController::class, 'persetujuanAction'])->name('persetujuan.action');

    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/store', [AdminController::class, 'usersStore'])->name('users.store');
    Route::post('/users/reset-password/{id}', [AdminController::class, 'usersResetPassword'])->name('users.reset_password');
    Route::post('/users/delete/{id}', [AdminController::class, 'usersDestroy'])->name('users.destroy');

    Route::get('/bank-sampah', [AdminController::class, 'bankSampah'])->name('bank_sampah');
    Route::post('/bank-sampah/setoran/store', [AdminController::class, 'bankSampahSetoranStore'])->name('bank_sampah.setoran.store');
    Route::post('/bank-sampah/jenis/store', [AdminController::class, 'bankSampahJenisStore'])->name('bank_sampah.jenis.store');
    Route::post('/bank-sampah/setoran/{id}', [AdminController::class, 'bankSampahSetoranAction'])->name('bank_sampah.setoran.action');

    // Modul Monitoring Portal IoT & KTP RFID
    Route::get('/iot-gate', [IotGateController::class, 'adminIndex'])->name('iot_gate');
    Route::post('/iot-gate/register-warga', [IotGateController::class, 'adminRegisterWarga'])->name('iot_gate.register_warga');
    Route::post('/iot-gate/toggle-status/{user}', [IotGateController::class, 'adminToggleStatus'])->name('iot_gate.toggle_status');
    Route::delete('/iot-gate/remove-user/{user}', [IotGateController::class, 'adminRemoveUserRfid'])->name('iot_gate.remove_user');
    Route::post('/iot-gate/manual-open', [IotGateController::class, 'adminManualOpen'])->name('iot_gate.manual_open');
    Route::get('/iot-gate/logs-data', [IotGateController::class, 'adminLogsData'])->name('iot_gate.logs_data');
    Route::get('/iot-gate/firmware', [IotGateController::class, 'downloadFirmware'])->name('iot_gate.firmware');
});

// Modul Eksekutif, Pengesahan & Pengawasan (RT, RW, Bendahara RW, Sekretaris RW)
Route::middleware(['auth', 'role:rt,rw,bendahara_rw,sekretaris_rw'])->prefix('ketua')->name('ketua.')->group(function () {
    Route::get('/dashboard', [KetuaController::class, 'dashboard'])->name('dashboard');
    Route::get('/monitoring', [KetuaController::class, 'monitoring'])->name('monitoring');
    Route::get('/rekap', [KetuaController::class, 'rekap'])->name('rekap');
    Route::get('/laporan', [KetuaController::class, 'laporan'])->name('laporan');
    Route::post('/laporan/validasi', [KetuaController::class, 'validasiLaporan'])->name('laporan.validasi');

    Route::get('/surat', [KetuaController::class, 'surat'])->name('surat');
    Route::post('/surat/{id}', [KetuaController::class, 'suratAction'])->name('surat.action');

    Route::get('/informasi', [KetuaController::class, 'informasi'])->name('informasi');
    Route::post('/kegiatan/{id}', [KetuaController::class, 'kegiatanAction'])->name('kegiatan.action');

    Route::get('/bank-sampah', [KetuaController::class, 'bankSampah'])->name('bank_sampah');
    Route::get('/posyandu', [KetuaController::class, 'posyandu'])->name('posyandu');

    // Monitoring Portal IoT Gerbang RT/RW
    Route::get('/iot-gate', [IotGateController::class, 'adminIndex'])->name('iot_gate');
    Route::post('/iot-gate/manual-open', [IotGateController::class, 'adminManualOpen'])->name('iot_gate.manual_open');
});

// Modul Warga
Route::middleware(['auth', 'role:warga'])->prefix('warga')->name('warga.')->group(function () {
    Route::get('/dashboard', [WargaController::class, 'dashboard'])->name('dashboard');

    Route::get('/bayar', [WargaController::class, 'bayar'])->name('bayar');
    Route::post('/bayar', [WargaController::class, 'bayarStore'])->name('bayar.store');

    Route::get('/riwayat', [WargaController::class, 'riwayat'])->name('riwayat');
    Route::get('/kas', [WargaController::class, 'kas'])->name('kas');

    Route::get('/bank-sampah', [WargaController::class, 'bankSampah'])->name('bank_sampah');
    Route::post('/bank-sampah/store', [WargaController::class, 'bankSampahStore'])->name('bank_sampah.store');

    Route::get('/pengaduan', [WargaController::class, 'pengaduan'])->name('pengaduan');
    Route::post('/pengaduan/store', [WargaController::class, 'pengaduanStore'])->name('pengaduan.store');

    Route::get('/inventaris', [WargaController::class, 'inventaris'])->name('inventaris');
    Route::post('/inventaris/pinjam', [WargaController::class, 'inventarisPinjamStore'])->name('inventaris.pinjam.store');

    Route::get('/posyandu', [WargaController::class, 'posyandu'])->name('posyandu');
    Route::post('/posyandu/anak/store', [WargaController::class, 'posyanduAnakStore'])->name('posyandu.anak.store');

    Route::get('/surat', [WargaController::class, 'surat'])->name('surat');
    Route::post('/surat/store', [WargaController::class, 'suratStore'])->name('surat.store');

    Route::get('/informasi', [WargaController::class, 'informasi'])->name('informasi');
    Route::post('/kegiatan/{id}/daftar', [WargaController::class, 'kegiatanDaftar'])->name('kegiatan.daftar');
    Route::post('/kegiatan/{id}/batal', [WargaController::class, 'kegiatanBatal'])->name('kegiatan.batal');

    // Akses Portal & Pendaftaran KTP Mandiri
    Route::get('/rfid', [IotGateController::class, 'wargaIndex'])->name('rfid');
    Route::post('/rfid/start-scan', [IotGateController::class, 'startScan'])->name('rfid.start_scan');
    Route::get('/rfid/check-scan', [IotGateController::class, 'checkScan'])->name('rfid.check_scan');
    Route::post('/rfid/register-manual', [IotGateController::class, 'registerManual'])->name('rfid.register_manual');
    Route::delete('/rfid/remove', [IotGateController::class, 'removeRfid'])->name('rfid.remove');
    Route::post('/rfid/simulate-tap', [IotGateController::class, 'simulateTap'])->name('rfid.simulate_tap');
});
