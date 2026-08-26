@extends('layouts.app')

@section('title', 'Jadwal Shift Pegawai - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Jadwal Shift</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Kelola Jadwal Shift Per Divisi</h1>
            <p class="text-xs text-slate-500 mt-1">Atur jadwal shift sesuai divisi/departemen (Satpam, Cleaning Service, Operasional, Staff). Klik sel kalender untuk set shift.</p>
        </div>

        <!-- Month Nav -->
        <div class="flex items-center gap-2">
            @php
                $prevBulan = $carbonBulan->copy()->subMonth()->format('Y-m');
                $nextBulan = $carbonBulan->copy()->addMonth()->format('Y-m');
            @endphp
            <a href="{{ route('admin.jadwal-shift.index', ['bulan' => $prevBulan, 'area' => request('area'), 'divisi_id' => request('divisi_id')]) }}"
               class="px-3 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 text-xs font-bold shadow-sm">← Prev</a>
            <div class="px-4 py-2 bg-[#000d6b] text-white rounded-lg text-xs font-bold min-w-[110px] text-center shadow">
                {{ $carbonBulan->translatedFormat('F Y') }}
            </div>
            <a href="{{ route('admin.jadwal-shift.index', ['bulan' => $nextBulan, 'area' => request('area'), 'divisi_id' => request('divisi_id')]) }}"
               class="px-3 py-2 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 text-slate-600 text-xs font-bold shadow-sm">Next →</a>
        </div>
    </div>

    <!-- Legends & Filter -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <!-- Legend -->
        <div class="flex flex-wrap items-center gap-3">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Tipe Shift:</span>
            <span class="px-2.5 py-1 rounded text-[10px] font-bold border bg-amber-100 text-amber-800 border-amber-200">☀️ Pagi</span>
            <span class="px-2.5 py-1 rounded text-[10px] font-bold border bg-indigo-100 text-indigo-800 border-indigo-200">🌙 Malam</span>
            <span class="px-2.5 py-1 rounded text-[10px] font-bold border bg-sky-100 text-sky-800 border-sky-200">🌤️ Siang</span>
            <span class="px-2.5 py-1 rounded text-[10px] font-bold border bg-slate-100 text-slate-500 border-slate-200">🏠 Libur</span>
            <span class="px-2.5 py-1 rounded text-[10px] font-bold border bg-white text-slate-300 border-dashed border-slate-300">Belum Dijadwalkan</span>
        </div>

        <!-- Filter Divisi / Department / Area -->
        <form method="GET" action="{{ route('admin.jadwal-shift.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <select name="divisi_id" class="px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs bg-white">
                <option value="">Semua Divisi</option>
                @foreach($divisis as $divisi)
                    <option value="{{ $divisi->id }}" {{ (string) request('divisi_id') === (string) $divisi->id ? 'selected' : '' }}>{{ $divisi->nama }}</option>
                @endforeach
            </select>
            <select name="area" class="px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs bg-white">
                <option value="">Semua Area</option>
                @foreach($areas as $area)
                    <option value="{{ $area }}" {{ request('area') === $area ? 'selected' : '' }}>{{ $area }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-3.5 py-2 bg-[#000d6b] text-white text-xs font-bold rounded-lg shadow-sm">Filter</button>
            @if(request('area') || request('divisi_id'))
                <a href="{{ route('admin.jadwal-shift.index', ['bulan' => $bulan]) }}" class="px-3 py-2 bg-slate-100 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-200">Reset</a>
            @endif
        </form>
    </div>

    <!-- Bulk Assign Form -->
    <div class="bg-gradient-to-r from-[#eef2ff] to-white rounded-xl border border-indigo-100 p-5 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
            <span class="section-bar"></span>
            <h3 class="text-sm font-bold text-slate-900">Input Jadwal Massal Per Pegawai (Mingguan / Bulanan)</h3>
        </div>
        <form method="POST" action="{{ route('admin.jadwal-shift.bulk') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            @csrf
            <div class="sm:col-span-3">
                <label class="block font-semibold text-slate-700 mb-1">Pilih Pegawai & Divisi</label>
                <select name="pegawai_id" required class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-xs bg-white">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawaisGrouped as $groupName => $groupMembers)
                        <optgroup label="Divisi / Area: {{ $groupName }}">
                            @foreach($groupMembers as $p)
                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->area_kerja ?: 'Staff' }})</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Dari Tanggal</label>
                <input type="date" name="tanggal_dari" required value="{{ $carbonBulan->copy()->startOfMonth()->toDateString() }}"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-xs">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="tanggal_sampai" required value="{{ $carbonBulan->copy()->endOfMonth()->toDateString() }}"
                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-xs">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-semibold text-slate-700 mb-1">Tipe Shift</label>
                <select name="tipe_shift" required class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-xs bg-white">
                    <option value="Pagi">☀️ Shift Pagi</option>
                    <option value="Malam">🌙 Shift Malam</option>
                    <option value="Siang">🌤️ Shift Siang</option>
                    <option value="Libur">🏠 Hari Libur</option>
                </select>
            </div>
            <div class="sm:col-span-3">
                <label class="block font-semibold text-slate-700 mb-1">Hari Aktif</label>
                <div class="flex flex-wrap gap-1.5 mt-1">
                    @php $hari = ['Min','Sen','Sel','Rab','Kam','Jum','Sab']; @endphp
                    @foreach($hari as $idx => $h)
                        <label class="flex items-center gap-1 cursor-pointer">
                            <input type="checkbox" name="hari_aktif[]" value="{{ $idx }}"
                                   {{ in_array($idx, [1,2,3,4,5]) ? 'checked' : '' }}
                                   class="w-3.5 h-3.5 rounded text-[#000d6b]">
                            <span class="text-[11px] font-semibold {{ in_array($idx, [0,6]) ? 'text-rose-500' : 'text-slate-700' }}">{{ $h }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="sm:col-span-12">
                <button type="submit" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Assign Jadwal Massal
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Render Calendar Grid Grouped by Division / Department -->
    @forelse($pegawaisGrouped as $groupName => $members)
        <div class="space-y-3">
            <!-- Division Header Banner -->
            <div class="flex items-center justify-between bg-[#000d6b] text-white px-5 py-3.5 rounded-xl shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-base">
                        @if(stripos($groupName, 'satpam') !== false || stripos($groupName, 'security') !== false) 🛡️
                        @elseif(stripos($groupName, 'cleaning') !== false || stripos($groupName, 'cs') !== false) 🧹
                        @elseif(stripos($groupName, 'operasional') !== false || stripos($groupName, 'it') !== false) ⚙️
                        @else 🏢
                        @endif
                    </div>
                    <div>
                        <h2 class="text-sm font-extrabold tracking-wide uppercase">DIVISI / AREA: {{ $groupName }}</h2>
                        <p class="text-[11px] text-indigo-200 font-normal">{{ $members->count() }} Anggota Pegawai Dijadwalkan</p>
                    </div>
                </div>
                <span class="text-xs bg-white/20 px-3 py-1 rounded-full font-bold">
                    {{ $carbonBulan->translatedFormat('F Y') }}
                </span>
            </div>

            <!-- Pegawai Rows inside this Division -->
            <div class="space-y-3">
                @foreach($members as $pegawai)
                    @php
                        $daysInMonth = $carbonBulan->daysInMonth;
                        $pegawaiJadwals = $jadwals->filter(fn($v, $k) => str_starts_with($k, $pegawai->id . '_'));
                    @endphp
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <!-- Pegawai Sub-Header -->
                        <div class="flex items-center justify-between px-5 py-3 bg-slate-50 border-b border-slate-200">
                            <div class="flex items-center gap-3">
                                @php
                                    $words = explode(' ', trim($pegawai->nama));
                                    $initials = count($words) >= 2
                                        ? strtoupper(substr($words[0],0,1).substr($words[1],0,1))
                                        : strtoupper(substr($pegawai->nama,0,2));
                                @endphp
                                <div class="w-8 h-8 rounded-full bg-[#e2e8f0] text-[#1e3a8a] font-bold flex items-center justify-center text-xs border border-slate-300/60">
                                    {{ $initials }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900">{{ $pegawai->nama }}</div>
                                    <div class="text-[11px] text-slate-400 font-medium">
                                        {{ $pegawai->area_kerja ?: ($pegawai->divisi?->nama ?? 'Staff') }}
                                        @if($pegawai->role && $pegawai->role !== 'staff')
                                            <span class="ml-1 text-[10px] px-1.5 py-0.5 bg-indigo-100 text-indigo-700 font-bold rounded">
                                                {{ strtoupper($pegawai->role) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @php
                                    $totalHadir = $pegawaiJadwals->where('tipe_shift','!=','Libur')->count();
                                    $totalLibur = $pegawaiJadwals->where('tipe_shift','Libur')->count();
                                @endphp
                                <span class="text-[11px] text-slate-500">Shift Kerja: <strong class="text-emerald-700 font-bold">{{ $totalHadir }} Hari</strong></span>
                                <span class="text-[11px] text-slate-500">Libur: <strong class="text-slate-700 font-bold">{{ $totalLibur }} Hari</strong></span>
                            </div>
                        </div>

                        <!-- Calendar Strip for Employee -->
                        <div class="overflow-x-auto p-4">
                            <div class="flex gap-1.5 min-w-max">
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $date = $carbonBulan->copy()->day($day);
                                        $key  = $pegawai->id . '_' . $date->format('Y-m-d');
                                        $jadwal = $jadwals->get($key);
                                        $isWeekend = $date->isWeekend();
                                        $isToday = $date->isToday();
                                    @endphp
                                    <div class="flex flex-col items-center group cursor-pointer"
                                         onclick="openAssignModal({{ $pegawai->id }}, '{{ addslashes($pegawai->nama) }}', '{{ $date->toDateString() }}', '{{ $jadwal?->tipe_shift ?? '' }}')"
                                         title="{{ $date->translatedFormat('l, d M Y') }}{{ $jadwal ? ' — ' . $jadwal->getShiftLabel() : ' — Klik untuk atur shift' }}">

                                        <!-- Day header -->
                                        <div class="text-[10px] font-bold {{ $isWeekend ? 'text-rose-400' : 'text-slate-400' }} mb-1">
                                            {{ $date->format('D')[0] }}
                                        </div>
                                        <div class="text-[11px] font-extrabold {{ $isToday ? 'text-[#000d6b]' : ($isWeekend ? 'text-rose-500' : 'text-slate-700') }} mb-1">
                                            {{ $day }}
                                        </div>

                                        <!-- Shift Cell -->
                                        <div class="w-10 h-10 rounded-lg border flex items-center justify-center text-sm transition-transform group-hover:scale-110 group-hover:shadow-md
                                            {{ $isToday ? 'ring-2 ring-[#000d6b] ring-offset-1' : '' }}
                                            @if($jadwal)
                                                @if($jadwal->tipe_shift === 'Pagi') bg-amber-100 border-amber-300
                                                @elseif($jadwal->tipe_shift === 'Malam') bg-indigo-100 border-indigo-300
                                                @elseif($jadwal->tipe_shift === 'Siang') bg-sky-100 border-sky-300
                                                @elseif($jadwal->tipe_shift === 'Libur') bg-slate-100 border-slate-200
                                                @else bg-white border-slate-200
                                                @endif
                                                {{ $jadwal->status === 'requested' ? 'ring-2 ring-amber-400' : '' }}
                                            @else bg-white border-dashed border-slate-200 @endif">
                                            @if($jadwal)
                                                @if($jadwal->tipe_shift === 'Pagi') ☀️
                                                @elseif($jadwal->tipe_shift === 'Malam') 🌙
                                                @elseif($jadwal->tipe_shift === 'Siang') 🌤️
                                                @elseif($jadwal->tipe_shift === 'Libur') 🏠
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Status dot for requested -->
                                        @if($jadwal && $jadwal->status === 'requested')
                                            <div class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-0.5" title="Menunggu konfirmasi"></div>
                                        @else
                                            <div class="w-1.5 h-1.5 mt-0.5"></div>
                                        @endif
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="text-center py-16 text-slate-400 text-sm bg-white rounded-xl border border-slate-200">
            Belum ada data pegawai pada divisi/area ini.
        </div>
    @endforelse
</div>

<!-- Assign Shift Modal -->
<div id="assign-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Assign Shift</h3>
                <p id="modal-sub" class="text-[11px] text-slate-500 mt-0.5"></p>
            </div>
            <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
        </div>

        <form id="assign-form" method="POST" action="{{ route('admin.jadwal-shift.store') }}" class="space-y-3 text-xs">
            @csrf
            <input type="hidden" name="pegawai_id" id="modal-pegawai-id">
            <input type="hidden" name="tanggal" id="modal-tanggal">

            <div>
                <label class="block font-semibold text-slate-700 mb-2">Pilih Tipe Shift</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(['Pagi' => ['☀️', 'amber', 'Shift Pagi'], 'Malam' => ['🌙', 'indigo', 'Shift Malam'], 'Siang' => ['🌤️', 'sky', 'Shift Siang'], 'Libur' => ['🏠', 'slate', 'Hari Istirahat']] as $tipe => [$icon, $color, $desc])
                        <label class="shift-option flex flex-col items-center p-3 rounded-xl border-2 cursor-pointer transition hover:border-[#000d6b]
                            border-slate-200" data-tipe="{{ $tipe }}">
                            <input type="radio" name="tipe_shift" value="{{ $tipe }}" class="sr-only shift-radio" required>
                            <span class="text-xl mb-1">{{ $icon }}</span>
                            <span class="font-bold text-slate-800">{{ $tipe }}</span>
                            <span class="text-[10px] text-slate-400 mt-0.5">{{ $desc }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Catatan (opsional)</label>
                <input type="text" name="catatan" placeholder="Misal: Ganti shift..."
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-xs">
            </div>

            <div class="pt-3 flex justify-between gap-2 border-t border-slate-100">
                <button type="button" id="btn-hapus-jadwal" onclick="hapusJadwal()"
                        class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold rounded-lg text-xs transition">
                    Hapus Shift
                </button>
                <button type="submit" class="px-5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm text-xs">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentPegawaiId = null;
    let currentTanggal = null;

    function openAssignModal(pegawaiId, nama, tanggal, existingShift) {
        currentPegawaiId = pegawaiId;
        currentTanggal = tanggal;

        document.getElementById('modal-pegawai-id').value = pegawaiId;
        document.getElementById('modal-tanggal').value = tanggal;
        document.getElementById('modal-sub').innerText = nama + ' — ' + tanggal;

        document.querySelectorAll('.shift-option').forEach(opt => {
            opt.classList.remove('border-[#000d6b]', 'bg-indigo-50');
            opt.classList.add('border-slate-200');
            const radio = opt.querySelector('.shift-radio');
            if (radio.value === existingShift) {
                radio.checked = true;
                opt.classList.add('border-[#000d6b]', 'bg-indigo-50');
                opt.classList.remove('border-slate-200');
            }
        });

        document.getElementById('btn-hapus-jadwal').style.display = existingShift ? 'block' : 'none';
        document.getElementById('assign-modal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('assign-modal').classList.add('hidden');
    }

    function hapusJadwal() {
        if (!confirm('Hapus jadwal shift untuk tanggal ini?')) return;
        fetch('{{ route('admin.jadwal-shift.destroy') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({pegawai_id: currentPegawaiId, tanggal: currentTanggal, _method: 'DELETE'})
        }).then(() => location.reload());
    }

    document.querySelectorAll('.shift-option').forEach(opt => {
        opt.addEventListener('click', () => {
            document.querySelectorAll('.shift-option').forEach(o => {
                o.classList.remove('border-[#000d6b]', 'bg-indigo-50');
                o.classList.add('border-slate-200');
            });
            opt.classList.add('border-[#000d6b]', 'bg-indigo-50');
            opt.classList.remove('border-slate-200');
            opt.querySelector('.shift-radio').checked = true;
        });
    });

    document.getElementById('assign-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch(this.action, {method: 'POST', body: formData})
            .then(() => location.reload());
    });

    document.getElementById('assign-modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });
</script>
@endsection
