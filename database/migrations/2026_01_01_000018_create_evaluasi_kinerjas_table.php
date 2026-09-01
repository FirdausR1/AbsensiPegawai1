<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluasi_kinerjas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            $table->string('periode_tipe', 30)->default('3_bulan'); // 1_bulan, 3_bulan, 6_bulan, 12_bulan
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            
            // Rekap Penilaian
            $table->integer('total_hadir')->default(0);
            $table->integer('total_terlambat')->default(0);
            $table->integer('total_menit_terlambat')->default(0);
            $table->integer('total_cuti_izin')->default(0);
            $table->integer('total_alpha')->default(0);
            
            // Skor & Bobot
            $table->decimal('skor_absensi', 5, 2)->default(100.00);
            $table->decimal('skor_tugas', 5, 2)->default(100.00);
            $table->decimal('skor_perilaku', 5, 2)->default(90.00);
            $table->decimal('skor_akhir', 5, 2)->default(95.00);
            
            $table->string('kategori_penilaian', 30)->default('Sangat Baik (A)');
            $table->string('rekomendasi', 100)->default('Diperpanjang Kontrak / Lanjut Tugas');
            $table->text('catatan_evaluasi')->nullable();
            
            $table->foreignId('evaluator_id')->nullable()->constrained('pegawais')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_kinerjas');
    }
};
