<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kantor_kliens', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kantor');
            $table->string('kode_kantor')->nullable();
            $table->text('alamat')->nullable();
            $table->string('penanggung_jawab')->nullable(); // Danru / Supervisor Area
            $table->string('telepon')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kantor_kliens');
    }
};
