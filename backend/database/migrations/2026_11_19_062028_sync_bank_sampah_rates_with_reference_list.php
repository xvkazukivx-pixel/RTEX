<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $jenisSampah = [
            ['nama' => 'Botol PET', 'kategori' => 'Plastik', 'harga_per_kg' => 1500],
            ['nama' => 'Gelas plastik', 'kategori' => 'Plastik', 'harga_per_kg' => 500],
            ['nama' => 'Plastik kemasan', 'kategori' => 'Plastik', 'harga_per_kg' => 500],
            ['nama' => 'Kardus', 'kategori' => 'Kertas', 'harga_per_kg' => 1000],
            ['nama' => 'Koran', 'kategori' => 'Kertas', 'harga_per_kg' => 1500],
            ['nama' => 'Majalah', 'kategori' => 'Kertas', 'harga_per_kg' => 800],
            ['nama' => 'Kaleng aluminium', 'kategori' => 'Logam', 'harga_per_kg' => 25000],
            ['nama' => 'Kaleng besi', 'kategori' => 'Logam', 'harga_per_kg' => 1000],
            ['nama' => 'Besi bekas', 'kategori' => 'Logam', 'harga_per_kg' => 5000],
            ['nama' => 'Botol kaca', 'kategori' => 'Kaca', 'harga_per_kg' => 500],
            ['nama' => 'Pakaian bekas', 'kategori' => 'Tekstil', 'harga_per_kg' => 500],
            ['nama' => 'Kabel tembaga', 'kategori' => 'Elektronik', 'harga_per_kg' => 100000],
        ];

        foreach ($jenisSampah as $item) {
            $attributes = [
                ...$item,
                'contoh_sampah' => $item['nama'],
                'status_aktif' => true,
                'updated_at' => now(),
            ];
            $query = DB::table('bank_sampah_jenis')->where('nama', $item['nama']);

            if ($query->exists()) {
                $query->update($attributes);
            } else {
                DB::table('bank_sampah_jenis')->insert([
                    ...$attributes,
                    'created_at' => now(),
                ]);
            }
        }

        DB::table('bank_sampah_jenis')
            ->whereIn('nama', ['Plastik Campur', 'Kertas', 'Logam'])
            ->update(['status_aktif' => false, 'updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('bank_sampah_jenis')
            ->whereIn('nama', [
                'Botol PET',
                'Gelas plastik',
                'Plastik kemasan',
                'Koran',
                'Majalah',
                'Kaleng aluminium',
                'Kaleng besi',
                'Besi bekas',
                'Botol kaca',
                'Pakaian bekas',
                'Kabel tembaga',
            ])
            ->update(['status_aktif' => false, 'updated_at' => now()]);

        DB::table('bank_sampah_jenis')->where('nama', 'Plastik Campur')->update([
            'kategori' => 'Plastik',
            'contoh_sampah' => 'Botol PET, gelas plastik, kemasan',
            'harga_per_kg' => 3000,
            'status_aktif' => true,
            'updated_at' => now(),
        ]);
        DB::table('bank_sampah_jenis')->where('nama', 'Kertas')->update([
            'kategori' => 'Kertas',
            'contoh_sampah' => 'Koran, koran bekas, buku bekas',
            'harga_per_kg' => 1500,
            'status_aktif' => true,
            'updated_at' => now(),
        ]);
        DB::table('bank_sampah_jenis')->where('nama', 'Kardus')->update([
            'kategori' => 'Kertas',
            'contoh_sampah' => 'Kardus, koran, buku bekas',
            'harga_per_kg' => 2000,
            'status_aktif' => true,
            'updated_at' => now(),
        ]);
        DB::table('bank_sampah_jenis')->where('nama', 'Logam')->update([
            'kategori' => 'Logam',
            'contoh_sampah' => 'Kaleng, besi, aluminium',
            'harga_per_kg' => 5000,
            'status_aktif' => true,
            'updated_at' => now(),
        ]);
    }
};
