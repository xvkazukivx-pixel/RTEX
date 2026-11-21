<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_login_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_ketua_can_login_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'ketua',
            'password' => 'ketua123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('ketua.surat'));
        $this->assertAuthenticated();
    }

    public function test_rw_can_login_and_only_reaches_confirmation_routes(): void
    {
        $response = $this->post('/login', [
            'username' => 'rw',
            'password' => 'rw123',
        ]);

        $response->assertRedirect(route('ketua.surat'));
        $this->assertAuthenticated();
    }

    public function test_super_admin_can_login_and_redirect_to_full_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'superadmin',
            'password' => 'super123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_warga_can_login_and_redirect_to_dashboard(): void
    {
        $response = $this->post('/login', [
            'username' => 'hafis',
            'password' => 'warga123',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('warga.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect(route('login'));
    }

    public function test_warga_cannot_access_admin_dashboard(): void
    {
        $user = User::where('username', 'hafis')->first();
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(403);
    }

    public function test_bendahara_cannot_access_super_admin_warga_management(): void
    {
        $user = User::where('username', 'admin')->first();
        $response = $this->actingAs($user)->get('/admin/warga');
        $response->assertStatus(403);
    }

    public function test_rt_cannot_access_bendahara_finance_routes(): void
    {
        $user = User::where('username', 'ketua')->first();
        $response = $this->actingAs($user)->get('/admin/iuran');
        $response->assertStatus(403);
    }

    public function test_rw_can_access_confirmation_routes(): void
    {
        $user = User::where('username', 'rw')->first();
        $this->actingAs($user)->get(route('ketua.surat'))->assertOk();
        $this->actingAs($user)->get(route('ketua.laporan'))->assertOk();
    }

    public function test_super_admin_can_access_warga_management(): void
    {
        $user = User::where('username', 'superadmin')->first();
        $this->actingAs($user)->get('/admin/warga')->assertOk();
    }

    public function test_super_admin_can_see_finance_charts(): void
    {
        $user = User::where('username', 'superadmin')->first();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pemasukan enam bulan')
            ->assertSee('Pembayaran perlu diperiksa');

        $this->actingAs($user)
            ->get(route('admin.iuran'))
            ->assertOk()
            ->assertSee('Grafik Nominal Iuran');

        $this->actingAs($user)
            ->get(route('admin.laporan'))
            ->assertOk()
            ->assertSee('Tren Pemasukan 6 Bulan Terakhir')
            ->assertSee('Persentase Kepatuhan');
    }
}
