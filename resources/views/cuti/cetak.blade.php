<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat_Permohonan_Cuti_{{ str_replace(' ', '_', $cuti->pegawai->nama) }}_{{ $cuti->tanggal_mulai->format('Ymd') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; font-size: 11px !important; }
            .print-container { box-shadow: none !important; border: none !important; width: 100% !important; margin: 0 !important; padding: 0 !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 font-sans text-slate-800 antialiased">

    <!-- Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ url()->previous() ?: route('cuti.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
            &larr; Kembali
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            🖨️ Cetak / Download Surat Cuti (PDF)
        </button>
    </div>

    <!-- MAIN FORMULIR SURAT CUTI -->
    <div class="print-container max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-lg border border-slate-200 rounded-2xl space-y-6">
        
        <!-- Kop Surat -->
        <div class="border-b-2 border-slate-800 pb-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                @if(file_exists(public_path('images/Picture1.png')))
                    <img src="{{ asset('images/Picture1.png') }}" alt="Logo ISW" class="h-16 w-auto object-contain">
                @else
                    <div class="w-14 h-14 bg-[#000d6b] text-white font-black text-xl flex items-center justify-center rounded-xl shadow-sm">ISW</div>
                @endif
                <div>
                    <h1 class="text-xl font-black tracking-wider text-[#000d6b] uppercase">PT. INTI SARANA WIJAYA</h1>
                    <p class="text-xs font-semibold text-slate-600">Head Office: Gedung Menara ISW, Jakarta Pusat, DKI Jakarta</p>
                    <p class="text-[11px] text-slate-500">Layanan Management Alih Daya, Security Guard, Cleaning Service & Facility Services</p>
                </div>
            </div>
            <div class="text-right border-l-2 border-slate-300 pl-4 py-1">
                <div class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">FORMULIR RESMI HRD</div>
                <div class="text-xs font-extrabold text-slate-800">NO: ISW/CUTI/{{ date('Y/m') }}/{{ str_pad($cuti->id, 4, '0', STR_PAD_LEFT) }}</div>
                <div class="text-[10px] text-slate-400 font-semibold mt-0.5">Tgl Pengajuan: {{ $cuti->created_at ? $cuti->created_at->translatedFormat('d F Y') : date('d F Y') }}</div>
            </div>
        </div>

        <!-- Judul Dokumen & Status Badge -->
        <div class="text-center relative space-y-1 py-2">
            <h2 class="text-base font-black text-slate-900 tracking-wide uppercase underline decoration-[#000d6b] decoration-2 underline-offset-4">
                FORMULIR PERMOHONAN & SURAT IZIN CUTI PEGAWAI
            </h2>
            <p class="text-xs text-slate-500">Dokumen Permohonan Cuti Tahunan, Cuti Sakit, dan Izin Meninggalkan Tugas</p>

            <!-- Stamp Status Approval -->
            <div class="absolute right-0 top-0">
                @if($cuti->status === 'approved')
                    <span class="inline-block px-3 py-1 bg-emerald-100 border-2 border-emerald-600 text-emerald-800 font-black text-xs rounded-lg uppercase tracking-wider shadow-sm transform rotate-3">
                        ✓ DISETUJUI / APPROVED
                    </span>
                @elseif($cuti->status === 'rejected')
                    <span class="inline-block px-3 py-1 bg-rose-100 border-2 border-rose-600 text-rose-800 font-black text-xs rounded-lg uppercase tracking-wider shadow-sm transform -rotate-2">
                        ❌ DITOLAK / REJECTED
                    </span>
                @else
                    <span class="inline-block px-3 py-1 bg-amber-100 border-2 border-amber-500 text-amber-900 font-black text-xs rounded-lg uppercase tracking-wider shadow-sm">
                        ⏳ MENUNGGU PERSETUJUAN
                    </span>
                @endif
            </div>
        </div>

        <!-- Data Pegawai Pemohon -->
        <div class="space-y-2">
            <div class="bg-slate-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider">
                I. DATA PEGAWAI PEMOHON
            </div>
            <div class="grid grid-cols-12 gap-y-2 text-xs px-2 py-1">
                <div class="col-span-3 font-semibold text-slate-600">Nama Lengkap</div>
                <div class="col-span-9 font-bold text-slate-900">: {{ $cuti->pegawai->nama }}</div>

                <div class="col-span-3 font-semibold text-slate-600">NIK (KTP)</div>
                <div class="col-span-9 font-mono text-slate-800">: {{ $cuti->pegawai->nik ?: '-' }}</div>

                <div class="col-span-3 font-semibold text-slate-600">Divisi / Jabatan</div>
                <div class="col-span-9 font-semibold text-slate-800">: {{ $cuti->pegawai->divisi?->nama ?: 'Staff Operasional' }}</div>

                <div class="col-span-3 font-semibold text-slate-600">Status Karyawan</div>
                <div class="col-span-9 font-semibold text-slate-800">: {{ $cuti->pegawai->status_karyawan == 'internal' ? 'Karyawan Internal PT ISW' : 'Karyawan Outsource Placement Site Klien' }}</div>

                <div class="col-span-3 font-semibold text-slate-600">Penempatan Kerja</div>
                <div class="col-span-9 font-bold text-indigo-900">: {{ $cuti->pegawai->area_kerja ?: 'Head Office PT ISW' }}</div>
            </div>
        </div>

        <!-- Rincian Permohonan Cuti -->
        <div class="space-y-2">
            <div class="bg-slate-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider">
                II. RINCIAN PERMOHONAN CUTI / IZIN
            </div>
            <div class="border border-slate-300 rounded-xl p-4 bg-slate-50/50 space-y-3 text-xs">
                <div class="grid grid-cols-12 gap-y-2">
                    <div class="col-span-3 font-semibold text-slate-600">Tipe / Jenis Cuti</div>
                    <div class="col-span-9 font-bold text-[#000d6b] uppercase">: {{ $cuti->tipe_cuti }}</div>

                    <div class="col-span-3 font-semibold text-slate-600">Tanggal Mulai Cuti</div>
                    <div class="col-span-9 font-bold text-slate-900">: {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('l, d F Y') }}</div>

                    <div class="col-span-3 font-semibold text-slate-600">Tanggal Selesai Cuti</div>
                    <div class="col-span-9 font-bold text-slate-900">: {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('l, d F Y') }}</div>

                    <div class="col-span-3 font-semibold text-slate-600">Total Durasi Cuti</div>
                    <div class="col-span-9 font-extrabold text-emerald-800">: {{ $cuti->jumlah_hari }} Hari Kerja / Kalender</div>

                    <div class="col-span-3 font-semibold text-slate-600">Alasan Permohonan Cuti</div>
                    <div class="col-span-9 font-medium text-slate-800 italic bg-white p-2.5 rounded-lg border border-slate-200">: "{{ $cuti->alasan }}"</div>
                </div>
            </div>
        </div>

        <!-- Catatan & Persetujuan Admin -->
        <div class="space-y-2">
            <div class="bg-slate-800 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold uppercase tracking-wider">
                III. KEPUTUSAN & CATATAN ATASAN / HRD
            </div>
            <div class="border border-slate-300 rounded-xl p-3 text-xs bg-slate-50 space-y-1">
                <div class="font-bold text-slate-700">Catatan Verifikasi HRD / Management:</div>
                <p class="text-slate-800 italic">
                    {{ $cuti->catatan_admin ?: ($cuti->status == 'approved' ? 'Permohonan cuti telah disetujui dan jadwal kerja telah disesuaikan di sistem presensi.' : 'Permohonan cuti sedang dalam proses peninjauan atasan.') }}
                </p>
                @if($cuti->approver)
                    <div class="text-[10px] text-slate-500 font-semibold pt-1 border-t border-slate-200 mt-2">
                        Diverifikasi Oleh: <strong>{{ $cuti->approver->nama }}</strong> pada {{ $cuti->approved_at ? $cuti->approved_at->translatedFormat('d F Y H:i WIB') : '-' }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Section Tanda Tangan Formal (3 Kolom) -->
        <div class="pt-4 grid grid-cols-3 gap-4 text-center text-xs font-sans">
            
            <!-- Kolom Pemohon -->
            <div class="flex flex-col justify-between h-44 py-1 border-r border-slate-200 pr-2">
                <div>
                    <div class="font-bold text-slate-700">Pemohon Cuti,</div>
                    <div class="text-[10px] text-slate-500">Pegawai Ybs</div>
                </div>
                <div class="my-auto text-[10px] text-slate-400 italic">( Tanda Tangan )</div>
                <div>
                    <div class="font-black text-slate-900 underline">{{ $cuti->pegawai->nama }}</div>
                    <div class="text-[10px] text-slate-600 font-semibold">{{ $cuti->pegawai->area_kerja ?: 'PT ISW' }}</div>
                </div>
            </div>

            <!-- Kolom Atasan / HRD -->
            <div class="flex flex-col justify-between h-44 py-1 border-r border-slate-200 pr-2">
                <div>
                    <div class="font-bold text-slate-700">Disetujui Oleh,</div>
                    <div class="text-[10px] text-slate-500">Atasan / Supervisor / HRD</div>
                </div>
                <div class="my-auto text-[10px] text-slate-400 italic">
                    @if($cuti->status == 'approved')
                        <span class="text-emerald-700 font-bold">✓ VERIFIED & APPROVED</span>
                    @else
                        ( Tanda Tangan Atasan )
                    @endif
                </div>
                <div>
                    <div class="font-black text-slate-900 underline">{{ $cuti->approver?->nama ?: 'HRD Management ISW' }}</div>
                    <div class="text-[10px] text-slate-600 font-semibold">Operational & HR Dept</div>
                </div>
            </div>

            <!-- Kolom Direktur Utama (QR Code TTE dengan Logo ISW) -->
            <div class="flex flex-col justify-between h-44 py-1">
                <div>
                    <div class="font-bold text-[#000d6b]">Disahkan Oleh,</div>
                    <div class="text-[10px] text-slate-500 font-normal">PT INTI SARANA WIJAYA</div>
                </div>

                <div class="my-auto flex flex-col items-center justify-center space-y-1">
                    @php
                        $qrUrl = $kepala ? $kepala->getQrSignatureUrl() : 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=VERIFIKASI+DIGITAL+TTE+CUTI+PT+ISW';
                    @endphp
                    <div class="relative inline-block mx-auto">
                        <img src="{{ $qrUrl }}" alt="QR Verifikasi TTD Elektronik" class="w-16 h-16 object-contain mx-auto">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            @if(file_exists(public_path('images/Picture1.png')))
                                <img src="{{ asset('images/Picture1.png') }}" alt="Logo ISW" class="w-4 h-4 object-contain bg-white rounded-full p-0.5 shadow-xs border border-slate-300">
                            @else
                                <span class="w-4 h-4 rounded-full bg-[#000d6b] text-white text-[6px] font-black flex items-center justify-center border border-white">ISW</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-[8px] text-slate-500 italic max-w-[170px] leading-tight">
                        Dokumen ini sah secara elektronik (TTE) PT Inti Sarana Wijaya.
                    </div>
                </div>

                <div>
                    <div class="font-black text-[#000d6b] underline">{{ $kepala?->nama ?: 'Firdaus Romandhanu' }}</div>
                    <div class="text-[10px] text-slate-600 font-bold">Direktur Utama PT ISW</div>
                </div>
            </div>

        </div>

    </div>

</body>
</html>
