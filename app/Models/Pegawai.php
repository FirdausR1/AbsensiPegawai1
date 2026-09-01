<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'area_kerja',
        'divisi_id',
        'sheet_tab_name',
        'signature_path',
        'foto_path',
        'is_admin',
        'role',
        'status_karyawan',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'status_bpjs_kesehatan',
        'no_bpjs_kesehatan',
        'status_bpjs_ketenagakerjaan',
        'no_bpjs_ketenagakerjaan',
        'nik',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'pendidikan_terakhir',
        'no_hp',
        'status_pernikahan',
        'kontak_darurat',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'status_bpjs_kesehatan' => 'boolean',
        'status_bpjs_ketenagakerjaan' => 'boolean',
        'tanggal_lahir' => 'date',
        'signature_updated_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function cutis()
    {
        return $this->hasMany(Cuti::class);
    }

    public function jadwalShifts()
    {
        return $this->hasMany(JadwalShift::class);
    }

    public function getJadwalOnDate(\Carbon\Carbon $date): ?JadwalShift
    {
        return $this->jadwalShifts()
            ->whereDate('tanggal', $date->toDateString())
            ->first();
    }

    public function getApprovedCutiOnDate(\Carbon\Carbon $date): ?Cuti
    {
        $dateStr = $date->toDateString();
        return $this->cutis()
            ->where('status', 'approved')
            ->where('tanggal_mulai', '<=', $dateStr)
            ->where('tanggal_selesai', '>=', $dateStr)
            ->first();
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || ($this->is_admin && empty($this->role));
    }

    public function isDivisionAdmin(): bool
    {
        return $this->role === 'admin_divisi';
    }

    public function hasAdminAccess(): bool
    {
        return $this->isSuperAdmin() || $this->isDivisionAdmin() || (bool) $this->is_admin;
    }

    public function getRoleBadgeText(): string
    {
        if ($this->isSuperAdmin()) {
            return 'SUPER ADMIN';
        }

        if ($this->isDivisionAdmin()) {
            $divisiName = $this->divisi?->nama ?? $this->area_kerja ?? 'DIVISI';
            return 'ADMIN ' . strtoupper($divisiName);
        }

        return 'STAFF';
    }

    public function getDivisi(): Divisi
    {
        try {
            if ($this->relationLoaded('divisi') && $this->divisi) {
                return $this->divisi;
            }
            if ($this->divisi_id && $this->divisi) {
                return $this->divisi;
            }
            if ($this->area_kerja) {
                $found = Divisi::where('nama', 'like', '%' . $this->area_kerja . '%')->first();
                if ($found) {
                    return $found;
                }
            }
            $existing = Divisi::first();
            if ($existing) {
                return $existing;
            }
        } catch (\Throwable $e) {
            // fallback if table does not exist or database is migrating
        }

        $fallback = new Divisi();
        $fallback->nama = $this->area_kerja ?: 'Staff Kantor';
        $fallback->jam_masuk = '08:00:00';
        $fallback->jam_pulang = '17:00:00';
        $fallback->toleransi_menit = 15;
        $fallback->keterangan = 'Jam operasional standar';
        return $fallback;
    }

    public function getFotoUrl(): ?string
    {
        if ($this->foto_path) {
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->foto_path)) {
                return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_path);
            }
            if (file_exists(public_path($this->foto_path))) {
                return asset($this->foto_path);
            }
        }
        return null;
    }

    public function hasFoto(): bool
    {
        return !empty($this->getFotoUrl());
    }

    public function isShiftWorker(): bool
    {
        if ($this->divisi && $this->divisi->hari_kerja_tipe === '7_hari') {
            return true;
        }
        $name = strtolower(($this->area_kerja ?: '') . ' ' . ($this->divisi?->nama ?: ''));
        return str_contains($name, 'satpam') || str_contains($name, 'security') || str_contains($name, 'cleaning') || str_contains($name, 'shift') || str_contains($name, 'danru');
    }

    public function getInitials(): string
    {
        $words = explode(' ', trim($this->nama ?? 'Pegawai'));
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->nama ?: 'PG', 0, 2));
    }

    public function hasSignature(): bool
    {
        return !empty($this->signature_path);
    }

    public function getSignatureUrl(): ?string
    {
        if (empty($this->signature_path)) {
            return null;
        }
        if (str_starts_with($this->signature_path, 'http://') || str_starts_with($this->signature_path, 'https://')) {
            return $this->signature_path;
        }
        return asset('storage/' . $this->signature_path);
    }

    // Nama tab di Google Sheet. Kalau belum diset manual, default ke nama pegawai.
    public function sheetTabName(): string
    {
        return $this->sheet_tab_name ?: $this->nama;
    }

    public function tugasPeriodiks()
    {
        return $this->hasMany(TugasPeriodik::class);
    }

    public function suratPeringatans()
    {
        return $this->hasMany(SuratPeringatan::class);
    }

    public function isCleaningService(): bool
    {
        $name = strtolower(($this->area_kerja ?: '') . ' ' . ($this->divisi?->nama ?: ''));
        return str_contains($name, 'cleaning') || str_contains($name, 'cs') || str_contains($name, 'kebersihan');
    }

    /**
     * Dapatkan Pegawai Paling Rajin Bulan Ini berdasarkan:
     * 1. Tidak Telat (0 keterlambatan / menit terlambat terkecil)
     * 2. Paling banyak tugas periodik harian/mingguan yang dikerjakan
     * 3. Total kehadiran paling tinggi
     */
    public static function getMostDiligentEmployee(?string $bulan = null, ?string $site = null)
    {
        $carbonMonth = $bulan ? \Carbon\Carbon::createFromFormat('Y-m', $bulan)->startOfMonth() : \Carbon\Carbon::now()->startOfMonth();
        $startOfMonth = $carbonMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth = $carbonMonth->copy()->endOfMonth()->toDateString();

        $query = static::with(['absensis' => function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
        }, 'tugasPeriodiks' => function ($q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('tanggal', [$startOfMonth, $endOfMonth]);
        }])
        ->where('role', '!=', 'super_admin')
        ->where('is_admin', false);

        if ($site) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->get();

        if ($pegawais->isEmpty()) {
            return null;
        }

        $ranked = $pegawais->map(function ($p) {
            $totalHadir = 0;
            $totalMenitTerlambat = 0;
            $totalHariTerlambat = 0;

            foreach ($p->absensis as $a) {
                if ($a->jam_masuk) {
                    $totalHadir++;
                    $menit = $a->getMenitTerlambat($p);
                    if ($menit > 0) {
                        $totalHariTerlambat++;
                        $totalMenitTerlambat += $menit;
                    }
                }
            }

            $totalTugas = $p->tugasPeriodiks->count();

            // Skor Kedisiplinan:
            // +100 poin per Kehadiran Tepat Waktu
            // -50 poin per Hari Terlambat
            // -1 poin per Menit Terlambat
            // +150 poin per Tugas Periodik Dikerjakan
            $tepatWaktu = max(0, $totalHadir - $totalHariTerlambat);
            $skor = ($tepatWaktu * 100) - ($totalHariTerlambat * 50) - $totalMenitTerlambat + ($totalTugas * 150);

            return (object) [
                'pegawai'               => $p,
                'skor'                  => $skor,
                'total_hadir'           => $totalHadir,
                'total_hari_terlambat'  => $totalHariTerlambat,
                'total_menit_terlambat' => $totalMenitTerlambat,
                'total_tugas'           => $totalTugas,
            ];
        })->sortByDesc('skor')->values();

        return $ranked->first();
    }

    public function getQrSignatureUrl(): string
    {
        if (!empty($this->qr_signature_path) && (str_starts_with($this->qr_signature_path, 'http://') || str_starts_with($this->qr_signature_path, 'https://'))) {
            return $this->qr_signature_path;
        }

        $nama = $this->nama ?: 'Firdaus Romandhanu';
        $jabatan = $this->jabatan_kepala ?: 'Direktur Utama PT Inti Sarana Wijaya';
        $code = 'ISW-DIR-VERIFIED-' . strtoupper(substr(md5(($this->id ?: 1) . 'ISW'), 0, 10));

        $qrText = "VERIFIKASI DIGITAL TANDA TANGAN RESMI\nPT INTI SARANA WIJAYA (ISW)\n----------------------------------------\nNama     : {$nama}\nJabatan  : {$jabatan}\nKode Ver : {$code}\nStatus   : DITANDATANGANI & SAH SECARA HUKUM";

        return "https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=" . urlencode($qrText);
    }
}
