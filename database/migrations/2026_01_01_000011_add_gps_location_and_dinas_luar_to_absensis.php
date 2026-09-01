<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            if (!Schema::hasColumn('absensis', 'status_presensi')) {
                $table->string('status_presensi')->default('reguler'); // reguler, dinas_luar
            }
            if (!Schema::hasColumn('absensis', 'lokasi_masuk')) {
                $table->string('lokasi_masuk')->nullable();
            }
            if (!Schema::hasColumn('absensis', 'lokasi_pulang')) {
                $table->string('lokasi_pulang')->nullable();
            }
            if (!Schema::hasColumn('absensis', 'foto_dinas_luar_masuk')) {
                $table->string('foto_dinas_luar_masuk')->nullable();
            }
            if (!Schema::hasColumn('absensis', 'foto_dinas_luar_pulang')) {
                $table->string('foto_dinas_luar_pulang')->nullable();
            }
        });

        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'status_karyawan')) {
                $table->string('status_karyawan')->default('outsourcing'); // internal, outsourcing
            }
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn(['status_presensi', 'lokasi_masuk', 'lokasi_pulang', 'foto_dinas_luar_masuk', 'foto_dinas_luar_pulang']);
        });

        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['status_karyawan']);
        });
    }
};
