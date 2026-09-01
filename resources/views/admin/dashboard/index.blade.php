@extends('layouts.app')

@section('title', 'Admin Dashboard & Executive Monitor - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard User</a>
                <span>/</span>
                <span class="text-slate-500">Admin</span>
                <span>/</span>
                <span class="text-slate-800 font-bold">Executive Monitor</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Dashboard & Monitoring Kehadiran</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Pantau siapa yang Alpa, Izin/Cuti, Terlambat, dan Hadir pada tanggal <strong class="text-slate-800">{{ $carbonDate->translatedFormat('d F Y (l)') }}</strong>.
            </p>
        </div>

        <!-- Download & Filter Quick Action -->
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.export.semua', ['bulan' => $carbonDate->format('Y-m')]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Download ZIP Absen Bulan Ini
            </a>
            <a href="{{ route('admin.rekap.index', ['bulan' => $carbonDate->format('Y-m')]) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-sm transition">
                Rekap Per Pegawai
            </a>
        </div>
    </div>

    <!-- Filter Bar Executive Monitor -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex flex-wrap items-center gap-3 flex-1">
                <div>
                    <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Tanggal Monitor</label>
                    <input type="date" name="date" value="{{ $dateStr }}" onchange="this.form.submit()"
                           class="px-3 py-2 rounded-lg border border-slate-300 font-bold text-slate-800 bg-white">
                </div>

                <div class="min-w-[220px]">
                    <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Kantor Klien / Site Project</label>
                    <select name="site" onchange="this.form.submit()" class="w-full px-3 py-2 rounded-lg border border-slate-300 font-medium text-slate-800 bg-white">
                        <option value="">Semua Kantor Klien</option>
                        @foreach($sites as $st)
                            <option value="{{ $st }}" {{ isset($site) && $site == $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if(request('site') || request('date'))
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200 inline-block">
                        Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Card Pegawai Paling Rajin Bulan Ini -->
    @if(isset($mostDiligent) && $mostDiligent->pegawai)
        <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-[#000d6b] text-white rounded-2xl p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center text-sm font-bold shrink-0 shadow-sm">
                        TOP
                    </div>
                    <div>
                        <div class="inline-block px-2.5 py-0.5 bg-amber-300 text-amber-950 text-[10px] font-extrabold uppercase tracking-wider rounded-md mb-1">
                            PEGAWAI TERBAIK {{ isset($site) && $site ? "KANTOR KLIEN: {$site}" : "PERUSAHAAN" }} — {{ $carbonDate->translatedFormat('F Y') }}
                        </div>
                        <h2 class="text-base sm:text-lg font-extrabold tracking-tight">
                            {{ $mostDiligent->pegawai->nama }}
                        </h2>
                        <p class="text-amber-100 text-xs mt-0.5">
                            {{ $mostDiligent->pegawai->area_kerja ?: ($mostDiligent->pegawai->divisi?->nama ?? 'Staff') }} &bull;
                            <strong>{{ $mostDiligent->total_hadir }} Hari Hadir Tepat Waktu</strong> ({{ $mostDiligent->total_hari_terlambat }} Telat) &bull;
                            <strong>{{ $mostDiligent->total_tugas }} Tugas Periodik Dikerjakan</strong>
                        </p>
                    </div>
                </div>

                <div class="bg-white/15 backdrop-blur-md px-4 py-2.5 rounded-xl border border-white/20 text-center sm:text-right shrink-0">
                    <div class="text-[10px] font-bold text-amber-200 uppercase tracking-wider">Skor Kedisiplinan</div>
                    <div class="text-lg font-black text-amber-300">{{ number_format($mostDiligent->skor) }} Poin</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Date Picker & Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Tanggal Monitor</label>
                <input type="date" name="date" value="{{ $dateStr }}"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs font-bold">
            </div>

            <div class="sm:col-span-5">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Nama Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama pegawai..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs">
            </div>

            <div class="sm:col-span-3 sm:self-end">
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2.5 px-3 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition">
                        Tampilkan Data
                    </button>
                    @if(request('search') || request('date') !== date('Y-m-d'))
                        <a href="{{ route('admin.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Live Attendance Stats Grid (Blue & White Theme) -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <!-- ALPA Card -->
        <a href="#section-alpa" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-[#000d6b] transition group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[#000d6b] uppercase tracking-wider">ALPA / Belum Absen</span>
                <span class="w-2.5 h-2.5 rounded-full bg-[#000d6b] animate-pulse"></span>
            </div>
            <div class="text-3xl font-black text-[#000d6b] mt-2">{{ $stats['total_alpa'] }} <span class="text-xs font-semibold text-slate-500">Orang</span></div>
            <p class="text-[10px] text-slate-400 mt-1 group-hover:text-[#000d6b] transition">Klik untuk lihat daftar pegawai</p>
        </a>

        <!-- Cuti / Izin Card -->
        <a href="#section-cuti" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-[#000d6b] transition group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[#000d6b] uppercase tracking-wider">Izin / Cuti Resmi</span>
                <span class="text-xs">📅</span>
            </div>
            <div class="text-3xl font-black text-[#000d6b] mt-2">{{ $stats['total_cuti'] }} <span class="text-xs font-semibold text-slate-500">Orang</span></div>
            <p class="text-[10px] text-slate-400 mt-1 group-hover:text-[#000d6b] transition">Cuti / Izin disetujui HRD</p>
        </a>

        <!-- Terlambat Card -->
        <a href="#section-terlambat" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-[#000d6b] transition group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[#000d6b] uppercase tracking-wider">Terlambat Masuk</span>
                <span class="text-xs">⏰</span>
            </div>
            <div class="text-3xl font-black text-[#000d6b] mt-2">{{ $stats['total_terlambat'] }} <span class="text-xs font-semibold text-slate-500">Orang</span></div>
            <p class="text-[10px] text-slate-400 mt-1 group-hover:text-[#000d6b] transition">Lewat batas toleransi</p>
        </a>

        <!-- Hadir Tepat Waktu Card -->
        <a href="#section-hadir" class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:border-[#000d6b] transition group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-[#000d6b] uppercase tracking-wider">Hadir Tepat Waktu</span>
                <span class="text-xs">✓</span>
            </div>
            <div class="text-3xl font-black text-[#000d6b] mt-2">{{ $stats['total_tepat'] }} <span class="text-xs font-semibold text-slate-500">Orang</span></div>
            <p class="text-[10px] text-slate-400 mt-1 group-hover:text-[#000d6b] transition">Absen sesuai jam shift</p>
        </a>

        <!-- Total Pegawai Card -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</span>
                <span class="text-xs">👥</span>
            </div>
            <div class="text-3xl font-black text-slate-900 mt-2">{{ $stats['total_pegawai'] }} <span class="text-xs font-semibold text-slate-500">Orang</span></div>
            <p class="text-[10px] text-slate-400 mt-1">Tersedia dalam divisi</p>
        </div>
    </div>

    <!-- Data Table 1: DAFTAR PEGAWAI ALPA / BELUM ABSEN -->
    <div id="section-alpa" class="scroll-mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 bg-[#eef2ff]/70 border-b border-indigo-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-[#000d6b]"></span>
                <div>
                    <h2 class="text-base font-extrabold text-[#000d6b]">Daftar Pegawai ALPA / Belum Absen</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai yang memiliki jadwal kerja pada {{ $carbonDate->format('d/m/Y') }} namun belum mencatat absen masuk</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-[#000d6b] text-white text-xs font-extrabold rounded-full">
                {{ $listAlpa->count() }} ALPA
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold">Divisi / Area Kerja</th>
                        <th class="py-3.5 px-5 font-bold">Email</th>
                        <th class="py-3.5 px-5 font-bold">Status Presensi</th>
                        <th class="py-3.5 px-5 font-bold text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($listAlpa as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900 text-sm">{{ $item->pegawai->nama }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->pegawai->sheetTabName() }}</div>
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-800">
                                {{ $item->pegawai->area_kerja ?: ($item->pegawai->divisi?->nama ?? 'Staff') }}
                            </td>
                            <td class="py-4 px-5 text-slate-500 font-mono text-xs">
                                {{ $item->pegawai->email }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-[#eef2ff] text-[#000d6b] border border-indigo-200">
                                    🔴 {{ $item->keterangan }}
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right space-x-2">
                                <a href="{{ route('admin.absensi.index', ['pegawai_id' => $item->pegawai->id, 'date' => $dateStr, 'auto_open' => 1]) }}"
                                   class="inline-block px-3 py-1.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg transition shadow-sm">
                                    + Input Absen Manual
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                🎉 Tidak ada pegawai yang ALPA pada tanggal ini. Semua pegawai hadir atau izin resmi!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Data Table 2: DAFTAR PEGAWAI CUTI / IZIN -->
    <div id="section-cuti" class="scroll-mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 bg-[#eef2ff]/70 border-b border-indigo-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-[#000d6b]"></span>
                <div>
                    <h2 class="text-base font-extrabold text-[#000d6b]">Daftar Pegawai Cuti & Izin Resmi</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai yang mengajukan Cuti Tahunan, Sakit, atau Izin Penting yang telah disetujui</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-[#000d6b] text-white text-xs font-extrabold rounded-full">
                {{ $listCuti->count() }} Izin/Cuti
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold">Divisi / Area Kerja</th>
                        <th class="py-3.5 px-5 font-bold">Keterangan / Tipe Cuti</th>
                        <th class="py-3.5 px-5 font-bold">Periode Izin</th>
                        <th class="py-3.5 px-5 font-bold text-right">Alasan / Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($listCuti as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900">
                                {{ $item->pegawai->nama }}
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-800">
                                {{ $item->pegawai->area_kerja ?: ($item->pegawai->divisi?->nama ?? 'Staff') }}
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-[#eef2ff] text-[#000d6b] border border-indigo-200">
                                    📋 {{ $item->keterangan }}
                                </span>
                            </td>
                            <td class="py-4 px-5 whitespace-nowrap text-slate-800 font-medium">
                                @if($item->cuti)
                                    {{ $item->cuti->tanggal_mulai->format('d/m/Y') }} – {{ $item->cuti->tanggal_selesai->format('d/m/Y') }}
                                @else
                                    {{ $carbonDate->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="py-4 px-5 text-right text-slate-500 text-xs">
                                {{ $item->cuti?->alasan ?: 'Izin tercatat' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada pegawai yang Cuti / Izin pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Data Table 3: DAFTAR PEGAWAI TERLAMBAT -->
    <div id="section-terlambat" class="scroll-mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 bg-[#eef2ff]/70 border-b border-indigo-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-[#000d6b]"></span>
                <div>
                    <h2 class="text-base font-extrabold text-[#000d6b]">Daftar Pegawai Terlambat Masuk</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai yang absen masuk melebihi batas jam kerja & toleransi divisi/shift</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-[#000d6b] text-white text-xs font-extrabold rounded-full">
                {{ $listTerlambat->count() }} Terlambat
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold">Divisi / Area</th>
                        <th class="py-3.5 px-5 font-bold">Jam Masuk Faktual</th>
                        <th class="py-3.5 px-5 font-bold">Keterlambatan</th>
                        <th class="py-3.5 px-5 font-bold text-right">Jam Pulang</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($listTerlambat as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900">
                                {{ $item->pegawai->nama }}
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-800">
                                {{ $item->pegawai->area_kerja ?: ($item->pegawai->divisi?->nama ?? 'Staff') }}
                            </td>
                            <td class="py-4 px-5 font-extrabold text-[#000d6b]">
                                {{ $item->jam_masuk }} WIB
                            </td>
                            <td class="py-4 px-5">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-[#eef2ff] text-[#000d6b] border border-indigo-200">
                                    ⏰ Terlambat {{ $item->menit_terlambat }} Menit
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right font-medium">
                                {{ $item->jam_pulang ? $item->jam_pulang . ' WIB' : 'Belum Pulang' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                👏 Luar biasa! Tidak ada pegawai yang terlambat pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Data Table 4: DAFTAR PEGAWAI HADIR TEPAT WAKTU -->
    <div id="section-hadir" class="scroll-mt-6 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 bg-[#eef2ff]/70 border-b border-indigo-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="w-3 h-3 rounded-full bg-[#000d6b]"></span>
                <div>
                    <h2 class="text-base font-extrabold text-[#000d6b]">Daftar Pegawai Hadir Tepat Waktu</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pegawai yang absen masuk sesuai jam kerja divisi/shift</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-[#000d6b] text-white text-xs font-extrabold rounded-full">
                {{ $listHadir->count() }} Hadir
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold">Divisi / Area</th>
                        <th class="py-3.5 px-5 font-bold">Jam Masuk</th>
                        <th class="py-3.5 px-5 font-bold">Jam Pulang</th>
                        <th class="py-3.5 px-5 font-bold text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse($listHadir as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5 font-bold text-slate-900">
                                {{ $item->pegawai->nama }}
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-800">
                                {{ $item->pegawai->area_kerja ?: ($item->pegawai->divisi?->nama ?? 'Staff') }}
                            </td>
                            <td class="py-4 px-5 font-bold text-[#000d6b]">
                                {{ $item->jam_masuk }} WIB
                            </td>
                            <td class="py-4 px-5 font-medium">
                                {{ $item->jam_pulang ? $item->jam_pulang . ' WIB' : 'Belum Pulang' }}
                            </td>
                            <td class="py-4 px-5 text-right">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold bg-[#eef2ff] text-[#000d6b] border border-indigo-200">
                                    ✓ Tepat Waktu
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400 text-xs">
                                Belum ada pegawai yang hadir tepat waktu pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
