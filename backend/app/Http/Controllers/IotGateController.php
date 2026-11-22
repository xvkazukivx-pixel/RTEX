<?php

namespace App\Http\Controllers;

use App\Models\GateAccessLog;
use App\Models\RfidScanSession;
use App\Models\User;
use App\Models\Warga;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class IotGateController extends Controller
{
    /**
     * Standarisasi UID KTP / RFID agar konsisten dalam format HEX huruf besar
     * Contoh: "4a2f891b" atau "4a-2f-89-1b" -> "4A:2F:89:1B"
     */
    public static function normalizeUid(?string $raw): ?string
    {
        if (!$raw) {
            return null;
        }

        $clean = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $raw));
        if (empty($clean)) {
            return null;
        }

        // Pecah per 2 karakter dengan pemisah titik dua (:)
        $chunks = str_split($clean, 2);
        return implode(':', $chunks);
    }

    /**
     * Memeriksa API KEY untuk komunikasi aman antara ESP32 dan Backend RTEX
     */
    protected function verifyApiKey(Request $request): bool
    {
        $expectedKey = env('RTEX_IOT_KEY', 'rtex-gate-key-2026');
        $givenKey = $request->header('X-API-KEY') 
            ?? $request->input('api_key') 
            ?? $request->bearerToken();

        if (empty($expectedKey)) {
            return true;
        }

        return $givenKey === $expectedKey;
    }

    /* =========================================================================
     * ENDPOINT API UNTUK ESP32 / HARDWARE RFID READER
     * ========================================================================= */

    /**
     * Endpoint utama yang dipanggil oleh ESP32 setiap kali kartu KTP di-tap
     * POST /api/iot/gate/tap
     */
    public function tap(Request $request): JsonResponse
    {
        if (!$this->verifyApiKey($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: API Key ESP32 tidak valid!',
            ], 401);
        }

        $rawUid = $request->input('uid');
        if (empty($rawUid)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter "uid" wajib dikirim.',
            ], 422);
        }

        $uid = self::normalizeUid($rawUid);
        $deviceId = $request->input('device_id', 'PORTAL-UTAMA');
        $mode = $request->input('mode', 'auto'); // 'auto', 'register', 'gate'

        // 1. Cek apakah ada sesi pairing / scan KTP yang sedang aktif dari web
        if ($mode !== 'gate') {
            $activeSession = RfidScanSession::where('status', 'waiting')
                ->where('expires_at', '>', Carbon::now())
                ->latest()
                ->first();

            if ($activeSession) {
                // Periksa apakah kartu ini sudah dipakai oleh akun lain
                $existingUser = User::where('rfid_uid', $uid)
                    ->where('id', '!=', $activeSession->user_id)
                    ->first();

                if ($existingUser) {
                    return response()->json([
                        'success' => false,
                        'mode' => 'registration',
                        'status' => 'duplicate',
                        'open_gate' => false,
                        'message' => 'KTP ini sudah terdaftar atas nama: ' . $existingUser->nama,
                    ], 200);
                }

                // Daftarkan UID ke user yang sedang membuka sesi scan
                $targetUser = $activeSession->user;
                if ($targetUser) {
                    $targetUser->update([
                        'rfid_uid' => $uid,
                        'rfid_label' => 'e-KTP',
                        'rfid_registered_at' => Carbon::now(),
                        'rfid_is_active' => true,
                    ]);

                    $activeSession->update([
                        'status' => 'scanned',
                        'scanned_uid' => $uid,
                    ]);

                    return response()->json([
                        'success' => true,
                        'mode' => 'registration',
                        'status' => 'registered',
                        'open_gate' => false,
                        'message' => 'e-KTP Berhasil Didaftarkan untuk ' . $targetUser->nama,
                        'user' => [
                            'id' => $targetUser->id,
                            'nama' => $targetUser->nama,
                            'rfid_uid' => $uid,
                            'no_rumah' => $targetUser->warga?->no_rumah ?? '-',
                        ],
                    ], 200);
                }
            }
        }

        // 2. Mode Akses Gerbang (Gate Access Verification)
        $cleanSearch = str_replace(':', '', $uid);
        $user = User::where('rfid_uid', $uid)->with('warga')->first();

        // Fallback jika ada variasi format di database
        if (!$user) {
            $user = User::whereRaw("REPLACE(REPLACE(rfid_uid, ':', ''), '-', '') = ?", [$cleanSearch])
                ->with('warga')
                ->first();
        }

        // KONDISI A: KTP TERDAFTAR DAN AKTIF -> AKSES DITERIMA
        if ($user && $user->rfid_is_active) {
            $wargaNama = $user->nama;
            $noRumah = $user->warga?->no_rumah ?? '-';

            // Simpan log akses
            GateAccessLog::create([
                'rfid_uid' => $uid,
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
                'mode' => 'gate_access',
                'access' => 'granted',
                'open_gate' => true,
                'gate_open_duration_ms' => 5000,
                'user_name' => $wargaNama,
                'house_number' => $noRumah,
                'role' => $user->role,
                'message' => 'Akses Diterima! Selamat Datang, ' . $wargaNama,
            ], 200);
        }

        // KONDISI B: KTP TIDAK TERDAFTAR ATAU DINONAKTIFKAN -> AKSES DITOLAK
        $isDeactivated = $user && !$user->rfid_is_active;
        $reason = $isDeactivated 
            ? 'Akses KTP telah dinonaktifkan oleh Pengurus RT/RW.' 
            : 'KTP belum terdaftar dalam sistem RTEX.';

        GateAccessLog::create([
            'rfid_uid' => $uid,
            'user_id' => $user?->id,
            'warga_nama' => $user ? $user->nama : 'Tidak Dikenal',
            'no_rumah' => $user?->warga?->no_rumah,
            'device_id' => $deviceId,
            'access_status' => 'denied',
            'denial_reason' => $reason,
            'gate_action' => 'NONE',
            'ip_address' => $request->ip(),
            'tapped_at' => Carbon::now(),
        ]);

        return response()->json([
            'success' => false,
            'mode' => 'gate_access',
            'access' => 'denied',
            'open_gate' => false,
            'message' => 'Akses Ditolak! ' . $reason,
        ], 200);
    }

    /**
     * Endpoint status IoT untuk memeriksa konektivitas ESP32
     * GET /api/iot/gate/status
     */
    public function status(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'online',
            'server_time' => Carbon::now()->toIso8601String(),
            'active_cards_count' => User::whereNotNull('rfid_uid')->where('rfid_is_active', true)->count(),
            'total_access_today' => GateAccessLog::whereDate('tapped_at', Carbon::today())->count(),
        ]);
    }

    /* =========================================================================
     * MODUL WARGA (PORTAL & PENDAFTARAN KTP MANDIRI)
     * ========================================================================= */

    /**
     * Halaman Akses Portal & KTP Warga
     * GET /warga/rfid
     */
    public function wargaIndex()
    {
        $user = auth()->user()->load('warga');
        $logs = GateAccessLog::where('user_id', $user->id)
            ->latest('tapped_at')
            ->take(15)
            ->get();

        $activeSession = RfidScanSession::where('user_id', $user->id)
            ->where('status', 'waiting')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        return view('warga.rfid', compact('user', 'logs', 'activeSession'));
    }

    /**
     * Memulai sesi scan KTP via ESP32 dari web
     * POST /warga/rfid/start-scan atau /admin/iot-gate/start-scan
     */
    public function startScan(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', auth()->id());

        // Jika user lain ditentukan, pastikan pemanggil adalah admin/pengurus
        if ($userId != auth()->id() && !in_array(auth()->user()->role, ['super_admin', 'sekretaris_rt', 'rt', 'ketua', 'rw', 'bendahara_rt', 'bendahara_rw', 'sekretaris_rw'])) {
            return response()->json(['success' => false, 'message' => 'Tidak memiliki izin.'], 403);
        }

        // Batalkan sesi sebelumnya yang masih menggantung untuk user ini
        RfidScanSession::where('user_id', $userId)
            ->where('status', 'waiting')
            ->update(['status' => 'expired']);

        // Buat sesi baru berlaku 90 detik
        $session = RfidScanSession::create([
            'user_id' => $userId,
            'device_id' => $request->input('device_id', 'PORTAL-UTAMA'),
            'status' => 'waiting',
            'expires_at' => Carbon::now()->addSeconds(90),
        ]);

        $targetUser = User::with('warga')->find($userId);

        return response()->json([
            'success' => true,
            'session_id' => $session->id,
            'target_user' => [
                'id' => $targetUser?->id,
                'nama' => $targetUser?->nama,
                'no_rumah' => $targetUser?->warga?->no_rumah ?? '-',
            ],
            'expires_at' => $session->expires_at->toIso8601String(),
            'remaining_seconds' => 90,
            'message' => 'Silakan tempelkan e-KTP pada alat scanner ESP32 sekarang.',
        ]);
    }

    /**
     * Polling AJAX untuk mengecek apakah e-KTP sudah berhasil di-scan oleh ESP32
     * GET /warga/rfid/check-scan atau /admin/iot-gate/check-scan
     */
    public function checkScan(Request $request): JsonResponse
    {
        $userId = $request->input('user_id', auth()->id());

        if ($userId != auth()->id() && !in_array(auth()->user()->role, ['super_admin', 'sekretaris_rt', 'rt', 'ketua', 'rw', 'bendahara_rt', 'bendahara_rw', 'sekretaris_rw'])) {
            return response()->json(['status' => 'unauthorized'], 403);
        }

        $session = RfidScanSession::where('user_id', $userId)
            ->latest()
            ->first();

        if (!$session) {
            return response()->json(['status' => 'none']);
        }

        if ($session->status === 'scanned') {
            $session->update(['status' => 'completed']);
            $user = User::with('warga')->find($userId);

            return response()->json([
                'status' => 'scanned',
                'uid' => $session->scanned_uid,
                'user_name' => $user?->nama ?? 'Warga',
                'user_id' => $user?->id,
                'no_rumah' => $user?->warga?->no_rumah ?? '-',
                'message' => 'e-KTP berhasil dideteksi dan tersimpan ke akun ' . ($user?->nama ?? 'Warga') . '!',
            ]);
        }

        if ($session->status === 'waiting') {
            if ($session->isExpired()) {
                $session->update(['status' => 'expired']);
                return response()->json(['status' => 'expired', 'message' => 'Waktu scan telah habis. Silakan ulangi kembali.']);
            }

            $remaining = max(0, Carbon::now()->diffInSeconds($session->expires_at, false));
            return response()->json([
                'status' => 'waiting',
                'remaining_seconds' => $remaining,
            ]);
        }

        return response()->json(['status' => $session->status]);
    }

    /**
     * Pendaftaran / Pengeditan UID KTP secara manual
     * POST /warga/rfid/register-manual
     */
    public function registerManual(Request $request)
    {
        $request->validate([
            'rfid_uid' => ['required', 'string', 'max:64'],
        ]);

        $uid = self::normalizeUid($request->input('rfid_uid'));
        if (!$uid) {
            return back()->with('error', 'Format UID KTP tidak valid. Masukkan kombinasi hex (contoh: 4A:2F:89:1B).');
        }

        $user = auth()->user();

        // Cek apakah UID sudah dipakai oleh akun lain
        $duplicate = User::where('rfid_uid', $uid)->where('id', '!=', $user->id)->first();
        if ($duplicate) {
            return back()->with('error', 'KTP tersebut sudah terdaftar pada akun lain (' . $duplicate->nama . ').');
        }

        $user->update([
            'rfid_uid' => $uid,
            'rfid_label' => $request->input('rfid_label', 'e-KTP'),
            'rfid_registered_at' => Carbon::now(),
            'rfid_is_active' => true,
        ]);

        return back()->with('success', 'e-KTP (' . $uid . ') berhasil didaftarkan untuk akun Anda.');
    }

    /**
     * Hapus / Reset UID KTP dari akun warga
     * DELETE /warga/rfid/remove
     */
    public function removeRfid(Request $request)
    {
        $user = auth()->user();
        $user->update([
            'rfid_uid' => null,
            'rfid_registered_at' => null,
            'rfid_label' => 'e-KTP',
        ]);

        return back()->with('success', 'Data e-KTP berhasil dihapus dari akun Anda.');
    }

    /**
     * Simulasi Tap Kartu KTP langsung dari web untuk menguji sistem gerbang
     * POST /warga/rfid/simulate-tap
     */
    public function simulateTap(Request $request): JsonResponse
    {
        $uid = $request->input('uid') ?? auth()->user()->rfid_uid;

        if (empty($uid)) {
            return response()->json([
                'success' => false,
                'access' => 'denied',
                'open_gate' => false,
                'message' => 'KTP belum terdaftar. Daftarkan KTP Anda terlebih dahulu atau gunakan fitur Scan KTP.',
            ]);
        }

        // Panggil internal handler tap
        $syntheticRequest = new Request([
            'uid' => $uid,
            'device_id' => 'PORTAL-SIMULATOR',
            'api_key' => env('RTEX_IOT_KEY', 'rtex-gate-key-2026'),
        ]);

        return $this->tap($syntheticRequest);
    }

    /* =========================================================================
     * MODUL PENGURUS & ADMIN (MONITORING PORTAL & MANAJEMEN KTP)
     * ========================================================================= */

    /**
     * Dashboard Monitoring Portal IoT & Kelola KTP Warga
     * GET /admin/iot-gate atau GET /ketua/iot-gate
     */
    public function adminIndex(Request $request)
    {
        $today = Carbon::today();

        $stats = [
            'today_total' => GateAccessLog::whereDate('tapped_at', $today)->count(),
            'today_granted' => GateAccessLog::whereDate('tapped_at', $today)->where('access_status', 'granted')->count(),
            'today_denied' => GateAccessLog::whereDate('tapped_at', $today)->where('access_status', 'denied')->count(),
            'registered_users' => User::whereNotNull('rfid_uid')->count(),
        ];

        // Filter log
        $statusFilter = $request->query('status');
        $search = $request->query('q');

        $logsQuery = GateAccessLog::latest('tapped_at');

        if ($statusFilter && in_array($statusFilter, ['granted', 'denied'])) {
            $logsQuery->where('access_status', $statusFilter);
        }

        if ($search) {
            $logsQuery->where(function ($q) use ($search) {
                $q->where('warga_nama', 'like', "%{$search}%")
                  ->orWhere('rfid_uid', 'like', "%{$search}%")
                  ->orWhere('no_rumah', 'like', "%{$search}%");
            });
        }

        $logs = $logsQuery->paginate(20)->withQueryString();

        // Sinkronisasi otomatis: pastikan seluruh warga di Kelola Warga memiliki akun user agar bisa didaftarkan e-KTP dan membuka portal
        $unlinkedWargas = Warga::whereDoesntHave('user')->get();
        if ($unlinkedWargas->isNotEmpty()) {
            foreach ($unlinkedWargas as $uw) {
                $cleanName = trim($uw->nama);
                $base = Str::slug($cleanName, '_');
                if (empty($base)) {
                    $base = 'warga_' . $uw->id;
                }
                $username = $base;
                $counter = 1;
                while (User::where('username', $username)->exists()) {
                    $username = $base . '_' . $counter;
                    $counter++;
                }

                User::create([
                    'nama' => $uw->nama,
                    'username' => $username,
                    'password' => \Illuminate\Support\Facades\Hash::make('warga123'),
                    'role' => 'warga',
                    'warga_id' => $uw->id,
                    'rfid_is_active' => true,
                ]);
            }
        }

        // Daftar semua user & warga untuk opsi pendaftaran KTP oleh admin
        $wargaUsers = User::with('warga')
            ->orderByRaw("CASE WHEN rfid_uid IS NOT NULL THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN role = 'super_admin' THEN 1 WHEN role IN ('rt', 'ketua') THEN 2 WHEN role IN ('bendahara_rt', 'bendahara', 'admin') THEN 3 WHEN role = 'sekretaris_rt' THEN 4 WHEN role = 'rw' THEN 5 WHEN role = 'bendahara_rw' THEN 6 WHEN role = 'sekretaris_rw' THEN 7 ELSE 8 END")
            ->orderBy('nama')
            ->get();

        $viewName = auth()->user()->isKetua() || auth()->user()->isRw() ? 'ketua.iot_gate' : 'admin.iot_gate';

        return view($viewName, compact('stats', 'logs', 'wargaUsers', 'statusFilter', 'search'));
    }

    /**
     * Admin mendaftarkan/mengubah KTP milik warga tertentu
     * POST /admin/iot-gate/register-warga
     */
    public function adminRegisterWarga(Request $request)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'rfid_uid' => ['required', 'string', 'max:64'],
        ]);

        $uid = self::normalizeUid($request->input('rfid_uid'));
        $targetUser = User::findOrFail($request->input('user_id'));

        $duplicate = User::where('rfid_uid', $uid)->where('id', '!=', $targetUser->id)->first();
        if ($duplicate) {
            return back()->with('error', 'KTP tersebut sudah digunakan oleh akun lain: ' . $duplicate->nama);
        }

        $targetUser->update([
            'rfid_uid' => $uid,
            'rfid_label' => $request->input('rfid_label', 'e-KTP'),
            'rfid_registered_at' => Carbon::now(),
            'rfid_is_active' => true,
        ]);

        return back()->with('success', 'e-KTP (' . $uid . ') berhasil didaftarkan untuk ' . $targetUser->nama);
    }

    /**
     * Aktifkan / Nonaktifkan akses KTP warga
     * POST /admin/iot-gate/toggle-status/{user}
     */
    public function adminToggleStatus(User $user)
    {
        $newStatus = !$user->rfid_is_active;
        $user->update(['rfid_is_active' => $newStatus]);

        $msg = $newStatus ? 'Akses KTP diaktifkan kembali.' : 'Akses KTP berhasil diblokir/dinonaktifkan.';
        return back()->with('success', $msg . ' (' . $user->nama . ')');
    }

    /**
     * Hapus KTP dari user tertentu oleh admin
     * DELETE /admin/iot-gate/remove-user/{user}
     */
    public function adminRemoveUserRfid(User $user)
    {
        $user->update([
            'rfid_uid' => null,
            'rfid_registered_at' => null,
            'rfid_is_active' => true,
        ]);

        return back()->with('success', 'Data KTP milik ' . $user->nama . ' telah dihapus.');
    }

    /**
     * Buka Portal Darurat via Web (Emergency Open)
     * POST /admin/iot-gate/manual-open
     */
    public function adminManualOpen(Request $request)
    {
        $operator = auth()->user()->nama ?? 'Pengurus RT';
        $deviceId = $request->input('device_id', 'PORTAL-UTAMA');

        GateAccessLog::create([
            'rfid_uid' => 'MANUAL-OVERRIDE',
            'user_id' => auth()->id(),
            'warga_nama' => $operator . ' (Buka Manual Web)',
            'no_rumah' => '-',
            'device_id' => $deviceId,
            'access_status' => 'granted',
            'gate_action' => 'MANUAL_OPEN',
            'denial_reason' => null,
            'ip_address' => $request->ip(),
            'tapped_at' => Carbon::now(),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'open_gate' => true,
                'message' => 'Perintah buka portal manual berhasil dikirim oleh ' . $operator,
            ]);
        }

        return back()->with('success', 'Perintah buka portal darurat berhasil dikirim.');
    }

    /**
     * Endpoint API untuk update log real-time di UI Admin
     * GET /admin/iot-gate/logs-data
     */
    public function adminLogsData(Request $request): JsonResponse
    {
        $logs = GateAccessLog::latest('tapped_at')->take(15)->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'tapped_at' => $log->tapped_at->format('H:i:s - d M Y'),
                'warga_nama' => $log->warga_nama,
                'no_rumah' => $log->no_rumah ?? '-',
                'rfid_uid' => $log->rfid_uid,
                'access_status' => $log->access_status,
                'gate_action' => $log->gate_action,
                'denial_reason' => $log->denial_reason,
            ];
        });

        return response()->json([
            'logs' => $logs,
            'today_total' => GateAccessLog::whereDate('tapped_at', Carbon::today())->count(),
            'today_granted' => GateAccessLog::whereDate('tapped_at', Carbon::today())->where('access_status', 'granted')->count(),
            'today_denied' => GateAccessLog::whereDate('tapped_at', Carbon::today())->where('access_status', 'denied')->count(),
        ]);
    }

    /**
     * Download kode sketch ESP32 Arduino (.ino)
     * GET /admin/iot-gate/firmware
     */
    public function downloadFirmware()
    {
        $filePath = base_path('firmware_esp32/rtex_portal_esp32.ino');
        if (!file_exists($filePath)) {
            $filePath = base_path('../firmware_esp32/rtex_portal_esp32.ino');
        }
        if (!file_exists($filePath)) {
            abort(404, 'File firmware tidak ditemukan.');
        }

        return Response::download($filePath, 'rtex_portal_esp32.ino', [
            'Content-Type' => 'text/plain',
        ]);
    }
}
