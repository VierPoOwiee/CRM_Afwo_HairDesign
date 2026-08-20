<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardOwnerController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LaporanKomisiController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::redirect('/', '/pelanggan');

// Owner-only routes
Route::middleware(['auth', 'role:owner'])->group(function () {
    Route::get('/dashboard', [DashboardOwnerController::class, 'index'])->name('dashboard');

    Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/laporan/pelanggan-aktif', [LaporanController::class, 'pelangganAktif'])->name('laporan.pelanggan-aktif');
    Route::get('/laporan/pelanggan-aktif/{pelanggan}', [LaporanController::class, 'pelangganRiwayat'])->name('laporan.pelanggan-riwayat');
    Route::get('/laporan/rekap-komisi', [LaporanController::class, 'rekapKomisi'])->name('laporan.rekap-komisi');
    Route::post('/laporan/rekap-komisi/hitung-ulang', [LaporanController::class, 'hitungUlang'])->name('laporan.rekap-komisi.hitung-ulang');
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
