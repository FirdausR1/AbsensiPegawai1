<?php

namespace App\Http\Controllers;

use App\Models\Cuti;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CutiController extends Controller
{
    public function index()
    {
        $pegawai = Auth::user();
        $cutis = Cuti::where('pegawai_id', $pegawai->id)
            ->latest('tanggal_mulai')
            ->paginate(10);

        return view('cuti.index', compact('pegawai', 'cutis'));
    }

    public function store(Request $request)
    {
        $pegawai = Auth::user();

        $request->validate([
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'tipe_cuti'       => ['required', 'string', 'max:100'],
            'alasan'          => ['required', 'string', 'max:1000'],
        ]);

        $startDate = Carbon::parse($request->tanggal_mulai);
        $endDate   = Carbon::parse($request->tanggal_selesai);

        // Hitung jumlah hari kalender (inklusif)
        $jumlahHari = $startDate->diffInDays($endDate) + 1;

        // Cek apakah ada pengajuan overlapping
        $overlap = Cuti::where('pegawai_id', $pegawai->id)
            ->where('status', '!=', 'rejected')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('tanggal_mulai', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhereBetween('tanggal_selesai', [$startDate->toDateString(), $endDate->toDateString()])
                      ->orWhere(function ($q) use ($startDate, $endDate) {
                          $q->where('tanggal_mulai', '<=', $startDate->toDateString())
                            ->where('tanggal_selesai', '>=', $endDate->toDateString());
                      });
            })
            ->exists();

        if ($overlap) {
            return back()->with('error', 'Anda sudah memiliki pengajuan cuti yang aktif atau pending pada rentang tanggal tersebut.');
        }

        Cuti::create([
            'pegawai_id'      => $pegawai->id,
            'tanggal_mulai'   => $startDate->toDateString(),
            'tanggal_selesai' => $endDate->toDateString(),
            'jumlah_hari'     => (int) $jumlahHari,
            'tipe_cuti'       => $request->tipe_cuti,
            'alasan'          => $request->alasan,
            'status'          => 'pending',
        ]);

        return back()->with('success', 'Pengajuan cuti Anda selama ' . $jumlahHari . ' hari berhasil dikirim dan sedang menunggu persetujuan Admin/HRD.');
    }

    public function cancel(Cuti $cuti)
    {
        $pegawai = Auth::user();

        if ($cuti->pegawai_id !== $pegawai->id) {
            return back()->with('error', 'Akses ditolak.');
        }

        if (!$cuti->isPending()) {
            return back()->with('error', 'Hanya pengajuan cuti yang berstatus Menunggu (Pending) yang dapat dibatalkan.');
        }

        $cuti->delete();

        return back()->with('success', 'Pengajuan cuti berhasil dibatalkan.');
    }
}
