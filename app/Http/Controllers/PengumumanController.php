<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\SuratPeringatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PengumumanController extends Controller
{
    public function index()
    {
        $pegawai = Auth::user();

        // High-priority announcements
        $pengumumans = Pengumuman::where('kategori', 'pengumuman')
            ->latest()
            ->get();

        // Standard Operating Procedures (SOP PT ISW)
        $sops = Pengumuman::where('kategori', 'sop')
            ->latest()
            ->get();

        // Pasal-Pasal Pelanggaran & Sanksi Perusahaan
        $pasals = Pengumuman::where('kategori', 'pasal_pelanggaran')
            ->latest()
            ->get();

        // Surat Peringatan (SP) milik pegawai ini sendiri jika ada
        $mySps = SuratPeringatan::where('pegawai_id', $pegawai->id)
            ->latest('tanggal_sp')
            ->get();

        return view('pengumuman.index', compact('pegawai', 'pengumumans', 'sops', 'pasals', 'mySps'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAdminAccess()) {
            return back()->with('error', 'Akses ditolak. Hanya Admin / Supervisor yang dapat menambahkan pengumuman.');
        }

        $request->validate([
            'judul'     => ['required', 'string', 'max:255'],
            'isi'       => ['required', 'string'],
            'kategori'  => ['required', 'in:pengumuman,sop,pasal_pelanggaran'],
            'prioritas' => ['required', 'in:biasa,penting,darurat'],
        ]);

        Pengumuman::create([
            'judul'      => $request->judul,
            'isi'        => $request->isi,
            'kategori'   => $request->kategori,
            'prioritas'  => $request->prioritas,
            'created_by' => $user->id,
        ]);

        return back()->with('success', 'Informasi / SOP / Pasal Pelanggaran berhasil diterbitkan!');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $user = Auth::user();
        if (!$user->hasAdminAccess()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $pengumuman->delete();

        return back()->with('success', 'Pengumuman / SOP berhasil dihapus.');
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $user = Auth::user();
        if (!$user->hasAdminAccess()) {
            return back()->with('error', 'Akses ditolak.');
        }

        $request->validate([
            'judul'     => ['required', 'string', 'max:255'],
            'isi'       => ['required', 'string'],
            'kategori'  => ['required', 'in:pengumuman,sop,pasal_pelanggaran'],
            'prioritas' => ['required', 'in:biasa,penting,darurat'],
        ]);

        $pengumuman->update([
            'judul'     => $request->judul,
            'isi'       => $request->isi,
            'kategori'  => $request->kategori,
            'prioritas' => $request->prioritas,
        ]);

        return back()->with('success', 'Pengumuman / SOP / Pasal Pelanggaran berhasil diperbarui!');
    }
}
