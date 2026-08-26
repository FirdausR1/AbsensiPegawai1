<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalShift extends Model
{
    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'tipe_shift',
        'jam_masuk',
        'jam_pulang',
        'status',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    // Tipe shift yang tersedia
    public const TIPE_PAGI   = 'Pagi';
    public const TIPE_MALAM  = 'Malam';
    public const TIPE_SIANG  = 'Siang';
    public const TIPE_LIBUR  = 'Libur';

    public static function tipeOptions(): array
    {
        return [
            self::TIPE_PAGI  => 'Shift Pagi',
            self::TIPE_MALAM => 'Shift Malam',
            self::TIPE_SIANG => 'Shift Siang',
            self::TIPE_LIBUR => 'Hari Libur',
        ];
    }

    // Default jam berdasarkan tipe shift dan divisi/pegawai
    public static function defaultJam(string $tipe, ?Pegawai $pegawai = null): array
    {
        if ($tipe === self::TIPE_LIBUR) {
            return ['jam_masuk' => null, 'jam_pulang' => null];
        }

        // Cek nama divisi / area kerja pegawai jika ada
        if ($pegawai) {
            $divisiNama = strtolower($pegawai->divisi?->nama ?? '');
            $areaNama = strtolower($pegawai->area_kerja ?? '');
            $fullNama = $divisiNama . ' ' . $areaNama;

            if (str_contains($fullNama, 'cleaning') || str_contains($fullNama, 'cs')) {
                return match ($tipe) {
                    self::TIPE_PAGI  => ['jam_masuk' => '06:30', 'jam_pulang' => '15:30'],
                    self::TIPE_SIANG => ['jam_masuk' => '12:00', 'jam_pulang' => '21:00'],
                    self::TIPE_MALAM => ['jam_masuk' => '19:00', 'jam_pulang' => '07:00'],
                    default          => ['jam_masuk' => null,    'jam_pulang' => null],
                };
            }

            // Jika pegawai memiliki relasi divisi yang memiliki jam_masuk & jam_pulang spesifik
            if ($pegawai->divisi && $pegawai->divisi->jam_masuk && $pegawai->divisi->jam_pulang) {
                if (str_contains(strtolower($pegawai->divisi->nama), strtolower($tipe))) {
                    return [
                        'jam_masuk'  => substr($pegawai->divisi->jam_masuk, 0, 5),
                        'jam_pulang' => substr($pegawai->divisi->jam_pulang, 0, 5),
                    ];
                }
            }
        }

        // Default standar (Satpam / General Shift)
        return match ($tipe) {
            self::TIPE_PAGI  => ['jam_masuk' => '07:00', 'jam_pulang' => '19:00'],
            self::TIPE_MALAM => ['jam_masuk' => '19:00', 'jam_pulang' => '07:00'],
            self::TIPE_SIANG => ['jam_masuk' => '12:00', 'jam_pulang' => '21:00'],
            default          => ['jam_masuk' => null,    'jam_pulang' => null],
        };
    }

    public static function getShiftInfoList(?Pegawai $pegawai = null): array
    {
        $pagi  = static::defaultJam(self::TIPE_PAGI, $pegawai);
        $malam = static::defaultJam(self::TIPE_MALAM, $pegawai);
        $siang = static::defaultJam(self::TIPE_SIANG, $pegawai);

        return [
            self::TIPE_PAGI => [
                'icon'       => '☀️',
                'label'      => 'Shift Pagi',
                'jam'        => "{$pagi['jam_masuk']} – {$pagi['jam_pulang']}",
                'jam_masuk'  => $pagi['jam_masuk'],
                'jam_pulang' => $pagi['jam_pulang'],
            ],
            self::TIPE_MALAM => [
                'icon'       => '🌙',
                'label'      => 'Shift Malam',
                'jam'        => "{$malam['jam_masuk']} – {$malam['jam_pulang']}",
                'jam_masuk'  => $malam['jam_masuk'],
                'jam_pulang' => $malam['jam_pulang'],
            ],
            self::TIPE_SIANG => [
                'icon'       => '🌤️',
                'label'      => 'Shift Siang',
                'jam'        => "{$siang['jam_masuk']} – {$siang['jam_pulang']}",
                'jam_masuk'  => $siang['jam_masuk'],
                'jam_pulang' => $siang['jam_pulang'],
            ],
            self::TIPE_LIBUR => [
                'icon'       => '🏠',
                'label'      => 'Minta Hari Libur',
                'jam'        => 'Hari Istirahat',
                'jam_masuk'  => null,
                'jam_pulang' => null,
            ],
        ];
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'created_by');
    }

    public function isLibur(): bool
    {
        return $this->tipe_shift === self::TIPE_LIBUR;
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isRequested(): bool
    {
        return $this->status === 'requested';
    }

    public function getJamMasukEfektif(): ?string
    {
        if ($this->jam_masuk) {
            return $this->jam_masuk;
        }
        return static::defaultJam($this->tipe_shift, $this->pegawai)['jam_masuk'];
    }

    public function getJamPulangEfektif(): ?string
    {
        if ($this->jam_pulang) {
            return $this->jam_pulang;
        }
        return static::defaultJam($this->tipe_shift, $this->pegawai)['jam_pulang'];
    }

    public function getBadgeColor(): string
    {
        return match ($this->tipe_shift) {
            self::TIPE_PAGI  => 'bg-amber-100 text-amber-800 border-amber-200',
            self::TIPE_MALAM => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            self::TIPE_SIANG => 'bg-sky-100 text-sky-800 border-sky-200',
            self::TIPE_LIBUR => 'bg-slate-100 text-slate-500 border-slate-200',
            default          => 'bg-slate-100 text-slate-600 border-slate-200',
        };
    }

    public function getShiftLabel(): string
    {
        return match ($this->tipe_shift) {
            self::TIPE_PAGI  => '☀️ Pagi',
            self::TIPE_MALAM => '🌙 Malam',
            self::TIPE_SIANG => '🌤️ Siang',
            self::TIPE_LIBUR => '🏠 Libur',
            default          => $this->tipe_shift,
        };
    }

    /**
     * Get jadwal for a specific employee on a specific date.
     */
    public static function forPegawaiOnDate(int $pegawaiId, Carbon $date): ?self
    {
        return static::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $date->toDateString())
            ->first();
    }
}
