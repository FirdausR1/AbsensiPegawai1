<?php

namespace Database\Seeders;

use App\Models\Divisi;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PegawaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffDivisiId = Divisi::where('nama', 'Staff Kantor')->value('id');
        $satpamDivisiId = Divisi::where('nama', 'Satpam / Security')->value('id');
        $cleaningDivisiId = Divisi::where('nama', 'Cleaning Service')->value('id');

        $pegawais = [
            [
                'nama'           => 'Administrator Utama',
                'email'          => 'admin@isw.co.id',
                'password'       => Hash::make('Admin@ISW2024'),
                'area_kerja'     => 'Management / HR',
                'divisi_id'      => $staffDivisiId,
                'sheet_tab_name' => 'Admin',
                'is_admin'       => true,
                'role'           => 'super_admin',
            ],
            [
                'nama'           => 'Firdaus Romandhanu',
                'email'          => 'firdaus@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Management / HR',
                'divisi_id'      => $staffDivisiId,
                'sheet_tab_name' => 'Firdaus Romandhanu',
                'is_admin'       => true,
                'role'           => 'super_admin',
            ],
            [
                'nama'           => 'Firdaus Romandhanu (Ops)',
                'email'          => 'firdaus@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'divisi_id'      => $staffDivisiId,
                'sheet_tab_name' => 'Firdaus Romandhanu',
                'is_admin'       => true,
                'role'           => 'super_admin',
            ],
            [
                'nama'           => 'Danru Satpam',
                'email'          => 'danru.satpam@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Satpam / Security',
                'divisi_id'      => $satpamDivisiId,
                'sheet_tab_name' => 'Danru Satpam',
                'is_admin'       => true,
                'role'           => 'admin_divisi',
            ],
            [
                'nama'           => 'Andriyanto Sutrisno',
                'email'          => 'andriyanto.sutrisno@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Satpam / Security',
                'divisi_id'      => $satpamDivisiId,
                'sheet_tab_name' => 'Andriyanto Sutrisno',
                'is_admin'       => true,
                'role'           => 'admin_divisi',
            ],
            [
                'nama'           => 'Dwi Satpam',
                'email'          => 'dwi.satpam@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Satpam / Security',
                'divisi_id'      => $satpamDivisiId,
                'sheet_tab_name' => 'Dwi Satpam',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Diki Satpam',
                'email'          => 'diki.satpam@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Satpam / Security',
                'divisi_id'      => $satpamDivisiId,
                'sheet_tab_name' => 'Diki Satpam',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Tomi Satpam',
                'email'          => 'tomi.satpam@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Satpam / Security',
                'divisi_id'      => $satpamDivisiId,
                'sheet_tab_name' => 'Tomi Satpam',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Isnan Anugroho',
                'email'          => 'isnan@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Cleaning Service',
                'divisi_id'      => $cleaningDivisiId,
                'sheet_tab_name' => 'Isnan Anugroho',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Wahyu Ari Nugroho',
                'email'          => 'wahyu@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Cleaning Service',
                'divisi_id'      => $cleaningDivisiId,
                'sheet_tab_name' => 'Wahyu Ari Nugroho',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Mohammad Adjwadi',
                'email'          => 'adjwadi@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Cleaning Service',
                'divisi_id'      => $cleaningDivisiId,
                'sheet_tab_name' => 'Mohammad Adjwadi',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Primadea',
                'email'          => 'primandhika@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Cleaning Service',
                'divisi_id'      => $cleaningDivisiId,
                'sheet_tab_name' => 'Primadea',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
        ];

        foreach ($pegawais as $data) {
            Pegawai::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }
    }
}
