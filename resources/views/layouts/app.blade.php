<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ISW Attendance - PT Inti Sarana Wijaya')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        isw: {
                            navy: '#000d6b',
                            dark: '#001253',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link rel="icon" type="image/png" href="{{ asset('images/Picture1.png') }}">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .nav-active { background-color: #eef2ff; color: #000d6b; font-weight: 600; }
        .nav-item { color: #475569; }
        .nav-item:hover { background-color: #f1f5f9; color: #0f172a; }
    </style>
</head>
<body class="bg-[#f0f2f5] text-slate-800 min-h-screen flex antialiased">

    {{-- Sidebar Desktop --}}
    <aside class="hidden lg:flex lg:flex-col w-56 bg-white border-r border-slate-200 shrink-0 h-screen sticky top-0 z-40">
        {{-- Brand --}}
        <a href="{{ route('dashboard') }}" class="p-4 flex items-center gap-2.5 shrink-0 border-b border-slate-100">
            <img src="{{ asset('images/Picture1.png') }}" alt="ISW" class="w-8 h-8 rounded-lg object-contain shrink-0">
            <div>
                <h1 class="font-bold text-slate-800 text-xs leading-tight">PT Inti Sarana Wijaya</h1>
                <p class="text-[10px] text-slate-400">Attendance System</p>
            </div>
        </a>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto sidebar-scroll">
            <a href="{{ route('dashboard') }}"
               class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('dashboard') ? 'nav-active' : 'nav-item' }}">
                Dashboard
            </a>

            <a href="{{ route('absen.riwayat') }}"
               class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('absen.riwayat') ? 'nav-active' : 'nav-item' }}">
                Riwayat Absensi
            </a>

            <a href="{{ route('cuti.index') }}"
               class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('cuti.*') && !request()->is('admin/*') ? 'nav-active' : 'nav-item' }}">
                Pengajuan Cuti
            </a>

            @if(auth()->check() && auth()->user()->isShiftWorker())
                <a href="{{ route('jadwal-shift.index') }}"
                   class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('jadwal-shift.index') && !request()->is('admin/*') ? 'nav-active' : 'nav-item' }}">
                    Jadwal Shift Saya
                </a>
            @endif

            @if(auth()->check() && (auth()->user()->isCleaningService() || auth()->user()->hasAdminAccess()))
                <a href="{{ route('tugas-periodik.index') }}"
                   class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('tugas-periodik.index') && !request()->is('admin/*') ? 'nav-active' : 'nav-item' }}">
                    Tugas Periodik CS
                </a>
            @endif

            <a href="{{ route('pengumuman.index') }}"
               class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('pengumuman.index') ? 'nav-active' : 'nav-item' }}">
                Pengumuman & SOP
            </a>

            <a href="{{ route('export.sendiri') }}"
               class="block px-3 py-2 rounded-md text-xs transition nav-item">
                Export Excel
            </a>

            <a href="{{ route('profile.ganti-password') }}"
               class="block px-3 py-2 rounded-md text-xs transition {{ request()->routeIs('profile.ganti-password') ? 'nav-active' : 'nav-item' }}">
                Ganti Password
            </a>

            @if(auth()->check() && auth()->user()->hasAdminAccess())
                {{-- Admin Section --}}
                <div class="pt-3 pb-1">
                    <button type="button" onclick="toggleAdminMenu()" class="w-full px-3 py-1.5 bg-[#000d6b] hover:bg-[#001253] rounded-md text-[10px] font-bold text-white uppercase tracking-wider flex items-center justify-between transition">
                        <span>{{ auth()->user()->isSuperAdmin() ? 'Admin Panel' : 'Koordinator' }}</span>
                        <svg id="admin-menu-arrow" class="w-3 h-3 transition-transform duration-200 {{ request()->is('admin*') ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>

                <div id="admin-menu-items" class="{{ request()->is('admin*') ? '' : 'hidden' }} space-y-0.5 pl-2 border-l-2 border-[#000d6b]/20 ml-2 mt-1">
                    <p class="px-2 pt-1 pb-0.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Monitoring</p>

                    <a href="{{ route('admin.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.index') || request()->routeIs('admin.dashboard') ? 'nav-active' : 'nav-item' }}">
                        Monitor Executive
                    </a>

                    <a href="{{ route('admin.rekap.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.rekap.*') ? 'nav-active' : 'nav-item' }}">
                        Rekap Per Pegawai
                    </a>

                    <a href="{{ route('admin.absensi.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.absensi.*') ? 'nav-active' : 'nav-item' }}">
                        Data Absensi
                    </a>

                    <p class="px-2 pt-2 pb-0.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider">Operasional</p>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.pegawai.index') }}"
                           class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.pegawai.*') ? 'nav-active' : 'nav-item' }}">
                            Kelola Pegawai
                        </a>
                        <a href="{{ route('admin.pegawai.cetak-data-all') }}"
                           class="block px-2.5 py-1.5 rounded-md text-[11px] transition nav-item" target="_blank">
                            Cetak Data Pegawai
                        </a>
                    @endif

                    <a href="{{ route('admin.cuti.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.cuti.*') ? 'nav-active' : 'nav-item' }}">
                        Persetujuan Cuti
                    </a>

                    <a href="{{ route('admin.jadwal-shift.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.jadwal-shift.*') ? 'nav-active' : 'nav-item' }}">
                        Shift Satpam
                    </a>

                    <a href="{{ route('admin.tugas-periodik.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.tugas-periodik.*') ? 'nav-active' : 'nav-item' }}">
                        Review Foto CS
                    </a>

                    <a href="{{ route('admin.sp.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.sp.*') ? 'nav-active' : 'nav-item' }}">
                        Sanksi & SP
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.kepala.index') }}"
                           class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.kepala.*') ? 'nav-active' : 'nav-item' }}">
                            TTD Kepala ISW
                        </a>
                    @endif

                    <a href="{{ route('admin.evaluasi-kinerja.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.evaluasi-kinerja.*') ? 'nav-active' : 'nav-item' }}">
                        Evaluasi Kinerja
                    </a>

                    <a href="{{ route('admin.slip.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.slip.*') ? 'nav-active' : 'nav-item' }}">
                        Slip Gaji & BPJS
                    </a>

                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.mou.index') }}"
                           class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.mou.*') ? 'nav-active' : 'nav-item' }}">
                            MoU & Kontrak
                        </a>
                    @endif

                    <a href="{{ route('admin.kantor-klien.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.kantor-klien.*') ? 'nav-active' : 'nav-item' }}">
                        Kantor Klien
                    </a>

                    <a href="{{ route('admin.divisi.index') }}"
                       class="block px-2.5 py-1.5 rounded-md text-[11px] transition {{ request()->routeIs('admin.divisi.*') ? 'nav-active' : 'nav-item' }}">
                        Shift & Waktu Kerja
                    </a>
                </div>
            @endif
        </nav>

        {{-- Bottom --}}
        <div class="p-3 border-t border-slate-100 space-y-0.5">
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded-md text-xs nav-item">Data Diri</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-xs text-red-600 hover:bg-red-50 transition">Logout</button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0">
        {{-- Top Bar --}}
        <header class="bg-white border-b border-slate-200 h-14 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-30">
            <div class="flex items-center gap-3">
                <button type="button" id="mobile-menu-btn" class="lg:hidden p-1.5 rounded text-slate-500 hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <span class="text-sm font-bold text-[#000d6b]">ISW Attendance</span>
            </div>

            @auth
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-2 py-1 rounded-md hover:bg-slate-50 transition">
                    @if(auth()->user()->hasFoto())
                        <img src="{{ auth()->user()->getFotoUrl() }}" alt="{{ auth()->user()->nama }}" class="w-7 h-7 rounded-full object-cover border border-slate-200">
                    @else
                        <div class="w-7 h-7 rounded-full bg-[#000d6b] text-white font-semibold flex items-center justify-center text-[10px]">
                            {{ auth()->user()->getInitials() }}
                        </div>
                    @endif
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-semibold text-slate-700 leading-tight">{{ auth()->user()->nama }}</div>
                        <div class="text-[10px] text-slate-400">{{ auth()->user()->getRoleBadgeText() }}</div>
                    </div>
                </a>
            @endauth
        </header>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="hidden lg:hidden bg-white border-b border-slate-200 px-4 py-2 space-y-0.5">
            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('dashboard') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Dashboard</a>
            <a href="{{ route('absen.riwayat') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('absen.riwayat') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Riwayat</a>
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('profile.edit') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Profil</a>
            <a href="{{ route('profile.signature') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('profile.signature') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Tanda Tangan</a>
            <a href="{{ route('cuti.index') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('cuti.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Cuti</a>
            @if(auth()->check() && auth()->user()->isShiftWorker())
                <a href="{{ route('jadwal-shift.index') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('jadwal-shift.index') && !request()->is('admin/*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Jadwal Shift</a>
            @endif
            <a href="{{ route('export.sendiri') }}" class="block px-3 py-2 rounded text-xs font-medium text-slate-600">Export</a>
            @if(auth()->check() && auth()->user()->hasAdminAccess())
                <div class="pt-2 pb-1 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider px-3">
                        {{ auth()->user()->isSuperAdmin() ? 'Admin' : 'Koordinator' }}
                    </p>
                </div>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.pegawai.index') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('admin.pegawai.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Kelola Pegawai</a>
                @endif
                <a href="{{ route('admin.absensi.index') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('admin.absensi.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Data Absensi</a>
                <a href="{{ route('admin.cuti.index') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('admin.cuti.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Persetujuan Cuti</a>
                <a href="{{ route('admin.divisi.index') }}" class="block px-3 py-2 rounded text-xs font-medium {{ request()->routeIs('admin.divisi.*') ? 'bg-[#eef2ff] text-[#000d6b]' : 'text-slate-600' }}">Shift & Waktu Kerja</a>
            @endif
            <form method="POST" action="{{ route('logout') }}" class="pt-2 border-t border-slate-100">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-xs font-medium text-red-600">Logout</button>
            </form>
        </div>

        {{-- Content --}}
        <main class="flex-1 p-4 sm:p-6 max-w-6xl w-full mx-auto">
            @if (session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 rounded-lg text-xs font-medium">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded-lg text-xs font-medium">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('info'))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-800 rounded-lg text-xs font-medium">
                    {{ session('info') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        function toggleAdminMenu() {
            const menu = document.getElementById('admin-menu-items');
            const arrow = document.getElementById('admin-menu-arrow');
            if (menu) {
                menu.classList.toggle('hidden');
                if (arrow) arrow.classList.toggle('rotate-180');
            }
        }
    </script>
</body>
</html>
