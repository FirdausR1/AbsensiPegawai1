<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PT Inti Sarana Wijaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="{{ asset('images/Picture1.png') }}">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-[#f0f2f5] min-h-screen flex items-center justify-center p-4 antialiased">

    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="text-center mb-6">
            <img src="{{ asset('images/Picture1.png') }}" alt="ISW" class="w-12 h-12 rounded-lg object-contain mx-auto mb-3">
            <h1 class="text-lg font-bold text-[#000d6b]">PT Inti Sarana Wijaya</h1>
            <p class="text-xs text-slate-500 mt-1">Sistem Presensi Digital</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-lg border border-slate-200 p-6">
            <h2 class="text-sm font-bold text-slate-800 mb-4">Masuk ke akun Anda</h2>

            @if ($errors->any())
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-medium text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                           placeholder="nama@email.com">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="password" class="block text-xs font-medium text-slate-600">Password</label>
                        <a href="#" onclick="alert('Hubungi Admin/IT Support untuk reset password.'); return false;" class="text-[11px] text-[#000d6b] hover:underline">Lupa password?</a>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="password" required
                               class="w-full px-3 py-2 rounded-md border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none text-sm text-slate-800 placeholder:text-slate-400 transition"
                               placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center text-xs text-slate-500 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded border-slate-300 text-[#000d6b] focus:ring-[#000d6b] mr-2">
                    Ingat saya
                </label>

                <button type="submit"
                        class="w-full py-2.5 bg-[#000d6b] hover:bg-[#001253] text-white font-semibold text-sm rounded-md transition">
                    Masuk
                </button>
            </form>

            <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                Pegawai baru? <a href="{{ route('register') }}" class="font-semibold text-[#000d6b] hover:underline">Daftar Akun</a>
            </div>
        </div>

        <p class="text-center text-[11px] text-slate-400 mt-4">&copy; {{ date('Y') }} PT Inti Sarana Wijaya</p>
    </div>

    <script>
        function togglePassword() {
            const p = document.getElementById('password');
            p.type = p.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
