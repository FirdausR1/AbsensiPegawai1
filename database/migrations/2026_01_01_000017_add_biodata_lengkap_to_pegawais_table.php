<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('nik', 20)->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin', 20)->default('Laki-laki');
            $table->text('alamat')->nullable();
            $table->string('pendidikan_terakhir', 50)->default('SMA/SMK');
            $table->string('no_hp', 50)->nullable();
            $table->string('status_pernikahan', 30)->default('Belum Menikah');
            $table->string('kontak_darurat')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn([
                'nik',
                'tempat_lahir',
                'tanggal_lahir',
                'jenis_kelamin',
                'alamat',
                'pendidikan_terakhir',
                'no_hp',
                'status_pernikahan',
                'kontak_darurat',
            ]);
        });
    }
};
