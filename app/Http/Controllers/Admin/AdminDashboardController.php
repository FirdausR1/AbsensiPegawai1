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

class AdminDashboardController extends Controller
{
    public function __construct(protected HolidayService $holidayService) {}

    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $dateStr = $request->input('date', Carbon::today()->toDateString());
        $carbonDate = Carbon::parse($dateStr);
        $site = $request->input('site');

        // Fetch pegawai scope
        $pegawaiQuery = Pegawai::with('divisi')
            ->where('role', '!=', 'super_admin')
            ->where('is_admin', false)
            ->orderBy('nama');

        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;
            $pegawaiQuery->where(function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }

        // Filter per Kantor Klien / Site Area Kerja
        if ($site) {
            $pegawaiQuery->where('area_kerja', 'like', "%{$site}%");
        }

        if ($search = $request->input('search')) {
            $pegawaiQuery->where('nama', 'like', "%{$search}%");
        }

        $pegawais = $pegawaiQuery->get();
        $pegawaiIds = $pegawais->pluck('id');
        $clientSites = \App\Models\KantorKlien::orderBy('nama_kantor')->pluck('nama_kantor');
        $sites = $clientSites->count() > 0 ? $clientSites : Pegawai::whereNotNull('area_kerja')->where('area_kerja', '!=', '')->distinct()->pluck('area_kerja');

        // Fetch absensis on $dateStr
        $absensis = Absensi::whereIn('pegawai_id', $pegawaiIds)
            ->whereDate('tanggal', $dateStr)
            ->get()
            ->keyBy('pegawai_id');

        // Fetch approved cutis on $dateStr
        $cutis = Cuti::whereIn('pegawai_id', $pegawaiIds)
            ->where('status', 'approved')
            ->where('tanggal_mulai', '<=', $dateStr)
            ->where('tanggal_selesai', '>=', $dateStr)
            ->get()
            ->keyBy('pegawai_id');

        // Fetch jadwal shifts on $dateStr
        $jadwals = JadwalShift::whereIn('pegawai_id', $pegawaiIds)
            ->whereDate('tanggal', $dateStr)
            ->get()
            ->keyBy('pegawai_id');

        // Categorize each employee
        $listHadir = collect();
        $listTerlambat = collect();
        $listCuti = collect();
        $listLibur = collect();
        $listAlpa = collect();

        foreach ($pegawais as $pegawai) {
            $absensi = $absensis->get($pegawai->id);
            $cuti = $cutis->get($pegawai->id);
            $jadwal = $jadwals->get($pegawai->id);
            $divisi = $pegawai->getDivisi();
            $isPastOrToday = $carbonDate->lte(Carbon::today());

            $item = (object) [
                'pegawai'       => $pegawai,
                'absensi'       => $absensi,
                'cuti'          => $cuti,
                'jadwal'        => $jadwal,
                'jam_masuk'     => $absensi?->jam_masuk ? substr($absensi->jam_masuk, 0, 5) : null,
                'jam_pulang'    => $absensi?->jam_pulang ? substr($absensi->jam_pulang, 0, 5) : null,
                'keterangan'    => '',
                'menit_terlambat' => 0,
            ];

            if ($cuti) {
                $item->keterangan = "Cuti ({$cuti->jumlah_hari} Hari - {$cuti->tipe_cuti})";
                $listCuti->push($item);
            } elseif ($absensi && !empty($absensi->keterangan) && empty($absensi->jam_masuk)) {
                $item->keterangan = $absensi->keterangan;
                $listCuti->push($item);
            } elseif ($absensi && $absensi->jam_masuk) {
                $menitTerlambat = $absensi->getMenitTerlambat($pegawai);
                $item->menit_terlambat = $menitTerlambat;
                if ($menitTerlambat > 0) {
                    $item->keterangan = "Terlambat {$menitTerlambat} Menit";
                    $listTerlambat->push($item);
                } else {
                    $item->keterangan = "Tepat Waktu";
                    $listHadir->push($item);
                }
            } elseif ($jadwal && $jadwal->isLibur()) {
                $item->keterangan = "Libur (Jadwal Shift)";
                $listLibur->push($item);
            } elseif ($this->holidayService->isNationalHoliday($carbonDate) && $divisi->hari_kerja_tipe !== '7_hari') {
                $item->keterangan = $this->holidayService->reasonIfHoliday($carbonDate) ?: "Libur Nasional";
                $listLibur->push($item);
            } elseif ($carbonDate->isWeekend() && $divisi->hari_kerja_tipe === '5_hari') {
                $item->keterangan = "Libur Akhir Pekan";
                $listLibur->push($item);
            } elseif ($carbonDate->isSunday() && $divisi->hari_kerja_tipe === '6_hari') {
                $item->keterangan = "Libur Hari Minggu";
                $listLibur->push($item);
            } elseif ($divisi->hari_kerja_tipe === '7_hari' && $isPastOrToday && !$jadwal) {
                $item->keterangan = "Libur Bergilir";
                $listLibur->push($item);
            } elseif ($isPastOrToday) {
                $item->keterangan = "ALPA (Belum/Tidak Absen)";
                $listAlpa->push($item);
            } else {
                $item->keterangan = "Belum Waktunya";
                $listLibur->push($item);
            }
        }

        $stats = [
            'total_pegawai'   => $pegawais->count(),
            'total_hadir'     => $listHadir->count() + $listTerlambat->count(),
            'total_tepat'     => $listHadir->count(),
            'total_terlambat' => $listTerlambat->count(),
            'total_cuti'      => $listCuti->count(),
            'total_libur'     => $listLibur->count(),
            'total_alpa'      => $listAlpa->count(),
        ];

        // Most Diligent per Site
        $mostDiligent = Pegawai::getMostDiligentEmployee($carbonDate->format('Y-m'), $site);

        return view('admin.dashboard.index', compact(
            'currentUser', 'dateStr', 'carbonDate', 'stats', 'sites', 'site',
            'listHadir', 'listTerlambat', 'listCuti', 'listLibur', 'listAlpa', 'mostDiligent'
        ));
    }
}
