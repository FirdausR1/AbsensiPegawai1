<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantorKlien extends Model
{
    use HasFactory;

    protected $table = 'kantor_kliens';

    protected $fillable = [
        'nama_kantor',
        'kode_kantor',
        'alamat',
        'penanggung_jawab',
        'telepon',
    ];

    public function pegawais()
    {
        return Pegawai::where('area_kerja', $this->nama_kantor);
    }
}
