<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ISW Attendance - PT Inti Sarana Wijaya')</title>
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
                            slate: '#f8fafc',
                            border: '#e2e8f0'
                        }
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Picture1.png') }}">
    <style>
        body { font-family: 'Plus Jakarta Sans', Inter, sans-serif; }
        .sidebar-active {
            background-color: #eef2ff;
            color: #000d6b;
            font-weight: 600;
        }
        .sidebar-inactive {
            color: #475569;
        }
        .sidebar-inactive:hover {
            background-color: #f1f5f9;
            color: #0f172a;
        }
        .section-bar {
            width: 3.5px;
            height: 18px;
            background-color: #000d6b;
            border-radius: 9999px;
            display: inline-block;
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-800 min-h-screen flex antialiased">

    <!-- Sidebar (Desktop) -->
    <aside class="hidden lg:flex lg:flex-col w-64 bg-white border-r border-slate-200 shrink-0 h-screen sticky top-0 z-40">
        <!-- Sidebar Brand -->
        <a href="{{ route('dashboard') }}" class="p-6 pb-4 flex items-center gap-3 hover:opacity-95 transition group">
            <img src="{{ asset('images/Picture1.png') }}"
                 alt="PT Inti Sarana Wijaya Logo"
                 class="w-10 h-10 rounded-xl object-contain shadow-sm shrink-0">
            <div>
                <h1 class="font-bold text-slate-900 text-sm tracking-tight leading-tight group-hover:text-[#000d6b] transition">PT Inti Sarana Wijaya</h1>
                <p class="text-[11px] text-slate-400 font-medium">Attendance System</p>
        </a>

        <!-- Quick Clock-In Button -->
        <div class="px-5 py-3">
            <a href="{{ route('dashboard') }}"
               class="w-full py-2.5 px-4 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-2 tracking-wide">
                <span>⚡</span> Quick Clock-In
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-2 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'sidebar-inactive' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                </svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dashboard') }}#attendance-section"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition sidebar-inactive">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"></circle>
                    <polyline points="12 7 12 12 15 15"></polyline>
                </svg>
                <span>Attendance</span>
            </a>

            <a href="{{ route('absen.riwayat') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('absen.riwayat') ? 'sidebar-active' : 'sidebar-inactive' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span>History</span>
            </a>

            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('profile.edit') ? 'sidebar-active' : 'sidebar-inactive' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span>Profile</span>
            </a>

            <a href="{{ route('profile.signature') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('profile.signature') ? 'sidebar-active' : 'sidebar-inactive' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                <span>Digital Signature</span>
            </a>

            <a href="{{ route('cuti.index') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('cuti.*') ? 'sidebar-active' : 'sidebar-inactive' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span>Pengajuan Cuti</span>
            </a>

            <a href="{{ route('export.sendiri') }}"
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition sidebar-inactive">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                <span>Export Excel</span>
            </a>

            @if(auth()->check() && auth()->user()->hasAdminAccess())
                <div class="pt-4 pb-1">
                    <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                        {{ auth()->user()->isSuperAdmin() ? 'Admin Panel' : 'Koordinator Divisi' }}
                    </div>
                </div>

                <a href="{{ route('admin.pegawai.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('admin.pegawai.*') ? 'sidebar-active' : 'sidebar-inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span>Manage Employees</span>
                </a>

                <a href="{{ route('admin.absensi.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('admin.absensi.*') ? 'sidebar-active' : 'sidebar-inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <span>All Attendance</span>
                </a>

                <a href="{{ route('admin.cuti.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('admin.cuti.*') ? 'sidebar-active' : 'sidebar-inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                    <span>Persetujuan Cuti</span>
                </a>

                <a href="{{ route('admin.divisi.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-xs transition {{ request()->routeIs('admin.divisi.*') ? 'sidebar-active' : 'sidebar-inactive' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <polyline points="12 7 12 12 15 15"></polyline>
                    </svg>
                    <span>Shift & Waktu Kerja</span>
                </a>
            @endif
        </nav>

        <!-- Sidebar Bottom Settings & Logout -->
        <div class="p-4 border-t border-slate-100 space-y-1">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2 rounded-lg text-xs sidebar-inactive">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"></path>
                </svg>
                <span>Settings</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2 rounded-lg text-xs text-rose-600 hover:bg-rose-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main App Container -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Navbar -->
        <header class="bg-white border-b border-slate-200 h-16 flex items-center justify-between px-4 sm:px-8 sticky top-0 z-30 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
            <div class="flex items-center gap-3">
                <!-- Mobile Menu Button -->
                <button type="button" id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <span class="text-base sm:text-lg font-bold text-[#000d6b] tracking-tight">ISW Attendance</span>
            </div>

            <!-- Top Header Icons & Profile -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Notification Bell -->
                <button type="button" class="w-9 h-9 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center transition relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="w-2 h-2 bg-[#000d6b] rounded-full absolute top-2 right-2"></span>
                </button>

                <!-- Help Icon -->
                <button type="button" class="w-9 h-9 rounded-full text-slate-500 hover:text-slate-800 hover:bg-slate-100 flex items-center justify-center transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="9"></circle>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"></path>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </button>

                <!-- User Profile Dropdown Pill -->
                @auth
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full hover:bg-slate-100 transition group">
                        <img src="{{ asset('images/default-avatar.jpg') }}"
                             alt="{{ auth()->user()->nama }}"
                             class="w-8 h-8 rounded-full object-cover border border-slate-300">
                        <div class="hidden sm:block text-left">
                            <div class="text-xs font-bold text-slate-800 group-hover:text-[#000d6b] leading-tight">
                                {{ auth()->user()->nama }}
                            </div>
                            <div class="text-[10px] text-slate-400 font-medium">
                                {{ auth()->user()->getRoleBadgeText() }}
                            </div>
                        </div>
                    </a>
                @endauth
            </div>
        </header>

        <!-- Mobile Drawer Navigation (Hidden by default) -->
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('dashboard') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Dashboard</a>
            <a href="{{ route('absen.riwayat') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('absen.riwayat') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">History</a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('profile.edit') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Profile</a>
            <a href="{{ route('profile.signature') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('profile.signature') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Digital Signature</a>
            <a href="{{ route('cuti.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('cuti.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Pengajuan Cuti</a>
            <a href="{{ route('export.sendiri') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold text-slate-700">Export Excel</a>
            @if(auth()->check() && auth()->user()->hasAdminAccess())
                <div class="pt-2 pb-1 border-t border-slate-100">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">
                        {{ auth()->user()->isSuperAdmin() ? 'Admin Panel' : 'Koordinator Divisi' }}
                    </div>
                </div>
                <a href="{{ route('admin.pegawai.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.pegawai.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Manage Employees</a>
                <a href="{{ route('admin.absensi.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.absensi.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">All Attendance</a>
                <a href="{{ route('admin.cuti.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.cuti.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Persetujuan Cuti</a>
                <a href="{{ route('admin.divisi.index') }}" class="block px-3 py-2 rounded-lg text-xs font-semibold {{ request()->routeIs('admin.divisi.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-700' }}">Shift & Waktu Kerja</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-xs font-semibold text-rose-600">Logout</button>
            </form>
        </div>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
            <!-- Flash Alerts -->
            @if (session('success'))
                <div class="mb-6 flex items-start gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm text-sm">
                    <span class="text-emerald-600 font-bold">✓</span>
                    <div class="flex-1 font-medium">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 flex items-start gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm text-sm">
                    <span class="text-rose-600 font-bold">✕</span>
                    <div class="flex-1 font-medium">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="mb-6 flex items-start gap-3 p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-xl shadow-sm text-sm">
                    <span class="text-sky-600 font-bold">ℹ</span>
                    <div class="flex-1 font-medium">{{ session('info') }}</div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });
    </script>
</body>
</html>
