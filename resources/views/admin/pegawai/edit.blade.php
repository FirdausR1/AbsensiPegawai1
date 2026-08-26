@extends('layouts.app')

@section('title', 'Edit Pegawai - Panel Admin')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('admin.pegawai.index') }}" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Daftar Pegawai
                </a>
                <span>/</span>
                <span class="text-slate-700 font-bold">Edit Pegawai</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">✏️ Edit Pegawai: {{ $pegawai->nama }}</h1>
        </div>

        <!-- Prominent Back Button -->
        <a href="{{ route('admin.pegawai.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Daftar
        </a>
    </div>

    <!-- Tanda Tangan Card & Reset -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Tanda Tangan Digital</h3>
            <div class="mt-1 flex items-center gap-2">
                @if($pegawai->hasSignature())
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span>✓</span> Tanda Tangan Tersimpan
                    </span>
                    <span class="text-xs text-slate-400">
                        ({{ $pegawai->signature_updated_at ? $pegawai->signature_updated_at->format('d/m/Y H:i') : '-' }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                        <span>✕</span> Belum Membuat TTD
                    </span>
                @endif
            </div>
        </div>

        @if($pegawai->hasSignature())
            <div class="flex items-center gap-3">
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2 h-14 w-28 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="TTD" class="max-h-full max-w-full object-contain">
                </div>
                <form method="POST" action="{{ route('admin.pegawai.reset-signature', $pegawai) }}"
                      onsubmit="return confirm('Apakah Anda yakin ingin mereset tanda tangan pegawai ini? Pegawai harus tanda tangan ulang sebelum absen.');">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-xl transition">
                        Reset TTD
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-2xl">
                <div class="font-bold mb-1">Terjadi kesalahan input:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pegawai.update', $pegawai) }}" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Lengkap Pegawai <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $pegawai->nama) }}" required autofocus
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alamat Email (Untuk Login) <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $pegawai->email) }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Password Baru (Opsional)
                </label>
                <input type="password" name="password" id="password"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm"
                       placeholder="Kosongkan jika tidak ingin mengubah password">
                <p class="text-[11px] text-slate-400 mt-1">Isi hanya jika ingin mereset password akun pegawai ini.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="area_kerja" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Area Kerja / Bagian
                    </label>
                    <input type="text" name="area_kerja" id="area_kerja" value="{{ old('area_kerja', $pegawai->area_kerja) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm">
                </div>

                <div>
                    <label for="sheet_tab_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Tab Google Sheet
                    </label>
                    <input type="text" name="sheet_tab_name" id="sheet_tab_name" value="{{ old('sheet_tab_name', $pegawai->sheet_tab_name) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm">
                    <p class="text-[11px] text-slate-400 mt-1">Tab Google Sheet yang akan dituju saat mencatat absensi.</p>
                </div>
            </div>

            <!-- Is Admin Checkbox -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex items-start gap-3">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin', $pegawai->is_admin) ? 'checked' : '' }}
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_admin" class="text-xs text-amber-950 select-none">
                    <span class="font-bold">Hak Akses Administrator (Admin)</span>
                    <p class="text-amber-700 text-[11px] mt-0.5">Pegawai ini akan dapat membuka seluruh menu panel admin.</p>
                </label>
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.pegawai.index') }}"
                   class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-900 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition">
                    Batal
                </a>
                <button type="submit"
                        class="px-6 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-md shadow-indigo-200 flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
