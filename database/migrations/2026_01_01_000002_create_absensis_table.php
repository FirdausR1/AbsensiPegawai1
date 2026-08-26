<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel ini adalah salinan lokal (log) dari setiap absen.
        // Sumber kebenaran tetap Google Sheet, tapi tabel ini dipakai untuk:
        // - mencegah pegawai absen dobel di hari yang sama
        // - fallback kalau sinkronisasi ke Sheets gagal (bisa di-retry)
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->string('keterangan')->nullable(); // LIBUR / HADIR / IZIN / dll
            $table->boolean('synced_to_sheet')->default(false);
            $table->text('sync_error')->nullable();
            $table->timestamps();

            $table->unique(['pegawai_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
