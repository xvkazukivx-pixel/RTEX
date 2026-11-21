<?php

namespace Tests\Feature;

use App\Models\GateAccessLog;
use App\Models\RfidScanSession;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IotGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_esp32_denies_access_when_ktp_is_not_registered(): void
    {
        $response = $this->postJson('/api/iot/gate/tap', [
            'uid' => '04:5A:B2:1A',
            'device_id' => 'PORTAL-UTAMA',
            'api_key' => 'rtex-gate-key-2026',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'mode' => 'gate_access',
                'access' => 'denied',
                'open_gate' => false,
            ]);

        $this->assertDatabaseHas('gate_access_logs', [
            'rfid_uid' => '04:5A:B2:1A',
            'access_status' => 'denied',
            'gate_action' => 'NONE',
        ]);
    }

    public function test_esp32_grants_access_and_opens_gate_when_ktp_is_registered(): void
    {
        $warga = Warga::create([
            'nama' => 'Bapak Budi',
            'no_rumah' => 'A-12',
            'alamat' => 'Jl. Mawar No. 12',
            'no_hp' => '081234567890',
            'status_hunian' => 'Tetap',
            'status_aktif' => true,
        ]);

        $user = User::create([
            'nama' => 'Bapak Budi',
            'username' => 'budi',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'warga_id' => $warga->id,
            'rfid_uid' => '4A:2F:89:1B',
            'rfid_is_active' => true,
        ]);

        // Kirim dalam format lowercase / tanpa pemisah untuk menguji normalisasi sistem
        $response = $this->postJson('/api/iot/gate/tap', [
            'uid' => '4a2f891b',
            'device_id' => 'PORTAL-UTAMA',
            'api_key' => 'rtex-gate-key-2026',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'mode' => 'gate_access',
                'access' => 'granted',
                'open_gate' => true,
                'user_name' => 'Bapak Budi',
                'house_number' => 'A-12',
            ]);

        $this->assertDatabaseHas('gate_access_logs', [
            'user_id' => $user->id,
            'rfid_uid' => '4A:2F:89:1B',
            'access_status' => 'granted',
            'gate_action' => 'OPEN_GATE',
        ]);
    }

    public function test_esp32_denies_access_when_ktp_is_deactivated_by_admin(): void
    {
        $user = User::create([
            'nama' => 'Warga Nonaktif',
            'username' => 'nonaktif',
            'password' => bcrypt('password123'),
            'role' => 'warga',
            'rfid_uid' => '55:66:77:88',
            'rfid_is_active' => false,
        ]);

        $response = $this->postJson('/api/iot/gate/tap', [
            'uid' => '55:66:77:88',
            'api_key' => 'rtex-gate-key-2026',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
                'access' => 'denied',
                'open_gate' => false,
            ]);

        $this->assertDatabaseHas('gate_access_logs', [
            'rfid_uid' => '55:66:77:88',
            'access_status' => 'denied',
        ]);
    }

    public function test_scan_pairing_mode_registers_uid_to_logged_in_user(): void
    {
        $user = User::create([
            'nama' => 'Warga Baru',
            'username' => 'wargabaru',
            'password' => bcrypt('password123'),
            'role' => 'warga',
        ]);

        // 1. User login dan klik 'Mulai Scan KTP'
        $scanInit = $this->actingAs($user)->postJson('/warga/rfid/start-scan');
        $scanInit->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('rfid_scan_sessions', [
            'user_id' => $user->id,
            'status' => 'waiting',
        ]);

        // 2. ESP32 men-tap kartu di sensor gerbang
        $tapResponse = $this->postJson('/api/iot/gate/tap', [
            'uid' => 'AA:BB:CC:DD',
            'api_key' => 'rtex-gate-key-2026',
        ]);

        $tapResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'mode' => 'registration',
                'status' => 'registered',
            ]);

        // Cek user terupdate
        $user->refresh();
        $this->assertEquals('AA:BB:CC:DD', $user->rfid_uid);

        // 3. Web polling check-scan
        $checkResponse = $this->actingAs($user)->getJson('/warga/rfid/check-scan');
        $checkResponse->assertStatus(200)
            ->assertJson([
                'status' => 'scanned',
                'uid' => 'AA:BB:CC:DD',
            ]);
    }

    public function test_warga_can_register_manual_and_simulate_gate_tap(): void
    {
        $user = User::create([
            'nama' => 'Hafis',
            'username' => 'hafis',
            'password' => bcrypt('password123'),
            'role' => 'warga',
        ]);

        // Input manual
        $this->actingAs($user)->post('/warga/rfid/register-manual', [
            'rfid_uid' => '12:34:56:78',
        ])->assertRedirect();

        $user->refresh();
        $this->assertEquals('12:34:56:78', $user->rfid_uid);

        // Simulasi tap gerbang langsung dari web
        $simResponse = $this->actingAs($user)->postJson('/warga/rfid/simulate-tap');
        $simResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'access' => 'granted',
                'open_gate' => true,
            ]);
    }

    public function test_admin_can_trigger_emergency_manual_open(): void
    {
        $admin = User::create([
            'nama' => 'Super Admin',
            'username' => 'superadmin',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
        ]);

        $response = $this->actingAs($admin)->post('/admin/iot-gate/manual-open');
        $response->assertRedirect();

        $this->assertDatabaseHas('gate_access_logs', [
            'gate_action' => 'MANUAL_OPEN',
            'access_status' => 'granted',
        ]);
    }
}
