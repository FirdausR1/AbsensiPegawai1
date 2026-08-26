<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cutis')) {
            Schema::create('cutis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawais')->cascadeOnDelete();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->integer('jumlah_hari')->default(1);
                $table->string('tipe_cuti')->default('Cuti Tahunan'); // Cuti Tahunan, Cuti Sakit, Cuti Khusus, Izin Penting
                $table->text('alasan')->nullable();
                $table->string('status')->default('pending'); // pending, approved, rejected
                $table->text('catatan_admin')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('pegawais')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cutis');
    }
};
