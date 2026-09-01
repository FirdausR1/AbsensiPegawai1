<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add rating & feedback columns to tugas_periodiks
        Schema::table('tugas_periodiks', function (Blueprint $table) {
            $table->string('nilai')->nullable(); // Bagus, Cukup, Perlu Perbaikan
            $table->text('feedback_supervisor')->nullable();
            $table->foreignId('rated_by')->nullable()->constrained('pegawais')->onDelete('set null');
            $table->timestamp('rated_at')->nullable();
        });

        // Create pengumumans & SOP table
        Schema::create('pengumumans', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('isi');
            $table->enum('kategori', ['pengumuman', 'sop', 'pasal_pelanggaran'])->default('pengumuman');
            $table->enum('prioritas', ['biasa', 'penting', 'darurat'])->default('biasa');
            $table->string('lampiran_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pegawais')->onDelete('set null');
            $table->timestamps();
        });

        // Create surat_peringatans table (Pelanggaran & SP 1-3)
        Schema::create('surat_peringatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            $table->enum('tingkat_sp', ['Teguran Lisan', 'SP 1', 'SP 2', 'SP 3'])->default('SP 1');
            $table->string('pasal_pelanggaran');
            $table->text('deskripsi');
            $table->date('tanggal_sp');
            $table->date('berlaku_sampai')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pegawais')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas_periodiks', function (Blueprint $table) {
            $table->dropForeign(['rated_by']);
            $table->dropColumn(['nilai', 'feedback_supervisor', 'rated_by', 'rated_at']);
        });

        Schema::dropIfExists('surat_peringatans');
        Schema::dropIfExists('pengumumans');
    }
};
