@extends('layouts.app')

@section('title', 'Leave Request (Pengajuan Cuti) - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Leave Requests</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Form Pengajuan Cuti</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Ajukan permohonan cuti tahunan, cuti sakit, atau izin khusus kepada manajemen dan HRD.
            </p>
        </div>
    </div>

    <!-- Official HR Notice Card -->
    <div class="bg-gradient-to-r from-[#eef2ff] to-[#f8fafc] border border-indigo-100 rounded-2xl p-5 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-[#000d6b] text-white flex items-center justify-center text-lg font-bold shrink-0 shadow-sm">
                ℹ️
            </div>
            <div>
                <h3 class="text-xs sm:text-sm font-bold text-slate-900">Informasi & Kebijakan Cuti Karyawan</h3>
                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                    Untuk pengajuan cuti, silakan hubungi <strong>HR Department PT Inti Sarana Wijaya</strong>. Pastikan pengajuan dilakukan minimal 3 hari sebelum tanggal mulai (kecuali kondisi darurat/sakit).
                </p>
            </div>
        </div>
        <div class="text-[11px] font-semibold text-indigo-700 bg-white border border-indigo-200 px-3 py-1.5 rounded-lg shrink-0">
            PT Inti Sarana Wijaya HR
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Pengajuan Cuti (Left 1 col) -->
        <div class="lg:col-span-1 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-5">
                <span class="section-bar"></span>
                <h2 class="text-sm font-bold text-slate-900">Formulir Permohonan Cuti</h2>
            </div>

            <form method="POST" action="{{ route('cuti.store') }}" class="space-y-4 text-xs" onsubmit="return validateDates();">
                @csrf

                <div>
                    <label for="tipe_cuti" class="block font-semibold text-slate-700 mb-1">
                        Jenis Cuti / Izin <span class="text-rose-500">*</span>
                    </label>
                    <select name="tipe_cuti" id="tipe_cuti" required
                            class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm bg-white font-medium">
                        <option value="Cuti Tahunan">Cuti Tahunan (Annual Leave)</option>
                        <option value="Cuti Sakit">Cuti Sakit (Medical Leave)</option>
                        <option value="Cuti Melahirkan / Bersalin">Cuti Melahirkan / Bersalin</option>
                        <option value="Cuti Khusus / Izin Penting">Cuti Khusus / Izin Penting (Menikah/Keluarga)</option>
                        <option value="Izin Tidak Masuk">Izin Khusus Lainnya</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tanggal_mulai" class="block font-semibold text-slate-700 mb-1">
                            Tanggal Mulai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" required value="{{ date('Y-m-d') }}"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-medium">
                    </div>
                    <div>
                        <label for="tanggal_selesai" class="block font-semibold text-slate-700 mb-1">
                            Tanggal Selesai <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" required value="{{ date('Y-m-d') }}"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-medium">
                    </div>
                </div>

                <div id="duration-preview" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700 text-xs flex items-center justify-between font-medium">
                    <span>Durasi Cuti:</span>
                    <strong id="duration-text" class="text-[#000d6b] font-bold">1 Hari</strong>
                </div>

                <div>
                    <label for="alasan" class="block font-semibold text-slate-700 mb-1">
                        Alasan / Keterangan Cuti <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="alasan" id="alasan" rows="3" required placeholder="Contoh: Keperluan keluarga mendesak di luar kota / Sakit demam berobat ke dokter..."
                              class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm"></textarea>
                </div>

                <button type="submit"
                        class="w-full py-3 px-4 bg-[#000d6b] hover:bg-[#001253] text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition tracking-wide">
                    Kirim Pengajuan Cuti
                </button>
            </form>
        </div>

        <!-- Tabel Riwayat Pengajuan Cuti (Right 2 cols) -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="section-bar"></span>
                        <h2 class="text-sm font-bold text-slate-900">Riwayat Pengajuan Cuti Anda</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('export.cuti.sendiri') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Download Excel
                        </a>
                        <span class="text-xs text-slate-400 font-medium">{{ $cutis->total() }} Total Pengajuan</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead>
                            <tr class="bg-[#000d6b] text-white">
                                <th class="py-3 px-4 font-bold tracking-wider">Jenis Cuti</th>
                                <th class="py-3 px-4 font-bold tracking-wider">Periode Tanggal</th>
                                <th class="py-3 px-4 font-bold tracking-wider">Durasi</th>
                                <th class="py-3 px-4 font-bold tracking-wider">Status</th>
                                <th class="py-3 px-4 font-bold tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                            @forelse($cutis as $item)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-900">{{ $item->tipe_cuti }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $item->alasan }}</div>
                                        @if($item->catatan_admin)
                                            <div class="text-[10px] text-slate-500 mt-1 italic">
                                                Catatan Admin: {{ $item->catatan_admin }}
                                            </div>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 whitespace-nowrap font-medium">
                                        {{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_selesai->format('d M Y') }}
                                    </td>

                                    <td class="py-3.5 px-4 whitespace-nowrap font-bold text-slate-900">
                                        {{ $item->jumlah_hari }} Hari
                                    </td>

                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold tracking-wider uppercase {{ $item->getStatusBadgeClass() }}">
                                            {{ $item->getStatusLabel() }}
                                        </span>
                                    </td>

                                    <td class="py-3.5 px-4 text-right whitespace-nowrap space-x-1">
                                        <a href="{{ route('cuti.cetak', $item->id) }}" target="_blank"
                                           class="px-2.5 py-1 bg-[#000d6b] hover:bg-[#001253] text-white text-[11px] font-bold rounded-lg transition inline-block">
                                            📄 Surat Cuti (PDF)
                                        </a>

                                        @if($item->isPending())
                                            <form method="POST" action="{{ route('cuti.cancel', $item->id) }}" class="inline"
                                                  onsubmit="return confirm('Batalkan pengajuan cuti ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold rounded-lg transition">
                                                    Batalkan
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400 text-xs">
                                        Belum ada pengajuan cuti. Silakan gunakan formulir di samping untuk mengajukan cuti.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($cutis->hasPages())
                    <div class="px-4 py-3 border-t border-slate-100">
                        {{ $cutis->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function updateDuration() {
        const start = document.getElementById('tanggal_mulai').value;
        const end = document.getElementById('tanggal_selesai').value;
        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            if (endDate >= startDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('duration-text').innerText = diffDays + ' Hari';
            } else {
                document.getElementById('duration-text').innerText = 'Tanggal tidak valid';
            }
        }
    }

    document.getElementById('tanggal_mulai').addEventListener('change', function() {
        document.getElementById('tanggal_selesai').min = this.value;
        if (document.getElementById('tanggal_selesai').value < this.value) {
            document.getElementById('tanggal_selesai').value = this.value;
        }
        updateDuration();
    });

    document.getElementById('tanggal_selesai').addEventListener('change', updateDuration);
</script>
@endsection
