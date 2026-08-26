@extends('layouts.app')

@section('title', 'Edit Profil & Data Diri')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Dashboard
                </a>
                <span>/</span>
                <span class="text-slate-900 font-bold">Profil Saya</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">👤 Edit Data Diri Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Perbarui nama lengkap, email, area kerja, dan kata sandi akun Anda.</p>
        </div>

        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm self-start">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm space-y-8">
        <!-- Profile Banner -->
        <div class="flex items-center gap-4 pb-6 border-b border-slate-100">
            <div class="w-16 h-16 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl font-extrabold shadow-md shadow-indigo-100 shrink-0">
                {{ strtoupper(substr($pegawai->nama, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-slate-900 truncate">{{ $pegawai->nama }}</h2>
                    @if($pegawai->is_admin)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800">ADMIN</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">PEGAWAI</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 truncate mt-0.5">{{ $pegawai->email }} &bull; {{ $pegawai->area_kerja ?: 'Belum diisi' }}</p>
            </div>
        </div>

        <!-- Form Edit Data Diri -->
        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Informasi Utama -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>📋</span> Informasi Akun & Identitas
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Lengkap -->
                    <div class="sm:col-span-2">
                        <label for="nama" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama" id="nama"
                               value="{{ old('nama', $pegawai->nama) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('nama') border-rose-500 ring-rose-200 @enderror"
                               placeholder="Contoh: Firdaus Romandhanu">
                        @error('nama')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Alamat Email (Untuk Login) <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" id="email"
                               value="{{ old('email', $pegawai->email) }}" required
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('email') border-rose-500 ring-rose-200 @enderror"
                               placeholder="nama@perusahaan.com">
                        @error('email')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Area Kerja -->
                    <div>
                        <label for="area_kerja" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Divisi / Area Kerja
                        </label>
                        <input type="text" name="area_kerja" id="area_kerja"
                               value="{{ old('area_kerja', $pegawai->area_kerja) }}"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('area_kerja') border-rose-500 ring-rose-200 @enderror"
                               placeholder="Contoh: Operasional / Lapangan">
                        @error('area_kerja')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Section 2: Ganti Password (Opsional) -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <span>🔒</span> Ganti Kata Sandi
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kosongkan jika tidak ingin mengubah kata sandi akun Anda.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Password Baru -->
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Kata Sandi Baru
                        </label>
                        <input type="password" name="password" id="password"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition @error('password') border-rose-500 ring-rose-200 @enderror"
                               placeholder="Minimal 6 karakter">
                        @error('password')
                            <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Konfirmasi Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">
                            Ulangi Kata Sandi Baru
                        </label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                               placeholder="Ketik ulang sandi baru">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                <a href="{{ route('dashboard') }}"
                   class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-md shadow-indigo-200 flex items-center gap-2">
                    <span>💾</span> Simpan Perubahan Profil
                </button>
            </div>
        </form>
    </div>

    <!-- Section 3: Status Tanda Tangan Digital -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>✍️</span> Tanda Tangan Digital Saya
                </h3>
                <p class="text-xs text-slate-500">
                    Tanda tangan ini akan otomatis ditempel pada lembar Excel absensi Anda saat absen masuk dan pulang.
                </p>
            </div>

            <a href="{{ route('profile.signature') }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded-xl transition self-start sm:self-auto">
                {{ $pegawai->hasSignature() ? '🔄 Buat Ulang Tanda Tangan' : '➕ Buat Tanda Tangan Sekarang' }}
            </a>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            @if ($pegawai->hasSignature())
                <div class="flex items-center gap-4 bg-slate-50 border border-slate-200 rounded-2xl p-4 max-w-sm">
                    <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="Tanda Tangan"
                         class="h-14 bg-white border border-slate-200 rounded-lg p-1 object-contain">
                    <div>
                        <div class="text-xs font-bold text-emerald-700 flex items-center gap-1">
                            <span>✅</span> Tersimpan & Aktif
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">
                            Update: {{ $pegawai->signature_updated_at ? $pegawai->signature_updated_at->format('d/m/Y H:i') : '-' }}
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-700 flex items-center gap-2">
                    <span>⚠️</span> Anda belum memiliki tanda tangan digital. Silakan klik tombol di atas untuk membuatnya.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
