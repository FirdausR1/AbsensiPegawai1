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
                'nama'           => 'Administrator',
                'email'          => 'admin@perusahaan.com',
                'password'       => Hash::make('admin123'),
                'area_kerja'     => 'Management / HR',
                'sheet_tab_name' => 'Admin',
                'is_admin'       => true,
            ],
            [
                'nama'           => 'Firdaus Romandhanu',
                'email'          => 'firdaus@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Firdaus Romandhanu',
                'is_admin'       => true,
            ],
            [
                'nama'           => 'Isnan Anugroho',
                'email'          => 'isnan@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Isnan Anugroho',
                'is_admin'       => false,
            ],
            [
                'nama'           => 'Wahyu Ari Nugroho',
                'email'          => 'wahyu@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Wahyu Ari Nugroho',
                'is_admin'       => false,
            ],
            [
                'nama'           => 'Mohamad Adjwadi',
                'email'          => 'adjwadi@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Mohamad Adjwadi',
                'is_admin'       => false,
            ],
            [
                'nama'           => 'Primandhika',
                'email'          => 'primandhika@perusahaan.com',
                'password'       => Hash::make('password123'),
                'area_kerja'     => 'Operasional',
                'sheet_tab_name' => 'Primandhika',
                'is_admin'       => false,
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
