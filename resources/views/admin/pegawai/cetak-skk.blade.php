<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Keterangan Bekerja - {{ $pegawai->nama }}</title>

    <!-- Tailwind CSS -->
    @if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 11px !important; }
            @page { size: A4; margin: 1.5cm; }
        }
        body { font-family: 'Times New Roman', Times, serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 text-slate-900 leading-relaxed">

    <!-- Action Bar (No Print) -->
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.pegawai.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
            &larr; Kembali ke Kelola Pegawai
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Surat Keterangan Bekerja (Print / PDF)
        </button>
    </div>

    <!-- Official Paper Frame -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-md border border-slate-200 rounded-2xl space-y-6">

        <!-- ── KOP SURAT RESMI PT INTI SARANA WIJAYA ────────────────────────── -->
        <div class="border-b-4 border-[#000d6b] pb-4 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @if(file_exists(public_path('images/Picture1.png')))
                    <img src="{{ asset('images/Picture1.png') }}" alt="Logo PT ISW" class="h-20 w-auto object-contain">
                @else
                    <div class="w-16 h-16 rounded-xl bg-[#000d6b] text-white font-extrabold flex items-center justify-center text-xl">ISW</div>
                @endif
                <div>
                    <h1 class="text-2xl font-black text-[#000d6b] tracking-wider uppercase">PT INTI SARANA WIJAYA</h1>
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wide">Penyedia & Pengelolaan Tenaga Kerja Outsourcing</div>
                    <div class="text-[11px] text-slate-600 font-sans mt-1">
                        Satpam / Security &bull; Cleaning Service &bull; Driver &bull; Professional Administrative Staff
                    </div>
                </div>
            </div>

            <div class="text-right text-[10px] font-sans text-slate-500 leading-tight border-l border-slate-300 pl-4 hidden sm:block">
                <div class="font-bold text-slate-800">HEAD OFFICE PT ISW</div>
                <div>Jl. Raya Jakarta - Bogor No. 88</div>
                <div>Jakarta Selatan, Indonesia</div>
                <div>Telp: (021) 7890-1234</div>
                <div class="text-indigo-900 font-semibold">www.isw.co.id | info@isw.co.id</div>
            </div>
        </div>

        <!-- ── JUDUL SURAT KETERANGAN BEKERJA ─────────────────────────────── -->
        <div class="text-center space-y-1 pt-2">
            <h2 class="text-lg font-black text-[#000d6b] underline tracking-wide uppercase">SURAT KETERANGAN BEKERJA</h2>
            <div class="text-xs font-mono font-bold text-slate-700">Nomor: SKK/ISW/{{ date('Y/m/') }}{{ str_pad($pegawai->id, 4, '0', STR_PAD_LEFT) }}</div>
        </div>

        <!-- ── PARAGRAF PEMBUKA ────────────────────────────────────────────── -->
        <div class="text-xs text-justify space-y-4 leading-relaxed pt-2">
            <p>
                Yang bertanda tangan di bawah ini atas nama Manajemen <strong>PT INTI SARANA WIJAYA</strong> menerangkan dengan sebenarnya bahwa:
            </p>

            <div class="pl-6 space-y-2 border-l-2 border-[#000d6b]">
                <div class="grid grid-cols-12 gap-1">
                    <div class="col-span-3 font-semibold text-slate-700">Nama Lengkap</div>
                    <div class="col-span-9 font-bold text-slate-900">: {{ $pegawai->nama }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">NIK (KTP)</div>
                    <div class="col-span-9 font-bold text-slate-800">: {{ $pegawai->nik ?: '-' }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">Tempat, Tgl Lahir</div>
                    <div class="col-span-9 text-slate-800">: {{ $pegawai->tempat_lahir ? $pegawai->tempat_lahir.', ' : '' }}{{ $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->translatedFormat('d F Y') : '-' }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">Pendidikan Terakhir</div>
                    <div class="col-span-9 text-slate-800">: {{ $pegawai->pendidikan_terakhir ?: 'SMA/SMK' }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">Alamat (KTP)</div>
                    <div class="col-span-9 text-slate-800">: {{ $pegawai->alamat ?: '-' }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">Divisi / Jabatan</div>
                    <div class="col-span-9 font-bold text-slate-800">: {{ $pegawai->divisi?->nama ?: 'Staff Operasional' }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">Status Karyawan</div>
                    <div class="col-span-9 font-semibold text-slate-800">: {{ $pegawai->status_karyawan == 'internal' ? 'Karyawan Internal PT Inti Sarana Wijaya' : 'Karyawan Outsource Placement Site Klien' }}</div>

                    <div class="col-span-3 font-semibold text-slate-700">Penempatan Kerja</div>
                    <div class="col-span-9 font-bold text-indigo-900">: {{ $pegawai->area_kerja ?: 'Head Office PT ISW' }}</div>
                </div>
            </div>

            <p>
                Adalah benar bahwa yang bersangkutan merupakan <strong>Karyawan Aktif PT Inti Sarana Wijaya</strong> yang sampai saat ini ditugaskan dan melaksanakan kewajiban tugas pada unit operasional penempatan <strong>{{ $pegawai->area_kerja ?: 'Head Office PT ISW' }}</strong>.
            </p>

            <p>
                Selama menjadi bagian dari PT Inti Sarana Wijaya, yang bersangkutan telah menunjukkan dedikasi, integritas, kedisiplinan presensi, serta loyalitas kerja yang sangat baik sesuai dengan standar operasional prosedur perusahaan.
            </p>

            <p>
                Demikian Surat Keterangan Bekerja ini diterbitkan secara resmi untuk dipergunakan sebagaimana mestinya.
            </p>
        </div>

        <!-- ── SIGNATURE SECTION ───────────────────────────────────────────── -->
        <div class="pt-8 flex justify-end">
            <div class="w-64 text-center text-xs font-sans space-y-1">
                <div>Jakarta, {{ date('d F Y') }}</div>
                <div class="font-bold text-[#000d6b]">
                    PT INTI SARANA WIJAYA<br>
                    <span class="text-[10px] text-slate-500 font-normal">Manajemen Direksi</span>
                </div>

                <!-- QR Code with Centered ISW Logo & Keterangan TTD Elektronik Sah -->
                <div class="my-auto flex flex-col items-center justify-center space-y-1 py-3">
                    @php
                        $qrUrl = $kepala ? $kepala->getQrSignatureUrl() : 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=VERIFIKASI+DIGITAL+TTE+PT+ISW';
                    @endphp
                    <div class="relative inline-block mx-auto">
                        <img src="{{ $qrUrl }}" alt="QR Verifikasi TTD Elektronik" class="w-20 h-20 object-contain mx-auto">
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            @if(file_exists(public_path('images/Picture1.png')))
                                <img src="{{ asset('images/Picture1.png') }}" alt="Logo ISW" class="w-5 h-5 object-contain bg-white rounded-full p-0.5 shadow-xs border border-slate-300">
                            @else
                                <span class="w-5 h-5 rounded-full bg-[#000d6b] text-white text-[7px] font-black flex items-center justify-center border border-white">ISW</span>
                            @endif
                        </div>
                    </div>
                    <div class="text-[8px] text-slate-500 italic max-w-[190px] leading-tight text-center">
                        Dokumen ini telah ditandatangani secara elektronik (TTE) & sah oleh PT Inti Sarana Wijaya.
                    </div>
                </div>

                <div>
                    <div class="font-black text-[#000d6b] underline text-sm">{{ $kepala?->nama ?: 'Firdaus Romandhanu' }}</div>
                    <div class="text-[10px] text-slate-600 font-bold">{{ $kepala?->jabatan_kepala ?: 'Direktur Utama PT ISW' }}</div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
