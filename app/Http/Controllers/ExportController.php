<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Cuti;
use App\Services\AbsensiExportService;
use App\Services\CutiExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function __construct(
        protected AbsensiExportService $exportService,
        protected CutiExportService $cutiExportService
    ) {}

    /**
     * Pegawai export absensi milik sendiri.
     */
    public function exportSendiri(Request $request, string $bulan = null)
    {
        $pegawai = Auth::user();
        $bulan   = $bulan ?? Carbon::now()->format('Y-m');
        $includeLocation = $request->boolean('include_location');

        return $this->downloadExcel($pegawai, $bulan, $includeLocation);
    }

    /**
     * Admin export absensi pegawai tertentu.
     */
    public function exportPegawai(Request $request, Pegawai $pegawai, string $bulan = null)
    {
        $bulan = $bulan ?? Carbon::now()->format('Y-m');
        $includeLocation = $request->boolean('include_location');

        return $this->downloadExcel($pegawai, $bulan, $includeLocation);
    }

    /**
     * Admin export semua pegawai dalam 1 file (sheet terpisah per pegawai).
     */
    public function exportSemua(Request $request, string $bulan = null)
    {
        $currentUser = Auth::user();
        $bulan    = $bulan ?? Carbon::now()->format('Y-m');
        $includeLocation = $request->boolean('include_location');
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $query = Pegawai::where('is_admin', false)->where('role', '!=', 'super_admin');
        if ($currentUser && $currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;
            $query->where(function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }

        if ($site = $request->input('site')) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->orderBy('nama')->get();

        if ($pegawais->isEmpty()) {
            return back()->with('error', 'Tidak ada pegawai yang ditemukan.');
        }

        // Generate 1 file Excel Universal dengan Sheet per Pegawai
        $filename = 'Rekap_Absensi_' . ($site ? \Illuminate\Support\Str::slug($site) : 'Semua_Pegawai') . '_' . $bulan . '.xlsx';
        $filePath = $this->exportService->exportSemuaPegawai($pegawais, $bulan, $includeLocation);

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
        ]);
    }

    // ─────────────────────────────────────────────────────────

    protected function downloadExcel(Pegawai $pegawai, string $bulan, bool $includeLocation = false): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $filePath = $this->exportService->exportPegawai($pegawai, $bulan, $includeLocation);
            $filename = 'Absensi_' . $pegawai->sheetTabName() . '_' . $bulan . '.xlsx';

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/octet-stream',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
                'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
            ]);
        } catch (\Throwable $e) {
            abort(500, 'Gagal membuat file Excel: ' . $e->getMessage());
        }
    }

    /**
     * Download Cuti milik Pegawai sendiri
     */
    public function exportCutiSendiri(Request $request)
    {
        $pegawai = Auth::user();
        $cutis = Cuti::with('approver')
            ->where('pegawai_id', $pegawai->id)
            ->latest('tanggal_mulai')
            ->get();

        if ($cutis->isEmpty()) {
            return back()->with('error', 'Belum ada data pengajuan cuti/izin untuk diunduh.');
        }

        $filePath = $this->cutiExportService->export($cutis, $pegawai->nama);
        $filename = 'Data_Cuti_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $pegawai->nama) . '.xlsx';

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download Rekap Cuti Admin (seluruh atau dengan filter)
     */
    public function exportCutiAdmin(Request $request)
    {
        $currentUser = Auth::user();
        $query = Cuti::with(['pegawai.divisi', 'approver'])->latest('tanggal_mulai');

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

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->input('search')) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($bulan = $request->input('bulan')) {
            $carbonMonth = Carbon::createFromFormat('Y-m', $bulan);
            $query->where(function ($q) use ($carbonMonth) {
                $q->whereBetween('tanggal_mulai', [$carbonMonth->copy()->startOfMonth()->toDateString(), $carbonMonth->copy()->endOfMonth()->toDateString()])
                  ->orWhereBetween('tanggal_selesai', [$carbonMonth->copy()->startOfMonth()->toDateString(), $carbonMonth->copy()->endOfMonth()->toDateString()]);
            });
        }

        $cutis = $query->get();

        if ($cutis->isEmpty()) {
            return back()->with('error', 'Tidak ada data cuti/izin yang sesuai filter untuk diunduh.');
        }

        $suffix = $request->input('bulan') ? 'Bulan ' . $request->input('bulan') : 'Semua';
        $filePath = $this->cutiExportService->export($cutis, $suffix);
        $filename = 'Rekap_Cuti_Izin_' . date('Ymd') . '.xlsx';

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
        ])->deleteFileAfterSend(true);
    }
}
