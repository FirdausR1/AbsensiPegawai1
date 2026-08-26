<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pegawai - Absensi Pegawai</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl p-8 border border-slate-200">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-600 text-white rounded-2xl shadow-md mb-3 text-2xl font-bold">
                ✍️
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Daftar Pegawai</h1>
            <p class="text-sm text-slate-500 mt-1">Buat akun untuk akses sistem absensi</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-sm rounded-xl">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            <div>
                <label for="nama" class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="nama" id="nama" value="{{ old('nama') }}" required autofocus
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-sm"
                       placeholder="Contoh: Budi Santoso">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-sm"
                       placeholder="budi@perusahaan.com">
            </div>

            <div>
                <label for="area_kerja" class="block text-sm font-medium text-slate-700 mb-1">Area Kerja / Bagian</label>
                <input type="text" name="area_kerja" id="area_kerja" value="{{ old('area_kerja') }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-sm"
                       placeholder="Contoh: Operasional / Lapangan">
            </div>

            <div>
                <label for="sheet_tab_name" class="block text-sm font-medium text-slate-700 mb-1">Nama Tab Google Sheet (Opsional)</label>
                <input type="text" name="sheet_tab_name" id="sheet_tab_name" value="{{ old('sheet_tab_name') }}"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-sm"
                       placeholder="Biarkan kosong jika sama dengan Nama">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-sm"
                       placeholder="Minimal 8 karakter">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition outline-none text-slate-800 text-sm"
                       placeholder="Ulangi password">
            </div>

            <button type="submit"
                    class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-md transition duration-150">
                Daftar
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-slate-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">Masuk</a>
        </div>
    </div>
</body>
</html>
