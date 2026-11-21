<?php

namespace Tests\Feature;

use App\Models\Tagihan;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class KetuaMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_dashboard_trend_handles_month_end_date_without_overflow(): void
    {
        Carbon::setTestNow('2026-03-31 12:00:00');

        $superAdmin = User::where('username', 'superadmin')->firstOrFail();
        $response = $this->actingAs($superAdmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $periodeGrafik = $response->viewData('periodeGrafik');
        $this->assertCount(6, $periodeGrafik);

        // Verify all 6 months are distinct and correctly consecutive
        $this->assertEquals([
            'Oktober 2025',
            'November 2025',
            'Desember 2025',
            'Januari 2026',
            'Februari 2026',
            'Maret 2026',
        ], $periodeGrafik);

        Carbon::setTestNow();
    }

    public function test_ketua_monitoring_only_displays_heads_of_household(): void
    {
        $superAdmin = User::where('username', 'superadmin')->firstOrFail();

        // Create family head and family member
        $kepala = Warga::create([
            'nama' => 'Kepala Keluarga Baru',
            'nik' => '3275010101900001',
            'nik_hash' => Warga::nikHash('3275010101900001'),
            'no_rumah' => 'Z99',
            'status_hunian' => 'Tetap',
            'status_aktif' => true,
            'status_pendaftaran' => 'Disetujui',
        ]);

        $anggota = Warga::create([
            'nama' => 'Anggota Keluarga Baru',
            'nik' => '3275010101900002',
            'nik_hash' => Warga::nikHash('3275010101900002'),
            'no_rumah' => 'Z99',
            'status_hunian' => 'Tetap',
            'status_aktif' => true,
            'status_pendaftaran' => 'Disetujui',
            'kepala_keluarga_id' => $kepala->id,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('ketua.monitoring'));
        $response->assertOk();

        $daftarWarga = $response->viewData('daftarWarga');
        $this->assertTrue($daftarWarga->contains('id', $kepala->id));
        $this->assertFalse($daftarWarga->contains('id', $anggota->id));
    }

    public function test_ketua_validasi_laporan_validates_periode_format(): void
    {
        $rt = User::where('username', 'ketua')->firstOrFail();

        $response = $this->actingAs($rt)->post(route('ketua.laporan.validasi'), [
            'periode' => 'invalid-periode',
        ]);

        $response->assertSessionHasErrors('periode');
    }
}
