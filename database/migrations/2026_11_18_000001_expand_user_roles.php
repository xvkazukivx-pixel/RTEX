<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('role', 'admin')->update(['role' => 'bendahara']);
        DB::table('users')->where('role', 'ketua')->update(['role' => 'rt']);

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('warga')->change();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'bendahara')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'rt')->update(['role' => 'ketua']);
        DB::table('users')->whereIn('role', ['super_admin', 'rw'])->update(['role' => 'warga']);
    }
};
