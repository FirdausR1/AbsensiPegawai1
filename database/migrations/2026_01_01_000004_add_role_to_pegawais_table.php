<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pegawais') && !Schema::hasColumn('pegawais', 'role')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('role')->default('staff')->after('is_admin');
            });

            // Update existing records
            try {
                DB::table('pegawais')->where('is_admin', true)->update(['role' => 'super_admin']);
                DB::table('pegawais')->where('is_admin', false)->update(['role' => 'staff']);
            } catch (\Throwable $e) {
                // Ignore during testing
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pegawais') && Schema::hasColumn('pegawais', 'role')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
