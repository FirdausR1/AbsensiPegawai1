@extends('layouts.app')

@section('content')
<div class="space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#000d6b] transition">Admin</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Evaluasi Kinerja Pegawai</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Evaluasi Kinerja Periodik</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Penilaian Kinerja Pegawai (Bulanan, 3 Bulan, 6 Bulan, 1 Tahun) berdasarkan Rekap Absensi & Tugas Periodik.
            </p>
        </div>

        <a href="{{ route('admin.evaluasi-kinerja.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-xs sm:text-sm rounded-xl shadow-sm transition shrink-0">
            <span>+</span> Buat Evaluasi Kinerja Baru
        </a>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm rounded-xl font-bold flex items-center justify-between">
            <span>✓ {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 font-extrabold">✕</button>
        </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs">
        <form method="GET" action="{{ route('admin.evaluasi-kinerja.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-600 mb-1">Filter Site / Office:</label>
                <select name="site" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="all">🌐 Semua Site / Office</option>
                    @foreach($kantorKliens as $kk)
                        <option value="{{ $kk->nama_kantor }}" {{ $site == $kk->nama_kantor ? 'selected' : '' }}>
                            🏢 {{ $kk->nama_kantor }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-600 mb-1">Filter Pegawai:</label>
                <select name="pegawai_id" onchange="this.form.submit()" class="px-3 py-2 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold bg-white">
                    <option value="">👤 Semua Pegawai</option>
                    @foreach($pegawais as $p)
                        <option value="{{ $p->id }}" {{ $pegawaiId == $p->id ? 'selected' : '' }}>
                            {{ $p->nama }} ({{ $p->area_kerja ?: 'HO ISW' }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if($site || $pegawaiId)
                <div class="self-end mb-0.5">
                    <a href="{{ route('admin.evaluasi-kinerja.index') }}" class="px-3 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-lg transition inline-block">
                        Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Table Evaluasi Kinerja -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs font-sans border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Pegawai & Site</th>
                        <th class="py-3.5 px-4">Periode Evaluasi</th>
                        <th class="py-3.5 px-4 text-center">Skor Absen</th>
                        <th class="py-3.5 px-4 text-center">Skor Tugas</th>
                        <th class="py-3.5 px-4 text-center">Skor Perilaku</th>
                        <th class="py-3.5 px-4 text-center">Skor Akhir & Grade</th>
                        <th class="py-3.5 px-4">Rekomendasi</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($evaluasis as $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-slate-900">{{ $item->pegawai?->nama }}</div>
                                <div class="text-[10px] text-indigo-900 font-bold">{{ $item->pegawai?->area_kerja ?: 'Head Office PT ISW' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $item->pegawai?->divisi?->nama ?: 'Staff' }}</div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-800 uppercase mb-0.5">
                                    {{ str_replace('_', ' ', strtoupper($item->periode_tipe)) }}
                                </span>
                                <div class="text-[11px] text-slate-600 font-mono">
                                    {{ $item->tanggal_mulai->format('d/m/Y') }} - {{ $item->tanggal_selesai->format('d/m/Y') }}
                                </div>
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                {{ number_format($item->skor_absensi, 1) }}
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                {{ number_format($item->skor_tugas, 1) }}
                            </td>

                            <td class="py-3.5 px-4 text-center font-mono font-bold text-slate-800">
                                {{ number_format($item->skor_perilaku, 1) }}
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <div class="text-sm font-black font-mono text-[#000d6b]">{{ number_format($item->skor_akhir, 1) }}</div>
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-extrabold 
                                    {{ str_contains($item->kategori_penilaian, 'A') ? 'bg-emerald-100 text-emerald-900' : (str_contains($item->kategori_penilaian, 'B') ? 'bg-blue-100 text-blue-900' : 'bg-amber-100 text-amber-900') }}">
                                    {{ $item->kategori_penilaian }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-semibold text-slate-800 max-w-xs">
                                {{ $item->rekomendasi }}
                            </td>

                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <a href="{{ route('admin.evaluasi-kinerja.cetak', $item->id) }}" target="_blank"
                                   class="px-2.5 py-1.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-xs rounded-lg transition inline-block">
                                    📄 Cetak Laporan Evaluasi
                                </a>

                                <form method="POST" action="{{ route('admin.evaluasi-kinerja.destroy', $item->id) }}"
                                      onsubmit="return confirm('Hapus evaluasi kinerja pegawai {{ $item->pegawai?->nama }}?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-lg transition">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400 italic">
                                Belum ada data evaluasi kinerja pegawai. Klik tombol "+ Buat Evaluasi Kinerja Baru" untuk menambahkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($evaluasis->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $evaluasis->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
