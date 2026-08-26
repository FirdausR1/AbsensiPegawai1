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
        $query = Absensi::with('pegawai')->latest('tanggal');

        if ($date = $request->input('date')) {
            $query->whereDate('tanggal', $date);
        }

        if ($pegawaiId = $request->input('pegawai_id')) {
            $query->where('pegawai_id', $pegawaiId);
        }

        if ($request->filled('synced')) {
            $query->where('synced_to_sheet', $request->boolean('synced'));
        }

        $absensis = $query->paginate(20)->withQueryString();
        $pegawais = Pegawai::orderBy('nama')->get();

        $today = Carbon::today()->toDateString();
        $stats = [
            'total_today' => Absensi::whereDate('tanggal', $today)->count(),
            'masuk_today' => Absensi::whereDate('tanggal', $today)->whereNotNull('jam_masuk')->count(),
            'pulang_today' => Absensi::whereDate('tanggal', $today)->whereNotNull('jam_pulang')->count(),
            'failed_sync' => Absensi::where('synced_to_sheet', false)->whereNotNull('jam_masuk')->count(),
        ];

        return view('admin.absensi.index', compact('absensis', 'pegawais', 'stats'));
    }

    public function retrySync(Absensi $absensi)
    {
        // Google Sheets sync telah dinonaktifkan.
        // Gunakan fitur Export Excel untuk mengunduh data absensi.
        return back()->with('info', 'Sinkronisasi ke Google Sheets sudah dinonaktifkan. Gunakan tombol Export Excel.');
    }

    public function manualStore(Request $request)
    {
        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'tanggal'    => ['required', 'date'],
            'jam_masuk'  => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);
        $date = Carbon::parse($request->tanggal);

        $absensi = Absensi::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'tanggal'    => $date->toDateString(),
            ],
            [
                'jam_masuk'  => $request->jam_masuk ? $request->jam_masuk . ':00' : null,
                'jam_pulang' => $request->jam_pulang ? $request->jam_pulang . ':00' : null,
                'keterangan' => $request->keterangan,
            ]
        );

        return back()->with('success', "Absensi manual untuk {$pegawai->nama} tanggal {$date->format('d/m/Y')} berhasil disimpan.");
    }

    public function destroy(Absensi $absensi)
    {
        $absensi->delete();
        return back()->with('success', 'Catatan absensi berhasil dihapus.');
    }
}
