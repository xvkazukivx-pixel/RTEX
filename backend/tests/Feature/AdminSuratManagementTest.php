<?php

namespace Tests\Feature;

use App\Models\PengajuanSurat;
use App\Models\PengaturanSurat;
use App\Models\User;
use App\Models\Warga;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSuratManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_super_admin_can_view_the_surat_management_page(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.surat'))
            ->assertOk()
            ->assertSee('Pengajuan Surat')
            ->assertSee('Ajukan Surat')
            ->assertSee('Belum ada pengajuan surat.')
            ->assertSee('<span>Surat</span>', false);
    }

    public function test_bendahara_cannot_access_super_admin_surat_management(): void
    {
        $bendahara = User::where('username', 'admin')->firstOrFail();

        $this->actingAs($bendahara)
            ->get(route('admin.surat'))
            ->assertForbidden();
    }

    public function test_super_admin_can_create_a_manual_applicant_with_encrypted_nik(): void
    {
        $payload = $this->manualApplicantData();

        $this->actingAs($this->superAdmin())
            ->from(route('admin.surat'))
            ->post(route('admin.surat.store'), $payload)
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasNoErrors();

        $surat = PengajuanSurat::where('nama_pemohon', 'Pemohon Contoh')->firstOrFail();

        $this->assertNull($surat->warga_id);
        $this->assertSame('1234567890123456', $surat->nik_pemohon);
        $this->assertSame(PengajuanSurat::STATUS_DIAJUKAN, $surat->status);
        $this->assertNotSame(
            '1234567890123456',
            DB::table('pengajuan_surat')->where('id', $surat->id)->value('nik_pemohon')
        );
    }

    public function test_manual_applicant_must_provide_a_sixteen_digit_nik(): void
    {
        $payload = $this->manualApplicantData(['nik_pemohon' => '12345']);

        $this->actingAs($this->superAdmin())
            ->from(route('admin.surat'))
            ->post(route('admin.surat.store'), $payload)
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasErrors('nik_pemohon');

        $this->assertDatabaseCount('pengajuan_surat', 0);
    }

    public function test_super_admin_can_create_a_request_for_a_registered_resident(): void
    {
        $warga = Warga::where('no_rumah', 'B-12')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.surat.store'), [
                'jenis_pemohon' => 'warga',
                'warga_id' => $warga->id,
                'jenis_surat' => 'Surat Keterangan Domisili',
                'keperluan' => 'Pengurusan dokumen',
            ])
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasNoErrors();

        $surat = PengajuanSurat::where('warga_id', $warga->id)->firstOrFail();

        $this->assertSame($warga->nama, $surat->nama_pemohon);
        $this->assertSame($warga->nik, $surat->nik_pemohon);
        $this->assertSame('warga', $surat->jenis_pemohon);
    }

    public function test_editing_a_processed_request_resets_it_for_review(): void
    {
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_DIPROSES,
            'nomor_surat' => '001/RT05/XI/2026',
            'catatan_admin' => 'Sudah dicek',
        ]);
        $warga = Warga::where('no_rumah', 'B-12')->firstOrFail();

        $this->actingAs($this->superAdmin())
            ->post(route('admin.surat.update', $surat), [
                'jenis_pemohon' => 'warga',
                'warga_id' => $warga->id,
                'jenis_surat' => 'Surat Pengantar SKCK',
                'keperluan' => 'Pengurusan SKCK',
            ])
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasNoErrors();

        $surat->refresh();

        $this->assertSame(PengajuanSurat::STATUS_DIAJUKAN, $surat->status);
        $this->assertNull($surat->nomor_surat);
        $this->assertNull($surat->diproses_oleh);
        $this->assertSame($warga->id, $surat->warga_id);
    }

    public function test_super_admin_can_mark_a_submitted_request_as_processing(): void
    {
        $surat = $this->createManualApplicant();
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->from(route('admin.surat'))
            ->post(route('admin.surat.action', $surat), [
                'aksi' => 'proses',
                'catatan_admin' => 'Data sudah diperiksa.',
            ])
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasNoErrors();

        $surat->refresh();

        $this->assertSame(PengajuanSurat::STATUS_DIPROSES, $surat->status);
        $this->assertSame($admin->id, $surat->diproses_oleh);
        $this->assertSame('Data sudah diperiksa.', $surat->catatan_admin);
    }

    public function test_super_admin_can_forward_a_processed_request_to_the_rt(): void
    {
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_DIPROSES,
        ]);

        $this->actingAs($this->superAdmin())
            ->from(route('admin.surat'))
            ->post(route('admin.surat.action', $surat), [
                'aksi' => 'kirim_persetujuan',
                'nomor_surat' => '002/RT05/XI/2026',
            ])
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasNoErrors();

        $surat->refresh();

        $this->assertSame(PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN, $surat->status);
        $this->assertSame('002/RT05/XI/2026', $surat->nomor_surat);
    }

    public function test_super_admin_cannot_bypass_rt_approval(): void
    {
        $surat = $this->createManualApplicant();

        $this->actingAs($this->superAdmin())
            ->from(route('admin.surat'))
            ->post(route('admin.surat.action', $surat), [
                'aksi' => 'setujui',
            ])
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHasErrors('aksi');

        $this->assertSame(PengajuanSurat::STATUS_DIAJUKAN, $surat->fresh()->status);
    }

    public function test_super_admin_cannot_delete_a_request_waiting_for_rt_approval(): void
    {
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_MENUNGGU_PERSETUJUAN,
        ]);

        $this->actingAs($this->superAdmin())
            ->from(route('admin.surat'))
            ->post(route('admin.surat.destroy', $surat))
            ->assertRedirect(route('admin.surat'))
            ->assertSessionHas('error', 'Surat yang menunggu persetujuan atau sudah disetujui tidak dapat dihapus.');

        $this->assertModelExists($surat);
    }

    public function test_super_admin_can_save_letterhead_settings_and_a_logo(): void
    {
        Storage::fake('public');
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_DISETUJUI,
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.surat.kop'), [
                'surat_id' => $surat->id,
                'nama_pemerintahan' => 'RTEX Administrasi Warga',
                'nama_kecamatan' => 'Kelurahan Contoh',
                'nama_rt_rw' => 'RT 05 / RW 01',
                'alamat_sekretariat' => 'Jl. Contoh No. 1',
                'nama_ketua' => 'Ketua Contoh',
                'jabatan_penanda_tangan' => 'Ketua RT',
                'kota_ttd' => 'Bekasi',
                'masa_berlaku' => '30 hari',
                'logo' => UploadedFile::fake()->image('kop.png', 80, 80),
            ])
            ->assertRedirect(route('surat.cetak', $surat))
            ->assertSessionHasNoErrors();

        $pengaturan = PengaturanSurat::query()->findOrFail(1);

        $this->assertSame('RTEX Administrasi Warga', $pengaturan->nama_pemerintahan);
        Storage::disk('public')->assertExists($pengaturan->logo_path);
    }

    public function test_letterhead_rejects_non_image_uploads(): void
    {
        Storage::fake('public');
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_DISETUJUI,
        ]);

        $this->actingAs($this->superAdmin())
            ->from(route('surat.cetak', $surat))
            ->post(route('admin.surat.kop'), [
                'surat_id' => $surat->id,
                'nama_pemerintahan' => 'RTEX',
                'nama_rt_rw' => 'RT 05 / RW 01',
                'jabatan_penanda_tangan' => 'Ketua RT',
                'logo' => UploadedFile::fake()->create('dokumen.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect(route('surat.cetak', $surat))
            ->assertSessionHasErrors('logo');

        $this->assertDatabaseCount('pengaturan_surat', 0);
    }

    public function test_approved_guest_letter_prints_escaped_applicant_data(): void
    {
        $surat = $this->createManualApplicant([
            'nama_pemohon' => '<script>alert(1)</script>',
            'status' => PengajuanSurat::STATUS_DISETUJUI,
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('surat.cetak', $surat));

        $response->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('Pratinjau &amp; Cetak Surat', false);
    }

    public function test_warga_cannot_print_an_approved_guest_letter(): void
    {
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_DISETUJUI,
        ]);
        $warga = User::where('username', 'hafis')->firstOrFail();

        $this->actingAs($warga)
            ->get(route('surat.cetak', $surat))
            ->assertForbidden();
    }

    public function test_unlinked_warga_cannot_print_an_approved_guest_letter(): void
    {
        $surat = $this->createManualApplicant([
            'status' => PengajuanSurat::STATUS_DISETUJUI,
        ]);
        $unlinkedWarga = User::create([
            'nama' => 'Warga Tanpa Data',
            'username' => 'wargatanpadata',
            'password' => 'password123',
            'role' => 'warga',
            'warga_id' => null,
        ]);

        $this->actingAs($unlinkedWarga)
            ->get(route('surat.cetak', $surat))
            ->assertForbidden();
    }

    /**
     * @return array<string, string|null>
     */
    private function manualApplicantData(array $overrides = []): array
    {
        return [
            'jenis_pemohon' => 'non_warga',
            'nama_pemohon' => 'Pemohon Contoh',
            'nik_pemohon' => '1234567890123456',
            'jenis_kelamin' => 'Perempuan',
            'tempat_lahir' => 'Bekasi',
            'tanggal_lahir' => '1990-01-02',
            'agama' => 'Islam',
            'pekerjaan' => 'Karyawan',
            'status_perkawinan' => 'Belum Kawin',
            'kewarganegaraan' => 'Indonesia',
            'alamat_ktp' => 'Jl. KTP No. 1',
            'alamat_domisili' => 'Jl. Domisili No. 2',
            'jenis_surat' => 'Surat Keterangan Domisili',
            'keperluan' => 'Administrasi warga',
            ...$overrides,
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function createManualApplicant(array $overrides = []): PengajuanSurat
    {
        return PengajuanSurat::create([
            ...$this->manualApplicantData(),
            'warga_id' => null,
            'catatan_warga' => null,
            'status' => PengajuanSurat::STATUS_DIAJUKAN,
            ...$overrides,
        ]);
    }

    private function superAdmin(): User
    {
        return User::where('username', 'superadmin')->firstOrFail();
    }
}
