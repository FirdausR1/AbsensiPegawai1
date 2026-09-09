<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KantorKlien;
use App\Models\Pegawai;
use App\Models\SlipGaji;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminSlipGajiController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $bulan = $request->input('bulan', date('Y-m'));
        $site = $request->input('site') ?: $request->input('department');

        $query = Pegawai::with(['divisi'])
            ->where('role', '!=', 'super_admin')
            ->orderBy('nama');

        // Scoping if division admin
        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $query->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            } elseif ($currentUser->divisi_id) {
                $query->where('divisi_id', $currentUser->divisi_id);
            }
        }

        if ($site) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->get();
        $kantorKliens = KantorKlien::orderBy('nama_kantor')->get();

        // Calculate attendance & BPJS preview for each employee
        foreach ($pegawais as $p) {
            $existingSlip = SlipGaji::where('pegawai_id', $p->id)
                ->where('bulan', $bulan)
                ->first();

            $p->slip_gaji = $existingSlip;

            // BPJS Calculations according to Indonesian Regulations
            // BPJS Kesehatan Pekerja: 1% of Gaji Pokok
            $bpjsKes = $p->status_bpjs_kesehatan ? ($p->gaji_pokok * 0.01) : 0;
            // BPJS Ketenagakerjaan Pekerja (JHT 2% + JP 1%): 3% of Gaji Pokok
            $bpjsTk = $p->status_bpjs_ketenagakerjaan ? ($p->gaji_pokok * 0.03) : 0;

            $p->calc_bpjs_kesehatan = $bpjsKes;
            $p->calc_bpjs_tk = $bpjsTk;
            $p->calc_total_pendapatan = $p->gaji_pokok + $p->tunjangan_jabatan + $p->tunjangan_transport;
            $p->calc_total_potongan = $bpjsKes + $bpjsTk;
            $p->calc_take_home_pay = $p->calc_total_pendapatan - $p->calc_total_potongan;
        }

        return view('admin.slip.index', compact('pegawais', 'kantorKliens', 'bulan', 'site'));
    }

    public function updateSalaryAndBpjs(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'gaji_pokok'                  => ['required', 'numeric', 'min:0'],
            'tunjangan_jabatan'           => ['required', 'numeric', 'min:0'],
            'tunjangan_transport'         => ['required', 'numeric', 'min:0'],
            'status_bpjs_kesehatan'       => ['required', 'boolean'],
            'no_bpjs_kesehatan'           => ['nullable', 'string', 'max:50'],
            'status_bpjs_ketenagakerjaan' => ['required', 'boolean'],
            'no_bpjs_ketenagakerjaan'     => ['nullable', 'string', 'max:50'],
            'nama_bank'                   => ['nullable', 'string', 'max:50'],
            'nomor_rekening'              => ['nullable', 'string', 'max:50'],
            'nama_rekening'               => ['nullable', 'string', 'max:150'],
        ]);

        $pegawai->update([
            'gaji_pokok'                  => $request->gaji_pokok,
            'tunjangan_jabatan'           => $request->tunjangan_jabatan,
            'tunjangan_transport'         => $request->tunjangan_transport,
            'status_bpjs_kesehatan'       => $request->status_bpjs_kesehatan,
            'no_bpjs_kesehatan'           => $request->no_bpjs_kesehatan,
            'status_bpjs_ketenagakerjaan' => $request->status_bpjs_ketenagakerjaan,
            'no_bpjs_ketenagakerjaan'     => $request->no_bpjs_ketenagakerjaan,
            'nama_bank'                   => $request->nama_bank,
            'nomor_rekening'              => $request->nomor_rekening,
            'nama_rekening'               => $request->nama_rekening,
        ]);

        return back()->with('success', "Data gaji, BPJS & rekening untuk {$pegawai->nama} berhasil diperbarui.");
    }

    public function updateSalaryAndBpjsMassal(Request $request)
    {
        $request->validate([
            'site'                        => ['required', 'string'],
            'gaji_pokok'                  => ['required', 'numeric', 'min:0'],
            'tunjangan_jabatan'           => ['required', 'numeric', 'min:0'],
            'tunjangan_transport'         => ['required', 'numeric', 'min:0'],
            'status_bpjs_kesehatan'       => ['required', 'boolean'],
            'status_bpjs_ketenagakerjaan' => ['required', 'boolean'],
        ]);

        $site = $request->site;

        $query = Pegawai::where('role', '!=', 'super_admin');
        if ($site !== 'all') {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $updatedCount = $query->update([
            'gaji_pokok'                  => $request->gaji_pokok,
            'tunjangan_jabatan'           => $request->tunjangan_jabatan,
            'tunjangan_transport'         => $request->tunjangan_transport,
            'status_bpjs_kesehatan'       => $request->status_bpjs_kesehatan,
            'status_bpjs_ketenagakerjaan' => $request->status_bpjs_ketenagakerjaan,
        ]);

        $siteLabel = $site === 'all' ? 'Semua Kantor Klien' : $site;

        return back()->with('success', "Master Gaji & BPJS Universal untuk Kantor ({$siteLabel}) berhasil diterapkan ke {$updatedCount} pegawai.");
    }

    public function generateSlip(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'bulan'             => ['required', 'string'],
            'gaji_pokok'        => ['required', 'numeric', 'min:0'],
            'tunjangan_jabatan' => ['nullable', 'numeric', 'min:0'],
            'tunjangan_transport' => ['nullable', 'numeric', 'min:0'],
            'bonus_overtime'    => ['nullable', 'numeric', 'min:0'],
            'potongan_absensi'  => ['nullable', 'numeric', 'min:0'],
            'potongan_lainnya'  => ['nullable', 'numeric', 'min:0'],
            'catatan'           => ['nullable', 'string'],
        ]);

        $bpjsKes = $pegawai->status_bpjs_kesehatan ? ($request->gaji_pokok * 0.01) : 0;
        $bpjsTk = $pegawai->status_bpjs_ketenagakerjaan ? ($request->gaji_pokok * 0.03) : 0;

        $tunjJabatan = $request->tunjangan_jabatan ?? 0;
        $tunjTransport = $request->tunjangan_transport ?? 0;
        $bonus = $request->bonus_overtime ?? 0;
        $potAbsen = $request->potongan_absensi ?? 0;
        $potLain = $request->potongan_lainnya ?? 0;

        $totalPendapatan = $request->gaji_pokok + $tunjJabatan + $tunjTransport + $bonus;
        $totalPotongan = $bpjsKes + $bpjsTk + $potAbsen + $potLain;
        $thp = $totalPendapatan - $totalPotongan;

        $slip = SlipGaji::updateOrCreate(
            [
                'pegawai_id' => $pegawai->id,
                'bulan'      => $request->bulan,
            ],
            [
                'area_kerja'              => $pegawai->area_kerja ?: 'Head Office PT ISW',
                'gaji_pokok'              => $request->gaji_pokok,
                'tunjangan_jabatan'       => $tunjJabatan,
                'tunjangan_transport'     => $tunjTransport,
                'bonus_overtime'          => $bonus,
                'potongan_bpjs_kesehatan' => $bpjsKes,
                'potongan_bpjs_tk'        => $bpjsTk,
                'potongan_absensi'        => $potAbsen,
                'potongan_lainnya'        => $potLain,
                'total_pendapatan'        => $totalPendapatan,
                'total_potongan'          => $totalPotongan,
                'take_home_pay'           => $thp,
                'catatan'                 => $request->catatan,
                'created_by'              => auth()->id(),
            ]
        );

        return back()->with('success', "Slip gaji {$pegawai->nama} periode {$request->bulan} berhasil diterbitkan!");
    }

    public function cetak(Pegawai $pegawai, string $bulan)
    {
        $pegawai->load('divisi');
        $slip = SlipGaji::where('pegawai_id', $pegawai->id)
            ->where('bulan', $bulan)
            ->first();

        // If no saved slip yet, dynamically construct calculation
        if (!$slip) {
            $bpjsKes = $pegawai->status_bpjs_kesehatan ? ($pegawai->gaji_pokok * 0.01) : 0;
            $bpjsTk = $pegawai->status_bpjs_ketenagakerjaan ? ($pegawai->gaji_pokok * 0.03) : 0;
            $totalPendapatan = $pegawai->gaji_pokok + $pegawai->tunjangan_jabatan + $pegawai->tunjangan_transport;
            $totalPotongan = $bpjsKes + $bpjsTk;

            $slip = new SlipGaji([
                'pegawai_id'              => $pegawai->id,
                'bulan'                   => $bulan,
                'area_kerja'              => $pegawai->area_kerja ?: 'Head Office PT ISW',
                'gaji_pokok'              => $pegawai->gaji_pokok,
                'tunjangan_jabatan'       => $pegawai->tunjangan_jabatan,
                'tunjangan_transport'     => $pegawai->tunjangan_transport,
                'bonus_overtime'          => 0,
                'potongan_bpjs_kesehatan' => $bpjsKes,
                'potongan_bpjs_tk'        => $bpjsTk,
                'potongan_absensi'        => 0,
                'potongan_lainnya'        => 0,
                'total_pendapatan'        => $totalPendapatan,
                'total_potongan'          => $totalPotongan,
                'take_home_pay'           => $totalPendapatan - $totalPotongan,
            ]);
        }

        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        return view('admin.slip.cetak', compact('pegawai', 'slip', 'kepala', 'bulan'));
    }

    public function cetakMassal(Request $request)
    {
        $currentUser = auth()->user();
        $bulan = $request->input('bulan', date('Y-m'));
        $site = $request->input('site') ?: $request->input('department');

        $query = Pegawai::with('divisi')
            ->where('role', '!=', 'super_admin')
            ->orderBy('nama');

        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $query->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            } elseif ($currentUser->divisi_id) {
                $query->where('divisi_id', $currentUser->divisi_id);
            }
        }

        if ($site) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->get();

        if ($pegawais->isEmpty()) {
            return back()->with('error', 'Tidak ada pegawai yang ditemukan untuk dicetak slip gaji massal.');
        }

        $dataSlips = [];
        foreach ($pegawais as $p) {
            $slip = SlipGaji::where('pegawai_id', $p->id)
                ->where('bulan', $bulan)
                ->first();

            if (!$slip) {
                $bpjsKes = $p->status_bpjs_kesehatan ? ($p->gaji_pokok * 0.01) : 0;
                $bpjsTk = $p->status_bpjs_ketenagakerjaan ? ($p->gaji_pokok * 0.03) : 0;
                $totalPendapatan = $p->gaji_pokok + $p->tunjangan_jabatan + $p->tunjangan_transport;
                $totalPotongan = $bpjsKes + $bpjsTk;

                $slip = new SlipGaji([
                    'pegawai_id'              => $p->id,
                    'bulan'                   => $bulan,
                    'area_kerja'              => $p->area_kerja ?: 'Head Office PT ISW',
                    'gaji_pokok'              => $p->gaji_pokok,
                    'tunjangan_jabatan'       => $p->tunjangan_jabatan,
                    'tunjangan_transport'     => $p->tunjangan_transport,
                    'bonus_overtime'          => 0,
                    'potongan_bpjs_kesehatan' => $bpjsKes,
                    'potongan_bpjs_tk'        => $bpjsTk,
                    'potongan_absensi'        => 0,
                    'potongan_lainnya'        => 0,
                    'total_pendapatan'        => $totalPendapatan,
                    'total_potongan'          => $totalPotongan,
                    'take_home_pay'           => $totalPendapatan - $totalPotongan,
                ]);
            }
            $dataSlips[] = [
                'pegawai' => $p,
                'slip'    => $slip,
            ];
        }

        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        $siteName = $site ?: 'Semua Kantor Klien';

        return view('admin.slip.cetak-massal', compact('dataSlips', 'kepala', 'bulan', 'siteName'));
    }

    public function exportExcel(Request $request, \App\Services\SlipGajiExportService $exportService)
    {
        $currentUser = auth()->user();
        $bulan = $request->input('bulan', date('Y-m'));
        $site = $request->input('site') ?: $request->input('department');

        $query = Pegawai::with('divisi')
            ->where('role', '!=', 'super_admin')
            ->orderBy('nama');

        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $query->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            } elseif ($currentUser->divisi_id) {
                $query->where('divisi_id', $currentUser->divisi_id);
            }
        }

        if ($site) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->get();

        if ($pegawais->isEmpty()) {
            return back()->with('error', 'Tidak ada pegawai yang ditemukan untuk di-export ke Excel.');
        }

        $filePath = $exportService->export($pegawais, $bulan, $site);
        $cleanSite = $site ? \Illuminate\Support\Str::slug($site) : 'Semua_Kantor';
        $filename = 'Rekap_Slip_Gaji_' . $cleanSite . '_' . $bulan . '.xlsx';

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/octet-stream',
            'Access-Control-Expose-Headers' => 'Content-Disposition',
            'Cache-Control' => 'no-cache, no-store, must-revalidate, private',
        ])->deleteFileAfterSend(true);
    }
}
