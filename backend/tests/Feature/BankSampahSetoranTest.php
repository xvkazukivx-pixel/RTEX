<?php

namespace Tests\Feature;

use App\Models\BankSampahJenis;
use App\Models\BankSampahSetoran;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankSampahSetoranTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_warga_can_submit_a_setoran_and_see_it_in_their_history(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();
        $jenis = BankSampahJenis::where('nama', 'Kardus')->firstOrFail();

        $this->actingAs($warga)
            ->from(route('warga.bank_sampah'))
            ->post(route('warga.bank_sampah.store'), [
                'jenis_id' => $jenis->id,
                'pilihan_sampah' => 'sampah-2-0',
                'nama_sampah' => 'Kardus bekas paket',
                'harga_per_kg' => '1000.00',
                'berat_estimasi' => '3.50',
                'catatan' => 'Sudah dipilah',
            ])
            ->assertRedirect(route('warga.bank_sampah'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $setoran = BankSampahSetoran::where('warga_id', $warga->warga_id)->firstOrFail();

        $this->assertDatabaseHas('bank_sampah_setoran', [
            'id' => $setoran->id,
            'jenis_id' => $jenis->id,
            'nama_sampah' => 'Kardus bekas paket',
            'berat_estimasi' => '3.50',
            'status' => 'Menunggu Validasi',
        ]);

        $this->get(route('warga.bank_sampah'))
            ->assertOk()
            ->assertSee('Kardus')
            ->assertSee('Koran')
            ->assertSee('Buku bekas')
            ->assertSee('Majalah')
            ->assertSee('Kaleng aluminium')
            ->assertSee('Kaleng besi')
            ->assertSee('Besi bekas')
            ->assertSee('Botol kaca')
            ->assertSee('Pakaian bekas')
            ->assertSee('Kabel tembaga')
            ->assertSee('Gelas plastik')
            ->assertSee('Plastik kemasan')
            ->assertSee('Elektronik')
            ->assertSee('Lainnya')
            ->assertSee('name="jenis_id"', false)
            ->assertSee('id="nama_sampah"', false)
            ->assertSee('id="harga_per_kg"', false)
            ->assertSee('Menunggu Validasi');
    }

    public function test_warga_cannot_submit_a_setoran_with_invalid_required_fields(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();

        $this->actingAs($warga)
            ->from(route('warga.bank_sampah'))
            ->post(route('warga.bank_sampah.store'), [
                'jenis_id' => '',
                'nama_sampah' => '',
                'berat_estimasi' => '0',
            ])
            ->assertRedirect(route('warga.bank_sampah'))
            ->assertSessionHasErrors(['jenis_id', 'nama_sampah', 'berat_estimasi']);

        $this->assertDatabaseCount('bank_sampah_setoran', 0);
    }

    public function test_super_admin_can_add_waste_type_with_category_and_examples(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.bank_sampah'))
            ->post(route('admin.bank_sampah.jenis.store'), [
                'nama' => 'Pecahan kaca',
                'kategori' => 'Kaca',
                'contoh_sampah' => 'Botol kaca, pecahan kaca',
                'harga_per_kg' => '1000',
            ])
            ->assertRedirect(route('admin.bank_sampah'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bank_sampah_jenis', [
            'nama' => 'Pecahan kaca',
            'kategori' => 'Kaca',
            'contoh_sampah' => 'Botol kaca, pecahan kaca',
            'harga_per_kg' => '1000.00',
        ]);
    }

    public function test_super_admin_can_record_and_accept_a_setoran(): void
    {
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $warga = User::where('username', 'hafis')->firstOrFail();
        $jenis = BankSampahJenis::where('nama', 'Kardus')->firstOrFail();

        $this->actingAs($admin)
            ->from(route('admin.bank_sampah'))
            ->post(route('admin.bank_sampah.setoran.store'), [
                'warga_id' => $warga->warga_id,
                'jenis_id' => $jenis->id,
                'berat' => '2.50',
                'catatan' => 'Sudah ditimbang',
            ])
            ->assertRedirect(route('admin.bank_sampah'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $setoran = BankSampahSetoran::where('warga_id', $warga->warga_id)->firstOrFail();

        $this->assertSame('Diterima', $setoran->status);
        $this->assertSame('2.50', $setoran->berat_final);
        $this->assertSame('1000.00', $setoran->harga_per_kg);
        $this->assertSame('2500.00', $setoran->nilai_setoran);
        $this->assertSame($admin->id, $setoran->diproses_oleh);
    }

    public function test_bendahara_can_manage_bank_sampah_prices_without_access_to_setoran_controls(): void
    {
        $bendahara = User::where('username', 'admin')->firstOrFail();
        $jenis = BankSampahJenis::where('nama', 'Botol PET')->firstOrFail();

        $this->actingAs($bendahara)
            ->get(route('admin.bank_sampah.harga'))
            ->assertOk()
            ->assertSee('Tarif per Jenis Sampah');

        $this->from(route('admin.bank_sampah.harga'))
            ->post(route('admin.bank_sampah.harga.update', $jenis->id), [
                'harga_per_kg' => '4200',
            ])
            ->assertRedirect(route('admin.bank_sampah.harga'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bank_sampah_jenis', [
            'id' => $jenis->id,
            'harga_per_kg' => '4200.00',
        ]);

        $this->from(route('admin.bank_sampah.harga'))
            ->post(route('admin.bank_sampah.harga.store'), [
                'nama' => 'Sampah Organik',
                'kategori' => 'Organik',
                'contoh_sampah' => 'Sisa makanan, daun',
                'harga_per_kg' => '750',
            ])
            ->assertRedirect(route('admin.bank_sampah.harga'))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bank_sampah_jenis', [
            'nama' => 'Sampah Organik',
            'kategori' => 'Organik',
            'harga_per_kg' => '750.00',
            'status_aktif' => true,
        ]);

        $this->get(route('admin.bank_sampah'))
            ->assertForbidden();
    }

    public function test_setoran_uses_the_rate_in_effect_when_the_warga_submitted_it(): void
    {
        $warga = User::where('username', 'hafis')->firstOrFail();
        $admin = User::where('username', 'superadmin')->firstOrFail();
        $jenis = BankSampahJenis::where('nama', 'Kardus')->firstOrFail();

        $this->actingAs($warga)
            ->from(route('warga.bank_sampah'))
            ->post(route('warga.bank_sampah.store'), [
                'jenis_id' => $jenis->id,
                'pilihan_sampah' => 'sampah-2-0',
                'nama_sampah' => 'Kardus bekas',
                'berat_estimasi' => '3.50',
            ])
            ->assertRedirect(route('warga.bank_sampah'));

        $jenis->update(['harga_per_kg' => 10000]);
        $setoran = BankSampahSetoran::where('warga_id', $warga->warga_id)->firstOrFail();

        $this->actingAs($admin)
            ->post(route('admin.bank_sampah.setoran.action', $setoran->id), [
                'aksi' => 'terima',
                'berat_final' => '3.50',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bank_sampah_setoran', [
            'id' => $setoran->id,
            'harga_per_kg' => '1000.00',
            'nilai_setoran' => '3500.00',
            'status' => 'Diterima',
        ]);
    }
}
