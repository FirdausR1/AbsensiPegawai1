<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasPeriodik extends Model
{
    use HasFactory;

    protected $fillable = [
        'pegawai_id',
        'tipe_tugas',
        'nama_tugas',
        'deskripsi',
        'foto_path',
        'waktu_upload',
        'tanggal',
        'catatan',
        'nilai',
        'feedback_supervisor',
        'rated_by',
        'rated_at',
    ];

    protected $casts = [
        'waktu_upload' => 'datetime',
        'tanggal'      => 'date',
        'rated_at'     => 'datetime',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function ratedBy()
    {
        return $this->belongsTo(Pegawai::class, 'rated_by');
    }

    public function getNilaiBadgeColor(): string
    {
        return match ($this->nilai) {
            'Sangat Bagus 🌟' => 'bg-emerald-100 text-emerald-900 border-emerald-300',
            'Bagus 👍'         => 'bg-blue-100 text-blue-900 border-blue-300',
            'Cukup 👌'         => 'bg-amber-100 text-amber-900 border-amber-300',
            'Perlu Perbaikan ⚠️' => 'bg-rose-100 text-rose-900 border-rose-300',
            default            => 'bg-slate-100 text-slate-700',
        };
    }
}
