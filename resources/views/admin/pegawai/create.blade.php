@extends('layouts.app')

@section('title', 'Add New Employee - PT Inti Sarana Wijaya')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('admin.pegawai.index') }}" class="hover:text-[#000d6b] transition">Manage Employees</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Add Employee</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Add New Employee</h1>
        </div>

        <a href="{{ route('admin.pegawai.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
            &larr; Back to Directory
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-2 mb-6">
            <span class="section-bar"></span>
            <h2 class="text-sm font-bold text-slate-900">Employee Details</h2>
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

        <form method="POST" action="{{ route('admin.pegawai.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
            @csrf

            <div>
                <label for="foto" class="block font-semibold text-slate-700 mb-1">
                    Foto Profil (Opsional - JPG, PNG, WEBP)
                </label>
                <input type="file" name="foto" id="foto" accept="image/*"
                       class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-xs text-slate-700 bg-white file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#000d6b] file:text-white hover:file:bg-[#001253]">
            </div>

            <div>
                <label for="nama" class="block font-semibold text-slate-700 mb-1">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required autofocus
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 text-sm placeholder:text-slate-400"
                       placeholder="e.g. Budi Santoso">
            </div>

            <div>
                <label for="email" class="block font-semibold text-slate-700 mb-1">
                    Work Email <span class="text-rose-500">*</span>
                </label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 text-sm placeholder:text-slate-400"
                       placeholder="budi.santoso@isw.co.id">
            </div>

            <div>
                <label for="status_karyawan" class="block font-semibold text-slate-700 mb-1">
                    Kategori Status Karyawan <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="flex items-center gap-2.5 p-3 border rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 border-slate-300">
                        <input type="radio" name="status_karyawan" value="internal" {{ old('status_karyawan', 'internal') == 'internal' ? 'checked' : '' }} onchange="toggleKantorKlien()" class="text-[#000d6b] focus:ring-[#000d6b]">
                        <div>
                            <div class="font-extrabold text-slate-900 text-xs">🏢 Pegawai Asli PT ISW (Internal)</div>
                            <div class="text-[11px] text-slate-500">Staff Head Office / Management ISW</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-2.5 p-3 border rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100 border-slate-300">
                        <input type="radio" name="status_karyawan" value="outsourcing" {{ old('status_karyawan') == 'outsourcing' ? 'checked' : '' }} onchange="toggleKantorKlien()" class="text-[#000d6b] focus:ring-[#000d6b]">
                        <div>
                            <div class="font-extrabold text-slate-900 text-xs">🤝 Pegawai Outsource</div>
                            <div class="text-[11px] text-slate-500">Tenaga Kerja Placement Site Klien</div>
                        </div>
                    </label>
                </div>
            </div>

            <div>
                <label for="divisi_id" class="block font-semibold text-slate-700 mb-1">
                    Division & Working Hours (Shift) <span class="text-rose-500">*</span>
                </label>
                <select name="divisi_id" id="divisi_id" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 text-sm bg-white font-medium">
                    <option value="">-- Select Division Schedule --</option>
                    @foreach($divisis as $div)
                        <option value="{{ $div->id }}" {{ old('divisi_id') == $div->id ? 'selected' : '' }}>
                            {{ $div->nama }} ({{ substr($div->jam_masuk, 0, 5) }} - {{ substr($div->jam_pulang, 0, 5) }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="kantor-klien-container" class="hidden">
                <label for="area_kerja" class="block font-semibold text-slate-700 mb-1">
                    Kantor Klien / Site Placement (Penempatan Kerja Outsourcing) <span class="text-rose-500">*</span>
                </label>
                <select name="area_kerja" id="area_kerja"
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm bg-white font-medium">
                    <option value="">-- Pilih Kantor Klien / Site Project --</option>
                    @foreach($kantorKliens as $kk)
                        <option value="{{ $kk->nama_kantor }}" {{ old('area_kerja') == $kk->nama_kantor ? 'selected' : '' }}>
                            {{ $kk->nama_kantor }} {{ $kk->kode_kantor ? '('.$kk->kode_kantor.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Section Biodata Lengkap Pegawai -->
            <div class="pt-4 border-t border-slate-200 space-y-4">
                <h3 class="font-extrabold text-[#000d6b] text-sm flex items-center gap-2">
                    <span>🪪</span> Biodata Pribadi Pegawai (KTP & Pendidikan)
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nik" class="block font-semibold text-slate-700 mb-1">NIK (KTP)</label>
                        <input type="text" name="nik" id="nik" value="{{ old('nik') }}" maxlength="16" placeholder="e.g. 3174012345670001"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm font-mono">
                    </div>

                    <div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="tempat_lahir" class="block font-semibold text-slate-700 mb-1">Tempat Lahir</label>
                                <input type="text" name="tempat_lahir" id="tempat_lahir" value="{{ old('tempat_lahir') }}" placeholder="Jakarta"
                                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">
                            </div>
                            <div>
                                <label for="tanggal_lahir" class="block font-semibold text-slate-700 mb-1">Tgl Lahir</label>
                                <input type="date" name="tanggal_lahir" id="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                                       class="w-full px-3 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="jenis_kelamin" class="block font-semibold text-slate-700 mb-1">Jenis Kelamin</label>
                        <select name="jenis_kelamin" id="jenis_kelamin" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 text-sm bg-white font-medium">
                            <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label for="pendidikan_terakhir" class="block font-semibold text-slate-700 mb-1">Pendidikan Terakhir</label>
                        <select name="pendidikan_terakhir" id="pendidikan_terakhir" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 text-sm bg-white font-medium">
                            <option value="SMA/SMK" {{ old('pendidikan_terakhir') == 'SMA/SMK' ? 'selected' : '' }}>SMA / SMK</option>
                            <option value="D3" {{ old('pendidikan_terakhir') == 'D3' ? 'selected' : '' }}>D3 (Diploma)</option>
                            <option value="S1" {{ old('pendidikan_terakhir') == 'S1' ? 'selected' : '' }}>S1 (Sarjana)</option>
                            <option value="S2" {{ old('pendidikan_terakhir') == 'S2' ? 'selected' : '' }}>S2 (Magister)</option>
                            <option value="SMP" {{ old('pendidikan_terakhir') == 'SMP' ? 'selected' : '' }}>SMP</option>
                            <option value="SD" {{ old('pendidikan_terakhir') == 'SD' ? 'selected' : '' }}>SD</option>
                        </select>
                    </div>

                    <div>
                        <label for="status_pernikahan" class="block font-semibold text-slate-700 mb-1">Status Pernikahan</label>
                        <select name="status_pernikahan" id="status_pernikahan" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 text-sm bg-white font-medium">
                            <option value="Belum Menikah" {{ old('status_pernikahan') == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                            <option value="Menikah" {{ old('status_pernikahan') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Cerai" {{ old('status_pernikahan') == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="no_hp" class="block font-semibold text-slate-700 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="no_hp" id="no_hp" value="{{ old('no_hp') }}" placeholder="0812xxxxxxxx"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">
                    </div>
                    <div>
                        <label for="kontak_darurat" class="block font-semibold text-slate-700 mb-1">Kontak Darurat (Keluarga)</label>
                        <input type="text" name="kontak_darurat" id="kontak_darurat" value="{{ old('kontak_darurat') }}" placeholder="e.g. Ibu / Istri - 0813xxxxxxxx"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">
                    </div>
                </div>

                <div>
                    <label for="alamat" class="block font-semibold text-slate-700 mb-1">Alamat Lengkap (KTP)</label>
                    <textarea name="alamat" id="alamat" rows="2" placeholder="Alamat rumah sesuai KTP..."
                              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">{{ old('alamat') }}</textarea>
                </div>
            </div>

            {{-- Informasi Rekening Bank (Payroll) --}}
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                    <span class="text-base">💳</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Informasi Rekening Bank (Payroll)</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="nama_bank" class="block font-semibold text-slate-700 mb-1">Nama Bank</label>
                        <input type="text" name="nama_bank" id="nama_bank" value="{{ old('nama_bank') }}" placeholder="e.g. BCA, Mandiri, BRI, BNI"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">
                    </div>
                    <div>
                        <label for="nomor_rekening" class="block font-semibold text-slate-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="nomor_rekening" id="nomor_rekening" value="{{ old('nomor_rekening') }}" placeholder="e.g. 5210987654"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm font-mono">
                    </div>
                    <div>
                        <label for="nama_rekening" class="block font-semibold text-slate-700 mb-1">Atas Nama Rekening</label>
                        <input type="text" name="nama_rekening" id="nama_rekening" value="{{ old('nama_rekening') }}" placeholder="e.g. Sesuai Buku Tabungan"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label for="password" class="block font-semibold text-slate-700 mb-1">
                    Default Initial Password <span class="text-rose-500">*</span>
                </label>
                <input type="password" name="password" id="password" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-slate-800 text-sm placeholder:text-slate-400"
                       placeholder="Min. 8 characters">
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div class="pt-2">
                    <label for="role" class="block font-semibold text-slate-700 mb-1">
                        Role & Hak Akses Pengguna <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" id="role" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-sm bg-white font-medium">
                        <option value="staff" {{ old('role', 'staff') == 'staff' ? 'selected' : '' }}>
                            Staff Biasa (Hanya Absen Masuk/Pulang & Riwayat Pribadi)
                        </option>
                        <option value="admin_divisi" {{ old('role') == 'admin_divisi' ? 'selected' : '' }}>
                            Admin Divisi / Koordinator (Danru Satpam, Supervisor CS - Kelola Anggota & Shift Divisinya)
                        </option>
                        <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>
                            Super Administrator (Akses Penuh Seluruh Perusahaan & Semua Divisi)
                        </option>
                    </select>
                </div>
            @else
                <input type="hidden" name="role" value="staff">
            @endif

            <div class="pt-4 flex items-center justify-end gap-2">
                <a href="{{ route('admin.pegawai.index') }}"
                   class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg transition">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm transition">
                    Save Employee
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    function toggleKantorKlien() {
        const isOutsource = document.querySelector('input[name="status_karyawan"][value="outsourcing"]')?.checked;
        const container = document.getElementById('kantor-klien-container');
        if (container) {
            if (isOutsource) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
    }
    document.addEventListener('DOMContentLoaded', toggleKantorKlien);
</script>
@endsection
