@extends('layouts.app')

@section('title', 'Attendance Management - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Attendance Management</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Real-time attendance tracking, filtering, input/koreksi absen terlewat, dan bulk export.
            </p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="openManualModal()"
                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 text-xs sm:text-sm font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
                <span>+</span> Input / Koreksi Absen Terlewat
            </button>

            <a href="{{ route('admin.export.semua', now()->format('Y-m')) }}"
               class="inline-flex items-center justify-center gap-1.5 px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-[#000d6b] hover:bg-[#001253] rounded-lg shadow-sm transition tracking-wide">
                <span>📥</span> Export All (.zip)
            </a>
        </div>
    </div>

    <!-- Search & Filter Card Box -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs sm:text-sm">
            <div class="sm:col-span-4">
                <label for="date" class="block font-semibold text-slate-600 mb-1 text-xs">Filter by Date</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}"
                       class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 bg-white font-medium text-xs sm:text-sm">
            </div>

            <div class="sm:col-span-5">
                <label for="pegawai_id" class="block font-semibold text-slate-600 mb-1 text-xs">Filter by Employee</label>
                <select name="pegawai_id" id="pegawai_id" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-700 bg-white font-medium text-xs sm:text-sm">
                    <option value="">All Employees</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}" {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} ({{ $p->area_kerja ?: 'Staff' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3 flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg transition">
                    Apply Filter
                </button>
                <a href="{{ route('admin.absensi.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Corporate Styled Attendance Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <!-- Navy Solid Table Header -->
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider">Employee</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Date</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Clock-In</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Clock-Out</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Status & Keterlambatan</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($absensis as $item)
                        @php
                            $nama = $item->pegawai->nama ?? 'Unknown';
                            $words = explode(' ', trim($nama));
                            $initials = count($words) >= 2 
                                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                : strtoupper(substr($nama, 0, 2));
                            
                            $menitTerlambat = $item->getMenitTerlambat();
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Employee Column with Initials Badge -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#e2e8f0] text-[#1e3a8a] font-bold flex items-center justify-center text-xs shrink-0 border border-slate-300/60">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $nama }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $item->pegawai->area_kerja ?? 'General' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Date Column -->
                            <td class="py-4 px-5 font-medium text-slate-800">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                            </td>

                            <!-- Clock-In Column -->
                            <td class="py-4 px-5 font-bold text-emerald-700">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>

                            <!-- Clock-Out Column -->
                            <td class="py-4 px-5 font-bold text-[#000d6b]">
                                {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '-' }}
                            </td>

                            <!-- Status Badge Column -->
                            <td class="py-4 px-5 space-y-1">
                                @if(!empty($item->keterangan))
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ $item->keterangan }}
                                    </span>
                                @elseif($item->jam_masuk && $item->jam_pulang)
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-[#e6f4ea] text-[#137333] tracking-wider uppercase">
                                        COMPLETE
                                    </span>
                                @elseif($item->jam_masuk)
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-50 text-amber-800 tracking-wider uppercase border border-amber-200">
                                        IN PROGRESS
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-extrabold bg-[#fce8e6] text-[#c5221f] tracking-wider uppercase">
                                        ALPA
                                    </span>
                                @endif

                                @if($menitTerlambat > 0)
                                    <div>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            Terlambat {{ $menitTerlambat }} Menit
                                        </span>
                                    </div>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td class="py-4 px-5 text-right space-x-1.5 whitespace-nowrap">
                                <button type="button"
                                        onclick="openEditAbsensiModal({{ json_encode($item) }}, '{{ $item->pegawai->nama ?? '' }}')"
                                        class="px-2.5 py-1 text-xs font-bold text-slate-700 hover:text-[#000d6b] bg-slate-100 hover:bg-slate-200 rounded-md transition inline-block">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('admin.absensi.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Hapus catatan absensi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-md transition inline-block">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No attendance records found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination Bar -->
        <div class="px-5 py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
            <div>
                @if(method_exists($absensis, 'firstItem') && $absensis->total() > 0)
                    Showing {{ $absensis->firstItem() }}-{{ $absensis->lastItem() }} of {{ $absensis->total() }} records
                @else
                    Showing {{ count($absensis) }} of {{ count($absensis) }} records
                @endif
            </div>

            @if(method_exists($absensis, 'hasPages') && $absensis->hasPages())
                <div>
                    {{ $absensis->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Input / Koreksi Absen Terlewat -->
<div id="manual-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Input / Koreksi Absen Terlewat</h3>
            <button type="button" onclick="closeManualModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.absensi.manual') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="modal_pegawai_id" class="block font-semibold text-slate-700 mb-1">Pegawai <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" id="modal_pegawai_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->area_kerja ?: 'Staff' }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_tanggal" class="block font-semibold text-slate-700 mb-1">Tanggal Absensi (Bisa Pilih Tanggal Terlewat) <span class="text-rose-500">*</span></label>
                <input type="date" name="tanggal" id="modal_tanggal" value="{{ date('Y-m-d') }}" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="modal_jam_masuk" class="block font-semibold text-slate-700 mb-1">Jam Masuk (HH:MM)</label>
                    <input type="time" name="jam_masuk" id="modal_jam_masuk" value="08:00" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                </div>
                <div>
                    <label for="modal_jam_pulang" class="block font-semibold text-slate-700 mb-1">Jam Pulang (HH:MM)</label>
                    <input type="time" name="jam_pulang" id="modal_jam_pulang" value="17:00" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                </div>
            </div>

            <div>
                <label for="modal_keterangan" class="block font-semibold text-slate-700 mb-1">Keterangan / Alasan (Opsional)</label>
                <input type="text" name="keterangan" id="modal_keterangan" placeholder="Contoh: Koreksi lupa absen / Dinas Luar"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeManualModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Simpan Data Absensi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Single Record -->
<div id="edit-absensi-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900" id="edit-modal-title">Edit Catatan Absensi</h3>
            <button type="button" onclick="closeEditAbsensiModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="edit-absensi-form" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_absensi_jam_masuk" class="block font-semibold text-slate-700 mb-1">Jam Masuk (HH:MM)</label>
                    <input type="time" name="jam_masuk" id="edit_absensi_jam_masuk" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                </div>
                <div>
                    <label for="edit_absensi_jam_pulang" class="block font-semibold text-slate-700 mb-1">Jam Pulang (HH:MM)</label>
                    <input type="time" name="jam_pulang" id="edit_absensi_jam_pulang" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none font-bold">
                </div>
            </div>

            <div>
                <label for="edit_absensi_keterangan" class="block font-semibold text-slate-700 mb-1">Keterangan / Catatan</label>
                <input type="text" name="keterangan" id="edit_absensi_keterangan" placeholder="Contoh: Koreksi Admin / Izin / Sakit"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeEditAbsensiModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Perbarui Absensi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openManualModal() {
        document.getElementById('manual-modal').classList.remove('hidden');
    }
    function closeManualModal() {
        document.getElementById('manual-modal').classList.add('hidden');
    }

    function openEditAbsensiModal(item, employeeName) {
        document.getElementById('edit-modal-title').textContent = 'Edit Absensi: ' + employeeName + ' (' + item.tanggal + ')';
        document.getElementById('edit_absensi_jam_masuk').value = item.jam_masuk ? item.jam_masuk.substring(0, 5) : '';
        document.getElementById('edit_absensi_jam_pulang').value = item.jam_pulang ? item.jam_pulang.substring(0, 5) : '';
        document.getElementById('edit_absensi_keterangan').value = item.keterangan || '';
        document.getElementById('edit-absensi-form').action = '/admin/absensi/' + item.id;
        document.getElementById('edit-absensi-modal').classList.remove('hidden');
    }
    function closeEditAbsensiModal() {
        document.getElementById('edit-absensi-modal').classList.add('hidden');
    }
</script>
@endsection
