<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\JadwalShift;
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

        // 1. Ambil sesi absensi aktif (termasuk jika kemarin dinas jaga malam dan belum checkout pagi ini)
        $activeAbsensi = $pegawai->getActiveAbsensi($now);
        $isOvernightShift = $pegawai->hasActiveOvernightShift($now);

        // Record absensi hari ini (jika ada)
        $todayAbsensi = Absensi::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $today)
            ->first();

        // 2. Ambil jadwal shift hari ini (jika ada)
        $todayJadwal = $pegawai->getJadwalOnDate($now);

        // Cek apakah hari ini berstatus Libur
        $isTodayLibur = ($todayAbsensi && (str_contains(strtolower($todayAbsensi->keterangan ?? ''), 'libur') || $todayAbsensi->status_presensi === 'libur'))
            || ($todayJadwal && $todayJadwal->isLibur() && (!$todayAbsensi || !$todayAbsensi->jam_masuk));

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

        return view('dashboard.index', compact('pegawai', 'todayAbsensi', 'activeAbsensi', 'isOvernightShift', 'todayJadwal', 'isTodayLibur', 'recentAbsensis', 'stats', 'mostDiligent', 'activePengumumans'));
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

            // Jika masih ada sesi shift malam aktif dari kemarin yang belum di-checkout, larang absen masuk baru!
            if ($pegawai->hasActiveOvernightShift($now)) {
                $overnight = $pegawai->getActiveAbsensi($now);
                $tglKemarin = Carbon::parse($overnight->tanggal)->translatedFormat('d F Y');
                return back()->with('error', "Anda masih tercatat dalam sesi dinas jaga malam (Shift {$tglKemarin}, masuk pukul " . substr($overnight->jam_masuk, 0, 5) . " WIB). Silakan klik Catat Absen Pulang terlebih dahulu untuk menyelesaikan tugas malam Anda sebelum memulai shift baru!");
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
            $yesterday = Carbon::parse($today)->subDay()->toDateString();

            // 1. Ambil sesi absensi aktif (otomatis mendeteksi jika sedang shift jaga malam kemarin)
            $absensi = $pegawai->getActiveAbsensi($now);

            // Fallback: jika getActiveAbsensi null atau sudah terisi jam_pulang, cari unclosed kemarin atau hari ini
            if (!$absensi || $absensi->jam_pulang) {
                $absensi = Absensi::where('pegawai_id', $pegawai->id)
                    ->where(function($query) use ($today, $yesterday) {
                        $query->whereDate('tanggal', $yesterday)
                              ->orWhereDate('tanggal', $today);
                    })
                    ->whereNotNull('jam_masuk')
                    ->whereNull('jam_pulang')
                    ->orderBy('tanggal', 'asc') // Utamakan yang lebih awal (kemarin dulu)
                    ->first();
            }

            if (!$absensi) {
                return back()->with('error', 'Kamu belum melakukan absen masuk atau sudah absen pulang sebelumnya.');
            }

            if ($absensi->jam_pulang) {
                return back()->with('error', 'Kamu sudah absen pulang untuk sesi ini pukul ' . substr($absensi->jam_pulang, 0, 5) . ' WIB');
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

            // Jika sesi yang ditutup adalah shift malam kemarin, dan sebelumnya sempat terjadi dobel masuk di pagi hari ini tanpa pulang, bersihkan record dobel masuk tersebut
            $isClosedYesterday = Carbon::parse($absensi->tanggal)->toDateString() === $yesterday;

            if ($isClosedYesterday) {
                $accidentalDuplicate = Absensi::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal', $today)
                    ->where('id', '!=', $absensi->id)
                    ->whereNull('jam_pulang')
                    ->where('jam_masuk', '<=', '11:00:00')
                    ->first();

                if ($accidentalDuplicate) {
                    $accidentalDuplicate->delete();
                }
            }

            $lokasiMsg = $lokasi ? " (Lokasi: {$lokasi})" : "";

            if ($isClosedYesterday) {
                $tglKemarin = Carbon::parse($absensi->tanggal)->translatedFormat('d F Y');
                return back()->with('success', "Absen pulang tugas jaga malam (Shift {$tglKemarin}) berhasil dicatat pukul {$now->format('H:i')} WIB{$lokasiMsg}!");
            }

            return back()->with('success', "Absen pulang berhasil dicatat pukul {$now->format('H:i')} WIB{$lokasiMsg}!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mencatat absen pulang: ' . $e->getMessage());
        }
    }

    /**
     * Khusus Satpam: Lapor mandiri bahwa hari ini adalah jadwal libur / lepas dinas.
     */
    public function absenLibur(Request $request)
    {
        try {
            $pegawai = Auth::user();

            if (!$pegawai->isSatpam()) {
                return back()->with('error', 'Fitur lapor hari libur ini khusus untuk petugas Satpam / Security.');
            }

            $now = Carbon::now();
            $today = $now->toDateString();

            if ($pegawai->hasActiveOvernightShift($now)) {
                return back()->with('error', 'Anda masih memiliki sesi jaga malam kemarin yang belum absen pulang. Harap selesaikan absen pulang terlebih dahulu.');
            }

            $todayAbsensi = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($todayAbsensi && $todayAbsensi->jam_masuk) {
                return back()->with('error', 'Anda sudah melakukan absen masuk hari ini. Tidak dapat mengubah status menjadi Libur.');
            }

            if (!$todayAbsensi) {
                Absensi::create([
                    'pegawai_id'      => $pegawai->id,
                    'tanggal'         => $today,
                    'jam_masuk'       => null,
                    'jam_pulang'      => null,
                    'keterangan'      => 'Libur (Jadwal)',
                    'status_presensi' => 'libur',
                ]);
            } else {
                $todayAbsensi->update([
                    'keterangan'      => 'Libur (Jadwal)',
                    'status_presensi' => 'libur',
                ]);
            }

            JadwalShift::updateOrCreate(
                [
                    'pegawai_id' => $pegawai->id,
                    'tanggal'    => $today,
                ],
                [
                    'tipe_shift' => JadwalShift::TIPE_LIBUR,
                    'jam_masuk'  => null,
                    'jam_pulang' => null,
                    'status'     => 'confirmed',
                    'catatan'    => 'Konfirmasi libur mandiri oleh Satpam',
                ]
            );

            return back()->with('success', 'Status hari ini berhasil dicatat sebagai LIBUR (Off Duty). Selamat beristirahat!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mencatat status libur: ' . $e->getMessage());
        }
    }

    /**
     * Khusus Satpam: Batalkan status libur hari ini jika mendadak ada tugas/piket ganti.
     */
    public function batalLibur(Request $request)
    {
        try {
            $pegawai = Auth::user();

            if (!$pegawai->isSatpam()) {
                return back()->with('error', 'Akses ditolak.');
            }

            $now = Carbon::now();
            $today = $now->toDateString();

            $todayAbsensi = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->first();

            if ($todayAbsensi && empty($todayAbsensi->jam_masuk)) {
                $todayAbsensi->delete();
            }

            $jadwal = JadwalShift::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $today)
                ->where('tipe_shift', JadwalShift::TIPE_LIBUR)
                ->first();

            if ($jadwal) {
                $jadwal->delete();
            }

            return back()->with('success', 'Status libur berhasil dibatalkan. Anda sekarang dapat mencatat absen masuk.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membatalkan status libur: ' . $e->getMessage());
        }
    }
}
