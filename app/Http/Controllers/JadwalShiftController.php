<?php

namespace App\Http\Controllers;

use App\Models\JadwalShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalShiftController extends Controller
{
    /**
     * Satpam lihat jadwal shift pribadinya (kalender bulan ini)
     */
    public function index(Request $request)
    {
        $pegawai = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonBulan = Carbon::parse($bulan . '-01');

        $jadwals = JadwalShift::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [
                $carbonBulan->copy()->startOfMonth()->toDateString(),
                $carbonBulan->copy()->endOfMonth()->toDateString(),
            ])
            ->orderBy('tanggal')
            ->get()
            ->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));

        return view('jadwal-shift.index', compact('pegawai', 'jadwals', 'carbonBulan', 'bulan'));
    }

    /**
     * Pegawai mengajukan permohonan perubahan / tukar shift
     */
    public function requestShift(Request $request)
    {
        $pegawai = Auth::user();

        $request->validate([
            'tanggal'    => ['required', 'date'],
            'tipe_shift' => ['required', 'in:Pagi,Malam,Siang,Libur'],
            'catatan'    => ['nullable', 'string', 'max:500'],
        ]);

        $date = Carbon::parse($request->tanggal);

        $jam = JadwalShift::defaultJam($request->tipe_shift, $pegawai);

        JadwalShift::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'tanggal'    => $date->toDateString(),
            ],
            [
                'tipe_shift' => $request->tipe_shift,
                'jam_masuk'  => $jam['jam_masuk'] ?? null,
                'jam_pulang' => $jam['jam_pulang'] ?? null,
                'status'     => 'requested',
                'catatan'    => $request->catatan ?: 'Pengajuan tukar/perubahan shift oleh pegawai',
            ]
        );

        return back()->with('success', 'Pengajuan perubahan/tukar shift untuk tanggal ' . $date->translatedFormat('d F Y') . ' berhasil dikirim. Menunggu persetujuan (ACC) Admin/Supervisor.');
    }

    /**
     * Pegawai membatalkan pengajuan shift yang masih pending
     */
    public function cancelRequest(JadwalShift $jadwalShift)
    {
        $pegawai = Auth::user();

        if ($jadwalShift->pegawai_id !== $pegawai->id) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($jadwalShift->status !== 'requested') {
            return back()->with('error', 'Hanya pengajuan yang berstatus Menunggu Konfirmasi yang dapat dibatalkan.');
        }

        $jadwalShift->delete();

        return back()->with('success', 'Pengajuan perubahan shift berhasil dibatalkan.');
    }
}
