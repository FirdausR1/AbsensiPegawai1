<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratPeringatan extends Model
{
    use HasFactory;

    protected $table = 'surat_peringatans';

    protected $fillable = [
        'pegawai_id',
        'tingkat_sp',
        'pasal_pelanggaran',
        'deskripsi',
        'tanggal_sp',
        'berlaku_sampai',
        'created_by',
    ];

    protected $casts = [
        'tanggal_sp'     => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function creator()
    {
        return $this->belongsTo(Pegawai::class, 'created_by');
    }

    public function getSpBadgeColor(): string
    {
        return match ($this->tingkat_sp) {
            'Teguran Lisan' => 'bg-amber-100 text-amber-900 border-amber-300',
            'SP 1'          => 'bg-amber-500 text-white',
            'SP 2'          => 'bg-orange-600 text-white',
            'SP 3'          => 'bg-rose-700 text-white font-black animate-pulse',
            default         => 'bg-slate-100 text-slate-800',
        };
    }
}
