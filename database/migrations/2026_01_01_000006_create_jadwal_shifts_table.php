<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jadwal_shifts')) {
            Schema::create('jadwal_shifts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
                $table->date('tanggal');
                $table->string('tipe_shift')->default('Pagi'); // Pagi, Malam, Siang, Libur
                $table->time('jam_masuk')->nullable();  // override dari default divisi
                $table->time('jam_pulang')->nullable(); // override dari default divisi
                $table->string('status')->default('confirmed'); // confirmed, requested, pending_swap
                $table->text('catatan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('pegawais')->nullOnDelete();
                $table->timestamps();

                $table->unique(['pegawai_id', 'tanggal']); // 1 pegawai 1 jadwal per hari
                $table->index(['pegawai_id', 'tanggal']);
                $table->index('tanggal');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_shifts');
    }
};
