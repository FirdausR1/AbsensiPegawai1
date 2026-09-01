<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\SlipGaji;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SlipGajiExportService
{
    /**
     * Export data slip gaji & payroll pegawai ke Excel
     *
     * @param iterable $pegawais
     * @param string $bulan Periode Y-m
     * @param string|null $siteName
     * @return string Filepath temporary file xlsx
     */
    public function export($pegawais, string $bulan, ?string $siteName = null): string
    {
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $periodeLabel = $carbonMonth->translatedFormat('F Y');
        $siteLabel = $siteName ?: 'Semua Kantor Klien / Site Area';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Payroll ' . substr($carbonMonth->format('M Y'), 0, 20));

        // ── Kolom Lebar ───────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(6);   // NO
        $sheet->getColumnDimension('B')->setWidth(26);  // NAMA PEGAWAI
        $sheet->getColumnDimension('C')->setWidth(20);  // DIVISI / JABATAN
        $sheet->getColumnDimension('D')->setWidth(24);  // KANTOR / SITE AREA
        $sheet->getColumnDimension('E')->setWidth(16);  // GAJI POKOK
        $sheet->getColumnDimension('F')->setWidth(15);  // T. JABATAN
        $sheet->getColumnDimension('G')->setWidth(15);  // T. TRANSPORT
        $sheet->getColumnDimension('H')->setWidth(14);  // BONUS / LEMBUR
        $sheet->getColumnDimension('I')->setWidth(18);  // TOTAL PENERIMAAN
        $sheet->getColumnDimension('J')->setWidth(15);  // BPJS KES (1%)
        $sheet->getColumnDimension('K')->setWidth(16);  // BPJS TK (3%)
        $sheet->getColumnDimension('L')->setWidth(15);  // POT. ABSENSI
        $sheet->getColumnDimension('M')->setWidth(15);  // POT. LAINNYA
        $sheet->getColumnDimension('N')->setWidth(17);  // TOTAL POTONGAN
        $sheet->getColumnDimension('O')->setWidth(20);  // TAKE HOME PAY (THP)
        $sheet->getColumnDimension('P')->setWidth(16);  // NO BPJS KES
        $sheet->getColumnDimension('Q')->setWidth(16);  // NO BPJS TK

        // ── Kop Surat Perusahaan ─────────────────────────────────
        $logoPath = file_exists(public_path('images/Picture1.png'))
            ? public_path('images/Picture1.png')
            : (file_exists(public_path('images/logo.png')) ? public_path('images/logo.png') : public_path('images/logo.jpg'));

        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);

        $sheet->mergeCells('A1:A2');
        $sheet->setCellValue('A3', 'PT. INTI SARANA WIJAYA');
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['rgb' => '1a237e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        if (file_exists($logoPath)) {
            $drawing = new Drawing();
            $drawing->setName('Logo ISW');
            $drawing->setDescription('Logo PT Inti Sarana Wijaya');
            $drawing->setPath($logoPath);
            $drawing->setCoordinates('A1');
            $drawing->setHeight(36);
            $drawing->setOffsetX(6);
            $drawing->setOffsetY(4);
            $drawing->setWorksheet($sheet);
        }

        // Judul Laporan
        $sheet->mergeCells('B1:Q1');
        $sheet->setCellValue('B1', 'REKAPITULASI SLIP GAJI & BPJS PAYROLL KARYAWAN');
        $sheet->getStyle('B1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '000d6b']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->mergeCells('B2:Q2');
        $sheet->setCellValue('B2', 'KANTOR / PENEMPATAN: ' . strtoupper($siteLabel) . ' | PERIODE: ' . strtoupper($periodeLabel));
        $sheet->getStyle('B2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->mergeCells('B3:Q3');
        $sheet->setCellValue('B3', 'Ketentuan BPJS: BPJS Kesehatan 1% (Pekerja), BPJS Ketenagakerjaan 3% JHT+JP (Pekerja)');
        $sheet->getStyle('B3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '64748b']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // ── Header Tabel ──────────────────────────────────────────
        // Baris 5 & 6 untuk Header Grouping
        $sheet->getRowDimension(5)->setRowHeight(24);
        $sheet->getRowDimension(6)->setRowHeight(22);

        $sheet->mergeCells('A5:A6');
        $sheet->setCellValue('A5', "NO");

        $sheet->mergeCells('B5:B6');
        $sheet->setCellValue('B5', "NAMA PEGAWAI");

        $sheet->mergeCells('C5:C6');
        $sheet->setCellValue('C5', "DIVISI / JABATAN");

        $sheet->mergeCells('D5:D6');
        $sheet->setCellValue('D5', "KANTOR / SITE");

        // Group Penerimaan
        $sheet->mergeCells('E5:I5');
        $sheet->setCellValue('E5', "PENERIMAAN (INCOME)");
        $sheet->setCellValue('E6', "GAJI POKOK");
        $sheet->setCellValue('F6', "T. JABATAN");
        $sheet->setCellValue('G6', "T. TRANSPORT");
        $sheet->setCellValue('H6', "BONUS/LEMBUR");
        $sheet->setCellValue('I6', "TOTAL GAJI");

        // Group Potongan
        $sheet->mergeCells('J5:N5');
        $sheet->setCellValue('J5', "POTONGAN (DEDUCTIONS)");
        $sheet->setCellValue('J6', "BPJS KES (1%)");
        $sheet->setCellValue('K6', "BPJS TK (3%)");
        $sheet->setCellValue('L6', "POT. ABSEN");
        $sheet->setCellValue('M6', "POT. LAIN");
        $sheet->setCellValue('N6', "TOTAL POTONGAN");

        // Take Home Pay
        $sheet->mergeCells('O5:O6');
        $sheet->setCellValue('O5', "TAKE HOME PAY\n(GAJI BERSIH)");
        $sheet->getStyle('O5')->getAlignment()->setWrapText(true);

        // Group Keterangan BPJS
        $sheet->mergeCells('P5:Q5');
        $sheet->setCellValue('P5', "NO KEPESERTAAN BPJS");
        $sheet->setCellValue('P6', "NO BPJS KES");
        $sheet->setCellValue('Q6', "NO BPJS TK");

        // Style Header Utama (Baris 5)
        $sheet->getStyle('A5:Q5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'ffffff'], 'size' => 9],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '000d6b'], // Navy ISW
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '334155']],
            ],
        ]);

        // Style Sub Header (Baris 6)
        $sheet->getStyle('A6:Q6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'ffffff'], 'size' => 8.5],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1e293b'], // Slate dark
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '334155']],
            ],
        ]);

        // Beri warna pembeda untuk grup penerimaan vs potongan pada header baris 5
        $sheet->getStyle('E5:I5')->getFill()->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('15803d')); // Green Dark
        $sheet->getStyle('J5:N5')->getFill()->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('b91c1c')); // Red Dark
        $sheet->getStyle('O5:O6')->getFill()->setStartColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0f172a')); // Darkest

        // ── Isi Data Pegawai ──────────────────────────────────────
        $row = 7;
        $no = 1;

        foreach ($pegawais as $p) {
            $sheet->getRowDimension($row)->setRowHeight(20);

            // Cek apakah ada record slip gaji yang sudah di-generate/disimpan
            $slip = SlipGaji::where('pegawai_id', $p->id)->where('bulan', $bulan)->first();

            $gapok = $slip ? (float)$slip->gaji_pokok : (float)$p->gaji_pokok;
            $tunjJab = $slip ? (float)$slip->tunjangan_jabatan : (float)$p->tunjangan_jabatan;
            $tunjTrans = $slip ? (float)$slip->tunjangan_transport : (float)$p->tunjangan_transport;
            $bonus = $slip ? (float)$slip->bonus_overtime : 0;
            $totPendapatan = $gapok + $tunjJab + $tunjTrans + $bonus;

            // Perhitungan BPJS
            if ($slip) {
                $bpjsKes = (float)$slip->potongan_bpjs_kesehatan;
                $bpjsTk = (float)$slip->potongan_bpjs_tk;
                $potAbsen = (float)$slip->potongan_absensi;
                $potLain = (float)$slip->potongan_lainnya;
            } else {
                $bpjsKes = $p->status_bpjs_kesehatan ? ($gapok * 0.01) : 0;
                $bpjsTk = $p->status_bpjs_ketenagakerjaan ? ($gapok * 0.03) : 0;
                $potAbsen = 0;
                $potLain = 0;
            }
            $totPotongan = $bpjsKes + $bpjsTk + $potAbsen + $potLain;
            $thp = $totPendapatan - $totPotongan;

            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $p->nama);
            $sheet->setCellValue('C' . $row, $p->divisi?->nama ?: 'Staff Operasional');
            $sheet->setCellValue('D' . $row, $p->area_kerja ?: 'Head Office PT ISW');

            // Nilai Numerik
            $sheet->setCellValue('E' . $row, $gapok);
            $sheet->setCellValue('F' . $row, $tunjJab);
            $sheet->setCellValue('G' . $row, $tunjTrans);
            $sheet->setCellValue('H' . $row, $bonus);
            $sheet->setCellValue('I' . $row, "=SUM(E{$row}:H{$row})");

            $sheet->setCellValue('J' . $row, $bpjsKes);
            $sheet->setCellValue('K' . $row, $bpjsTk);
            $sheet->setCellValue('L' . $row, $potAbsen);
            $sheet->setCellValue('M' . $row, $potLain);
            $sheet->setCellValue('N' . $row, "=SUM(J{$row}:M{$row})");

            $sheet->setCellValue('O' . $row, "=I{$row}-N{$row}");

            $sheet->setCellValueExplicit('P' . $row, $p->no_bpjs_kesehatan ?: ($p->status_bpjs_kesehatan ? 'Terdaftar (1%)' : '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueExplicit('Q' . $row, $p->no_bpjs_ketenagakerjaan ?: ($p->status_bpjs_ketenagakerjaan ? 'Terdaftar (3%)' : '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

            // Styling baris bergantian (Zebra)
            if ($no % 2 == 0) {
                $sheet->getStyle("A{$row}:Q{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('f8fafc');
            }

            $row++;
        }

        $lastDataRow = $row - 1;

        // ── Baris Ringkasan Total ─────────────────────────────────
        $sheet->getRowDimension($row)->setRowHeight(24);
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue("A{$row}", "TOTAL KESELURUHAN PAYROLL");

        $sheet->setCellValue("E{$row}", "=SUM(E7:E{$lastDataRow})");
        $sheet->setCellValue("F{$row}", "=SUM(F7:F{$lastDataRow})");
        $sheet->setCellValue("G{$row}", "=SUM(G7:G{$lastDataRow})");
        $sheet->setCellValue("H{$row}", "=SUM(H7:H{$lastDataRow})");
        $sheet->setCellValue("I{$row}", "=SUM(I7:I{$lastDataRow})");
        $sheet->setCellValue("J{$row}", "=SUM(J7:J{$lastDataRow})");
        $sheet->setCellValue("K{$row}", "=SUM(K7:K{$lastDataRow})");
        $sheet->setCellValue("L{$row}", "=SUM(L7:L{$lastDataRow})");
        $sheet->setCellValue("M{$row}", "=SUM(M7:M{$lastDataRow})");
        $sheet->setCellValue("N{$row}", "=SUM(N7:N{$lastDataRow})");
        $sheet->setCellValue("O{$row}", "=SUM(O7:O{$lastDataRow})");
        $sheet->setCellValue("P{$row}", "");
        $sheet->setCellValue("Q{$row}", "");

        // Style Baris Total
        $sheet->getStyle("A{$row}:Q{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000d6b'], 'size' => 9.5],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'e2e8f0'],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'top'    => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000d6b']],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '000d6b']],
            ],
        ]);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ── Number Formatting & Borders ───────────────────────────
        $sheet->getStyle("A7:Q{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cbd5e1']],
            ],
            'font' => ['size' => 9],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getStyle("A7:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("P7:Q{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Format Currency / Accounting Rupiah
        $sheet->getStyle("E7:O{$row}")->getNumberFormat()->setFormatCode('#,##0');

        // Highlight Kolom THP
        $sheet->getStyle("O7:O{$lastDataRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => '000d6b']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'eff6ff'],
            ],
        ]);

        // ── Signatures & Export Time ──────────────────────────────
        $sigRow = $row + 3;
        $sheet->setCellValue("B{$sigRow}", "Dibuat Oleh,");
        $sheet->setCellValue("O{$sigRow}", "Mengetahui / Disetujui,");

        $sheet->setCellValue("B" . ($sigRow + 4), "Finance & Payroll Dept.");
        $sheet->setCellValue("O" . ($sigRow + 4), "Direktur / Kepala Cabang");

        $sheet->getStyle("B{$sigRow}:O" . ($sigRow + 4))->applyFromArray([
            'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => '334155']],
        ]);

        // Simpan File ke Storage Temp
        $cleanSite = $siteName ? preg_replace('/[^A-Za-z0-9_-]/', '_', $siteName) : 'Semua_Kantor';
        $filename = 'Slip_Gaji_Payroll_' . $cleanSite . '_' . $bulan . '.xlsx';
        $tempPath = storage_path('app/export_temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }
}
