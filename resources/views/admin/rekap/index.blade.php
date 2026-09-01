@extends('layouts.app')

@section('title', 'Rekap Data Presensi Per Pegawai & Download Excel - PT Inti Sarana Wijaya')

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
                <span class="text-slate-800 font-bold">Rekapitulasi Pegawai</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Rekap Data Presensi Setiap Orang</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Ringkasan akumulasi kehadiran, keterlambatan, cuti, dan alpa setiap pegawai bulan <strong class="text-slate-800">{{ $carbonMonth->translatedFormat('F Y') }}</strong>.
            </p>
        </div>

        <!-- Download Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <label class="inline-flex items-center gap-2 text-xs font-bold text-slate-700 cursor-pointer bg-slate-100 px-3.5 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-200 transition">
                <input type="checkbox" id="chk-include-location" onchange="updateDownloadLinks()" class="rounded text-[#000d6b]">
                <span>Tampilkan Lokasi GPS & Dinas Luar di Excel</span>
            </label>

            <div class="flex flex-wrap items-center gap-2">
                <a id="btn-export-zip" href="{{ route('admin.export.semua', ['bulan' => $bulan, 'site' => request('site')]) }}"
                   onclick="downloadViaBlob(event, this)"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 01-2-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    📊 Download Excel Universal {{ request('site') ? '('.request('site').')' : 'Semua Pegawai' }}
                </a>
                <a href="{{ route('admin.export.cuti', ['bulan' => $bulan]) }}"
                   onclick="downloadViaBlob(event, this)"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download Excel Cuti & Izin
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.rekap.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Pilih Bulan & Tahun</label>
                <input type="month" name="bulan" value="{{ $bulan }}"
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs font-bold bg-white">
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kantor Klien / Site Area</label>
                <select name="site" class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs font-medium bg-white">
                    <option value="">Semua Kantor Klien</option>
                    @foreach($sites as $st)
                        <option value="{{ $st }}" {{ request('site') == $st ? 'selected' : '' }}>
                            {{ $st }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-3">
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Nama Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pegawai..."
                       class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] outline-none text-slate-800 text-xs">
            </div>

            <div class="sm:col-span-3 sm:self-end">
                <div class="flex gap-2">
                    <button type="submit" class="w-full py-2.5 px-3 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition">
                        Filter Rekap
                    </button>
                    @if(request('site') || request('search') || request('bulan'))
                        <a href="{{ route('admin.rekap.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 text-xs font-bold rounded-lg hover:bg-slate-200">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Table Rekap Per Pegawai -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="section-bar"></span>
                <h2 class="text-base font-extrabold text-slate-900">Rekapitulasi Kehadiran Pegawai — {{ $carbonMonth->translatedFormat('F Y') }}</h2>
            </div>
            <span class="text-xs text-slate-500 font-medium">{{ $rekapData->count() }} Pegawai Terdaftar</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-3.5 px-5 font-bold">No</th>
                        <th class="py-3.5 px-5 font-bold">Pegawai</th>
                        <th class="py-3.5 px-5 font-bold">Divisi / Area Kerja</th>
                        <th class="py-3.5 px-5 font-bold text-center">Hadir</th>
                        <th class="py-3.5 px-5 font-bold text-center">Terlambat</th>
                        <th class="py-3.5 px-5 font-bold text-center">Cuti / Izin</th>
                        <th class="py-3.5 px-5 font-bold text-center">ALPA</th>
                        <th class="py-3.5 px-5 font-bold text-center">Kehadiran</th>
                        <th class="py-3.5 px-5 font-bold text-right">Download & Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white text-slate-700">
                    @forelse($rekapData as $index => $item)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-4 px-5 text-center font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>
                            <td class="py-4 px-5">
                                <div class="font-bold text-slate-900 text-sm">{{ $item->pegawai->nama }}</div>
                                <div class="text-[11px] text-slate-400 font-mono">{{ $item->pegawai->email }}</div>
                            </td>
                            <td class="py-4 px-5 font-medium text-slate-800">
                                {{ $item->pegawai->area_kerja ?: ($item->pegawai->divisi?->nama ?? 'Staff') }}
                            </td>
                            <td class="py-4 px-5 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    {{ $item->total_hadir }} Hari
                                </span>
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if($item->total_terlambat > 0)
                                    <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        {{ $item->total_terlambat }} Hari
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium">0</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if($item->total_cuti > 0)
                                    <span class="inline-block px-2.5 py-1 rounded-md text-xs font-bold bg-indigo-50 text-indigo-800 border border-indigo-200">
                                        {{ $item->total_cuti }} Hari
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium">0</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center">
                                @if($item->total_alpa > 0)
                                    <span class="inline-block px-2.5 py-1 rounded-md text-xs font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                        🔴 {{ $item->total_alpa }} Hari
                                    </span>
                                @else
                                    <span class="text-slate-400 font-medium">0</span>
                                @endif
                            </td>
                            <td class="py-4 px-5 text-center font-bold">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-extrabold
                                    {{ $item->persentase >= 90 ? 'bg-emerald-100 text-emerald-800' : ($item->persentase >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $item->persentase }}%
                                </span>
                            </td>
                            <td class="py-4 px-5 text-right space-x-2 whitespace-nowrap">
                                <a href="{{ route('admin.rekap.detail', ['pegawai' => $item->pegawai->id, 'bulan' => $bulan]) }}"
                                   class="inline-block px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
                                    Detail History
                                </a>
                                <a href="{{ route('admin.export.pegawai', ['pegawai' => $item->pegawai->id, 'bulan' => $bulan]) }}"
                                   onclick="downloadViaBlob(event, this)"
                                   class="inline-block px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow-sm transition">
                                    Download Excel
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400 text-xs">
                                Tidak ada data rekapitulasi pegawai yang sesuai filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    function updateDownloadLinks() {
        const chk = document.getElementById('chk-include-location');
        const btnZip = document.getElementById('btn-export-zip');
        if (btnZip && chk) {
            let url = new URL(btnZip.href);
            if (chk.checked) {
                url.searchParams.set('include_location', '1');
            } else {
                url.searchParams.delete('include_location');
            }
            btnZip.href = url.toString();
        }
    }

    async function downloadViaBlob(event, element) {
        event.preventDefault();
        let rawUrl = element.getAttribute('href');
        if (!rawUrl || rawUrl === '#') return;

        const chk = document.getElementById('chk-include-location');
        let targetUrl = new URL(rawUrl, window.location.origin);
        if (chk && chk.checked) {
            targetUrl.searchParams.set('include_location', '1');
        } else {
            targetUrl.searchParams.delete('include_location');
        }

        const originalText = element.innerHTML;
        element.style.pointerEvents = 'none';
        element.style.opacity = '0.7';
        element.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin inline mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...`;

        try {
            const response = await fetch(targetUrl.toString(), { credentials: 'same-origin' });
            if (!response.ok) {
                alert('Gagal mengunduh file: Server mengembalikan HTTP ' + response.status);
                return;
            }

            const contentType = response.headers.get('Content-Type') || '';
            let defaultName = contentType.includes('zip') ? 'Rekap_Absensi_Pegawai.zip' : 'Rekap_Absensi_Pegawai.xlsx';

            let filename = defaultName;
            const disposition = response.headers.get('Content-Disposition');
            if (disposition && disposition.includes('filename=')) {
                const matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                if (matches && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            }

            const buffer = await response.arrayBuffer();
            if (buffer.byteLength === 0) {
                alert('File hasil unduhan kosong (0 Bytes). Silakan coba lagi.');
                return;
            }

            // Convert ArrayBuffer to Base64 to bypass IDM http/https interceptor
            let binary = '';
            const bytes = new Uint8Array(buffer);
            const chunk = 0x8000;
            for (let i = 0; i < bytes.length; i += chunk) {
                binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunk));
            }
            const base64Data = window.btoa(binary);
            const dataUrl = 'data:application/octet-stream;base64,' + base64Data;

            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = dataUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        } catch (err) {
            alert('Terjadi kesalahan saat mengunduh: ' + err.message);
        } finally {
            element.style.pointerEvents = 'auto';
            element.style.opacity = '1';
            element.innerHTML = originalText;
        }
    }
</script>
@endsection
