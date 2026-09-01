<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\TugasPeriodik;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminTugasPeriodikController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $query = TugasPeriodik::with('pegawai.divisi')
            ->whereBetween('tanggal', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->latest('waktu_upload');

        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;
            $query->whereHas('pegawai', function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }

        // Filter per Kantor Klien / Site Placement
        if ($site = $request->input('site')) {
            $query->whereHas('pegawai', function ($q) use ($site) {
                $q->where('area_kerja', 'like', "%{$site}%");
            });
        }

        if ($search = $request->input('search')) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        } elseif ($pegawaiId = $request->input('pegawai_id')) {
            $query->where('pegawai_id', $pegawaiId);
        }

        if ($tipe = $request->input('tipe_tugas')) {
            $query->where('tipe_tugas', $tipe);
        }

        $tugasList = $query->paginate(20)->withQueryString();
        $pegawais = Pegawai::orderBy('nama')->get();
        $clientSites = \App\Models\KantorKlien::orderBy('nama_kantor')->pluck('nama_kantor');
        $sites = $clientSites->count() > 0 ? $clientSites : Pegawai::whereNotNull('area_kerja')->where('area_kerja', '!=', '')->distinct()->pluck('area_kerja');

        return view('admin.tugas-periodik.index', compact('tugasList', 'pegawais', 'sites', 'bulan', 'carbonMonth', 'currentUser'));
    }

    public function rateTask(Request $request, TugasPeriodik $tugasPeriodik)
    {
        $request->validate([
            'nilai'               => ['required', 'string'],
            'feedback_supervisor' => ['nullable', 'string', 'max:500'],
        ]);

        $tugasPeriodik->update([
            'nilai'               => $request->nilai,
            'feedback_supervisor' => $request->feedback_supervisor,
            'rated_by'            => auth()->id(),
            'rated_at'            => now(),
        ]);

        return back()->with('success', "Penilaian & reaksi untuk tugas {$tugasPeriodik->nama_tugas} ({$tugasPeriodik->pegawai->nama}) berhasil disimpan!");
    }
}
