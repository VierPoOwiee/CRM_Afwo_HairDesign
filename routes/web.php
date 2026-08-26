<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardOwnerController;
use App\Http\Controllers\InsightController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Profile
Route::middleware('auth')->group(function () {
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil/info', [ProfileController::class, 'updateInfo'])->name('profile.info.update');
    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
});

Route::redirect('/', '/pelanggan');

// Owner-only routes
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [DashboardOwnerController::class, 'index'])->name('dashboard');

    Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/laporan/pelanggan-aktif', [LaporanController::class, 'pelangganAktif'])->name('laporan.pelanggan-aktif');
    Route::get('/laporan/pelanggan-aktif/{pelanggan}', [LaporanController::class, 'pelangganRiwayat'])->name('laporan.pelanggan-riwayat');
    Route::get('/laporan/rekap-komisi', [LaporanController::class, 'rekapKomisi'])->name('laporan.rekap-komisi');
    Route::get('/laporan/pendapatan-karyawan', [LaporanController::class, 'pendapatanKaryawan'])->name('laporan.pendapatan-karyawan');
    Route::get('/laporan/rekap-komisi/cetak', [LaporanController::class, 'cetakRekapKomisi'])->name('laporan.rekap-komisi.cetak');
    Route::get('/laporan/rekap-komisi/staf/{karyawan}', [LaporanController::class, 'slipPendapatan'])->name('laporan.rekap-komisi.slip');
    Route::post('/laporan/rekap-komisi/hitung-ulang', [LaporanController::class, 'hitungUlang'])->name('laporan.rekap-komisi.hitung-ulang');
    Route::post('/laporan/insight/generate', [InsightController::class, 'generate'])->name('laporan.insight.generate');
    Route::post('/laporan/insight/tanya', [InsightController::class, 'tanya'])->name('laporan.insight.tanya');
});

// Existing routes (accessible to all for now)
Route::resource('pelanggan', PelangganController::class)->only([
    'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
]);

Route::resource('layanan', LayananController::class)->only([
    'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
]);

Route::resource('produk', ProdukController::class)->only([
    'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
]);

Route::resource('karyawan', KaryawanController::class)->only([
    'index', 'create', 'store', 'edit', 'update', 'destroy',
]);

Route::resource('transaksi', TransaksiController::class)->only([
    'index', 'create', 'store', 'show', 'destroy',
]);

Route::put('transaksi/{transaksi}/batal', [TransaksiController::class, 'cancel'])
    ->name('transaksi.cancel');

Route::put('transaksi/{transaksi}/komisi', [TransaksiController::class, 'updateKomisi'])
    ->name('transaksi.komisi.update');

Route::put('transaksi/{transaksi}/komisi-staf', [TransaksiController::class, 'updateKomisiStaf'])
    ->name('transaksi.komisi-staf.update');

Route::get('api/pelanggan/search', [TransaksiController::class, 'searchPelanggan'])
    ->name('api.pelanggan.search');
Route::post('api/pelanggan', [TransaksiController::class, 'storePelanggan'])
    ->name('api.pelanggan.store');
Route::get('api/layanan/search', [TransaksiController::class, 'searchLayanan'])
    ->name('api.layanan.search');
Route::get('api/produk/search', [TransaksiController::class, 'searchProduk'])
    ->name('api.produk.search');

// Old laporan komisi route → redirect to new location
Route::get('laporan/komisi', function () {
    return redirect()->route('laporan.rekap-komisi', request()->query());
})->name('laporan-komisi.index');

Route::post('laporan/komisi/hitung-ulang', function () {
    return redirect()->route('laporan.rekap-komisi.hitung-ulang', request()->query());
})->name('laporan-komisi.hitung-ulang');
