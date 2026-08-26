<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pegawai extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'area_kerja',
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
