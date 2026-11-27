<?php

use App\Http\Controllers\IotGateController;
use App\Http\Controllers\RfidController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk RTEX IoT Gate & Hardware ESP32
|--------------------------------------------------------------------------
| Seluruh rute API di sini tidak memerlukan CSRF token dan cocok dipanggil
| oleh mikrokontroler ESP32, Arduino, maupun modul integrasi pihak ketiga.
*/

// =========================================================================
// 1. ENDPOINT KHUSUS ESP32 KELOMPOK (Format smartrw-rfid-device)
// =========================================================================
Route::prefix('rfid')->group(function () {
    // Menerima data tap kartu RFID / e-KTP dari alat fisik ESP32
    Route::post('/tap', [RfidController::class, 'tap'])->name('api.rfid.tap');

    // Menerima sinyal heartbeat berkala status online ESP32 setiap 2 detik
    Route::post('/heartbeat', [RfidController::class, 'heartbeat'])->name('api.rfid.heartbeat');

    // Helper polling frontend untuk membaca UID terakhir yang di-tap
    Route::get('/last-scanned', [RfidController::class, 'lastScanned'])->name('api.rfid.last_scanned');
});

// =========================================================================
// 2. ENDPOINT IOT GATE BAWAAN RTEX (Tetap dipertahankan agar kompatibel)
// =========================================================================
Route::prefix('iot')->name('api.iot.')->group(function () {
    // Dipanggil oleh simulator / ESP32 bawaan saat e-KTP di-tap
    Route::post('/gate/tap', [IotGateController::class, 'tap'])->name('gate.tap');

    // Health check & status online IoT Gate
    Route::get('/gate/status', [IotGateController::class, 'status'])->name('gate.status');

    // Sesi Scan/Pairing KTP
    Route::post('/rfid/start-scan', [IotGateController::class, 'startScan'])->name('rfid.start_scan');
    Route::get('/rfid/check-scan', [IotGateController::class, 'checkScan'])->name('rfid.check_scan');
});
