<?php

namespace App\Services;

use App\Models\Cuti;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CutiExportService
{
    /**
     * Export data cuti ke file Excel (.xlsx)
     *
     * @param Collection $cutis
     * @param string|null $titleSuffix
     * @return string Path ke file Excel temporary
     */
    public function export(Collection $cutis, ?string $titleSuffix = null): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Cuti & Izin');

        // ── Lebar Kolom ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(6);   // NO
        $sheet->getColumnDimension('B')->setWidth(25);  // NAMA PEGAWAI
        $sheet->getColumnDimension('C')->setWidth(22);  // DIVISI / AREA
        $sheet->getColumnDimension('D')->setWidth(22);  // TIPE CUTI/IZIN
        $sheet->getColumnDimension('E')->setWidth(15);  // TGL MULAI
        $sheet->getColumnDimension('F')->setWidth(15);  // TGL SELESAI
        $sheet->getColumnDimension('G')->setWidth(12);  // DURASI
        $sheet->getColumnDimension('H')->setWidth(30);  // ALASAN
        $sheet->getColumnDimension('I')->setWidth(15);  // STATUS
        $sheet->getColumnDimension('J')->setWidth(20);  // APPROVED BY
        $sheet->getColumnDimension('K')->setWidth(18);  // APPROVED AT
        $sheet->getColumnDimension('L')->setWidth(25);  // CATATAN ADMIN

        // ── Header Perusahaan & Title ──────────────────────────────────────
        $logoPath = file_exists(public_path('images/Picture1.png'))
            ? public_path('images/Picture1.png')
            : (file_exists(public_path('images/logo.png')) ? public_path('images/logo.png') : public_path('images/logo.jpg'));

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(18);

        $sheet->mergeCells('A1:A2');
        $sheet->setCellValue('A3', 'PT.INTI SARANA WIJAYA');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['rgb' => '1a237e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        if (file_exists($logoPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setPath($logoPath);
                $drawing->setCoordinates('A1');
                $drawing->setWidth(50);
                $drawing->setHeight(45);
                $drawing->setOffsetX(3);
                $drawing->setOffsetY(2);
                $drawing->setWorksheet($sheet);
            } catch (\Throwable $e) {
                // Ignore failure
            }
        }

        $title = 'REKAP DATA CUTI & IZIN PEGAWAI' . ($titleSuffix ? " - {$titleSuffix}" : '');
        $sheet->mergeCells('B1:L3');
        $sheet->setCellValue('B1', $title);
        $sheet->getStyle('B1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1a237e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle('A3:L3')->applyFromArray([
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1a237e']]],
        ]);

        // ── Header Tabel (Baris 5) ──────────────────────────────────────────
        $headerRow = 5;
        $sheet->getRowDimension($headerRow)->setRowHeight(25);

        $headers = [
            'A' => 'NO',
            'B' => 'NAMA PEGAWAI',
            'C' => 'DIVISI / AREA KERJA',
            'D' => 'TIPE CUTI / IZIN',
            'E' => 'TANGGAL MULAI',
            'F' => 'TANGGAL SELESAI',
            'G' => 'DURASI',
            'H' => 'ALASAN / KETERANGAN',
            'I' => 'STATUS',
            'J' => 'DISETUJUI OLEH',
            'K' => 'TGL DISETUJUI',
            'L' => 'CATATAN ADMIN',
        ];

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a237e']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];

        foreach ($headers as $col => $text) {
            $cell = "{$col}{$headerRow}";
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        // ── Data Rows ──────────────────────────────────────────────────────
        $row = 6;
        $no = 1;

        foreach ($cutis as $cuti) {
            $pegawai = $cuti->pegawai;
            $divisiName = $pegawai?->area_kerja ?: ($pegawai?->divisi?->nama ?: '-');
            $approverName = $cuti->approver?->nama ?: '-';
            $approvedAt = $cuti->approved_at ? Carbon::parse($cuti->approved_at)->format('d/m/Y H:i') : '-';

            $sheet->getRowDimension($row)->setRowHeight(20);

            $sheet->setCellValue("A{$row}", $no++);
            $sheet->setCellValue("B{$row}", $pegawai?->nama ?: '-');
            $sheet->setCellValue("C{$row}", $divisiName);
            $sheet->setCellValue("D{$row}", $cuti->tipe_cuti);
            $sheet->setCellValue("E{$row}", Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y'));
            $sheet->setCellValue("F{$row}", Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y'));
            $sheet->setCellValue("G{$row}", "{$cuti->jumlah_hari} Hari");
            $sheet->setCellValue("H{$row}", $cuti->alasan ?: '-');
            $sheet->setCellValue("I{$row}", $cuti->getStatusLabel());
            $sheet->setCellValue("J{$row}", $approverName);
            $sheet->setCellValue("K{$row}", $approvedAt);
            $sheet->setCellValue("L{$row}", $cuti->catatan_admin ?: '-');

            // Style dasar per baris
            $rowStyle = [
                'font'      => ['size' => 9],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
            ];

            // Warna status
            $statusBg = match ($cuti->status) {
                'approved' => 'E6F4EA', // Hijau muda
                'rejected' => 'FCE8E6', // Merah muda
                default    => 'FFF8E1', // Kuning muda
            };

            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray($rowStyle);

            // Alignment khusus
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}:K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Highlight status cell
            $sheet->getStyle("I{$row}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => match ($cuti->status) {
                    'approved' => '137333',
                    'rejected' => 'C5221F',
                    default    => 'B45309',
                }]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $statusBg]],
            ]);

            $row++;
        }

        // Freeze pane pada header tabel
        $sheet->freezePane('A6');

        $filename = 'rekap_cuti_izin_' . date('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/export_temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}
