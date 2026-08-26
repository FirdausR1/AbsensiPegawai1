<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\JadwalShift;
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

        if ($currentUser->isDivisionAdmin()) {
            $pegawaiQuery->where(function ($q) use ($currentUser) {
                if ($currentUser->divisi_id) {
                    $q->where('divisi_id', $currentUser->divisi_id);
                }
                if ($currentUser->area_kerja) {
                    $q->orWhere('area_kerja', 'like', '%' . $currentUser->area_kerja . '%');
                }
            });
        }

        // Filter by area/divisi
        if ($area = $request->input('area')) {
            $pegawaiQuery->where('area_kerja', 'like', "%{$area}%");
        }

        $pegawais = $pegawaiQuery->get();

        // Grouping pegawai berdasarkan Divisi / Department / Area Kerja
        $pegawaisGrouped = $pegawais->groupBy(function ($p) {
            if ($p->divisi) {
                return $p->divisi->nama;
            }
            return $p->area_kerja ?: 'Staff / General';
        });

        // Ambil semua jadwal bulan ini untuk pegawai-pegawai tersebut
        $pegawaiIds = $pegawais->pluck('id');
        $jadwals = JadwalShift::whereIn('pegawai_id', $pegawaiIds)
            ->whereBetween('tanggal', [
                $carbonBulan->copy()->startOfMonth()->toDateString(),
                $carbonBulan->copy()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->groupBy(function ($j) {
                return $j->pegawai_id . '_' . $j->tanggal->format('Y-m-d');
            });

        $divisis = Divisi::all();

        $areas = Pegawai::whereNotNull('area_kerja')
            ->distinct()
            ->pluck('area_kerja')
            ->filter()
            ->sort()
            ->values();

        return view('admin.jadwal-shift.index', compact(
            'pegawais', 'pegawaisGrouped', 'jadwals', 'carbonBulan', 'bulan', 'areas', 'divisis', 'currentUser'
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

        if ($currentUser->isDivisionAdmin()) {
            if ($targetPegawai && $targetPegawai->divisi_id !== $currentUser->divisi_id) {
                return response()->json(['error' => 'Akses ditolak.'], 403);
            }
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

    /**
     * Bulk assign shift untuk satu pegawai di banyak tanggal (misal seminggu)
     */
    public function storeBulk(Request $request)
    {
        $request->validate([
            'pegawai_id'  => ['required', 'exists:pegawais,id'],
            'tanggal_dari' => ['required', 'date'],
            'tanggal_sampai' => ['required', 'date', 'after_or_equal:tanggal_dari'],
            'tipe_shift'  => ['required', 'in:Pagi,Malam,Siang,Libur'],
            'hari_aktif'  => ['required', 'array'],  // [0=Min,1=Sen,...,6=Sab]
        ]);

        $currentUser = Auth::user();
        $targetPegawai = Pegawai::with('divisi')->find($request->pegawai_id);
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

        return back()->with('success', "Berhasil menjadwalkan {$count} hari shift {$request->tipe_shift}.");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'tanggal'    => ['required', 'date'],
        ]);

        JadwalShift::where('pegawai_id', $request->pegawai_id)
            ->whereDate('tanggal', $request->tanggal)
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Approve request tukar shift dari satpam
     */
    public function approveRequest(JadwalShift $jadwalShift)
    {
        $jadwalShift->update(['status' => 'confirmed']);
        return back()->with('success', 'Request shift disetujui.');
    }

    /**
     * Lihat semua request yang pending (dari satpam)
     */
    public function requests(Request $request)
    {
        $currentUser = Auth::user();

        $query = JadwalShift::with('pegawai.divisi')
            ->where('status', 'requested')
            ->orderBy('tanggal');

        if ($currentUser->isDivisionAdmin()) {
            $query->whereHas('pegawai', function ($q) use ($currentUser) {
                $q->where('divisi_id', $currentUser->divisi_id);
            });
        }

        $requests = $query->paginate(20);

        return view('admin.jadwal-shift.requests', compact('requests', 'currentUser'));
    }
}
