@extends('layouts.app')

@section('title', 'Division Working Hours & Shifts - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header & Action Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <span class="text-slate-500">Admin</span>
                <span>/</span>
                <span class="text-slate-800 font-bold">Division Schedules</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Shift & Jadwal Kerja Divisi</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Atur jadwal jam kerja, toleransi keterlambatan, dan ketentuan hari kerja (5 hari kantor, 6 hari operasional, atau shift 7 hari Satpam/CS).
            </p>
        </div>

        <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs sm:text-sm font-bold rounded-lg shadow-sm transition tracking-wide">
            <span>+</span> Tambah Divisi / Shift Baru
        </button>
    </div>

    <!-- Corporate Styled Division Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <!-- Navy Solid Table Header -->
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider">Nama Divisi / Shift</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Jam Masuk - Pulang</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Tipe Hari Kerja</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Toleransi</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($divisis as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Divisi Name -->
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900 text-xs sm:text-sm">{{ $item->nama }}</div>
                                <div class="text-[11px] text-slate-400 font-normal">{{ $item->keterangan ?: 'Tidak ada keterangan khusus' }}</div>
                            </td>

                            <!-- Jam Kerja -->
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900">
                                    <span class="text-emerald-700">{{ substr($item->jam_masuk, 0, 5) }}</span>
                                    <span class="text-slate-400"> - </span>
                                    <span class="text-[#000d6b]">{{ substr($item->jam_pulang, 0, 5) }}</span>
                                    <span class="text-[11px] text-slate-400 font-normal">WIB</span>
                                </div>
                            </td>

                            <!-- Tipe Hari Kerja -->
                            <td class="py-4 px-5">
                                @if($item->hari_kerja_tipe === '7_hari')
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold bg-[#eef2ff] text-[#000d6b] border border-indigo-100">
                                        Shift 7 Hari (Termasuk Libur/Weekend)
                                    </span>
                                @elseif($item->hari_kerja_tipe === '6_hari')
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        Senin - Sabtu (6 Hari Kerja)
                                    </span>
                                @else
                                    <span class="inline-block px-2.5 py-1 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        Senin - Jumat (5 Hari Kantor)
                                    </span>
                                @endif
                            </td>

                            <!-- Toleransi -->
                            <td class="py-4 px-5 font-medium">
                                @if($item->toleransi_menit > 0)
                                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        +{{ $item->toleransi_menit }} Menit
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">0 Menit</span>
                                @endif
                            </td>

                            <!-- Total Pegawai -->
                            <td class="py-4 px-5 font-semibold text-slate-800">
                                <span class="inline-block px-2.5 py-1 rounded text-[10px] font-extrabold bg-slate-100 text-slate-800">
                                    {{ $item->pegawais_count }} Pegawai
                                </span>
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-5 text-right space-x-2 whitespace-nowrap">
                                <button type="button"
                                        onclick="openEditModal({{ json_encode($item) }})"
                                        class="px-3 py-1.5 text-xs font-bold text-slate-700 hover:text-[#000d6b] bg-slate-100 hover:bg-slate-200 rounded-md transition inline-block">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('admin.divisi.destroy', $item->id) }}" class="inline" onsubmit="return confirm('Hapus divisi {{ $item->nama }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-md transition inline-block">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada data divisi/shift kerja.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Divisi -->
<div id="create-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Tambah Divisi / Shift Kerja Baru</h3>
            <button type="button" onclick="closeCreateModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.divisi.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="nama" class="block font-semibold text-slate-700 mb-1">Nama Divisi / Shift <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" id="nama" required placeholder="Contoh: Satpam / Security / Cleaning Service"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-medium">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="jam_masuk" class="block font-semibold text-slate-700 mb-1">Jam Masuk Target <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_masuk" id="jam_masuk" value="08:00" required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-bold">
                </div>
                <div>
                    <label for="jam_pulang" class="block font-semibold text-slate-700 mb-1">Jam Pulang Target <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_pulang" id="jam_pulang" value="17:00" required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-bold">
                </div>
            </div>

            <div>
                <label for="hari_kerja_tipe" class="block font-semibold text-slate-700 mb-1">Ketentuan Hari Kerja & Libur <span class="text-rose-500">*</span></label>
                <select name="hari_kerja_tipe" id="hari_kerja_tipe" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 font-medium">
                    <option value="5_hari">Senin - Jumat (5 Hari Kerja, Weekend & Libur Nasional = Libur)</option>
                    <option value="6_hari">Senin - Sabtu (6 Hari Kerja, Minggu = Libur)</option>
                    <option value="7_hari">Setiap Hari / Shift 7 Hari (Satpam/CS: Termasuk Sabtu, Minggu, & Libur Nasional)</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Pilih <strong>Shift 7 Hari</strong> untuk Satpam/Security agar absen di hari Sabtu, Minggu, dan Libur Nasional otomatis tercatat sebagai kehadiran aktif.</p>
            </div>

            <div>
                <label for="toleransi_menit" class="block font-semibold text-slate-700 mb-1">Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span></label>
                <input type="number" name="toleransi_menit" id="toleransi_menit" value="15" min="0" max="120" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-medium">
            </div>

            <div>
                <label for="keterangan" class="block font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
                <textarea name="keterangan" id="keterangan" rows="2" placeholder="Catatan shift divisi..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeCreateModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Simpan Divisi</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Divisi -->
<div id="edit-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Edit Jadwal Jam Kerja Divisi</h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="edit-form" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_nama" class="block font-semibold text-slate-700 mb-1">Nama Divisi / Shift <span class="text-rose-500">*</span></label>
                <input type="text" name="nama" id="edit_nama" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-medium">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="edit_jam_masuk" class="block font-semibold text-slate-700 mb-1">Jam Masuk <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_masuk" id="edit_jam_masuk" required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-bold">
                </div>
                <div>
                    <label for="edit_jam_pulang" class="block font-semibold text-slate-700 mb-1">Jam Pulang <span class="text-rose-500">*</span></label>
                    <input type="time" name="jam_pulang" id="edit_jam_pulang" required
                           class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-bold">
                </div>
            </div>

            <div>
                <label for="edit_hari_kerja_tipe" class="block font-semibold text-slate-700 mb-1">Ketentuan Hari Kerja & Libur <span class="text-rose-500">*</span></label>
                <select name="hari_kerja_tipe" id="edit_hari_kerja_tipe" required
                        class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 font-medium">
                    <option value="5_hari">Senin - Jumat (5 Hari Kerja, Weekend & Libur Nasional = Libur)</option>
                    <option value="6_hari">Senin - Sabtu (6 Hari Kerja, Minggu = Libur)</option>
                    <option value="7_hari">Setiap Hari / Shift 7 Hari (Satpam/CS: Termasuk Sabtu, Minggu, & Libur Nasional)</option>
                </select>
            </div>

            <div>
                <label for="edit_toleransi_menit" class="block font-semibold text-slate-700 mb-1">Toleransi Keterlambatan (Menit) <span class="text-rose-500">*</span></label>
                <input type="number" name="toleransi_menit" id="edit_toleransi_menit" min="0" max="120" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm font-medium">
            </div>

            <div>
                <label for="edit_keterangan" class="block font-semibold text-slate-700 mb-1">Keterangan Tambahan</label>
                <textarea name="keterangan" id="edit_keterangan" rows="2"
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs sm:text-sm"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Perbarui Divisi</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('create-modal').classList.remove('hidden');
    }
    function closeCreateModal() {
        document.getElementById('create-modal').classList.add('hidden');
    }

    function openEditModal(divisi) {
        document.getElementById('edit_nama').value = divisi.nama;
        document.getElementById('edit_jam_masuk').value = divisi.jam_masuk.substring(0, 5);
        document.getElementById('edit_jam_pulang').value = divisi.jam_pulang.substring(0, 5);
        document.getElementById('edit_hari_kerja_tipe').value = divisi.hari_kerja_tipe || '5_hari';
        document.getElementById('edit_toleransi_menit').value = divisi.toleransi_menit || 0;
        document.getElementById('edit_keterangan').value = divisi.keterangan || '';
        document.getElementById('edit-form').action = '/admin/divisi/' + divisi.id;
        document.getElementById('edit-modal').classList.remove('hidden');
    }
    function closeEditModal() {
        document.getElementById('edit-modal').classList.add('hidden');
    }
</script>
@endsection
