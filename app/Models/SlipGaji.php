<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlipGaji extends Model
{
    use HasFactory;

    protected $table = 'slips_gaji';

    protected $fillable = [
        'pegawai_id',
        'bulan',
        'area_kerja',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_transport',
        'bonus_overtime',
        'potongan_bpjs_kesehatan',
        'potongan_bpjs_tk',
        'potongan_absensi',
        'potongan_lainnya',
        'total_pendapatan',
        'total_potongan',
        'take_home_pay',
        'catatan',
        'created_by',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function creator()
    {
        return $this->belongsTo(Pegawai::class, 'created_by');
    }
}
