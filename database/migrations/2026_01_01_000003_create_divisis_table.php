<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('divisis')) {
            Schema::create('divisis', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->time('jam_masuk')->default('08:00:00');
                $table->time('jam_pulang')->default('17:00:00');
                $table->integer('toleransi_menit')->default(0);
                $table->string('hari_kerja_tipe')->default('5_hari'); // 5_hari, 6_hari, 7_hari (Shift Satpam/CS)
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('divisis', 'hari_kerja_tipe')) {
            Schema::table('divisis', function (Blueprint $table) {
                $table->string('hari_kerja_tipe')->default('5_hari')->after('toleransi_menit');
            });
        }

        if (Schema::hasTable('pegawais') && !Schema::hasColumn('pegawais', 'divisi_id')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->foreignId('divisi_id')->nullable()->after('area_kerja')->constrained('divisis')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pegawais') && Schema::hasColumn('pegawais', 'divisi_id')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropForeign(['divisi_id']);
                $table->dropColumn('divisi_id');
            });
        }

        Schema::dropIfExists('divisis');
    }
};
