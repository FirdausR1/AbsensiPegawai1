<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('nama_bank', 50)->nullable();
            $table->string('nomor_rekening', 50)->nullable();
            $table->string('nama_rekening', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'nama_bank',
                'nomor_rekening',
                'nama_rekening',
            ]);
        });
    }
};
