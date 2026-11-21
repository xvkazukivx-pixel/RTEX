<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengaturan_surat', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pemerintahan', 160)->default('RTEX — Administrasi Warga');
            $table->string('nama_kecamatan', 160)->nullable();
            $table->string('nama_rt_rw', 160)->default('RT 05 / RW 01');
            $table->string('alamat_sekretariat', 255)->nullable();
            $table->string('nama_ketua', 120)->nullable();
            $table->string('jabatan_penanda_tangan', 100)->default('Ketua RT');
            $table->string('kota_ttd', 100)->nullable();
            $table->string('masa_berlaku', 50)->nullable();
            $table->string('logo_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_surat');
    }
};
