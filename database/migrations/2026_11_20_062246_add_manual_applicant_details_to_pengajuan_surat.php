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
        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->foreignId('warga_id')->nullable()->change();
            $table->string('jenis_pemohon', 20)->default('warga');
            $table->string('nama_pemohon', 150)->nullable();
            $table->text('nik_pemohon')->nullable();
            $table->string('jenis_kelamin', 20)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('agama', 50)->nullable();
            $table->string('pekerjaan', 100)->nullable();
            $table->string('status_perkawinan', 30)->nullable();
            $table->string('kewarganegaraan', 80)->nullable();
            $table->text('alamat_ktp')->nullable();
            $table->text('alamat_domisili')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('pengajuan_surat')->whereNull('warga_id')->exists()) {
            throw new RuntimeException('Hapus atau tautkan pengajuan pemohon non-warga sebelum membatalkan migrasi.');
        }

        Schema::table('pengajuan_surat', function (Blueprint $table) {
            $table->dropColumn([
                'jenis_pemohon',
                'nama_pemohon',
                'nik_pemohon',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'agama',
                'pekerjaan',
                'status_perkawinan',
                'kewarganegaraan',
                'alamat_ktp',
                'alamat_domisili',
            ]);
            $table->foreignId('warga_id')->nullable(false)->change();
        });
    }
};
