@extends('layouts.app')

@section('title', 'Jadwal Shift Saya - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Jadwal Shift Saya</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Jadwal Shift Bulan Ini</h1>
            <p class="text-xs text-slate-500 mt-1">
                Lihat jadwal shift Anda dan ajukan request perubahan shift kepada Danru/Koordinator.
            </p>
        </div>

        <!-- Month nav -->
        <div class="flex items-center gap-2">
            @php
                $prevBulan = $carbonBulan->copy()->subMonth()->format('Y-m');
                $nextBulan = $carbonBulan->copy()->addMonth()->format('Y-m');
            @endphp
            <a href="{{ route('jadwal-shift.index', ['bulan' => $prevBulan]) }}"
               class="px-3 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 text-xs font-bold shadow-sm">← Prev</a>
            <div class="px-4 py-2 bg-[#000d6b] text-white rounded-lg text-xs font-bold min-w-[110px] text-center shadow">
                {{ $carbonBulan->translatedFormat('F Y') }}
            </div>
            <a href="{{ route('jadwal-shift.index', ['bulan' => $nextBulan]) }}"
               class="px-3 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 text-xs font-bold shadow-sm">Next →</a>
        </div>
    </div>

    @if(!$pegawai->isShiftWorker() && $jadwals->isEmpty())
        <div class="bg-gradient-to-r from-[#eef2ff] to-white rounded-2xl border border-indigo-100 p-6 shadow-sm flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-[#000d6b] text-white flex items-center justify-center text-lg font-bold shrink-0">
                🏢
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Informasi Jam Kerja Staff / Non-Shift</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Divisi/Departemen Anda (<strong>{{ $pegawai->area_kerja ?: ($pegawai->divisi?->nama ?? 'Staff Kantor') }}</strong>) menggunakan jam operasional standar perusahaan (<strong>08:00 – 17:00</strong>, Senin – Jumat) dan tidak memerlukan jadwal shift bergilir.
                </p>
                <p class="text-[11px] text-slate-400 mt-1">
                    *Fitur Jadwal Shift khusus digunakan oleh Divisi Satpam, Security, dan Cleaning Service yang memiliki jadwal pergantian jam kerja 24/7.
                </p>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Stats Summary -->
    @php
        $totalPagi  = $jadwals->where('tipe_shift', 'Pagi')->count();
        $totalMalam = $jadwals->where('tipe_shift', 'Malam')->count();
        $totalSiang = $jadwals->where('tipe_shift', 'Siang')->count();
        $totalLibur = $jadwals->where('tipe_shift', 'Libur')->count();
        $totalBelum = $carbonBulan->daysInMonth - $jadwals->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center shadow-sm">
            <div class="text-xl font-black text-amber-800">{{ $totalPagi }}</div>
            <div class="text-[11px] font-bold text-amber-600 mt-0.5">☀️ Shift Pagi</div>
        </div>
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-3 text-center shadow-sm">
            <div class="text-xl font-black text-indigo-800">{{ $totalMalam }}</div>
            <div class="text-[11px] font-bold text-indigo-600 mt-0.5">🌙 Shift Malam</div>
        </div>
        <div class="bg-sky-50 border border-sky-200 rounded-xl p-3 text-center shadow-sm">
            <div class="text-xl font-black text-sky-800">{{ $totalSiang }}</div>
            <div class="text-[11px] font-bold text-sky-600 mt-0.5">🌤️ Shift Siang</div>
        </div>
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-center shadow-sm">
            <div class="text-xl font-black text-slate-600">{{ $totalLibur }}</div>
            <div class="text-[11px] font-bold text-slate-500 mt-0.5">🏠 Hari Libur</div>
        </div>
        <div class="bg-white border border-dashed border-slate-300 rounded-xl p-3 text-center shadow-sm">
            <div class="text-xl font-black text-slate-400">{{ $totalBelum }}</div>
            <div class="text-[11px] font-bold text-slate-400 mt-0.5">Belum Dijadwalkan</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kalender Visual -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center gap-2">
                <span class="section-bar"></span>
                <h2 class="text-sm font-bold text-slate-900">Kalender Shift — {{ $carbonBulan->translatedFormat('F Y') }}</h2>
            </div>

            <div class="p-5">
                <!-- Day headers -->
                <div class="grid grid-cols-7 gap-1.5 mb-2">
                    @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $idx => $hari)
                        <div class="text-center text-[10px] font-bold {{ in_array($idx,[0,6]) ? 'text-rose-400' : 'text-slate-400' }} pb-1">
                            {{ $hari }}
                        </div>
                    @endforeach
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-1.5">
                    @php
                        // Padding awal bulan
                        $firstDayOfWeek = $carbonBulan->copy()->startOfMonth()->dayOfWeek; // 0=Sun
                    @endphp
                    @for($pad = 0; $pad < $firstDayOfWeek; $pad++)
                        <div></div>
                    @endfor

                    @for($day = 1; $day <= $carbonBulan->daysInMonth; $day++)
                        @php
                            $date   = $carbonBulan->copy()->day($day);
                            $key    = $date->format('Y-m-d');
                            $jadwal = $jadwals->get($key);
                            $isToday = $date->isToday();
                            $isFuture = $date->isFuture();
                            $isWeekend = $date->isWeekend();
                        @endphp
                        <div class="flex flex-col items-center p-1.5 rounded-xl border transition
                            {{ $isToday ? 'ring-2 ring-[#000d6b] bg-[#eef2ff] border-indigo-200' : 'border-transparent hover:border-slate-200' }}
                            {{ $isFuture ? 'cursor-pointer hover:bg-slate-50' : '' }}"
                            {{ $isFuture ? "onclick=\"openRequestModal('{$date->toDateString()}', '{$jadwal?->tipe_shift}')\"" : '' }}>

                            <div class="text-[10px] font-bold {{ $isWeekend ? 'text-rose-400' : 'text-slate-400' }}">
                                {{ $day }}
                            </div>

                            @if($jadwal)
                                <div class="mt-1 w-8 h-8 rounded-lg flex items-center justify-center text-base
                                    {{ $jadwal->tipe_shift === 'Pagi' ? 'bg-amber-100' : ($jadwal->tipe_shift === 'Malam' ? 'bg-indigo-100' : ($jadwal->tipe_shift === 'Siang' ? 'bg-sky-100' : 'bg-slate-100')) }}">
                                    @if($jadwal->tipe_shift === 'Pagi') ☀️
                                    @elseif($jadwal->tipe_shift === 'Malam') 🌙
                                    @elseif($jadwal->tipe_shift === 'Siang') 🌤️
                                    @elseif($jadwal->tipe_shift === 'Libur') 🏠
                                    @endif
                                </div>
                                @if($jadwal->status === 'requested')
                                    <div class="text-[8px] text-amber-600 font-bold mt-0.5">pending</div>
                                @endif
                            @else
                                <div class="mt-1 w-8 h-8 rounded-lg border border-dashed border-slate-200 flex items-center justify-center text-slate-200 text-base">
                                    @if($isFuture) ＋ @else · @endif
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <!-- Form Pengajuan Tukar / Perubahan Shift Card -->
            <div id="request-card" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <span class="section-bar"></span>
                    <h3 class="text-sm font-bold text-slate-900">Form Permohonan / Tukar Shift</h3>
                </div>

                <form id="request-form" method="POST" action="{{ route('jadwal-shift.request') }}" class="space-y-3 text-xs">
                    @csrf
                    <div>
                        <label for="req-tanggal" class="block font-semibold text-slate-700 mb-1">Tanggal Shift <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" id="req-tanggal" required value="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs font-medium">
                    </div>

                    <div>
                        <label for="req-shift" class="block font-semibold text-slate-700 mb-1">Shift yang Diinginkan <span class="text-rose-500">*</span></label>
                        <select name="tipe_shift" id="req-shift" required
                                class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs bg-white font-medium">
                            <option value="Pagi">☀️ Shift Pagi</option>
                            <option value="Malam">🌙 Shift Malam</option>
                            <option value="Siang">🌤️ Shift Siang</option>
                            <option value="Libur">🏠 Hari Libur / Off</option>
                        </select>
                    </div>

                    <div>
                        <label for="req-catatan" class="block font-semibold text-slate-700 mb-1">Alasan / Catatan Penukaran <span class="text-rose-500">*</span></label>
                        <textarea name="catatan" id="req-catatan" rows="3" required placeholder="Contoh: Tukar shift dengan Sdr. Ahmad / Ada keperluan keluarga mendesak..."
                                  class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 px-4 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition tracking-wide">
                        Kirim Pengajuan Shift
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Riwayat Jadwal Bulan Ini (Tabel) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center gap-2">
            <span class="section-bar"></span>
            <h2 class="text-sm font-bold text-slate-900">Detail Jadwal — {{ $carbonBulan->translatedFormat('F Y') }}</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3 px-5 font-bold tracking-wider">Tanggal</th>
                        <th class="py-3 px-5 font-bold tracking-wider">Shift</th>
                        <th class="py-3 px-5 font-bold tracking-wider">Jam Kerja</th>
                        <th class="py-3 px-5 font-bold tracking-wider">Status</th>
                        <th class="py-3 px-5 font-bold tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 text-slate-700">
                    @forelse($jadwals->sortBy('tanggal') as $j)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-5 font-medium whitespace-nowrap">
                                {{ $j->tanggal->translatedFormat('d F Y (l)') }}
                            </td>
                            <td class="py-3.5 px-5">
                                <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider border {{ $j->getBadgeColor() }}">
                                    {{ $j->getShiftLabel() }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5 font-medium whitespace-nowrap text-slate-600">
                                @if(!$j->isLibur())
                                    {{ substr($j->getJamMasukEfektif() ?? '–', 0, 5) }} – {{ substr($j->getJamPulangEfektif() ?? '–', 0, 5) }}
                                @else
                                    <span class="text-slate-300">–</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-5">
                                @if($j->status === 'confirmed')
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Dikonfirmasi</span>
                                @elseif($j->status === 'requested')
                                    <div class="flex items-center gap-2">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">Menunggu ACC</span>
                                        <form method="POST" action="{{ route('jadwal-shift.cancel', $j->id) }}" class="inline"
                                              onsubmit="return confirm('Batalkan pengajuan shift ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[10px] text-rose-600 hover:underline font-bold">Batal</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                            <td class="py-3.5 px-5 text-slate-400 text-[11px]">{{ $j->catatan ?: '–' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-slate-400 text-xs">
                                Belum ada jadwal shift bulan ini. Hubungi Danru/Koordinator untuk mendapatkan jadwal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function openRequestModal(tanggal, existingShift) {
        document.getElementById('req-tanggal').value = tanggal;
        if (existingShift) {
            document.getElementById('req-shift').value = existingShift;
        }
        document.getElementById('req-catatan').focus();
        document.getElementById('request-form').scrollIntoView({behavior: 'smooth', block: 'center'});
    }
</script>
@endsection
