<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'keterangan',
        'synced_to_sheet',
        'sync_error',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'synced_to_sheet' => 'boolean',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    /**
     * Hitung berapa menit keterlambatan jam masuk terhadap jadwal divisi pegawai
     */
    public function getMenitTerlambat(?Pegawai $pegawai = null): int
    {
        if (!$this->jam_masuk) {
            return 0;
        }

        $pegawai = $pegawai ?: $this->pegawai;
        if (!$pegawai) {
            return 0;
        }

        try {
            $divisi = $pegawai->getDivisi();
            $targetJamMasuk = $divisi->jam_masuk ?: '08:00:00';
            $toleransi = (int) ($divisi->toleransi_menit ?: 0);

            $tanggalStr = $this->tanggal instanceof Carbon 
                ? $this->tanggal->toDateString() 
                : Carbon::parse($this->tanggal)->toDateString();

            $targetDateTime = Carbon::parse($tanggalStr . ' ' . substr($targetJamMasuk, 0, 5) . ':00')->addMinutes($toleransi);
            $actualDateTime = Carbon::parse($tanggalStr . ' ' . substr($this->jam_masuk, 0, 5) . ':00');

            if ($actualDateTime->greaterThan($targetDateTime)) {
                return (int) $targetDateTime->diffInMinutes($actualDateTime);
            }
        } catch (\Throwable $e) {
            return 0;
        }

        return 0;
    }

    /**
     * Dapatkan teks status keterlambatan atau keterangan absensi
     */
    public function getStatusKeterangan(): string
    {
        if (!empty($this->keterangan)) {
            return $this->keterangan;
        }

        if (!$this->jam_masuk) {
            return 'ALPA';
        }

        $menitTerlambat = $this->getMenitTerlambat();
        if ($menitTerlambat > 0) {
            return "Terlambat {$menitTerlambat} Menit";
        }

        return 'Tepat Waktu';
    }
}
