<?php

namespace Database\Seeders;

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
        $pegawais = [
            [
                'nama'           => 'Administrator Utama',
                'email'          => 'admin@isw.co.id',
                'password'       => Hash::make('Admin@ISW2024'),
                'area_kerja'     => 'Management / HR',
                'sheet_tab_name' => 'Admin',
                'is_admin'       => true,
                'role'           => 'super_admin',
            ],
            [
                'nama'           => 'Firdaus Romandhanu',
                'email'          => 'firdaus@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Management / HR',
                'sheet_tab_name' => 'Firdaus Romandhanu',
                'is_admin'       => true,
                'role'           => 'super_admin',
            ],
            [
                'nama'           => 'Firdaus Romandhanu (Ops)',
                'email'          => 'firdaus@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Firdaus Romandhanu',
                'is_admin'       => true,
                'role'           => 'super_admin',
            ],
            [
                'nama'           => 'Danru Satpam',
                'email'          => 'danru.satpam@isw.co.id',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Satpam / Security',
                'sheet_tab_name' => 'Danru Satpam',
                'is_admin'       => true,
                'role'           => 'admin_divisi',
            ],
            [
                'nama'           => 'Isnan Anugroho',
                'email'          => 'isnan@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Isnan Anugroho',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Wahyu Ari Nugroho',
                'email'          => 'wahyu@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Wahyu Ari Nugroho',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Mohamad Adjwadi',
                'email'          => 'adjwadi@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Mohamad Adjwadi',
                'is_admin'       => false,
                'role'           => 'staff',
            ],
            [
                'nama'           => 'Primandhika',
                'email'          => 'primandhika@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Primandhika',
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
