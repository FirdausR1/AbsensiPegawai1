<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->decimal('gaji_pokok', 15, 2)->default(5000000.00);
            $table->decimal('tunjangan_jabatan', 15, 2)->default(500000.00);
            $table->decimal('tunjangan_transport', 15, 2)->default(500000.00);
            $table->boolean('status_bpjs_kesehatan')->default(true);
            $table->string('no_bpjs_kesehatan')->nullable();
            $table->boolean('status_bpjs_ketenagakerjaan')->default(true);
            $table->string('no_bpjs_ketenagakerjaan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'gaji_pokok',
                'tunjangan_jabatan',
                'tunjangan_transport',
                'status_bpjs_kesehatan',
                'no_bpjs_kesehatan',
                'status_bpjs_ketenagakerjaan',
                'no_bpjs_ketenagakerjaan',
            ]);
        });
    }
};
