@extends('layouts.app')

@section('title', 'Data Diri - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-4">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-base font-bold text-slate-800">Data Diri</h1>
            <p class="text-xs text-slate-400 mt-0.5">Lengkapi informasi pribadi Anda.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('profile.signature') }}" class="px-3 py-1.5 bg-white border border-slate-200 text-xs font-semibold text-slate-600 rounded-md hover:bg-slate-50 transition">Tanda Tangan Digital</a>
            <a href="{{ route('profile.ganti-password') }}" class="px-3 py-1.5 bg-white border border-slate-200 text-xs font-semibold text-slate-600 rounded-md hover:bg-slate-50 transition">Ganti Password</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Foto & Info Dasar --}}
        <div class="bg-white border border-slate-200 rounded-lg p-5 space-y-4">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide border-b border-slate-100 pb-2">Informasi Akun</h3>

            <div class="flex items-center gap-4">
                @if($pegawai->hasFoto())
                    <img src="{{ $pegawai->getFotoUrl() }}" class="w-16 h-16 rounded-lg object-cover border border-slate-200">
                @else
                    <div class="w-16 h-16 rounded-lg bg-[#000d6b] text-white font-bold flex items-center justify-center text-lg">{{ $pegawai->getInitials() }}</div>
                @endif
                <div class="flex-1">
                    <label class="block text-xs font-medium text-slate-600 mb-1">Foto Profil</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-600 border border-slate-200 rounded-md p-1.5 bg-white">
                    <p class="text-[10px] text-slate-400 mt-0.5">JPG, PNG, WEBP. Maks. 4MB</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap *</label>
                    <input type="text" name="nama" value="{{ old('nama', $pegawai->nama) }}" required
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $pegawai->email) }}" required
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Divisi / Area Kerja</label>
                <input type="text" name="area_kerja" value="{{ old('area_kerja', $pegawai->area_kerja) }}"
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                       placeholder="Contoh: Satpam / IT / Operasional">
            </div>
        </div>

        {{-- Data Diri Lengkap --}}
        <div class="bg-white border border-slate-200 rounded-lg p-5 space-y-4 mt-4">
            <h3 class="text-xs font-bold text-slate-600 uppercase tracking-wide border-b border-slate-100 pb-2">Data Diri Lengkap</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">NIK (No. KTP)</label>
                    <input type="text" name="nik" value="{{ old('nik', $pegawai->nik) }}" maxlength="20"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                           placeholder="3201234567890001">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $pegawai->no_hp) }}"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                           placeholder="08123456789">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                           placeholder="Jakarta">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->format('Y-m-d') : '') }}"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition">
                        <option value="Laki-laki" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin', $pegawai->jenis_kelamin) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Status Pernikahan</label>
                    <select name="status_pernikahan" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition">
                        <option value="Belum Menikah" {{ old('status_pernikahan', $pegawai->status_pernikahan) == 'Belum Menikah' ? 'selected' : '' }}>Belum Menikah</option>
                        <option value="Menikah" {{ old('status_pernikahan', $pegawai->status_pernikahan) == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                        <option value="Duda/Janda" {{ old('status_pernikahan', $pegawai->status_pernikahan) == 'Duda/Janda' ? 'selected' : '' }}>Duda/Janda</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Pendidikan Terakhir</label>
                    <select name="pendidikan_terakhir" class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition">
                        <option value="SD" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'SD' ? 'selected' : '' }}>SD</option>
                        <option value="SMP" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'SMP' ? 'selected' : '' }}>SMP</option>
                        <option value="SMA/SMK" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'SMA/SMK' ? 'selected' : '' }}>SMA/SMK</option>
                        <option value="D3" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'D3' ? 'selected' : '' }}>D3</option>
                        <option value="S1" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'S1' ? 'selected' : '' }}>S1</option>
                        <option value="S2" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'S2' ? 'selected' : '' }}>S2</option>
                        <option value="S3" {{ old('pendidikan_terakhir', $pegawai->pendidikan_terakhir) == 'S3' ? 'selected' : '' }}>S3</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Alamat Lengkap</label>
                <textarea name="alamat" rows="2"
                          class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition resize-none"
                          placeholder="Jl. Contoh No. 1 RT/RW, Kelurahan, Kecamatan, Kota">{{ old('alamat', $pegawai->alamat) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Kontak Darurat (Nama & No. HP)</label>
                <input type="text" name="kontak_darurat" value="{{ old('kontak_darurat', $pegawai->kontak_darurat) }}"
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                       placeholder="Contoh: Budi (Ayah) - 081234567890">
            </div>

            {{-- Informasi Rekening Bank (Payroll) --}}
            <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-lg space-y-3">
                <div class="flex items-center gap-2 border-b border-slate-200 pb-1.5">
                    <span class="text-sm">💳</span>
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Rekening Bank (Payroll / Gaji)</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nama Bank</label>
                        <input type="text" name="nama_bank" value="{{ old('nama_bank', $pegawai->nama_bank) }}"
                               class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                               placeholder="e.g. BCA, Mandiri, BRI, BNI">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Nomor Rekening</label>
                        <input type="text" name="nomor_rekening" value="{{ old('nomor_rekening', $pegawai->nomor_rekening) }}"
                               class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition font-mono"
                               placeholder="e.g. 5210987654">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Atas Nama Rekening</label>
                        <input type="text" name="nama_rekening" value="{{ old('nama_rekening', $pegawai->nama_rekening) }}"
                               class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                               placeholder="Nama di buku tabungan">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex justify-end">
            <button type="submit" class="px-5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-semibold rounded-md transition">
                Simpan Data Diri
            </button>
        </div>
    </form>

</div>
@endsection
