@extends('layouts.app')

@section('title', 'Dashboard - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header Welcome Card -->
    <div class="bg-gradient-to-r from-[#000d6b] via-[#001253] to-[#1e3a8a] text-white rounded-2xl p-6 sm:p-8 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
            <div>
                <div class="inline-flex items-center px-3 py-1 bg-white/10 backdrop-blur-md rounded-md text-[11px] font-bold uppercase tracking-wider mb-3 text-indigo-100 border border-white/15">
                    {{ $pegawai->area_kerja ?: 'Divisi Operasional & IT' }}
                    @if($pegawai->is_admin)
                        &bull; <span class="text-amber-300 font-bold">ADMINISTRATOR</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ $pegawai->nama }}</h1>
                <p class="text-indigo-100/80 text-xs sm:text-sm mt-1 max-w-xl">
                    Sistem Presensi Digital PT Inti Sarana Wijaya. Catat kehadiran harian, verifikasi tanda tangan, dan download rekapitulasi Excel.
                </p>
            </div>

            <!-- Digital Clock Badge -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-4 text-center md:text-right min-w-[210px] shadow-sm">
                <div class="text-[11px] uppercase tracking-wider text-indigo-200 font-medium" id="current-date">Memuat tanggal...</div>
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-0.5" id="current-time">00:00:00</div>
                <div class="text-[10px] text-indigo-200/90 mt-1 font-medium">Waktu Indonesia Barat (WIB)</div>
            </div>
        </div>
    </div>

    <!-- Signature Warning Alert if not yet created -->
    @if (!$pegawai->hasSignature())
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div>
                <h3 class="text-xs font-bold text-rose-900">Tanda Tangan Digital Belum Dibuat</h3>
                <p class="text-xs text-rose-700 mt-0.5">Anda wajib membuat tanda tangan digital sekali di awal sebelum dapat mencatat Absen Masuk & Pulang.</p>
            </div>
            <a href="{{ route('profile.signature') }}"
               class="w-full sm:w-auto text-center px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-lg transition shadow-sm whitespace-nowrap">
                Buat Tanda Tangan &rarr;
            </a>
        </div>
    @endif

    <!-- Action Attendance Cards -->
    <div id="attendance-section" class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Absen Masuk Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 transition">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="section-bar"></span>
                        <h2 class="text-base font-bold text-slate-900">Absen Masuk (Clock-In)</h2>
                    </div>
                    @if($todayAbsensi && $todayAbsensi->jam_masuk)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">
                            Masuk: {{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB
                        </span>
                    @else
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600">
                            Shift Pagi / Datang
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    @if($todayAbsensi && $todayAbsensi->jam_masuk)
                        Kehadiran masuk Anda hari ini sudah tercatat pada pukul <strong class="text-emerald-700 font-bold">{{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB</strong>.
                    @else
                        Catat waktu kedatangan kerja Anda hari ini. Jam dan tanda tangan digital Anda akan otomatis tersimpan dalam sistem.
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('absen.masuk') }}" class="mt-6">
                @csrf
                @if($todayAbsensi && $todayAbsensi->jam_masuk)
                    <button type="button" disabled
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-default flex items-center justify-center gap-2">
                        Sudah Absen Masuk ({{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB)
                    </button>
                @else
                    <button type="submit"
                            {{ $pegawai->hasSignature() ? '' : 'disabled' }}
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs shadow-sm transition flex items-center justify-center gap-2 {{ $pegawai->hasSignature() ? 'bg-[#000d6b] hover:bg-[#001253] text-white shadow-indigo-950/20' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                        Catat Absen Masuk
                    </button>
                @endif
            </form>
        </div>

        <!-- Absen Pulang Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 transition">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="section-bar"></span>
                        <h2 class="text-base font-bold text-slate-900">Absen Pulang (Clock-Out)</h2>
                    </div>
                    @if($todayAbsensi && $todayAbsensi->jam_pulang)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-indigo-50 text-[#000d6b] border border-indigo-200">
                            Pulang: {{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB
                        </span>
                    @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-amber-50 text-amber-800 border border-amber-200">
                            Siap Absen Pulang
                        </span>
                    @else
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-slate-100 text-slate-600">
                            Shift Selesai Kerja
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    @if($todayAbsensi && $todayAbsensi->jam_pulang)
                        Absensi pulang hari ini sudah tercatat pada pukul <strong class="text-[#000d6b] font-bold">{{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB</strong>.
                    @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                        Anda sudah absen masuk. Klik tombol di bawah saat jam kerja Anda selesai hari ini.
                    @else
                        Catat waktu selesai kerja hari ini. Pastikan Anda sudah mencatat absen masuk terlebih dahulu.
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('absen.pulang') }}" class="mt-6">
                @csrf
                @if($todayAbsensi && $todayAbsensi->jam_pulang)
                    <button type="button" disabled
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs bg-slate-100 text-slate-600 border border-slate-200 cursor-default flex items-center justify-center gap-2">
                        Sudah Absen Pulang ({{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB)
                    </button>
                @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                    <button type="submit"
                            {{ $pegawai->hasSignature() ? '' : 'disabled' }}
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs shadow-sm transition flex items-center justify-center gap-2 {{ $pegawai->hasSignature() ? 'bg-slate-900 hover:bg-black text-white shadow-slate-300 ring-2 ring-[#000d6b]' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                        Catat Absen Pulang
                    </button>
                @else
                    <button type="button" disabled
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs bg-slate-100 text-slate-400 cursor-not-allowed flex items-center justify-center gap-2">
                        Catat Absen Pulang
                    </button>
                @endif
            </form>
        </div>

    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Total Hadir Bulan Ini</div>
            <div class="text-2xl font-extrabold text-slate-900">{{ $stats['total_bulan_ini'] ?? 0 }} <span class="text-xs font-medium text-slate-500">Hari</span></div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Kehadiran Lengkap</div>
            <div class="text-2xl font-extrabold text-emerald-700">{{ $stats['total_lengkap'] ?? 0 }} <span class="text-xs font-medium text-slate-500">Hari</span></div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Rekap Absensi Excel</div>
                <div class="text-xs font-bold text-slate-700">Format .xlsx</div>
            </div>
            <a href="{{ route('export.sendiri') }}" class="px-4 py-2 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition">
                Download
            </a>
        </div>
    </div>

    <!-- Recent Attendance Log Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-[#000d6b]">Recent Attendance (7 Hari Terakhir)</h3>
            <a href="{{ route('absen.riwayat') }}" class="text-xs font-bold text-[#000d6b] hover:underline">
                View All History &rarr;
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <!-- Navy Solid Table Header -->
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider">Date</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Clock-In</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Clock-Out</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($recentAbsensis as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}
                            </td>
                            <td class="py-4 px-5 font-bold text-emerald-700">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-4 px-5 font-bold text-[#000d6b]">
                                {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-4 px-5">
                                @if($item->jam_masuk && $item->jam_pulang)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-[#e6f4ea] text-[#137333] tracking-wider uppercase">
                                        ACTIVE / COMPLETE
                                    </span>
                                @elseif($item->jam_masuk)
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-amber-50 text-amber-800 tracking-wider uppercase border border-amber-200">
                                        IN PROGRESS
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-slate-100 text-slate-600 tracking-wider uppercase">
                                        -
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-slate-400">
                                Belum ada riwayat absensi tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        const dateEl = document.getElementById('current-date');
        const timeEl = document.getElementById('current-time');
        if (dateEl) dateEl.textContent = dateStr;
        if (timeEl) timeEl.textContent = timeStr;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endsection
