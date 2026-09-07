<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - PT Inti Sarana Wijaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/Picture1.png') }}">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#f0f2f5] min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-6">
            <img src="{{ asset('images/Picture1.png') }}" alt="ISW" class="w-12 h-12 rounded-lg object-contain mx-auto mb-3">
            <h1 class="text-lg font-bold text-[#000d6b]">PT Inti Sarana Wijaya</h1>
            <p class="text-xs text-slate-500 mt-1">Registrasi Akun Pegawai</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h2 class="text-sm font-bold text-slate-800 mb-4">Buat Akun Baru</h2>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="nama" class="block text-xs font-medium text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required autofocus
                           class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                           placeholder="Contoh: Budi Santoso">
                </div>

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                           placeholder="nama@email.com">
                </div>

                <div>
                    <label for="area_kerja" class="block text-xs font-medium text-slate-600 mb-1">Divisi / Area Kerja</label>
                    <input type="text" name="area_kerja" id="area_kerja" value="{{ old('area_kerja') }}"
                           class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                           placeholder="Contoh: Satpam / IT / Operasional">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="password" class="block text-xs font-medium text-slate-600 mb-1">Password</label>
                        <input type="password" name="password" id="password" required
                               class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                               placeholder="Min. 8 karakter">
                    </div>
                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-slate-600 mb-1">Ulangi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                               class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                               placeholder="Ulangi password">
                    </div>
                </div>

                <button type="submit"
                        class="w-full py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-semibold text-sm rounded-md transition">
                    Daftar
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                Sudah punya akun? <a href="{{ route('login') }}" class="font-semibold text-[#000d6b] hover:underline">Masuk</a>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-400 mt-4">&copy; {{ date('Y') }} PT Inti Sarana Wijaya</p>
    </div>
</body>
</html>
