<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\SuratPeringatan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratPeringatanController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();

        $query = SuratPeringatan::with(['pegawai.divisi', 'creator'])->latest('tanggal_sp');

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

        if ($pegawaiId = $request->input('pegawai_id')) {
            $query->where('pegawai_id', $pegawaiId);
        }

        if ($spLevel = $request->input('tingkat_sp')) {
            $query->where('tingkat_sp', $spLevel);
        }

        $sps = $query->paginate(20)->withQueryString();

        $pegawaiQuery = Pegawai::orderBy('nama');
        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;
            $pegawaiQuery->where(function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
        }
        $pegawais = $pegawaiQuery->get();

        return view('admin.sp.index', compact('sps', 'pegawais', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = Auth::user();

        $request->validate([
            'pegawai_id'        => ['required', 'exists:pegawais,id'],
            'tingkat_sp'        => ['required', 'in:Teguran Lisan,SP 1,SP 2,SP 3'],
            'pasal_pelanggaran' => ['required', 'string', 'max:255'],
            'deskripsi'         => ['required', 'string'],
            'tanggal_sp'        => ['required', 'date'],
            'berlaku_sampai'    => ['nullable', 'date', 'after_or_equal:tanggal_sp'],
        ]);

        $targetPegawai = Pegawai::findOrFail($request->pegawai_id);
        if ($currentUser->isDivisionAdmin() && $targetPegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Akses ditolak. Pegawai bukan anggota divisi Anda.');
        }

        $berlakuSampai = $request->berlaku_sampai ?: Carbon::parse($request->tanggal_sp)->addMonths(6)->toDateString();

        SuratPeringatan::create([
            'pegawai_id'        => $request->pegawai_id,
            'tingkat_sp'        => $request->tingkat_sp,
            'pasal_pelanggaran' => $request->pasal_pelanggaran,
            'deskripsi'         => $request->deskripsi,
            'tanggal_sp'        => $request->tanggal_sp,
            'berlaku_sampai'    => $berlakuSampai,
            'created_by'        => $currentUser->id,
        ]);

        return back()->with('success', "Surat Peringatan ({$request->tingkat_sp}) untuk {$targetPegawai->nama} telah berhasil diterbitkan!");
    }

    public function destroy(SuratPeringatan $suratPeringatan)
    {
        $currentUser = Auth::user();

        if ($currentUser->isDivisionAdmin() && $suratPeringatan->pegawai->divisi_id !== $currentUser->divisi_id) {
            return back()->with('error', 'Akses ditolak.');
        }

        $nama = $suratPeringatan->pegawai->nama;
        $sp = $suratPeringatan->tingkat_sp;

        $suratPeringatan->delete();

        return back()->with('success', "Data sanksi/SP ({$sp}) milik {$nama} berhasil dihapus.");
    }

    public function cetak(SuratPeringatan $suratPeringatan)
    {
        $currentUser = Auth::user();

        if ($currentUser->isDivisionAdmin() && $suratPeringatan->pegawai->divisi_id !== $currentUser->divisi_id) {
            abort(403, 'Akses ditolak.');
        }

        $suratPeringatan->load(['pegawai.divisi', 'creator']);

        return view('admin.sp.cetak', compact('suratPeringatan', 'currentUser'));
    }
}
