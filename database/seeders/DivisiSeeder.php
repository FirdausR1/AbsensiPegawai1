<?php

namespace Database\Seeders;

use App\Models\Divisi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultDivisis = [
            [
                'nama' => 'Staff Kantor',
                'jam_masuk' => '08:00:00',
                'jam_pulang' => '17:00:00',
                'toleransi_menit' => 15,
                'hari_kerja_tipe' => '5_hari',
                'keterangan' => 'Jam operasional staf kantor & administrasi (Senin - Jumat)',
            ],
            [
                'nama' => 'Satpam / Security',
                'jam_masuk' => '07:00:00',
                'jam_pulang' => '14:00:00',
                'toleransi_menit' => 0,
                'hari_kerja_tipe' => '7_hari',
                'keterangan' => 'Shift 1: 07:00-14:00, Shift 2: 14:00-21:00, Shift 3: 21:00-07:00 (termasuk weekend)',
            ],
            [
                'nama' => 'Cleaning Service',
                'jam_masuk' => '06:30:00',
                'jam_pulang' => '15:30:00',
                'toleransi_menit' => 10,
                'hari_kerja_tipe' => '6_hari',
                'keterangan' => 'Shift kebersihan pagi (Senin - Sabtu)',
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

        $legacyNames = [
            'Staff Kantor (Regular)',
            'Satpam - Shift Pagi',
            'Satpam - Shift Malam',
            'Cleaning Service - Shift Pagi',
            'Cleaning Service - Shift Siang',
        ];

        $legacyIds = Divisi::whereIn('nama', $legacyNames)->pluck('id');
        if ($legacyIds->isNotEmpty()) {
            DB::table('pegawais')->whereIn('divisi_id', $legacyIds)->update(['divisi_id' => null]);
            Divisi::whereIn('id', $legacyIds)->delete();
        }
    }
}
