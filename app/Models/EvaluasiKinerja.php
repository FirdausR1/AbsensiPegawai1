<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiKinerja extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'periode_tipe',
        'tanggal_mulai',
        'tanggal_selesai',
        'total_hadir',
        'total_terlambat',
        'total_menit_terlambat',
        'total_cuti_izin',
        'total_alpha',
        'skor_absensi',
        'skor_tugas',
        'skor_perilaku',
        'skor_akhir',
        'kategori_penilaian',
        'rekomendasi',
        'catatan_evaluasi',
        'evaluator_id',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'skor_absensi'    => 'float',
        'skor_tugas'      => 'float',
        'skor_perilaku'    => 'float',
        'skor_akhir'      => 'float',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function evaluator()
    {
        return $this->belongsTo(Pegawai::class, 'evaluator_id');
    }
}
