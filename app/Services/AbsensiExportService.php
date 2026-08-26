<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AbsensiExportService
{
    // Baris data awal (tanggal 1) dan kolom sesuai template
    const FIRST_DATA_ROW = 12;

    public function exportPegawai(Pegawai $pegawai, string $bulan): string
    {
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $absensis = Absensi::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->get()
            ->keyBy(fn($a) => Carbon::parse($a->tanggal)->day);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($pegawai->sheetTabName());

        $this->buildSheet($sheet, $pegawai, $carbonMonth, $absensis);

        $filename = 'absensi_' . $pegawai->sheetTabName() . '_' . $bulan . '.xlsx';
        $tempPath = storage_path('app/export_temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    protected function buildSheet($sheet, Pegawai $pegawai, Carbon $carbonMonth, $absensis): void
    {
        $daysInMonth  = $carbonMonth->daysInMonth;
        $logoPath     = file_exists(public_path('images/Picture1.png'))
            ? public_path('images/Picture1.png')
            : (file_exists(public_path('images/logo.png')) ? public_path('images/logo.png') : public_path('images/logo.jpg'));
        $firstDataRow = 12;   // Baris 12 = tanggal 1 (sesuai template asli)

        // ── Lebar Kolom ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(14);  // Logo / NO
        $sheet->getColumnDimension('B')->setWidth(18);  // TANGGAL
        $sheet->getColumnDimension('C')->setWidth(13);  // JAM MASUK
        $sheet->getColumnDimension('D')->setWidth(15);  // TTD MASUK
        $sheet->getColumnDimension('E')->setWidth(13);  // JAM PULANG
        $sheet->getColumnDimension('F')->setWidth(15);  // TTD PULANG
        $sheet->getColumnDimension('G')->setWidth(25);  // KETERANGAN

        // ── BARIS 1-3: Header Utama ─────────────────────────────────────────
        // Tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // === KIRI: A1:A2 Logo, A3 Nama Perusahaan ===
        $sheet->mergeCells('A1:A2');
        $sheet->setCellValue('A3', 'PT.INTI SARANA WIJAYA');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['rgb' => '1a237e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Sisipkan logo ke A1:A2
        if (file_exists($logoPath)) {
            try {
                $drawing = new Drawing();
                $drawing->setPath($logoPath);
                $drawing->setCoordinates('A1');
                $drawing->setWidth(55);
                $drawing->setHeight(50);
                $drawing->setOffsetX(5);
                $drawing->setOffsetY(2);
                $drawing->setWorksheet($sheet);
            } catch (\Throwable $e) {
                // Ignore if drawing fails
            }
        }

        // === TENGAH: B1:E3 — "DAFTAR HADIR TENAGA KERJA" ===
        $sheet->mergeCells('B1:E3');
        $sheet->setCellValue('B1', 'DAFTAR HADIR TENAGA KERJA');
        $sheet->getStyle('B1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a237e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // === KANAN: F1:G3 — Kotak No. / Revisi / Berlaku ===
        $infoStyle = [
            'font'      => ['size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->setCellValue('F1', 'No.');
        $sheet->setCellValue('G1', ':');
        $sheet->setCellValue('F2', 'Revisi');
        $sheet->setCellValue('G2', ':');
        $sheet->setCellValue('F3', 'Berlaku');
        $sheet->setCellValue('G3', ':');

        foreach (['F1','F2','F3','G1','G2','G3'] as $c) {
            $sheet->getStyle($c)->applyFromArray($infoStyle);
        }

        // Border kotak No./Revisi/Berlaku
        $sheet->getStyle('F1:G3')->applyFromArray([
            'borders' => [
                'outline'     => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                'horizontal'  => ['borderStyle' => Border::BORDER_HAIR,  'color' => ['rgb' => '999999']],
                'vertical'    => ['borderStyle' => Border::BORDER_HAIR,  'color' => ['rgb' => '999999']],
            ],
        ]);

        // Border bawah seluruh header (pemisah)
        $sheet->getStyle('A3:G3')->applyFromArray([
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '000000']]],
        ]);

        // ── BARIS 5-7: Info Pegawai ─────────────────────────────────────────
        $sheet->getRowDimension(4)->setRowHeight(4);
        $sheet->getRowDimension(5)->setRowHeight(16);
        $sheet->getRowDimension(6)->setRowHeight(16);
        $sheet->getRowDimension(7)->setRowHeight(16);
        $sheet->getRowDimension(8)->setRowHeight(4);

        $labelStyle = ['font' => ['bold' => true, 'size' => 10]];
        $valueStyle = ['font' => ['size' => 10]];

        foreach ([5 => 'NAMA', 6 => 'BULAN', 7 => 'AREA KERJA'] as $row => $label) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", ':');
            $sheet->getStyle("A{$row}")->applyFromArray($labelStyle);
            $sheet->getStyle("B{$row}:G{$row}")->applyFromArray($valueStyle);
        }

        $sheet->setCellValue('C5', $pegawai->nama);
        $sheet->setCellValue('C6', $carbonMonth->translatedFormat('F Y'));
        $sheet->setCellValue('C7', $pegawai->area_kerja ?: '-');

        // ── BARIS 9-11: Header Tabel ────────────────────────────────────────
        $sheet->mergeCells('A9:A11');
        $sheet->mergeCells('B9:B11');
        $sheet->mergeCells('C9:C11');
        $sheet->mergeCells('D9:D11');
        $sheet->mergeCells('E9:E11');
        $sheet->mergeCells('F9:F11');
        $sheet->mergeCells('G9:G11');

        $headers = [
            'A9' => 'NO',
            'B9' => 'TANGGAL',
            'C9' => 'JAM MASUK',
            'D9' => 'TANDA TANGAN',
            'E9' => 'JAM PULANG',
            'F9' => 'TANDA TANGAN',
            'G9' => 'KETERANGAN',
        ];

        $headerStyle = [
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1a237e']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
            $sheet->getStyle($cell)->applyFromArray($headerStyle);
        }

        $sheet->getRowDimension(9)->setRowHeight(20);
        $sheet->getRowDimension(10)->setRowHeight(20);
        $sheet->getRowDimension(11)->setRowHeight(20);

        // ── BARIS 12+: Data Harian (Row 12 = Tanggal 1) ────────────────────

        $signatureRowHeight = 35;
        $normalRowHeight    = 16;

        $holidayService = app(\App\Services\HolidayService::class);

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $row  = $firstDataRow + ($day - 1);
            $date = $carbonMonth->copy()->day($day);
            $absensi = $absensis->get($day);

            $isPastOrToday = $date->lte(Carbon::today());
            $isHoliday = $holidayService->isHoliday($date);
            $holidayName = $holidayService->getHolidayReason($date);

            // Warna baris dan Keterangan
            $rowBg = null;
            $keterangan = '';
            $isAlpa = false;

            if ($absensi && !empty($absensi->keterangan)) {
                $keterangan = $absensi->keterangan;
            } elseif ($isHoliday) {
                $keterangan = $holidayName ?: 'Libur Nasional';
                $rowBg = 'ffebee'; // merah muda lembut
            } elseif ($date->isWeekend()) {
                $keterangan = 'Libur';
                $rowBg = 'fff8e1'; // kuning muda
            } elseif ($absensi && $absensi->jam_masuk) {
                $menitTerlambat = $absensi->getMenitTerlambat($pegawai);
                if ($menitTerlambat > 0) {
                    $keterangan = "Terlambat {$menitTerlambat} Menit";
                } else {
                    $keterangan = "Hadir";
                }
            } elseif ($isPastOrToday) {
                // Hari kerja yang terlewat dan tidak absen => ALPA
                $keterangan = 'ALPA';
                $rowBg = 'fce8e6';
                $isAlpa = true;
            }

            // Isi data
            $sheet->setCellValue("A{$row}", $day);
            $sheet->setCellValue("B{$row}", $date->translatedFormat('d F Y'));
            $sheet->setCellValue("C{$row}", $absensi?->jam_masuk ? substr($absensi->jam_masuk, 0, 5) : '');
            $sheet->setCellValue("E{$row}", $absensi?->jam_pulang ? substr($absensi->jam_pulang, 0, 5) : '');
            $sheet->setCellValue("G{$row}", $keterangan);

            // Style baris data
            $dataStyle = [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                'font' => ['size' => 9],
            ];
            if ($rowBg) {
                $dataStyle['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]];
            }
            $sheet->getStyle("A{$row}:G{$row}")->applyFromArray($dataStyle);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

            if ($isAlpa) {
                $sheet->getStyle("G{$row}")->getFont()->setBold(true)->getColor()->setRGB('c5221f');
            }

            // Tanda tangan
            $hasSignature = $absensi && $pegawai->hasSignature();
            $signaturePath = $hasSignature ? storage_path('app/public/' . $pegawai->signature_path) : null;

            if ($signaturePath && file_exists($signaturePath) && $absensi->jam_masuk) {
                $sheet->getRowDimension($row)->setRowHeight($signatureRowHeight);
                $this->insertSignatureImage($sheet, $signaturePath, "D{$row}", $row);
            }
            if ($signaturePath && file_exists($signaturePath) && $absensi?->jam_pulang) {
                $sheet->getRowDimension($row)->setRowHeight($signatureRowHeight);
                $this->insertSignatureImage($sheet, $signaturePath, "F{$row}", $row);
            }
            if (!isset($signaturePath) || !($absensi?->jam_masuk || $absensi?->jam_pulang)) {
                $sheet->getRowDimension($row)->setRowHeight($normalRowHeight);
            }
        }

        // ── Footer Catatan ──────────────────────────────────────────────────
        $footerRow = $firstDataRow + $daysInMonth + 1;
        $sheet->mergeCells("A{$footerRow}:G{$footerRow}");
        $sheet->setCellValue("A{$footerRow}", 'CATATAN : DAFTAR HADIR DIISI SETIAP HARI SESUAI KEHADIRAN. BUKAN DIRAPEL ATAU DIISI AKHIR BULAN.');
        $sheet->getStyle("A{$footerRow}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => 'c62828']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Tanda Tangan Pengguna Jasa & Personil ───────────────────────────
        $ttdRow = $footerRow + 3;
        $sheet->mergeCells("A{$ttdRow}:C{$ttdRow}");
        $sheet->mergeCells("E{$ttdRow}:G{$ttdRow}");
        $sheet->setCellValue("A{$ttdRow}", 'PENGGUNA JASA / PEJABAT YG TERKAIT');
        $sheet->setCellValue("E{$ttdRow}", 'Personil');
        foreach (["A{$ttdRow}", "E{$ttdRow}"] as $cell) {
            $sheet->getStyle($cell)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }

        // ── Freeze header ───────────────────────────────────────────────────
        $sheet->freezePane("A{$firstDataRow}");
    }

    protected function insertSignatureImage($sheet, string $imagePath, string $coordinate, int $row): void
    {
        try {
            $drawing = new Drawing();
            $drawing->setPath($imagePath);
            $drawing->setCoordinates($coordinate);
            $drawing->setWidth(60);
            $drawing->setHeight(28);
            $drawing->setOffsetX(3);
            $drawing->setOffsetY(3);
            $drawing->setWorksheet($sheet);
        } catch (\Throwable $e) {
            // Jika gambar tidak bisa dimasukkan, biarkan sel kosong
        }
    }
}
