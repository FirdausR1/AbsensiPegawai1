<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
        $query = Pegawai::query();

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

        $stats = [
            'total' => Pegawai::count(),
            'has_signature' => Pegawai::whereNotNull('signature_path')->where('signature_path', '!=', '')->count(),
            'with_signature' => Pegawai::whereNotNull('signature_path')->where('signature_path', '!=', '')->count(),
            'admin' => Pegawai::where('is_admin', true)->count(),
            'admins' => Pegawai::where('is_admin', true)->count(),
        ];

        return view('admin.pegawai.index', compact('pegawais', 'stats'));
    }

    public function create()
    {
        return view('admin.pegawai.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:pegawais'],
            'password' => ['required', Rules\Password::defaults()],
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'sheet_tab_name' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        Pegawai::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'area_kerja' => $request->area_kerja,
            'sheet_tab_name' => $request->sheet_tab_name ?: $request->nama,
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()->route('admin.pegawai.index')->with('success', "Pegawai {$request->nama} berhasil ditambahkan.");
    }

    public function edit(Pegawai $pegawai)
    {
        return view('admin.pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('pegawais')->ignore($pegawai->id)],
            'password' => ['nullable', Rules\Password::defaults()],
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'sheet_tab_name' => ['nullable', 'string', 'max:255'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $data = [
            'nama' => $request->nama,
            'email' => $request->email,
            'area_kerja' => $request->area_kerja,
            'sheet_tab_name' => $request->sheet_tab_name ?: $request->nama,
            'is_admin' => $request->boolean('is_admin'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $pegawai->update($data);

        return redirect()->route('admin.pegawai.index')->with('success', "Data pegawai {$pegawai->nama} berhasil diperbarui.");
    }

    public function destroy(Pegawai $pegawai)
    {
        if ($pegawai->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
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
