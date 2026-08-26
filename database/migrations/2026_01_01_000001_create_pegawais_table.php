<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('area_kerja')->nullable();
            $table->string('sheet_tab_name')->nullable(); // nama tab di Google Sheet, default = nama pegawai
            $table->string('signature_path')->nullable(); // lokasi file PNG hasil signature pad
            $table->boolean('is_admin')->default(false);
            $table->timestamp('signature_updated_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};
