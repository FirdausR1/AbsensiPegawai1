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
     * Tampilkan halaman profil & data diri pengguna yang sedang login.
     */
    public function showProfile()
    {
        $pegawai = Auth::user();
        return view('profile.edit', compact('pegawai'));
    }

    /**
     * Simpan perubahan data diri lengkap (biodata).
     */
    public function updateProfile(Request $request)
    {
        $pegawai = Auth::user();

        $request->validate([
            'nama'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'email', 'max:255', Rule::unique('pegawais')->ignore($pegawai->id)],
            'area_kerja'          => ['nullable', 'string', 'max:255'],
            'foto'                => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'nik'                 => ['nullable', 'string', 'max:20'],
            'tempat_lahir'        => ['nullable', 'string', 'max:255'],
            'tanggal_lahir'       => ['nullable', 'date'],
            'jenis_kelamin'       => ['nullable', 'string', 'max:20'],
            'alamat'              => ['nullable', 'string'],
            'pendidikan_terakhir' => ['nullable', 'string', 'max:50'],
            'no_hp'               => ['nullable', 'string', 'max:50'],
            'status_pernikahan'   => ['nullable', 'string', 'max:30'],
            'kontak_darurat'      => ['nullable', 'string', 'max:255'],
            'nama_bank'           => ['nullable', 'string', 'max:50'],
            'nomor_rekening'      => ['nullable', 'string', 'max:50'],
            'nama_rekening'       => ['nullable', 'string', 'max:150'],
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah digunakan akun lain.',
            'foto.max' => 'Ukuran foto maksimal 4MB.',
        ]);

        $updateData = [
            'nama'                => $request->nama,
            'email'               => $request->email,
            'area_kerja'          => $request->area_kerja,
            'nik'                 => $request->nik,
            'tempat_lahir'        => $request->tempat_lahir,
            'tanggal_lahir'       => $request->tanggal_lahir,
            'jenis_kelamin'       => $request->jenis_kelamin,
            'alamat'              => $request->alamat,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'no_hp'               => $request->no_hp,
            'status_pernikahan'   => $request->status_pernikahan,
            'kontak_darurat'      => $request->kontak_darurat,
            'nama_bank'           => $request->nama_bank,
            'nomor_rekening'      => $request->nomor_rekening,
            'nama_rekening'       => $request->nama_rekening,
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

        $pegawai->update($updateData);

        return back()->with('success', 'Data diri berhasil diperbarui.');
    }

    /**
     * Form ganti password.
     */
    public function showChangePassword()
    {
        return view('profile.ganti-password');
    }

    /**
     * Proses ganti password (wajib input password lama).
     */
    public function updatePassword(Request $request)
    {
        $pegawai = Auth::user();

        $request->validate([
            'password_lama'           => ['required', 'string'],
            'password_baru'           => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password_lama.required'       => 'Password lama wajib diisi.',
            'password_baru.required'       => 'Password baru wajib diisi.',
            'password_baru.min'            => 'Password baru minimal 6 karakter.',
            'password_baru.confirmed'      => 'Konfirmasi password baru tidak cocok.',
        ]);

        if (!Hash::check($request->password_lama, $pegawai->password)) {
            return back()->with('error', 'Password lama salah.');
        }

        $pegawai->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }

    /**
     * Halaman tanda tangan digital.
     */
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
