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

    public function exportPegawai(Pegawai $pegawai, string $bulan, bool $includeLocation = false): string
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

        $this->buildSheet($sheet, $pegawai, $carbonMonth, $absensis, $includeLocation);

        $filename = 'absensi_' . $pegawai->sheetTabName() . '_' . $bulan . '.xlsx';
        $tempPath = storage_path('app/export_temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    public function exportSemuaPegawai($pegawais, string $bulan, bool $includeLocation = false): string
    {
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();
        $spreadsheet = new Spreadsheet();

        foreach ($pegawais as $index => $pegawai) {
            $absensis = Absensi::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [
                    $carbonMonth->copy()->startOfMonth()->toDateString(),
                    $carbonMonth->copy()->endOfMonth()->toDateString(),
                ])
                ->get()
                ->keyBy(fn($a) => Carbon::parse($a->tanggal)->day);

            $sheet = $index === 0 ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();

            $sheetTitle = substr(preg_replace('/[^A-Za-z0-9 _-]/', '', $pegawai->nama), 0, 30);
            $sheet->setTitle($sheetTitle ?: 'Pegawai ' . ($index + 1));

            $this->buildSheet($sheet, $pegawai, $carbonMonth, $absensis, $includeLocation);
        }

        $filename = 'Rekap_Absensi_Semua_Pegawai_' . $bulan . '.xlsx';
        $tempPath = storage_path('app/export_temp/' . $filename);

        if (!is_dir(dirname($tempPath))) {
            mkdir(dirname($tempPath), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return $tempPath;
    }

    protected function buildSheet($sheet, Pegawai $pegawai, Carbon $carbonMonth, $absensis, bool $includeLocation = false): void
    {
        $daysInMonth  = $carbonMonth->daysInMonth;
        $logoPath     = file_exists(public_path('images/Picture1.png'))
            ? public_path('images/Picture1.png')
            : (file_exists(public_path('images/logo.png')) ? public_path('images/logo.png') : public_path('images/logo.jpg'));
        $firstDataRow = 12;   // Baris 12 = tanggal 1 (sesuai template asli)

        $approvedCutis = $pegawai->cutis()
            ->where('status', 'approved')
            ->where('tanggal_mulai', '<=', $carbonMonth->copy()->endOfMonth()->toDateString())
            ->where('tanggal_selesai', '>=', $carbonMonth->copy()->startOfMonth()->toDateString())
            ->get();

        // ── Lebar Kolom ────────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(14);  // Logo / NO
        $sheet->getColumnDimension('B')->setWidth(18);  // TANGGAL
        $sheet->getColumnDimension('C')->setWidth(13);  // JAM MASUK
        $sheet->getColumnDimension('D')->setWidth(15);  // TTD MASUK
        $sheet->getColumnDimension('E')->setWidth(13);  // JAM PULANG
        $sheet->getColumnDimension('F')->setWidth(15);  // TTD PULANG
        $sheet->getColumnDimension('G')->setWidth(25);  // KETERANGAN
        $sheet->getColumnDimension('H')->setWidth(38);  // LOKASI / KOORDINAT GPS

        // Kontrol visibilitas kolom Lokasi (Kolom H):
        // Jika tidak dicentang/includeLocation=false maka kolom H disembunyikan (hidden)
        $sheet->getColumnDimension('H')->setVisible($includeLocation);

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

        // === TENGAH: B1:F3 — "DAFTAR HADIR TENAGA KERJA" ===
        $sheet->mergeCells('B1:F3');
        $sheet->setCellValue('B1', 'DAFTAR HADIR TENAGA KERJA');
        $sheet->getStyle('B1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1a237e']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // === KANAN: G1:H3 — Kotak No. / Revisi / Berlaku ===
        $infoStyle = [
            'font'      => ['size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->setCellValue('G1', 'No.');
        $sheet->setCellValue('H1', ':');
        $sheet->setCellValue('G2', 'Revisi');
        $sheet->setCellValue('H2', ':');
        $sheet->setCellValue('G3', 'Berlaku');
        $sheet->setCellValue('H3', ':');

        foreach (['G1','G2','G3','H1','H2','H3'] as $c) {
            $sheet->getStyle($c)->applyFromArray($infoStyle);
        }

        // Border kotak No./Revisi/Berlaku
        $sheet->getStyle('G1:H3')->applyFromArray([
            'borders' => [
                'outline'     => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
                'horizontal'  => ['borderStyle' => Border::BORDER_HAIR,  'color' => ['rgb' => '999999']],
                'vertical'    => ['borderStyle' => Border::BORDER_HAIR,  'color' => ['rgb' => '999999']],
            ],
        ]);

        // Border bawah seluruh header (pemisah)
        $sheet->getStyle('A3:H3')->applyFromArray([
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
            $sheet->getStyle("B{$row}:H{$row}")->applyFromArray($valueStyle);
        }

        $sheet->setCellValue('C5', $pegawai->nama);
        $sheet->setCellValue('C6', $carbonMonth->translatedFormat('F Y'));
        
        $isInternal = ($pegawai->status_karyawan === 'internal' || $pegawai->is_admin || in_array($pegawai->role, ['super_admin', 'kepala_isw']));

        if ($isInternal) {
            $rawArea = $pegawai->area_kerja;
            if (empty($rawArea) || in_array($rawArea, ['Management / HR', 'Operasional', 'Satpam / Security', 'Cleaning Service'])) {
                $areaKerjaText = "Head Office PT ISW" . ($rawArea ? " ({$rawArea})" : "");
            } else {
                $areaKerjaText = $rawArea;
            }
        } else {
            $areaKerjaText = $pegawai->area_kerja ?: ($pegawai->divisi?->nama ?: 'Site Proyek');
        }

        $sheet->setCellValue('C7', $areaKerjaText);

        // ── BARIS 9-11: Header Tabel ────────────────────────────────────────
        $sheet->mergeCells('A9:A11');
        $sheet->mergeCells('B9:B11');
        $sheet->mergeCells('C9:C11');
        $sheet->mergeCells('D9:D11');
        $sheet->mergeCells('E9:E11');
        $sheet->mergeCells('F9:F11');
        $sheet->mergeCells('G9:G11');
        $sheet->mergeCells('H9:H11');

        $headers = [
            'A9' => 'NO',
            'B9' => 'TANGGAL',
            'C9' => 'JAM MASUK',
            'D9' => 'TANDA TANGAN',
            'E9' => 'JAM PULANG',
            'F9' => 'TANDA TANGAN',
            'G9' => 'KETERANGAN',
            'H9' => 'LOKASI / KOORDINAT GPS',
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

            $divisi = $pegawai->getDivisi();
            $isPastOrToday = $date->lte(Carbon::today());
            $isHoliday = $holidayService->isNationalHoliday($date);
            $holidayName = $holidayService->reasonIfHoliday($date);

            // Warna baris dan Keterangan
            $rowBg = null;
            $keterangan = '';
            $lokasiText = '';
            $isAlpa = false;

            $approvedCuti = $approvedCutis->first(function ($c) use ($date) {
                $d = $date->toDateString();
                return $c->tanggal_mulai->toDateString() <= $d && $c->tanggal_selesai->toDateString() >= $d;
            });
            $jadwalShift  = $pegawai->getJadwalOnDate($date);

            if ($approvedCuti) {
                $isIzin = stripos($approvedCuti->tipe_cuti, 'izin') !== false || stripos($approvedCuti->tipe_cuti, 'ijin') !== false;
                $prefix = $isIzin ? 'Izin' : 'Cuti';
                $keterangan = "{$prefix} ({$approvedCuti->jumlah_hari} Hari - {$approvedCuti->tipe_cuti})";
                $rowBg = $isIzin ? 'e0f2fe' : 'e8eaf6'; // soft blue / soft purple
            } elseif ($absensi && !empty($absensi->keterangan)) {
                $keterangan = $absensi->keterangan;
                $ketLower = strtolower($keterangan);
                if (str_contains($ketLower, 'cuti')) {
                    $rowBg = 'e8eaf6';
                } elseif (str_contains($ketLower, 'izin') || str_contains($ketLower, 'ijin') || str_contains($ketLower, 'sakit')) {
                    $rowBg = 'e0f2fe';
                } elseif (str_contains($ketLower, 'dinas')) {
                    $rowBg = 'e0f7fa';
                }
            } elseif ($absensi && $absensi->jam_masuk) {
                // Pegawai hadir (termasuk shift di hari libur/akhir pekan)
                $menitTerlambat = $absensi->getMenitTerlambat($pegawai);
                if ($menitTerlambat > 0) {
                    $keterangan = "Terlambat {$menitTerlambat} Menit";
                } elseif ($jadwalShift) {
                    $keterangan = "Hadir (Shift {$jadwalShift->tipe_shift})";
                } elseif ($isHoliday || $date->isWeekend()) {
                    $keterangan = "Hadir (Shift)";
                } else {
                    $keterangan = "Hadir";
                }
            } elseif ($jadwalShift && $jadwalShift->isLibur()) {
                // Dijadwalkan libur oleh Danru
                $keterangan = 'Libur (Jadwal)';
                $rowBg = 'fff8e1';
            } elseif ($jadwalShift) {
                // Ada jadwal shift tapi tidak absen = ALPA (harusnya masuk)
                $keterangan = 'ALPA';
                $rowBg = 'fce8e6';
                $isAlpa = true;
            } elseif ($isHoliday && $divisi->hari_kerja_tipe !== '7_hari') {
                $keterangan = $holidayName ?: 'Libur Nasional';
                $rowBg = 'ffebee';
            } elseif ($date->isWeekend() && $divisi->hari_kerja_tipe === '5_hari') {
                $keterangan = 'Libur';
                $rowBg = 'fff8e1';
            } elseif ($date->isSunday() && $divisi->hari_kerja_tipe === '6_hari') {
                $keterangan = 'Libur';
                $rowBg = 'fff8e1';
            } elseif ($divisi->hari_kerja_tipe === '7_hari' && $isPastOrToday && !$jadwalShift) {
                // 7 hari kerja tapi tidak ada jadwal = Libur otomatis (sistem shift bergilir)
                $keterangan = 'Libur';
                $rowBg = 'fff8e1';
            } elseif ($isPastOrToday) {
                // Hari kerja terlewat dan tidak absen = ALPA
                $keterangan = 'ALPA';
                $rowBg = 'fce8e6';
                $isAlpa = true;
            }

            // Bangun informasi Lokasi GPS untuk Kolom H
            if ($absensi) {
                $locArr = [];
                if ($absensi->status_presensi === 'dinas_luar' || str_contains(strtolower($absensi->keterangan ?? ''), 'dinas')) {
                    $locArr[] = "[DINAS LUAR]";
                }
                if (!empty($absensi->lokasi_masuk)) {
                    $locArr[] = "Masuk: " . $absensi->lokasi_masuk;
                }
                if (!empty($absensi->lokasi_pulang)) {
                    $locArr[] = "Pulang: " . $absensi->lokasi_pulang;
                }
                if (!empty($absensi->lokasi_absen) && empty($absensi->lokasi_masuk)) {
                    $locArr[] = "GPS: " . $absensi->lokasi_absen;
                }
                if (!empty($locArr)) {
                    $lokasiText = implode(" | ", $locArr);
                }
            }

            // Isi data
            $sheet->setCellValue("A{$row}", $day);
            $sheet->setCellValue("B{$row}", $date->translatedFormat('d F Y'));
            $sheet->setCellValue("C{$row}", $absensi?->jam_masuk ? substr($absensi->jam_masuk, 0, 5) : '');
            $sheet->setCellValue("E{$row}", $absensi?->jam_pulang ? substr($absensi->jam_pulang, 0, 5) : '');
            $sheet->setCellValue("G{$row}", $keterangan);
            $sheet->setCellValue("H{$row}", $lokasiText);

            // Style baris data
            $dataStyle = [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'cccccc']]],
                'font' => ['size' => 9],
            ];
            if ($rowBg) {
                $dataStyle['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBg]];
            }
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($dataStyle);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

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
        $sheet->mergeCells("A{$footerRow}:H{$footerRow}");
        $sheet->setCellValue("A{$footerRow}", 'CATATAN : DAFTAR HADIR DIISI SETIAP HARI SESUAI KEHADIRAN. BUKAN DIRAPEL ATAU DIISI AKHIR BULAN.');
        $sheet->getStyle("A{$footerRow}")->applyFromArray([
            'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => 'c62828']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);

        // ── Tanda Tangan Pengguna Jasa & Personil ───────────────────────────
        $ttdRow = $footerRow + 3;
        $sheet->mergeCells("A{$ttdRow}:D{$ttdRow}");
        $sheet->mergeCells("E{$ttdRow}:H{$ttdRow}");
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
