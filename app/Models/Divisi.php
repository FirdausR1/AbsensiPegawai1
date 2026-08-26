<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jam_masuk',
        'jam_pulang',
        'toleransi_menit',
        'hari_kerja_tipe',
        'keterangan',
    ];

    protected $casts = [
        'toleransi_menit' => 'integer',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }

    /**
     * Cek apakah tanggal adalah hari kerja wajib untuk divisi ini
     */
    public function isHariKerjaWajib(Carbon $date, $holidayService = null): bool
    {
        $tipe = $this->hari_kerja_tipe ?: '5_hari';

        if ($tipe === '7_hari') {
            return true; // Satpam / Shift 24/7
        }

        if ($tipe === '6_hari') {
            // Senin - Sabtu kerja, Minggu libur
            if ($date->isSunday()) {
                return false;
            }
            if ($holidayService && $holidayService->isNationalHoliday($date)) {
                return false;
            }
            return true;
        }

        // Default: 5_hari (Senin - Jumat)
        if ($date->isWeekend()) {
            return false;
        }

        if ($holidayService && $holidayService->isNationalHoliday($date)) {
            return false;
        }

        return true;
    }

    public function getHariKerjaLabel(): string
    {
        return match ($this->hari_kerja_tipe) {
            '7_hari' => 'Setiap Hari / Shift 7 Hari (Termasuk Libur & Weekend)',
            '6_hari' => 'Senin - Sabtu (6 Hari Kerja)',
            default => 'Senin - Jumat (5 Hari Kerja)',
        };
    }
}
