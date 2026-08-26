@extends('layouts.app')

@section('title', 'User Management - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Top Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">User Management</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Manage system access, roles, and employee records.</p>
        </div>

        <a href="{{ route('admin.pegawai.create') }}"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition tracking-wide">
            <span>+</span> Add New User
        </a>
    </div>

    <!-- Search & Filter Card Box -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.pegawai.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <!-- Search Input with Magnifier -->
            <div class="sm:col-span-8 relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search users by name or ID..."
                       class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 placeholder:text-slate-400 bg-white">
            </div>

            <!-- Department Filter -->
            <div class="sm:col-span-3">
                <select name="department" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-700 bg-white font-medium">
                    <option value="">All Departments</option>
                    @php
                        $departments = \App\Models\Pegawai::whereNotNull('area_kerja')->where('area_kerja', '!=', '')->distinct()->pluck('area_kerja');
                    @endphp
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                            {{ $dept }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Submit Filter Button -->
            <div class="sm:col-span-1 flex gap-1">
                <button type="submit" class="w-full py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Corporate Styled Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <!-- Navy Solid Table Header -->
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider">Employee</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">ID</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Department</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Role</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Status</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($pegawais as $item)
                        @php
                            // Extract initials for the avatar badge
                            $words = explode(' ', trim($item->nama));
                            $initials = count($words) >= 2 
                                ? strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1))
                                : strtoupper(substr($item->nama, 0, 2));
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Employee Column with Initials Badge -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#e2e8f0] text-[#1e3a8a] font-bold flex items-center justify-center text-xs shrink-0 border border-slate-300/60">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $item->nama }}</div>
                                        <div class="text-[11px] text-slate-400 font-normal">{{ $item->email }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- ID Column -->
                            <td class="py-4 px-5 font-medium text-slate-700">
                                ISW-{{ date('Y', strtotime($item->created_at ?? now())) }}-{{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}
                            </td>

                            <!-- Department Column -->
                            <td class="py-4 px-5 text-slate-700 font-medium">
                                {{ $item->area_kerja ?: 'Operations' }}
                            </td>

                            <!-- Role Column -->
                            <td class="py-4 px-5 font-medium">
                                {{ $item->is_admin ? 'Admin' : 'Staff' }}
                            </td>

                            <!-- Status Badge Column -->
                            <td class="py-4 px-5">
                                @if($item->hasSignature())
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-[#e6f4ea] text-[#137333] tracking-wider uppercase">
                                        ACTIVE
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-[#fce8e6] text-[#c5221f] tracking-wider uppercase">
                                        INACTIVE
                                    </span>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td class="py-4 px-5 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.pegawai.edit', $item->id) }}"
                                   class="px-3 py-1.5 text-xs font-bold text-slate-700 hover:text-[#000d6b] bg-slate-100 hover:bg-slate-200 rounded-md transition inline-block">
                                    Edit
                                </a>
                                
                                @if($item->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.pegawai.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Delete employee {{ $item->nama }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-md transition inline-block">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                No employees found matching the criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer Pagination Bar -->
        <div class="px-5 py-4 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-slate-500">
            <div>
                @if(method_exists($pegawais, 'firstItem') && $pegawais->total() > 0)
                    Showing {{ $pegawais->firstItem() }}-{{ $pegawais->lastItem() }} of {{ $pegawais->total() }} users
                @else
                    Showing {{ count($pegawais) }} of {{ count($pegawais) }} users
                @endif
            </div>

            @if(method_exists($pegawais, 'hasPages') && $pegawais->hasPages())
                <div>
                    {{ $pegawais->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
