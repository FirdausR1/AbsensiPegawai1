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
            </div>
        </div>
    </div>

    <!-- Card Pengumuman & SOP Perusahaan Terbaru -->
    @if(isset($activePengumumans) && $activePengumumans->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="section-bar"></span>
                    <h2 class="text-sm font-extrabold text-slate-900">Pengumuman & Informasi SOP Perusahaan</h2>
                </div>
                <a href="{{ route('pengumuman.index') }}" class="text-xs font-bold text-[#000d6b] hover:underline">
                    Lihat Semua SOP & Pengumuman &rarr;
                </a>
            </div>

            <div class="space-y-2 text-xs">
                @foreach($activePengumumans as $info)
                    <div class="p-3.5 rounded-xl border border-slate-200 bg-slate-50/70 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ $info->getKategoriBadgeColor() }}">
                                    {{ strtoupper($info->kategori) }}
                                </span>
                                <h3 class="font-bold text-slate-900">{{ $info->judul }}</h3>
                            </div>
                            <p class="text-slate-600 text-[11px] line-clamp-2 leading-relaxed">{{ $info->isi }}</p>
                        </div>
                        <span class="text-[10px] text-slate-400 font-medium shrink-0">{{ $info->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Card Pegawai Paling Rajin Bulan Ini -->
    @if(isset($mostDiligent) && $mostDiligent->pegawai)
        <div class="bg-gradient-to-r from-amber-500 via-amber-600 to-[#000d6b] text-white rounded-2xl p-5 sm:p-6 shadow-sm relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-white/20 backdrop-blur-md border border-white/30 flex items-center justify-center font-bold text-lg shrink-0 shadow-sm">
                        TOP
                    </div>
                    <div>
                        <div class="inline-block px-2.5 py-0.5 bg-amber-300 text-amber-950 text-[10px] font-extrabold uppercase tracking-wider rounded-md mb-1">
                            PRESTASI BULAN INI — {{ now()->translatedFormat('F Y') }}
                        </div>
                        <h2 class="text-base sm:text-lg font-extrabold tracking-tight">
                            Pegawai Paling Rajin: {{ $mostDiligent->pegawai->nama }}
                        </h2>
                        <p class="text-amber-100 text-xs mt-0.5">
                            {{ $mostDiligent->pegawai->area_kerja ?: ($mostDiligent->pegawai->divisi?->nama ?? 'Staff') }} &bull;
                            <strong>{{ $mostDiligent->total_hadir }} Hari Hadir Tepat Waktu</strong> ({{ $mostDiligent->total_hari_terlambat }} Telat) &bull;
                            <strong>{{ $mostDiligent->total_tugas }} Tugas Periodik Selesai</strong>
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

    <!-- Banner Jadwal Shift Hari Ini -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="px-3 py-2 rounded-xl text-xs font-black uppercase tracking-wider shrink-0
                @if($todayJadwal)
                    @if($todayJadwal->tipe_shift === 'Pagi') bg-amber-100 border border-amber-200 text-amber-800
                    @elseif($todayJadwal->tipe_shift === 'Malam') bg-indigo-100 border border-indigo-200 text-indigo-800
                    @elseif($todayJadwal->tipe_shift === 'Siang') bg-sky-100 border border-sky-200 text-sky-800
                    @elseif($todayJadwal->tipe_shift === 'Libur') bg-slate-100 border border-slate-200 text-slate-600
                    @endif
                @else bg-[#eef2ff] border border-indigo-100 text-[#000d6b]
                @endif">
                {{ $todayJadwal ? $todayJadwal->tipe_shift : 'REGULER' }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Jadwal Shift Hari Ini:</span>
                    @if($todayJadwal)
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $todayJadwal->getBadgeColor() }}">
                            {{ $todayJadwal->getShiftLabel() }}
                        </span>
                    @else
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            Reguler / Non-Shift
                        </span>
                    @endif
                </div>
                <div class="text-sm font-extrabold text-slate-900 mt-0.5">
                    @if($todayJadwal && $todayJadwal->isLibur())
                        <span class="text-slate-500">Hari Ini Bebas Tugas / Hari Libur</span>
                    @elseif($todayJadwal)
                        Jam Kerja: <span class="text-[#000d6b]">{{ substr($todayJadwal->getJamMasukEfektif(), 0, 5) }} – {{ substr($todayJadwal->getJamPulangEfektif(), 0, 5) }} WIB</span>
                    @else
                        Jam Kerja: <span class="text-[#000d6b]">{{ substr($pegawai->getDivisi()->jam_masuk, 0, 5) }} – {{ substr($pegawai->getDivisi()->jam_pulang, 0, 5) }} WIB</span>
                    @endif
                </div>
                @if($todayJadwal && $todayJadwal->catatan)
                    <p class="text-[11px] text-slate-500 mt-0.5 italic">Catatan: {{ $todayJadwal->catatan }}</p>
                @endif
            </div>
        </div>
        <a href="{{ route('jadwal-shift.index') }}"
           class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition shrink-0">
            Lihat Kalender Shift Saya &rarr;
        </a>
    </div>

    <!-- GPS Location Live Status Banner -->
    <div id="gps-status-card" class="rounded-2xl border p-4 shadow-sm transition-all duration-300 bg-amber-50/70 border-amber-200 text-amber-900">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-start sm:items-center gap-3">
                <div id="gps-status-icon" class="w-9 h-9 rounded-xl flex items-center justify-center text-lg bg-amber-100 text-amber-700 shrink-0">
                    📡
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 id="gps-status-title" class="text-xs sm:text-sm font-extrabold text-amber-950">Mendeteksi Lokasi GPS...</h4>
                        <span id="gps-status-badge" class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-200 text-amber-900 animate-pulse">Menunggu Izin GPS</span>
                    </div>
                    <p id="gps-status-desc" class="text-[11px] sm:text-xs text-amber-800/90 mt-0.5">
                        Mohon aktifkan GPS di perangkat dan klik "Izinkan" (Allow) pada browser agar absensi otomatis terverifikasi dengan titik lokasi.
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" onclick="detectGPSLocation(true)" id="btn-retry-gps"
                        class="px-3.5 py-1.5 bg-amber-200/80 hover:bg-amber-300 text-amber-900 font-bold text-xs rounded-lg transition flex items-center gap-1.5">
                    🔄 Deteksi Ulang GPS
                </button>
            </div>
        </div>
    </div>

    <!-- Action Attendance Cards -->
    <div id="attendance-section" class="scroll-mt-8 grid grid-cols-1 md:grid-cols-2 gap-6 transition-all duration-300">
        
        <!-- Absen Masuk Card -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:border-slate-300 transition">
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <span class="section-bar"></span>
                        <h2 class="text-base font-bold text-slate-900">Absen Masuk (Clock-In)</h2>
                    </div>
                    @if($todayAbsensi && $todayAbsensi->jam_masuk)
                        <div class="flex flex-col items-end gap-1">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-800 border border-emerald-200">
                                Masuk: {{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB
                            </span>
                            @php
                                $menitTerlambat = $todayAbsensi->getMenitTerlambat();
                            @endphp
                            @if($menitTerlambat > 0)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-50 text-rose-700 border border-rose-200">
                                    Terlambat {{ $menitTerlambat }} Menit
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-emerald-700">
                                    ✓ Tepat Waktu
                                </span>
                            @endif
                        </div>
                    @else
                        @php
                            $targetMasuk = $todayJadwal ? $todayJadwal->getJamMasukEfektif() : $pegawai->getDivisi()->jam_masuk;
                            $targetPulang = $todayJadwal ? $todayJadwal->getJamPulangEfektif() : $pegawai->getDivisi()->jam_pulang;
                        @endphp
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-[#eef2ff] text-[#000d6b]">
                            @if($todayJadwal && $todayJadwal->isLibur())
                                Status: Hari Libur
                            @else
                                Jam Masuk: {{ $targetMasuk ? substr($targetMasuk, 0, 5) : '08:00' }} WIB
                            @endif
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    @if($todayAbsensi && $todayAbsensi->jam_masuk)
                        Kehadiran masuk Anda hari ini sudah tercatat pada pukul <strong class="text-emerald-700 font-bold">{{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB</strong>.
                        @if($todayAbsensi->lokasi_masuk)
                            <div class="mt-2 text-[11px] text-slate-600 font-medium bg-slate-50 p-2 rounded-lg border border-slate-100 flex items-center gap-1.5">
                                <span>📍</span> <span>Lokasi: <strong>{{ $todayAbsensi->lokasi_masuk }}</strong></span>
                            </div>
                        @endif
                    @elseif($todayJadwal && $todayJadwal->isLibur())
                        Anda hari ini dijadwalkan <strong class="text-slate-700 font-bold">Hari Libur / Istirahat</strong>.
                    @else
                        Jadwal masuk Anda hari ini (<strong>{{ $todayJadwal ? $todayJadwal->getShiftLabel() : $pegawai->getDivisi()->nama }}</strong>) adalah pukul <strong>{{ $targetMasuk ? substr($targetMasuk, 0, 5) : '08:00' }} WIB</strong>.
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('absen.masuk') }}" enctype="multipart/form-data" class="mt-6 space-y-3" onsubmit="return validateAttendanceSubmission(this)">
                @csrf
                <input type="hidden" name="lokasi" class="user-gps-location">

                @if(!$todayAbsensi || !$todayAbsensi->jam_masuk)
                    <div class="space-y-2 text-xs">
                        <label class="block font-bold text-slate-700">Mode Presensi Masuk:</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer font-semibold text-slate-800">
                                <input type="radio" name="status_presensi" value="reguler" checked onclick="toggleDinasLuarInput('masuk', false)" class="text-[#000d6b]">
                                <span>Kantor / Reguler</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer font-semibold text-slate-800">
                                <input type="radio" name="status_presensi" value="dinas_luar" onclick="toggleDinasLuarInput('masuk', true)" class="text-[#000d6b]">
                                <span>Dinas Luar</span>
                            </label>
                        </div>
                    </div>

                    <div id="dinas-luar-box-masuk" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1 text-xs">
                        <label class="block font-bold text-amber-900">Upload Foto Bukti Dinas Luar <span class="text-rose-500">*</span></label>
                        <input type="file" name="foto_dinas_luar" accept="image/*" class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded-lg p-1.5">
                    </div>
                @endif

                @if($todayAbsensi && $todayAbsensi->jam_masuk)
                    <button type="button" disabled
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs bg-emerald-50 text-emerald-700 border border-emerald-200 cursor-default flex items-center justify-center gap-2">
                        Sudah Absen Masuk ({{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB)
                    </button>
                @else
                    <button type="submit"
                            id="btn-absen-masuk"
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
                            @if($todayJadwal && $todayJadwal->isLibur())
                                Status: Hari Libur
                            @else
                                Jam Pulang: {{ $targetPulang ? substr($targetPulang, 0, 5) : '17:00' }} WIB
                            @endif
                        </span>
                    @endif
                </div>

                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    @if($todayAbsensi && $todayAbsensi->jam_pulang)
                        Absensi pulang hari ini sudah tercatat pada pukul <strong class="text-[#000d6b] font-bold">{{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB</strong>.
                        @if($todayAbsensi->lokasi_pulang)
                            <div class="mt-2 text-[11px] text-slate-600 font-medium bg-slate-50 p-2 rounded-lg border border-slate-100 flex items-center gap-1.5">
                                <span>📍</span> <span>Lokasi: <strong>{{ $todayAbsensi->lokasi_pulang }}</strong></span>
                            </div>
                        @endif
                    @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                        Anda sudah absen masuk. Klik tombol di bawah saat jam kerja Anda selesai hari ini.
                    @else
                        Catat waktu selesai kerja hari ini. Pastikan Anda sudah mencatat absen masuk terlebih dahulu.
                    @endif
                </p>
            </div>

            <form method="POST" action="{{ route('absen.pulang') }}" enctype="multipart/form-data" class="mt-6 space-y-3" onsubmit="return validateAttendanceSubmission(this)">
                @csrf
                <input type="hidden" name="lokasi" class="user-gps-location">

                @if($todayAbsensi && $todayAbsensi->jam_masuk && !$todayAbsensi->jam_pulang)
                    @if($todayAbsensi->status_presensi === 'dinas_luar')
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl space-y-1 text-xs">
                            <label class="block font-bold text-amber-900">Upload Foto Bukti Dinas Luar (Pulang)</label>
                            <input type="file" name="foto_dinas_luar" accept="image/*" class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded-lg p-1.5">
                        </div>
                    @endif
                @endif

                @if($todayAbsensi && $todayAbsensi->jam_pulang)
                    <button type="button" disabled
                            class="w-full py-3 px-4 rounded-lg font-bold text-xs bg-slate-100 text-slate-600 border border-slate-200 cursor-default flex items-center justify-center gap-2">
                        Sudah Absen Pulang ({{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB)
                    </button>
                @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                    <button type="submit"
                            id="btn-absen-pulang"
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

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex flex-col justify-between gap-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Rekap Absensi Excel</div>
                    <div class="text-xs font-bold text-slate-700">Format .xlsx</div>
                </div>
                <button type="button" onclick="document.getElementById('modal-export-user').classList.remove('hidden')"
                        class="px-4 py-2 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Download
                </button>
            </div>
            <div class="text-[10px] text-slate-400">
                Pilih download dengan koordinat GPS atau disembunyikan.
            </div>
        </div>
    </div>

    <!-- Modal Export Excel User -->
    <div id="modal-export-user" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-2xl border border-slate-200 max-w-sm w-full p-5 shadow-xl space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                <h3 class="text-sm font-bold text-[#000d6b]">Export Rekap Absensi Excel</h3>
                <button onclick="document.getElementById('modal-export-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>
            <p class="text-xs text-slate-600">
                Pilih opsi tampilan kolom lokasi/koordinat presensi pada lembar Excel (.xlsx):
            </p>
            <div class="space-y-2">
                <a href="{{ route('export.sendiri', ['include_location' => 1]) }}"
                   class="w-full flex items-center justify-center gap-2 p-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl transition">
                    <span>📍</span> Download Dengan Kolom Lokasi GPS
                </a>
                <a href="{{ route('export.sendiri') }}"
                   class="w-full flex items-center justify-center gap-2 p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl transition">
                    <span>📄</span> Download Standar (Kolom Lokasi Dihide)
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Attendance Log Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
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
    let currentGPSLocationString = "";
    let isGPSAcquired = false;

    function toggleDinasLuarInput(type, show) {
        const el = document.getElementById('dinas-luar-box-' + type);
        if (el) {
            if (show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }
    }

    function setGPSCardState(status, title, desc, badgeText, badgeClass) {
        const card = document.getElementById('gps-status-card');
        const icon = document.getElementById('gps-status-icon');
        const titleEl = document.getElementById('gps-status-title');
        const descEl = document.getElementById('gps-status-desc');
        const badge = document.getElementById('gps-status-badge');

        if (!card) return;

        if (status === 'success') {
            card.className = "rounded-2xl border p-4 shadow-sm transition-all duration-300 bg-emerald-50 border-emerald-200 text-emerald-950";
            icon.className = "w-9 h-9 rounded-xl flex items-center justify-center text-lg bg-emerald-100 text-emerald-700 shrink-0";
            icon.innerHTML = "📍";
        } else if (status === 'error') {
            card.className = "rounded-2xl border p-4 shadow-sm transition-all duration-300 bg-rose-50 border-rose-200 text-rose-950";
            icon.className = "w-9 h-9 rounded-xl flex items-center justify-center text-lg bg-rose-100 text-rose-700 shrink-0";
            icon.innerHTML = "⚠️";
        } else {
            card.className = "rounded-2xl border p-4 shadow-sm transition-all duration-300 bg-amber-50/70 border-amber-200 text-amber-900";
            icon.className = "w-9 h-9 rounded-xl flex items-center justify-center text-lg bg-amber-100 text-amber-700 shrink-0";
            icon.innerHTML = "📡";
        }

        titleEl.textContent = title;
        descEl.innerHTML = desc;
        badge.textContent = badgeText;
        badge.className = "px-2 py-0.5 rounded text-[10px] font-extrabold " + badgeClass;
    }

    function detectGPSLocation(isUserTriggered = false) {
        if (!("geolocation" in navigator)) {
            setGPSCardState('error', 'Perangkat Tidak Mendukung GPS', 'Browser Anda tidak mendukung Geolocation API.', 'GPS Tidak Didukung', 'bg-rose-200 text-rose-900');
            return;
        }

        setGPSCardState('loading', 'Sedang Mendeteksi Lokasi GPS...', 'Meminta koordinat presisi dari satelit perangkat Anda...', 'Mencari GPS...', 'bg-amber-200 text-amber-900 animate-pulse');

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                const coordStr = `${lat}, ${lng}`;

                isGPSAcquired = true;
                let locationDisplayName = coordStr;

                // Tampilkan koordinat terlebih dahulu
                currentGPSLocationString = "GPS: " + coordStr;
                document.querySelectorAll('.user-gps-location').forEach(el => el.value = currentGPSLocationString);

                setGPSCardState('success', 'Lokasi GPS Terdeteksi & Terverifikasi', `Koordinat: <strong>${coordStr}</strong> (Akurasi: ±${Math.round(position.coords.accuracy)}m). Siap untuk absensi.`, '✓ GPS Aktif', 'bg-emerald-200 text-emerald-900');

                // Coba reverse geocode ke nama area/jalan (async non-blocking)
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=17&addressdetails=1`, {
                        headers: { 'Accept-Language': 'id' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.display_name) {
                            const shortName = data.address?.road 
                                ? `${data.address.road}, ${data.address.suburb || data.address.city || data.address.county || ''}`
                                : data.display_name.split(',').slice(0, 3).join(',');
                            
                            currentGPSLocationString = `${shortName.trim()} (${coordStr})`;
                            document.querySelectorAll('.user-gps-location').forEach(el => el.value = currentGPSLocationString);
                            setGPSCardState('success', 'Lokasi GPS Terverifikasi', `📍 <strong>${shortName.trim()}</strong><br><span class="text-[10px] text-emerald-700">Koordinat: ${coordStr}</span>`, '✓ GPS Aktif', 'bg-emerald-200 text-emerald-900');
                        }
                    }
                } catch (e) {
                    // Fallback tetap menggunakan koordinat
                }
            },
            function(error) {
                isGPSAcquired = false;
                let errMsg = "GPS belum aktif atau izin akses ditolak.";
                if (error.code === 1) {
                    errMsg = "Izin lokasi diblokir oleh browser. Silakan klik ikon gembok/pengaturan di baris URL browser, pilih <strong>Izinkan Lokasi (Allow Location)</strong> lalu muat ulang.";
                } else if (error.code === 2) {
                    errMsg = "Posisi GPS tidak dapat diperoleh. Pastikan layanan lokasi (GPS) pada HP / Laptop / Komputer sudah dinyalakan (ON).";
                } else if (error.code === 3) {
                    errMsg = "Waktu pencarian GPS habis (Timeout). Silakan tekan tombol 'Deteksi Ulang GPS'.";
                }

                setGPSCardState('error', 'GPS Belum Aktif / Ditolak', errMsg, '⚠️ GPS Non-Aktif', 'bg-rose-200 text-rose-900');
                if (isUserTriggered) {
                    alert("Peringatan: GPS belum terdeteksi. Silakan nyalakan GPS pada perangkat dan izinkan akses lokasi di browser untuk melakukan absensi.");
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function validateAttendanceSubmission(form) {
        const locInput = form.querySelector('.user-gps-location');
        if (!locInput || !locInput.value || locInput.value.trim() === "") {
            alert("⚠️ GPS/Lokasi Anda belum terdeteksi!\n\nMohon nyalakan GPS di perangkat Anda dan izinkan akses lokasi pada browser sebelum melakukan absen.");
            detectGPSLocation(true);
            return false;
        }
        return true;
    }

    // Panggil saat halaman pertama dibuka
    detectGPSLocation();

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

    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#attendance-section') {
            const el = document.getElementById('attendance-section');
            if (el) {
                setTimeout(() => {
                    el.scrollIntoView({ behavior: 'smooth' });
                    el.classList.add('ring-2', 'ring-[#000d6b]', 'ring-offset-4', 'rounded-2xl');
                    setTimeout(() => {
                        el.classList.remove('ring-2', 'ring-[#000d6b]', 'ring-offset-4');
                    }, 2500);
                }, 100);
            }
        }
    });
</script>
@endsection
