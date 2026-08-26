@extends('layouts.app')

@section('title', 'Employee Profile - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Top Breadcrumb / Header Title -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Employee Profile</h1>
            <p class="text-xs text-slate-500 mt-0.5">Manage personal information, contact details, and account security.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile.signature') }}"
               class="px-4 py-2 bg-white border border-slate-300 hover:border-[#000d6b] text-slate-700 hover:text-[#000d6b] text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                <span>✍️</span> Digital Signature
            </a>
            <a href="{{ route('export.sendiri') }}"
               class="px-4 py-2 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                <span>📥</span> Export Attendance (.xlsx)
            </a>
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Left Column: Profile Card + Leave Balance (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- 1. Profile Summary Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden text-center p-6 relative">
                <!-- Top Navy Accent Line -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#000d6b]"></div>

                <!-- Avatar Picture -->
                <div class="mt-2 relative inline-block">
                    @if($pegawai->hasFoto())
                        <img src="{{ $pegawai->getFotoUrl() }}"
                             alt="{{ $pegawai->nama }}"
                             class="w-28 h-28 rounded-2xl object-cover border-2 border-slate-200 shadow-md mx-auto">
                    @else
                        <div class="w-28 h-28 rounded-2xl bg-[#000d6b] text-white font-extrabold flex items-center justify-center text-3xl border-2 border-slate-200 shadow-md mx-auto">
                            {{ $pegawai->getInitials() }}
                        </div>
                    @endif
                    @if($pegawai->is_admin)
                        <span class="absolute -bottom-2 -right-2 bg-[#000d6b] text-white text-[10px] font-bold px-2 py-0.5 rounded-md shadow">
                            ADMIN
                        </span>
                    @endif
                </div>

                <!-- Name & Role -->
                <h2 class="text-lg font-extrabold text-slate-900 mt-4">{{ $pegawai->nama }}</h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">
                    {{ $pegawai->area_kerja ?: 'Senior System Administrator' }}
                </p>

                <!-- Employee ID Badge -->
                <div class="mt-3">
                    <span class="inline-block px-3 py-1 bg-[#eef2ff] text-[#000d6b] border border-indigo-100 rounded-md text-xs font-bold tracking-wide">
                        ID: ISW-{{ date('Y') }}-{{ str_pad($pegawai->id, 3, '0', STR_PAD_LEFT) }}
                    </span>
                </div>

                <!-- Signature Status Badge -->
                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-medium">Digital Signature:</span>
                    @if($pegawai->hasSignature())
                        <span class="text-emerald-700 font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span> Active
                        </span>
                    @else
                        <a href="{{ route('profile.signature') }}" class="text-rose-600 font-bold hover:underline">
                            ! Create Signature
                        </a>
                    @endif
                </div>
            </div>

            <!-- 2. Leave Balance Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <!-- Section Header -->
                <div class="flex items-center gap-2 mb-4">
                    <span class="section-bar"></span>
                    <h3 class="text-sm font-bold text-slate-900">Leave Balance</h3>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Annual Leave</span>
                        <span class="font-bold text-slate-800">12 Days</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Sick Leave</span>
                        <span class="font-bold text-slate-800">4 Days</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Compensatory</span>
                        <span class="font-bold text-slate-800">0 Days</span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-slate-100">
                        <span class="text-slate-500 font-medium">Present this Month</span>
                        <span class="font-bold text-emerald-700">{{ $stats['total_bulan_ini'] ?? 0 }} Days</span>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="button" onclick="alert('Untuk pengajuan cuti, silakan hubungi HR Department PT Inti Sarana Wijaya.');"
                            class="w-full py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition text-center tracking-wide">
                        Request Leave
                    </button>
                </div>
            </div>

        </div>

        <!-- Right Column: Personal Info + Contact + Account Settings (8 Cols) -->
        <div class="lg:col-span-8 space-y-6">
            
            <!-- 3. Personal Information Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <span class="section-bar"></span>
                    <h3 class="text-sm font-bold text-slate-900">Personal Information</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 gap-x-6 text-xs">
                    <div>
                        <div class="text-slate-400 font-medium text-[11px] mb-1">Full Name</div>
                        <div class="text-slate-900 font-bold text-sm">{{ $pegawai->nama }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-medium text-[11px] mb-1">Employee ID</div>
                        <div class="text-slate-900 font-bold text-sm">ISW-{{ date('Y') }}-{{ str_pad($pegawai->id, 3, '0', STR_PAD_LEFT) }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-medium text-[11px] mb-1">Department</div>
                        <div class="text-slate-900 font-bold text-sm">{{ $pegawai->area_kerja ?: 'Information Technology' }}</div>
                    </div>

                    <div>
                        <div class="text-slate-400 font-medium text-[11px] mb-1">Join Date</div>
                        <div class="text-slate-900 font-bold text-sm">
                            {{ $pegawai->created_at ? $pegawai->created_at->format('d F Y') : '15 March 2024' }}
                        </div>
                    </div>

                    <div class="sm:col-span-2 pt-2 border-t border-slate-100">
                        <div class="text-slate-400 font-medium text-[11px] mb-2">Direct Supervisor</div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 font-bold flex items-center justify-center text-xs">
                                HW
                            </div>
                            <div>
                                <div class="text-slate-900 font-bold text-xs">Hendra Wijaya</div>
                                <div class="text-[10px] text-slate-400">IT / Operations Director</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Contact Details Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-2">
                        <span class="section-bar"></span>
                        <h3 class="text-sm font-bold text-slate-900">Contact Details</h3>
                    </div>
                    <a href="#account-settings" class="text-xs font-bold text-[#000d6b] hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                        Edit
                    </a>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Work Email -->
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-slate-400 font-medium text-[11px]">Email Address (Work)</div>
                            <div class="text-slate-900 font-semibold text-xs mt-0.5">{{ $pegawai->email }}</div>
                        </div>
                    </div>

                    <!-- Phone Number -->
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-slate-400 font-medium text-[11px]">Phone Number</div>
                            <div class="text-slate-900 font-semibold text-xs mt-0.5">+62 812 3456 7890</div>
                        </div>
                    </div>

                    <!-- Current Address -->
                    <div class="flex items-start gap-3">
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="text-slate-400 font-medium text-[11px]">Current Address</div>
                            <div class="text-slate-900 font-semibold text-xs mt-0.5">Jl. Sudirman No. 45, Kebayoran Baru, Jakarta Selatan, 12190</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 5. Account Settings Card (Update Form) -->
            <div id="account-settings" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
                <div class="flex items-center gap-2 mb-5">
                    <span class="section-bar"></span>
                    <h3 class="text-sm font-bold text-slate-900">Account Settings</h3>
                </div>

                @if ($errors->any())
                    <div class="mb-5 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl space-y-1">
                        @foreach ($errors->all() as $error)
                            <div>⚠️ {{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="foto" class="block font-semibold text-slate-700 mb-1">Foto Profil (JPG, PNG, WEBP, Maks. 4MB)</label>
                        <div class="flex items-center gap-3">
                            <input type="file" name="foto" id="foto" accept="image/*"
                                   class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-700 bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#000d6b] file:text-white hover:file:bg-[#001253]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="nama" class="block font-semibold text-slate-700 mb-1">Full Name</label>
                            <input type="text" name="nama" id="nama" value="{{ old('nama', $pegawai->nama) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 font-medium">
                        </div>

                        <div>
                            <label for="email" class="block font-semibold text-slate-700 mb-1">Work Email</label>
                            <input type="email" name="email" id="email" value="{{ old('email', $pegawai->email) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 font-medium">
                        </div>
                    </div>

                    <div>
                        <label for="area_kerja" class="block font-semibold text-slate-700 mb-1">Department / Area Kerja</label>
                        <input type="text" name="area_kerja" id="area_kerja" value="{{ old('area_kerja', $pegawai->area_kerja) }}"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 font-medium"
                               placeholder="e.g. Information Technology / Operasional">
                    </div>

                    <div class="pt-3 border-t border-slate-100">
                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-3">Change Password (Leave blank to keep current)</div>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block font-semibold text-slate-700 mb-1">New Password</label>
                                <input type="password" name="password" id="password"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 font-medium"
                                       placeholder="••••••••">
                            </div>

                            <div>
                                <label for="password_confirmation" class="block font-semibold text-slate-700 mb-1">Confirm New Password</label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 font-medium"
                                       placeholder="••••••••">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit"
                                class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-xs rounded-lg shadow-sm transition tracking-wide">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection
