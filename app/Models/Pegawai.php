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

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function getDivisi(): Divisi
    {
        if ($this->divisi) {
            return $this->divisi;
        }

        // Try to match by area_kerja name
        if ($this->area_kerja) {
            $found = Divisi::where('nama', 'like', '%' . $this->area_kerja . '%')->first();
            if ($found) {
                return $found;
            }
        }

        // Fallback default divisi
        return Divisi::firstOrCreate(
            ['nama' => 'Staff Kantor'],
            [
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '17:00:00',
                'toleransi_menit' => 0,
                'keterangan' => 'Jam kerja standar operasional kantor',
            ]
        );
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
