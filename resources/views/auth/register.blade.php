<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Registration - PT Inti Sarana Wijaya</title>
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
        
        <!-- Left Side: Registration Form -->
        <div class="lg:col-span-6 xl:col-span-5 flex flex-col justify-between p-8 sm:p-12 bg-white z-10 overflow-y-auto">
            <div>
                <!-- Top Branding -->
                <div class="w-12 h-12 bg-[#000d6b] rounded-xl flex items-center justify-center text-white shadow-md shadow-indigo-950/20 mb-6">
                    <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 2H9c-1.1 0-2 .9-2 2v1.5H5c-1.1 0-2 .9-2 2V20c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM5 7.5h2V10H5V7.5zm0 4.5h2v2.5H5V12zm0 4.5h2V19H5v-2.5zm14 3.5H9V4h10v15zm-8-13h2V8h-2V6.5zm0 3.5h2v1.5h-2V10zm0 3.5h2V15h-2v-1.5zm0 3.5h2v1.5h-2V17zm4-10.5h2V8h-2V6.5zm0 3.5h2v1.5h-2V10zm0 3.5h2V15h-2v-1.5zm0 3.5h2v1.5h-2V17z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-extrabold text-[#000d6b] tracking-tight">
                    New Employee Registration
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Create an account to access the PT Inti Sarana Wijaya attendance system.
                </p>

                <!-- Errors / Alerts -->
                @if ($errors->any())
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <span>⚠️</span>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Register Form -->
                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                    @csrf
                    
                    <div>
                        <label for="nama" class="block text-xs font-semibold text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required autofocus
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                               placeholder="e.g. Budi Santoso">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Work Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                               placeholder="budi.santoso@isw.co.id">
                    </div>

                    <div>
                        <label for="area_kerja" class="block text-xs font-semibold text-slate-700 mb-1">Department / Division</label>
                        <input type="text" name="area_kerja" id="area_kerja" value="{{ old('area_kerja') }}"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                               placeholder="e.g. Information Technology / Operasional">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                            <input type="password" name="password" id="password" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                                   placeholder="Min. 8 characters">
                        </div>
                        <div>
                            <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Confirm Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-[#000d6b] focus:border-[#000d6b] transition outline-none text-slate-800 text-sm placeholder:text-slate-400"
                                   placeholder="Repeat password">
                        </div>
                    </div>

                    <button type="submit"
                            class="w-full py-3 px-4 bg-[#000d6b] hover:bg-[#001253] text-white font-bold text-sm rounded-lg shadow-md shadow-indigo-950/20 transition duration-150 tracking-wide mt-2">
                        Create Account
                    </button>
                </form>

                <div class="mt-6 text-center text-xs text-slate-500">
                    Already have an account? <a href="{{ route('login') }}" class="font-semibold text-[#000d6b] hover:underline">Sign In</a>
                </div>
            </div>

            <div class="pt-6 text-xs text-slate-400 text-center lg:text-left">
                &copy; {{ date('Y') }} PT Inti Sarana Wijaya
            </div>
        </div>

        <!-- Right Side: Office Workspace Image -->
        <div class="hidden lg:block lg:col-span-6 xl:col-span-7 relative bg-slate-900 overflow-hidden">
            <img src="{{ asset('images/office-bg.jpg') }}"
                 alt="ISW Corporate Office"
                 class="w-full h-full object-cover object-center scale-105">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-black/30"></div>

            <div class="absolute bottom-12 left-12 right-12 text-white bg-slate-950/40 backdrop-blur-md p-6 rounded-2xl border border-white/20">
                <div class="flex items-center gap-3 mb-2">
                    <span class="text-xs uppercase tracking-widest text-emerald-300 font-bold">ISW Attendance</span>
                </div>
                <h2 class="text-lg font-bold text-white">Join the Smart Attendance Portal</h2>
                <p class="text-xs text-slate-200 mt-1">Register once, verify your digital signature, and manage your attendance seamlessly.</p>
            </div>
        </div>

    </div>
</body>
</html>
