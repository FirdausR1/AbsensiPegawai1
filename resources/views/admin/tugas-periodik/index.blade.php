@extends('layouts.app')

@section('title', 'Review & Nilai Tugas Periodik CS - Admin - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Admin</span>
                <span>/</span>
                <span class="text-slate-800 font-bold">Review Tugas CS</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Monitoring & Rating Tugas CS</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Review foto bukti tugas Cleaning Service, berikan reaksi/penilaian (`Bagus`, `Cukup`, `Perlu Perbaikan`), dan catatan feedback supervisor.
            </p>
        </div>

        <div>
            <span class="px-3.5 py-2 bg-[#000d6b] text-white text-xs font-extrabold rounded-xl shadow-sm inline-block">
                📋 Total {{ $tugasList->total() }} Bukti Foto Uploaded
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.tugas-periodik.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 font-bold bg-white">
            </div>

            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Kantor Klien / Site Area</label>
                <select name="site" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 bg-white font-medium">
                    <option value="">Semua Kantor Klien</option>
                    @foreach($sites as $st)
                        <option value="{{ $st }}" {{ request('site') == $st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Nama Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}" list="cs_pegawai_list" placeholder="Ketik nama pegawai..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 bg-white font-medium">
                <datalist id="cs_pegawai_list">
                    @foreach($pegawais as $p)
                        <option value="{{ $p->nama }}">
                    @endforeach
                </datalist>
            </div>

            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Tipe Tugas</label>
                <select name="tipe_tugas" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 bg-white font-medium">
                    <option value="">Semua Periode</option>
                    <option value="harian" {{ request('tipe_tugas') == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="mingguan" {{ request('tipe_tugas') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 px-3 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg transition">
                    Filter
                </button>
                @if(request('site') || request('search') || request('pegawai_id') || request('tipe_tugas') || request('bulan') !== date('Y-m'))
                    <a href="{{ route('admin.tugas-periodik.index') }}" class="py-2.5 px-3 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Gallery Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($tugasList as $tugas)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                <div>
                    <!-- Photo -->
                    <div class="relative h-48 bg-slate-100 overflow-hidden">
                        @if($tugas->foto_path && Storage::disk('public')->exists($tugas->foto_path))
                            <a href="{{ Storage::disk('public')->url($tugas->foto_path) }}" target="_blank">
                                <img src="{{ Storage::disk('public')->url($tugas->foto_path) }}" alt="{{ $tugas->nama_tugas }}"
                                     class="w-full h-full object-cover hover:scale-105 transition duration-300">
                            </a>
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">Foto Tidak Ditemukan</div>
                        @endif

                        <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-extrabold shadow-sm {{ $tugas->tipe_tugas === 'harian' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-indigo-100 text-indigo-900 border border-indigo-300' }}">
                            {{ strtoupper($tugas->tipe_tugas) }}
                        </span>
                    </div>

                    <div class="p-4 space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 text-sm">{{ $tugas->pegawai->nama }}</span>
                            <span class="text-[10px] text-slate-400">{{ $tugas->pegawai->area_kerja ?: 'CS' }}</span>
                        </div>

                        <h3 class="font-extrabold text-[#000d6b] text-sm leading-snug">{{ $tugas->nama_tugas }}</h3>

                        <div class="text-[11px] font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded-lg border border-slate-200">
                            ⏱️ Waktu Upload: {{ $tugas->waktu_upload->translatedFormat('d M Y, H:i:s') }} WIB
                        </div>

                        @if($tugas->catatan)
                            <p class="text-slate-500 text-[11px] italic">"{{ $tugas->catatan }}"</p>
                        @endif

                        <!-- Supervisor Rating Badge / Reaksi -->
                        @if($tugas->nilai)
                            <div class="p-3 rounded-xl border {{ $tugas->getNilaiBadgeColor() }} space-y-1">
                                <div class="flex items-center justify-between font-extrabold text-xs">
                                    <span>Penilaian Supervisor:</span>
                                    <span>{{ $tugas->nilai }}</span>
                                </div>
                                @if($tugas->feedback_supervisor)
                                    <p class="text-[11px] text-slate-700 leading-relaxed font-normal">
                                        💬 "{{ $tugas->feedback_supervisor }}"
                                    </p>
                                @endif
                            </div>
                        @else
                            <div class="p-2.5 bg-slate-50 border border-slate-200 text-slate-400 text-center rounded-xl text-[11px]">
                                Belum Dinilai Supervisor
                            </div>
                        @endif
                    </div>
                </div>

                <div class="p-4 border-t border-slate-100 flex items-center justify-between text-xs gap-2">
                    <span class="text-slate-400 text-[11px]">{{ $tugas->tanggal->translatedFormat('d/m/Y') }}</span>

                    <button type="button" onclick="openRateModal({{ json_encode($tugas) }}, '{{ $tugas->pegawai->nama }}')"
                            class="px-3 py-1.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg transition shadow-xs">
                        ⭐ Beri Nilai / Reaksi
                    </button>
                </div>
            </div>
        @empty
            <div class="sm:col-span-3 bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400 text-xs">
                📷 Belum ada dokumentasi tugas periodik Cleaning Service yang diunggah.
            </div>
        @endforelse
    </div>

    @if($tugasList->hasPages())
        <div class="px-2">
            {{ $tugasList->links() }}
        </div>
    @endif
</div>

<!-- Modal Beri Nilai / Reaksi Supervisor -->
<div id="rate-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900" id="rate-modal-title">Penilaian Tugas Supervisor</h3>
            <button type="button" onclick="closeRateModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="rate-form" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="rate_nilai" class="block font-semibold text-slate-700 mb-1">Pilih Reaksi / Nilai <span class="text-rose-500">*</span></label>
                <select name="nilai" id="rate_nilai" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="Sangat Bagus 🌟">🌟 Sangat Bagus (Sempurna)</option>
                    <option value="Bagus 👍">👍 Bagus (Sesuai SOP)</option>
                    <option value="Cukup 👌">👌 Cukup (Standar)</option>
                    <option value="Perlu Perbaikan ⚠️">⚠️ Perlu Perbaikan (Kurang Bersih)</option>
                </select>
            </div>

            <div>
                <label for="rate_feedback" class="block font-semibold text-slate-700 mb-1">Catatan / Feedback Supervisor (Opsional)</label>
                <textarea name="feedback_supervisor" id="rate_feedback" rows="3" placeholder="Contoh: Pembersihan area toilet sangat rapi, pertahankan..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeRateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Simpan Penilaian</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRateModal(tugas, namaPegawai) {
        document.getElementById('rate-modal-title').textContent = 'Nilai Tugas: ' + tugas.nama_tugas + ' (' + namaPegawai + ')';
        if (tugas.nilai) {
            document.getElementById('rate_nilai').value = tugas.nilai;
        }
        document.getElementById('rate_feedback').value = tugas.feedback_supervisor || '';
        document.getElementById('rate-form').action = '/admin/tugas-periodik/' + tugas.id + '/rate';
        document.getElementById('rate-modal').classList.remove('hidden');
    }

    function closeRateModal() {
        document.getElementById('rate-modal').classList.add('hidden');
    }
</script>
@endsection
