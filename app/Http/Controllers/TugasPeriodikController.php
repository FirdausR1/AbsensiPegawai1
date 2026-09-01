<?php

namespace App\Http\Controllers;

use App\Models\TugasPeriodik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TugasPeriodikController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = Auth::user();
        $bulan = $request->input('bulan', now()->format('Y-m'));
        $carbonMonth = Carbon::createFromFormat('Y-m', $bulan)->startOfMonth();

        $tugasList = TugasPeriodik::where('pegawai_id', $pegawai->id)
            ->whereBetween('tanggal', [
                $carbonMonth->copy()->startOfMonth()->toDateString(),
                $carbonMonth->copy()->endOfMonth()->toDateString(),
            ])
            ->latest('waktu_upload')
            ->get();

        // Templates based on Satpam vs Cleaning Service
        if ($pegawai->isShiftWorker() && !$pegawai->isCleaningService()) {
            // Template Satpam / Security Patrol
            $templateHarian = [
                'Patroli Keliling Pos 1-4 & Perimeter Area Kantor Klien',
                'Pengecekan Pintu Utama, Jendela & Akses Masuk Gedung',
                'Inspeksi Kendaraan, Slot Parkir & Ketertiban Area',
                'Pengecekan Tabung APAR, Hydrant & Panel Listrik',
                'Pemeriksaan Log Buku Tamu & Kartu Akses Visitor',
            ];

            $templateMingguan = [
                'Pengecekan & Test Kamera CCTV Perangkat Keamanan',
                'Inspeksi Kelayakan Pagar Pembatas & Lampu Sorot',
                'Simulasi Safety Briefing & Tanggap Darurat Klien',
            ];
        } else {
            // Template Cleaning Service
            $templateHarian = [
                'Pembersihan Area Lobi & Koridor Utama',
                'Sanitasi & Sterilisasi Toilet Restroom',
                'Penyapuan & Pengepelan Lantai Ruangan',
                'Pembersihan Meja Kerja & Fasilitas Umum',
                'Pengosongan & Pengangkutan Tempat Sampah',
            ];

            $templateMingguan = [
                'Pembersihan Kaca, Jendela & Ventilasi',
                'Deep Cleaning Karpet / Polishing Lantai',
                'Pembersihan Langit-langit & Sarang Laba-laba',
                'Pembersihan Area Outdoor & Tempat Parkir',
            ];
        }

        return view('tugas-periodik.index', compact('pegawai', 'tugasList', 'bulan', 'carbonMonth', 'templateHarian', 'templateMingguan'));
    }

    public function store(Request $request)
    {
        $pegawai = Auth::user();

        if (!$pegawai->isCleaningService() && !$pegawai->isShiftWorker() && !$pegawai->hasAdminAccess()) {
            return back()->with('error', 'Menu tugas periodik ini khusus untuk divisi Satpam / Security & Cleaning Service.');
        }

        $request->validate([
            'tipe_tugas' => ['required', 'in:harian,mingguan'],
            'nama_tugas' => ['required', 'string', 'max:255'],
            'foto'       => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'], // max 10MB
            'catatan'    => ['nullable', 'string', 'max:500'],
            'tanggal'    => ['nullable', 'date'],
        ]);

        $tanggalStr = $request->tanggal ?: Carbon::today()->toDateString();
        $waktuUpload = Carbon::now();

        // Upload foto
        $fotoPath = $request->file('foto')->store('tugas_periodik', 'public');

        TugasPeriodik::create([
            'pegawai_id'   => $pegawai->id,
            'tipe_tugas'   => $request->tipe_tugas,
            'nama_tugas'   => $request->nama_tugas,
            'deskripsi'    => $request->catatan,
            'foto_path'    => $fotoPath,
            'waktu_upload' => $waktuUpload,
            'tanggal'      => $tanggalStr,
            'catatan'      => $request->catatan,
        ]);

        $jamFormatted = $waktuUpload->translatedFormat('H:i:s \W\I\B (\d F Y)');

        return back()->with('success', "Foto tugas periodik ({$request->nama_tugas}) berhasil diunggah pada {$jamFormatted}!");
    }

    public function destroy(TugasPeriodik $tugasPeriodik)
    {
        $pegawai = Auth::user();

        if ($tugasPeriodik->pegawai_id !== $pegawai->id && !$pegawai->hasAdminAccess()) {
            return back()->with('error', 'Akses ditolak.');
        }

        if ($tugasPeriodik->foto_path && Storage::disk('public')->exists($tugasPeriodik->foto_path)) {
            Storage::disk('public')->delete($tugasPeriodik->foto_path);
        }

        $tugasPeriodik->delete();

        return back()->with('success', 'Dokumentasi tugas periodik berhasil dihapus.');
    }
}
