@extends('layouts.app')

@section('title', 'Leave Management & Approvals - PT Inti Sarana Wijaya')

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
                <span class="text-slate-800 font-bold">Leave Approvals</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Persetujuan & Kelola Cuti</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Tinjau dan setujui permohonan cuti tahunan, cuti sakit, dan izin pegawai. Cuti yang disetujui otomatis masuk ke rekap absensi.
            </p>
        </div>
    </div>

    <!-- Stats Row Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pengajuan</div>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</div>
        </div>

        <div class="bg-white rounded-xl border border-amber-200 bg-amber-50/20 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu (Pending)</div>
            <div class="text-2xl font-black text-amber-800 mt-1">{{ $stats['pending'] }}</div>
        </div>

        <div class="bg-white rounded-xl border border-emerald-200 bg-emerald-50/20 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Disetujui (Approved)</div>
            <div class="text-2xl font-black text-emerald-800 mt-1">{{ $stats['approved'] }}</div>
        </div>

        <div class="bg-white rounded-xl border border-rose-200 bg-rose-50/20 p-4 shadow-sm">
            <div class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Ditolak (Rejected)</div>
            <div class="text-2xl font-black text-rose-800 mt-1">{{ $stats['rejected'] }}</div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.cuti.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-5">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama pegawai..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs">
            </div>

            <div class="sm:col-span-5">
                <select name="status" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs bg-white">
                    <option value="">Semua Status Permohonan</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan (Pending)</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak (Rejected)</option>
                </select>
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="w-full py-2.5 px-3 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Leave Requests Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Jenis Cuti</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Periode Tanggal</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Durasi</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Status</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($cutis as $item)
                        @php
                            $words = explode(' ', trim($item->pegawai->nama ?? 'User'));
                            $initials = count($words) >= 2 
                                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                : strtoupper(substr($item->pegawai->nama ?? 'US', 0, 2));
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Pegawai Cell -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#e2e8f0] text-[#1e3a8a] font-bold flex items-center justify-center text-xs shrink-0 border border-slate-300/60">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $item->pegawai->nama ?? 'Pegawai' }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">
                                            {{ $item->pegawai->area_kerja ?: ($item->pegawai->divisi->nama ?? 'Staff') }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Jenis Cuti & Alasan -->
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900">{{ $item->tipe_cuti }}</div>
                                <div class="text-[11px] text-slate-500 max-w-xs truncate" title="{{ $item->alasan }}">{{ $item->alasan }}</div>
                                @if($item->catatan_admin)
                                    <div class="text-[10px] text-slate-400 mt-1 italic">
                                        Catatan: {{ $item->catatan_admin }}
                                    </div>
                                @endif
                            </td>

                            <!-- Periode Tanggal -->
                            <td class="py-4 px-5 whitespace-nowrap font-medium text-slate-800">
                                {{ $item->tanggal_mulai->format('d M Y') }} s/d {{ $item->tanggal_selesai->format('d M Y') }}
                            </td>

                            <!-- Durasi -->
                            <td class="py-4 px-5 whitespace-nowrap font-bold text-slate-900">
                                <span class="inline-block px-2.5 py-1 rounded text-xs font-extrabold bg-slate-100 text-slate-800">
                                    {{ $item->jumlah_hari }} Hari
                                </span>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-4 px-5 whitespace-nowrap">
                                <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider uppercase {{ $item->getStatusBadgeClass() }}">
                                    {{ $item->getStatusLabel() }}
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right space-x-2 whitespace-nowrap">
                                @if($item->isPending())
                                    <form method="POST" action="{{ route('admin.cuti.approve', $item->id) }}" class="inline"
                                          onsubmit="return confirm('Setujui pengajuan cuti {{ $item->pegawai->nama }} selama {{ $item->jumlah_hari }} hari?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold rounded-lg transition">
                                            ✓ Setujui
                                        </button>
                                    </form>

                                    <button type="button" onclick="openRejectModal({{ $item->id }}, '{{ addslashes($item->pegawai->nama) }}')"
                                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg transition">
                                        ✕ Tolak
                                    </button>
                                @else
                                    <span class="text-xs text-slate-400 font-medium">Selesai Diproses</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada data permohonan cuti yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($cutis->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $cutis->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Tolak Cuti -->
<div id="reject-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Alasan Penolakan Cuti</h3>
            <button type="button" onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="reject-form" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            <div>
                <p id="reject-pegawai-text" class="text-slate-600 mb-2 font-medium"></p>
                <label for="catatan_admin" class="block font-semibold text-slate-700 mb-1">Tuliskan Catatan Penolakan <span class="text-rose-500">*</span></label>
                <textarea name="catatan_admin" id="catatan_admin" rows="3" required placeholder="Contoh: Jadwal proyek mendesak / Kuota cuti divisi hari tersebut sudah penuh..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-rose-500 outline-none text-slate-800 text-xs sm:text-sm"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg shadow-sm">Tolak Pengajuan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(cutiId, namaPegawai) {
        document.getElementById('reject-pegawai-text').innerText = 'Menolak pengajuan cuti untuk: ' + namaPegawai;
        document.getElementById('reject-form').action = '/admin/cuti/' + cutiId + '/reject';
        document.getElementById('reject-modal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('reject-modal').classList.add('hidden');
    }
</script>
@endsection
