<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bank_sampah_jenis', function (Blueprint $table) {
            $table->string('kategori', 50)->nullable()->after('nama');
            $table->text('contoh_sampah')->nullable()->after('kategori');
        });

        DB::table('bank_sampah_jenis')->where('nama', 'Plastik Campur')->update([
            'kategori' => 'Plastik',
            'contoh_sampah' => 'Botol PET, gelas plastik, kemasan',
        ]);
        DB::table('bank_sampah_jenis')->where('nama', 'Kertas')->update([
            'kategori' => 'Kertas',
            'contoh_sampah' => 'Koran, koran bekas, buku bekas',
        ]);
        DB::table('bank_sampah_jenis')->where('nama', 'Kardus')->update([
            'kategori' => 'Kertas',
            'contoh_sampah' => 'Kardus, koran, buku bekas',
        ]);
        DB::table('bank_sampah_jenis')->where('nama', 'Logam')->update([
            'kategori' => 'Logam',
            'contoh_sampah' => 'Kaleng, besi, aluminium',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_sampah_jenis', function (Blueprint $table) {
            $table->dropColumn(['kategori', 'contoh_sampah']);
        });
    }
};
