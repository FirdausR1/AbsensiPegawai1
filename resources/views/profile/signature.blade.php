@extends('layouts.app')

@section('title', 'Digital Signature - PT Inti Sarana Wijaya')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header with Breadcrumbs -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('profile.edit') }}" class="hover:text-[#000d6b] transition">Profile</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Digital Signature</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Digital Signature Verification</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Official signature used for automated attendance authentication and report exports.
            </p>
        </div>

        <a href="{{ route('profile.edit') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 rounded-lg transition shadow-sm">
            &larr; Back to Profile
        </a>
    </div>

    <!-- Current Signature Preview (if exists) -->
    @if($pegawai->hasSignature())
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <span class="section-bar"></span>
                    <h3 class="text-sm font-bold text-slate-900">Current Verified Signature</h3>
                </div>
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span>✓</span> Verified Active
                </span>
            </div>
            
            <div class="bg-slate-50 border border-dashed border-slate-300 rounded-xl p-6 flex items-center justify-center min-h-[140px]">
                <img src="{{ asset('storage/' . $pegawai->signature_path) }}" alt="Signature {{ $pegawai->nama }}" class="max-h-24 max-w-full object-contain">
            </div>
            
            <p class="text-[11px] text-slate-400 mt-3 text-center">
                Last updated on: {{ $pegawai->signature_updated_at ? $pegawai->signature_updated_at->translatedFormat('d F Y, H:i') . ' WIB' : '-' }}
            </p>
        </div>
    @endif

    <!-- Drawing Pad Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center gap-2 mb-1">
            <span class="section-bar"></span>
            <h2 class="text-sm font-bold text-slate-900">
                {{ $pegawai->hasSignature() ? 'Update Digital Signature' : 'Create Official Digital Signature' }}
            </h2>
        </div>
        <p class="text-xs text-slate-500">
            Sign inside the white canvas box below using your mouse, trackpad, or touchscreen finger/stylus.
        </p>

        <!-- Canvas Box -->
        <div class="border-2 border-dashed border-slate-300 hover:border-[#000d6b] transition rounded-xl p-2 bg-slate-50">
            <div class="relative bg-white rounded-lg shadow-inner flex justify-center items-center overflow-hidden">
                <canvas id="signature-pad" class="w-full h-56 touch-none cursor-crosshair block"></canvas>
                <div id="signature-placeholder" class="absolute pointer-events-none text-slate-300 text-xs font-medium tracking-wide flex items-center gap-1.5">
                    <span>✍️</span> Sign here
                </div>
            </div>
        </div>

        <!-- Action Controls -->
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
            <button type="button" id="clear-btn"
                    class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition tracking-wide flex items-center justify-center gap-1.5">
                <span>🔄</span> Clear & Redraw
            </button>

            <form id="signature-form" method="POST" action="{{ route('profile.signature.save') }}" class="w-full sm:w-auto">
                @csrf
                <input type="hidden" name="signature_data" id="signature_data">
                <button type="submit" id="save-btn"
                        class="w-full sm:w-auto px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition tracking-wide flex items-center justify-center gap-1.5">
                    <span>💾</span> Save Digital Signature
                </button>
            </form>
        </div>
    </div>
</div>

<!-- SignaturePad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const canvas = document.getElementById('signature-pad');
        const placeholder = document.getElementById('signature-placeholder');
        const clearBtn = document.getElementById('clear-btn');
        const form = document.getElementById('signature-form');
        const inputData = document.getElementById('signature_data');

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
        }

        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgba(255, 255, 255, 0)',
            penColor: '#0f172a',
            minWidth: 1.5,
            maxWidth: 3.5,
        });

        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        signaturePad.addEventListener('beginStroke', () => {
            if (placeholder) placeholder.style.display = 'none';
        });

        clearBtn.addEventListener('click', () => {
            signaturePad.clear();
            if (placeholder) placeholder.style.display = 'flex';
        });

        form.addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert('Silakan tanda tangan terlebih dahulu pada kotak sebelum menyimpan.');
                return false;
            }
            inputData.value = signaturePad.toDataURL('image/png');
        });
    });
</script>
@endsection
