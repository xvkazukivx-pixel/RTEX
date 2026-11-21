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
        // 1. Iuran Setting
        Schema::create('iuran_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_iuran', 50);
            $table->decimal('nominal', 12, 2);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 2. Tagihan
        Schema::create('tagihan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('periode', 7); // format: YYYY-MM
            $table->decimal('total_tagihan', 12, 2);
            $table->enum('status', ['Belum Bayar', 'Menunggu Konfirmasi', 'Lunas'])->default('Belum Bayar');
            $table->timestamps();
            $table->unique(['warga_id', 'periode']);
        });

        // 3. Transaksi
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tagihan_id')->constrained('tagihan')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->decimal('jumlah_bayar', 12, 2);
            $table->enum('metode', ['Transfer', 'Tunai'])->default('Transfer');
            $table->string('bukti_transfer', 255)->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->dateTime('tanggal_bayar')->useCurrent();
            $table->enum('status_konfirmasi', ['Menunggu', 'Diterima', 'Ditolak'])->default('Menunggu');
            $table->foreignId('dikonfirmasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tanggal_konfirmasi')->nullable();
            $table->string('catatan_admin', 255)->nullable();
            $table->timestamps();
        });

        // 4. Laporan Validasi
        Schema::create('laporan_validasi', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->unique();
            $table->enum('status', ['Draft', 'Tervalidasi'])->default('Draft');
            $table->foreignId('divalidasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('tanggal_validasi')->nullable();
            $table->timestamps();
        });

        // 5. Pengajuan Surat
        Schema::create('pengajuan_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('jenis_surat', 100);
            $table->string('keperluan', 255);
            $table->text('catatan_warga')->nullable();
            $table->enum('status', ['Diajukan', 'Diproses', 'Menunggu Persetujuan', 'Disetujui', 'Ditolak'])->default('Diajukan');
            $table->string('nomor_surat', 100)->nullable()->unique();
            $table->text('catatan_admin')->nullable();
            $table->text('catatan_ketua')->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diproses_pada')->nullable();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('disetujui_pada')->nullable();
            $table->timestamps();
        });

        // 6. Pengumuman
        Schema::create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('isi');
            $table->enum('status', ['Dipublikasikan', 'Arsip'])->default('Dipublikasikan');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('dipublikasikan_pada')->useCurrent();
            $table->timestamps();
        });

        // 7. Kegiatan
        Schema::create('kegiatan', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->text('deskripsi');
            $table->string('lokasi', 150);
            $table->dateTime('mulai_pada');
            $table->dateTime('selesai_pada')->nullable();
            $table->integer('kapasitas')->nullable();
            $table->enum('status', ['Draft', 'Menunggu Persetujuan', 'Dipublikasikan', 'Selesai', 'Dibatalkan'])->default('Draft');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('disetujui_pada')->nullable();
            $table->text('catatan_ketua')->nullable();
            $table->timestamps();
        });

        // 8. Kegiatan Peserta
        Schema::create('kegiatan_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('kegiatan')->cascadeOnDelete();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->enum('status', ['Terdaftar', 'Batal'])->default('Terdaftar');
            $table->dateTime('terdaftar_pada')->useCurrent();
            $table->timestamps();
            $table->unique(['kegiatan_id', 'warga_id']);
        });

        // 9. Bank Sampah Jenis
        Schema::create('bank_sampah_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->decimal('harga_per_kg', 12, 2);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 10. Bank Sampah Setoran
        Schema::create('bank_sampah_setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->foreignId('jenis_id')->constrained('bank_sampah_jenis')->cascadeOnDelete();
            $table->decimal('berat_estimasi', 8, 2);
            $table->decimal('berat_final', 8, 2)->nullable();
            $table->decimal('nilai_setoran', 12, 2)->nullable();
            $table->enum('status', ['Menunggu Validasi', 'Diterima', 'Ditolak'])->default('Menunggu Validasi');
            $table->string('catatan', 255)->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diproses_pada')->nullable();
            $table->timestamps();
        });

        // 11. Posyandu Anak
        Schema::create('posyandu_anak', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->string('nama', 100);
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->timestamps();
        });

        // 12. Posyandu Jadwal
        Schema::create('posyandu_jadwal', function (Blueprint $table) {
            $table->id();
            $table->string('judul', 150);
            $table->dateTime('tanggal');
            $table->string('lokasi', 150);
            $table->text('catatan')->nullable();
            $table->enum('status', ['Terjadwal', 'Selesai', 'Dibatalkan'])->default('Terjadwal');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 13. Posyandu Pemeriksaan
        Schema::create('posyandu_pemeriksaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_id')->constrained('posyandu_jadwal')->cascadeOnDelete();
            $table->foreignId('anak_id')->constrained('posyandu_anak')->cascadeOnDelete();
            $table->decimal('berat_kg', 5, 2)->nullable();
            $table->decimal('tinggi_cm', 5, 2)->nullable();
            $table->decimal('lingkar_kepala_cm', 5, 2)->nullable();
            $table->string('imunisasi', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['jadwal_id', 'anak_id']);
        });

        // 14. Koperasi Produk
        Schema::create('koperasi_produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 150);
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2);
            $table->integer('stok')->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        // 15. Koperasi Pesanan
        Schema::create('koperasi_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warga_id')->constrained('warga')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('koperasi_produk')->cascadeOnDelete();
            $table->integer('jumlah');
            $table->decimal('harga_satuan', 12, 2);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['Dipesan', 'Dikonfirmasi', 'Dibayar', 'Dibatalkan'])->default('Dipesan');
            $table->string('catatan', 255)->nullable();
            $table->foreignId('diproses_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('diproses_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('koperasi_pesanan');
        Schema::dropIfExists('koperasi_produk');
        Schema::dropIfExists('posyandu_pemeriksaan');
        Schema::dropIfExists('posyandu_jadwal');
        Schema::dropIfExists('posyandu_anak');
        Schema::dropIfExists('bank_sampah_setoran');
        Schema::dropIfExists('bank_sampah_jenis');
        Schema::dropIfExists('kegiatan_peserta');
        Schema::dropIfExists('kegiatan');
        Schema::dropIfExists('pengumuman');
        Schema::dropIfExists('pengajuan_surat');
        Schema::dropIfExists('laporan_validasi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('tagihan');
        Schema::dropIfExists('iuran_settings');
    }
};
