<?php

namespace Tests\Feature;

use App\Models\InventarisBarang;
use App\Models\InventarisPeminjaman;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventarisFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_add_inventory_barang(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.inventaris'))
            ->post(route('admin.inventaris.barang.store'), [
                'nama' => 'Meja Lipat',
                'kategori' => 'Peralatan Acara',
                'deskripsi' => 'Meja lipat untuk stand kegiatan',
                'jumlah_total' => 4,
                'kondisi' => 'Baik',
            ])
            ->assertRedirect(route('admin.inventaris'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventaris_barang', [
            'nama' => 'Meja Lipat',
            'jumlah_total' => 4,
            'jumlah_tersedia' => 4,
            'kondisi' => 'Baik',
        ]);
    }

    public function test_warga_can_request_inventory_borrowing(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();
        $barang = InventarisBarang::where('nama', 'Tenda Lipat')->firstOrFail();

        $this->actingAs($warga)
            ->from(route('warga.inventaris'))
            ->post(route('warga.inventaris.pinjam.store'), [
                'barang_id' => $barang->id,
                'jumlah' => 1,
                'tanggal_pinjam' => '2026-12-01',
                'rencana_kembali' => '2026-12-03',
                'keperluan' => 'Kegiatan kerja bakti lingkungan',
            ])
            ->assertRedirect(route('warga.inventaris'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('inventaris_peminjaman', [
            'barang_id' => $barang->id,
            'warga_id' => $warga->warga_id,
            'jumlah' => 1,
            'status' => 'Diajukan',
        ]);

        $this->get(route('warga.inventaris'))
            ->assertOk()
            ->assertSee('Tenda Lipat')
            ->assertSee('Diajukan');
    }

    public function test_admin_approval_reduces_available_stock_and_return_restores_it(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $barang = InventarisBarang::where('nama', 'Tenda Lipat')->firstOrFail();
        $peminjaman = InventarisPeminjaman::create([
            'barang_id' => $barang->id,
            'warga_id' => $warga->warga_id,
            'nama_peminjam' => $warga->nama,
            'jumlah' => 1,
            'tanggal_pinjam' => '2026-12-01',
            'rencana_kembali' => '2026-12-03',
            'status' => 'Diajukan',
            'keperluan' => 'Acara warga',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.inventaris'))
            ->post(route('admin.inventaris.peminjaman.action', $peminjaman), [
                'aksi' => 'setujui',
                'catatan' => 'Silakan ambil di sekretariat.',
            ])
            ->assertRedirect(route('admin.inventaris'))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, $barang->refresh()->jumlah_tersedia);
        $this->assertSame('Dipinjam', $peminjaman->refresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.inventaris.peminjaman.action', $peminjaman), [
                'aksi' => 'kembalikan',
                'catatan' => 'Barang diterima kembali dalam kondisi baik.',
            ])
            ->assertRedirect(route('admin.inventaris'));

        $this->assertSame(3, $barang->refresh()->jumlah_tersedia);
        $this->assertSame('Dikembalikan', $peminjaman->refresh()->status);
    }
}
