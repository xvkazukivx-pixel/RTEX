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
        Schema::table('bank_sampah_setoran', function (Blueprint $table) {
            $table->string('nama_sampah', 100)->nullable()->after('jenis_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bank_sampah_setoran', function (Blueprint $table) {
            $table->dropColumn('nama_sampah');
        });
    }
};
