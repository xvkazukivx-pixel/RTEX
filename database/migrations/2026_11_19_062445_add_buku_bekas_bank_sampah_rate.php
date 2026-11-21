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
        $attributes = [
            'nama' => 'Buku bekas',
            'kategori' => 'Kertas',
            'contoh_sampah' => 'Buku bekas',
            'harga_per_kg' => 1500,
            'status_aktif' => true,
            'updated_at' => now(),
        ];
        $query = DB::table('bank_sampah_jenis')->where('nama', 'Buku bekas');

        if ($query->exists()) {
            $query->update($attributes);
        } else {
            DB::table('bank_sampah_jenis')->insert([
                ...$attributes,
                'created_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('bank_sampah_jenis')
            ->where('nama', 'Buku bekas')
            ->update(['status_aktif' => false, 'updated_at' => now()]);
    }
};
