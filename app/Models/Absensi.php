<?php

namespace App\Models;

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
}
