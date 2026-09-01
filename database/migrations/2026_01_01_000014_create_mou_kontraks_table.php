<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mou_kontraks', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_mou')->unique();
            $table->foreignId('kantor_klien_id')->nullable()->constrained('kantor_kliens')->nullOnDelete();
            $table->string('nama_kantor');
            $table->string('penanggung_jawab_klien');
            $table->string('jabatan_klien')->default('Pimpinan / Manager Klien');
            $table->string('telepon_klien')->nullable();
            $table->text('alamat_klien')->nullable();
            $table->date('tanggal_mou');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->integer('jumlah_personil')->default(1);
            $table->string('layanan_outsourcing')->default('Satpam / Security & Cleaning Service');
            $table->decimal('nilai_kontrak', 15, 2)->nullable();
            $table->text('catatan_pasal')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('pegawais')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mou_kontraks');
    }
};
