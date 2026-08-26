@extends('layouts.app')

@section('title', 'Edit Employee - PT Inti Sarana Wijaya')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('admin.pegawai.index') }}" class="hover:text-[#000d6b] transition">Manage Employees</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Edit Employee</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Employee: {{ $pegawai->nama }}</h1>
        </div>

        <a href="{{ route('admin.pegawai.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
            &larr; Back to Directory
        </a>
    </div>

    <!-- Signature Status & Reset Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Digital Signature Status</div>
            <div class="mt-1 flex items-center gap-2">
                @if($pegawai->hasSignature())
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span>✓</span> Active & Verified
                    </span>
                    <span class="text-xs text-slate-400">
                        ({{ $pegawai->signature_updated_at ? $pegawai->signature_updated_at->format('d/m/Y H:i') : '-' }})
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        <span>✕</span> Not Created
                    </span>
                @endif
            </div>
        </div>

        @if($pegawai->hasSignature())
            <div class="flex items-center gap-3">
                <div class="bg-slate-50 border border-slate-200 rounded-lg p-2 h-12 w-28 flex items-center justify-center">
                    <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="TTD" class="max-h-full max-w-full object-contain">
                </div>
                <form method="POST" action="{{ route('admin.pegawai.reset-signature', $pegawai) }}"
                      onsubmit="return confirm('Reset tanda tangan digital pegawai ini?');">
                    @csrf
                    <button type="submit"
                            class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg transition">
                        Reset TTD
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-2 mb-6">
            <span class="section-bar"></span>
            <h2 class="text-sm font-bold text-slate-900">Update Profile Details</h2>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl space-y-1">
                <div class="font-bold">Please correct the following errors:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.pegawai.update', $pegawai) }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label for="foto" class="block font-semibold text-slate-700 mb-1">
                    Foto Profil (JPG, PNG, WEBP)
                </label>
                <div class="flex items-center gap-4">
                    @if($pegawai->hasFoto())
                        <img src="{{ $pegawai->getFotoUrl() }}" alt="{{ $pegawai->nama }}" class="w-14 h-14 rounded-xl object-cover border border-slate-300 shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-[#000d6b] text-white font-bold flex items-center justify-center text-base shrink-0">
                            {{ $pegawai->getInitials() }}
                        </div>
                    @endif
                    <input type="file" name="foto" id="foto" accept="image/*"
                           class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-xs text-slate-700 bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#000d6b] file:text-white hover:file:bg-[#001253]">
                </div>
            </div>

            <div>
                <label for="nama" class="block font-semibold text-slate-700 mb-1">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" value="{{ old('nama', $pegawai->nama) }}" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm font-medium">
            </div>

            <div>
                <label for="email" class="block font-semibold text-slate-700 mb-1">
                    Work Email <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email', $pegawai->email) }}" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm font-medium">
            </div>

            <div>
                <label for="divisi_id" class="block font-semibold text-slate-700 mb-1">
                    Division & Working Hours (Shift) <span class="text-rose-500">*</span>
                </label>
                <select name="divisi_id" id="divisi_id" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm bg-white font-medium">
                    @foreach($divisis as $div)
                        <option value="{{ $div->id }}" {{ old('divisi_id', $pegawai->divisi_id) == $div->id ? 'selected' : '' }}>
                            {{ $div->nama }} ({{ substr($div->jam_masuk, 0, 5) }} - {{ substr($div->jam_pulang, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pt-3 border-t border-slate-100">
                <label for="password" class="block font-semibold text-slate-700 mb-1">
                    Reset Password (Leave blank to keep unchanged)
                </label>
                <input type="password" name="password" id="password"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm placeholder:text-slate-400"
                       placeholder="Enter new password if changing">
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div class="pt-2">
                    <label for="role" class="block font-semibold text-slate-700 mb-1">
                        Role & Hak Akses Pengguna <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" id="role" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm bg-white font-medium">
                        <option value="staff" {{ old('role', $pegawai->role ?: 'staff') == 'staff' ? 'selected' : '' }}>
                            Staff Biasa (Hanya Absen Masuk/Pulang & Riwayat Pribadi)
                        </option>
                        <option value="admin_divisi" {{ old('role', $pegawai->role) == 'admin_divisi' ? 'selected' : '' }}>
                            Admin Divisi / Koordinator (Danru Satpam, Supervisor CS - Kelola Anggota & Shift Divisinya)
                        </option>
                        <option value="super_admin" {{ old('role', $pegawai->role) == 'super_admin' || ($pegawai->is_admin && empty($pegawai->role)) ? 'selected' : '' }}>
                            Super Administrator (Akses Penuh Seluruh Perusahaan & Semua Divisi)
                        </option>
                    </select>
                </div>
            @endif

            <div class="pt-4 flex items-center justify-end gap-2">
                <a href="{{ route('admin.pegawai.index') }}"
                   class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm transition">
                    Update Employee
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
