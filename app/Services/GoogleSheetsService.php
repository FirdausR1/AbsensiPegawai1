<?php

namespace App\Services;

use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mengirim data absensi ke Google Sheets melalui Google Apps Script Web App.
 * Tidak membutuhkan Service Account JSON — cukup URL dari Apps Script yang di-deploy.
 *
 * Cara setup:
 * 1. Buka Google Spreadsheet → Extensions → Apps Script
 * 2. Paste script dari GOOGLE_APPS_SCRIPT.js
 * 3. Deploy sebagai Web App (Execute as: Me, Who has access: Anyone)
 * 4. Salin URL Web App → isi di .env: GOOGLE_APPS_SCRIPT_URL=https://script.google.com/macros/s/...
 */
class GoogleSheetsService
{
    protected string $scriptUrl;
    protected string $spreadsheetId;

    // Baris pertama data (tanggal 1) dan kolom-kolom sesuai template asli.
    protected int $firstDataRow = 12;
    protected string $colTanggal    = 'B';
    protected string $colJamMasuk   = 'C';
    protected string $colTtdMasuk   = 'D';
    protected string $colJamPulang  = 'E';
    protected string $colTtdPulang  = 'F';
    protected string $colKeterangan = 'G';

    public function __construct()
    {
        $this->scriptUrl      = config('services.google_apps_script.url', '');
        $this->spreadsheetId  = config('services.google_sheets.spreadsheet_id', '');
    }

    /**
     * Kirim request ke Apps Script Web App.
     */
    protected function send(array $payload): array
    {
        if (empty($this->scriptUrl)) {
            throw new \RuntimeException('GOOGLE_APPS_SCRIPT_URL belum diisi di .env');
        }

        $response = Http::timeout(30)
            ->withoutVerifying()          // disable SSL verify untuk dev lokal
            ->post($this->scriptUrl, $payload);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Apps Script error HTTP {$response->status()}: " . $response->body()
            );
        }

        $json = $response->json();

        if (isset($json['status']) && $json['status'] === 'error') {
            throw new \RuntimeException("Apps Script error: " . ($json['message'] ?? 'unknown'));
        }

        return $json ?? [];
    }

    protected function rowForDate(Carbon $date): int
    {
        return $this->firstDataRow + ($date->day - 1);
    }

    /**
     * Pastikan tab pegawai ada di spreadsheet (dibuat otomatis jika belum ada).
     */
    public function ensureSheetTabExists(Pegawai $pegawai): void
    {
        $this->send([
            'action'        => 'ensureTab',
            'spreadsheetId' => $this->spreadsheetId,
            'tabName'       => $pegawai->sheetTabName(),
            'nama'          => $pegawai->nama,
            'area_kerja'    => $pegawai->area_kerja ?? '-',
            'bulan'         => Carbon::now()->translatedFormat('F Y'),
        ]);
    }

    public function writeCheckIn(Pegawai $pegawai, Carbon $date, string $jam): void
    {
        $row = $this->rowForDate($date);
        $signatureUrl = $pegawai->hasSignature()
            ? asset('storage/' . $pegawai->signature_path)
            : null;

        $this->send([
            'action'        => 'writeCheckIn',
            'spreadsheetId' => $this->spreadsheetId,
            'tabName'       => $pegawai->sheetTabName(),
            'nama'          => $pegawai->nama,
            'area_kerja'    => $pegawai->area_kerja ?? '-',
            'bulan'         => Carbon::now()->translatedFormat('F Y'),
            'row'           => $row,
            'colJamMasuk'   => $this->colJamMasuk,
            'colTtdMasuk'   => $this->colTtdMasuk,
            'jamMasuk'      => $jam,
            'signatureUrl'  => $signatureUrl,
        ]);
    }

    public function writeCheckOut(Pegawai $pegawai, Carbon $date, string $jam): void
    {
        $row = $this->rowForDate($date);
        $signatureUrl = $pegawai->hasSignature()
            ? asset('storage/' . $pegawai->signature_path)
            : null;

        $this->send([
            'action'        => 'writeCheckOut',
            'spreadsheetId' => $this->spreadsheetId,
            'tabName'       => $pegawai->sheetTabName(),
            'nama'          => $pegawai->nama,
            'area_kerja'    => $pegawai->area_kerja ?? '-',
            'bulan'         => Carbon::now()->translatedFormat('F Y'),
            'row'           => $row,
            'colJamPulang'  => $this->colJamPulang,
            'colTtdPulang'  => $this->colTtdPulang,
            'jamPulang'     => $jam,
            'signatureUrl'  => $signatureUrl,
        ]);
    }

    public function writeKeterangan(Pegawai $pegawai, Carbon $date, string $keterangan): void
    {
        $row = $this->rowForDate($date);

        $this->send([
            'action'        => 'writeKeterangan',
            'spreadsheetId' => $this->spreadsheetId,
            'tabName'       => $pegawai->sheetTabName(),
            'nama'          => $pegawai->nama,
            'area_kerja'    => $pegawai->area_kerja ?? '-',
            'bulan'         => Carbon::now()->translatedFormat('F Y'),
            'row'           => $row,
            'colKeterangan' => $this->colKeterangan,
            'keterangan'    => $keterangan,
        ]);
    }
}
