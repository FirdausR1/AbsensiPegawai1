<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak MoU Kerjasama - {{ $mouKontrak->nomor_mou }}</title>

    <!-- Tailwind CSS (Vite / CDN Fallback for Print) -->
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
        <a href="{{ route('admin.mou.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
            &larr; Kembali ke Daftar MoU
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Dokumen MoU Kerjasama (Print / PDF)
        </button>
    </div>

    <!-- Official Paper Document Frame -->
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

        <!-- ── JUDUL SURAT MOU ─────────────────────────────────────────────── -->
        <div class="text-center space-y-1 pt-2">
            <h2 class="text-lg font-black text-[#000d6b] underline tracking-wide uppercase">SURAT PERJANJIAN KERJASAMA (MoU)</h2>
            <div class="text-xs font-mono font-bold text-slate-700">Nomor: {{ $mouKontrak->nomor_mou }}</div>
            <div class="text-xs italic text-slate-500">Tentang Pengelolaan & Penempatan Tenaga Kerja Outsourcing</div>
        </div>

        <!-- ── PARAGRAF PEMBUKA ────────────────────────────────────────────── -->
        <div class="text-xs text-justify space-y-3 leading-relaxed">
            <p>
                Pada hari ini, <strong>{{ $mouKontrak->tanggal_mou->translatedFormat('l') }}</strong> tanggal <strong>{{ $mouKontrak->tanggal_mou->translatedFormat('d F Y') }}</strong>, kami yang bertanda tangan di bawah ini:
            </p>

            <div class="pl-4 space-y-2 border-l-2 border-[#000d6b]">
                <div class="grid grid-cols-12 gap-1">
                    <div class="col-span-3 font-bold">1. Nama Perusahaan</div>
                    <div class="col-span-9 font-bold text-[#000d6b]">: PT INTI SARANA WIJAYA (PT ISW)</div>
                    <div class="col-span-3 font-bold">   Diwakili Oleh</div>
                    <div class="col-span-9 font-bold">: {{ $kepala?->nama ?: 'Firdaus Romandhanu' }} ({{ $kepala?->jabatan_kepala ?: 'Direktur Utama' }})</div>
                    <div class="col-span-3 font-bold">   Alamat Head Office</div>
                    <div class="col-span-9">: Jl. Raya Jakarta - Bogor No. 88, Jakarta Selatan</div>
                </div>
                <div class="text-slate-600 italic">Selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong> (Penyedia Tenaga Kerja).</div>
            </div>

            <div class="pl-4 space-y-2 border-l-2 border-amber-500">
                <div class="grid grid-cols-12 gap-1">
                    <div class="col-span-3 font-bold">2. Nama Kantor Klien</div>
                    <div class="col-span-9 font-bold text-slate-900">: {{ $mouKontrak->nama_kantor }}</div>
                    <div class="col-span-3 font-bold">   Penanggung Jawab</div>
                    <div class="col-span-9 font-bold">: {{ $mouKontrak->penanggung_jawab_klien }} ({{ $mouKontrak->jabatan_klien }})</div>
                    <div class="col-span-3 font-bold">   Telepon / Kontak</div>
                    <div class="col-span-9">: {{ $mouKontrak->telepon_klien ?: '-' }}</div>
                    <div class="col-span-3 font-bold">   Alamat Proyek</div>
                    <div class="col-span-9">: {{ $mouKontrak->alamat_klien ?: 'Site Placement Proyek' }}</div>
                </div>
                <div class="text-slate-600 italic">Selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong> (Pengguna Jasa Outsourcing).</div>
            </div>

            <p>
                <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> secara bersama-sama sepakat untuk mengikatkan diri dalam Perjanjian Kerjasama Penempatan Tenaga Kerja Outsourcing dengan syarat-syarat dan ketentuan sebagai berikut:
            </p>
        </div>

        <!-- ── PASAL PASAL KETENTUAN ──────────────────────────────────────── -->
        <div class="space-y-4 text-xs leading-relaxed text-justify">
            <div>
                <h3 class="font-bold text-[#000d6b] uppercase">Pasal 1: Lingkup Pekerjaan & Layanan</h3>
                <p>
                    PIHAK PERTAMA bertindak sebagai penyedia dan pengelola tenaga kerja berkualifikasi untuk ditempatkan pada lokasi operasional PIHAK KEDUA dengan jenis layanan <strong>{{ $mouKontrak->layanan_outsourcing }}</strong> sejumlah <strong>{{ $mouKontrak->jumlah_personil }} Personil</strong>.
                </p>
            </div>

            <div>
                <h3 class="font-bold text-[#000d6b] uppercase">Pasal 2: Jangka Waktu Kerjasama</h3>
                <p>
                    Perjanjian Kerjasama ini berlaku untuk jangka waktu efektif mulai tanggal <strong>{{ $mouKontrak->tanggal_mulai->translatedFormat('d F Y') }}</strong> sampai dengan tanggal <strong>{{ $mouKontrak->tanggal_selesai->translatedFormat('d F Y') }}</strong>, dan dapat diperpanjang atas kesepakatan tertulis kedua belah pihak.
                </p>
            </div>

            <div>
                <h3 class="font-bold text-[#000d6b] uppercase">Pasal 3: Pengawasan & Presensi Kehadiran Digital</h3>
                <p>
                    Pengawasan performa, tugas harian/periodik, serta presensi kehadiran personil dilakukan secara real-time menggunakan Sistem Aplikasi Presensi GPS & Monitoring Digital PT Inti Sarana Wijaya.
                </p>
            </div>

            @if($mouKontrak->catatan_pasal)
                <div>
                    <h3 class="font-bold text-[#000d6b] uppercase">Pasal 4: Ketentuan Khusus & Pembayaran</h3>
                    <p class="whitespace-pre-line">{{ $mouKontrak->catatan_pasal }}</p>
                </div>
            @endif
        </div>

        <!-- ── TANDA TANGAN DUA PIHAK + QR CODE KEPALA ISW ─────────────────── -->
        <div class="pt-6">
            <div class="text-xs font-bold text-right mb-4">
                Ditetapkan di Jakarta, {{ $mouKontrak->tanggal_mou->translatedFormat('d F Y') }}
            </div>

            <div class="grid grid-cols-2 gap-8 text-center text-xs">
                <!-- PIHAK PERTAMA: PT ISW (Direktur Utama) -->
                <div class="flex flex-col justify-between h-48 py-2">
                    <div class="font-bold text-[#000d6b]">
                        PIHAK PERTAMA<br>
                        <span class="text-[10px] text-slate-500 font-normal">PT INTI SARANA WIJAYA</span>
                    </div>

                    <!-- QR Code with Centered ISW Logo & Keterangan TTD Elektronik Sah -->
                    <div class="my-auto flex flex-col items-center justify-center space-y-1 py-1">
                        @if($kepala && $kepala->qr_signature_path)
                            <div class="relative inline-block mx-auto">
                                <img src="{{ $kepala->qr_signature_path }}" alt="QR Verifikasi TTD Elektronik" class="w-20 h-20 object-contain mx-auto">
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                    @if(file_exists(public_path('images/Picture1.png')))
                                        <img src="{{ asset('images/Picture1.png') }}" alt="Logo ISW" class="w-5 h-5 object-contain bg-white rounded-full p-0.5 shadow-xs border border-slate-300">
                                    @else
                                        <span class="w-5 h-5 rounded-full bg-[#000d6b] text-white text-[7px] font-black flex items-center justify-center border border-white">ISW</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="w-16 h-16 bg-slate-100 border border-slate-300 rounded-lg flex items-center justify-center text-[9px] font-bold text-slate-400">QR CODE</div>
                        @endif
                        <div class="text-[9px] text-slate-500 font-sans italic max-w-[190px] leading-tight text-center">
                            Dokumen ini telah ditandatangani secara elektronik (TTE) & sah oleh PT Inti Sarana Wijaya.
                        </div>
                    </div>

                    <div>
                        <div class="font-black text-[#000d6b] underline">{{ $kepala?->nama ?: 'Firdaus Romandhanu' }}</div>
                        <div class="text-[10px] text-slate-600 font-bold">{{ $kepala?->jabatan_kepala ?: 'Direktur Utama PT ISW' }}</div>
                    </div>
                </div>

                <!-- PIHAK KEDUA: KANTOR KLIEN -->
                <div class="flex flex-col justify-between h-48 py-2">
                    <div class="font-bold text-slate-800">
                        PIHAK KEDUA<br>
                        <span class="text-[10px] text-slate-500 font-normal">{{ $mouKontrak->nama_kantor }}</span>
                    </div>

                    <div class="my-auto text-[10px] text-slate-400 italic">
                        <div class="w-16 h-16 rounded-full border-2 border-dashed border-slate-300 mx-auto flex items-center justify-center text-[9px] font-bold">
                            STEMPEL KLIEN
                        </div>
                    </div>

                    <div>
                        <div class="font-black text-slate-900 underline">{{ $mouKontrak->penanggung_jawab_klien }}</div>
                        <div class="text-[10px] text-slate-600 font-bold">{{ $mouKontrak->jabatan_klien }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
