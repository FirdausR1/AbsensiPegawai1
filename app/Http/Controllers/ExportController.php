<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Services\AbsensiExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function __construct(protected AbsensiExportService $exportService) {}

    /**
     * Pegawai export absensi milik sendiri.
     */
    public function exportSendiri(Request $request, string $bulan = null)
    {
        $pegawai = Auth::user();
        $bulan   = $bulan ?? Carbon::now()->format('Y-m');

        return $this->downloadExcel($pegawai, $bulan);
    }

    /**
     * Admin export absensi pegawai tertentu.
     */
    public function exportPegawai(Request $request, Pegawai $pegawai, string $bulan = null)
    {
        $bulan = $bulan ?? Carbon::now()->format('Y-m');
        return $this->downloadExcel($pegawai, $bulan);
    }

    /**
     * Admin export semua pegawai dalam 1 file (sheet terpisah per pegawai).
     */
    public function exportSemua(Request $request, string $bulan = null)
    {
        $currentUser = Auth::user();
        $bulan    = $bulan ?? Carbon::now()->format('Y-m');
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

        $pegawais = $query->orderBy('nama')->get();

        if ($pegawais->isEmpty()) {
            return back()->with('error', 'Tidak ada pegawai yang ditemukan.');
        }

        // Generate file untuk setiap pegawai, lalu zip
        $zipFilename = 'absensi_semua_' . $bulan . '.zip';
        $zipPath     = storage_path('app/export_temp/' . $zipFilename);

        if (!is_dir(storage_path('app/export_temp'))) {
            mkdir(storage_path('app/export_temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        $zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach ($pegawais as $pegawai) {
            try {
                $filePath = $this->exportService->exportPegawai($pegawai, $bulan);
                $zip->addFile($filePath, basename($filePath));
            } catch (\Throwable $e) {
                // Skip pegawai yang gagal
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFilename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────────────────

    protected function downloadExcel(Pegawai $pegawai, string $bulan): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $filePath = $this->exportService->exportPegawai($pegawai, $bulan);
            $filename = 'Absensi_' . $pegawai->sheetTabName() . '_' . $bulan . '.xlsx';

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            abort(500, 'Gagal membuat file Excel: ' . $e->getMessage());
        }
    }
}
