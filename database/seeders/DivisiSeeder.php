<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultDivisis = [
            [
                'nama' => 'Staff Kantor (Regular)',
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '17:00:00',
                'toleransi_menit' => 15,
                'hari_kerja_tipe' => '5_hari',
                'keterangan' => 'Jam operasional staf kantor & administrasi (Senin - Jumat)',
            ],
            [
                'nama' => 'Satpam - Shift Pagi',
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '19:00:00',
                'toleransi_menit' => 0,
                'hari_kerja_tipe' => '7_hari',
                'keterangan' => 'Shift penjagaan keamanan pagi (Termasuk Weekend & Libur)',
            ],
            [
                'nama' => 'Satpam - Shift Malam',
                'jam_masuk' => '19:00:00',
                'jam_pulang' => '07:00:00',
                'toleransi_menit' => 0,
                'hari_kerja_tipe' => '7_hari',
                'keterangan' => 'Shift penjagaan keamanan malam (Termasuk Weekend & Libur)',
            ],
            [
                'nama' => 'Cleaning Service - Shift Pagi',
                'jam_masuk' => '06:30:00',
                'jam_pulang' => '15:30:00',
                'toleransi_menit' => 10,
                'hari_kerja_tipe' => '6_hari',
                'keterangan' => 'Shift kebersihan pagi (Senin - Sabtu)',
            ],
            [
                'nama' => 'Cleaning Service - Shift Siang',
                'jam_masuk' => '12:00:00',
                'jam_pulang' => '21:00:00',
                'toleransi_menit' => 10,
                'hari_kerja_tipe' => '6_hari',
                'keterangan' => 'Shift kebersihan siang-malam (Senin - Sabtu)',
            ],
            [
                'nama' => 'Operasional & IT',
                'jam_masuk' => '08:30:00',
                'jam_pulang' => '17:30:00',
                'toleransi_menit' => 15,
                'hari_kerja_tipe' => '5_hari',
                'keterangan' => 'Divisi teknologi informasi & operasional sistem',
            ],
        ];

        foreach ($defaultDivisis as $div) {
            Divisi::updateOrCreate(
                ['nama' => $div['nama']],
                $div
            );
        }
    }
}
