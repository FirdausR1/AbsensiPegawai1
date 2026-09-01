@extends('layouts.app')

@section('title', 'Detail Presensi Pegawai - ' . $pegawai->nama)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('admin.rekap.index', ['bulan' => $bulan]) }}" class="hover:text-[#000d6b] transition">Rekap Pegawai</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">{{ $pegawai->nama }}</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Detail Absensi — {{ $pegawai->nama }}</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Riwayat lengkap kehadiran harian bulan <strong class="text-slate-800">{{ $carbonMonth->translatedFormat('F Y') }}</strong> ({{ $pegawai->area_kerja ?: ($pegawai->divisi?->nama ?? 'Staff') }}).
            </p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('admin.export.pegawai', ['pegawai' => $pegawai->id, 'bulan' => $bulan, 'include_location' => 1]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition" title="Export dengan kolom koordinat lokasi GPS">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    📍 Excel (+ Lokasi)
                </a>
                <a href="{{ route('admin.export.pegawai', ['pegawai' => $pegawai->id, 'bulan' => $bulan]) }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-sm transition" title="Export standar (kolom lokasi disembunyikan)">
                    <span>📄</span> Excel Standar
                </a>
            </div>
            <a href="{{ route('admin.rekap.index', ['bulan' => $bulan]) }}"
               class="px-4 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-xl hover:bg-slate-200">
                &larr; Kembali
            </a>
        </div>
    </div>

    <!-- Calendar Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Rincian Absensi Harian ({{ $carbonMonth->daysInMonth }} Hari)</h2>
            <form method="GET" action="{{ route('admin.rekap.detail', $pegawai->id) }}" class="flex items-center gap-2">
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()"
                       class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3 px-4 font-bold">No / Tgl</th>
                        <th class="py-3 px-4 font-bold">Hari & Tanggal</th>
                        <th class="py-3 px-4 font-bold">Jam Masuk</th>
                        <th class="py-3 px-4 font-bold">Jam Pulang</th>
                        <th class="py-3 px-4 font-bold">Status Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @for($day = 1; $day <= $carbonMonth->daysInMonth; $day++)
                        @php
                            $date = $carbonMonth->copy()->day($day);
                            $dateStr = $date->toDateString();
                            $absensi = $absensis->get($day);
                            $isPastOrToday = $date->lte(\Carbon\Carbon::today());
                            $isWeekend = $date->isWeekend();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3 px-4 font-bold text-slate-400">
                                {{ $day }}
                            </td>
                            <td class="py-3 px-4 font-medium {{ $isWeekend ? 'text-rose-500' : 'text-slate-800' }}">
                                {{ $date->translatedFormat('d F Y (l)') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ $absensi?->jam_masuk ? substr($absensi->jam_masuk, 0, 5) . ' WIB' : '–' }}
                            </td>
                            <td class="py-3 px-4 font-medium text-slate-700">
                                {{ $absensi?->jam_pulang ? substr($absensi->jam_pulang, 0, 5) . ' WIB' : '–' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($absensi && !empty($absensi->keterangan))
                                    <span class="inline-block px-2.5 py-1 rounded text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                        {{ $absensi->keterangan }}
                                    </span>
                                @elseif($absensi && $absensi->jam_masuk)
                                    @php $m = $absensi->getMenitTerlambat($pegawai); @endphp
                                    @if($m > 0)
                                        <span class="inline-block px-2.5 py-1 rounded text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            Terlambat {{ $m }} Menit
                                        </span>
                                    @else
                                        <span class="inline-block px-2.5 py-1 rounded text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            ✓ Hadir Tepat Waktu
                                        </span>
                                    @endif
                                @elseif($isPastOrToday && !$isWeekend)
                                    <span class="inline-block px-2.5 py-1 rounded text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        🔴 ALPA
                                    </span>
                                @elseif($isWeekend)
                                    <span class="inline-block px-2.5 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-500">
                                        Libur Akhir Pekan
                                    </span>
                                @else
                                    <span class="text-slate-300">–</span>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
