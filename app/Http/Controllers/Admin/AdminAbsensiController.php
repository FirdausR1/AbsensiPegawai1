<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $query = Absensi::with('pegawai.divisi')->latest('tanggal');

        if ($date = $request->input('date')) {
            $query->whereDate('tanggal', $date);
        }

        if ($pegawaiId = $request->input('pegawai_id')) {
            $query->where('pegawai_id', $pegawaiId);
        }

        $absensis = $query->paginate(20)->withQueryString();
        $pegawais = Pegawai::orderBy('nama')->get();

        $today = Carbon::today()->toDateString();
        $stats = [
            'total_today' => Absensi::whereDate('tanggal', $today)->count(),
            'masuk_today' => Absensi::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count(),
            'pulang_today' => Absensi::whereDate('tanggal', $today)->whereNotNull('jam_pulang')->count(),
        ];

        return view('admin.absensi.index', compact('absensis', 'pegawais', 'stats'));
    }

    /**
     * Input atau Koreksi Absen Terlewat / Manual
     */
    public function manualStore(Request $request)
    {
        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'tanggal'    => ['required', 'date'],
            'jam_masuk'  => ['nullable'],
            'jam_pulang' => ['nullable'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);
        $date = Carbon::parse($request->tanggal);

        $jamMasuk = $request->jam_masuk 
            ? (strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk)
            : null;

        $jamPulang = $request->jam_pulang 
            ? (strlen($request->jam_pulang) === 5 ? $request->jam_pulang . ':00' : $request->jam_pulang)
            : null;

        $absensi = Absensi::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'tanggal'    => $date->toDateString(),
            ],
            [
                'jam_masuk'  => $jamMasuk,
                'jam_pulang' => $jamPulang,
                'keterangan' => $request->keterangan,
            ]
        );

        return back()->with('success', "Absensi untuk {$pegawai->nama} tanggal {$date->format('d/m/Y')} berhasil disimpan/dikoreksi.");
    }

    /**
     * Update existing record
     */
    public function update(Request $request, Absensi $absensi)
    {
        $request->validate([
            'jam_masuk'  => ['nullable'],
            'jam_pulang' => ['nullable'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $jamMasuk = $request->jam_masuk 
            ? (strlen($request->jam_masuk) === 5 ? $request->jam_masuk . ':00' : $request->jam_masuk)
            : null;

        $jamPulang = $request->jam_pulang 
            ? (strlen($request->jam_pulang) === 5 ? $request->jam_pulang . ':00' : $request->jam_pulang)
            : null;

        $absensi->update([
            'jam_masuk'  => $jamMasuk,
            'jam_pulang' => $jamPulang,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('success', "Data absensi tanggal {$absensi->tanggal->format('d/m/Y')} berhasil diperbarui.");
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();
        return back()->with('success', 'Catatan absensi berhasil dihapus.');
    }
}
