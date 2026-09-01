@extends('layouts.app')

@section('title', 'Master Data Kantor Klien / Site Project - PT Inti Sarana Wijaya')

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
                <span class="text-slate-800 font-bold">Master Kantor Klien</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Master Data Kantor Klien / Site Placement</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola daftar kantor klien, lokasi project outsourcing, dan Penanggung Jawab Site (Danru / Supervisor).
            </p>
        </div>

        <div>
            <button type="button" onclick="openTambahKantorModal()"
                    class="px-4 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-sm transition">
                + Tambah Kantor Klien / Site Baru
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

    <!-- Data Table Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-base font-bold text-[#000d6b]">Daftar Kantor Klien Outsourcing ({{ $kantors->count() }} Site)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold tracking-wider">Nama Kantor Klien / Site</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Kode Site</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Penanggung Jawab Area (Danru/SPV)</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Kontak & Alamat</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider">Pegawai Bertugas</th>
                        <th class="py-3.5 px-5 font-bold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($kantors as $k)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 font-extrabold text-slate-900">
                                {{ $k->nama_kantor }}
                            </td>
                            <td class="py-4 px-5 font-bold text-indigo-700">
                                {{ $k->kode_kantor ?: '-' }}
                            </td>
                            <td class="py-4 px-5 font-semibold text-slate-800">
                                {{ $k->penanggung_jawab ?: 'Belum ditentukan' }}
                            </td>
                            <td class="py-4 px-5 text-slate-600">
                                <div>{{ $k->telepon ?: '-' }}</div>
                                <div class="text-[11px] text-slate-400 truncate max-w-xs">{{ $k->alamat ?: '-' }}</div>
                            </td>
                            <td class="py-4 px-5">
                                <span class="px-2.5 py-1 rounded-full text-xs font-black bg-indigo-50 text-[#000d6b] border border-indigo-200">
                                    {{ $k->total_pegawai }} Pegawai
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.pegawai.cetak-skk-massal', ['site' => $k->nama_kantor]) }}" target="_blank"
                                   class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-800 border border-indigo-200 font-bold text-xs rounded-lg transition inline-block">
                                    📜 Cetak SKK Massal
                                </a>

                                <a href="{{ route('admin.pegawai.cetak-kontrak-massal', ['site' => $k->nama_kantor]) }}" target="_blank"
                                   class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 font-bold text-xs rounded-lg transition inline-block">
                                    📄 Cetak SPK Massal
                                </a>

                                <button type="button" onclick="openEditKantorModal({{ json_encode($k) }})"
                                        class="px-3 py-1.5 bg-[#eef2ff] hover:bg-indigo-100 text-[#000d6b] font-bold text-xs rounded-lg transition">
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('admin.kantor-klien.destroy', $k->id) }}"
                                      onsubmit="return confirm('Hapus Kantor Klien {{ $k->nama_kantor }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Belum ada Kantor Klien terdaftar. Klik "+ Tambah Kantor Klien / Site Baru" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Kantor Klien -->
<div id="tambah-kantor-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Tambah Kantor Klien / Site Baru</h3>
            <button type="button" onclick="closeTambahKantorModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.kantor-klien.store') }}" class="space-y-4 text-xs">
            @csrf
            <div>
                <label for="nama_kantor" class="block font-semibold text-slate-700 mb-1">Nama Kantor Klien / Site <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kantor" id="nama_kantor" required placeholder="Contoh: Gedung Menara BCA / Pabrik Cikarang..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold">
            </div>

            <div>
                <label for="kode_kantor" class="block font-semibold text-slate-700 mb-1">Kode Site / Singkatan</label>
                <input type="text" name="kode_kantor" id="kode_kantor" placeholder="Contoh: BCA-01 / CKR-A..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div>
                <label for="penanggung_jawab" class="block font-semibold text-slate-700 mb-1">Penanggung Jawab Area (Danru / Supervisor)</label>
                <input type="text" name="penanggung_jawab" id="penanggung_jawab" placeholder="Nama Danru Satpam / Supervisor CS di site..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800">
            </div>

            <div>
                <label for="telepon" class="block font-semibold text-slate-700 mb-1">Nomor Telepon Posko / Site</label>
                <input type="text" name="telepon" id="telepon" placeholder="Nomor telepon posko atau kontak supervisor..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800">
            </div>

            <div>
                <label for="alamat" class="block font-semibold text-slate-700 mb-1">Alamat Lengkap Site Project</label>
                <textarea name="alamat" id="alamat" rows="3" placeholder="Alamat lokasi kantor klien..."
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeTambahKantorModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Simpan Site Baru</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Kantor Klien -->
<div id="edit-kantor-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-sm font-bold text-slate-900">Edit Kantor Klien / Site Project</h3>
            <button type="button" onclick="closeEditKantorModal()" class="text-slate-400 hover:text-slate-600 text-lg">&times;</button>
        </div>

        <form id="edit-kantor-form" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')
            <div>
                <label for="edit_nama_kantor" class="block font-semibold text-slate-700 mb-1">Nama Kantor Klien / Site <span class="text-rose-500">*</span></label>
                <input type="text" name="nama_kantor" id="edit_nama_kantor" required
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold">
            </div>

            <div>
                <label for="edit_kode_kantor" class="block font-semibold text-slate-700 mb-1">Kode Site / Singkatan</label>
                <input type="text" name="kode_kantor" id="edit_kode_kantor"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-medium">
            </div>

            <div>
                <label for="edit_penanggung_jawab" class="block font-semibold text-slate-700 mb-1">Penanggung Jawab Area (Danru / Supervisor)</label>
                <input type="text" name="penanggung_jawab" id="edit_penanggung_jawab"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800">
            </div>

            <div>
                <label for="edit_telepon" class="block font-semibold text-slate-700 mb-1">Nomor Telepon Posko / Site</label>
                <input type="text" name="telepon" id="edit_telepon"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800">
            </div>

            <div>
                <label for="edit_alamat" class="block font-semibold text-slate-700 mb-1">Alamat Lengkap Site Project</label>
                <textarea name="alamat" id="edit_alamat" rows="3"
                          class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800"></textarea>
            </div>

            <div class="pt-3 flex justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeEditKantorModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-lg shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openTambahKantorModal() {
        document.getElementById('tambah-kantor-modal').classList.remove('hidden');
    }
    function closeTambahKantorModal() {
        document.getElementById('tambah-kantor-modal').classList.add('hidden');
    }

    function openEditKantorModal(k) {
        document.getElementById('edit_nama_kantor').value = k.nama_kantor;
        document.getElementById('edit_kode_kantor').value = k.kode_kantor || '';
        document.getElementById('edit_penanggung_jawab').value = k.penanggung_jawab || '';
        document.getElementById('edit_telepon').value = k.telepon || '';
        document.getElementById('edit_alamat').value = k.alamat || '';
        document.getElementById('edit-kantor-form').action = '/admin/kantor-klien/' + k.id;
        document.getElementById('edit-kantor-modal').classList.remove('hidden');
    }
    function closeEditKantorModal() {
        document.getElementById('edit-kantor-modal').classList.add('hidden');
    }
</script>
@endsection
