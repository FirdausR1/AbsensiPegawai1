@extends('layouts.app')

@section('title', 'Manage Employees - PT Inti Sarana Wijaya')

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
                <span class="text-slate-800 font-bold">Manage Employees</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Employee Directory & Management</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage employee accounts, roles, departments, and digital signatures.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.pegawai.create') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold text-white bg-[#000d6b] hover:bg-[#001253] rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add New Employee
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Employees</div>
                <div class="text-2xl font-extrabold text-slate-900 mt-1">{{ $stats['total'] }}</div>
            </div>
            <div class="w-10 h-10 bg-[#eef2ff] text-[#000d6b] rounded-xl flex items-center justify-center text-lg font-bold">
                👥
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Active Signatures</div>
                <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['has_signature'] }}</div>
            </div>
            <div class="w-10 h-10 bg-emerald-50 text-emerald-700 rounded-xl flex items-center justify-center text-lg font-bold">
                ✍️
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">System Administrators</div>
                <div class="text-2xl font-extrabold text-[#000d6b] mt-1">{{ $stats['admin'] }}</div>
            </div>
            <div class="w-10 h-10 bg-indigo-50 text-[#000d6b] rounded-xl flex items-center justify-center text-lg font-bold">
                🛡️
            </div>
        </div>
    </div>

    <!-- Employee Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-5">
            <div class="flex items-center gap-2">
                <span class="section-bar"></span>
                <h3 class="text-sm font-bold text-slate-900">All Registered Employees</h3>
            </div>

            <!-- Search Form -->
            <form method="GET" action="{{ route('admin.pegawai.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                       class="px-3 py-1.5 text-xs rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none w-52">
                <button type="submit" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    Search
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 border-b border-slate-200">
                        <th class="py-3 px-4 font-semibold">Employee</th>
                        <th class="py-3 px-4 font-semibold">Department</th>
                        <th class="py-3 px-4 font-semibold">Role</th>
                        <th class="py-3 px-4 font-semibold">Signature</th>
                        <th class="py-3 px-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pegawais as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $item->nama }}</div>
                                <div class="text-[11px] text-slate-400">{{ $item->email }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 font-medium">
                                {{ $item->area_kerja ?: 'General' }}
                            </td>
                            <td class="py-3 px-4">
                                @if($item->is_admin)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-[#eef2ff] text-[#000d6b] border border-indigo-100">
                                        ADMIN
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">
                                        Staff
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4">
                                @if($item->hasSignature())
                                    <span class="text-emerald-700 font-bold text-xs flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                                    </span>
                                @else
                                    <span class="text-rose-600 font-semibold text-xs">None</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <a href="{{ route('admin.pegawai.edit', $item->id) }}"
                                   class="px-2.5 py-1 text-slate-700 hover:text-[#000d6b] bg-slate-100 hover:bg-slate-200 font-bold rounded-md transition text-[11px]">
                                    Edit
                                </a>
                                
                                @if($item->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.pegawai.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai {{ $item->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 font-bold rounded-md transition text-[11px]">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">
                                No employees found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pegawais->hasPages())
            <div class="mt-4 pt-3 border-t border-slate-100">
                {{ $pegawais->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
