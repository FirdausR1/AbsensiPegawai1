<?php

namespace App\Models;

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
        'keterangan',
    ];

    protected $casts = [
        'toleransi_menit' => 'integer',
    ];

    public function pegawais(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }
}
