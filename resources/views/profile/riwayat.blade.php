@extends('layouts.app')

@section('title', 'Riwayat Absensi Saya - ' . $pegawai->nama)

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
                <span class="text-slate-900 font-bold">Hasil Absensi Saya</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">📅 Hasil & Riwayat Absensi Saya</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Daftar absensi pribadi Anda untuk periode <strong>{{ $carbonMonth->translatedFormat('F Y') }}</strong> (Tab Sheet: <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-indigo-700 font-bold">{{ $pegawai->sheetTabName() }}</code>).
            </p>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali
            </a>

            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>

            {{-- Export Excel untuk bulan yang sedang dilihat --}}
            <a href="{{ route('export.sendiri', $carbonMonth->format('Y-m')) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-white bg-green-600 hover:bg-green-700 rounded-xl transition shadow-sm">
                📥 Export Excel (.xlsx)
            </a>
        </div>
    </div>

    <!-- Filter & Summary Bar -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Month Selector Form -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm md:col-span-1 flex flex-col justify-between">
            <form method="GET" action="{{ route('absen.riwayat') }}">
                <label for="month" class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Pilih Bulan & Tahun</label>
                <div class="flex gap-2">
                    <input type="month" name="month" id="month" value="{{ $selectedMonth }}"
                           class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 outline-none bg-slate-50 font-bold text-slate-800">
                    <button type="submit" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm transition">
                        Lihat
                    </button>
                </div>
            </form>
            <div class="text-[11px] text-slate-400 mt-2">
                Pegawai: <strong class="text-slate-700">{{ $pegawai->nama }}</strong>
            </div>
        </div>

        <!-- Stat: Total Hadir -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500 uppercase">Total Kehadiran</div>
                <div class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $summary['total_hadir'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">Dari {{ $summary['total_hari_kerja'] }} hari kerja</div>
            </div>
            <div class="w-12 h-12 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-2xl">
                ☀️
            </div>
        </div>

        <!-- Stat: Total Absen Pulang -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500 uppercase">Absen Pulang Lengkap</div>
                <div class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $summary['total_pulang'] }} <span class="text-xs font-semibold text-slate-400">Hari</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ $summary['total_hadir'] - $summary['total_pulang'] }} belum absen pulang</div>
            </div>
            <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                🌙
            </div>
        </div>

        <!-- Stat: Status Tanda Tangan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-slate-500 uppercase">Tanda Tangan Digital</div>
                <div class="mt-1">
                    @if($pegawai->hasSignature())
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                            <span>✓</span> Aktif Digunakan
                        </span>
                    @else
                        <a href="{{ route('profile.signature') }}" class="inline-flex items-center gap-1 text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-1 rounded-full border border-rose-200 hover:bg-rose-100">
                            <span>✕</span> Belum Dibuat
                        </a>
                    @endif
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Otomatis masuk ke Sheet</div>
            </div>
            <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-2xl">
                ✍️
            </div>
        </div>
    </div>

    <!-- Monthly Sheet-Style Table Card -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-800">
                    Tabel Daftar Hadir Tenaga Kerja &bull; {{ $carbonMonth->translatedFormat('F Y') }}
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Nama: <strong>{{ $pegawai->nama }}</strong> | Area Kerja: <strong>{{ $pegawai->area_kerja ?: 'Operasional' }}</strong>
                </p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-white border border-slate-200 rounded-lg text-slate-600">
                Total {{ count($monthlyDays) }} Hari
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100/90 text-slate-700 uppercase text-[11px] font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-4 py-3.5">Hari, Tanggal</th>
                        <th class="px-4 py-3.5 text-center">Jam Masuk</th>
                        <th class="px-4 py-3.5 text-center">TTD Masuk</th>
                        <th class="px-4 py-3.5 text-center">Jam Pulang</th>
                        <th class="px-4 py-3.5 text-center">TTD Pulang</th>
                        <th class="px-4 py-3.5">Keterangan</th>
                        <th class="px-4 py-3.5 text-center">Sync Sheet</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($monthlyDays as $item)
                        @php
                            $abs = $item['absensi'];
                            $rowBg = '';
                            if ($item['is_today']) {
                                $rowBg = 'bg-indigo-50/50 font-medium';
                            } elseif ($item['is_weekend']) {
                                $rowBg = 'bg-slate-50/60 text-slate-400';
                            } elseif ($item['is_holiday']) {
                                $rowBg = 'bg-rose-50/40 text-rose-500';
                            }
                        @endphp
                        <tr class="{{ $rowBg }} hover:bg-slate-50 transition">
                            <!-- No (Tanggal) -->
                            <td class="px-4 py-3 text-center font-bold text-slate-700 {{ $item['is_today'] ? 'text-indigo-600' : '' }}">
                                {{ $item['day'] }}
                            </td>

                            <!-- Tanggal & Hari -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                    {{ $item['date']->translatedFormat('l') }}
                                    @if($item['is_today'])
                                        <span class="px-1.5 py-0.2 rounded-full text-[9px] font-bold bg-indigo-600 text-white">Hari Ini</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $item['date']->translatedFormat('d F Y') }}
                                </div>
                            </td>

                            <!-- Jam Masuk -->
                            <td class="px-4 py-3 text-center font-mono font-bold whitespace-nowrap">
                                @if($abs && $abs->jam_masuk)
                                    <span class="text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg border border-emerald-200">
                                        {{ substr($abs->jam_masuk, 0, 5) }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- TTD Masuk -->
                            <td class="px-4 py-3 text-center">
                                @if($abs && $abs->jam_masuk && $pegawai->hasSignature())
                                    <div class="inline-flex items-center justify-center p-1 bg-white border border-slate-200 rounded-lg shadow-sm">
                                        <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="TTD" class="h-6 w-12 object-contain">
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Jam Pulang -->
                            <td class="px-4 py-3 text-center font-mono font-bold whitespace-nowrap">
                                @if($abs && $abs->jam_pulang)
                                    <span class="text-slate-800 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">
                                        {{ substr($abs->jam_pulang, 0, 5) }}
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <!-- TTD Pulang -->
                            <td class="px-4 py-3 text-center">
                                @if($abs && $abs->jam_pulang && $pegawai->hasSignature())
                                    <div class="inline-flex items-center justify-center p-1 bg-white border border-slate-200 rounded-lg shadow-sm">
                                        <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="TTD" class="h-6 w-12 object-contain">
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>

                            <!-- Keterangan -->
                            <td class="px-4 py-3">
                                @if($item['is_holiday'])
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200">
                                        🎉 {{ $item['holiday_reason'] ?: 'Libur Nasional' }}
                                    </span>
                                @elseif($item['is_weekend'])
                                    <span class="text-slate-400 text-[11px] italic">Akhir Pekan</span>
                                @elseif($abs && $abs->keterangan)
                                    <span class="text-slate-700 font-medium">{{ $abs->keterangan }}</span>
                                @elseif($abs && $abs->jam_masuk && $abs->jam_pulang)
                                    <span class="text-emerald-600 font-semibold text-[11px]">✓ Hadir Lengkap</span>
                                @elseif($abs && $abs->jam_masuk)
                                    <span class="text-amber-600 font-semibold text-[11px]">Sedang Bekerja</span>
                                @elseif($item['is_future'])
                                    <span class="text-slate-300 text-[11px]">-</span>
                                @else
                                    <span class="text-slate-400 text-[11px]">-</span>
                                @endif
                            </td>

                            <!-- Status Sync Sheet -->
                            <td class="px-4 py-3 text-center">
                                @if($abs && $abs->synced_to_sheet)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="Data tersimpan di Google Sheet">
                                        <span>✓</span> Sync
                                    </span>
                                @elseif($abs && $abs->jam_masuk)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200" title="{{ $abs->sync_error ?: 'Gagal sync' }}">
                                        <span>✕</span> Pending
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Back to Dashboard Footer Button -->
    <div class="flex justify-start">
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 px-4 py-2.5 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard Utama
        </a>
    </div>
</div>
@endsection
