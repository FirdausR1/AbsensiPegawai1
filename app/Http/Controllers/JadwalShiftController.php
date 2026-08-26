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

}
