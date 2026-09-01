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

    public function exportExcel(Request $request)
    {
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $query = TugasPeriodik::with('pegawai.divisi')
            ->whereBetween('tanggal', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->latest('waktu_upload');

        $currentUser = auth()->user();
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

        $tugasList = $query->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tugas Periodik');

        // Headers
        $headers = ['No', 'Nama Pegawai', 'Area Kerja', 'Tanggal', 'Tipe Tugas', 'Nama Tugas', 'Catatan', 'Nilai', 'Feedback', 'Foto'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }

        $row = 2;
        foreach ($tugasList as $index => $tugas) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $tugas->pegawai->nama);
            $sheet->setCellValue('C' . $row, $tugas->pegawai->area_kerja ?? '-');
            $sheet->setCellValue('D' . $row, $tugas->tanggal->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, $tugas->tipe_tugas);
            $sheet->setCellValue('F' . $row, $tugas->nama_tugas);
            $sheet->setCellValue('G' . $row, $tugas->catatan);
            $sheet->setCellValue('H' . $row, $tugas->nilai);
            $sheet->setCellValue('I' . $row, $tugas->feedback_supervisor);

            // Set row height for image
            $sheet->getRowDimension($row)->setRowHeight(80);

            if ($tugas->foto_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($tugas->foto_path)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Foto');
                $drawing->setDescription('Foto Tugas');
                $drawing->setPath(storage_path('app/public/' . $tugas->foto_path));
                $drawing->setHeight(100);
                $drawing->setCoordinates('J' . $row);
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setWorksheet($sheet);
            }

            $row++;
        }

        // Auto-size columns except J (Foto)
        foreach (range('A', 'I') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->getColumnDimension('J')->setWidth(25); // Set fixed width for image column

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Laporan_Tugas_Periodik_' . $carbonMonth->format('F_Y') . '.xlsx';
        
        // Save to output buffer and return
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();

        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }
}
