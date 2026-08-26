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
     * Satpam request ganti shift (mengubah status jadi 'requested')
     */
    public function requestGanti(Request $request)
    {
        $pegawai = Auth::user();

        $request->validate([
            'tanggal'    => ['required', 'date', 'after_or_equal:today'],
            'tipe_shift' => ['required', 'in:Pagi,Malam,Siang,Libur'],
            'catatan'    => ['required', 'string', 'max:500'],
        ]);

        $tanggal = Carbon::parse($request->tanggal);
        $jam = JadwalShift::defaultJam($request->tipe_shift);

        // Cek apakah sudah ada jadwal
        $existing = JadwalShift::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal', $tanggal->toDateString())
            ->first();

        if ($existing && $existing->status === 'confirmed') {
            // Ada jadwal confirmed, jadikan request ganti
            $existing->update([
                'tipe_shift'  => $request->tipe_shift,
                'jam_masuk'   => $jam['jam_masuk'] ?? null,
                'jam_pulang'  => $jam['jam_pulang'] ?? null,
                'status'      => 'requested',
                'catatan'     => $request->catatan,
            ]);
        } else {
            // Belum ada jadwal, buat baru sebagai request
            JadwalShift::updateOrCreate(
                ['pegawai_id' => $pegawai->id, 'tanggal' => $tanggal->toDateString()],
                [
                    'tipe_shift' => $request->tipe_shift,
                    'jam_masuk'  => $jam['jam_masuk'] ?? null,
                    'jam_pulang' => $jam['jam_pulang'] ?? null,
                    'status'     => 'requested',
                    'catatan'    => $request->catatan,
                    'created_by' => $pegawai->id,
                ]
            );
        }

        return back()->with('success', 'Request ganti shift berhasil dikirim ke Danru/Koordinator untuk disetujui.');
    }
}
