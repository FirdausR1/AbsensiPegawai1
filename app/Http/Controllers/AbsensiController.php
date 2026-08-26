<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function __construct(
        protected HolidayService $holidayService,
    ) {}

    public function dashboard()
    {
        $pegawai = Auth::user();
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $todayAbsensi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $today)
            ->first();

        $recentAbsensis = Absensi::where('pegawai_id', $pegawai->id)
            ->latest('tanggal')
            ->take(7)
            ->get();

        $stats = [
            'total_bulan_ini' => Absensi::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereNotNull('jam_masuk')
                ->count(),
            'total_lengkap' => Absensi::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereNotNull('jam_masuk')
                ->whereNotNull('jam_pulang')
                ->count(),
        ];

        return view('dashboard.index', compact('pegawai', 'todayAbsensi', 'recentAbsensis', 'stats'));
    }

    public function riwayat(Request $request)
    {
        $pegawai = Auth::user();

        // Default bulan dan tahun saat ini
        $selectedMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();

        $startOfMonth = $carbonMonth->copy()->startOfMonth()->toDateString();
        $endOfMonth = $carbonMonth->copy()->endOfMonth()->toDateString();

        $absensis = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->orderBy('tanggal', 'asc')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->tanggal)->format('Y-m-d');
            });

        // Bangun data kalender harian dalam 1 bulan
        $daysInMonth = $carbonMonth->daysInMonth;
        $monthlyDays = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $currentDate = $carbonMonth->copy()->day($day);
            $dateString = $currentDate->toDateString();
            $isWeekend = $currentDate->isWeekend();
            $isHoliday = !$this->holidayService->isWorkingDay($currentDate);
            $holidayReason = $this->holidayService->reasonIfHoliday($currentDate);

            $absensiRecord = $absensis->get($dateString);

            $monthlyDays[] = [
                'day'           => $day,
                'date'          => $currentDate,
                'is_weekend'    => $isWeekend,
                'is_holiday'    => $isHoliday,
                'holiday_reason'=> $holidayReason,
                'absensi'       => $absensiRecord,
                'is_today'      => $currentDate->isToday(),
                'is_future'     => $currentDate->isFuture(),
            ];
        }

        $summary = [
            'total_hadir'     => $absensis->whereNotNull('jam_masuk')->count(),
            'total_pulang'    => $absensis->whereNotNull('jam_pulang')->count(),
            'total_synced'    => $absensis->where('synced_to_sheet', true)->count(),
            'total_hari_kerja'=> collect($monthlyDays)->filter(fn($d) => !$d['is_weekend'] && !$d['is_holiday'])->count(),
        ];

        return view('profile.riwayat', compact('pegawai', 'monthlyDays', 'selectedMonth', 'carbonMonth', 'summary'));
    }

    public function absenMasuk(Request $request)
    {
        try {
            $pegawai = Auth::user();
            $now = Carbon::now();
            $today = $now->toDateString();

            if (!$pegawai->hasSignature()) {
                return back()->with('error', 'Silakan buat tanda tangan digital dulu di halaman profil sebelum absen.');
            }

            if (!$this->holidayService->isWorkingDay($now)) {
                $reason = $this->holidayService->reasonIfHoliday($now) ?? 'Hari Libur';
                return back()->with('error', 'Hari ini libur (' . $reason . '), tidak perlu absen.');
            }

            $absensi = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($absensi && $absensi->jam_masuk) {
                return back()->with('error', 'Kamu sudah absen masuk hari ini pukul ' . substr($absensi->jam_masuk, 0, 5) . ' WIB');
            }

            if (!$absensi) {
                Absensi::create([
                    'pegawai_id' => $pegawai->id,
                    'tanggal'    => $today,
                    'jam_masuk'  => $now->format('H:i:s'),
                ]);
            } else {
                $absensi->update(['jam_masuk' => $now->format('H:i:s')]);
            }

            return back()->with('success', 'Absen masuk berhasil dicatat pukul ' . $now->format('H:i') . ' WIB!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error absenMasuk: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencatat absen masuk: ' . $e->getMessage());
        }
    }

    public function absenPulang(Request $request)
    {
        try {
            $pegawai = Auth::user();
            $now = Carbon::now();
            $today = $now->toDateString();

            $absensi = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->first();

            if (!$absensi || !$absensi->jam_masuk) {
                return back()->with('error', 'Kamu belum melakukan absen masuk hari ini.');
            }

            if ($absensi->jam_pulang) {
                return back()->with('error', 'Kamu sudah absen pulang hari ini pukul ' . substr($absensi->jam_pulang, 0, 5) . ' WIB');
            }

            $absensi->update(['jam_pulang' => $now->format('H:i:s')]);

            return back()->with('success', 'Absen pulang berhasil dicatat pukul ' . $now->format('H:i') . ' WIB!');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Error absenPulang: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal mencatat absen pulang: ' . $e->getMessage());
        }
    }
}
