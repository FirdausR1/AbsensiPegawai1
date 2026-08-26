<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Tampilkan form edit data diri pengguna yang sedang login.
     */
    public function showProfile()
    {
        $pegawai = Auth::user();
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = \Carbon\Carbon::now()->endOfMonth()->toDateString();

        $stats = [
            'total_bulan_ini' => \App\Models\Absensi::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereNotNull('jam_masuk')
                ->count(),
            'total_lengkap' => \App\Models\Absensi::where('pegawai_id', $pegawai->id)
                ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
                ->whereNotNull('jam_masuk')
                ->whereNotNull('jam_pulang')
                ->count(),
        ];

        return view('profile.edit', compact('pegawai', 'stats'));
    }

    /**
     * Simpan perubahan data diri (Nama, Email, Area Kerja, Password).
     */
    public function updateProfile(Request $request)
    {
        $pegawai = Auth::user();

        $request->validate([
            'nama'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', Rule::unique('pegawais')->ignore($pegawai->id)],
            'area_kerja' => ['nullable', 'string', 'max:255'],
            'password'   => ['nullable', 'string', 'min:6', 'confirmed'],
            'foto'       => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
        ], [
            'nama.required'      => 'Nama lengkap wajib diisi.',
            'email.required'     => 'Alamat email wajib diisi.',
            'email.unique'       => 'Alamat email ini sudah digunakan oleh akun lain.',
            'password.min'       => 'Password baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'foto.image'         => 'File yang diupload harus berupa gambar.',
            'foto.max'           => 'Ukuran foto maksimal 4MB.',
        ]);

        $updateData = [
            'nama'       => $request->nama,
            'email'      => $request->email,
            'area_kerja' => $request->area_kerja,
        ];

        // Handle foto upload
        if ($request->hasFile('foto')) {
            if ($pegawai->foto_path && Storage::disk('public')->exists($pegawai->foto_path)) {
                Storage::disk('public')->delete($pegawai->foto_path);
            }
            $updateData['foto_path'] = $request->file('foto')->store('avatars', 'public');
        }

        // Jika sheet_tab_name belum diubah admin, default ikuti nama baru
        if (empty($pegawai->sheet_tab_name) || $pegawai->sheet_tab_name === $pegawai->getOriginal('nama')) {
            $updateData['sheet_tab_name'] = $request->nama;
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $pegawai->update($updateData);

        return back()->with('success', 'Data profil Anda berhasil diperbarui.');
    }

    public function edit()
    {
        return view('profile.signature', ['pegawai' => Auth::user()]);
    }

    /**
     * Menerima gambar base64 dari signature_pad.js (dikirim lewat canvas.toDataURL())
     * dan menyimpannya sebagai file PNG di storage publik.
     */
    public function saveSignature(Request $request)
    {
        $request->validate([
            'signature_data' => 'required|string',
        ]);

        $pegawai = Auth::user();

        // signature_data formatnya: "data:image/png;base64,iVBORw0KG..."
        [, $encoded] = explode(',', $request->signature_data, 2);
        $imageContent = base64_decode($encoded);

        $filename = 'signatures/' . $pegawai->id . '_' . Str::random(8) . '.png';
        Storage::disk('public')->put($filename, $imageContent);

        // Hapus tanda tangan lama supaya tidak menumpuk file
        if ($pegawai->signature_path) {
            Storage::disk('public')->delete($pegawai->signature_path);
        }

        $pegawai->update([
            'signature_path' => $filename,
            'signature_updated_at' => now(),
        ]);

        return back()->with('success', 'Tanda tangan digital berhasil disimpan.');
    }
}
