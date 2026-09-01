<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Peringatan - {{ $suratPeringatan->tingkat_sp }} - {{ $suratPeringatan->pegawai->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; background-color: #f8fafc; }
        @media print {
            .no-print { display: none !important; }
            body { background-color: white !important; padding: 0 !important; }
            .print-container { border: none !important; shadow: none !important; margin: 0 !important; width: 100% !important; padding: 0 !important; }
            @page { size: A4; margin: 15mm 20mm; }
        }
    </style>
</head>
<body class="p-4 sm:p-8 text-slate-800 antialiased min-h-screen flex flex-col items-center">

    <!-- Floating Print Control Bar -->
    <div class="no-print w-full max-w-4xl bg-white rounded-2xl border border-slate-200 p-4 mb-6 shadow-sm flex items-center justify-between">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.sp.index') }}" class="px-3.5 py-2 bg-slate-100 text-slate-700 hover:bg-slate-200 text-xs font-bold rounded-xl transition">
                &larr; Kembali ke Kelola SP
            </a>
            <span class="text-xs text-slate-400 font-medium">Dokumen Resmi Surat Peringatan PT ISW</span>
        </div>

        <button onclick="window.print()" class="px-5 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- Official Printable Letter Container (A4 Size Sheet) -->
    <div class="print-container w-full max-w-4xl bg-white border border-slate-300 p-8 sm:p-12 rounded-2xl shadow-xl space-y-6">
        
        <!-- Official Kop Surat Header PT Inti Sarana Wijaya -->
        <div class="border-b-4 border-double border-[#000d6b] pb-4 flex items-center gap-5">
            <img src="{{ asset('images/Picture1.png') }}" alt="PT Inti Sarana Wijaya Logo" class="w-20 h-20 object-contain shrink-0">
            <div class="flex-1">
                <h1 class="text-xl sm:text-2xl font-black text-[#000d6b] tracking-wider uppercase leading-none">PT INTI SARANA WIJAYA</h1>
                <p class="text-xs font-bold text-slate-700 tracking-wide mt-1">Facility Management, Security Guard & General Services</p>
                <p class="text-[11px] text-slate-500 mt-0.5 leading-snug">
                    Jl. Raya Utama No. 88, Kawasan Industri & Bisnis Terpadu, Indonesia<br>
                    Telp: (021) 555-0199 | Email: hrd@intisaranawijaya.co.id | Website: www.intisaranawijaya.co.id
                </p>
            </div>
        </div>

        <!-- Document Title & Reference Number -->
        <div class="text-center space-y-1 py-2">
            <h2 class="text-lg sm:text-xl font-black text-[#000d6b] tracking-wide uppercase underline">
                SURAT PERINGATAN ({{ strtoupper($suratPeringatan->tingkat_sp) }})
            </h2>
            <p class="text-xs font-bold text-slate-600">
                Nomor: SP/ISW/{{ $suratPeringatan->tanggal_sp->format('Y/m') }}/{{ str_pad($suratPeringatan->id, 3, '0', STR_PAD_LEFT) }}
            </p>
        </div>

        <!-- Statement Intro -->
        <div class="text-xs text-slate-800 leading-relaxed space-y-3">
            <p>
                Surat Peringatan ini diterbitkan oleh Manajemen <strong>PT Inti Sarana Wijaya (ISW)</strong> berdasarkan hasil evaluasi kedisiplinan dan laporan pelanggaran tata tertib kerja perusahaan. Surat Peringatan ini ditujukan kepada:
            </p>

            <!-- Employee Details Box -->
            <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-2 font-medium">
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-500 font-bold">Nama Pegawai</span>
                    <span class="col-span-8 font-black text-slate-900">: {{ $suratPeringatan->pegawai->nama }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-500 font-bold">Jabatan / Divisi / Area</span>
                    <span class="col-span-8 font-bold text-slate-800">: {{ $suratPeringatan->pegawai->area_kerja ?: ($suratPeringatan->pegawai->divisi?->nama ?? 'Staff Operasional') }}</span>
                </div>
                <div class="grid grid-cols-12">
                    <span class="col-span-4 text-slate-500 font-bold">Email / Kontak</span>
                    <span class="col-span-8 text-slate-700 font-mono">: {{ $suratPeringatan->pegawai->email }}</span>
                </div>
            </div>

            <!-- Violation Details Section -->
            <div class="space-y-2 pt-2">
                <h3 class="font-bold text-slate-900 text-xs uppercase tracking-wider text-[#000d6b]">Rincian & Dasaran Pelanggaran:</h3>
                
                <div class="p-4 bg-rose-50/50 rounded-xl border border-rose-200 space-y-2">
                    <div class="font-extrabold text-rose-900 text-sm">
                        ⚖️ {{ $suratPeringatan->pasal_pelanggaran }}
                    </div>
                    <p class="text-slate-700 leading-relaxed whitespace-pre-line text-xs">
                        {{ $suratPeringatan->deskripsi }}
                    </p>
                </div>
            </div>

            <!-- Sanction Consequences -->
            <div class="space-y-2 pt-2">
                <p>
                    Atas tindakan pelanggaran tersebut di atas, Manajemen PT Inti Sarana Wijaya secara resmi menetapkan sanksi kedisiplinan berupa 
                    <strong class="text-rose-700 underline">{{ strtoupper($suratPeringatan->tingkat_sp) }}</strong> 
                    dengan ketentuan sebagai berikut:
                </p>
                <ul class="list-disc list-inside space-y-1 text-slate-700 pl-2">
                    <li>Sanksi ini berlaku sejak tanggal diterbitkan: <strong>{{ $suratPeringatan->tanggal_sp->translatedFormat('d F Y') }}</strong> sampai dengan <strong>{{ $suratPeringatan->berlaku_sampai ? $suratPeringatan->berlaku_sampai->translatedFormat('d F Y') : 'Masa Penilaian' }}</strong>.</li>
                    <li>Pegawai yang bersangkutan diminta untuk segera melakukan perbaikan sikap, kinerja, dan kedisiplinan kerja sesuai SOP PT ISW.</li>
                    <li>Apabila selama masa berlaku sanksi ini pegawai kembali melakukan pelanggaran serupa atau pelanggaran lainnya, maka Manajemen PT Inti Sarana Wijaya akan memberikan sanksi tindakan tegas berupa tingkatan Surat Peringatan berikutnya hingga Pemutusan Hubungan Kerja (PHK).</li>
                </ul>
            </div>
        </div>

        <!-- Closing Statement -->
        <p class="text-xs text-slate-700 pt-2">
            Demikian Surat Peringatan ini dibuat dan diterbitkan untuk dapat diperhatikan serta dipatuhi sebagaimana mestinya.
        </p>

        <div class="text-xs font-bold text-slate-800 text-right pt-2">
            Jakarta, {{ $suratPeringatan->tanggal_sp->translatedFormat('d F Y') }}
        </div>

        <!-- Official 3-Party Signature Blocks -->
        <div class="grid grid-cols-3 gap-4 pt-6 text-center text-xs">
            <!-- Signature 1: Yang Menerima (Pegawai) -->
            <div class="flex flex-col justify-between h-44 p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                <div class="font-bold text-slate-700">Yang Menerima,<br><span class="text-[10px] text-slate-400 font-normal">(Pegawai Yang Bersangkutan)</span></div>
                
                <div class="my-auto flex items-center justify-center">
                    @if($suratPeringatan->pegawai->hasSignature() && file_exists(public_path($suratPeringatan->pegawai->signature_path)))
                        <img src="{{ asset($suratPeringatan->pegawai->signature_path) }}" alt="Tanda Tangan Pegawai" class="max-h-16 object-contain">
                    @else
                        <div class="text-[10px] text-slate-300 italic">( Tanda Tangan / Digital )</div>
                    @endif
                </div>

                <div>
                    <div class="font-black text-slate-900 underline">{{ $suratPeringatan->pegawai->nama }}</div>
                    <div class="text-[10px] text-slate-400">Pegawai PT ISW</div>
                </div>
            </div>

            <!-- Signature 2: Yang Mengajukan (Supervisor / Danru) -->
            <div class="flex flex-col justify-between h-44 p-3 rounded-xl border border-slate-200 bg-slate-50/50">
                <div class="font-bold text-slate-700">Yang Mengajukan,<br><span class="text-[10px] text-slate-400 font-normal">(Supervisor / Danru Area)</span></div>
                
                <div class="my-auto text-[10px] text-slate-300 italic">
                    ( Tanda Tangan Atasan )
                </div>

                <div>
                    <div class="font-black text-slate-900 underline">{{ $suratPeringatan->creator?->nama ?: 'Supervisor / Danru' }}</div>
                    <div class="text-[10px] text-slate-400">Atasan Langsung</div>
                </div>
            </div>

            <!-- Signature 3: Yang Menetapkan (Direktur / Kepala ISW) -->
            @php
                $kepala = \App\Models\Pegawai::where('role', 'kepala_isw')->orWhere('is_admin', true)->first();
            @endphp
            <div class="flex flex-col justify-between h-44 p-3 rounded-xl border border-indigo-200 bg-indigo-50/30">
                <div class="font-bold text-[#000d6b]">Yang Menetapkan,<br><span class="text-[10px] text-slate-500 font-normal">(Direktur Utama PT ISW)</span></div>
                
                <div class="my-auto flex flex-col items-center justify-center space-y-1">
                    @if($kepala && $kepala->qr_signature_path)
                        <div class="relative inline-block mx-auto">
                            <img src="{{ $kepala->qr_signature_path }}" alt="QR Verifikasi TTD Elektronik" class="w-16 h-16 object-contain mx-auto">
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                @if(file_exists(public_path('images/Picture1.png')))
                                    <img src="{{ asset('images/Picture1.png') }}" alt="Logo ISW" class="w-4 h-4 object-contain bg-white rounded-full p-0.5 shadow-xs border border-slate-300">
                                @else
                                    <span class="w-4 h-4 rounded-full bg-[#000d6b] text-white text-[6px] font-black flex items-center justify-center border border-white">ISW</span>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="w-12 h-12 bg-slate-100 border border-slate-300 rounded flex items-center justify-center text-[8px] font-bold text-slate-400">QR CODE</div>
                    @endif
                    <div class="text-[8px] text-slate-500 font-sans italic max-w-[160px] leading-tight text-center">
                        Dokumen ini telah ditandatangani secara elektronik (TTE) & sah oleh PT Inti Sarana Wijaya.
                    </div>
                </div>

                <div>
                    <div class="font-black text-[#000d6b] underline">{{ $kepala?->nama ?: 'Firdaus Romandhanu' }}</div>
                    <div class="text-[10px] text-slate-500 font-bold">{{ $kepala?->jabatan_kepala ?: 'Direktur Utama PT ISW' }}</div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
