<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\JadwalShift;
use App\Models\Pegawai;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRekapController extends Controller
{
    public function __construct(protected HolidayService $holidayService) {}

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $startOfMonth = $carbonMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth = $carbonMonth->copy()->endOfMonth()->toDateString();
        $daysInMonth = $carbonMonth->daysInMonth;

        $query = Pegawai::with('divisi')
            ->where('role', '!=', 'super_admin')
            ->where('is_admin', false)
            ->orderBy('nama');

        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;
            $query->where(function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }

        if ($site = $request->input('site')) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        if ($search = $request->input('search')) {
            $query->where('nama', 'like', "%{$search}%");
        }

        $pegawais = $query->get();
        $clientSites = \App\Models\KantorKlien::orderBy('nama_kantor')->pluck('nama_kantor');
        $sites = $clientSites->count() > 0 ? $clientSites : Pegawai::whereNotNull('area_kerja')->where('area_kerja', '!=', '')->distinct()->pluck('area_kerja');
        $pegawaiIds = $pegawais->pluck('id');

        // Fetch all absensis for month
        $allAbsensis = Absensi::whereIn('pegawai_id', $pegawaiIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('pegawai_id');

        // Fetch all approved cutis for month
        $allCutis = Cuti::whereIn('pegawai_id', $pegawaiIds)
            ->where('status', 'approved')
            ->where('tanggal_mulai', '<=', $endOfMonth)
            ->where('tanggal_selesai', '>=', $startOfMonth)
            ->get()
            ->groupBy('pegawai_id');

        // Fetch all jadwal shifts for month
        $allJadwals = JadwalShift::whereIn('pegawai_id', $pegawaiIds)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get()
            ->groupBy('pegawai_id');

        $rekapData = collect();

        foreach ($pegawais as $pegawai) {
            $pAbsensis = $allAbsensis->get($pegawai->id, collect())->keyBy(fn($a) => Carbon::parse($a->tanggal)->day);
            $pCutis    = $allCutis->get($pegawai->id, collect());
            $pJadwals  = $allJadwals->get($pegawai->id, collect())->keyBy(fn($j) => Carbon::parse($j->tanggal)->day);
            $divisi    = $pegawai->getDivisi();

            $totalHadir = 0;
            $totalTerlambat = 0;
            $totalCuti = 0;
            $totalLibur = 0;
            $totalAlpa = 0;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $carbonMonth->copy()->day($day);
                $dateStr = $date->toDateString();
                $isPastOrToday = $date->lte(Carbon::today());

                $absensi = $pAbsensis->get($day);
                $jadwal  = $pJadwals->get($day);

                $approvedCuti = $pCutis->first(function ($c) use ($dateStr) {
                    return $c->tanggal_mulai->toDateString() <= $dateStr && $c->tanggal_selesai->toDateString() >= $dateStr;
                });

                if ($approvedCuti) {
                    $totalCuti++;
                } elseif ($absensi && !empty($absensi->keterangan) && empty($absensi->jam_masuk)) {
                    $totalCuti++;
                } elseif ($absensi && $absensi->jam_masuk) {
                    $totalHadir++;
                    if ($absensi->getMenitTerlambat($pegawai) > 0) {
                        $totalTerlambat++;
                    }
                } elseif ($jadwal && $jadwal->isLibur()) {
                    $totalLibur++;
                } elseif ($this->holidayService->isNationalHoliday($date) && $divisi->hari_kerja_tipe !== '7_hari') {
                    $totalLibur++;
                } elseif ($date->isWeekend() && $divisi->hari_kerja_tipe === '5_hari') {
                    $totalLibur++;
                } elseif ($date->isSunday() && $divisi->hari_kerja_tipe === '6_hari') {
                    $totalLibur++;
                } elseif ($divisi->hari_kerja_tipe === '7_hari' && $isPastOrToday && !$jadwal) {
                    $totalLibur++;
                } elseif ($isPastOrToday) {
                    $totalAlpa++;
                } else {
                    $totalLibur++;
                }
            }

            $totalWajib = $daysInMonth - $totalLibur;
            $persentase = $totalWajib > 0 ? round(($totalHadir / $totalWajib) * 100, 1) : 100;

            $rekapData->push((object) [
                'pegawai'        => $pegawai,
                'total_hadir'    => $totalHadir,
                'total_terlambat'=> $totalTerlambat,
                'total_cuti'     => $totalCuti,
                'total_libur'    => $totalLibur,
                'total_alpa'     => $totalAlpa,
                'persentase'     => $persentase,
            ]);
        }

        return view('admin.rekap.index', compact('rekapData', 'sites', 'bulan', 'carbonMonth', 'currentUser'));
    }

    public function detailPegawai(Request $request, Pegawai $pegawai)
    {
        $currentUser = Auth::user();
        if ($currentUser->isDivisionAdmin() && $pegawai->divisi_id !== $currentUser->divisi_id) {
            abort(403, 'Akses ditolak.');
        }

        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $absensis = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->tanggal)->day);

        return view('admin.rekap.detail', compact('pegawai', 'absensis', 'carbonMonth', 'bulan'));
    }
}
