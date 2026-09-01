<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KantorKlien;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class AdminKantorKlienController extends Controller
{
    public function index(Request $request)
    {
        $kantors = KantorKlien::latest()->get();

        // Attach pegawai count per site
        foreach ($kantors as $k) {
            $k->total_pegawai = Pegawai::where('area_kerja', $k->nama_kantor)->count();
        }

        return view('admin.kantor-klien.index', compact('kantors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kantor'      => ['required', 'string', 'max:255', 'unique:kantor_kliens,nama_kantor'],
            'kode_kantor'      => ['nullable', 'string', 'max:50'],
            'alamat'           => ['nullable', 'string'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'telepon'          => ['nullable', 'string', 'max:50'],
        ]);

        KantorKlien::create($request->only(['nama_kantor', 'kode_kantor', 'alamat', 'penanggung_jawab', 'telepon']));

        return back()->with('success', "Kantor Klien / Site Project '{$request->nama_kantor}' berhasil ditambahkan!");
    }

    public function update(Request $request, KantorKlien $kantorKlien)
    {
        $request->validate([
            'nama_kantor'      => ['required', 'string', 'max:255', 'unique:kantor_kliens,nama_kantor,' . $kantorKlien->id],
            'kode_kantor'      => ['nullable', 'string', 'max:50'],
            'alamat'           => ['nullable', 'string'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'telepon'          => ['nullable', 'string', 'max:50'],
        ]);

        $oldName = $kantorKlien->nama_kantor;
        $kantorKlien->update($request->only(['nama_kantor', 'kode_kantor', 'alamat', 'penanggung_jawab', 'telepon']));

        // If name changed, update area_kerja in pegawais table
        if ($oldName !== $request->nama_kantor) {
            Pegawai::where('area_kerja', $oldName)->update(['area_kerja' => $request->nama_kantor]);
        }

        return back()->with('success', "Data Kantor Klien '{$request->nama_kantor}' berhasil diperbarui!");
    }

    public function destroy(KantorKlien $kantorKlien)
    {
        $nama = $kantorKlien->nama_kantor;
        $kantorKlien->delete();

        return back()->with('success', "Kantor Klien '{$nama}' berhasil dihapus.");
    }
}
