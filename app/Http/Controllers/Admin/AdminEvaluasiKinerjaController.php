<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\EvaluasiKinerja;
use App\Models\KantorKlien;
use App\Models\Pegawai;
use App\Models\TugasPeriodik;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminEvaluasiKinerjaController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $site = $request->input('site');
        $pegawaiId = $request->input('pegawai_id');

        $query = EvaluasiKinerja::with(['pegawai.divisi', 'evaluator'])
            ->orderBy('id', 'desc');

        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $query->whereHas('pegawai', function ($q) use ($currentUser) {
                    $q->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
                });
            } elseif ($currentUser->divisi_id) {
                $query->whereHas('pegawai', function ($q) use ($currentUser) {
                    $q->where('divisi_id', $currentUser->divisi_id);
                });
            }
        }

        if ($site && $site !== 'all') {
            $query->whereHas('pegawai', function ($q) use ($site) {
                $q->where('area_kerja', 'like', "%{$site}%");
            });
        }

        if ($pegawaiId) {
            $query->where('pegawai_id', $pegawaiId);
        }

        $evaluasis = $query->paginate(15)->withQueryString();

        $pegawaisQuery = Pegawai::where('role', '!=', 'super_admin')->orderBy('nama');
        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $pegawaisQuery->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            } elseif ($currentUser->divisi_id) {
                $pegawaisQuery->where('divisi_id', $currentUser->divisi_id);
            }
        }
        $pegawais = $pegawaisQuery->get();

        $kantorKliens = KantorKlien::orderBy('nama_kantor')->get();

        return view('admin.evaluasi.index', compact('evaluasis', 'pegawais', 'kantorKliens', 'site', 'pegawaiId'));
    }

    public function create(Request $request)
    {
        $currentUser = auth()->user();
        $pegawaisQuery = Pegawai::where('role', '!=', 'super_admin')->orderBy('nama');
        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $pegawaisQuery->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            } elseif ($currentUser->divisi_id) {
                $pegawaisQuery->where('divisi_id', $currentUser->divisi_id);
            }
        }
        $pegawais = $pegawaisQuery->get();

        $selectedPegawai = null;
        $rekapAbsensi = null;
        $rekapTugas = null;

        if ($request->filled('pegawai_id') && $request->filled('tanggal_mulai') && $request->filled('tanggal_selesai')) {
            $selectedPegawai = Pegawai::with('divisi')->find($request->pegawai_id);
            $mulai = Carbon::parse($request->tanggal_mulai);
            $selesai = Carbon::parse($request->tanggal_selesai);

            if ($selectedPegawai) {
                $absensis = Absensi::where('pegawai_id', $selectedPegawai->id)
                    ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
                    ->get();

                $totalHadir = $absensis->whereIn('status', ['Hadir', 'terlambat'])->count();
                $totalTerlambat = $absensis->where('status', 'terlambat')->count();
                $totalMenitTerlambat = $absensis->sum('terlambat_menit');

                $totalCutiIzin = Cuti::where('pegawai_id', $selectedPegawai->id)
                    ->where('status', 'approved')
                    ->where(function ($q) use ($mulai, $selesai) {
                        $q->whereBetween('tanggal_mulai', [$mulai->toDateString(), $selesai->toDateString()])
                          ->orWhereBetween('tanggal_selesai', [$mulai->toDateString(), $selesai->toDateString()]);
                    })->count();

                $rekapAbsensi = [
                    'total_hadir'           => $totalHadir,
                    'total_terlambat'       => $totalTerlambat,
                    'total_menit_terlambat' => $totalMenitTerlambat,
                    'total_cuti_izin'       => $totalCutiIzin,
                    'total_alpha'           => max(0, $mulai->diffInDaysFiltered(fn(Carbon $date) => !$date->isWeekend(), $selesai) - ($totalHadir + $totalCutiIzin)),
                ];

                $tugasList = TugasPeriodik::where('pegawai_id', $selectedPegawai->id)
                    ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
                    ->get();

                $rekapTugas = [
                    'total_tugas' => $tugasList->count(),
                    'bagus'       => $tugasList->whereIn('nilai', ['Sangat Bagus 🌟', 'Bagus 👍'])->count(),
                ];
            }
        }

        return view('admin.evaluasi.create', compact('pegawais', 'selectedPegawai', 'rekapAbsensi', 'rekapTugas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'pegawai_id'          => ['required', 'exists:pegawais,id'],
            'periode_tipe'        => ['required', 'string'],
            'tanggal_mulai'       => ['required', 'date'],
            'tanggal_selesai'     => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'skor_absensi'        => ['required', 'numeric', 'min:0', 'max:100'],
            'skor_tugas'          => ['required', 'numeric', 'min:0', 'max:100'],
            'skor_perilaku'       => ['required', 'numeric', 'min:0', 'max:100'],
            'rekomendasi'         => ['required', 'string'],
            'catatan_evaluasi'    => ['nullable', 'string'],
        ]);

        $pegawai = Pegawai::findOrFail($request->pegawai_id);
        $mulai = Carbon::parse($request->tanggal_mulai);
        $selesai = Carbon::parse($request->tanggal_selesai);

        // Hitung Otomatis Rekap Absensi
        $absensis = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->get();

        $totalHadir = $absensis->whereIn('status', ['Hadir', 'terlambat'])->count();
        $totalTerlambat = $absensis->where('status', 'terlambat')->count();
        $totalMenitTerlambat = $absensis->sum('terlambat_menit');

        $totalCutiIzin = Cuti::where('pegawai_id', $pegawai->id)
            ->where('status', 'approved')
            ->where(function ($q) use ($mulai, $selesai) {
                $q->whereBetween('tanggal_mulai', [$mulai->toDateString(), $selesai->toDateString()])
                  ->orWhereBetween('tanggal_selesai', [$mulai->toDateString(), $selesai->toDateString()]);
            })->count();

        // Formula skor akhir: 40% Absen + 40% Tugas + 20% Perilaku
        $skorAbsensi  = (float) $request->skor_absensi;
        $skorTugas    = (float) $request->skor_tugas;
        $skorPerilaku = (float) $request->skor_perilaku;

        $skorAkhir = round(($skorAbsensi * 0.40) + ($skorTugas * 0.40) + ($skorPerilaku * 0.20), 2);

        $kategori = match (true) {
            $skorAkhir >= 90 => 'Sangat Baik (A)',
            $skorAkhir >= 75 => 'Baik (B)',
            $skorAkhir >= 60 => 'Cukup (C)',
            default          => 'Kurang (D)',
        };

        $evaluasi = EvaluasiKinerja::create([
            'pegawai_id'            => $pegawai->id,
            'periode_tipe'          => $request->periode_tipe,
            'tanggal_mulai'         => $mulai->toDateString(),
            'tanggal_selesai'       => $selesai->toDateString(),
            'total_hadir'           => $totalHadir,
            'total_terlambat'       => $totalTerlambat,
            'total_menit_terlambat' => $totalMenitTerlambat,
            'total_cuti_izin'       => $totalCutiIzin,
            'total_alpha'           => 0,
            'skor_absensi'          => $skorAbsensi,
            'skor_tugas'            => $skorTugas,
            'skor_perilaku'         => $skorPerilaku,
            'skor_akhir'            => $skorAkhir,
            'kategori_penilaian'    => $kategori,
            'rekomendasi'           => $request->rekomendasi,
            'catatan_evaluasi'      => $request->catatan_evaluasi,
            'evaluator_id'          => auth()->id(),
        ]);

        return redirect()->route('admin.evaluasi-kinerja.index')
            ->with('success', "Evaluasi Kinerja untuk {$pegawai->nama} berhasil disimpan dengan Skor Akhir {$skorAkhir} ({$kategori}).");
    }

    public function cetak(EvaluasiKinerja $evaluasiKinerja)
    {
        $evaluasi = $evaluasiKinerja->load(['pegawai.divisi', 'evaluator']);
        $pegawai = $evaluasi->pegawai;

        $mulai = $evaluasi->tanggal_mulai;
        $selesai = $evaluasi->tanggal_selesai;

        // Lampiran 1: Rekap Absensi
        $absensis = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->orderBy('tanggal', 'asc')
            ->get();

        // Lampiran 2: Rekap Tugas Periodik Operasional
        $tugasList = TugasPeriodik::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [$mulai->toDateString(), $selesai->toDateString()])
            ->orderBy('tanggal', 'desc')
            ->get();

        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        return view('admin.evaluasi.cetak', compact('evaluasi', 'pegawai', 'absensis', 'tugasList', 'kepala'));
    }

    public function destroy(EvaluasiKinerja $evaluasiKinerja)
    {
        $name = $evaluasiKinerja->pegawai?->nama;
        $evaluasiKinerja->delete();

        return back()->with('success', "Data evaluasi kinerja {$name} berhasil dihapus.");
    }
}
