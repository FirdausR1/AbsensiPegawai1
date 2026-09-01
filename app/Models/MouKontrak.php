<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MouKontrak extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_mou',
        'kantor_klien_id',
        'nama_kantor',
        'penanggung_jawab_klien',
        'jabatan_klien',
        'telepon_klien',
        'alamat_klien',
        'tanggal_mou',
        'tanggal_mulai',
        'tanggal_selesai',
        'jumlah_personil',
        'layanan_outsourcing',
        'nilai_kontrak',
        'catatan_pasal',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mou'     => 'date',
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function kantorKlien()
    {
        return $this->belongsTo(KantorKlien::class, 'kantor_klien_id');
    }

    public function creator()
    {
        return $this->belongsTo(Pegawai::class, 'created_by');
    }
}
