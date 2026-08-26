@extends('layouts.app')

@section('title', 'All Attendance - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header & Navigation -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Admin</span>
                <span>/</span>
                <span class="text-slate-800 font-bold">Attendance Records</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Organization Attendance Records</h1>
            <p class="text-xs text-slate-500 mt-0.5">Real-time attendance tracking, filtering, manual log input, and bulk export.</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" onclick="document.getElementById('manual-modal').classList.remove('hidden')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
                <span>➕</span> Input Manual Log
            </button>

            <a href="{{ route('admin.export.semua', now()->format('Y-m')) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#000d6b] hover:bg-[#001253] rounded-lg transition shadow-sm">
                <span>📥</span> Export All (.zip)
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Logged Today</div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total_today'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Clock-Ins Today</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['masuk_today'] }}</div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Clock-Outs Today</div>
            <div class="text-2xl font-extrabold text-[#000d6b] mt-1">{{ $stats['pulang_today'] }}</div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.absensi.index') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs">
            <div>
                <label for="date" class="block font-semibold text-slate-600 mb-1">Filter by Date</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}"
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none">
            </div>

            <div>
                <label for="pegawai_id" class="block font-semibold text-slate-600 mb-1">Filter by Employee</label>
                <select name="pegawai_id" id="pegawai_id" class="w-full px-3 py-2 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none">
                    <option value="">All Employees</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}" {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2 flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm transition">
                    Apply Filter
                </button>
                <a href="{{ route('admin.absensi.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Attendance Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="section-bar"></span>
            <h3 class="text-sm font-bold text-slate-900">Attendance Log</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <th class="py-3 px-4 font-semibold">Date</th>
                        <th class="py-3 px-4 font-semibold">Employee</th>
                        <th class="py-3 px-4 font-semibold">Clock-In</th>
                        <th class="py-3 px-4 font-semibold">Clock-Out</th>
                        <th class="py-3 px-4 font-semibold">Status</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($absensis as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-bold text-slate-800">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900">{{ $item->pegawai->nama ?? 'Unknown' }}</div>
                                <div class="text-[10px] text-slate-400">{{ $item->pegawai->area_kerja ?? 'General' }}</div>
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-700">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-[#000d6b]">
                                {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) . ' WIB' : '-' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($item->jam_masuk && $item->jam_pulang)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        Complete
                                    </span>
                                @elseif($item->jam_masuk)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        No Clock-out
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                        -
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form method="POST" action="{{ route('admin.absensi.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Delete this attendance record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 font-bold rounded-md transition text-[11px]">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                No attendance records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($absensis->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $absensis->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Manual Attendance Modal -->
<div id="manual-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Input Manual Attendance Log</h3>
            <button type="button" onclick="document.getElementById('manual-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.absensi.manual') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="modal_pegawai_id" class="block font-semibold text-slate-700 mb-1">Employee</label>
                <select name="pegawai_id" id="modal_pegawai_id" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none">
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="modal_tanggal" class="block font-semibold text-slate-700 mb-1">Date</label>
                <input type="date" name="tanggal" id="modal_tanggal" value="{{ date('Y-m-d') }}" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="modal_jam_masuk" class="block font-semibold text-slate-700 mb-1">Clock-In (HH:MM)</label>
                    <input type="time" name="jam_masuk" id="modal_jam_masuk" class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none">
                </div>
                <div>
                    <label for="modal_jam_pulang" class="block font-semibold text-slate-700 mb-1">Clock-Out (HH:MM)</label>
                    <input type="time" name="jam_pulang" id="modal_jam_pulang" class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none">
                </div>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('manual-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Save Record</button>
            </div>
        </form>
    </div>
</div>
@endsection
