@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.evaluasi-kinerja.index') }}" class="hover:text-[#000d6b] transition">Evaluasi Kinerja</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Buat Form Evaluasi Kinerja</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#000d6b] tracking-tight">Buat Penilaian Evaluasi Kinerja</h1>
            <p class="text-xs text-slate-500 mt-1">
                Sistem akan menghitung otomatis Rekapitulasi Presensi & Tugas Periodik dari database berdasarkan rentang tanggal.
            </p>
        </div>

        <a href="{{ route('admin.evaluasi-kinerja.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition">
            &larr; Batal
        </a>
    </div>

    <!-- Step 1: Form Pilih Pegawai & Rentang Periode Penilaian -->
    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <h2 class="text-sm font-extrabold text-[#000d6b] border-b border-slate-100 pb-2">
            1. Pilih Pegawai & Periode Waktu Evaluasi
        </h2>

        <form method="GET" action="{{ route('admin.evaluasi-kinerja.create') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 text-xs">
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Pilih Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}" {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} ({{ $p->area_kerja ?: 'HO ISW' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai', date('Y-m-01', strtotime('-2 months'))) }}" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-800">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal_selesai" value="{{ request('tanggal_selesai', date('Y-m-t')) }}" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-800">
            </div>

            <div class="sm:col-span-4 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-indigo-900 hover:bg-indigo-950 text-white font-bold text-xs rounded-xl shadow-xs transition">
                    🔄 Hitung Otomatis Rekap Presensi & Tugas Database
                </button>
            </div>
        </form>
    </div>

    <!-- Step 2: Form Simpan Evaluasi jika Pegawai telah dipilih -->
    @if($selectedPegawai)
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <h2 class="text-sm font-extrabold text-[#000d6b] border-b border-slate-100 pb-2">
                2. Input Penilaian Kinerja Pegawai: <span class="text-indigo-900 underline">{{ $selectedPegawai->nama }}</span>
            </h2>

            <!-- Summary Auto Rekap Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs font-sans">
                <!-- Box Rekap Absen -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>📊 Rekapitulasi Presensi Database</span>
                        <span class="text-[10px] text-slate-500">({{ request('tanggal_mulai') }} s/d {{ request('tanggal_selesai') }})</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1 text-[11px]">
                        <div>Total Hadir: <strong>{{ $rekapAbsensi['total_hadir'] }} hari</strong></div>
                        <div>Total Terlambat: <strong class="text-rose-700">{{ $rekapAbsensi['total_terlambat'] }}x ({{ $rekapAbsensi['total_menit_terlambat'] }} menit)</strong></div>
                        <div>Total Cuti/Izin: <strong>{{ $rekapAbsensi['total_cuti_izin'] }} hari</strong></div>
                        <div>Estimasi Alpha: <strong class="text-rose-700">{{ $rekapAbsensi['total_alpha'] }} hari</strong></div>
                    </div>
                </div>

                <!-- Box Rekap Tugas Periodik -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-1.5">
                    <div class="font-bold text-slate-900 flex items-center justify-between">
                        <span>📋 Rekapitulasi Tugas Periodik</span>
                        <span class="text-[10px] text-slate-500">({{ request('tanggal_mulai') }} s/d {{ request('tanggal_selesai') }})</span>
                    </div>
                    <div class="text-[11px] space-y-1">
                        <div>Total Laporan/Tugas Diupload: <strong>{{ $rekapTugas['total_tugas'] }} laporan</strong></div>
                        <div>Tugas Bernilai Bagus/Sangat Bagus: <strong class="text-emerald-700">{{ $rekapTugas['bagus'] }} laporan</strong></div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.evaluasi-kinerja.store') }}" class="space-y-4 text-xs">
                @csrf
                <input type="hidden" name="pegawai_id" value="{{ $selectedPegawai->id }}">
                <input type="hidden" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}">
                <input type="hidden" name="tanggal_selesai" value="{{ request('tanggal_selesai') }}">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tipe Periode Evaluasi <span class="text-rose-500">*</span></label>
                        <select name="periode_tipe" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                            <option value="3_bulan">Evaluasi 3 Bulan (Triwulan)</option>
                            <option value="1_bulan">Evaluasi 1 Bulan (Bulanan / Probation)</option>
                            <option value="6_bulan">Evaluasi 6 Bulan (Semester / Perpanjangan Kontrak)</option>
                            <option value="12_bulan">Evaluasi 1 Tahun (Tahunan / Annual KPI)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Rekomendasi Keputusan <span class="text-rose-500">*</span></label>
                        <select name="rekomendasi" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                            <option value="Diperpanjang Kontrak / Lanjut Tugas">✓ Diperpanjang Kontrak / Lanjut Tugas</option>
                            <option value="Diangkat Karyawan Tetap / Promosi Jabatan">🌟 Diangkat Karyawan Tetap / Promosi Jabatan</option>
                            <option value="Dipertahankan Dengan Evaluasi 1 Bulan">⚠️ Dipertahankan Dengan Evaluasi 1 Bulan</option>
                            <option value="Diberikan Surat Peringatan (SP)">⛔ Diberikan Surat Peringatan (SP)</option>
                            <option value="Tidak Diperpanjang Kontrak / Putus Kerja">❌ Tidak Diperpanjang Kontrak / Putus Kerja</option>
                        </select>
                    </div>
                </div>

                <!-- Input Nilai Skor 3 Pilar -->
                <div class="p-4 bg-indigo-50/50 rounded-xl border border-indigo-200 space-y-3">
                    <div class="font-extrabold text-[#000d6b] text-xs">Penilaian Skor 3 Pilar Kinerja (Skala 0 - 100):</div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Skor Presensi & Kedisiplinan (Bobot 40%)</label>
                            @php
                                $suggestedAbsen = max(50, 100 - ($rekapAbsensi['total_terlambat'] * 3) - ($rekapAbsensi['total_alpha'] * 15));
                            @endphp
                            <input type="number" step="0.1" name="skor_absensi" value="{{ $suggestedAbsen }}" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                            <span class="text-[10px] text-slate-500">Rekomendasi sistem: {{ $suggestedAbsen }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Skor Tugas Periodik (Bobot 40%)</label>
                            @php
                                $suggestedTugas = $rekapTugas['total_tugas'] > 0 ? round(($rekapTugas['bagus'] / $rekapTugas['total_tugas']) * 100, 1) : 85.0;
                            @endphp
                            <input type="number" step="0.1" name="skor_tugas" value="{{ $suggestedTugas }}" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                            <span class="text-[10px] text-slate-500">Rekomendasi sistem: {{ $suggestedTugas }}</span>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Skor Perilaku & Sikap (Bobot 20%)</label>
                            <input type="number" step="0.1" name="skor_perilaku" value="90.0" required
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                            <span class="text-[10px] text-slate-500">Penilaian softskills / SOP</span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Catatan Evaluasi Kinerja & Feedback Evaluator</label>
                    <textarea name="catatan_evaluasi" rows="3" placeholder="Catatan kelebihan, kekurangan, dan instruksi perbaikan kerja..."
                              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 text-xs">Pegawai menunjukkan loyalitas dan tanggung jawab kerja yang baik selama periode ini.</textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <a href="{{ route('admin.evaluasi-kinerja.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</a>
                    <button type="submit" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-xl shadow-md">
                        Simpan Evaluasi Kinerja
                    </button>
                </div>
            </form>
        </div>
    @endif

</div>
@endsection
