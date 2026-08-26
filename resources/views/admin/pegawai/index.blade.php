@extends('layouts.app')

@section('title', 'Kelola Pegawai - Panel Admin')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
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
                <span class="text-slate-700">Admin</span>
                <span>/</span>
                <span class="text-slate-900 font-bold">Kelola Pegawai</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">👥 Kelola Data Pegawai</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Tambah, perbarui profil, kelola hak akses admin, dan atur nama tab sheet pegawai.</p>
        </div>

        <!-- Action Buttons with Back Button -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>

            <a href="{{ route('admin.pegawai.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-md shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Pegawai Baru
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500 uppercase">Total Pegawai</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
            </div>
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-xl">
                👥
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500 uppercase">Sudah Buat TTD</div>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['with_signature'] }}</div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-xl">
                ✍️
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500 uppercase">Administrator</div>
                <div class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['admins'] }}</div>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
                ⚙️
            </div>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Search & Filter Bar -->
        <div class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <form method="GET" action="{{ route('admin.pegawai.index') }}" class="flex-1 max-w-md">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cari nama, email, area kerja, atau tab..."
                           class="w-full pl-10 pr-4 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        🔍
                    </div>
                </div>
            </form>

            <div class="flex items-center gap-2">
                @if(request('search'))
                    <a href="{{ route('admin.pegawai.index') }}"
                       class="px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg border border-rose-200 transition">
                        Reset Pencarian
                    </a>
                @endif
                <span class="text-xs text-slate-500">Menampilkan <strong>{{ $pegawais->count() }}</strong> pegawai</span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/80 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-4 py-3.5">Area Kerja</th>
                        <th class="px-4 py-3.5">Tab Sheet</th>
                        <th class="px-4 py-3.5 text-center">Tanda Tangan</th>
                        <th class="px-4 py-3.5 text-center">Role</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pegawais as $p)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-sm">
                                        {{ strtoupper(substr($p->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
                                            {{ $p->nama }}
                                            @if($p->id === auth()->id())
                                                <span class="text-[10px] font-semibold px-1.5 py-0.2 bg-indigo-50 text-indigo-700 rounded-full border border-indigo-200">Anda</span>
                                            @endif
                                        </div>
                                        <div class="text-slate-500 text-[11px]">{{ $p->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4 font-medium text-slate-700">
                                {{ $p->area_kerja ?: '-' }}
                            </td>
                            <td class="px-4 py-4">
                                <span class="inline-flex items-center gap-1 font-mono text-[11px] bg-slate-100 text-slate-800 px-2 py-0.5 rounded-lg border border-slate-200">
                                    <span>📑</span> {{ $p->sheetTabName() }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($p->hasSignature())
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Tersimpan">
                                        <span>✓</span> Ada TTD
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span>✕</span> Belum Ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($p->is_admin)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        ADMIN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        Pegawai
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Edit Button -->
                                    <a href="{{ route('admin.pegawai.edit', $p) }}"
                                       title="Edit Pegawai"
                                       class="px-2.5 py-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-600 hover:text-white rounded-lg border border-indigo-200 transition">
                                        Edit
                                    </a>

                                    <!-- Delete Button -->
                                    @if($p->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.pegawai.destroy', $p) }}"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pegawai {{ $p->nama }}? Seluruh riwayat absensinya juga akan terhapus.');"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Pegawai"
                                                    class="px-2.5 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-lg border border-rose-200 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="text-3xl mb-2">🔍</div>
                                <p class="text-sm font-semibold">Tidak ada data pegawai yang sesuai.</p>
                                <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian lain.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
