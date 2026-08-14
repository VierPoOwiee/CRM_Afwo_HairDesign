<?php

use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\PelangganController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/pelanggan');

Route::resource('pelanggan', PelangganController::class)->only([
    'index',
    'create',
    'store',
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

Route::resource('karyawan', KaryawanController::class)->only([
    'index',
    'create',
    'store',
    'edit',
    'update',
    'destroy',
]);
