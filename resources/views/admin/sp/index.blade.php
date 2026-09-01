@extends('layouts.app')

@section('title', 'Kelola Surat Peringatan (SP 1 - SP 3) - Admin PT ISW')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Admin</span>
                <span>/</span>
                <span class="text-slate-800 font-bold">Kelola SP & Pelanggaran</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Input Pelanggaran & Surat Peringatan (SP 1 - SP 3)</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Penerbitan sanksi kedisiplinan pegawai dari Teguran Lisan, SP 1, SP 2, hingga SP 3 sesuai ketentuan pasal pelanggaran perusahaan.
            </p>
        </div>

        <div>
            <button type="button" onclick="openInputSpModal()"
                    class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                + Terbitkan SP / Sanksi Baru
            </button>
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

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.sp.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-5">
                <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Filter Pegawai</label>
                <select name="pegawai_id" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 bg-white font-medium">
                    <option value="">Semua Pegawai</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}" {{ request('pegawai_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} ({{ $p->area_kerja ?: 'Staff' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-4">
                <label class="block font-bold text-slate-500 uppercase tracking-wider mb-1">Tingkat SP / Sanksi</label>
                <select name="tingkat_sp" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 bg-white font-medium">
                    <option value="">Semua Tingkat Sanksi</option>
                    <option value="Teguran Lisan" {{ request('tingkat_sp') == 'Teguran Lisan' ? 'selected' : '' }}>💬 Teguran Lisan</option>
                    <option value="SP 1" {{ request('tingkat_sp') == 'SP 1' ? 'selected' : '' }}>⚠️ SP 1 (Pertama)</option>
                    <option value="SP 2" {{ request('tingkat_sp') == 'SP 2' ? 'selected' : '' }}>🚨 SP 2 (Kedua)</option>
                    <option value="SP 3" {{ request('tingkat_sp') == 'SP 3' ? 'selected' : '' }}>🛑 SP 3 (Terakhir)</option>
                </select>
            </div>

            <div class="sm:col-span-3 flex items-end gap-2">
                <button type="submit" class="w-full py-2.5 px-3 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg transition">
                    Filter Data
                </button>
                @if(request('pegawai_id') || request('tingkat_sp'))
                    <a href="{{ route('admin.sp.index') }}" class="py-2.5 px-3 bg-slate-100 text-slate-600 font-bold rounded-lg hover:bg-slate-200">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Surat Peringatan List -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="section-bar"></span>
                <h2 class="text-base font-extrabold text-slate-900">Daftar Sanksi & Surat Peringatan Pegawai</h2>
            </div>
            <span class="text-xs text-slate-500 font-medium">{{ $sps->total() }} Catatan Diterbitkan</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold">Tingkat SP</th>
                        <th class="py-3.5 px-5 font-bold">Pasal & Alasan Pelanggaran</th>
                        <th class="py-3.5 px-5 font-bold">Tanggal Penerbitan</th>
                        <th class="py-3.5 px-5 font-bold">Berlaku Sampai</th>
                        <th class="py-3.5 px-5 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($sps as $sp)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900 text-sm">{{ $sp->pegawai->nama }}</div>
                                <div class="text-[11px] text-slate-400">{{ $sp->pegawai->area_kerja ?: ($sp->pegawai->divisi?->nama ?? 'Staff') }}</div>
                            </td>

                            <td class="py-4 px-5">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-extrabold shadow-xs {{ $sp->getSpBadgeColor() }}">
                                    {{ $sp->tingkat_sp }}
                                </span>
                            </td>

                            <td class="py-4 px-5 space-y-1">
                                <div class="font-bold text-slate-900">{{ $sp->pasal_pelanggaran }}</div>
                                <p class="text-slate-500 text-xs leading-relaxed">{{ $sp->deskripsi }}</p>
                            </td>

                            <td class="py-4 px-5 whitespace-nowrap font-medium text-slate-800">
                                {{ $sp->tanggal_sp->translatedFormat('d F Y') }}
                            </td>

                            <td class="py-4 px-5 whitespace-nowrap font-medium text-slate-800">
                                {{ $sp->berlaku_sampai ? $sp->berlaku_sampai->translatedFormat('d F Y') : '–' }}
                            </td>

                            <td class="py-4 px-5 text-right whitespace-nowrap space-x-2">
                                <a href="{{ route('admin.sp.cetak', $sp->id) }}" target="_blank"
                                   class="inline-block px-3 py-1.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-xs transition">
                                    🖨️ Cetak Surat SP
                                </a>

                                <form method="POST" action="{{ route('admin.sp.destroy', $sp->id) }}" onsubmit="return confirm('Hapus catatan Surat Peringatan ini?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400 text-xs">
                                Belum ada catatan Surat Peringatan (SP) yang diterbitkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sps->hasPages())
            <div class="px-5 py-4 border-t border-slate-100">
                {{ $sps->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Input Surat Peringatan (SP) Baru -->
<div id="sp-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Terbitkan Surat Peringatan (SP / Sanksi)</h3>
            <button type="button" onclick="closeInputSpModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.sp.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="modal_sp_pegawai_id" class="block font-semibold text-slate-700 mb-1">Pilih Pegawai Yang Melanggar <span class="text-rose-500">*</span></label>
                <select name="pegawai_id" id="modal_sp_pegawai_id" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="">-- Pilih Pegawai --</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->area_kerja ?: 'Staff' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="modal_tingkat_sp" class="block font-semibold text-slate-700 mb-1">Tingkat SP / Sanksi <span class="text-rose-500">*</span></label>
                    <select name="tingkat_sp" id="modal_tingkat_sp" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                        <option value="Teguran Lisan">💬 Teguran Lisan</option>
                        <option value="SP 1">⚠️ SP 1 (Surat Peringatan I)</option>
                        <option value="SP 2">🚨 SP 2 (Surat Peringatan II)</option>
                        <option value="SP 3">🛑 SP 3 (Surat Peringatan Terakhir)</option>
                    </select>
                </div>

                <div>
                    <label for="modal_tanggal_sp" class="block font-semibold text-slate-700 mb-1">Tanggal Penerbitan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_sp" id="modal_tanggal_sp" value="{{ date('Y-m-d') }}" required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold">
                </div>
            </div>

            <div>
                <label for="modal_pasal_pelanggaran" class="block font-semibold text-slate-700 mb-1">Pasal / Kategori Pelanggaran <span class="text-rose-500">*</span></label>
                <input type="text" name="pasal_pelanggaran" id="modal_pasal_pelanggaran" required placeholder="Contoh: Pasal 4 Ayat 2 - Indisipliner Keterlambatan Berulang..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div>
                <label for="modal_deskripsi_sp" class="block font-semibold text-slate-700 mb-1">Deskripsi Kronologi & Detail Pelanggaran <span class="text-rose-500">*</span></label>
                <textarea name="deskripsi" id="modal_deskripsi_sp" rows="4" required placeholder="Jelaskan secara detail bentuk pelanggaran yang dilakukan oleh pegawai..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800"></textarea>
            </div>

            <div>
                <label for="modal_berlaku_sampai" class="block font-semibold text-slate-700 mb-1">Masa Berlaku SP S/D (Opsional, Default 6 Bulan)</label>
                <input type="date" name="berlaku_sampai" id="modal_berlaku_sampai" value="{{ \Carbon\Carbon::now()->addMonths(6)->toDateString() }}"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeInputSpModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg shadow-sm">Terbitkan SP Sekarang</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openInputSpModal() {
        document.getElementById('sp-modal').classList.remove('hidden');
    }
    function closeInputSpModal() {
        document.getElementById('sp-modal').classList.add('hidden');
    }
</script>
@endsection
