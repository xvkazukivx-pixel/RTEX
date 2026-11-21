<?php

namespace Database\Seeders;

use App\Models\BankSampahJenis;
use App\Models\IuranSetting;
use App\Models\InventarisBarang;
use App\Models\User;
use App\Models\Warga;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Data Warga Awal
        $warga1 = Warga::create([
            'id' => 1,
            'nama' => 'Bapak Hafis',
            'no_rumah' => 'B-12',
            'alamat' => 'Jl. Mawar No. 12',
            'no_hp' => '081234567890',
            'status_hunian' => 'Tetap',
            'status_aktif' => true,
            'status_pendaftaran' => 'Disetujui',
        ]);

        $warga2 = Warga::create([
            'id' => 2,
            'nama' => 'Ibu Sari',
            'no_rumah' => 'B-14',
            'alamat' => 'Jl. Mawar No. 14',
            'no_hp' => '081234567891',
            'status_hunian' => 'Kontrak',
            'status_aktif' => true,
            'status_pendaftaran' => 'Disetujui',
        ]);

        // 2. Akun Users
        User::create([
            'nama' => 'Bendahara RT',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'bendahara',
            'warga_id' => null,
        ]);

        User::create([
            'nama' => 'Ketua RT 05',
            'username' => 'ketua',
            'password' => Hash::make('ketua123'),
            'role' => 'rt',
            'warga_id' => null,
        ]);

        User::create([
            'nama' => 'Ketua RW',
            'username' => 'rw',
            'password' => Hash::make('rw123'),
            'role' => 'rw',
            'warga_id' => null,
        ]);

        User::create([
            'nama' => 'Super Admin',
            'username' => 'superadmin',
            'password' => Hash::make('super123'),
            'role' => 'super_admin',
            'warga_id' => null,
        ]);

        User::create([
            'nama' => 'Bapak Hafis',
            'username' => 'hafis',
            'password' => Hash::make('warga123'),
            'role' => 'warga',
            'warga_id' => $warga1->id,
        ]);

        // 3. Jenis Iuran Awal
        IuranSetting::create([
            'nama_iuran' => 'Iuran Sampah',
            'nominal' => 20000,
            'status_aktif' => true,
        ]);

        IuranSetting::create([
            'nama_iuran' => 'Iuran Keamanan',
            'nominal' => 30000,
            'status_aktif' => true,
        ]);

        // 4. Jenis Bank Sampah Awal
        $jenisSampah = [
            ['nama' => 'Botol PET', 'kategori' => 'Plastik', 'harga_per_kg' => 1500],
            ['nama' => 'Gelas plastik', 'kategori' => 'Plastik', 'harga_per_kg' => 500],
            ['nama' => 'Plastik kemasan', 'kategori' => 'Plastik', 'harga_per_kg' => 500],
            ['nama' => 'Kardus', 'kategori' => 'Kertas', 'harga_per_kg' => 1000],
            ['nama' => 'Koran', 'kategori' => 'Kertas', 'harga_per_kg' => 1500],
            ['nama' => 'Buku bekas', 'kategori' => 'Kertas', 'harga_per_kg' => 1500],
            ['nama' => 'Majalah', 'kategori' => 'Kertas', 'harga_per_kg' => 800],
            ['nama' => 'Kaleng aluminium', 'kategori' => 'Logam', 'harga_per_kg' => 25000],
            ['nama' => 'Kaleng besi', 'kategori' => 'Logam', 'harga_per_kg' => 1000],
            ['nama' => 'Besi bekas', 'kategori' => 'Logam', 'harga_per_kg' => 5000],
            ['nama' => 'Botol kaca', 'kategori' => 'Kaca', 'harga_per_kg' => 500],
            ['nama' => 'Pakaian bekas', 'kategori' => 'Tekstil', 'harga_per_kg' => 500],
            ['nama' => 'Kabel tembaga', 'kategori' => 'Elektronik', 'harga_per_kg' => 100000],
        ];

        foreach ($jenisSampah as $item) {
            BankSampahJenis::updateOrCreate(
                ['nama' => $item['nama']],
                [
                    ...$item,
                    'contoh_sampah' => $item['nama'],
                    'status_aktif' => true,
                ]
            );
        }

        BankSampahJenis::whereIn('nama', ['Plastik Campur', 'Kertas', 'Logam'])
            ->update(['status_aktif' => false]);

        InventarisBarang::updateOrCreate(
            ['nama' => 'Tenda Lipat'],
            ['kategori' => 'Perlengkapan Kegiatan', 'deskripsi' => 'Tenda lipat untuk kegiatan warga', 'jumlah_total' => 3, 'jumlah_tersedia' => 3, 'jumlah_perbaikan' => 0, 'kondisi' => 'Baik']
        );
        InventarisBarang::updateOrCreate(
            ['nama' => 'Kursi Plastik'],
            ['kategori' => 'Peralatan Acara', 'deskripsi' => 'Kursi serbaguna untuk rapat dan acara RT', 'jumlah_total' => 100, 'jumlah_tersedia' => 92, 'jumlah_perbaikan' => 8, 'kondisi' => 'Baik']
        );
        InventarisBarang::updateOrCreate(
            ['nama' => 'Sound System'],
            ['kategori' => 'Peralatan Elektronik', 'deskripsi' => 'Perangkat suara portabel untuk kegiatan lingkungan', 'jumlah_total' => 1, 'jumlah_tersedia' => 1, 'jumlah_perbaikan' => 0, 'kondisi' => 'Baik']
        );

        $this->call([
            DemoFinanceDataSeeder::class,
            DemoHouseholdMembersSeeder::class,
        ]);
    }
}
