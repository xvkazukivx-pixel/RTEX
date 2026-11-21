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
        Schema::table('warga', function (Blueprint $table) {
            $table->text('nik')->nullable();
            $table->char('nik_hash', 64)->nullable()->unique();
            $table->foreignId('kepala_keluarga_id')
                ->nullable()
                ->constrained('warga')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warga', function (Blueprint $table) {
            $table->dropForeign(['kepala_keluarga_id']);
            $table->dropUnique(['nik_hash']);
            $table->dropColumn(['kepala_keluarga_id', 'nik_hash', 'nik']);
        });
    }
};
