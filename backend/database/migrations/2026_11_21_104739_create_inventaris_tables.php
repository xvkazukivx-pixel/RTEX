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
        Schema::create('inventaris_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->string('kategori', 100);
            $table->text('deskripsi')->nullable();
            $table->unsignedInteger('jumlah_total')->default(0);
            $table->unsignedInteger('jumlah_tersedia')->default(0);
            $table->unsignedInteger('jumlah_perbaikan')->default(0);
            $table->enum('kondisi', ['Baik', 'Perlu Perbaikan', 'Rusak'])->default('Baik');
            $table->timestamps();
        });

        Schema::create('inventaris_peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barang_id')->constrained('inventaris_barang')->cascadeOnDelete();
            $table->foreignId('warga_id')->nullable()->constrained('warga')->nullOnDelete();
            $table->string('nama_peminjam', 150);
            $table->unsignedInteger('jumlah');
            $table->date('tanggal_pinjam');
            $table->date('rencana_kembali');
            $table->date('tanggal_kembali')->nullable();
            $table->enum('status', ['Diajukan', 'Dipinjam', 'Dikembalikan', 'Ditolak'])->default('Diajukan');
            $table->text('keperluan');
            $table->text('catatan')->nullable();
            $table->string('foto_pinjam')->nullable();
            $table->string('foto_kembali')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaris_peminjaman');
        Schema::dropIfExists('inventaris_barang');
    }
};
