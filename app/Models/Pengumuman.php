<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = [
        'judul',
        'isi',
        'kategori',
        'prioritas',
        'lampiran_path',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(Pegawai::class, 'created_by');
    }

    public function getKategoriBadgeColor(): string
    {
        return match ($this->kategori) {
            'sop' => 'bg-indigo-100 text-indigo-900 border-indigo-300',
            'pasal_pelanggaran' => 'bg-rose-100 text-rose-900 border-rose-300',
            default => 'bg-[#eef2ff] text-[#000d6b] border-indigo-200',
        };
    }
}
