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
        'is_admin',
        'role',
        'signature_updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
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

    public function hasSignature(): bool
    {
        return !empty($this->signature_path);
    }

    // Nama tab di Google Sheet. Kalau belum diset manual, default ke nama pegawai.
    public function sheetTabName(): string
    {
        return $this->sheet_tab_name ?: $this->nama;
    }
}
