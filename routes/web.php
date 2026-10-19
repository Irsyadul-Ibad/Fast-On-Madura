<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PbpdController;
use App\Http\Controllers\PbpdUploadController;
use App\Http\Controllers\PerluasanController;
use App\Http\Controllers\VendorKonstruksiController;
use App\Http\Controllers\VendorTiangController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

// Hanya untuk tamu. Tidak ada route register.
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Hanya untuk user yang sudah login
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    
    Route::get('/dashboard', function (Request $request) {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (in_array($user->role?->role_code, ['VENDOR_TIANG', 'VENDOR_TIANG2', '5170T1', '5170T2', '5170T3', '5170T4', '5170T5', '5170T6', '5170T7', '5170T8', '5170T9'], true)) {
            return redirect()->route('vendor.tiang');
        }
        if (in_array($user->role?->role_code, ['VENDOR_KONSTRUKSI', 'VENDOR_KONSTRUKSI2', '5170K1', '5170K2', '5170K3', '5170K4', '5170K5', '5170K6', '5170K7'], true)) {
            return redirect()->route('vendor.konstruksi');
        }

        abort_unless($user->hasPermission('dashboard.view'), 403);
        return app(DashboardController::class)->index();
    })->name('dashboard');
    Route::get('/laporan', [LaporanController::class, 'index'])
        ->middleware('permission:laporan.view')->name('laporan');
    Route::get('/laporan/export', [LaporanController::class, 'export'])
        ->middleware('permission:laporan.view')->name('laporan.export');
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])
        ->middleware('permission:notifikasi.view')->name('notifikasi');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'readAll'])
        ->middleware('permission:notifikasi.view')->name('notifikasi.read-all');
    Route::post('/notifikasi/{notification}/baca', [NotifikasiController::class, 'read'])
        ->middleware('permission:notifikasi.view')->name('notifikasi.read');

    // Halaman asli
    Route::get('/pbpd', [PbpdController::class, 'index'])
        ->middleware('permission:pbpd.view')
        ->name('pbpd.index');
    Route::post('/pbpd/{pelanggan}/rab', [PbpdController::class, 'updateRab'])
        ->middleware('permission:pbpd.view')
        ->name('pbpd.rab.update');
    Route::post('/pbpd/{pelanggan}/detail', [PbpdController::class, 'updateDetail'])
        ->middleware('permission:perluasan.jtm.view')
        ->name('pbpd.detail.update');
    Route::post('/pbpd/{pelanggan}/kirim-vendor', [PbpdController::class, 'kirimVendor'])
        ->middleware('permission:perencanaan.process')
        ->name('pbpd.vendor.send');
    Route::post('/pbpd/{pelanggan}/kirim-konstruksi', [PbpdController::class, 'kirimKonstruksi'])
        ->middleware('permission:konstruksi.process')
        ->name('pbpd.konstruksi.send');
    Route::post('/pbpd/{pelanggan}/hasil-transaksi', [PbpdController::class, 'uploadHasilTransaksi'])
        ->middleware('permission:pengoperasian.upload')
        ->name('pbpd.transaksi.result.upload');
    Route::get('/pbpd/{pelanggan}/hasil-transaksi/{index}', [PbpdController::class, 'hasilTransaksiFile'])
        ->name('pbpd.transaksi.result.file');
    Route::delete('/pbpd/{pelanggan}/hasil-transaksi/{index}', [PbpdController::class, 'hapusHasilTransaksi'])
        ->middleware('permission:pengoperasian.upload')->name('pbpd.transaksi.result.delete');
    Route::post('/pbpd/{pelanggan}/hasil-jaringan', [PbpdController::class, 'uploadHasilJaringan'])
        ->middleware('permission:jaringan.upload')
        ->name('pbpd.jaringan.result.upload');
    Route::get('/pbpd/{pelanggan}/hasil-jaringan/{index}', [PbpdController::class, 'hasilJaringanFile'])
        ->name('pbpd.jaringan.result.file');
    Route::delete('/pbpd/{pelanggan}/hasil-jaringan/{index}', [PbpdController::class, 'hapusHasilJaringan'])
        ->middleware('permission:jaringan.upload')->name('pbpd.jaringan.result.delete');
    Route::post('/pbpd/{pelanggan}/syarat/{jenis}', [PbpdController::class, 'uploadSyarat'])
        ->middleware('permission:permintaan.create')->name('pbpd.syarat.upload');
    Route::get('/pbpd/{pelanggan}/syarat/{jenis}/{index}', [PbpdController::class, 'syaratFile'])
        ->name('pbpd.syarat.file');
    Route::get('/vendor/tiang', [VendorTiangController::class, 'index'])
        ->middleware('permission:vendor.tiang.view')
        ->name('vendor.tiang');
    Route::get('/vendor/tiang/riwayat', [VendorTiangController::class, 'history'])
        ->middleware('permission:vendor.tiang.history')
        ->name('vendor.tiang.history');
    Route::post('/vendor/tiang/{pelanggan}/laporan', [VendorTiangController::class, 'kirimLaporan'])
        ->middleware('permission:vendor.tiang.process')
        ->name('vendor.tiang.report');
    Route::get('/vendor/tiang/{pelanggan}/wo', [VendorTiangController::class, 'wo'])
        ->name('vendor.tiang.wo');
    Route::get('/vendor/tiang/{pelanggan}/laporan/{index}', [VendorTiangController::class, 'laporanFile'])
        ->name('vendor.tiang.report.file');
    Route::post('/vendor/tiang/{pelanggan}/hasil', [VendorTiangController::class, 'uploadHasil'])
        ->middleware('permission:perencanaan.process')
        ->name('vendor.tiang.result.upload');
    Route::get('/vendor/tiang/{pelanggan}/hasil/{index}', [VendorTiangController::class, 'hasilFile'])
        ->name('vendor.tiang.result.file');
    Route::get('/vendor/konstruksi', [VendorKonstruksiController::class, 'index'])
        ->middleware('permission:vendor.konstruksi.view')->name('vendor.konstruksi');
    Route::get('/vendor/konstruksi/riwayat', [VendorKonstruksiController::class, 'history'])
        ->middleware('permission:vendor.konstruksi.history')->name('vendor.konstruksi.history');
    Route::post('/vendor/konstruksi/{pelanggan}/laporan', [VendorKonstruksiController::class, 'kirimLaporan'])
        ->middleware('permission:vendor.konstruksi.process')->name('vendor.konstruksi.report');
    Route::delete('/vendor/konstruksi/{pelanggan}/laporan', [VendorKonstruksiController::class, 'hapusLaporan'])
        ->middleware('permission:vendor.konstruksi.process')->name('vendor.konstruksi.report.delete');
    Route::post('/vendor/konstruksi/{pelanggan}/hasil', [VendorKonstruksiController::class, 'uploadHasil'])
        ->middleware('permission:perencanaan.process')->name('vendor.konstruksi.result.upload');
    Route::get('/vendor/konstruksi/{pelanggan}/laporan/{index}', [VendorKonstruksiController::class, 'laporanFile'])
        ->name('vendor.konstruksi.report.file');
    Route::get('/vendor/konstruksi/{pelanggan}/berkas/{index}', [VendorKonstruksiController::class, 'deliveryFile'])
        ->name('vendor.konstruksi.delivery.file');
    Route::post('/vendor/konstruksi/{pelanggan}/hasil-konstruksi', [VendorKonstruksiController::class, 'uploadHasilKonstruksi'])
        ->middleware('permission:konstruksi.process')->name('vendor.konstruksi.construction-result.upload');
    Route::get('/vendor/konstruksi/{pelanggan}/hasil-konstruksi/{index}', [VendorKonstruksiController::class, 'hasilKonstruksiFile'])
        ->name('vendor.konstruksi.result.file');
    Route::delete('/vendor/konstruksi/{pelanggan}/hasil-konstruksi/{index}', [VendorKonstruksiController::class, 'hapusHasilKonstruksi'])
        ->middleware('permission:konstruksi.process')->name('vendor.konstruksi.result.delete');
    Route::post('/vendor/tiang/{pelanggan}/hasil/{index}/hapus', [VendorTiangController::class, 'hapusHasil'])
        ->middleware('permission:perencanaan.process')
        ->name('vendor.tiang.result.delete');
    Route::post('/perluasan/jtm', [VendorTiangController::class, 'uploadHasilFromExpansion'])
        ->middleware('permission:perencanaan.process')
        ->name('perluasan.jtm.result.upload');
    Route::post('/perluasan/jtr', [VendorTiangController::class, 'uploadHasilFromExpansion'])
        ->middleware('permission:perencanaan.process')
        ->name('perluasan.jtr.result.upload');
    Route::post('/tanpa/perluasan', [VendorTiangController::class, 'uploadHasilFromExpansion'])
        ->middleware('permission:perencanaan.process')
        ->name('tanpa.perluasan.result.upload');
    Route::post('/pbpd/kirim', [PbpdController::class, 'kirim'])
        ->middleware('permission:pbpd.view')
        ->name('pbpd.kirim');

    Route::get('/pbpd-upload', [PbpdUploadController::class, 'index'])
        ->middleware('permission:pbpd.upload')
        ->name('pbpd.upload');
    Route::post('/pbpd-upload', [PbpdUploadController::class, 'store'])
        ->middleware('permission:pbpd.upload');

    Route::get('/perluasan/jtm', [PerluasanController::class, 'index'])
        ->middleware('permission:perluasan.jtm.view')
        ->defaults('tujuan', 'JTM')
        ->name('perluasan.jtm');
    Route::get('/perluasan/jtr', [PerluasanController::class, 'index'])
        ->middleware('permission:perluasan.jtr.view')
        ->defaults('tujuan', 'JTR')
        ->name('perluasan.jtr');
    Route::get('/tanpa/perluasan', [PerluasanController::class, 'index'])
        ->middleware('permission:tanpa.perluasan.view')
        ->defaults('tujuan', 'TANPA_PERLUASAN')
        ->name('tanpa.perluasan');

    // Halaman sementara untuk menu yang belum dikerjakan
    foreach (config('menu') as $m) {
        if (in_array($m['route'], ['perluasan.jtm', 'perluasan.jtr', 'tanpa.perluasan', 'vendor.konstruksi', 'vendor.konstruksi.history'], true)) {
            continue;
        }
        if ($m['route'] === 'dashboard' || ($m['placeholder'] ?? true) === false) {
            continue;
        }

        Route::get($m['path'], fn () => view('halaman', ['judul' => $m['label']]))
            ->middleware('permission:' . $m['permission'])
            ->name($m['route']);
    }
});
