<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - PT Inti Sarana Wijaya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        isw: {
                            navy: '#000d6b',
                            dark: '#001253',
                            accent: '#1e3a8a',
                            light: '#eef2ff',
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; }
    </style>
</head>
<body class="bg-white min-h-screen flex antialiased">
    <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 min-h-screen">
        
        <!-- Left Side: Login Form -->
        <div class="lg:col-span-5 xl:col-span-5 flex flex-col justify-between p-8 sm:p-12 lg:p-16 bg-white z-10">
            <!-- Top Branding -->
            <div>
                <div class="w-12 h-12 bg-[#000d6b] rounded-xl flex items-center justify-center text-white shadow-md shadow-indigo-950/20 mb-8">
                    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 2H9c-1.1 0-2 .9-2 2v1.5H5c-1.1 0-2 .9-2 2V20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM5 7.5h2V10H5V7.5zm0 4.5h2v2.5H5V12zm0 4.5h2V19H5v-2.5zm14 3.5H9V4h10v15zm-8-13h2V8h-2V6.5zm0 3.5h2v1.5h-2V10zm0 3.5h2V15h-2v-1.5zm0 3.5h2v1.5h-2V17zm4-10.5h2V8h-2V6.5zm0 3.5h2v1.5h-2V10zm0 3.5h2V15h-2v-1.5zm0 3.5h2v1.5h-2V17z"/>
                    </svg>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#000d6b] tracking-tight">
                    PT Inti Sarana Wijaya
                </h1>
                <p class="text-sm text-slate-500 mt-2">
                    Welcome back. Please sign in to your attendance portal.
                </p>

                <!-- Errors / Alerts -->
                @if ($errors->any())
                    <div class="mt-6 p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <span>⚠️</span>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if (session('success'))
                    <div class="mt-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl">
                        ✓ {{ session('success') }}
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                    @csrf
                    
                    <!-- Email / Employee ID -->
                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                            Employee ID or Email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                   class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                                   placeholder="Enter your ID or Email">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-slate-700">
                                Password
                            </label>
                            <a href="#" onclick="alert('Silakan hubungi IT Support / Admin untuk reset password.'); return false;" class="text-xs font-medium text-[#000d6b] hover:underline">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0110 0v4"></path>
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" required
                                   class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                                   placeholder="Enter your password">
                            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                                <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <label class="flex items-center text-xs text-slate-600 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-[#000d6b] focus:ring-[#000d6b] mr-2.5">
                            Remember me for 30 days
                        </label>
                    </div>

                    <!-- Sign In Button -->
                    <button type="submit"
                            class="w-full py-3 px-4 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-sm rounded-lg shadow-md shadow-indigo-950/20 transition duration-150 tracking-wide flex items-center justify-center gap-2">
                        Sign In
                    </button>
                </form>

                <div class="mt-6 text-center text-xs text-slate-500">
                    Need help accessing your account? <a href="#" onclick="alert('Silakan hubungi IT Administrator di admin@perusahaan.com'); return false;" class="font-semibold text-[#000d6b] hover:underline">Contact IT Support</a>.
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 text-center text-xs text-slate-500">
                    New Employee? <a href="{{ route('register') }}" class="font-semibold text-[#000d6b] hover:underline">Register New Account</a>
                </div>
            </div>

            <!-- Bottom Copyright -->
            <div class="pt-8 text-xs text-slate-400 text-center lg:text-left">
                &copy; {{ date('Y') }} PT Inti Sarana Wijaya. All rights reserved.
            </div>
        </div>

        <!-- Right Side: High-res Office Atmosphere Background -->
        <div class="hidden lg:block lg:col-span-7 xl:col-span-7 relative bg-slate-900 overflow-hidden">
            <img src="{{ asset('images/office-bg.jpg') }}"
                 alt="ISW Corporate Office"
                 class="w-full h-full object-cover object-center scale-105 transition duration-700 hover:scale-100">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/60 via-transparent to-black/20"></div>

            <!-- Floating Badge on Office Photo -->
            <div class="absolute bottom-12 left-12 right-12 text-white bg-slate-950/40 backdrop-blur-md p-6 rounded-2xl border border-white/20">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                    <span class="text-xs uppercase tracking-widest text-emerald-300 font-bold">ISW Attendance Portal</span>
                </div>
                <h2 class="text-lg font-bold text-white">Smart Corporate Attendance & Management</h2>
                <p class="text-xs text-slate-200 mt-1">Seamless digital presence, real-time reporting, and verified signatures.</p>
            </div>
        </div>

    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
    </script>
</body>
</html>
