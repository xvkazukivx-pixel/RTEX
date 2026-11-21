<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Warga;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminFamilyManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_create_a_head_and_family_members_with_encrypted_nik(): void
    {
        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'Kepala Contoh',
                'nik' => '1234567890123456',
                'no_rumah' => 'C-10',
                'alamat' => 'Jalan Contoh',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => [
                    ['nama' => 'Anggota Satu', 'nik' => '2234567890123456'],
                    ['nama' => 'Anggota Dua', 'nik' => '3234567890123456'],
                ],
            ])
            ->assertRedirect(route('admin.warga'))
            ->assertSessionHasNoErrors();

        $kepala = Warga::where('no_rumah', 'C-10')->firstOrFail();
        $anggota = $kepala->anggotaKeluarga()->orderBy('id')->get();

        $this->assertSame('1234567890123456', $kepala->nik);
        $this->assertCount(2, $anggota);
        $this->assertSame($kepala->id, $anggota[0]->kepala_keluarga_id);
        $this->assertSame('2234567890123456', $anggota[0]->nik);
        $this->assertNotSame('1234567890123456', DB::table('warga')->where('id', $kepala->id)->value('nik'));
        $this->assertSame(Warga::nikHash('1234567890123456'), $kepala->nik_hash);

        $this->get(route('admin.warga'))
            ->assertOk()
            ->assertSee('Kepala Contoh')
            ->assertSee('Anggota Satu')
            ->assertSee('1234567890123456');
    }

    public function test_super_admin_cannot_create_a_second_head_for_the_same_house(): void
    {
        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'Kepala Duplikat',
                'nik' => '4234567890123456',
                'no_rumah' => 'B-12',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => [
                    ['nama' => 'Anggota Duplikat', 'nik' => '4234567890123457'],
                ],
            ])
            ->assertSessionHasErrors([
                'no_rumah' => 'Nomor rumah ini sudah memiliki kepala keluarga.',
            ]);

        $this->assertDatabaseMissing('warga', ['nama' => 'Kepala Duplikat']);
    }

    public function test_super_admin_cannot_create_family_with_invalid_nik(): void
    {
        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'NIK Tidak Valid',
                'nik' => '123',
                'no_rumah' => 'C-11',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => [
                    ['nama' => 'Anggota NIK Invalid', 'nik' => '1234567890123457'],
                ],
            ])
            ->assertSessionHasErrors('nik');

        $this->assertDatabaseMissing('warga', ['no_rumah' => 'C-11']);
    }

    public function test_super_admin_cannot_reuse_an_existing_nik(): void
    {
        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'Keluarga NIK Pertama',
                'nik' => '9234567890123456',
                'no_rumah' => 'C-13',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => [
                    ['nama' => 'Anggota NIK Pertama', 'nik' => '9234567890123457'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->from(route('admin.warga'))
            ->post(route('admin.warga.store'), [
                'nama' => 'Keluarga NIK Duplikat',
                'nik' => '9234567890123456',
                'no_rumah' => 'C-14',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => [
                    ['nama' => 'Anggota NIK Duplikat', 'nik' => '9234567890123458'],
                ],
            ])
            ->assertSessionHasErrors([
                'nik' => 'NIK ini sudah terdaftar.',
            ]);

        $this->assertDatabaseMissing('warga', ['no_rumah' => 'C-14']);
    }

    public function test_super_admin_cannot_create_a_house_without_an_additional_resident(): void
    {
        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'Keluarga Sendiri',
                'nik' => '8234567890123456',
                'no_rumah' => 'C-16',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
            ])
            ->assertSessionHasErrors([
                'anggota' => 'Setiap rumah minimal terdiri dari kepala keluarga dan satu anggota.',
            ]);

        $this->assertDatabaseMissing('warga', ['no_rumah' => 'C-16']);
    }

    public function test_family_creation_rejects_more_than_four_additional_members(): void
    {
        $anggota = [];
        for ($index = 1; $index <= 5; $index++) {
            $anggota[] = [
                'nama' => 'Anggota '.$index,
                'nik' => sprintf('%016d', 1000000000000000 + $index),
            ];
        }

        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'Keluarga Terlalu Besar',
                'nik' => '1123456789012345',
                'no_rumah' => 'C-15',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => $anggota,
            ])
            ->assertSessionHasErrors('anggota');

        $this->assertDatabaseMissing('warga', ['no_rumah' => 'C-15']);
    }

    public function test_super_admin_can_add_a_member_to_an_existing_household(): void
    {
        $kepala = Warga::where('no_rumah', 'B-12')->firstOrFail();

        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.anggota.store', $kepala->id), [
                'nama' => 'Anggota Tambahan',
                'nik' => '8234567890123456',
            ])
            ->assertRedirect(route('admin.warga'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('warga', [
            'nama' => 'Anggota Tambahan',
            'no_rumah' => 'B-12',
            'kepala_keluarga_id' => $kepala->id,
        ]);
    }

    public function test_super_admin_cannot_remove_the_last_member_of_a_household(): void
    {
        $kepala = Warga::where('no_rumah', 'B-12')->firstOrFail();
        $kepala->anggotaKeluarga()->oldest()->firstOrFail()->delete();
        $anggotaTerakhir = $kepala->anggotaKeluarga()->firstOrFail();

        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.destroy', $anggotaTerakhir->id))
            ->assertRedirect(route('admin.warga'))
            ->assertSessionHas('error', 'Setiap rumah harus memiliki minimal 2 warga. Tambahkan anggota lain sebelum menghapus warga ini.');

        $this->assertModelExists($anggotaTerakhir);
    }

    public function test_demo_households_have_members_linked_to_their_heads(): void
    {
        $rumahTanpaAnggota = Warga::whereNull('kepala_keluarga_id')
            ->whereDoesntHave('anggotaKeluarga')
            ->pluck('no_rumah')
            ->all();

        $this->assertSame([], $rumahTanpaAnggota);

        $this->actingAs(User::where('username', 'superadmin')->first())
            ->get(route('admin.warga'))
            ->assertOk()
            ->assertSee('Data Kartu Keluarga')
            ->assertSee('Kontrak / Tidak Tetap')
            ->assertSee('Siti Ahmad')
            ->assertSee('Total warga dalam KK');
    }

    public function test_generated_iuran_is_created_only_for_the_head_of_household(): void
    {
        $this->from(route('admin.warga'))
            ->actingAs(User::where('username', 'superadmin')->first())
            ->post(route('admin.warga.store'), [
                'nama' => 'Kepala Tagihan',
                'nik' => '5234567890123456',
                'no_rumah' => 'C-12',
                'status_hunian' => 'Tetap',
                'status_aktif' => '1',
                'anggota' => [
                    ['nama' => 'Anggota Tagihan', 'nik' => '6234567890123456'],
                ],
            ])
            ->assertSessionHasNoErrors();

        $kepala = Warga::where('no_rumah', 'C-12')->firstOrFail();
        $anggota = $kepala->anggotaKeluarga()->firstOrFail();

        $this->post(route('admin.dashboard.generate'));

        $this->assertDatabaseHas('tagihan', ['warga_id' => $kepala->id]);
        $this->assertDatabaseMissing('tagihan', ['warga_id' => $anggota->id]);
    }

    public function test_public_registration_joins_the_existing_household(): void
    {
        $kepala = Warga::where('no_rumah', 'B-12')->firstOrFail();

        $this->post(route('register.post'), [
            'nama' => 'Pendaftar Anggota',
            'nik' => '7234567890123456',
            'no_rumah' => 'B-12',
            'status_hunian' => 'Tetap',
            'username' => 'anggota_b12',
            'password' => 'password123',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors();

        $anggota = Warga::where('nama', 'Pendaftar Anggota')->firstOrFail();

        $this->assertSame($kepala->id, $anggota->kepala_keluarga_id);
        $this->assertSame('7234567890123456', $anggota->nik);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('NIK (16 digit)')
            ->assertSee('Jika nomor rumah sudah terdaftar, akun akan menjadi anggota keluarga.');
    }
}
