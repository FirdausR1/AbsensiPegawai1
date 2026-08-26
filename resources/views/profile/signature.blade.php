@extends('layouts.app')

@section('title', 'Tanda Tangan Digital - Absensi Pegawai')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Dashboard
                </a>
                <span>/</span>
                <span class="text-slate-700">Tanda Tangan Digital</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">✍️ Tanda Tangan Digital</h1>
        </div>

        <!-- Prominent Back Button -->
        <a href="{{ route('dashboard') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-xl transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- Current Signature Preview (if exists) -->
    @if($pegawai->hasSignature())
        <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Tanda Tangan Tersimpan Saat Ini</h3>
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span>✓</span> Aktif
                </span>
            </div>
            <div class="bg-slate-50 border border-dashed border-slate-300 rounded-2xl p-4 flex items-center justify-center min-h-[120px]">
                <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="Tanda Tangan {{ $pegawai->nama }}" class="max-h-24 max-w-full object-contain">
            </div>
            <p class="text-xs text-slate-400 mt-2 text-center">
                Terakhir diperbarui: {{ $pegawai->signature_updated_at ? $pegawai->signature_updated_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
            </p>
        </div>
    @endif

    <!-- Drawing Pad Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div>
            <h2 class="text-base font-bold text-slate-800">
                {{ $pegawai->hasSignature() ? 'Buat / Ganti Tanda Tangan' : 'Buat Tanda Tangan Baru' }}
            </h2>
            <p class="text-xs text-slate-500 mt-1">
                Gunakan jari (di HP/Tablet) atau mouse (di Laptop/PC) untuk menandatangani area kanvas putih di bawah ini.
            </p>
        </div>

        <div class="border-2 border-dashed border-slate-300 rounded-2xl p-2 bg-slate-50 relative overflow-hidden">
            <canvas id="signature-pad" class="bg-white rounded-xl w-full touch-none cursor-crosshair border border-slate-200 shadow-inner" height="220"></canvas>
            <div class="absolute bottom-4 right-4 pointer-events-none text-[11px] text-slate-400 font-medium select-none">
                Area Tanda Tangan
            </div>
        </div>

        <!-- Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
            <button id="clear-btn" type="button"
                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-xs font-bold text-slate-700 transition flex items-center justify-center gap-1.5 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Kanvas
            </button>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <a href="{{ route('dashboard') }}"
                   class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-xs font-bold text-slate-600 transition">
                    Batal
                </a>
                <button id="save-btn" type="button"
                        class="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-md shadow-indigo-200 transition flex items-center justify-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Simpan Tanda Tangan
                </button>
            </div>
        </div>
    </div>
</div>

<form id="signature-form" method="POST" action="{{ route('profile.signature.save') }}" class="hidden">
    @csrf
    <input type="hidden" name="signature_data" id="signature_data">
</form>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = 220 * ratio;
        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255,255,255)',
        penColor: 'rgb(20, 20, 30)',
        minWidth: 1.5,
        maxWidth: 3.5
    });

    document.getElementById('clear-btn').addEventListener('click', () => {
        signaturePad.clear();
    });

    document.getElementById('save-btn').addEventListener('click', () => {
        if (signaturePad.isEmpty()) {
            alert('Silakan tanda tangan terlebih dahulu pada kanvas sebelum menyimpan.');
            return;
        }
        document.getElementById('signature_data').value = signaturePad.toDataURL('image/png');
        document.getElementById('signature-form').submit();
    });
</script>
@endsection
