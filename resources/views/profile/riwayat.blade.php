@extends('layouts.app')

@section('title', 'Attendance History - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Attendance History</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Attendance History & Recap</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Periodic attendance record for <strong>{{ $carbonMonth->translatedFormat('F Y') }}</strong> &bull; {{ $pegawai->nama }} (ID: ISW-{{ date('Y') }}-{{ str_pad($pegawai->id, 3, '0', STR_PAD_LEFT) }})
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>

            <a href="{{ route('export.sendiri', $carbonMonth->format('Y-m')) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#000d6b] hover:bg-[#001253] rounded-lg transition shadow-sm">
                📥 Export Excel (.xlsx)
            </a>
        </div>
    </div>

    <!-- Filter & Summary Bar -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Month Selector Form -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm md:col-span-1 flex flex-col justify-between">
            <form method="GET" action="{{ route('absen.riwayat') }}">
                <label for="month" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Select Month & Year</label>
                <div class="flex gap-2">
                    <input type="month" name="month" id="month" value="{{ $selectedMonth }}"
                           class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none bg-slate-50 font-bold text-slate-800">
                    <button type="submit" class="px-3.5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white rounded-lg text-xs font-bold shadow-sm transition">
                        View
                    </button>
                </div>
            </form>
            <div class="text-[11px] text-slate-400 mt-3">
                Total Calendar Days: <strong>{{ $monthlyDays ? count($monthlyDays) : 0 }} Days</strong>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Present / Hadir</div>
                <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $summary['total_hadir'] }} <span class="text-xs font-medium text-slate-500">Days</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">Recorded clock-ins</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-lg font-bold">
                ✓
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Clock-Outs / Pulang</div>
                <div class="text-2xl font-extrabold text-[#000d6b] mt-1">{{ $summary['total_pulang'] }} <span class="text-xs font-medium text-slate-500">Days</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">Completed shifts</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-[#eef2ff] text-[#000d6b] flex items-center justify-center text-lg font-bold">
                🏠
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Working Days</div>
                <div class="text-2xl font-extrabold text-slate-800 mt-1">{{ $summary['total_hari_kerja'] }} <span class="text-xs font-medium text-slate-500">Days</span></div>
                <div class="text-[11px] text-slate-400 mt-0.5">Excluding weekends & holidays</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-lg font-bold">
                🏢
            </div>
        </div>
    </div>

    <!-- Monthly Detailed Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="section-bar"></span>
            <h3 class="text-sm font-bold text-slate-900">Daily Log - {{ $carbonMonth->translatedFormat('F Y') }}</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <th class="py-3 px-3 font-semibold text-center w-12">Date</th>
                        <th class="py-3 px-4 font-semibold">Day & Date</th>
                        <th class="py-3 px-4 font-semibold">Clock-In (Masuk)</th>
                        <th class="py-3 px-4 font-semibold">Clock-Out (Pulang)</th>
                        <th class="py-3 px-4 font-semibold">Remarks / Status</th>
                        <th class="py-3 px-4 font-semibold text-center">Signature</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($monthlyDays as $dayItem)
                        @php
                            $dateObj = $dayItem['date'];
                            $absensi = $dayItem['absensi'];
                            $isWeekend = $dayItem['is_weekend'];
                            $isHoliday = $dayItem['is_holiday'];
                            $isToday = $dayItem['is_today'];
                            $isFuture = $dayItem['is_future'];

                            $rowClass = '';
                            if ($isToday) {
                                $rowClass = 'bg-[#eef2ff]/50 font-medium';
                            } elseif ($isWeekend || $isHoliday) {
                                $rowClass = 'bg-slate-50/70 text-slate-400';
                            }
                        @endphp
                        <tr class="{{ $rowClass }} hover:bg-slate-100/60 transition">
                            <td class="py-3 px-3 text-center font-extrabold text-slate-800">
                                @if($isToday)
                                    <span class="w-6 h-6 rounded-full bg-[#000d6b] text-white flex items-center justify-center mx-auto text-xs">
                                        {{ $dayItem['day'] }}
                                    </span>
                                @else
                                    {{ $dayItem['day'] }}
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold {{ $isToday ? 'text-[#000d6b]' : 'text-slate-800' }}">
                                    {{ $dateObj->translatedFormat('l') }}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    {{ $dateObj->translatedFormat('d M Y') }}
                                </div>
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-700">
                                @if($absensi && $absensi->jam_masuk)
                                    {{ substr($absensi->jam_masuk, 0, 5) }} WIB
                                @else
                                    <span class="text-slate-300 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 font-bold text-[#000d6b]">
                                @if($absensi && $absensi->jam_pulang)
                                    {{ substr($absensi->jam_pulang, 0, 5) }} WIB
                                @else
                                    <span class="text-slate-300 font-normal">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($isHoliday)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        {{ $dayItem['holiday_reason'] ?: 'National Holiday' }}
                                    </span>
                                @elseif($isWeekend)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Weekend (Libur)
                                    </span>
                                @elseif($absensi && $absensi->jam_masuk && $absensi->jam_pulang)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Present & Complete
                                    </span>
                                @elseif($absensi && $absensi->jam_masuk)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        Incomplete (No Clock-Out)
                                    </span>
                                @elseif(!$isFuture)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">
                                        Absent
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($absensi && $absensi->jam_masuk && $pegawai->hasSignature())
                                    <span class="text-emerald-700 font-bold text-xs" title="Digital signature verified">✓ Signed</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
