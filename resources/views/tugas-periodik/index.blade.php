@extends('layouts.app')

@section('title', 'Tugas Periodik Cleaning Service - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Tugas Periodik</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Dokumentasi Tugas Periodik & Patroli</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Upload foto bukti pengerjaan tugas harian/mingguan & patroli di kantor klien. Waktu upload dicatat otomatis secara real-time.
            </p>
        </div>

        <div>
            <span class="px-3.5 py-2 bg-[#eef2ff] text-[#000d6b] text-xs font-extrabold rounded-xl border border-indigo-200 shadow-sm inline-block">
                Site: {{ $pegawai->area_kerja ?: ($pegawai->divisi?->nama ?? 'Kantor Klien ISW') }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-semibold">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Upload Tugas Card -->
        <div class="lg:col-span-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm space-y-4">
                <div class="flex items-center gap-2">
                    <span class="section-bar"></span>
                    <h2 class="text-sm font-bold text-slate-900">Upload Foto Bukti Tugas</h2>
                </div>

                <form method="POST" action="{{ route('tugas-periodik.store') }}" enctype="multipart/form-data" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label for="tipe_tugas" class="block font-semibold text-slate-700 mb-1">Pilih Periode Tugas <span class="text-rose-500">*</span></label>
                        <select name="tipe_tugas" id="tipe_tugas" onchange="updateTaskTemplates(this.value)" required
                                class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 font-bold bg-white">
                            <option value="harian">☀️ Tugas Periodik Harian</option>
                            <option value="mingguan">📅 Tugas Periodik Mingguan</option>
                        </select>
                    </div>

                    <div>
                        <label for="nama_tugas" class="block font-semibold text-slate-700 mb-1">Nama / Jenis Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_tugas" id="nama_tugas" list="template_list" required placeholder="Contoh: Pembersihan Lobi Utama..."
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 font-medium">
                        <datalist id="template_list">
                            @foreach($templateHarian as $t)
                                <option value="{{ $t }}">
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label for="tanggal" class="block font-semibold text-slate-700 mb-1">Tanggal Tugas <span class="text-rose-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ date('Y-m-d') }}" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 font-bold">
                    </div>

                    <div>
                        <label for="foto" class="block font-semibold text-slate-700 mb-1">Foto Bukti Hasil Pekerjaan <span class="text-rose-500">*</span></label>
                        <input type="file" name="foto" id="foto" accept="image/*" capture="environment" required onchange="previewUploadImage(event)"
                               class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-700 bg-white">
                        <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Bisa langsung ambil foto dari kamera smartphone)</p>
                    </div>

                    <!-- Image Preview -->
                    <div id="image_preview_container" class="hidden">
                        <span class="block text-[11px] font-semibold text-slate-500 mb-1">Preview Foto:</span>
                        <img id="image_preview" src="" alt="Preview" class="w-full h-44 object-cover rounded-xl border border-slate-200 shadow-sm">
                    </div>

                    <!-- Auto Timestamp Indicator -->
                    <div class="bg-[#eef2ff] border border-indigo-100 rounded-xl p-3.5 text-center">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Stamp Waktu Upload Otomatis</div>
                        <div id="live_timestamp" class="text-sm font-extrabold text-[#000d6b] mt-0.5">
                            {{ now()->translatedFormat('H:i:s \W\I\B — d F Y') }}
                        </div>
                    </div>

                    <div>
                        <label for="catatan" class="block font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="catatan" id="catatan" rows="2" placeholder="Contoh: Kondisi area sudah bersih & kinclong..."
                                  class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800"></textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-sm transition tracking-wide">
                        📷 Upload Foto & Simpan Tugas
                    </button>
                </form>
            </div>
        </div>

        <!-- Task Upload History List -->
        <div class="lg:col-span-7 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="section-bar"></span>
                    <h2 class="text-sm font-bold text-slate-900">Riwayat Tugas Periodik Bulan {{ $carbonMonth->translatedFormat('F Y') }}</h2>
                </div>
                <form method="GET" action="{{ route('tugas-periodik.index') }}">
                    <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()"
                           class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-bold text-slate-800 bg-white">
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($tugasList as $tugas)
                    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition">
                        <div>
                            <!-- Photo -->
                            <div class="relative h-44 bg-slate-100 overflow-hidden">
                                @if($tugas->foto_path && Storage::disk('public')->exists($tugas->foto_path))
                                    <img src="{{ Storage::disk('public')->url($tugas->foto_path) }}" alt="{{ $tugas->nama_tugas }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400 text-xs">Foto Tidak Ditemukan</div>
                                @endif

                                <!-- Tipe Badge -->
                                <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-extrabold shadow-sm {{ $tugas->tipe_tugas === 'harian' ? 'bg-amber-100 text-amber-900 border border-amber-300' : 'bg-indigo-100 text-indigo-900 border border-indigo-300' }}">
                                    {{ strtoupper($tugas->tipe_tugas) }}
                                </span>
                            </div>

                            <div class="p-4 space-y-2 text-xs">
                                <h3 class="font-extrabold text-slate-900 text-sm leading-snug">{{ $tugas->nama_tugas }}</h3>

                                <div class="text-[11px] font-semibold text-[#000d6b] bg-[#eef2ff] px-2.5 py-1 rounded-lg border border-indigo-100">
                                    ⏱️ Upload: {{ $tugas->waktu_upload->translatedFormat('d M Y, H:i:s') }} WIB
                                </div>

                                @if($tugas->catatan)
                                    <p class="text-slate-500 text-[11px] leading-relaxed italic">"{{ $tugas->catatan }}"</p>
                                @endif
                            </div>
                        </div>

                        <div class="p-4 pt-0 border-t border-slate-100 flex items-center justify-between text-xs mt-2">
                            <span class="text-slate-400 font-medium text-[11px]">{{ $tugas->tanggal->translatedFormat('d F Y') }}</span>

                            <form method="POST" action="{{ route('tugas-periodik.destroy', $tugas->id) }}" onsubmit="return confirm('Hapus dokumentasi tugas ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-[11px]">Hapus</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="sm:col-span-2 bg-white rounded-2xl border border-slate-200 p-10 text-center text-slate-400 text-xs">
                        📷 Belum ada foto dokumentasi tugas periodik yang diunggah bulan ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<script>
    function updateTaskTemplates(tipe) {
        const datalist = document.getElementById('template_list');
        const harian = @json($templateHarian);
        const mingguan = @json($templateMingguan);
        const list = tipe === 'harian' ? harian : mingguan;

        datalist.innerHTML = '';
        list.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            datalist.appendChild(opt);
        });
    }

    function previewUploadImage(event) {
        const input = event.target;
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('image_preview').src = e.target.result;
                document.getElementById('image_preview_container').classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Live clock for timestamp indicator
    setInterval(() => {
        const now = new Date();
        const str = now.toLocaleTimeString('id-ID') + ' WIB — ' + now.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'});
        const el = document.getElementById('live_timestamp');
        if (el) el.textContent = str;
    }, 1000);
</script>
@endsection
