<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HolidayService
{
    /**
     * Cek apakah tanggal tertentu adalah hari kerja (Senin-Jumat, bukan libur nasional).
     */
    public function isWorkingDay(Carbon $date): bool
    {
        if ($date->isWeekend()) { // Sabtu & Minggu = 5 hari kerja
            return false;
        }

        return !$this->isNationalHoliday($date);
    }

    /**
     * Alasan hari itu libur, untuk ditulis ke kolom KETERANGAN. Null kalau hari kerja biasa.
     */
    public function reasonIfHoliday(Carbon $date): ?string
    {
        if ($date->isSaturday() || $date->isSunday()) {
            return 'LIBUR (Akhir Pekan)';
        }

        $holidayName = $this->nationalHolidayName($date);
        return $holidayName ? "LIBUR NASIONAL - {$holidayName}" : null;
    }

    public function isNationalHoliday(Carbon $date): bool
    {
        return $this->nationalHolidayName($date) !== null;
    }

    protected function nationalHolidayName(Carbon $date): ?string
    {
        $holidays = $this->holidaysForYear($date->year);
        return $holidays[$date->format('Y-m-d')] ?? null;
    }

    /**
     * Ambil daftar libur nasional Indonesia untuk satu tahun, di-cache 1 hari.
     * Sumber: Nager.Date public API (gratis, tanpa key). Fallback: array kosong kalau API down,
     * supaya aplikasi tetap jalan (hari itu dianggap hari kerja biasa).
     */
    protected function holidaysForYear(int $year): array
    {
        return Cache::remember("holidays_id_{$year}", now()->addDay(), function () use ($year) {
            try {
                $url = rtrim(config('services.holiday.api_url'), '/') . "/{$year}/" . config('services.holiday.country_code', 'ID');
                $response = Http::timeout(5)->get($url);

                if (!$response->successful()) {
                    Log::warning("Gagal ambil data libur nasional tahun {$year}: HTTP " . $response->status());
                    return [];
                }

                $map = [];
                foreach ($response->json() as $item) {
                    // item: ['date' => '2026-01-01', 'localName' => 'Tahun Baru', ...]
                    $map[$item['date']] = $item['localName'] ?? $item['name'];
                }
                return $map;
            } catch (\Throwable $e) {
                Log::warning("Error ambil data libur nasional: {$e->getMessage()}");
                return [];
            }
        });
    }
}
