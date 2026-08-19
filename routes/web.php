<?php

use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LaporanKomisiController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/pelanggan');

Route::resource('pelanggan', PelangganController::class)->only([
    'index',
    'create',
    'store',
    'show',
    'edit',
    'update',
    'destroy',
]);

Route::resource('layanan', LayananController::class)->only([
    'index',
    'create',
    'store',
    'show',
    'edit',
    'update',
    'destroy',
]);

Route::resource('produk', ProdukController::class)->only([
    'index',
    'create',
    'store',
    'show',
    'edit',
    'update',
    'destroy',
]);

Route::resource('karyawan', KaryawanController::class)->only([
    'index',
    'create',
    'store',
    'edit',
    'update',
    'destroy',
]);

Route::resource('transaksi', TransaksiController::class)->only([
    'index',
    'create',
    'store',
    'show',
    'destroy',
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

Route::get('laporan/komisi', [LaporanKomisiController::class, 'index'])
    ->name('laporan-komisi.index');
Route::post('laporan/komisi/hitung-ulang', [LaporanKomisiController::class, 'hitungUlang'])
    ->name('laporan-komisi.hitung-ulang');
