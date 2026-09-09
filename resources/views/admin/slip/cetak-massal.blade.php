<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Slip Gaji Massal - {{ $siteName }} ({{ $bulan }})</title>

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
            .page-break { page-break-after: always; page-break-inside: avoid; }
            @page { size: A4; margin: 1.5cm; }
        }
        body { font-family: 'Times New Roman', Times, serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 text-slate-900 leading-relaxed">

    <!-- Action Bar (No Print) -->
    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between no-print">
        <div>
            <a href="{{ route('admin.slip.index', ['bulan' => $bulan, 'site' => $siteName]) }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition inline-block">
                &larr; Kembali ke Payroll
            </a>
            <div class="text-xs text-slate-500 font-bold mt-1">
                Menampilkan <strong>{{ count($dataSlips) }} Slip Gaji Massal</strong> Periode {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }} untuk Site: <span class="text-[#000d6b]">{{ $siteName }}</span>
            </div>
        </div>

        <button onclick="window.print()" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Seluruh Slip Massal ({{ count($dataSlips) }} Pegawai)
        </button>
    </div>

    <!-- Loop Through All Slips -->
    @foreach($dataSlips as $item)
        @php
            $pegawai = $item['pegawai'];
            $slip = $item['slip'];
        @endphp
        <div class="max-w-3xl mx-auto bg-white p-8 sm:p-10 shadow-md border border-slate-200 rounded-2xl space-y-5 mb-8 page-break">

            <!-- ── KOP SURAT RESMI PT INTI SARANA WIJAYA ────────────────────────── -->
            <div class="border-b-4 border-[#000d6b] pb-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if(file_exists(public_path('images/Picture1.png')))
                        <img src="{{ asset('images/Picture1.png') }}" alt="Logo PT ISW" class="h-16 w-auto object-contain">
                    @else
                        <div class="w-14 h-14 rounded-xl bg-[#000d6b] text-white font-extrabold flex items-center justify-center text-lg">ISW</div>
                    @endif
                    <div>
                        <h1 class="text-xl font-black text-[#000d6b] tracking-wider uppercase">PT INTI SARANA WIJAYA</h1>
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wide">Penyedia & Pengelolaan Tenaga Kerja Outsourcing</div>
                        <div class="text-[10px] text-slate-500 font-sans mt-0.5">
                            Satpam / Security &bull; Cleaning Service &bull; Driver &bull; Administrative Staff
                        </div>
                    </div>
                </div>

                <div class="text-right text-[9px] font-sans text-slate-500 leading-tight border-l border-slate-300 pl-3 hidden sm:block">
                    <div class="font-bold text-slate-800">HEAD OFFICE PT ISW</div>
                    <div>Jl. Raya Jakarta - Bogor No. 88</div>
                    <div>Jakarta Selatan</div>
                    <div>Telp: (021) 7890-1234</div>
                </div>
            </div>

            <!-- ── JUDUL SLIP GAJI ─────────────────────────────────────────────── -->
            <div class="text-center space-y-0.5">
                <h2 class="text-base font-black text-[#000d6b] underline tracking-wider uppercase">SLIP GAJI PEGAWAI</h2>
                <div class="text-xs font-bold text-slate-700">Periode: {{ \Carbon\Carbon::parse($bulan.'-01')->translatedFormat('F Y') }}</div>
            </div>

            <!-- ── IDENTITAS PEGAWAI PLAIN TABLE ───────────────────────────────── -->
            <table class="w-full text-xs font-sans border-collapse mb-2">
                <tr>
                    <td class="py-1 w-32 font-semibold text-slate-600">Nama Pegawai</td>
                    <td class="py-1 font-bold text-slate-900">: {{ $pegawai->nama }}</td>
                    <td class="py-1 w-36 font-semibold text-slate-600">Penempatan Site</td>
                    <td class="py-1 font-bold text-indigo-900">: {{ $pegawai->area_kerja ?: 'Head Office PT ISW' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-slate-600">Email / NIK</td>
                    <td class="py-1 text-slate-800">: {{ $pegawai->email }}</td>
                    <td class="py-1 font-semibold text-slate-600">BPJS Kesehatan</td>
                    <td class="py-1 text-slate-800">: {{ $pegawai->status_bpjs_kesehatan ? ($pegawai->no_bpjs_kesehatan ?: 'Terdaftar (1%)') : 'Tidak Ada' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-slate-600">Divisi / Jabatan</td>
                    <td class="py-1 text-slate-800">: {{ $pegawai->divisi?->nama ?: 'Staff Operasional' }}</td>
                    <td class="py-1 font-semibold text-slate-600">BPJS Ketenagakerjaan</td>
                    <td class="py-1 text-slate-800">: {{ $pegawai->status_bpjs_ketenagakerjaan ? ($pegawai->no_bpjs_ketenagakerjaan ?: 'Terdaftar (3%)') : 'Tidak Ada' }}</td>
                </tr>
                <tr>
                    <td class="py-1 font-semibold text-slate-600">Rekening Transfer</td>
                    <td class="py-1 font-bold text-slate-900" colspan="3">: {{ $pegawai->nomor_rekening ? ($pegawai->nama_bank . ' ' . $pegawai->nomor_rekening . ($pegawai->nama_rekening ? ' a.n. ' . $pegawai->nama_rekening : '')) : 'Belum Terdaftar' }}</td>
                </tr>
            </table>

            <!-- ── RINCIAN PENDAPATAN & POTONGAN PLAIN TABLE ────────────────────── -->
            <table class="w-full border-collapse border border-slate-800 text-xs font-sans mb-3">
                <thead>
                    <tr class="bg-slate-100 border-b border-slate-800 text-slate-900">
                        <th class="border-r border-slate-800 py-2 px-3 text-left w-1/2 font-bold uppercase">I. PENERIMAAN (INCOME)</th>
                        <th class="py-2 px-3 text-left w-1/2 font-bold uppercase">II. POTONGAN (DEDUCTIONS)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-slate-300">
                        <td class="border-r border-slate-800 py-2.5 px-3 align-top space-y-1">
                            <div class="flex justify-between">
                                <span>Gaji Pokok</span>
                                <span class="font-bold">Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tunjangan Jabatan</span>
                                <span>Rp {{ number_format($slip->tunjangan_jabatan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tunjangan Transport & Makan</span>
                                <span>Rp {{ number_format($slip->tunjangan_transport, 0, ',', '.') }}</span>
                            </div>
                            @if($slip->bonus_overtime > 0)
                                <div class="flex justify-between">
                                    <span>Bonus / Lembur</span>
                                    <span>Rp {{ number_format($slip->bonus_overtime, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </td>

                        <td class="py-2.5 px-3 align-top space-y-1">
                            <div class="flex justify-between">
                                <span>BPJS Kesehatan (1%)</span>
                                <span>Rp {{ number_format($slip->potongan_bpjs_kesehatan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>BPJS TK (3% JHT+JP)</span>
                                <span>Rp {{ number_format($slip->potongan_bpjs_tk, 0, ',', '.') }}</span>
                            </div>
                            @if($slip->potongan_absensi > 0)
                                <div class="flex justify-between">
                                    <span>Potongan Terlambat/Absen</span>
                                    <span>Rp {{ number_format($slip->potongan_absensi, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($slip->potongan_lainnya > 0)
                                <div class="flex justify-between">
                                    <span>Potongan Lain-lain</span>
                                    <span>Rp {{ number_format($slip->potongan_lainnya, 0, ',', '.') }}</span>
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr class="bg-slate-50 font-bold border-b border-slate-800">
                        <td class="border-r border-slate-800 py-2 px-3">
                            <div class="flex justify-between">
                                <span>TOTAL PENERIMAAN (A)</span>
                                <span>Rp {{ number_format($slip->total_pendapatan, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td class="py-2 px-3">
                            <div class="flex justify-between">
                                <span>TOTAL POTONGAN (B)</span>
                                <span>Rp {{ number_format($slip->total_potongan, 0, ',', '.') }}</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ── TAKE HOME PAY (THP) PLAIN BOX ───────────────────────────────── -->
            <div class="border-2 border-slate-800 p-3 flex justify-between items-center text-xs font-bold font-sans bg-slate-50">
                <span class="uppercase tracking-wide">PENERIMAAN BERSIH / TAKE HOME PAY (THP = A - B)</span>
                <span class="text-sm font-mono font-black text-slate-900">Rp {{ number_format($slip->take_home_pay, 0, ',', '.') }}</span>
            </div>

            <!-- ── SIGNATURE SECTION ───────────────────────────────────────────── -->
            <div class="pt-4 grid grid-cols-2 gap-8 text-center text-xs font-sans">
                <!-- Pihak Management ISW (Direktur Utama TTE QR) -->
                <div class="flex flex-col justify-between h-44 py-1">
                    <div class="font-bold text-[#000d6b]">
                        Dibuat & Disahkan Oleh,<br>
                        <span class="text-[10px] text-slate-500 font-normal">PT INTI SARANA WIJAYA</span>
                    </div>

                    <div class="my-auto flex flex-col items-center justify-center space-y-1">
                        @php
                            $qrUrl = $kepala ? $kepala->getQrSignatureUrl() : 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=VERIFIKASI+DIGITAL+TTE+PT+ISW';
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
                        <div class="text-[10px] text-slate-600 font-bold">{{ $kepala?->jabatan_kepala ?: 'Direktur Utama PT ISW' }}</div>
                    </div>
                </div>

                <!-- Pihak Pegawai -->
                <div class="flex flex-col justify-between h-44 py-1">
                    <div class="font-bold text-slate-800">
                        Penerima Gaji,<br>
                        <span class="text-[10px] text-slate-500 font-normal">(Pegawai Yang Bersangkutan)</span>
                    </div>

                    <div class="my-auto text-[10px] text-slate-400 italic">
                        ( Tanda Tangan Penerima )
                    </div>

                    <div>
                        <div class="font-black text-slate-900 underline">{{ $pegawai->nama }}</div>
                        <div class="text-[10px] text-slate-600 font-bold">Pegawai PT ISW</div>
                    </div>
                </div>
            </div>

        </div>
    @endforeach

</body>
</html>
