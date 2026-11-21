<?php

use App\Http\Controllers\IotGateController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes untuk RTEX IoT Gate & Hardware ESP32
|--------------------------------------------------------------------------
| Seluruh rute API di sini tidak memerlukan CSRF token dan cocok dipanggil
| oleh mikrokontroler ESP32, Arduino, maupun modul integrasi pihak ketiga.
*/

Route::prefix('iot')->name('api.iot.')->group(function () {
    // Dipanggil oleh ESP32 saat e-KTP di-tap di reader gerbang
    Route::post('/gate/tap', [IotGateController::class, 'tap'])->name('gate.tap');

    // Health check & status online IoT Gate
    Route::get('/gate/status', [IotGateController::class, 'status'])->name('gate.status');

    // Sesi Scan/Pairing KTP
    Route::post('/rfid/start-scan', [IotGateController::class, 'startScan'])->name('rfid.start_scan');
    Route::get('/rfid/check-scan', [IotGateController::class, 'checkScan'])->name('rfid.check_scan');
});
