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

    // Jadwal Shift (Employee view & Request)
    Route::get('/jadwal-shift', [JadwalShiftController::class, 'index'])->name('jadwal-shift.index');
    Route::post('/jadwal-shift/request', [JadwalShiftController::class, 'requestShift'])->name('jadwal-shift.request');
    Route::delete('/jadwal-shift/{jadwalShift}/cancel', [JadwalShiftController::class, 'cancelRequest'])->name('jadwal-shift.cancel');

    // Pengajuan Cuti (Employee)
    Route::get('/cuti', [CutiController::class, 'index'])->name('cuti.index');
    Route::get('/cuti/{cuti}/cetak', [CutiController::class, 'cetak'])->name('cuti.cetak');
    Route::post('/cuti', [CutiController::class, 'store'])->name('cuti.store');
    Route::delete('/cuti/{cuti}', [CutiController::class, 'cancel'])->name('cuti.cancel');

    // Tugas Periodik Harian & Mingguan (Cleaning Service)
    Route::get('/tugas-periodik', [\App\Http\Controllers\TugasPeriodikController::class, 'index'])->name('tugas-periodik.index');
    Route::post('/tugas-periodik', [\App\Http\Controllers\TugasPeriodikController::class, 'store'])->name('tugas-periodik.store');
    Route::delete('/tugas-periodik/{tugasPeriodik}', [\App\Http\Controllers\TugasPeriodikController::class, 'destroy'])->name('tugas-periodik.destroy');

    // Pengumuman, Ketentuan SOP ISW & Pasal Pelanggaran
    Route::get('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'index'])->name('pengumuman.index');
    Route::post('/pengumuman', [\App\Http\Controllers\PengumumanController::class, 'store'])->name('pengumuman.store');
    Route::put('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'update'])->name('pengumuman.update');
    Route::delete('/pengumuman/{pengumuman}', [\App\Http\Controllers\PengumumanController::class, 'destroy'])->name('pengumuman.destroy');

    // Profile / Data Diri
    Route::get('/profile', [ProfileController::class, 'showProfile'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::get('/profile/signature', [ProfileController::class, 'edit'])->name('profile.signature');
    Route::post('/profile/signature', [ProfileController::class, 'saveSignature'])->name('profile.signature.save');
    Route::get('/profile/ganti-password', [ProfileController::class, 'showChangePassword'])->name('profile.ganti-password');
    Route::put('/profile/ganti-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');

    Route::post('/absen/masuk', [AbsensiController::class, 'absenMasuk'])->name('absen.masuk');
    Route::post('/absen/pulang', [AbsensiController::class, 'absenPulang'])->name('absen.pulang');

    // Export Excel
    Route::get('/export/{bulan?}', [ExportController::class, 'exportSendiri'])->name('export.sendiri');
    Route::get('/export-cuti', [ExportController::class, 'exportCutiSendiri'])->name('export.cuti.sendiri');

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // Dashboard Admin Executive / Realtime Monitor
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('index');
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Rekap Presensi Per Pegawai
        Route::get('/rekap-pegawai', [\App\Http\Controllers\Admin\AdminRekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap-pegawai/{pegawai}', [\App\Http\Controllers\Admin\AdminRekapController::class, 'detailPegawai'])->name('rekap.detail');

        // Kelola Pegawai
        Route::get('/pegawai', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'index'])->name('pegawai.index');
        Route::get('/pegawai/create', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'create'])->name('pegawai.create');
        Route::post('/pegawai', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/pegawai/{pegawai}/edit', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'edit'])->name('pegawai.edit');
        Route::put('/pegawai/{pegawai}', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('/pegawai/{pegawai}', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::post('/pegawai/{pegawai}/reset-signature', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'resetSignature'])->name('pegawai.reset-signature');
        Route::get('/pegawai/cetak-kontrak-massal', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'cetakKontrakMassal'])->name('pegawai.cetak-kontrak-massal');
        Route::get('/pegawai/{pegawai}/cetak-kontrak', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'cetakKontrak'])->name('pegawai.cetak-kontrak');
        Route::get('/pegawai/cetak-skk-massal', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'cetakSkkMassal'])->name('pegawai.cetak-skk-massal');
        Route::get('/pegawai/{pegawai}/cetak-skk', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'cetakSkk'])->name('pegawai.cetak-skk');
        Route::get('/pegawai/cetak-data-all', [\App\Http\Controllers\Admin\AdminPegawaiController::class, 'cetakDataAll'])->name('pegawai.cetak-data-all');

        // Kelola Persetujuan Cuti (Admin)
        Route::get('/cuti', [\App\Http\Controllers\Admin\AdminCutiController::class, 'index'])->name('cuti.index');
        Route::get('/cuti/{cuti}/cetak', [\App\Http\Controllers\Admin\AdminCutiController::class, 'cetak'])->name('cuti.cetak');
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
        Route::post('/jadwal-shift/{jadwalShift}/approve', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'approve'])->name('jadwal-shift.approve');
        Route::post('/jadwal-shift/{jadwalShift}/reject', [\App\Http\Controllers\Admin\AdminJadwalShiftController::class, 'reject'])->name('jadwal-shift.reject');

        // Review & Rating Tugas Periodik Cleaning Service (Admin)
        Route::get('/tugas-periodik', [\App\Http\Controllers\Admin\AdminTugasPeriodikController::class, 'index'])->name('tugas-periodik.index');
        Route::post('/tugas-periodik/{tugasPeriodik}/rate', [\App\Http\Controllers\Admin\AdminTugasPeriodikController::class, 'rateTask'])->name('tugas-periodik.rate');
        Route::get('/tugas-periodik/export-excel', [\App\Http\Controllers\Admin\AdminTugasPeriodikController::class, 'exportExcel'])->name('tugas-periodik.export-excel');

        // Kelola Master Data Kantor Klien / Site Project (Admin)
        Route::get('/kantor-klien', [\App\Http\Controllers\Admin\AdminKantorKlienController::class, 'index'])->name('kantor-klien.index');
        Route::post('/kantor-klien', [\App\Http\Controllers\Admin\AdminKantorKlienController::class, 'store'])->name('kantor-klien.store');
        Route::put('/kantor-klien/{kantorKlien}', [\App\Http\Controllers\Admin\AdminKantorKlienController::class, 'update'])->name('kantor-klien.update');
        Route::delete('/kantor-klien/{kantorKlien}', [\App\Http\Controllers\Admin\AdminKantorKlienController::class, 'destroy'])->name('kantor-klien.destroy');

        // Kelola Surat Peringatan & Pelanggaran (SP 1, SP 2, SP 3)
        Route::get('/surat-peringatan', [\App\Http\Controllers\Admin\SuratPeringatanController::class, 'index'])->name('sp.index');
        Route::post('/surat-peringatan', [\App\Http\Controllers\Admin\SuratPeringatanController::class, 'store'])->name('sp.store');
        Route::delete('/surat-peringatan/{suratPeringatan}', [\App\Http\Controllers\Admin\SuratPeringatanController::class, 'destroy'])->name('sp.destroy');
        Route::get('/surat-peringatan/{suratPeringatan}/cetak', [\App\Http\Controllers\Admin\SuratPeringatanController::class, 'cetak'])->name('sp.cetak');

        // Kelola Tanda Tangan & QR Code Kepala ISW (Admin)
        Route::get('/kepala-isw', [\App\Http\Controllers\Admin\AdminKepalaController::class, 'index'])->name('kepala.index');
        Route::post('/kepala-isw/signature', [\App\Http\Controllers\Admin\AdminKepalaController::class, 'updateSignature'])->name('kepala.signature.update');

        // Kelola MoU & Kontrak Kerjasama Outsourcing Klien (Admin)
        Route::get('/mou-kontrak', [\App\Http\Controllers\Admin\AdminMouKontrakController::class, 'index'])->name('mou.index');
        Route::post('/mou-kontrak', [\App\Http\Controllers\Admin\AdminMouKontrakController::class, 'store'])->name('mou.store');
        Route::delete('/mou-kontrak/{mouKontrak}', [\App\Http\Controllers\Admin\AdminMouKontrakController::class, 'destroy'])->name('mou.destroy');
        Route::get('/mou-kontrak/{mouKontrak}/cetak', [\App\Http\Controllers\Admin\AdminMouKontrakController::class, 'cetak'])->name('mou.cetak');

        // Kelola Slip Gaji & BPJS Payroll (Admin)
        Route::get('/slip-gaji', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'index'])->name('slip.index');
        Route::get('/slip-gaji/export-excel', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'exportExcel'])->name('slip.export-excel');
        Route::put('/slip-gaji/massal-update', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'updateSalaryAndBpjsMassal'])->name('slip.update-salary-bpjs-massal');
        Route::put('/slip-gaji/{pegawai}/salary-bpjs', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'updateSalaryAndBpjs'])->name('slip.update-salary-bpjs');
        Route::post('/slip-gaji/{pegawai}/generate', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'generateSlip'])->name('slip.generate');
        Route::get('/slip-gaji/cetak-massal', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'cetakMassal'])->name('slip.cetak-massal');
        Route::get('/slip-gaji/{pegawai}/cetak/{bulan}', [\App\Http\Controllers\Admin\AdminSlipGajiController::class, 'cetak'])->name('slip.cetak');

        // Kelola Evaluasi Kinerja Periodik & Lampiran Absen + Tugas
        Route::get('/evaluasi-kinerja', [\App\Http\Controllers\Admin\AdminEvaluasiKinerjaController::class, 'index'])->name('evaluasi-kinerja.index');
        Route::get('/evaluasi-kinerja/create', [\App\Http\Controllers\Admin\AdminEvaluasiKinerjaController::class, 'create'])->name('evaluasi-kinerja.create');
        Route::post('/evaluasi-kinerja', [\App\Http\Controllers\Admin\AdminEvaluasiKinerjaController::class, 'store'])->name('evaluasi-kinerja.store');
        Route::get('/evaluasi-kinerja/{evaluasiKinerja}/cetak', [\App\Http\Controllers\Admin\AdminEvaluasiKinerjaController::class, 'cetak'])->name('evaluasi-kinerja.cetak');
        Route::delete('/evaluasi-kinerja/{evaluasiKinerja}', [\App\Http\Controllers\Admin\AdminEvaluasiKinerjaController::class, 'destroy'])->name('evaluasi-kinerja.destroy');

        // Export Excel (Admin)
        Route::get('/export/{pegawai}/{bulan?}', [ExportController::class, 'exportPegawai'])->name('export.pegawai');
        Route::get('/export-semua/{bulan?}', [ExportController::class, 'exportSemua'])->name('export.semua');
        Route::get('/export-cuti', [ExportController::class, 'exportCutiAdmin'])->name('export.cuti');
    });
});
