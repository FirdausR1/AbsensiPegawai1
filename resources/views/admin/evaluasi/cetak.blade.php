<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Evaluasi Kinerja - {{ $pegawai->nama }}</title>

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
    <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="{{ route('admin.evaluasi-kinerja.index') }}" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-bold rounded-xl transition">
            &larr; Kembali ke Evaluasi Kinerja
        </a>
        <button onclick="window.print()" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak Laporan Evaluasi Kinerja (Print / PDF)
        </button>
    </div>

    <!-- ── HALAMAN 1: FORMULIR EVALUASI KINERJA ────────────────────────── -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-md border border-slate-200 rounded-2xl space-y-5 page-break">

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

        <!-- ── JUDUL LAPORAN ─────────────────────────────────────────────── -->
        <div class="text-center space-y-0.5">
            <h2 class="text-base font-black text-[#000d6b] underline tracking-wider uppercase">LAPORAN EVALUASI KINERJA PERIODIK PEGAWAI</h2>
            <div class="text-xs font-bold text-slate-700">
                Periode: {{ $evaluasi->tanggal_mulai->translatedFormat('d F Y') }} s/d {{ $evaluasi->tanggal_selesai->translatedFormat('d F Y') }} ({{ str_replace('_', ' ', strtoupper($evaluasi->periode_tipe)) }})
            </div>
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
                <td class="py-1 font-semibold text-slate-600">NIK (KTP)</td>
                <td class="py-1 text-slate-800">: {{ $pegawai->nik ?: '-' }}</td>
                <td class="py-1 font-semibold text-slate-600">Divisi / Jabatan</td>
                <td class="py-1 text-slate-800">: {{ $pegawai->divisi?->nama ?: 'Staff Operasional' }}</td>
            </tr>
            <tr>
                <td class="py-1 font-semibold text-slate-600">Tempat, Tgl Lahir</td>
                <td class="py-1 text-slate-800">: {{ $pegawai->tempat_lahir ? $pegawai->tempat_lahir.', ' : '' }}{{ $pegawai->tanggal_lahir ? $pegawai->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td>
                <td class="py-1 font-semibold text-slate-600">Status Karyawan</td>
                <td class="py-1 text-slate-800">: {{ $pegawai->status_karyawan == 'internal' ? 'Internal PT ISW' : 'Outsourcing Site Project' }}</td>
            </tr>
        </table>

        <!-- ── TABEL HASIL EVALUASI KINERJA ────────────────────────────────── -->
        <table class="w-full border-collapse border border-slate-800 text-xs font-sans mb-3">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-800 text-slate-900">
                    <th class="border-r border-slate-800 py-2 px-3 text-left w-12 font-bold">NO</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-left font-bold">ASPEK / PILAR PENILAIAN</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-center w-24 font-bold">BOBOT</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-center w-28 font-bold">SKOR (0-100)</th>
                    <th class="py-2 px-3 text-center w-32 font-bold">NILAI TERTIMBANG</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-300">
                <tr>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-bold">1</td>
                    <td class="border-r border-slate-800 py-2.5 px-3">
                        <div class="font-bold text-slate-900">Kedisiplinan & Presensi Kehadiran</div>
                        <div class="text-[10px] text-slate-500">Tepat waktu, kehadiran, keterlambatan, dan izin (Lampiran 1)</div>
                    </td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-semibold">40%</td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-bold font-mono">{{ number_format($evaluasi->skor_absensi, 1) }}</td>
                    <td class="py-2.5 px-3 text-center font-bold font-mono text-slate-900">{{ number_format($evaluasi->skor_absensi * 0.40, 2) }}</td>
                </tr>
                <tr>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-bold">2</td>
                    <td class="border-r border-slate-800 py-2.5 px-3">
                        <div class="font-bold text-slate-900">Penyelesaian Tugas Periodik & Operasional</div>
                        <div class="text-[10px] text-slate-500">Kualitas & kelengkapan upload laporan tugas periodik (Lampiran 2)</div>
                    </td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-semibold">40%</td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-bold font-mono">{{ number_format($evaluasi->skor_tugas, 1) }}</td>
                    <td class="py-2.5 px-3 text-center font-bold font-mono text-slate-900">{{ number_format($evaluasi->skor_tugas * 0.40, 2) }}</td>
                </tr>
                <tr>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-bold">3</td>
                    <td class="border-r border-slate-800 py-2.5 px-3">
                        <div class="font-bold text-slate-900">Sikap, Perilaku & Integritas Kerja (Softskills)</div>
                        <div class="text-[10px] text-slate-500">Kerjasama tim, komunikasi, kerapian seragam & kepatuhan SOP</div>
                    </td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-semibold">20%</td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-bold font-mono">{{ number_format($evaluasi->skor_perilaku, 1) }}</td>
                    <td class="py-2.5 px-3 text-center font-bold font-mono text-slate-900">{{ number_format($evaluasi->skor_perilaku * 0.20, 2) }}</td>
                </tr>
                <tr class="bg-slate-100 font-extrabold border-t-2 border-slate-800 text-slate-950">
                    <td colspan="3" class="border-r border-slate-800 py-2.5 px-3 text-right uppercase">TOTAL SKOR AKHIR & KATEGORI (GRADE)</td>
                    <td class="border-r border-slate-800 py-2.5 px-3 text-center font-mono text-sm text-[#000d6b]">{{ number_format($evaluasi->skor_akhir, 1) }}</td>
                    <td class="py-2.5 px-3 text-center font-mono text-sm text-[#000d6b] uppercase">{{ $evaluasi->kategori_penilaian }}</td>
                </tr>
            </tbody>
        </table>

        <!-- ── REKOMENDASI & CATATAN EVALUASI ──────────────────────────────── -->
        <div class="border border-slate-800 p-3 space-y-2 text-xs font-sans bg-slate-50">
            <div>
                <span class="font-bold text-slate-700">REKOMENDASI MANAJEMEN:</span>
                <div class="font-extrabold text-[#000d6b] text-sm underline mt-0.5">{{ $evaluasi->rekomendasi }}</div>
            </div>
            <div>
                <span class="font-bold text-slate-700">CATATAN & FEEDBACK EVALUATOR:</span>
                <p class="text-slate-800 italic mt-0.5 leading-relaxed">{{ $evaluasi->catatan_evaluasi ?: 'Pegawai yang bersangkutan telah menjalankan kewajiban tugas dengan baik.' }}</p>
            </div>
        </div>

        <!-- ── SIGNATURE SECTION ───────────────────────────────────────────── -->
        <div class="pt-4 grid grid-cols-2 gap-8 text-center text-xs font-sans">
            <div class="flex flex-col justify-between h-44 py-1">
                <div class="font-bold text-slate-800">
                    Evaluator / Atasan Langsung,<br>
                    <span class="text-[10px] text-slate-500 font-normal">Management Operasional ISW</span>
                </div>
                <div class="my-auto text-[10px] text-slate-400 italic">( Tanda Tangan Evaluator )</div>
                <div>
                    <div class="font-black text-slate-900 underline">{{ $evaluasi->evaluator?->nama ?: 'Admin Management' }}</div>
                    <div class="text-[10px] text-slate-600 font-bold">Evaluator PT ISW</div>
                </div>
            </div>

            <div class="flex flex-col justify-between h-44 py-1">
                <div class="font-bold text-[#000d6b]">
                    Disahkan Oleh,<br>
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
        </div>

    </div>

    <!-- ── HALAMAN 2: LAMPIRAN 1 - REKAP PRESENSI ABSENSI ─────────────── -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-md border border-slate-200 rounded-2xl space-y-4 page-break mt-8">
        <div class="border-b-2 border-slate-800 pb-2 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 uppercase">LAMPIRAN 1: REKAPITULASI PRESENSI ABSENSI PEGAWAI</h3>
            <span class="text-xs font-mono font-bold text-slate-600">{{ $pegawai->nama }} ({{ $pegawai->area_kerja ?: 'HO ISW' }})</span>
        </div>

        <p class="text-xs text-slate-600">
            Rincian kehadiran presensi pegawai selama periode evaluasi ({{ $evaluasi->tanggal_mulai->format('d/m/Y') }} s/d {{ $evaluasi->tanggal_selesai->format('d/m/Y') }}):
        </p>

        <table class="w-full border-collapse border border-slate-800 text-xs font-sans">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-800 text-slate-900 font-bold">
                    <th class="border-r border-slate-800 py-2 px-3 text-left w-10">NO</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-left">TANGGAL</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-center">JAM MASUK</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-center">JAM PULANG</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-center">STATUS</th>
                    <th class="py-2 px-3 text-center">KETERANGAN / TERLAMBAT</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-300">
                @forelse($absensis as $index => $abs)
                    <tr>
                        <td class="border-r border-slate-800 py-1.5 px-3 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border-r border-slate-800 py-1.5 px-3 font-mono">{{ \Carbon\Carbon::parse($abs->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="border-r border-slate-800 py-1.5 px-3 text-center font-mono">{{ $abs->jam_masuk ? substr($abs->jam_masuk, 0, 5) : '-' }}</td>
                        <td class="border-r border-slate-800 py-1.5 px-3 text-center font-mono">{{ $abs->jam_keluar ? substr($abs->jam_keluar, 0, 5) : '-' }}</td>
                        <td class="border-r border-slate-800 py-1.5 px-3 text-center font-bold uppercase {{ $abs->status == 'terlambat' ? 'text-rose-700' : 'text-slate-900' }}">
                            {{ $abs->status }}
                        </td>
                        <td class="py-1.5 px-3 text-center text-slate-700">
                            @if($abs->terlambat_menit > 0)
                                <span class="font-bold text-rose-700">Terlambat {{ $abs->terlambat_menit }} Menit</span>
                            @else
                                Tepat Waktu
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-4 text-center text-slate-400 italic">Tidak ada catatan presensi pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- ── HALAMAN 3: LAMPIRAN 2 - REKAP TUGAS PERIODIK OPERASIONAL ──────── -->
    <div class="max-w-4xl mx-auto bg-white p-8 sm:p-12 shadow-md border border-slate-200 rounded-2xl space-y-4 mt-8">
        <div class="border-b-2 border-slate-800 pb-2 flex items-center justify-between">
            <h3 class="text-sm font-black text-slate-900 uppercase">LAMPIRAN 2: REKAPITULASI HASIL TUGAS PERIODIK OPERASIONAL</h3>
            <span class="text-xs font-mono font-bold text-slate-600">{{ $pegawai->nama }} ({{ $pegawai->area_kerja ?: 'HO ISW' }})</span>
        </div>

        <p class="text-xs text-slate-600">
            Rincian laporan penyelesaian tugas operasional pegawai selama periode evaluasi ({{ $evaluasi->tanggal_mulai->format('d/m/Y') }} s/d {{ $evaluasi->tanggal_selesai->format('d/m/Y') }}):
        </p>

        <table class="w-full border-collapse border border-slate-800 text-xs font-sans">
            <thead>
                <tr class="bg-slate-100 border-b border-slate-800 text-slate-900 font-bold">
                    <th class="border-r border-slate-800 py-2 px-3 text-left w-10">NO</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-left">TANGGAL & TIPE</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-left">NAMA TUGAS & LAPORAN</th>
                    <th class="border-r border-slate-800 py-2 px-3 text-center">NILAI / GRADING</th>
                    <th class="py-2 px-3 text-left">FEEDBACK SUPERVISOR</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-300">
                @forelse($tugasList as $index => $tgs)
                    <tr>
                        <td class="border-r border-slate-800 py-2 px-3 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="border-r border-slate-800 py-2 px-3 font-mono">
                            <div>{{ $tgs->tanggal ? $tgs->tanggal->format('d/m/Y') : '-' }}</div>
                            <span class="text-[10px] text-slate-500 font-sans uppercase">{{ $tgs->tipe_tugas }}</span>
                        </td>
                        <td class="border-r border-slate-800 py-2 px-3">
                            <div class="font-bold text-slate-900">{{ $tgs->nama_tugas }}</div>
                            <div class="text-[10px] text-slate-600 line-clamp-1">{{ $tgs->deskripsi }}</div>
                        </td>
                        <td class="border-r border-slate-800 py-2 px-3 text-center font-bold">
                            {{ $tgs->nilai ?: 'Belum Dinilai' }}
                        </td>
                        <td class="py-2 px-3 text-slate-700 italic">
                            {{ $tgs->feedback_supervisor ?: '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-slate-400 italic">Tidak ada catatan laporan tugas periodik pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</body>
</html>
