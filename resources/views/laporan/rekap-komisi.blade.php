@extends('layouts.app')

@section('title', 'Rekap Komisi Staf')

@section('content')
    @include('laporan._nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rekap Komisi Staf</h1>
            <p class="mt-1 text-sm text-gray-500">Total komisi yang harus dibayarkan ke semua staf.</p>
        </div>
        <div class="flex shrink-0 flex-wrap items-center gap-2 no-print">
            <a href="{{ route('laporan.pendapatan-karyawan') }}"
               class="inline-flex items-center gap-2 rounded-md bg-accent/15 px-3 py-2 text-sm font-semibold text-accent-text ring-1 ring-inset ring-accent/30 hover:bg-accent/20">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-5a2 2 0 00-2-2h-2m-6 7V9a2 2 0 012-2h2a2 2 0 012 2v8a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Pendapatan Bulanan
            </a>
            <a href="{{ route('laporan.rekap-komisi.cetak', request()->query()) }}"
               class="inline-flex items-center gap-2 rounded-md bg-dark px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak + Slip Karyawan
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-md bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak
            </button>
        </div>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.rekap-komisi'),
        'resetUrl' => route('laporan.rekap-komisi', ['preset' => 'bulan-ini']),
        'showExtraFilters' => false,
    ])

    @include('laporan._rekap-komisi-body')
@endsection
