@extends('layouts.app')

@section('title', 'Slip Gaji & BPJS Payroll - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#000d6b] transition">Admin</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Slip Gaji & BPJS Payroll</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Slip Gaji & Penggajian Payroll</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola Gaji Pokok, Tunjangan, serta Potongan BPJS Kesehatan (1%) & BPJS Ketenagakerjaan (3%) per Kantor Klien / Site Area.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button onclick="document.getElementById('modal-gaji-massal').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition shrink-0">
                ⚙️ Edit Gaji & BPJS Universal (Massal)
            </button>

            <a href="{{ route('admin.slip.export-excel', ['bulan' => $bulan, 'site' => $site]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                📥 Download Excel Universal {{ $site ? '('.$site.')' : '' }}
            </a>

            <a href="{{ route('admin.slip.cetak-massal', ['bulan' => $bulan, 'site' => $site]) }}" target="_blank"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                📄 Cetak Slip Massal {{ $site ? '('.$site.')' : '' }}
            </a>
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

    <!-- Filter Card Box -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.slip.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Filter Bulan -->
            <div class="sm:col-span-4">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Periode Bulan & Tahun</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()"
                       class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 outline-none font-bold text-slate-800 bg-white">
            </div>

            <!-- Filter Site / Kantor Klien -->
            <div class="sm:col-span-8">
                <label class="block text-[11px] font-bold text-slate-500 mb-1">Filter Kantor Klien / Placement Site</label>
                <select name="site" onchange="this.form.submit()"
                        class="w-full px-3.5 py-2 text-xs sm:text-sm rounded-lg border border-slate-300 outline-none text-slate-800 bg-white font-bold">
                    <option value="">🏢 Semua Kantor Klien / Site Placement ({{ $kantorKliens->count() }} Site)</option>
                    @foreach($kantorKliens as $kk)
                        <option value="{{ $kk->nama_kantor }}" {{ $site == $kk->nama_kantor ? 'selected' : '' }}>
                            🏢 {{ $kk->nama_kantor }} {{ $kk->kode_kantor ? '('.$kk->kode_kantor.')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Employee Payroll Data Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h2 class="text-sm font-bold text-[#000d6b]">
                Daftar Payroll Pegawai Periode {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }} ({{ count($pegawais) }} Pegawai)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-4 font-bold">Pegawai & Site</th>
                        <th class="py-3.5 px-4 font-bold">Gaji Pokok & Tunjangan</th>
                        <th class="py-3.5 px-4 font-bold">Status BPJS Indonesia</th>
                        <th class="py-3.5 px-4 font-bold">Potongan BPJS (1% + 3%)</th>
                        <th class="py-3.5 px-4 font-bold">Take Home Pay (THP)</th>
                        <th class="py-3.5 px-4 font-bold text-center">Aksi Slip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($pegawais as $p)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-4">
                                <div class="font-bold text-slate-900">{{ $p->nama }}</div>
                                <div class="text-[11px] text-slate-500">{{ $p->divisi?->nama ?: 'Staff Operasional' }}</div>
                                <div class="text-[10px] font-bold text-indigo-900 mt-0.5">🏢 {{ $p->area_kerja ?: 'Head Office PT ISW' }}</div>
                            </td>

                            <td class="py-4 px-4 font-medium">
                                <div class="text-slate-900 font-bold">Gapok: Rp {{ number_format($p->gaji_pokok, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-slate-500">T. Jabatan: Rp {{ number_format($p->tunjangan_jabatan, 0, ',', '.') }}</div>
                                <div class="text-[11px] text-slate-500">T. Transport/Makan: Rp {{ number_format($p->tunjangan_transport, 0, ',', '.') }}</div>
                            </td>

                            <td class="py-4 px-4 space-y-1">
                                <div>
                                    @if($p->status_bpjs_kesehatan)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                            ✓ BPJS Kes ({{ $p->no_bpjs_kesehatan ?: 'Aktif' }})
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-slate-500">
                                            ✕ BPJS Kes (Belum)
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    @if($p->status_bpjs_ketenagakerjaan)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                            ✓ BPJS TK ({{ $p->no_bpjs_ketenagakerjaan ?: 'Aktif' }})
                                        </span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-100 text-slate-500">
                                            ✕ BPJS TK (Belum)
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="py-4 px-4 text-xs font-semibold text-rose-700 space-y-0.5">
                                <div>Kes (1%): Rp {{ number_format($p->calc_bpjs_kesehatan, 0, ',', '.') }}</div>
                                <div>TK (3% JHT+JP): Rp {{ number_format($p->calc_bpjs_tk, 0, ',', '.') }}</div>
                                <div class="font-bold text-rose-900 border-t border-rose-100 pt-0.5">
                                    Total Pot: Rp {{ number_format($p->calc_total_potongan, 0, ',', '.') }}
                                </div>
                            </td>

                            <td class="py-4 px-4">
                                <div class="font-black text-[#000d6b] text-sm">
                                    Rp {{ number_format($p->calc_take_home_pay, 0, ',', '.') }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-bold">Gaji Bersih Diterima</div>
                            </td>

                            <td class="py-4 px-4 text-center space-x-1 whitespace-nowrap">
                                <button type="button" onclick="openModalEditGaji({{ json_encode($p) }})"
                                        class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition">
                                    ⚙️ Setting BPJS/Gaji
                                </button>
                                <a href="{{ route('admin.slip.cetak', ['pegawai' => $p->id, 'bulan' => $bulan]) }}" target="_blank"
                                   class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition inline-block">
                                    📄 Cetak Slip
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                Tidak ada pegawai ditemukan pada kantor/site ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Setting Gaji Pokok & Status BPJS -->
<div id="modal-gaji" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-xl space-y-4 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#000d6b]">Edit Master Gaji & Status BPJS Pegawai</h2>
            <button onclick="document.getElementById('modal-gaji').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form id="form-gaji" method="POST" action="" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Pegawai</label>
                <input type="text" id="modal-nama-pegawai" readonly class="w-full px-3.5 py-2 rounded-lg border border-slate-200 bg-slate-100 text-slate-600 font-bold">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Gaji Pokok (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="gaji_pokok" id="modal-gaji-pokok" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">T. Jabatan (Rp)</label>
                    <input type="number" name="tunjangan_jabatan" id="modal-tunjangan-jabatan" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">T. Transport (Rp)</label>
                    <input type="number" name="tunjangan_transport" id="modal-tunjangan-transport" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                </div>
            </div>

            <!-- Setting BPJS Kesehatan -->
            <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-200 space-y-2">
                <div class="font-extrabold text-emerald-900 text-xs">🏥 Status Kepesertaan BPJS Kesehatan (Potongan 1%)</div>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_kesehatan" value="1" id="bpjs-kes-1" class="text-emerald-600">
                        <span class="font-bold text-slate-800 text-[11px]">✓ Sudah Terdaftar (Potong 1%)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_kesehatan" value="0" id="bpjs-kes-0" class="text-emerald-600">
                        <span class="font-bold text-slate-500 text-[11px]">✕ Belum Terdaftar</span>
                    </label>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1 text-[11px]">No. Kartu BPJS Kesehatan</label>
                    <input type="text" name="no_bpjs_kesehatan" id="modal-no-bpjs-kes" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 outline-none font-mono text-slate-800" placeholder="e.g. 000123456789">
                </div>
            </div>

            <!-- Setting BPJS Ketenagakerjaan -->
            <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-200 space-y-2">
                <div class="font-extrabold text-indigo-900 text-xs">🛡️ Status BPJS Ketenagakerjaan (Potongan 3% JHT+JP)</div>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_ketenagakerjaan" value="1" id="bpjs-tk-1" class="text-indigo-600">
                        <span class="font-bold text-slate-800 text-[11px]">✓ Sudah Terdaftar (Potong 3%)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_ketenagakerjaan" value="0" id="bpjs-tk-0" class="text-indigo-600">
                        <span class="font-bold text-slate-500 text-[11px]">✕ Belum Terdaftar</span>
                    </label>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1 text-[11px]">No. Kartu BPJS Ketenagakerjaan (TK)</label>
                    <input type="text" name="no_bpjs_ketenagakerjaan" id="modal-no-bpjs-tk" class="w-full px-3 py-1.5 rounded-lg border border-slate-300 outline-none font-mono text-slate-800" placeholder="e.g. 190123456789">
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-gaji').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-xl">Simpan Master Gaji & BPJS</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Master Gaji & BPJS Universal (Massal Per Kantor) -->
<div id="modal-gaji-massal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden overflow-y-auto">
    <div class="bg-white rounded-2xl border border-slate-200 max-w-lg w-full p-6 shadow-xl space-y-4 my-8">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h2 class="text-base font-extrabold text-[#000d6b]">Edit Master Gaji & BPJS Universal (Per Kantor Klien)</h2>
            <button onclick="document.getElementById('modal-gaji-massal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
        </div>

        <form method="POST" action="{{ route('admin.slip.update-salary-bpjs-massal') }}" class="space-y-4 text-xs">
            @csrf
            @method('PUT')

            <div>
                <label class="block font-bold text-slate-700 mb-1">Target Kantor Klien / Site Area <span class="text-rose-500">*</span></label>
                <select name="site" required class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="all">🌐 SEMUA KANTOR KLIEN & SITE AREA (GLOBAL)</option>
                    @foreach($kantorKliens as $kk)
                        <option value="{{ $kk->nama_kantor }}" {{ $site == $kk->nama_kantor ? 'selected' : '' }}>
                            🏢 {{ $kk->nama_kantor }} {{ $kk->kode_kantor ? '('.$kk->kode_kantor.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Nilai Gaji & BPJS di bawah akan diterapkan ke seluruh pegawai di kantor yang dipilih.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Gaji Pokok (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="gaji_pokok" value="5000000" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">T. Jabatan (Rp)</label>
                    <input type="number" name="tunjangan_jabatan" value="500000" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">T. Transport (Rp)</label>
                    <input type="number" name="tunjangan_transport" value="500000" required class="w-full px-3 py-2 rounded-lg border border-slate-300 outline-none font-bold text-slate-900">
                </div>
            </div>

            <!-- Setting BPJS Kesehatan Massal -->
            <div class="p-3 bg-emerald-50/50 rounded-xl border border-emerald-200 space-y-2">
                <div class="font-extrabold text-emerald-900 text-xs">🏥 Status Kepesertaan BPJS Kesehatan (Potongan 1%)</div>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_kesehatan" value="1" checked class="text-emerald-600">
                        <span class="font-bold text-slate-800 text-[11px]">✓ Aktifkan (Potong 1%)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_kesehatan" value="0" class="text-emerald-600">
                        <span class="font-bold text-slate-500 text-[11px]">✕ Non-Aktif (Rp 0)</span>
                    </label>
                </div>
            </div>

            <!-- Setting BPJS Ketenagakerjaan Massal -->
            <div class="p-3 bg-indigo-50/50 rounded-xl border border-indigo-200 space-y-2">
                <div class="font-extrabold text-indigo-900 text-xs">🛡️ Status BPJS Ketenagakerjaan (Potongan 3% JHT+JP)</div>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_ketenagakerjaan" value="1" checked class="text-indigo-600">
                        <span class="font-bold text-slate-800 text-[11px]">✓ Aktifkan (Potong 3%)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2 bg-white rounded-lg border cursor-pointer">
                        <input type="radio" name="status_bpjs_ketenagakerjaan" value="0" class="text-indigo-600">
                        <span class="font-bold text-slate-500 text-[11px]">✕ Non-Aktif (Rp 0)</span>
                    </label>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('modal-gaji-massal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white font-bold rounded-xl shadow-md">Terapkan Ke Seluruh Pegawai Kantor</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalEditGaji(pegawai) {
        document.getElementById('modal-nama-pegawai').value = pegawai.nama;
        document.getElementById('modal-gaji-pokok').value = pegawai.gaji_pokok || 0;
        document.getElementById('modal-tunjangan-jabatan').value = pegawai.tunjangan_jabatan || 0;
        document.getElementById('modal-tunjangan-transport').value = pegawai.tunjangan_transport || 0;

        if (pegawai.status_bpjs_kesehatan) {
            document.getElementById('bpjs-kes-1').checked = true;
        } else {
            document.getElementById('bpjs-kes-0').checked = true;
        }
        document.getElementById('modal-no-bpjs-kes').value = pegawai.no_bpjs_kesehatan || '';

        if (pegawai.status_bpjs_ketenagakerjaan) {
            document.getElementById('bpjs-tk-1').checked = true;
        } else {
            document.getElementById('bpjs-tk-0').checked = true;
        }
        document.getElementById('modal-no-bpjs-tk').value = pegawai.no_bpjs_ketenagakerjaan || '';

        document.getElementById('form-gaji').action = `/admin/slip-gaji/${pegawai.id}/salary-bpjs`;
        document.getElementById('modal-gaji').classList.remove('hidden');
    }
</script>
@endsection
