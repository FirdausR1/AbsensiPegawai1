<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\JadwalShiftController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AbsensiController::class, 'dashboard'])->name('dashboard');
    Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('absen.riwayat');

    // Jadwal Shift (Employee view)
    Route::get('/jadwal-shift', [JadwalShiftController::class, 'index'])->name('jadwal-shift.index');
    Route::post('/jadwal-shift/request', [JadwalShiftController::class, 'requestGanti'])->name('jadwal-shift.request');

    // Pengajuan Cuti (Employee)
    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
    Route::delete('/cuti/{cuti}', [CutiController::class, 'cancel'])->name('cuti.cancel');

    // Profile / Data Diri
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/signature', [ProfileController::class, 'edit'])->name('profile.signature');
    Route::post('/profile/signature', [ProfileController::class, 'saveSignature'])->name('profile.signature.save');

    Route::post('/absen/masuk', [AbsensiController::class, 'absenMasuk'])->name('absen.masuk');
    Route::post('/absen/pulang', [AbsensiController::class, 'absenPulang'])->name('absen.pulang');

    // Export Excel
    Route::get('/export/{bulan?}', [ExportController::class, 'exportSendiri'])->name('export.sendiri');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.pegawai.index');
        })->name('index');

        // Kelola Pegawai
        Route::get('/pegawai', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/pegawai/create', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/pegawai/{pegawai}/edit', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('/pegawai/{pegawai}', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('/pegawai/{pegawai}', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::post('/pegawai/{pegawai}/reset-signature', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'resetSignature'])->name('pegawai.reset-signature');

        // Kelola Persetujuan Cuti (Admin)
        Route::get('/cuti', [\App\Http\Controllers\Admin\AdminCutiController::class, 'index'])->name('cuti.index');
        Route::post('/cuti/{cuti}/approve', [\App\Http\Controllers\Admin\AdminCutiController::class, 'approve'])->name('cuti.approve');
        Route::post('/cuti/{cuti}/reject', [\App\Http\Controllers\Admin\AdminCutiController::class, 'reject'])->name('cuti.reject');

        // Kelola Absensi & Absen Terlewat
        Route::get('/absensi', [\App\Http\Controllers\Admin\AdminAbsensiController::class, 'index'])->name('absensi.index');
        Route::post('/absensi/retry-sync/{absensi}', [\App\Http\Controllers\Admin\AdminAbsensiController::class, 'retrySync'])->name('absensi.retry-sync');
        Route::post('/absensi/manual', [\App\Http\Controllers\Admin\AdminAbsensiController::class, 'manualStore'])->name('absensi.manual');
        Route::put('/absensi/{absensi}', [\App\Http\Controllers\Admin\AdminAbsensiController::class, 'update'])->name('absensi.update');
        Route::delete('/absensi/{absensi}', [\App\Http\Controllers\Admin\AdminAbsensiController::class, 'destroy'])->name('absensi.destroy');

        // Kelola Divisi & Jadwal Jam Kerja
        Route::get('/divisi', [\App\Http\Controllers\Admin\AdminDivisiController::class, 'index'])->name('divisi.index');
        Route::post('/divisi', [\App\Http\Controllers\Admin\AdminDivisiController::class, 'store'])->name('divisi.store');
        Route::put('/divisi/{divisi}', [\App\Http\Controllers\Admin\AdminDivisiController::class, 'update'])->name('divisi.update');
        Route::delete('/divisi/{divisi}', [\App\Http\Controllers\Admin\AdminDivisiController::class, 'destroy'])->name('divisi.destroy');

        // Kelola Jadwal Shift (Satpam / CS)
        Route::get('/jadwal-shift', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'index'])->name('jadwal-shift.index');
        Route::post('/jadwal-shift', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'store'])->name('jadwal-shift.store');
        Route::post('/jadwal-shift/bulk', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'storeBulk'])->name('jadwal-shift.bulk');
        Route::post('/jadwal-shift/destroy', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'destroy'])->name('jadwal-shift.destroy');
        Route::get('/jadwal-shift/requests', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'requests'])->name('jadwal-shift.requests');
        Route::post('/jadwal-shift/{jadwalShift}/approve', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'approveRequest'])->name('jadwal-shift.approve');

        // Export Excel (Admin)
        Route::get('/export/{pegawai}/{bulan?}', [ExportController::class, 'exportPegawai'])->name('export.pegawai');
        Route::get('/export-semua/{bulan?}', [ExportController::class, 'exportSemua'])->name('export.semua');
    });
});
