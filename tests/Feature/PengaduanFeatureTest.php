<?php

namespace Tests\Feature;

use App\Models\Pengaduan;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengaduanFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_warga_can_submit_pengaduan_with_photo(): void
    {
        Storage::fake('public');
        $warga = User::where('username', 'hafis')->firstOrFail();

        $this->actingAs($warga)
            ->from(route('warga.pengaduan'))
            ->post(route('warga.pengaduan.store'), [
                'kategori' => 'Sarana & Prasarana',
                'judul' => 'Pipa saluran air tersumbat',
                'deskripsi' => 'Pipa di depan rumah warga nomor 10 tersumbat.',
                'foto' => UploadedFile::fake()->image('pengaduan.jpg'),
            ])
            ->assertRedirect(route('warga.pengaduan'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $pengaduan = Pengaduan::where('warga_id', $warga->warga_id)->firstOrFail();

        $this->assertSame('Pipa saluran air tersumbat', $pengaduan->judul);
        $this->assertSame('Sarana & Prasarana', $pengaduan->kategori);
        $this->assertSame('Diajukan', $pengaduan->status);
        Storage::disk('public')->assertExists($pengaduan->foto);

        $this->get(route('warga.pengaduan'))
            ->assertOk()
            ->assertSee('Pipa saluran air tersumbat');
    }

    public function test_warga_cannot_submit_pengaduan_with_invalid_required_fields(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();

        $this->actingAs($warga)
            ->from(route('warga.pengaduan'))
            ->post(route('warga.pengaduan.store'), [
                'kategori' => '',
                'judul' => '',
                'deskripsi' => '',
            ])
            ->assertRedirect(route('warga.pengaduan'))
            ->assertSessionHasErrors(['kategori', 'judul', 'deskripsi']);

        $this->assertDatabaseCount('pengaduan', 0);
    }

    public function test_super_admin_can_update_pengaduan_status_and_response(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $pengaduan = Pengaduan::create([
            'warga_id' => $warga->warga_id,
            'kategori' => 'Lingkungan',
            'judul' => 'Sampah menumpuk',
            'deskripsi' => 'Sampah di titik kumpul belum diangkut.',
            'status' => 'Diajukan',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.pengaduan'))
            ->post(route('admin.pengaduan.action', $pengaduan), [
                'status' => 'Diterima',
                'tanggapan' => 'Petugas akan memeriksa lokasi besok.',
            ])
            ->assertRedirect(route('admin.pengaduan'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $pengaduan->refresh();

        $this->assertSame('Diterima', $pengaduan->status);
        $this->assertSame('Petugas akan memeriksa lokasi besok.', $pengaduan->tanggapan);
        $this->assertSame($admin->id, $pengaduan->diproses_oleh);
        $this->assertNotNull($pengaduan->diproses_pada);

        $this->get(route('admin.pengaduan'))
            ->assertOk()
            ->assertSee('Sampah menumpuk')
            ->assertSee('Diterima');
    }
}
