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
        $currentUser = auth()->user();
        $query = Absensi::with('pegawai.divisi')->latest('tanggal');
        $pegawaiQuery = Pegawai::query();

        // Scope jika user adalah Admin Divisi (Danru/Supervisor)
        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;

            $query->whereHas('pegawai', function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });

            $pegawaiQuery->where(function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }

        if ($date = $request->input('date')) {
            $query->whereDate('tanggal', $date);
        }

        if ($pegawaiId = $request->input('pegawai_id')) {
            $query->where('pegawai_id', $pegawaiId);
        }

        $absensis = $query->paginate(20)->withQueryString();
        $pegawais = $pegawaiQuery->orderBy('nama')->get();

        $today = Carbon::today()->toDateString();
        $statsQuery = Absensi::whereDate('tanggal', $today);
        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;
            $statsQuery->whereHas('pegawai', function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }

        $stats = [
            'total_today' => (clone $statsQuery)->count(),
            'masuk_today' => (clone $statsQuery)->whereNotNull('jam_masuk')->count(),
            'pulang_today' => (clone $statsQuery)->whereNotNull('jam_pulang')->count(),
        ];

        return view('admin.absensi.index', compact('absensis', 'pegawais', 'stats', 'currentUser'));
    }

    /**
     * Input atau Koreksi Absen Terlewat / Manual
     */
    public function manualStore(Request $request)
    {
        $currentUser = auth()->user();

        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'tanggal'    => ['required', 'date'],
            'jam_masuk'  => ['nullable'],
            'jam_pulang' => ['nullable'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);

        if ($currentUser->isDivisionAdmin() && $pegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Anda hanya dapat menginput absen untuk anggota divisi Anda.');
        }

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

        $redirectUrl = $request->input('redirect_to');
        if ($redirectUrl && str_contains($redirectUrl, '/admin')) {
            return redirect($redirectUrl)->with('success', "Absensi untuk {$pegawai->nama} tanggal {$date->format('d/m/Y')} berhasil disimpan/dikoreksi.");
        }

        return redirect()->route('admin.absensi.index', [
            'date'       => $date->toDateString(),
            'pegawai_id' => $pegawai->id,
        ])->with('success', "Absensi untuk {$pegawai->nama} tanggal {$date->format('d/m/Y')} berhasil disimpan/dikoreksi.");
    }

    /**
     * Update existing record
     */
    public function update(Request $request, Absensi $absensi)
    {
        $currentUser = auth()->user();

        if ($currentUser->isDivisionAdmin() && $absensi->pegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Akses ditolak.');
        }

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
        $currentUser = auth()->user();

        if ($currentUser->isDivisionAdmin() && $absensi->pegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $absensi->delete();
        return back()->with('success', 'Catatan absensi berhasil dihapus.');
    }
}
