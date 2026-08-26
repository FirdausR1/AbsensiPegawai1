@extends('layouts.app')

@section('title', 'Dashboard - Absensi Pegawai')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 text-white rounded-3xl p-6 sm:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 text-9xl select-none">📋</div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-wider mb-3">
                    <span>🏢</span> {{ $pegawai->area_kerja ?: 'Divisi Operasional' }}
                    @if($pegawai->is_admin)
                        &bull; <span class="text-amber-300 font-bold">AKUN ADMIN</span>
                    @endif
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold">Selamat Datang, {{ $pegawai->nama }}! 👋</h1>
                <p class="text-indigo-100 text-sm mt-1">Absensi digital Anda tersimpan aman dan dapat diekspor ke Excel kapan saja.</p>
            </div>

            <!-- Digital Clock -->
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-4 text-center md:text-right min-w-[200px]">
                <div class="text-xs uppercase tracking-wider text-indigo-200" id="current-date">Memuat tanggal...</div>
                <div class="text-2xl sm:text-3xl font-extrabold tracking-tight mt-0.5" id="current-time">00:00:00</div>
                <div class="text-[11px] text-indigo-200 mt-1">Waktu Indonesia Barat (WIB)</div>
            </div>
        </div>
    </div>

    <!-- Admin Quick Access Card (if admin) -->
    @if($pegawai->is_admin)
        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 bg-amber-500 text-white rounded-2xl flex items-center justify-center text-2xl shadow-sm">
                    ⚙️
                </div>
                <div>
                    <h2 class="text-base font-bold text-amber-900">Akses Khusus Admin Aktif</h2>
                    <p class="text-xs text-amber-700 mt-0.5">Kelola seluruh data akun pegawai, edit identitas, reset tanda tangan, dan cek rekap absensi harian.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('admin.pegawai.index') }}"
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                    👥 Kelola Pegawai
                </a>
                <a href="{{ route('admin.absensi.index') }}"
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 bg-white hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold rounded-xl transition shadow-sm">
                    📊 Rekap Absensi
                </a>
            </div>
        </div>
    @endif

    <!-- Warning Signature if empty -->
    @if (!$pegawai->hasSignature())
        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 sm:p-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-rose-100 text-rose-600 rounded-xl flex items-center justify-center text-xl font-bold shrink-0">
                    ✍️
                </div>
                <div>
                    <h3 class="text-sm font-bold text-rose-900">Tanda Tangan Digital Belum Dibuat</h3>
                    <p class="text-xs text-rose-700 mt-0.5">Anda harus membuat tanda tangan digital sebelum dapat melakukan Absen Masuk / Pulang.</p>
                </div>
            </div>
            <a href="{{ route('profile.signature') }}"
               class="w-full sm:w-auto text-center px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl transition shadow-sm">
                Buat Tanda Tangan Sekarang &rarr;
            </a>
        </div>
    @endif

    <!-- Action Absen Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <!-- Absen Masuk Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:border-indigo-200 transition">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 text-2xl">
                        ☀️
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                        Shift Pagi / Datang
                    </span>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Absen Masuk</h2>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Catat waktu kedatangan kerja hari ini. Jam dan tanda tangan digital Anda akan otomatis disinkronkan ke Google Sheet.
                </p>
            </div>

            <form method="POST" action="{{ route('absen.masuk') }}" class="mt-6">
                @csrf
                <button type="submit"
                        {{ $pegawai->hasSignature() ? '' : 'disabled' }}
                        class="w-full py-3.5 px-4 rounded-2xl font-bold text-sm shadow-md transition duration-150 flex items-center justify-center gap-2 {{ $pegawai->hasSignature() ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-emerald-200' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                    <span>👉</span> Catat Absen Masuk
                </button>
            </form>
        </div>

        <!-- Absen Pulang Card -->
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm flex flex-col justify-between hover:border-indigo-200 transition">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <span class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-slate-100 text-slate-700 text-2xl">
                        🌙
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">
                        Selesai Kerja
                    </span>
                </div>
                <h2 class="text-lg font-bold text-slate-800">Absen Pulang</h2>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Catat waktu selesai kerja hari ini. Pastikan Anda sudah mencatat absen masuk terlebih dahulu sebelum absen pulang.
                </p>
            </div>

            <form method="POST" action="{{ route('absen.pulang') }}" class="mt-6">
                @csrf
                <button type="submit"
                        {{ $pegawai->hasSignature() ? '' : 'disabled' }}
                        class="w-full py-3.5 px-4 rounded-2xl font-bold text-sm shadow-md transition duration-150 flex items-center justify-center gap-2 {{ $pegawai->hasSignature() ? 'bg-slate-800 hover:bg-slate-900 text-white shadow-slate-300' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                    <span>🏠</span> Catat Absen Pulang
                </button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
        <h3 class="text-sm font-bold text-slate-800 mb-3 flex items-center gap-2">
            <span>⚡</span> Menu Cepat Pegawai
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <a href="{{ route('profile.edit') }}"
               class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">👤</span>
                    <div>
                        <div class="text-xs font-bold text-slate-800 group-hover:text-indigo-600">Profil & Data Diri</div>
                        <div class="text-[11px] text-slate-500">Edit nama, email & password</div>
                    </div>
                </div>
                <span class="text-xs font-semibold text-indigo-600">&rarr;</span>
            </a>

            <a href="{{ route('profile.signature') }}"
               class="p-4 rounded-2xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">✍️</span>
                    <div>
                        <div class="text-xs font-bold text-slate-800 group-hover:text-indigo-600">Tanda Tangan</div>
                        <div class="text-[11px] text-slate-500">{{ $pegawai->hasSignature() ? 'Status: Aktif' : 'Belum Ada' }}</div>
                    </div>
                </div>
                <span class="text-xs font-semibold text-indigo-600">&rarr;</span>
            </a>

            <a href="{{ route('absen.riwayat') }}"
               class="p-4 rounded-2xl border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/50 transition flex items-center justify-between group">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📅</span>
                    <div>
                        <div class="text-xs font-bold text-slate-800 group-hover:text-emerald-700">Riwayat Absensi</div>
                        <div class="text-[11px] text-slate-500">Lihat hasil & rekap bulanan</div>
                    </div>
                </div>
                <span class="text-xs font-semibold text-emerald-600">&rarr;</span>
            </a>

            <a href="{{ route('export.sendiri') }}"
               class="p-4 rounded-2xl border border-green-200 bg-green-50 hover:border-green-400 hover:bg-green-100 transition flex items-center justify-between group sm:col-span-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">📥</span>
                    <div>
                        <div class="text-xs font-bold text-green-800 group-hover:text-green-900">Export Absensi ke Excel</div>
                        <div class="text-[11px] text-green-600">Download file .xlsx bulan ini sesuai template DAFTAR HADIR — termasuk tanda tangan</div>
                    </div>
                </div>
                <span class="text-xs font-bold text-green-700 bg-green-200 px-3 py-1.5 rounded-xl">Download ↓</span>
            </a>
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

        document.getElementById('current-date').textContent = dateStr;
        document.getElementById('current-time').textContent = timeStr;
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endsection
