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
        // 1. Tambah kolom rfid pada tabel users
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rfid_uid')) {
                $table->string('rfid_uid', 64)->nullable()->unique()->after('warga_id');
            }
            if (!Schema::hasColumn('users', 'rfid_label')) {
                $table->string('rfid_label', 100)->nullable()->default('e-KTP')->after('rfid_uid');
            }
            if (!Schema::hasColumn('users', 'rfid_registered_at')) {
                $table->timestamp('rfid_registered_at')->nullable()->after('rfid_label');
            }
            if (!Schema::hasColumn('users', 'rfid_is_active')) {
                $table->boolean('rfid_is_active')->default(true)->after('rfid_registered_at');
            }
        });

        // 2. Tabel sesi scan / pairing KTP real-time dari ESP32
        if (!Schema::hasTable('rfid_scan_sessions')) {
            Schema::create('rfid_scan_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('device_id', 50)->nullable();
                $table->string('status', 20)->default('waiting'); // waiting, scanned, completed, expired
                $table->string('scanned_uid', 64)->nullable();
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->index(['status', 'expires_at']);
            });
        }

        // 3. Tabel log riwayat akses gerbang/portal perumahan
        if (!Schema::hasTable('gate_access_logs')) {
            Schema::create('gate_access_logs', function (Blueprint $table) {
                $table->id();
                $table->string('rfid_uid', 64);
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('warga_nama', 150)->default('-');
                $table->string('no_rumah', 50)->nullable();
                $table->string('device_id', 50)->default('PORTAL-UTAMA');
                $table->string('access_status', 20); // granted, denied
                $table->string('denial_reason', 255)->nullable();
                $table->string('gate_action', 30)->default('NONE'); // OPEN_GATE, NONE, MANUAL_OPEN
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('tapped_at')->useCurrent();
                $table->timestamps();

                $table->index(['rfid_uid', 'created_at']);
                $table->index('access_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gate_access_logs');
        Schema::dropIfExists('rfid_scan_sessions');

        Schema::table('users', function (Blueprint $table) {
            $columns = ['rfid_uid', 'rfid_label', 'rfid_registered_at', 'rfid_is_active'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
