<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pegawais') && !Schema::hasColumn('pegawais', 'foto_path')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('foto_path')->nullable()->after('signature_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pegawais') && Schema::hasColumn('pegawais', 'foto_path')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('foto_path');
            });
        }
    }
};
