<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\JadwalShift;
use App\Models\KantorKlien;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminJadwalShiftController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonBulan = Carbon::parse($bulan . '-01');

        // Query pegawai yang bisa dikelola
        $pegawaiQuery = Pegawai::with('divisi')
            ->where('role', '!=', 'super_admin')
            ->orderBy('area_kerja')
            ->orderBy('nama');

        // Supervisor / Admin Divisi hanya bisa melihat & mengatur shift pegawai di Kantor Klien-nya sendiri / divisinya
        if ($currentUser->isDivisionAdmin()) {
            $myArea = strtolower(trim($currentUser->area_kerja ?? ''));
            $myDivisiId = $currentUser->divisi_id;
            $myKeyword = strtolower(trim(($currentUser->area_kerja ?? '') . ' ' . ($currentUser->nama ?? '') . ' ' . ($currentUser->divisi?->nama ?? '')));

            $pegawaiQuery->where(function ($q) use ($myArea, $myDivisiId, $myKeyword) {
                if ($myDivisiId) {
                    $q->where('divisi_id', $myDivisiId);
                }
                if ($myArea) {
                    $q->orWhere('area_kerja', 'like', "%{$myArea}%");
                }
                if (str_contains($myKeyword, 'satpam') || str_contains($myKeyword, 'danru') || str_contains($myKeyword, 'security')) {
                    $q->orWhereHas('divisi', function ($sub) {
                        $sub->where('nama', 'like', '%satpam%')->orWhere('nama', 'like', '%security%');
                    })->orWhere('area_kerja', 'like', '%satpam%');
                }
                if (str_contains($myKeyword, 'clean')) {
                    $q->orWhereHas('divisi', function ($sub) {
                        $sub->where('nama', 'like', '%cleaning%');
                    })->orWhere('area_kerja', 'like', '%cleaning%');
                }
                // Koordinator / Danru Lapangan bisa mengelola seluruh pekerja divisi shift
                $q->orWhereHas('divisi', function ($sub) {
                    $sub->whereIn('hari_kerja_tipe', ['7_hari', '6_hari'])
                        ->orWhere('nama', 'like', '%shift%');
                });
            });
        }

        if ($divisiId = $request->input('divisi_id')) {
            $pegawaiQuery->where('divisi_id', $divisiId);
        }

        if ($area = $request->input('area')) {
            $pegawaiQuery->where('area_kerja', 'like', "%{$area}%");
        }

        $pegawais = $pegawaiQuery->get();

        // Grouping pegawai berdasarkan Divisi / Area Kerja
        $pegawaisGrouped = $pegawais->groupBy(function ($p) {
            return $p->area_kerja ?: ($p->divisi?->nama ?? 'Staff / General');
        });

        // Ambil semua jadwal bulan ini untuk pegawai-pegawai tersebut
        $pegawaiIds = $pegawais->pluck('id');
        $jadwals = JadwalShift::whereIn('pegawai_id', $pegawaiIds)
            ->whereBetween('tanggal', [
                $carbonBulan->copy()->startOfMonth()->toDateString(),
                $carbonBulan->copy()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->keyBy(function ($j) {
                return $j->pegawai_id . '_' . $j->tanggal->format('Y-m-d');
            });

        $divisis = Divisi::all();

        // Ambil daftar area resmi dari Master Data Kantor Klien
        $clientSites = KantorKlien::orderBy('nama_kantor')->pluck('nama_kantor');
        $areas = $clientSites->count() > 0 
            ? $clientSites 
            : Pegawai::whereNotNull('area_kerja')->distinct()->pluck('area_kerja')->filter()->sort()->values();

        $pendingQuery = JadwalShift::with(['pegawai.divisi'])
            ->where('status', 'requested')
            ->latest('tanggal');

        if ($currentUser->isDivisionAdmin()) {
            $pendingQuery->whereHas('pegawai', function ($q) use ($currentUser) {
                $myArea = strtolower(trim($currentUser->area_kerja ?? ''));
                $myDivisiId = $currentUser->divisi_id;
                $myKeyword = strtolower(trim(($currentUser->area_kerja ?? '') . ' ' . ($currentUser->nama ?? '') . ' ' . ($currentUser->divisi?->nama ?? '')));

                $q->where(function ($sq) use ($myArea, $myDivisiId, $myKeyword) {
                    if ($myDivisiId) {
                        $sq->where('divisi_id', $myDivisiId);
                    }
                    if ($myArea) {
                        $sq->orWhere('area_kerja', 'like', "%{$myArea}%");
                    }
                    if (str_contains($myKeyword, 'satpam') || str_contains($myKeyword, 'danru') || str_contains($myKeyword, 'security')) {
                        $sq->orWhereHas('divisi', function ($sub) {
                            $sub->where('nama', 'like', '%satpam%')->orWhere('nama', 'like', '%security%');
                        })->orWhere('area_kerja', 'like', '%satpam%');
                    }
                    if (str_contains($myKeyword, 'clean')) {
                        $sq->orWhereHas('divisi', function ($sub) {
                            $sub->where('nama', 'like', '%cleaning%');
                        })->orWhere('area_kerja', 'like', '%cleaning%');
                    }
                    // Koordinator / Danru Lapangan bisa melihat dan meng-ACC seluruh permohonan pekerja shift
                    $sq->orWhereHas('divisi', function ($sub) {
                        $sub->whereIn('hari_kerja_tipe', ['7_hari', '6_hari'])
                            ->orWhere('nama', 'like', '%shift%');
                    });
                });
            });
        }
        $pendingRequests = $pendingQuery->get();

        return view('admin.jadwal-shift.index', compact(
            'pegawais', 'pegawaisGrouped', 'jadwals', 'carbonBulan', 'bulan', 'areas', 'divisis', 'currentUser', 'pendingRequests'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'tanggal'    => ['required', 'date'],
            'tipe_shift' => ['required', 'in:Pagi,Malam,Siang,Libur'],
            'jam_masuk'  => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'catatan'    => ['nullable', 'string', 'max:500'],
        ]);

        $currentUser = Auth::user();
        $targetPegawai = Pegawai::with('divisi')->find($request->pegawai_id);

        if ($currentUser->isDivisionAdmin() && !$currentUser->canManagePegawai($targetPegawai)) {
            return response()->json(['error' => 'Akses ditolak. Anda hanya dapat mengelola shift pegawai di divisi/area Anda.'], 403);
        }

        // Default jam jika tidak diisi
        $jam = JadwalShift::defaultJam($request->tipe_shift, $targetPegawai);

        JadwalShift::updateOrCreate(
            [
                'pegawai_id' => $request->pegawai_id,
                'tanggal'    => $request->tanggal,
            ],
            [
                'tipe_shift' => $request->tipe_shift,
                'jam_masuk'  => $request->jam_masuk ?: ($jam['jam_masuk'] ?? null),
                'jam_pulang' => $request->jam_pulang ?: ($jam['jam_pulang'] ?? null),
                'status'     => 'confirmed',
                'catatan'    => $request->catatan,
                'created_by' => $currentUser->id,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function storeBulk(Request $request)
    {
        $request->validate([
            'pegawai_id'     => ['required', 'exists:pegawais,id'],
            'tanggal_dari'   => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'tipe_shift'     => ['required', 'in:Pagi,Malam,Siang,Libur'],
            'hari_aktif'     => ['required', 'array'],  // [0=Min,1=Sen,...,6=Sab]
        ]);

        $currentUser = Auth::user();
        $targetPegawai = Pegawai::with('divisi')->find($request->pegawai_id);

        if ($currentUser->isDivisionAdmin() && !$currentUser->canManagePegawai($targetPegawai)) {
            return back()->with('error', 'Akses ditolak. Anda hanya dapat mengelola shift pegawai di divisi/area Anda.');
        }

        $jam = JadwalShift::defaultJam($request->tipe_shift, $targetPegawai);

        $start = Carbon::parse($request->tanggal_dari);
        $end   = Carbon::parse($request->tanggal_sampai);
        $hariAktif = array_map('intval', $request->hari_aktif);

        $count = 0;
        while ($start->lte($end)) {
            $dayOfWeek = $start->dayOfWeek; // 0=Sun, 1=Mon,...
            if (in_array($dayOfWeek, $hariAktif)) {
                JadwalShift::updateOrCreate(
                    [
                        'pegawai_id' => $request->pegawai_id,
                        'tanggal'    => $start->toDateString(),
                    ],
                    [
                        'tipe_shift' => $request->tipe_shift,
                        'jam_masuk'  => $jam['jam_masuk'] ?? null,
                        'jam_pulang' => $jam['jam_pulang'] ?? null,
                        'status'     => 'confirmed',
                        'catatan'    => $request->catatan ?? null,
                        'created_by' => $currentUser->id,
                    ]
                );
                $count++;
            }
            $start->addDay();
        }

        return back()->with('success', "Jadwal shift ({$request->tipe_shift}) berhasil diset untuk {$count} hari pada pegawai {$targetPegawai->nama}!");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'tanggal'    => ['required', 'date'],
        ]);

        $currentUser = Auth::user();
        $targetPegawai = Pegawai::find($request->pegawai_id);

        if ($currentUser->isDivisionAdmin() && !$currentUser->canManagePegawai($targetPegawai)) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        JadwalShift::where('pegawai_id', $request->pegawai_id)
            ->whereDate('tanggal', $request->tanggal)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function approve(JadwalShift $jadwalShift)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasAdminAccess()) {
            return back()->with('error', 'Akses ditolak. Anda tidak memiliki hak akses admin.');
        }

        if ($currentUser->isDivisionAdmin() && !$currentUser->canManagePegawai($jadwalShift->pegawai)) {
            return back()->with('error', 'Akses ditolak. Anda hanya dapat menyetujui shift anggota divisi/area Anda.');
        }

        $jadwalShift->update(['status' => 'confirmed']);
        $namaPegawai = $jadwalShift->pegawai->nama;
        $tanggal = $jadwalShift->tanggal->translatedFormat('d F Y');
        return back()->with('success', "Pengajuan jadwal shift untuk {$namaPegawai} ({$jadwalShift->tipe_shift}) pada tanggal {$tanggal} berhasil disetujui (ACC).");
    }

    public function reject(JadwalShift $jadwalShift)
    {
        $currentUser = Auth::user();
        if (!$currentUser->hasAdminAccess()) {
            return back()->with('error', 'Akses ditolak. Anda tidak memiliki hak akses admin.');
        }

        if ($currentUser->isDivisionAdmin() && !$currentUser->canManagePegawai($jadwalShift->pegawai)) {
            return back()->with('error', 'Akses ditolak. Anda hanya dapat menolak shift anggota divisi/area Anda.');
        }

        $namaPegawai = $jadwalShift->pegawai->nama;
        $tanggal = $jadwalShift->tanggal->translatedFormat('d F Y');
        $jadwalShift->delete();
        return back()->with('success', "Pengajuan jadwal shift {$namaPegawai} pada tanggal {$tanggal} telah ditolak.");
    }
}
