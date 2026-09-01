<?php

namespace Database\Seeders;

use App\Models\KantorKlien;
use Illuminate\Database\Seeder;

class KantorKlienSeeder extends Seeder
{
    public function run(): void
    {
        $sites = [
            [
                'nama_kantor'      => 'Head Office PT ISW',
                'kode_kantor'      => 'HO-ISW',
                'alamat'           => 'Jl. Sudirman No. 45, Jakarta Selatan',
                'penanggung_jawab' => 'Bambang Hartono (HRD ISW)',
                'telepon'          => '021-5551234',
            ],
            [
                'nama_kantor'      => 'Gedung Menara BCA',
                'kode_kantor'      => 'BCA-01',
                'alamat'           => 'Jl. M.H. Thamrin No. 1, Jakarta Pusat',
                'penanggung_jawab' => 'Danru Ahmad Fauzi',
                'telepon'          => '081234567890',
            ],
            [
                'nama_kantor'      => 'Pabrik Cikarang Site A',
                'kode_kantor'      => 'CKR-A',
                'alamat'           => 'Kawasan Industri Jababeka V, Cikarang',
                'penanggung_jawab' => 'Supervisor Rahmat Hidayat',
                'telepon'          => '081398765432',
            ],
            [
                'nama_kantor'      => 'Mall Grand Indonesia',
                'kode_kantor'      => 'GI-MALL',
                'alamat'           => 'Jl. Kebon Kacang, Jakarta Pusat',
                'penanggung_jawab' => 'Danru Satpam Budi Santoso',
                'telepon'          => '081511223344',
            ],
        ];

        foreach ($sites as $site) {
            KantorKlien::firstOrCreate(['nama_kantor' => $site['nama_kantor']], $site);
        }
    }
}
