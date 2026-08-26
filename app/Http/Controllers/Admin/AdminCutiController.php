<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCutiController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $query = Cuti::with(['pegawai.divisi', 'approver'])->latest('created_at');

        // Scoping jika user adalah Admin Divisi (Danru/Supervisor)
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
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $cutis = $query->paginate(15)->withQueryString();

        $statsQuery = Cuti::query();
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
            'total'    => (clone $statsQuery)->count(),
            'pending'  => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
        ];

        return view('admin.cuti.index', compact('cutis', 'stats', 'currentUser'));
    }

    public function approve(Request $request, Cuti $cuti)
    {
        $currentUser = Auth::user();

        if ($currentUser->isDivisionAdmin() && $cuti->pegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Anda hanya dapat menyetujui cuti untuk anggota divisi Anda.');
        }

        $cuti->update([
            'status'        => 'approved',
            'approved_by'   => $currentUser->id,
            'approved_at'   => Carbon::now(),
            'catatan_admin' => $request->input('catatan_admin', 'Disetujui oleh ' . $currentUser->nama),
        ]);

        // Otomatis tandai setiap hari cuti di tabel absensi agar tidak dihitung ALPA
        $current = Carbon::parse($cuti->tanggal_mulai);
        $end     = Carbon::parse($cuti->tanggal_selesai);

        while ($current->lte($end)) {
            $dateStr = $current->toDateString();
            
            $absensi = Absensi::where('pegawai_id', $cuti->pegawai_id)
                ->whereDate('tanggal', $dateStr)
                ->first();

            $keteranganCuti = "Cuti ({$cuti->jumlah_hari} Hari - {$cuti->tipe_cuti})";

            if (!$absensi) {
                Absensi::create([
                    'pegawai_id' => $cuti->pegawai_id,
                    'tanggal'    => $dateStr,
                    'keterangan' => $keteranganCuti,
                ]);
            } elseif (empty($absensi->jam_masuk)) {
                $absensi->update([
                    'keterangan' => $keteranganCuti,
                ]);
            }

            $current->addDay();
        }

        return back()->with('success', "Pengajuan cuti untuk {$cuti->pegawai->nama} selama {$cuti->jumlah_hari} hari berhasil disetujui.");
    }

    public function reject(Request $request, Cuti $cuti)
    {
        $currentUser = Auth::user();

        if ($currentUser->isDivisionAdmin() && $cuti->pegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ]);

        $cuti->update([
            'status'        => 'rejected',
            'approved_by'   => $currentUser->id,
            'approved_at'   => Carbon::now(),
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', "Pengajuan cuti untuk {$cuti->pegawai->nama} telah ditolak.");
    }
}
