<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KantorKlien;
use App\Models\MouKontrak;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminMouKontrakController extends Controller
{
    public function index()
    {
        $mous = MouKontrak::with(['kantorKlien', 'creator'])
            ->latest('tanggal_mou')
            ->paginate(15);

        $kantorKliens = KantorKlien::orderBy('nama_kantor')->get();

        return view('admin.mou.index', compact('mous', 'kantorKliens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kantor_klien_id'        => ['nullable', 'exists:kantor_kliens,id'],
            'nama_kantor'            => ['required', 'string', 'max:255'],
            'penanggung_jawab_klien' => ['required', 'string', 'max:255'],
            'jabatan_klien'          => ['required', 'string', 'max:255'],
            'telepon_klien'          => ['nullable', 'string', 'max:50'],
            'alamat_klien'           => ['nullable', 'string'],
            'tanggal_mou'            => ['required', 'date'],
            'tanggal_mulai'          => ['required', 'date'],
            'tanggal_selesai'        => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jumlah_personil'        => ['required', 'integer', 'min:1'],
            'layanan_outsourcing'    => ['required', 'string', 'max:255'],
            'nilai_kontrak'          => ['nullable', 'numeric', 'min:0'],
            'catatan_pasal'          => ['nullable', 'string'],
        ]);

        // Auto-generate Nomor MoU
        $countThisMonth = MouKontrak::whereMonth('tanggal_mou', date('m'))
            ->whereYear('tanggal_mou', date('Y'))
            ->count() + 1;

        $nomorMou = 'MOU/ISW/' . date('Y/m/') . str_pad($countThisMonth, 3, '0', STR_PAD_LEFT);

        // Fill data kantor jika kantor_klien_id dipilih
        $namaKantor = $request->nama_kantor;
        $pjKlien = $request->penanggung_jawab_klien;
        $telp = $request->telepon_klien;
        $alamat = $request->alamat_klien;

        if ($request->kantor_klien_id) {
            $kk = KantorKlien::find($request->kantor_klien_id);
            if ($kk) {
                $namaKantor = $kk->nama_kantor;
                $pjKlien = $request->penanggung_jawab_klien ?: ($kk->penanggung_jawab ?: 'Pimpinan Klien');
                $telp = $request->telepon_klien ?: $kk->telepon;
                $alamat = $request->alamat_klien ?: $kk->alamat;
            }
        }

        $mou = MouKontrak::create([
            'nomor_mou'              => $nomorMou,
            'kantor_klien_id'        => $request->kantor_klien_id,
            'nama_kantor'            => $namaKantor,
            'penanggung_jawab_klien' => $pjKlien,
            'jabatan_klien'          => $request->jabatan_klien,
            'telepon_klien'          => $telp,
            'alamat_klien'           => $alamat,
            'tanggal_mou'            => $request->tanggal_mou,
            'tanggal_mulai'          => $request->tanggal_mulai,
            'tanggal_selesai'        => $request->tanggal_selesai,
            'jumlah_personil'        => $request->jumlah_personil,
            'layanan_outsourcing'    => $request->layanan_outsourcing,
            'nilai_kontrak'          => $request->nilai_kontrak,
            'catatan_pasal'          => $request->catatan_pasal,
            'created_by'             => auth()->id(),
        ]);

        return back()->with('success', "Dokumen MoU / Kontrak Kerjasama ({$mou->nomor_mou}) berhasil dibuat!");
    }

    public function cetak(MouKontrak $mouKontrak)
    {
        $mouKontrak->load(['kantorKlien', 'creator']);
        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        return view('admin.mou.cetak', compact('mouKontrak', 'kepala'));
    }

    public function destroy(MouKontrak $mouKontrak)
    {
        $nomor = $mouKontrak->nomor_mou;
        $mouKontrak->delete();

        return back()->with('success', "Dokumen MoU ({$nomor}) berhasil dihapus.");
    }
}
