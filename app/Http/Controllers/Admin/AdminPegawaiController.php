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

            $query->where(function ($q) use ($divisiId, $area) {
                if ($divisiId) {
                    $q->where('divisi_id', $divisiId);
                }
                if ($area) {
                    $q->orWhere('area_kerja', 'like', "%{$area}%");
                }
            });
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

        $pegawais = $query->orderBy('nama', 'asc')->paginate(15)->withQueryString();

        $statsQuery = Pegawai::query();
        if ($currentUser->isDivisionAdmin()) {
            $statsQuery->where(function ($q) use ($currentUser) {
                if ($currentUser->divisi_id) {
                    $q->where('divisi_id', $currentUser->divisi_id);
                }
                if ($currentUser->area_kerja) {
                    $q->orWhere('area_kerja', 'like', "%{$currentUser->area_kerja}%");
                }
            });
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'has_signature' => (clone $statsQuery)->whereNotNull('signature_path')->where('signature_path', '!=', '')->count(),
            'admin' => (clone $statsQuery)->where(function ($q) {
                $q->where('is_admin', true)->orWhereIn('role', ['super_admin', 'admin_divisi']);
            })->count(),
        ];

        return view('admin.pegawai.index', compact('pegawais', 'stats', 'currentUser'));
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

        return view('admin.pegawai.create', compact('divisis', 'currentUser'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();

        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:pegawais'],
            'password' => ['required', Rules\Password::defaults()],
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'divisi_id' => ['nullable', 'exists:divisis,id'],
            'role' => ['nullable', 'in:staff,admin_divisi,super_admin'],
            'sheet_tab_name' => ['nullable', 'string', 'max:255'],
        ]);

        $divisiId = $request->divisi_id;
        $role = $request->input('role', 'staff');

        // Jika dibuat oleh Admin Divisi, kunci ke divisinya dan batasi role
        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id ?: $divisiId;
            $role = 'staff';
        }

        $divisiName = $request->area_kerja;
        if ($divisiId) {
            $divObj = Divisi::find($divisiId);
            if ($divObj) {
                $divisiName = $divObj->nama;
            }
        }

        $isAdmin = ($role === 'super_admin' || $role === 'admin_divisi');

        Pegawai::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_kerja' => $divisiName,
            'divisi_id' => $divisiId,
            'role' => $role,
            'is_admin' => $isAdmin,
            'sheet_tab_name' => $request->sheet_tab_name ?: $request->nama,
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

        return view('admin.pegawai.edit', compact('pegawai', 'divisis', 'currentUser'));
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
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'divisi_id' => ['nullable', 'exists:divisis,id'],
            'role' => ['nullable', 'in:staff,admin_divisi,super_admin'],
            'sheet_tab_name' => ['nullable', 'string', 'max:255'],
        ]);

        $divisiId = $request->divisi_id;
        $role = $currentUser->isSuperAdmin() ? $request->input('role', $pegawai->role ?: 'staff') : ($pegawai->role ?: 'staff');

        if ($currentUser->isDivisionAdmin()) {
            $divisiId = $currentUser->divisi_id ?: $pegawai->divisi_id;
        }

        $divisiName = $request->area_kerja;
        if ($divisiId) {
            $divObj = Divisi::find($divisiId);
            if ($divObj) {
                $divisiName = $divObj->nama;
            }
        }

        $isAdmin = ($role === 'super_admin' || $role === 'admin_divisi');

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'area_kerja' => $divisiName,
            'divisi_id' => $divisiId,
            'role' => $role,
            'is_admin' => $isAdmin,
            'sheet_tab_name' => $request->sheet_tab_name ?: $request->nama,
        ];

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
            return back()->with('error', 'Akses ditolak.');
        }

        if ($pegawai->signature_path && Storage::disk('public')->exists($pegawai->signature_path)) {
            Storage::disk('public')->delete($pegawai->signature_path);
        }

        $pegawai->update([
            'signature_path' => null,
            'signature_updated_at' => null,
        ]);

        return back()->with('success', "Tanda tangan digital {$pegawai->nama} berhasil direset.");
    }
}
