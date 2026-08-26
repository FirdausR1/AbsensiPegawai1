@extends('layouts.app')

@section('title', 'Rekap Absensi - Panel Admin')

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
                <span class="text-slate-900 font-bold">Rekap Absensi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">📊 Rekap & Log Absensi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau kehadiran seluruh pegawai, status sinkronisasi Google Sheets, dan input absensi manual.</p>
        </div>

        <!-- Action & Back Buttons -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Dashboard
            </a>

            <button type="button" onclick="document.getElementById('manual-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-md shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Input Absen Manual
            </button>

            {{-- Export semua pegawai bulan ini --}}
            <a href="{{ route('admin.export.semua', now()->format('Y-m')) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-xl transition shadow-md shadow-green-200">
                📥 Export Semua (.zip)
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase">Tercatat Hari Ini</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total_today'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase">Absen Masuk</div>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['masuk_today'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase">Absen Pulang</div>
            <div class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $stats['pulang_today'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
            <div class="text-xs font-bold text-slate-500 uppercase">Gagal Sync Sheet</div>
            <div class="text-2xl font-extrabold text-rose-600 mt-1">{{ $stats['failed_sync'] }}</div>
        </div>
    </div>

    <!-- Filter & Table Container -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="p-4 sm:p-5 border-b border-slate-200 bg-slate-50/50">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Filter Tanggal</label>
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white">
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Filter Pegawai</label>
                    <select name="pegawai_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white">
                        <option value="">Semua Pegawai</option>
                        @foreach($pegawais as $peg)
                            <option value="{{ $peg->id }}" {{ request('pegawai_id') == $peg->id ? 'selected' : '' }}>
                                {{ $peg->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status Sync Sheet</label>
                    <select name="synced" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none bg-white">
                        <option value="">Semua Status</option>
                        <option value="1" {{ request('synced') === '1' ? 'selected' : '' }}>Berhasil Ter-Sync</option>
                        <option value="0" {{ request('synced') === '0' ? 'selected' : '' }}>Gagal / Belum Sync</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs rounded-xl transition shadow-sm">
                        Terapkan Filter
                    </button>
                    @if(request()->hasAny(['date', 'pegawai_id', 'synced']))
                        <a href="{{ route('admin.absensi.index') }}" class="py-2 px-3 bg-white hover:bg-slate-100 text-slate-600 border border-slate-300 font-bold text-xs rounded-xl transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/80 text-slate-600 uppercase text-[11px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Pegawai</th>
                        <th class="px-4 py-3.5">Jam Masuk</th>
                        <th class="px-4 py-3.5">Jam Pulang</th>
                        <th class="px-4 py-3.5 text-center">Sync Sheet</th>
                        <th class="px-5 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($absensis as $abs)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $abs->tanggal ? $abs->tanggal->translatedFormat('d M Y') : '-' }}
                                <div class="text-[11px] font-normal text-slate-400">
                                    {{ $abs->tanggal ? $abs->tanggal->translatedFormat('l') : '' }}
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="font-bold text-slate-900">{{ $abs->pegawai->nama ?? 'Pegawai Terhapus' }}</div>
                                <div class="text-[11px] text-slate-400">Tab: {{ $abs->pegawai->sheet_tab_name ?? '-' }}</div>
                            </td>
                            <td class="px-4 py-4 font-mono font-medium text-emerald-700">
                                {{ $abs->jam_masuk ? substr($abs->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-4 py-4 font-mono font-medium text-slate-700">
                                {{ $abs->jam_pulang ? substr($abs->jam_pulang, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($abs->synced_to_sheet)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>✓</span> Tersinkron
                                    </span>
                                @else
                                    <div class="inline-flex flex-col items-center">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200"
                                              title="{{ $abs->sync_error ?: 'Gagal sinkronisasi' }}">
                                            <span>✕</span> Gagal Sync
                                        </span>
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Retry Sync Button -->
                                    <form method="POST" action="{{ route('admin.absensi.retry-sync', $abs) }}" class="inline">
                                        @csrf
                                        <button type="submit" title="Sinkronkan Ulang ke Google Sheets"
                                                class="px-2.5 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-600 hover:text-white rounded-lg border border-emerald-200 transition">
                                            Sync Ulang
                                        </button>
                                    </form>

                                    <!-- Delete Button -->
                                    <form method="POST" action="{{ route('admin.absensi.destroy', $abs) }}"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan absensi ini?');"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Catatan"
                                                class="px-2.5 py-1.5 text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-lg border border-rose-200 transition">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                                <div class="text-3xl mb-2">📋</div>
                                <p class="text-sm font-semibold">Belum ada riwayat absensi yang tercatat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($absensis->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $absensis->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Input Absen Manual -->
<div id="manual-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-200 space-y-5">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">✍️ Input Absensi Manual</h3>
            <button type="button" onclick="document.getElementById('manual-modal').classList.add('hidden')"
                    class="text-slate-400 hover:text-slate-600 text-lg font-bold">
                ✕
            </button>
        </div>

        <form method="POST" action="{{ route('admin.absensi.manual') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none">
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Tanggal Absensi <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Masuk (HH:MM)</label>
                    <input type="time" name="jam_masuk" value="08:00"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jam Pulang (HH:MM)</label>
                    <input type="time" name="jam_pulang" value="17:00"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan (Opsional)</label>
                <input type="text" name="keterangan" placeholder="Contoh: Izin / Hadir tepat waktu"
                       class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <div class="p-3 bg-indigo-50/70 border border-indigo-200 rounded-xl flex items-center gap-2">
                <input type="checkbox" name="sync_now" id="sync_now" value="1" checked class="h-4 w-4 rounded text-indigo-600">
                <label for="sync_now" class="text-xs text-indigo-950 font-medium select-none">
                    Langsung sinkronkan ke Google Sheet pegawai
                </label>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="document.getElementById('manual-modal').classList.add('hidden')"
                        class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition shadow-md shadow-indigo-200">
                    Simpan Absensi
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
