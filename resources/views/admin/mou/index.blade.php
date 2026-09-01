@extends('layouts.app')

@section('title', 'MoU & Kontrak Kerjasama Outsourcing - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#000d6b] transition">Admin</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">MoU & Kontrak Kerjasama</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">MoU & Kontrak Kerjasama Outsourcing</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola & cetak Dokumen Perjanjian MoU Kerjasama Penempatan Tenaga Kerja (Satpam, CS, Driver) dengan Kantor Klien.
            </p>
        </div>
        <button onclick="document.getElementById('modal-mou').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-xs rounded-xl shadow-sm transition shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Buat Dokumen MoU / Kontrak Baru
        </button>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Data Table Container -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">Nomor MoU / Kontrak</th>
                        <th class="py-3.5 px-5 font-bold">Kantor Klien (Pihak Ke-2)</th>
                        <th class="py-3.5 px-5 font-bold">Masa Berlaku</th>
                        <th class="py-3.5 px-5 font-bold">Personil / Layanan</th>
                        <th class="py-3.5 px-5 font-bold text-center">Aksi Dokumen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($mous as $mou)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5">
                                <div class="font-bold text-[#000d6b] text-xs font-mono">{{ $mou->nomor_mou }}</div>
                                <div class="text-[11px] text-slate-400">Dibuat: {{ $mou->tanggal_mou->translatedFormat('d M Y') }}</div>
                            </td>
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900">{{ $mou->nama_kantor }}</div>
                                <div class="text-[11px] text-slate-500 font-medium">PJ: {{ $mou->penanggung_jawab_klien }} ({{ $mou->jabatan_klien }})</div>
                            </td>
                            <td class="py-4 px-5">
                                <div class="font-semibold text-slate-800 text-xs">
                                    {{ $mou->tanggal_mulai->translatedFormat('d M Y') }} &ndash; {{ $mou->tanggal_selesai->translatedFormat('d M Y') }}
                                </div>
                            </td>
                            <td class="py-4 px-5 space-y-1">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-50 text-[#000d6b] border border-indigo-200">
                                    {{ $mou->jumlah_personil }} Personil Outsourcing
                                </span>
                                <div class="text-[11px] text-slate-500 font-medium">{{ $mou->layanan_outsourcing }}</div>
                            </td>
                            <td class="py-4 px-5 text-center space-x-2">
                                <a href="{{ route('admin.mou.cetak', $mou) }}" target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Cetak MoU Kop ISW
                                </a>
                                <form method="POST" action="{{ route('admin.mou.destroy', $mou) }}" class="inline-block" onsubmit="return confirm('Hapus MoU ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 text-xs text-rose-600 hover:bg-rose-50 rounded-lg font-bold transition">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                Belum ada Dokumen MoU / Kontrak Kerjasama tercatat. Klik tombol di atas untuk membuat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mous->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $mous->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Form Buat MoU Baru -->
<div id="modal-mou" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl border border-slate-200 max-w-2xl w-full p-6 shadow-xl space-y-4 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#000d6b]">Buat Dokumen MoU & Kontrak Kerjasama Baru</h2>
            <button onclick="document.getElementById('modal-mou').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.mou.store') }}" class="space-y-4 text-xs">
            @csrf

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Kantor Klien (Master Data)</label>
                <select name="kantor_klien_id" onchange="autoFillKantor(this)" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium bg-white">
                    <option value="">-- Pilih Kantor Klien (Atau Input Manual Below) --</option>
                    @foreach($kantorKliens as $kk)
                        <option value="{{ $kk->id }}" data-nama="{{ $kk->nama_kantor }}" data-pj="{{ $kk->penanggung_jawab }}" data-telp="{{ $kk->telepon }}" data-alamat="{{ $kk->alamat }}">
                            🏢 {{ $kk->nama_kantor }} ({{ $kk->kode_kantor }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Kantor Klien (Pihak Ke-2) <span class="text-rose-500">*</span></label>
                    <input type="text" name="nama_kantor" id="input-nama-kantor" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold" placeholder="e.g. PT Bank Central Asia Tbk">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Penanggung Jawab Klien <span class="text-rose-500">*</span></label>
                    <input type="text" name="penanggung_jawab_klien" id="input-pj-klien" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold" placeholder="e.g. Budi Santoso">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jabatan Penanggung Jawab <span class="text-rose-500">*</span></label>
                    <input type="text" name="jabatan_klien" value="General Manager / Pimpinan Klien" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Telepon Klien</label>
                    <input type="text" name="telepon_klien" id="input-telp-klien" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium" placeholder="021-xxxxxx">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Alamat Kantor Klien</label>
                <textarea name="alamat_klien" id="input-alamat-klien" rows="2" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium" placeholder="Alamat lengkap lokasi site klien..."></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Tangatangan MoU <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_mou" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-800">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai Kontrak <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-800">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai Kontrak <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_selesai" value="{{ date('Y-12-31') }}" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Jumlah Personil <span class="text-rose-500">*</span></label>
                    <input type="number" name="jumlah_personil" value="10" min="1" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-800">
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Layanan Outsourcing <span class="text-rose-500">*</span></label>
                    <input type="text" name="layanan_outsourcing" value="Satpam / Security, Cleaning Service, Driver" required class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none font-medium text-slate-800">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Ketentuan Tambahan / Pasal Kerjasama</label>
                <textarea name="catatan_pasal" rows="3" class="w-full px-3.5 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium" placeholder="Catatan syarat & ketentuan khusus kerjasama..."></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-mou').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200">Batal</button>
                <button type="submit" class="px-6 py-2 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-xl shadow-sm">Buat & Generate Dokumen MoU</button>
            </div>
        </form>
    </div>
</div>

<script>
    function autoFillKantor(selectEl) {
        const opt = selectEl.options[selectEl.selectedIndex];
        if (opt && opt.value) {
            document.getElementById('input-nama-kantor').value = opt.getAttribute('data-nama') || '';
            document.getElementById('input-pj-klien').value = opt.getAttribute('data-pj') || '';
            document.getElementById('input-telp-klien').value = opt.getAttribute('data-telp') || '';
            document.getElementById('input-alamat-klien').value = opt.getAttribute('data-alamat') || '';
        }
    }
</script>
@endsection
