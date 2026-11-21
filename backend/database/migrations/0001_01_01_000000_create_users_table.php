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
        Schema::create('warga', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('no_rumah', 20)->nullable();
            $table->string('alamat', 150)->nullable();
            $table->string('no_hp', 20)->nullable();
            $table->enum('status_hunian', ['Tetap', 'Kontrak'])->default('Tetap');
            $table->boolean('status_aktif')->default(true);
            $table->enum('status_pendaftaran', ['Menunggu Persetujuan', 'Disetujui', 'Ditolak'])->default('Disetujui');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('username', 50)->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'bendahara', 'rt', 'rw', 'warga', 'admin', 'ketua'])->default('warga');
            $table->foreignId('warga_id')->nullable()->constrained('warga')->nullOnDelete();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('warga');
    }
};
