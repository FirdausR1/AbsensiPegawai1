@extends('layouts.app')

@section('title', 'Ganti Password - PT Inti Sarana Wijaya')

@section('content')
<div class="max-w-md space-y-4">

    <div>
        <h1 class="text-base font-bold text-slate-800">Ganti Password</h1>
        <p class="text-xs text-slate-400 mt-0.5">Masukkan password lama dan password baru Anda.</p>
    </div>

    @if ($errors->any())
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg space-y-1">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white border border-slate-200 rounded-lg p-5">
        <form method="POST" action="{{ route('profile.update-password') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Password Lama</label>
                <input type="password" name="password_lama" required
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                       placeholder="Masukkan password lama">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Password Baru</label>
                <input type="password" name="password_baru" required
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                       placeholder="Minimal 6 karakter">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Konfirmasi Password Baru</label>
                <input type="password" name="password_baru_confirmation" required
                       class="w-full px-3 py-2 rounded-md border border-slate-300 text-sm text-slate-800 focus:ring-1 focus:ring-[#000d6b] focus:border-[#000d6b] outline-none transition"
                       placeholder="Ulangi password baru">
            </div>

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('profile.edit') }}" class="text-xs text-slate-500 hover:underline">Kembali ke Data Diri</a>
                <button type="submit" class="px-5 py-2 bg-[#000d6b] hover:bg-[#001253] text-white text-xs font-semibold rounded-md transition">
                    Ubah Password
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
