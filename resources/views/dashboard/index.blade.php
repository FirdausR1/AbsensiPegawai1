@extends('layouts.app')

@section('title', 'Dashboard - PT Inti Sarana Wijaya')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="bg-[#000d6b] text-white rounded-lg p-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold">{{ $pegawai->nama }}</h1>
                <p class="text-xs text-blue-200 mt-0.5">
                    {{ $pegawai->area_kerja ?: 'Staff' }}
                    @if($pegawai->is_admin) — Administrator @endif
                </p>
            </div>
            <div class="text-right">
                <div class="text-[11px] text-blue-200" id="current-date"></div>
                <div class="text-xl font-bold tracking-tight" id="current-time">00:00:00</div>
            </div>
        </div>
    </div>

    {{-- Signature Warning --}}
    @if (!$pegawai->hasSignature())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold text-red-800">Tanda tangan digital belum dibuat.</p>
                <p class="text-[11px] text-red-600 mt-0.5">Wajib dibuat sebelum bisa absen.</p>
            </div>
            <a href="{{ route('profile.signature') }}" class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition text-center">
                Buat Tanda Tangan
            </a>
        </div>
    @endif

    {{-- Shift Info --}}
    <div class="bg-white border border-slate-200 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <p class="text-[11px] text-slate-400 font-medium uppercase tracking-wide">Jadwal Hari Ini</p>
            <p class="text-sm font-bold text-slate-800 mt-0.5">
                @if($todayJadwal && $todayJadwal->isLibur())
                    Libur
                @elseif($todayJadwal)
                    Shift {{ $todayJadwal->tipe_shift }} — {{ substr($todayJadwal->getJamMasukEfektif(), 0, 5) }} s/d {{ substr($todayJadwal->getJamPulangEfektif(), 0, 5) }} WIB
                @else
                    Reguler — {{ substr($pegawai->getDivisi()->jam_masuk, 0, 5) }} s/d {{ substr($pegawai->getDivisi()->jam_pulang, 0, 5) }} WIB
                @endif
            </p>
            @if($todayJadwal && $todayJadwal->catatan)
                <p class="text-[11px] text-slate-500 mt-0.5">{{ $todayJadwal->catatan }}</p>
            @endif
        </div>
        @if($pegawai->isShiftWorker())
            <a href="{{ route('jadwal-shift.index') }}" class="text-xs font-semibold text-[#000d6b] hover:underline">Lihat Kalender Shift</a>
        @endif
    </div>

    {{-- GPS Status --}}
    <div id="gps-status-card" class="border border-amber-200 bg-amber-50 rounded-lg p-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <p id="gps-status-title" class="text-xs font-semibold text-amber-800">Mendeteksi lokasi GPS...</p>
                <p id="gps-status-desc" class="text-[11px] text-amber-700 mt-0.5">Mohon aktifkan GPS dan izinkan akses lokasi di browser.</p>
            </div>
            <div class="flex items-center gap-2">
                <span id="gps-status-badge" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-200 text-amber-900">Menunggu</span>
                <button type="button" onclick="detectGPSLocation(true)" class="px-3 py-1 bg-amber-200 hover:bg-amber-300 text-amber-900 text-[11px] font-semibold rounded transition">Deteksi Ulang</button>
            </div>
        </div>
    </div>

    {{-- Absen Masuk & Pulang --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="attendance-section">

        {{-- Absen Masuk --}}
        <div class="bg-white border border-slate-200 rounded-lg p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-slate-800">Absen Masuk</h2>
                @if($todayAbsensi && $todayAbsensi->jam_masuk)
                    <span class="text-xs font-semibold text-green-700 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                        {{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB
                    </span>
                @endif
            </div>

            @if($todayAbsensi && $todayAbsensi->jam_masuk)
                @php $menitTerlambat = $todayAbsensi->getMenitTerlambat(); @endphp
                <p class="text-xs text-slate-500">
                    Sudah tercatat pukul {{ substr($todayAbsensi->jam_masuk, 0, 5) }} WIB.
                    @if($menitTerlambat > 0)
                        <span class="text-red-600 font-semibold">Terlambat {{ $menitTerlambat }} menit.</span>
                    @else
                        <span class="text-green-600 font-semibold">Tepat waktu.</span>
                    @endif
                </p>
                @if($todayAbsensi->lokasi_masuk)
                    <p class="text-[11px] text-slate-400 mt-1">Lokasi: {{ $todayAbsensi->lokasi_masuk }}</p>
                @endif
            @else
                @php
                    $targetMasuk = $todayJadwal ? $todayJadwal->getJamMasukEfektif() : $pegawai->getDivisi()->jam_masuk;
                @endphp
                <p class="text-xs text-slate-500">
                    @if($todayJadwal && $todayJadwal->isLibur())
                        Hari ini libur.
                    @else
                        Jadwal masuk: {{ $targetMasuk ? substr($targetMasuk, 0, 5) : '08:00' }} WIB
                    @endif
                </p>
            @endif

            <form method="POST" action="{{ route('absen.masuk') }}" enctype="multipart/form-data" class="mt-4 space-y-3" onsubmit="return validateAttendanceSubmission(this)">
                @csrf
                <input type="hidden" name="lokasi" class="user-gps-location">

                @if(!$todayAbsensi || !$todayAbsensi->jam_masuk)
                    <div class="space-y-2 text-xs">
                        <label class="block font-semibold text-slate-600">Mode Presensi:</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-700">
                                <input type="radio" name="status_presensi" value="reguler" checked onclick="toggleDinasLuarInput('masuk', false)" class="text-[#000d6b]">
                                <span>Reguler</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-slate-700">
                                <input type="radio" name="status_presensi" value="dinas_luar" onclick="toggleDinasLuarInput('masuk', true)" class="text-[#000d6b]">
                                <span>Dinas Luar</span>
                            </label>
                        </div>
                    </div>

                    <div id="dinas-luar-box-masuk" class="hidden p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1 text-xs">
                        <label class="block font-semibold text-slate-700">Foto Bukti Dinas Luar <span class="text-red-500">*</span></label>
                        <input type="file" name="foto_dinas_luar" accept="image/*" class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded p-1.5">
                    </div>
                @endif

                @if($todayAbsensi && $todayAbsensi->jam_masuk)
                    <button type="button" disabled class="w-full py-2.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700 border border-green-200 cursor-default">
                        Sudah Absen Masuk ({{ substr($todayAbsensi->jam_masuk, 0, 5) }})
                    </button>
                @else
                    <button type="submit" id="btn-absen-masuk"
                            {{ $pegawai->hasSignature() ? '' : 'disabled' }}
                            class="w-full py-2.5 rounded-lg text-xs font-bold transition {{ $pegawai->hasSignature() ? 'bg-[#000d6b] hover:bg-[#001253] text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                        Catat Absen Masuk
                    </button>
                @endif
            </form>
        </div>

        {{-- Absen Pulang --}}
        <div class="bg-white border border-slate-200 rounded-lg p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-slate-800">Absen Pulang</h2>
                @if($todayAbsensi && $todayAbsensi->jam_pulang)
                    <span class="text-xs font-semibold text-[#000d6b] bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                        {{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB
                    </span>
                @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded">
                        Belum Pulang
                    </span>
                @endif
            </div>

            <p class="text-xs text-slate-500">
                @if($todayAbsensi && $todayAbsensi->jam_pulang)
                    Tercatat pukul {{ substr($todayAbsensi->jam_pulang, 0, 5) }} WIB.
                    @if($todayAbsensi->lokasi_pulang)
                        <br><span class="text-[11px] text-slate-400">Lokasi: {{ $todayAbsensi->lokasi_pulang }}</span>
                    @endif
                @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                    Klik tombol di bawah saat selesai bekerja.
                @else
                    Absen masuk terlebih dahulu.
                @endif
            </p>

            <form method="POST" action="{{ route('absen.pulang') }}" enctype="multipart/form-data" class="mt-4 space-y-3" onsubmit="return validateAttendanceSubmission(this)">
                @csrf
                <input type="hidden" name="lokasi" class="user-gps-location">

                @if($todayAbsensi && $todayAbsensi->jam_masuk && !$todayAbsensi->jam_pulang)
                    @if($todayAbsensi->status_presensi === 'dinas_luar')
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-1 text-xs">
                            <label class="block font-semibold text-slate-700">Foto Bukti Dinas Luar (Pulang)</label>
                            <input type="file" name="foto_dinas_luar" accept="image/*" class="w-full text-xs text-slate-700 bg-white border border-slate-300 rounded p-1.5">
                        </div>
                    @endif
                @endif

                @if($todayAbsensi && $todayAbsensi->jam_pulang)
                    <button type="button" disabled class="w-full py-2.5 rounded-lg text-xs font-semibold bg-slate-50 text-slate-500 border border-slate-200 cursor-default">
                        Sudah Absen Pulang ({{ substr($todayAbsensi->jam_pulang, 0, 5) }})
                    </button>
                @elseif($todayAbsensi && $todayAbsensi->jam_masuk)
                    <button type="submit" id="btn-absen-pulang"
                            {{ $pegawai->hasSignature() ? '' : 'disabled' }}
                            class="w-full py-2.5 rounded-lg text-xs font-bold transition {{ $pegawai->hasSignature() ? 'bg-slate-800 hover:bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 cursor-not-allowed' }}">
                        Catat Absen Pulang
                    </button>
                @else
                    <button type="button" disabled class="w-full py-2.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-400 cursor-not-allowed">
                        Catat Absen Pulang
                    </button>
                @endif
            </form>
        </div>
    </div>

    {{-- Statistik Singkat --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <p class="text-[11px] text-slate-400 font-medium">Hadir Bulan Ini</p>
            <p class="text-xl font-bold text-slate-800 mt-0.5">{{ $stats['total_bulan_ini'] ?? 0 }} <span class="text-xs font-normal text-slate-400">hari</span></p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <p class="text-[11px] text-slate-400 font-medium">Kehadiran Lengkap</p>
            <p class="text-xl font-bold text-green-700 mt-0.5">{{ $stats['total_lengkap'] ?? 0 }} <span class="text-xs font-normal text-slate-400">hari</span></p>
        </div>
        <div class="bg-white border border-slate-200 rounded-lg p-4 col-span-2 sm:col-span-1">
            <p class="text-[11px] text-slate-400 font-medium">Export Rekap</p>
            <button type="button" onclick="document.getElementById('modal-export-user').classList.remove('hidden')"
                    class="mt-1 px-3 py-1.5 bg-[#000d6b] hover:bg-[#001253] text-white text-[11px] font-semibold rounded transition">
                Download Excel
            </button>
        </div>
    </div>

    {{-- Pengumuman --}}
    @if(isset($activePengumumans) && $activePengumumans->count() > 0)
        <div class="bg-white border border-slate-200 rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-800">Pengumuman</h3>
                <a href="{{ route('pengumuman.index') }}" class="text-xs font-semibold text-[#000d6b] hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-2">
                @foreach($activePengumumans as $info)
                    <div class="p-3 bg-slate-50 border border-slate-100 rounded-lg">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $info->getKategoriBadgeColor() }}">{{ strtoupper($info->kategori) }}</span>
                                <span class="text-xs font-semibold text-slate-800">{{ $info->judul }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 shrink-0">{{ $info->created_at->translatedFormat('d M Y') }}</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">{{ $info->isi }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Riwayat 7 Hari Terakhir --}}
    <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-800">Riwayat 7 Hari Terakhir</h3>
            <a href="{{ route('absen.riwayat') }}" class="text-xs font-semibold text-[#000d6b] hover:underline">Lihat Semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-[#000d6b] text-white">
                        <th class="py-2.5 px-4 text-left font-semibold">Tanggal</th>
                        <th class="py-2.5 px-4 text-left font-semibold">Masuk</th>
                        <th class="py-2.5 px-4 text-left font-semibold">Pulang</th>
                        <th class="py-2.5 px-4 text-left font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentAbsensis as $item)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-medium text-slate-700">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-600">
                                {{ $item->jam_masuk ? substr($item->jam_masuk, 0, 5) : '-' }}
                            </td>
                            <td class="py-2.5 px-4 text-slate-600">
                                {{ $item->jam_pulang ? substr($item->jam_pulang, 0, 5) : '-' }}
                            </td>
                            <td class="py-2.5 px-4">
                                @if($item->jam_masuk && $item->jam_pulang)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">Lengkap</span>
                                @elseif($item->jam_masuk)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Belum Pulang</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-400">Belum ada riwayat absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Export --}}
    <div id="modal-export-user" class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-lg max-w-sm w-full p-5 space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <h3 class="text-sm font-bold text-[#000d6b]">Export Rekap Absensi</h3>
                <button onclick="document.getElementById('modal-export-user').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>
            <p class="text-xs text-slate-500">Pilih opsi tampilan kolom lokasi:</p>
            <div class="space-y-2">
                <a href="{{ route('export.sendiri', ['include_location' => 1]) }}"
                   class="block w-full text-center p-2.5 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-semibold rounded-lg transition">
                    Download Dengan Lokasi GPS
                </a>
                <a href="{{ route('export.sendiri') }}"
                   class="block w-full text-center p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition">
                    Download Standar
                </a>
            </div>
        </div>
    </div>

</div>

<script>
    let currentGPSLocationString = "";
    let isGPSAcquired = false;

    function toggleDinasLuarInput(type, show) {
        const el = document.getElementById('dinas-luar-box-' + type);
        if (el) {
            if (show) el.classList.remove('hidden');
            else el.classList.add('hidden');
        }
    }

    function setGPSCardState(status, title, desc, badgeText, badgeClass) {
        const card = document.getElementById('gps-status-card');
        const titleEl = document.getElementById('gps-status-title');
        const descEl = document.getElementById('gps-status-desc');
        const badge = document.getElementById('gps-status-badge');

        if (!card) return;

        if (status === 'success') {
            card.className = "border border-green-200 bg-green-50 rounded-lg p-3";
            titleEl.className = "text-xs font-semibold text-green-800";
            descEl.className = "text-[11px] text-green-700 mt-0.5";
            badge.className = "px-2 py-0.5 rounded text-[10px] font-semibold bg-green-200 text-green-900";
        } else if (status === 'error') {
            card.className = "border border-red-200 bg-red-50 rounded-lg p-3";
            titleEl.className = "text-xs font-semibold text-red-800";
            descEl.className = "text-[11px] text-red-700 mt-0.5";
            badge.className = "px-2 py-0.5 rounded text-[10px] font-semibold bg-red-200 text-red-900";
        } else {
            card.className = "border border-amber-200 bg-amber-50 rounded-lg p-3";
            titleEl.className = "text-xs font-semibold text-amber-800";
            descEl.className = "text-[11px] text-amber-700 mt-0.5";
            badge.className = "px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-200 text-amber-900";
        }

        titleEl.textContent = title;
        descEl.innerHTML = desc;
        badge.textContent = badgeText;
    }

    function detectGPSLocation(isUserTriggered = false) {
        if (!("geolocation" in navigator)) {
            setGPSCardState('error', 'Perangkat tidak mendukung GPS', 'Browser Anda tidak mendukung Geolocation.', 'Tidak Didukung', '');
            return;
        }

        setGPSCardState('loading', 'Mendeteksi lokasi GPS...', 'Meminta koordinat dari perangkat...', 'Mencari...', '');

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const lat = position.coords.latitude.toFixed(6);
                const lng = position.coords.longitude.toFixed(6);
                const coordStr = `${lat}, ${lng}`;

                isGPSAcquired = true;
                currentGPSLocationString = "GPS: " + coordStr;
                document.querySelectorAll('.user-gps-location').forEach(el => el.value = currentGPSLocationString);

                setGPSCardState('success', 'Lokasi GPS terdeteksi', `Koordinat: <strong>${coordStr}</strong> (±${Math.round(position.coords.accuracy)}m)`, 'GPS Aktif', '');

                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=17&addressdetails=1`, {
                        headers: { 'Accept-Language': 'id' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.display_name) {
                            const shortName = data.address?.road
                                ? `${data.address.road}, ${data.address.suburb || data.address.city || data.address.county || ''}`
                                : data.display_name.split(',').slice(0, 3).join(',');

                            currentGPSLocationString = `${shortName.trim()} (${coordStr})`;
                            document.querySelectorAll('.user-gps-location').forEach(el => el.value = currentGPSLocationString);
                            setGPSCardState('success', 'Lokasi GPS terverifikasi', `<strong>${shortName.trim()}</strong><br><span class="text-[10px] text-green-600">${coordStr}</span>`, 'GPS Aktif', '');
                        }
                    }
                } catch (e) {}
            },
            function(error) {
                isGPSAcquired = false;
                let errMsg = "GPS belum aktif atau izin ditolak.";
                if (error.code === 1) {
                    errMsg = "Izin lokasi diblokir. Klik ikon gembok di URL bar, pilih Izinkan Lokasi, lalu muat ulang halaman.";
                } else if (error.code === 2) {
                    errMsg = "Posisi GPS tidak tersedia. Pastikan GPS perangkat sudah dinyalakan.";
                } else if (error.code === 3) {
                    errMsg = "Waktu pencarian GPS habis. Silakan coba lagi.";
                }
                setGPSCardState('error', 'GPS belum aktif', errMsg, 'Non-Aktif', '');
                if (isUserTriggered) {
                    alert("GPS belum terdeteksi. Nyalakan GPS dan izinkan akses lokasi di browser.");
                }
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    }

    function validateAttendanceSubmission(form) {
        const locInput = form.querySelector('.user-gps-location');
        if (!locInput || !locInput.value || locInput.value.trim() === "") {
            alert("GPS/Lokasi belum terdeteksi. Nyalakan GPS dan izinkan akses lokasi sebelum absen.");
            detectGPSLocation(true);
            return false;
        }
        return true;
    }

    detectGPSLocation();

    function updateClock() {
        const now = new Date();
        const dateEl = document.getElementById('current-date');
        const timeEl = document.getElementById('current-time');
        if (dateEl) dateEl.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
        if (timeEl) timeEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    updateClock();
    setInterval(updateClock, 1000);
</script>
@endsection
