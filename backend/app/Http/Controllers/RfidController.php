<?php

namespace App\Http\Controllers;

use App\Models\GateAccessLog;
use App\Models\RfidScanSession;
use App\Models\User;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RfidController extends Controller
{
    /**
     * Kunci autentikasi perangkat ESP32
     */
    protected const DEVICE_KEY = 'smartrw-rfid-device';

    /**
     * Memeriksa keamanan header X-Device-Key atau Authorization Bearer token
     */
    protected function authenticateDevice(Request $request): bool
    {
        $deviceKey = $request->header('X-Device-Key') ?? $request->bearerToken();
        return $deviceKey === self::DEVICE_KEY;
    }

    /**
     * Normalisasi UID menjadi hex bersih dan format bertitik dua (:)
     */
    protected function normalizeUid(string $raw): array
    {
        $clean = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $raw));
        $colonFormat = implode(':', str_split($clean, 2));
        return [$clean, $colonFormat];
    }

    /**
     * POST /api/rfid/tap
     * Menerima data scan tap kartu RFID / e-KTP dari alat fisik ESP32
     */
    public function tap(Request $request): JsonResponse
    {
        // 1. Cek keamanan header X-Device-Key (atau Bearer token)
        if (!$this->authenticateDevice($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Header X-Device-Key atau Bearer token tidak valid.',
            ], 401);
        }

        // 2. Validasi uid wajib ada dan bertipe string
        if (!$request->filled('uid') || !is_string($request->input('uid'))) {
            return response()->json([
                'success' => false,
                'message' => 'UID kartu wajib diisi dan bertipe string.',
            ], 422);
        }

        // 3. Ambil UID kartu dengan huruf besar
        $uid = strtoupper((string) $request->input('uid'));
        $deviceId = (string) $request->input('device_id', 'esp32-scanner-02');

        // 4. Simpan sementara di Cache selama 30 detik untuk kebutuhan auto-fill di frontend
        Cache::put('last_rfid_uid', $uid, now()->addSeconds(30));
        Cache::put('last_rfid_scanned', [
            'uid' => $uid,
            'device_id' => $deviceId,
            'scanned_at' => now()->toIso8601String(),
        ], now()->addSeconds(30));

        // 5. Hubungkan $uid dengan logika fitur IoT di projek RTEX
        [$cleanUid, $colonUid] = $this->normalizeUid($uid);

        // a. Cek apakah ada sesi pairing / pendaftaran e-KTP dari web yang sedang aktif
        $activeSession = RfidScanSession::where('status', 'waiting')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if ($activeSession && $activeSession->user) {
            $targetUser = $activeSession->user;
            $targetUser->update([
                'rfid_uid' => $colonUid,
                'rfid_label' => 'e-KTP',
                'rfid_registered_at' => Carbon::now(),
                'rfid_is_active' => true,
            ]);

            $activeSession->update([
                'status' => 'scanned',
                'scanned_uid' => $colonUid,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kartu berhasil dibaca dan didaftarkan untuk ' . $targetUser->nama,
                'uid' => $uid,
                'device_id' => $deviceId,
                'warga' => [
                    'nama' => $targetUser->nama,
                    'no_rumah' => $targetUser->warga?->no_rumah ?? '-',
                    'role' => $targetUser->role,
                ],
                'action' => 'registered',
            ], 200);
        }

        // b. Cek data warga / user pemilik kartu di database
        $user = User::where('rfid_uid', $uid)
            ->orWhere('rfid_uid', $colonUid)
            ->orWhereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanUid])
            ->with('warga')
            ->first();

        if ($user && $user->rfid_is_active) {
            $wargaNama = $user->nama;
            $noRumah = $user->warga?->no_rumah ?? '-';

            // Catat log akses gerbang diterima
            GateAccessLog::create([
                'rfid_uid' => $colonUid,
                'user_id' => $user->id,
                'warga_nama' => $wargaNama,
                'no_rumah' => $noRumah,
                'device_id' => $deviceId,
                'access_status' => 'granted',
                'gate_action' => 'OPEN_GATE',
                'ip_address' => $request->ip(),
                'tapped_at' => Carbon::now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kartu berhasil dibaca - Akses Diterima (' . $wargaNama . ')',
                'uid' => $uid,
                'device_id' => $deviceId,
                'warga' => [
                    'nama' => $wargaNama,
                    'no_rumah' => $noRumah,
                    'role' => $user->role,
                ],
                'access' => 'granted',
                'open_gate' => true,
            ], 200);
        }

        // c. Jika kartu belum terdaftar, tetap catat log audit untuk keamanan
        GateAccessLog::create([
            'rfid_uid' => $colonUid,
            'user_id' => $user?->id,
            'warga_nama' => $user ? $user->nama : 'Belum Terdaftar',
            'no_rumah' => $user?->warga?->no_rumah,
            'device_id' => $deviceId,
            'access_status' => 'denied',
            'denial_reason' => 'KTP belum terdaftar dalam sistem RTEX.',
            'gate_action' => 'NONE',
            'ip_address' => $request->ip(),
            'tapped_at' => Carbon::now(),
        ]);

        // Wajib kembalikan response JSON dengan HTTP Status Code 200 agar buzzer ESP32 berbunyi sukses
        return response()->json([
            'success' => true,
            'message' => 'Kartu berhasil dibaca',
            'uid' => $uid,
            'device_id' => $deviceId,
            'access' => 'unregistered',
            'open_gate' => false,
        ], 200);
    }

    /**
     * POST /api/rfid/heartbeat
     * Menerima sinyal status online ESP32 setiap 2 detik
     */
    public function heartbeat(Request $request): JsonResponse
    {
        // 1. Cek keamanan X-Device-Key atau Bearer token
        if (!$this->authenticateDevice($request)) {
            return response()->json([
                'status' => 'unauthorized',
            ], 401);
        }

        // 2. Simpan status alat online di Cache selama 6 detik (ESP32 heartbeat setiap 2 detik)
        Cache::put('rfid_device_online', true, now()->addSeconds(6));
        Cache::put('rfid_device_info', [
            'device_id' => $request->input('device_id', 'esp32-scanner-02'),
            'ip' => $request->input('ip', $request->ip()),
            'last_seen' => now()->toIso8601String(),
        ], now()->addSeconds(6));

        // 3. Kembalikan response JSON HTTP 200
        return response()->json([
            'status' => 'online',
        ], 200);
    }

    /**
     * GET /api/rfid/last-scanned
     * Helper endpoint untuk frontend membaca UID kartu terakhir yang di-tap
     */
    public function lastScanned(): JsonResponse
    {
        $lastUid = Cache::get('last_rfid_uid');
        $info = Cache::get('last_rfid_scanned');
        $isOnline = (bool) Cache::get('rfid_device_online', false);

        return response()->json([
            'device_online' => $isOnline,
            'uid' => $lastUid,
            'details' => $info,
        ], 200);
    }
}
