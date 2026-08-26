@extends('layouts.app')

@section('title', 'Attendance History - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Attendance History</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Periodic attendance record for <strong>{{ $carbonMonth->translatedFormat('F Y') }}</strong> &bull; {{ $pegawai->nama }} (ID: ISW-{{ date('Y') }}-{{ str_pad($pegawai->id, 3, '0', STR_PAD_LEFT) }})
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print
            </button>

            <a href="{{ route('export.sendiri', $carbonMonth->format('Y-m')) }}"
               class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#000d6b] hover:bg-[#001253] rounded-lg transition shadow-sm tracking-wide">
                <span>📥</span> Export Excel (.xlsx)
            </a>
        </div>
    </div>

    <!-- Search & Filter Card Box -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('absen.riwayat') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-8 flex items-center gap-2">
                <label for="month" class="text-xs sm:text-sm font-bold text-slate-700 whitespace-nowrap">Select Period:</label>
                <input type="month" name="month" id="month" value="{{ $selectedMonth }}"
                       class="px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none bg-white font-bold text-slate-800">
                <button type="submit" class="px-4 py-2 bg-[#000d6b] hover:bg-[#001253] text-white rounded-lg text-xs sm:text-sm font-bold shadow-sm transition">
                    View
                </button>
            </div>

            <div class="sm:col-span-4 text-left sm:text-right text-xs text-slate-500 font-medium">
                Total Calendar Days: <strong>{{ count($monthlyDays) }} Days</strong>
            </div>
        </form>
    </div>

    <!-- Corporate Styled Attendance History Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <!-- Navy Solid Table Header -->
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider text-center w-14">Day</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Date</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Clock-In</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Clock-Out</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Status</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider text-center">Signature</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
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
                                $rowClass = 'bg-[#eef2ff]/60 font-medium';
                            } elseif ($isWeekend || $isHoliday) {
                                $rowClass = 'bg-slate-50/60 text-slate-400';
                            }
                        @endphp
                        <tr class="{{ $rowClass }} hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 text-center font-extrabold text-slate-800">
                                @if($isToday)
                                    <span class="w-7 h-7 rounded-full bg-[#000d6b] text-white flex items-center justify-center mx-auto text-xs font-bold shadow-sm">
                                        {{ $dayItem['day'] }}
                                    </span>
                                @else
                                    {{ $dayItem['day'] }}
                                @endif
                            </td>

                            <td class="py-4 px-5">
                                <div class="font-bold {{ $isToday ? 'text-[#000d6b]' : 'text-slate-900' }}">
                                    {{ $dateObj->translatedFormat('l') }}
                                </div>
                                <div class="text-[11px] text-slate-400">
                                    {{ $dateObj->translatedFormat('d M Y') }}
                                </div>
                            </td>

                            <td class="py-4 px-5 font-bold text-emerald-700">
                                @if($absensi && $absensi->jam_masuk)
                                    {{ substr($absensi->jam_masuk, 0, 5) }} WIB
                                @else
                                    <span class="text-slate-300 font-normal">-</span>
                                @endif
                            </td>

                            <td class="py-4 px-5 font-bold text-[#000d6b]">
                                @if($absensi && $absensi->jam_pulang)
                                    {{ substr($absensi->jam_pulang, 0, 5) }} WIB
                                @else
                                    <span class="text-slate-300 font-normal">-</span>
                                @endif
                            </td>

                            <td class="py-4 px-5">
                                @if($isHoliday)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-[#fce8e6] text-[#c5221f] tracking-wider uppercase">
                                        {{ $dayItem['holiday_reason'] ?: 'HOLIDAY' }}
                                    </span>
                                @elseif($isWeekend)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold bg-slate-100 text-slate-500 tracking-wider uppercase">
                                        WEEKEND
                                    </span>
                                @elseif($absensi && $absensi->jam_masuk && $absensi->jam_pulang)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-[#e6f4ea] text-[#137333] tracking-wider uppercase">
                                        ACTIVE / COMPLETE
                                    </span>
                                @elseif($absensi && $absensi->jam_masuk)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-amber-50 text-amber-800 tracking-wider uppercase border border-amber-200">
                                        IN PROGRESS
                                    </span>
                                @elseif(!$isFuture)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-[#fce8e6] text-[#c5221f] tracking-wider uppercase">
                                        ABSENT
                                    </span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>

                            <td class="py-4 px-5 text-center">
                                @if($absensi && $absensi->jam_masuk && $pegawai->hasSignature())
                                    <span class="text-emerald-700 font-bold text-xs">✓ Verified</span>
                                @else
                                    <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500">
            <div>
                Total Recorded Days: <strong>{{ $summary['total_hadir'] }} of {{ $summary['total_hari_kerja'] }} Working Days</strong>
            </div>
            <div>
                Status: <span class="text-emerald-700 font-bold">Synced</span>
            </div>
        </div>
    </div>
</div>
@endsection
