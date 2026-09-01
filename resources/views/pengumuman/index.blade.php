@extends('layouts.app')

@section('title', 'Pengumuman, SOP & Rules - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Informasi & SOP</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Pengumuman, SOP & Ketentuan Perusahaan</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Ketentuan bertugas, Standar Operasional Prosedur (SOP) PT Inti Sarana Wijaya, dan aturan pasal-pasal sanksi pelanggaran (SP 1 - SP 3).
            </p>
        </div>

        @if($pegawai->hasAdminAccess())
            <div>
                <button type="button" onclick="openTambahInfoModal()"
                        class="px-4 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-sm transition">
                    + Terbitkan Pengumuman / SOP Baru
                </button>
            </div>
        @endif
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

    <!-- Personal SP Warning Banner if employee has active SP -->
    @if(isset($mySps) && $mySps->count() > 0)
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-5 shadow-sm space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-rose-600 animate-ping"></span>
                    <h2 class="text-base font-black text-rose-900">PERATURAN & SURAT PERINGATAN (SP) AKTIF ANDA</h2>
                </div>
                <span class="text-xs font-extrabold text-rose-700 bg-rose-100 px-3 py-1 rounded-full border border-rose-300">
                    {{ $mySps->count() }} Sanksi Diterbitkan
                </span>
            </div>

            <div class="space-y-2 text-xs">
                @foreach($mySps as $sp)
                    <div class="p-3.5 bg-white rounded-xl border border-rose-200 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold {{ $sp->getSpBadgeColor() }}">
                                    {{ $sp->tingkat_sp }}
                                </span>
                                <span class="font-bold text-slate-900">{{ $sp->pasal_pelanggaran }}</span>
                            </div>
                            <p class="text-slate-600 text-[11px]">"{{ $sp->deskripsi }}"</p>
                        </div>
                        <div class="text-right text-[11px] text-slate-400 shrink-0">
                            <div>Diterbitkan: <strong>{{ $sp->tanggal_sp->format('d/m/Y') }}</strong></div>
                            <div>Berlaku s/d: <strong>{{ $sp->berlaku_sampai ? $sp->berlaku_sampai->format('d/m/Y') : 'Permanen' }}</strong></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Section 1: BroadCast Pengumuman Perusahaan -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <span class="section-bar"></span>
            <h2 class="text-base font-extrabold text-slate-900">Pengumuman Internal Perusahaan</h2>
        </div>

        <div class="space-y-3">
            @forelse($pengumumans as $p)
                <div class="p-4 bg-[#eef2ff]/50 rounded-xl border border-indigo-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded text-[10px] font-extrabold {{ $p->getKategoriBadgeColor() }}">
                                PENGUMUMAN
                            </span>
                            @if($p->prioritas === 'darurat')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">DARURAT</span>
                            @elseif($p->prioritas === 'penting')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white">PENTING</span>
                            @endif
                            <h3 class="font-extrabold text-slate-900 text-sm">{{ $p->judul }}</h3>
                        </div>

                        <span class="text-[11px] text-slate-400 font-medium">{{ $p->created_at->translatedFormat('d F Y, H:i') }} WIB</span>
                    </div>

                    <p class="text-slate-700 leading-relaxed text-xs whitespace-pre-line">{{ $p->isi }}</p>

                    @if($pegawai->hasAdminAccess())
                        <div class="pt-2 text-right space-x-3">
                            <button type="button" onclick="openEditInfoModal({{ json_encode($p) }})" class="text-[#000d6b] hover:underline font-bold text-[11px]">Edit</button>
                            <form method="POST" action="{{ route('pengumuman.destroy', $p->id) }}" onsubmit="return confirm('Hapus pengumuman ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:underline font-bold text-[11px]">Hapus</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-slate-400 text-xs text-center py-4">Belum ada pengumuman internal terbaru.</p>
            @endforelse
        </div>
    </div>

    <!-- Section 2: Ketentuan Bertugas & SOP ISW -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <span class="section-bar"></span>
            <h2 class="text-base font-extrabold text-slate-900">Standar Operasional Prosedur (SOP) PT Inti Sarana Wijaya</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <!-- Template default SOP jika belum ada custom input -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <div class="font-bold text-[#000d6b] text-sm">1. Ketentuan Jam Absensi & Presensi Digital</div>
                <ul class="text-slate-600 space-y-1 text-[11px] leading-relaxed list-disc list-inside">
                    <li>Setiap pegawai wajib melakukan Absen Masuk & Pulang secara digital sesuai waktu shift.</li>
                    <li>Batas toleransi keterlambatan maksimal sesuai divisi (default 15 menit).</li>
                    <li>Pegawai Satpam & Cleaning Service wajib mengikuti jadwal shift yang ditentukan Danru.</li>
                </ul>
            </div>

            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <div class="font-bold text-[#000d6b] text-sm">2. SOP Tugas Periodik Cleaning Service</div>
                <ul class="text-slate-600 space-y-1 text-[11px] leading-relaxed list-disc list-inside">
                    <li>Wajib mengunggah bukti foto pelaksanaan tugas harian dan mingguan.</li>
                    <li>Foto bukti diunggah langsung melalui sistem dengan stamp waktu otomatis.</li>
                    <li>Supervisor/Danru akan memberikan nilai & rating performa atas hasil pekerjaan.</li>
                </ul>
            </div>

            @foreach($sops as $sop)
                <div class="p-4 bg-indigo-50/40 rounded-xl border border-indigo-100 space-y-2 md:col-span-2">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-[#000d6b] text-sm">{{ $sop->judul }}</h3>
                        @if($pegawai->hasAdminAccess())
                            <div class="space-x-3">
                                <button type="button" onclick="openEditInfoModal({{ json_encode($sop) }})" class="text-[#000d6b] hover:underline font-bold text-[11px]">Edit</button>
                                <form method="POST" action="{{ route('pengumuman.destroy', $sop->id) }}" onsubmit="return confirm('Hapus SOP ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline font-bold text-[11px]">Hapus</button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <p class="text-slate-700 text-xs whitespace-pre-line leading-relaxed">{{ $sop->isi }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Section 3: Pasal-Pasal Pelanggaran & Ketentuan SP 1 - SP 3 -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
            <span class="section-bar"></span>
            <h2 class="text-base font-extrabold text-slate-900">Ketentuan Pasal Pelanggaran & Tahapan Sanksi SP</h2>
        </div>

        <div class="space-y-3 text-xs">
            <div class="p-4 bg-amber-50/70 border border-amber-200 rounded-xl space-y-2">
                <div class="font-extrabold text-amber-900 text-sm">Ketentuan Tahapan Surat Peringatan (SP)</div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2 text-[11px] text-amber-900 mt-2">
                    <div class="p-2.5 bg-white rounded-lg border border-amber-200">
                        <strong class="block text-amber-800">1. Teguran Lisan</strong>
                        Keterlambatan ringan / alpa 1 hari tanpa keterangan.
                    </div>
                    <div class="p-2.5 bg-white rounded-lg border border-amber-200">
                        <strong class="block text-amber-900">2. SP 1 (Pertama)</strong>
                        Keterlambatan berulang >3x sebulan / tidak mengerjakan tugas periodik.
                    </div>
                    <div class="p-2.5 bg-white rounded-lg border border-orange-200">
                        <strong class="block text-orange-800">3. SP 2 (Kedua)</strong>
                        Tidak mengindahkan SP 1 / alpa berturut-turut >3 hari.
                    </div>
                    <div class="p-2.5 bg-white rounded-lg border border-rose-200">
                        <strong class="block text-rose-800">4. SP 3 (Terakhir)</strong>
                        Pelanggaran berat / kelalaian fatal. Ancaman pemutusan hubungan kerja (PHK).
                    </div>
                </div>
            </div>

            @foreach($pasals as $pasal)
                <div class="p-4 bg-rose-50/40 rounded-xl border border-rose-200 space-y-1">
                    <div class="flex items-center justify-between">
                        <h3 class="font-extrabold text-rose-900 text-sm">{{ $pasal->judul }}</h3>
                        @if($pegawai->hasAdminAccess())
                            <div class="space-x-3">
                                <button type="button" onclick="openEditInfoModal({{ json_encode($pasal) }})" class="text-[#000d6b] hover:underline font-bold text-[11px]">Edit</button>
                                <form method="POST" action="{{ route('pengumuman.destroy', $pasal->id) }}" onsubmit="return confirm('Hapus pasal ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline font-bold text-[11px]">Hapus</button>
                                </form>
                            </div>
                        @endif
                    </div>
                    <p class="text-slate-700 text-xs whitespace-pre-line leading-relaxed">{{ $pasal->isi }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Modal Terbitkan Pengumuman / SOP Baru (Admin Only) -->
@if($pegawai->hasAdminAccess())
<div id="tambah-info-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Terbitkan Pengumuman / SOP / Pasal Baru</h3>
            <button type="button" onclick="closeTambahInfoModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('pengumuman.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="modal_kategori" class="block font-semibold text-slate-700 mb-1">Kategori Konten <span class="text-rose-500">*</span></label>
                <select name="kategori" id="modal_kategori" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="pengumuman">Pengumuman Perusahaan</option>
                    <option value="sop">Standar Operasional Prosedur (SOP)</option>
                    <option value="pasal_pelanggaran">Pasal Pelanggaran & Sanksi</option>
                </select>
            </div>

            <div>
                <label for="modal_judul" class="block font-semibold text-slate-700 mb-1">Judul / Nama Informasi <span class="text-rose-500">*</span></label>
                <input type="text" name="judul" id="modal_judul" required placeholder="Contoh: SOP Presensi Shift / Pasal 4 Pelanggaran Jam Kerja..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div>
                <label for="modal_prioritas" class="block font-semibold text-slate-700 mb-1">Tingkat Prioritas</label>
                <select name="prioritas" id="modal_prioritas" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="biasa">Biasa / Informasi Umum</option>
                    <option value="penting">Penting (Wajib Dibaca)</option>
                    <option value="darurat">Darurat (Perhatian Khusus)</option>
                </select>
            </div>

            <div>
                <label for="modal_isi" class="block font-semibold text-slate-700 mb-1">Isi Detail Informasi <span class="text-rose-500">*</span></label>
                <textarea name="isi" id="modal_isi" rows="5" required placeholder="Tuliskan isi pengumuman atau ketentuan poin-poin SOP secara jelas..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeTambahInfoModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Terbitkan Sekarang</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Pengumuman / SOP (Admin Only) -->
<div id="edit-info-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Edit Pengumuman / SOP / Pasal Pelanggaran</h3>
            <button type="button" onclick="closeEditInfoModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="edit-info-form" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_kategori" class="block font-semibold text-slate-700 mb-1">Kategori Konten <span class="text-rose-500">*</span></label>
                <select name="kategori" id="edit_kategori" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="pengumuman">Pengumuman Perusahaan</option>
                    <option value="sop">Standar Operasional Prosedur (SOP)</option>
                    <option value="pasal_pelanggaran">Pasal Pelanggaran & Sanksi</option>
                </select>
            </div>

            <div>
                <label for="edit_judul" class="block font-semibold text-slate-700 mb-1">Judul / Nama Informasi <span class="text-rose-500">*</span></label>
                <input type="text" name="judul" id="edit_judul" required placeholder="Judul Pengumuman / SOP..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div>
                <label for="edit_prioritas" class="block font-semibold text-slate-700 mb-1">Tingkat Prioritas</label>
                <select name="prioritas" id="edit_prioritas" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="biasa">Biasa / Informasi Umum</option>
                    <option value="penting">Penting (Wajib Dibaca)</option>
                    <option value="darurat">Darurat (Perhatian Khusus)</option>
                </select>
            </div>

            <div>
                <label for="edit_isi" class="block font-semibold text-slate-700 mb-1">Isi Detail Informasi <span class="text-rose-500">*</span></label>
                <textarea name="isi" id="edit_isi" rows="5" required placeholder="Detail pengumuman / SOP..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeEditInfoModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTambahInfoModal() {
        document.getElementById('tambah-info-modal').classList.remove('hidden');
    }
    function closeTambahInfoModal() {
        document.getElementById('tambah-info-modal').classList.add('hidden');
    }

    function openEditInfoModal(data) {
        document.getElementById('edit_kategori').value = data.kategori;
        document.getElementById('edit_judul').value = data.judul;
        document.getElementById('edit_prioritas').value = data.prioritas || 'biasa';
        document.getElementById('edit_isi').value = data.isi;
        document.getElementById('edit-info-form').action = '/pengumuman/' + data.id;
        document.getElementById('edit-info-modal').classList.remove('hidden');
    }
    function closeEditInfoModal() {
        document.getElementById('edit-info-modal').classList.add('hidden');
    }
</script>
@endif
@endsection
