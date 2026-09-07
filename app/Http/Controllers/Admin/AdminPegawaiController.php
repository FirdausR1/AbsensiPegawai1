<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Divisi;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class AdminPegawaiController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();
        $query = Pegawai::query();

        // Scope query jika user adalah Admin Divisi (Danru/Supervisor)
        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id;
            $area = $currentUser->area_kerja;

            if ($divisiId) {
                $query->where('divisi_id', $divisiId);
            } elseif ($area) {
                $query->where('area_kerja', 'like', "%{$area}%");
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('area_kerja', 'like', "%{$search}%")
                  ->orWhere('sheet_tab_name', 'like', "%{$search}%");
            });
        }

        if ($department = $request->input('department')) {
            $query->where('area_kerja', $department);
        }

        if ($status = $request->input('status_karyawan')) {
            $query->where('status_karyawan', $status);
        }

        $pegawais = $query->orderBy('nama', 'asc')->paginate(15)->withQueryString();

        $statsQuery = Pegawai::query();
        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->divisi_id) {
                $statsQuery->where('divisi_id', $currentUser->divisi_id);
            } elseif ($currentUser->area_kerja) {
                $statsQuery->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            }
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'has_signature' => (clone $statsQuery)->whereNotNull('signature_path')->where('signature_path', '!=', '')->count(),
            'admin' => (clone $statsQuery)->where(function ($q) {
                $q->where('is_admin', true)->orWhereIn('role', ['super_admin', 'admin_divisi']);
            })->count(),
        ];

        $kantorKliens = \App\Models\KantorKlien::orderBy('nama_kantor')->get();

        return view('admin.pegawai.index', compact('pegawais', 'stats', 'currentUser', 'kantorKliens'));
    }

    public function create()
    {
        $currentUser = auth()->user();

        if ($currentUser->isDivisionAdmin()) {
            $divisis = Divisi::where('id', $currentUser->divisi_id)->get();
            if ($divisis->isEmpty() && $currentUser->area_kerja) {
                $divisis = Divisi::where('nama', 'like', "%{$currentUser->area_kerja}%")->get();
            }
        } else {
            $divisis = Divisi::orderBy('nama')->get();
        }

        $kantorKliens = \App\Models\KantorKlien::orderBy('nama_kantor')->get();

        return view('admin.pegawai.create', compact('divisis', 'currentUser', 'kantorKliens'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:pegawais'],
            'password' => ['required', Rules\Password::defaults()],
            'status_karyawan' => ['required', 'in:internal,outsourcing'],
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'divisi_id' => ['nullable', 'exists:divisis,id'],
            'role' => ['nullable', 'in:staff,admin_divisi,super_admin,kepala_isw'],
            'sheet_tab_name' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'nik' => ['nullable', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'status_pernikahan' => ['nullable', 'string', 'max:50'],
            'kontak_darurat' => ['nullable', 'string', 'max:255'],
        ]);

        $divisiId = $request->divisi_id;
        $role = $request->input('role', 'staff');

        // Jika dibuat oleh Admin Divisi, kunci ke divisinya dan batasi role
        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id ?: $divisiId;
            $role = 'staff';
        }

        $areaKerja = $request->area_kerja;
        if (empty($areaKerja) && $request->status_karyawan === 'internal') {
            $areaKerja = 'Head Office PT ISW';
        }

        $isAdmin = ($role === 'super_admin' || $role === 'admin_divisi' || $role === 'kepala_isw');

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('avatars', 'public');
        }

        Pegawai::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_kerja' => $areaKerja,
            'status_karyawan' => $request->status_karyawan,
            'divisi_id' => $divisiId,
            'role' => $role,
            'is_admin' => $isAdmin,
            'foto_path' => $fotoPath,
            'sheet_tab_name' => $request->sheet_tab_name ?: $request->nama,
            'nik' => $request->nik,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin ?: 'Laki-laki',
            'alamat' => $request->alamat,
            'pendidikan_terakhir' => $request->pendidikan_terakhir ?: 'SMA/SMK',
            'no_hp' => $request->no_hp,
            'status_pernikahan' => $request->status_pernikahan ?: 'Belum Menikah',
            'kontak_darurat' => $request->kontak_darurat,
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', "Pegawai {$request->nama} berhasil ditambahkan.");
    }

    public function edit(Pegawai $pegawai)
    {
        $currentUser = auth()->user();

        // Validasi akses Admin Divisi
        if ($currentUser->isDivisionAdmin()) {
            if ($pegawai->isSuperAdmin() || ($pegawai->divisi_id && $pegawai->divisi_id !== $currentUser->divisi_id)) {
                return redirect()->route('admin.pegawai.index')->with('error', 'Anda tidak memiliki hak untuk mengedit data pegawai di luar divisi Anda.');
            }
            $divisis = Divisi::where('id', $currentUser->divisi_id)->get();
        } else {
            $divisis = Divisi::orderBy('nama')->get();
        }

        $kantorKliens = \App\Models\KantorKlien::orderBy('nama_kantor')->get();

        return view('admin.pegawai.edit', compact('pegawai', 'divisis', 'currentUser', 'kantorKliens'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $currentUser = auth()->user();

        if ($currentUser->isDivisionAdmin()) {
            if ($pegawai->isSuperAdmin() || ($pegawai->divisi_id && $pegawai->divisi_id !== $currentUser->divisi_id)) {
                return redirect()->route('admin.pegawai.index')->with('error', 'Akses ditolak.');
            }
        }

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('pegawais')->ignore($pegawai->id)],
            'password' => ['nullable', Rules\Password::defaults()],
            'status_karyawan' => ['required', 'in:internal,outsourcing'],
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'divisi_id' => ['nullable', 'exists:divisis,id'],
            'role' => ['nullable', 'in:staff,admin_divisi,super_admin,kepala_isw'],
            'sheet_tab_name' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'nik' => ['nullable', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'status_pernikahan' => ['nullable', 'string', 'max:50'],
            'kontak_darurat' => ['nullable', 'string', 'max:255'],
        ]);

        $divisiId = $request->divisi_id;
        $role = $currentUser->isSuperAdmin() ? $request->input('role', $pegawai->role ?: 'staff') : ($pegawai->role ?: 'staff');

        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id ?: $pegawai->divisi_id;
        }

        $areaKerja = $request->area_kerja;
        if (empty($areaKerja) && $request->status_karyawan === 'internal') {
            $areaKerja = 'Head Office PT ISW';
        }

        $isAdmin = ($role === 'super_admin' || $role === 'admin_divisi' || $role === 'kepala_isw');

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'area_kerja' => $areaKerja,
            'status_karyawan' => $request->status_karyawan,
            'divisi_id' => $divisiId,
            'role' => $role,
            'is_admin' => $isAdmin,
            'sheet_tab_name' => $request->sheet_tab_name ?: $request->nama,
            'nik' => $request->nik,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin ?: 'Laki-laki',
            'alamat' => $request->alamat,
            'pendidikan_terakhir' => $request->pendidikan_terakhir ?: 'SMA/SMK',
            'no_hp' => $request->no_hp,
            'status_pernikahan' => $request->status_pernikahan ?: 'Belum Menikah',
            'kontak_darurat' => $request->kontak_darurat,
        ];

        if ($request->hasFile('foto')) {
            if ($pegawai->foto_path && Storage::disk('public')->exists($pegawai->foto_path)) {
                Storage::disk('public')->delete($pegawai->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('avatars', 'public');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pegawai->update($data);

        return redirect()->route('admin.pegawai.index')->with('success', "Data pegawai {$pegawai->nama} berhasil diperbarui.");
    }

    public function destroy(Pegawai $pegawai)
    {
        $currentUser = auth()->user();

        if ($pegawai->id === $currentUser->id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($currentUser->isDivisionAdmin() && ($pegawai->isSuperAdmin() || $pegawai->divisi_id !== $currentUser->divisi_id)) {
            return back()->with('error', 'Anda tidak memiliki hak untuk menghapus akun di luar divisi Anda.');
        }

        if ($pegawai->signature_path && Storage::disk('public')->exists($pegawai->signature_path)) {
            Storage::disk('public')->delete($pegawai->signature_path);
        }

        $nama = $pegawai->nama;
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')->with('success', "Pegawai {$nama} berhasil dihapus.");
    }

    public function resetSignature(Pegawai $pegawai)
    {
        $currentUser = auth()->user();

        if ($currentUser->isDivisionAdmin() && $pegawai->divisi_id !== $currentUser->divisi_id) {
            return redirect()->route('admin.pegawai.index')->with('error', 'Akses ditolak.');
        }

        if ($pegawai->signature_path && Storage::disk('public')->exists($pegawai->signature_path)) {
            Storage::disk('public')->delete($pegawai->signature_path);
        }

        $pegawai->update([
            'signature_path' => null,
            'signature_updated_at' => null,
        ]);

        return redirect()->back()->with('success', "Tanda tangan digital untuk {$pegawai->nama} berhasil direset.");
    }

    /**
     * Cetak SPK / Kontrak Kerja Universal Pegawai
     */
    public function cetakKontrak(Pegawai $pegawai)
    {
        $pegawai->load('divisi');
        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        return view('admin.pegawai.cetak-kontrak', compact('pegawai', 'kepala'));
    }

    /**
     * Cetak SPK / Kontrak Kerja Massal Per Kantor Klien (Site Area)
     */
    public function cetakKontrakMassal(Request $request)
    {
        $currentUser = auth()->user();
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

        if ($site = $request->input('site') ?: $request->input('department')) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        if ($status = $request->input('status_karyawan')) {
            $query->where('status_karyawan', $status);
        }

        $pegawais = $query->get();

        if ($pegawais->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada pegawai yang ditemukan untuk dicetak kontrak massal.');
        }

        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        $siteName = $site ?: 'Semua Kantor Klien';

        return view('admin.pegawai.cetak-kontrak-massal', compact('pegawais', 'kepala', 'siteName'));
    }

    /**
     * Cetak Surat Keterangan Bekerja (SKK) Individual
     */
    public function cetakSkk(Pegawai $pegawai)
    {
        $pegawai->load('divisi');
        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        return view('admin.pegawai.cetak-skk', compact('pegawai', 'kepala'));
    }

    /**
     * Cetak Surat Keterangan Bekerja (SKK) Massal Per Kantor Klien
     */
    public function cetakSkkMassal(Request $request)
    {
        $currentUser = auth()->user();
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

        if ($site = $request->input('site') ?: $request->input('department')) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->get();

        if ($pegawais->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada pegawai yang ditemukan untuk dicetak surat keterangan kerja massal.');
        }

        $kepala = Pegawai::where('role', 'kepala_isw')
            ->orWhere('id', auth()->id())
            ->first() ?: (Pegawai::where('is_admin', true)->first() ?: auth()->user());

        $siteName = $site ?: 'Semua Kantor Klien';

        return view('admin.pegawai.cetak-skk-massal', compact('pegawais', 'kepala', 'siteName'));
    }

    /**
     * Cetak Data Diri Seluruh Pegawai (printable view)
     */
    public function cetakDataAll(Request $request)
    {
        $currentUser = auth()->user();
        $query = Pegawai::with('divisi')->orderBy('nama');

        if ($currentUser->isDivisionAdmin()) {
            if ($currentUser->area_kerja) {
                $query->where('area_kerja', 'like', "%{$currentUser->area_kerja}%");
            } elseif ($currentUser->divisi_id) {
                $query->where('divisi_id', $currentUser->divisi_id);
            }
        }

        if ($site = $request->input('site')) {
            $query->where('area_kerja', 'like', "%{$site}%");
        }

        $pegawais = $query->get();

        return view('admin.pegawai.cetak-data-all', compact('pegawais'));
    }
}
