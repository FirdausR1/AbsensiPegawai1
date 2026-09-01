<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'qr_signature_path')) {
                $table->string('qr_signature_path')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'qr_payload')) {
                $table->text('qr_payload')->nullable();
            }
            if (!Schema::hasColumn('pegawais', 'jabatan_kepala')) {
                $table->string('jabatan_kepala')->nullable()->default('Direktur Utama PT Inti Sarana Wijaya');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['qr_signature_path', 'qr_payload', 'jabatan_kepala']);
        });
    }
};
