@extends('layouts.app')

@section('title', 'Tambah Pegawai Baru - Panel Admin')

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
                <span class="text-slate-700 font-bold">Tambah Pegawai</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">👤 Tambah Pegawai Baru</h1>
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

    <!-- Form Card -->
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

        <form method="POST" action="{{ route('admin.pegawai.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="nama" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Nama Lengkap Pegawai <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required autofocus
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm"
                       placeholder="Contoh: Budi Santoso">
            </div>

            <div>
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Alamat Email (Untuk Login) <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm"
                       placeholder="budi@perusahaan.com">
            </div>

            <div>
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Password Awal <span class="text-rose-500">*</span>
                </label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm"
                       placeholder="Minimal 8 karakter (contoh: password123)">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="area_kerja" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Area Kerja / Bagian
                    </label>
                    <input type="text" name="area_kerja" id="area_kerja" value="{{ old('area_kerja', 'Operasional') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm"
                           placeholder="Contoh: Operasional / Lapangan">
                </div>

                <div>
                    <label for="sheet_tab_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Nama Tab Google Sheet
                    </label>
                    <input type="text" name="sheet_tab_name" id="sheet_tab_name" value="{{ old('sheet_tab_name') }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-xs sm:text-sm"
                           placeholder="Kosongkan jika sama dengan Nama">
                    <p class="text-[11px] text-slate-400 mt-1">Jika dikosongkan, nama tab otomatis disamakan dengan Nama Lengkap.</p>
                </div>
            </div>

            <!-- Is Admin Checkbox -->
            <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex items-start gap-3">
                <input type="checkbox" name="is_admin" id="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}
                       class="mt-1 h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_admin" class="text-xs text-amber-950 select-none">
                    <span class="font-bold">Berikan Hak Akses Administrator (Admin)</span>
                    <p class="text-amber-700 text-[11px] mt-0.5">Admin dapat mengelola data semua pegawai, mereset tanda tangan, dan melihat log absensi keseluruhan.</p>
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
                    Simpan Pegawai Baru
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
