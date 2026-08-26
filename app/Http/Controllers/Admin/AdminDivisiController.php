<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class AdminDivisiController extends Controller
{
    public function index()
    {
        // Pastikan ada default divisi jika tabel masih kosong
        if (Divisi::count() === 0) {
            $defaultDivisis = [
                [
                    'nama' => 'Staff Kantor',
                    'jam_masuk' => '08:00:00',
                    'jam_pulang' => '17:00:00',
                    'toleransi_menit' => 15,
                    'keterangan' => 'Jam operasional reguler staf kantor & administrasi',
                ],
                [
                    'nama' => 'Cleaning Service',
                    'jam_masuk' => '06:30:00',
                    'jam_pulang' => '15:30:00',
                    'toleransi_menit' => 10,
                    'keterangan' => 'Shift kebersihan dan pemeliharaan gedung pagi',
                ],
                [
                    'nama' => 'Satpam / Security',
                    'jam_masuk' => '07:00:00',
                    'jam_pulang' => '19:00:00',
                    'toleransi_menit' => 0,
                    'keterangan' => 'Shift keamanan dan ketertiban area gedung',
                ],
                [
                    'nama' => 'Operasional & IT',
                    'jam_masuk' => '08:30:00',
                    'jam_pulang' => '17:30:00',
                    'toleransi_menit' => 15,
                    'keterangan' => 'Divisi teknologi informasi & operasional sistem',
                ],
            ];

            foreach ($defaultDivisis as $div) {
                Divisi::create($div);
            }
        }

        $divisis = Divisi::withCount('pegawais')->orderBy('nama', 'asc')->get();

        return view('admin.divisi.index', compact('divisis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:divisis,nama'],
            'jam_masuk' => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i'],
            'toleransi_menit' => ['required', 'integer', 'min:0', 'max:120'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        Divisi::create([
            'nama' => $request->nama,
            'jam_masuk' => $request->jam_masuk . ':00',
            'jam_pulang' => $request->jam_pulang . ':00',
            'toleransi_menit' => (int) $request->toleransi_menit,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.divisi.index')->with('success', "Divisi {$request->nama} berhasil ditambahkan.");
    }

    public function update(Request $request, Divisi $divisi)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:divisis,nama,' . $divisi->id],
            'jam_masuk' => ['required'],
            'jam_pulang' => ['required'],
            'toleransi_menit' => ['required', 'integer', 'min:0', 'max:120'],
            'keterangan' => ['nullable', 'string', 'max:500'],
        ]);

        $jamMasuk = strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk;
        $jamPulang = strlen($request->jam_pulang) === 5 ? $request->jam_pulang . ':00' : $request->jam_pulang;

        $divisi->update([
            'nama' => $request->nama,
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang,
            'toleransi_menit' => (int) $request->toleransi_menit,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.divisi.index')->with('success', "Jadwal jam kerja divisi {$divisi->nama} berhasil diperbarui.");
    }

    public function destroy(Divisi $divisi)
    {
        // Unlink pegawais before deleting
        Pegawai::where('divisi_id', $divisi->id)->update(['divisi_id' => null]);

        $nama = $divisi->nama;
        $divisi->delete();

        return redirect()->route('admin.divisi.index')->with('success', "Divisi {$nama} berhasil dihapus.");
    }
}
