<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Services\HolidayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    public function __construct(protected HolidayService $holidayService) {}

    public function dashboard()
    {
        $pegawai = Auth::user();
        $now = Carbon::now();
        $today = $now->toDateString();

        // 1. Ambil record absensi hari ini (jika ada)
        $todayAbsensi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $today)
            ->first();

        // 2. Ambil jadwal shift hari ini (jika ada)
        $todayJadwal = $pegawai->getJadwalOnDate($now);

        // 3. Ambil 7 data absensi terakhir
        $recentAbsensis = Absensi::where('pegawai_id', $pegawai->id)
            ->orderBy('tanggal', 'desc')
            ->take(7)
            ->get();

        // 4. Hitung ringkasan statistik bulan ini
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth   = $now->copy()->endOfMonth()->toDateString();

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

        $mostDiligent = Pegawai::getMostDiligentEmployee();
        $activePengumumans = \App\Models\Pengumuman::latest()->take(3)->get();

        return view('dashboard.index', compact('pegawai', 'todayAbsensi', 'todayJadwal', 'recentAbsensis', 'stats', 'mostDiligent', 'activePengumumans'));
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

            $absensi = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($absensi && $absensi->jam_masuk) {
                return back()->with('error', 'Kamu sudah absen masuk hari ini pukul ' . substr($absensi->jam_masuk, 0, 5) . ' WIB');
            }

            $statusPresensi = $request->input('status_presensi', 'reguler');
            $lokasi = $request->input('lokasi');
            $fotoPath = null;

            // Jika Absen Dinas Luar, wajib unggah foto bukti dinas luar
            if ($statusPresensi === 'dinas_luar') {
                $request->validate([
                    'foto_dinas_luar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                ]);
                $fotoPath = $request->file('foto_dinas_luar')->store('dinas_luar', 'public');
            }

            $data = [
                'pegawai_id'            => $pegawai->id,
                'tanggal'               => $today,
                'jam_masuk'             => $now->format('H:i:s'),
                'status_presensi'       => $statusPresensi,
                'lokasi_masuk'          => $lokasi,
                'foto_dinas_luar_masuk' => $fotoPath,
            ];

            if (!$absensi) {
                Absensi::create($data);
            } else {
                $absensi->update($data);
            }

            $lokasiMsg = $lokasi ? " (Lokasi: {$lokasi})" : "";
            $dinasMsg = $statusPresensi === 'dinas_luar' ? " [DINAS LUAR - Foto Bukti Terupload]" : "";

            return back()->with('success', "Absen masuk berhasil dicatat pukul {$now->format('H:i')} WIB{$dinasMsg}{$lokasiMsg}!");
        } catch (\Throwable $e) {
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

            $lokasi = $request->input('lokasi');
            $fotoPath = null;

            if ($request->hasFile('foto_dinas_luar')) {
                $request->validate([
                    'foto_dinas_luar' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
                ]);
                $fotoPath = $request->file('foto_dinas_luar')->store('dinas_luar', 'public');
            }

            $absensi->update([
                'jam_pulang'             => $now->format('H:i:s'),
                'lokasi_pulang'          => $lokasi,
                'foto_dinas_luar_pulang' => $fotoPath ?: $absensi->foto_dinas_luar_pulang,
            ]);

            $lokasiMsg = $lokasi ? " (Lokasi: {$lokasi})" : "";

            return back()->with('success', "Absen pulang berhasil dicatat pukul {$now->format('H:i')} WIB{$lokasiMsg}!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mencatat absen pulang: ' . $e->getMessage());
        }
    }
}
