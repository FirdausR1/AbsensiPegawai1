<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Absensi Pegawai')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-indigo-600 font-bold text-lg hover:text-indigo-700 transition">
                        <div class="w-9 h-9 bg-indigo-600 text-white rounded-xl flex items-center justify-center shadow-sm font-bold text-lg">
                            📋
                        </div>
                        <span class="text-slate-900 tracking-tight font-extrabold">AbsensiApp</span>
                    </a>

                    <!-- Nav Links -->
                    @auth
                        <div class="hidden md:flex items-center space-x-1 ml-6 pl-6 border-l border-slate-200 text-sm font-medium">
                            <a href="{{ route('dashboard') }}"
                               class="px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('absen.riwayat') }}"
                               class="px-3 py-2 rounded-lg transition {{ request()->routeIs('absen.riwayat') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                📅 Hasil / Riwayat Absen
                            </a>
                            <a href="{{ route('profile.edit') }}"
                               class="px-3 py-2 rounded-lg transition {{ request()->routeIs('profile.edit') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                👤 Profil Saya
                            </a>
                            <a href="{{ route('profile.signature') }}"
                               class="px-3 py-2 rounded-lg transition {{ request()->routeIs('profile.signature') ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                ✍️ Tanda Tangan
                            </a>

                            @if(auth()->user()->is_admin)
                                <div class="h-4 w-px bg-slate-300 mx-2"></div>
                                <a href="{{ route('admin.pegawai.index') }}"
                                   class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.pegawai.*') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    👥 Kelola Pegawai
                                </a>
                                <a href="{{ route('admin.absensi.index') }}"
                                   class="px-3 py-2 rounded-lg transition {{ request()->routeIs('admin.absensi.*') ? 'bg-indigo-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                    📊 Rekap Seluruh Pegawai
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>

                <!-- User Profile & Logout -->
                <div class="flex items-center space-x-3">
                    @auth
                        <div class="flex items-center space-x-3">
                            <a href="{{ route('profile.edit') }}" class="hidden sm:flex flex-col text-right hover:opacity-80 transition group">
                                <span class="text-xs font-semibold text-slate-800 group-hover:text-indigo-600 flex items-center justify-end gap-1">
                                    {{ auth()->user()->nama }}
                                    <svg class="w-3 h-3 text-slate-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </span>
                                <span class="text-[11px] text-slate-500">
                                    @if(auth()->user()->is_admin)
                                        <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-100 text-amber-800">ADMIN</span>
                                    @else
                                        {{ auth()->user()->area_kerja ?: 'Pegawai' }}
                                    @endif
                                </span>
                            </a>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" title="Keluar"
                                        class="px-3 py-1.5 text-xs font-medium text-rose-600 hover:text-white bg-rose-50 hover:bg-rose-600 rounded-lg border border-rose-200 hover:border-rose-600 transition flex items-center gap-1.5 shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">Masuk</a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Mobile Navigation for Admin / Auth -->
        @auth
            <div class="md:hidden border-t border-slate-100 bg-slate-50 px-4 py-2 flex flex-wrap gap-2 text-xs">
                <a href="{{ route('dashboard') }}" class="px-2.5 py-1.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border' }}">
                    Dashboard
                </a>
                <a href="{{ route('absen.riwayat') }}" class="px-2.5 py-1.5 rounded-lg {{ request()->routeIs('absen.riwayat') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border' }}">
                    📅 Riwayat
                </a>
                <a href="{{ route('profile.edit') }}" class="px-2.5 py-1.5 rounded-lg {{ request()->routeIs('profile.edit') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border' }}">
                    👤 Profil Saya
                </a>
                <a href="{{ route('profile.signature') }}" class="px-2.5 py-1.5 rounded-lg {{ request()->routeIs('profile.signature') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border' }}">
                    ✍️ TTD
                </a>
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.pegawai.index') }}" class="px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.pegawai.*') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border' }}">
                        👥 Pegawai
                    </a>
                    <a href="{{ route('admin.absensi.index') }}" class="px-2.5 py-1.5 rounded-lg {{ request()->routeIs('admin.absensi.*') ? 'bg-indigo-600 text-white' : 'bg-white text-slate-700 border' }}">
                        📊 Rekap
                    </a>
                @endif
            </div>
        @endauth
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mb-6 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm">
                <span class="text-xl">✅</span>
                <div class="flex-1 text-sm font-medium pt-0.5">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm">
                <span class="text-xl">⚠️</span>
                <div class="flex-1 text-sm font-medium pt-0.5">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('info'))
            <div class="mb-6 flex items-start gap-3 p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-2xl shadow-sm">
                <span class="text-xl">ℹ️</span>
                <div class="flex-1 text-sm font-medium pt-0.5">{{ session('info') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400">
        Absensi Pegawai &copy; {{ date('Y') }} &bull; Terhubung ke Google Sheets
    </footer>
</body>
</html>
