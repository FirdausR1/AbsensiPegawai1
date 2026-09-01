@extends('layouts.app')

@section('title', 'Tanda Tangan & QR Code Digital Kepala ISW - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-[#000d6b] transition">Dashboard</a>
                <span>/</span>
                <a href="{{ route('admin.dashboard') }}" class="hover:text-[#000d6b] transition">Admin</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Verifikasi Kepala ISW</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">Tanda Tangan & QR Code Digital Kepala ISW</h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">
                Kelola data Pimpinan / Direktur Utama PT Inti Sarana Wijaya, tanda tangan digital, dan verifikasi QR Code resmi untuk dokumen & SP.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Form Canvas TTD & Profile Kepala ISW -->
        <div class="lg:col-span-7">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-5">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="section-bar"></span>
                    <h2 class="text-base font-bold text-slate-900">Form Pengesahan Tanda Tangan Digital Pimpinan</h2>
                </div>

                <form method="POST" action="{{ route('admin.kepala.signature.update') }}" enctype="multipart/form-data" id="kepala-ttd-form" class="space-y-4 text-xs">
                    @csrf
                    <input type="hidden" name="signature_data" id="signature_data">

                    <div>
                        <label for="nama" class="block font-semibold text-slate-700 mb-1">Nama Lengkap Kepala / Direktur ISW <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama', $kepala?->nama ?? 'Firdaus Romandhanu') }}" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-extrabold text-sm">
                    </div>

                    <div>
                        <label for="jabatan_kepala" class="block font-semibold text-slate-700 mb-1">Jabatan Resmi <span class="text-rose-500">*</span></label>
                        <input type="text" name="jabatan_kepala" id="jabatan_kepala" value="{{ old('jabatan_kepala', $kepala?->jabatan_kepala ?? 'Direktur Utama PT Inti Sarana Wijaya') }}" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 outline-none text-slate-800 font-bold">
                    </div>

                    <!-- Canvas Digital Signature -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Goreskan Tanda Tangan Digital (Menggunakan Mouse / Touchscreen)
                        </label>
                        <div class="border-2 border-dashed border-indigo-200 rounded-xl bg-slate-50/50 p-2 text-center">
                            <canvas id="signature-pad" width="500" height="180" class="w-full h-44 bg-white rounded-lg border border-slate-200 touch-none shadow-xs"></canvas>
                            <div class="mt-2 flex justify-between items-center px-1">
                                <span class="text-[11px] text-slate-400 font-medium">Gunakan jari / stylus / mouse untuk menggambar tanda tangan</span>
                                <button type="button" id="clear-canvas" class="px-3 py-1 bg-slate-200 hover:bg-slate-300 text-slate-700 text-[11px] font-bold rounded-md">
                                    Bersihkan Canvas
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Atau Upload File Gambar TTD -->
                    <div>
                        <label for="foto_ttd" class="block font-semibold text-slate-700 mb-1">
                            Atau Upload File Gambar TTD (PNG Transparan / JPG)
                        </label>
                        <input type="file" name="foto_ttd" id="foto_ttd" accept="image/*"
                               class="w-full px-3.5 py-2 rounded-lg border border-slate-300 text-xs text-slate-700 bg-white">
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-xs rounded-xl shadow-sm transition">
                            Simpan & Regenerate QR Code Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Preview QR Code & Digital Signature Badge -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3">
                    <span class="section-bar"></span>
                    <h2 class="text-base font-bold text-slate-900">Preview QR Code & Verifikasi TTD Sah</h2>
                </div>

                <div class="p-5 bg-gradient-to-br from-indigo-50/70 via-slate-50 to-white rounded-xl border border-indigo-100 text-center space-y-4">
                    <div class="inline-block px-3 py-1 bg-[#000d6b] text-white text-[10px] font-black uppercase tracking-wider rounded-full">
                        DOKUMEN RESMI PT INTI SARANA WIJAYA
                    </div>

                    <!-- Tanda Tangan Digital Image -->
                    <div class="flex items-center justify-center min-h-[90px]">
                        @if($kepala && $kepala->hasSignature())
                            <img src="{{ $kepala->getSignatureUrl() }}" alt="Signature {{ $kepala->nama }}" class="max-h-24 max-w-[200px] object-contain">
                        @else
                            <span class="text-xs text-slate-400 italic">Belum ada tanda tangan digital tersimpan</span>
                        @endif
                    </div>

                    <!-- Dynamic QR Code -->
                    <div class="flex justify-center pt-2">
                        @if($kepala && $kepala->qr_signature_path)
                            <div class="p-3 bg-white rounded-xl border border-slate-300 shadow-sm text-center space-y-2">
                                <img src="{{ $kepala->qr_signature_path }}" alt="QR Code Verifikasi" class="w-36 h-36 mx-auto">
                                <div class="text-[10px] font-mono text-slate-500 font-bold">QR CODE DOKUMEN RESMI</div>
                            </div>
                        @else
                            <div class="w-32 h-32 bg-slate-100 border-2 border-dashed border-slate-300 rounded-xl flex items-center justify-center text-[10px] text-slate-400 font-bold">
                                QR Code Otomatis
                            </div>
                        @endif
                    </div>

                    <div class="space-y-1 pt-2 border-t border-slate-200/60 text-xs">
                        <div id="preview-nama" class="font-extrabold text-[#000d6b] text-sm">{{ $kepala?->nama ?? 'Firdaus Romandhanu' }}</div>
                        <div id="preview-jabatan" class="text-slate-600 text-xs font-semibold">{{ $kepala?->jabatan_kepala ?? 'Direktur Utama PT Inti Sarana Wijaya' }}</div>
                        @if($kepala && $kepala->signature_updated_at)
                            <div class="text-[10px] text-slate-400 pt-1">
                                Waktu Pengesahan: <strong>{{ $kepala->signature_updated_at->translatedFormat('d F Y, H:i') }} WIB</strong>
                            </div>
                        @endif
                    </div>
                </div>

                @if($kepala && $kepala->qr_payload)
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 font-mono text-[10px] text-slate-700 whitespace-pre-line leading-relaxed">
                        {{ $kepala->qr_payload }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('signature-pad');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let hasDrawn = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasDrawn = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            ctx.strokeStyle = '#000d6b';
            ctx.lineWidth = 3;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
        }

        function stopDrawing() {
            if (isDrawing) {
                ctx.closePath();
                isDrawing = false;
            }
        }

        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        canvas.addEventListener('touchstart', startDrawing, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDrawing);

        document.getElementById('clear-canvas')?.addEventListener('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasDrawn = false;
            document.getElementById('signature_data').value = '';
        });

        document.getElementById('kepala-ttd-form')?.addEventListener('submit', function() {
            if (hasDrawn) {
                document.getElementById('signature_data').value = canvas.toDataURL('image/png');
            }
        });
        const namaInput = document.getElementById('nama');
        const jabatanInput = document.getElementById('jabatan_kepala');
        const previewNama = document.getElementById('preview-nama');
        const previewJabatan = document.getElementById('preview-jabatan');

        if (namaInput && previewNama) {
            namaInput.addEventListener('input', function() {
                previewNama.textContent = this.value || 'Nama Pimpinan';
            });
        }
        if (jabatanInput && previewJabatan) {
            jabatanInput.addEventListener('input', function() {
                previewJabatan.textContent = this.value || 'Direktur Utama PT ISW';
            });
        }
    });
</script>
@endsection
