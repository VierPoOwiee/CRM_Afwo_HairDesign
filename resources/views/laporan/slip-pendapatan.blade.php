@extends('layouts.app')

@section('title', 'Slip Pendapatan — ' . $karyawan->nama)

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm no-print">
                <a href="{{ route('laporan.rekap-komisi', request()->only(['preset', 'dari', 'sampai'])) }}"
                   class="inline-flex items-center gap-1 font-medium text-accent-text hover:text-accent">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Kembali ke Rekap Komisi
                </a>
            </div>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Slip Pendapatan — {{ $karyawan->nama }}</h1>
            <p class="mt-1 text-sm text-gray-500">Rincian gaji pokok + komisi untuk periode terpilih.</p>
        </div>
        <button onclick="window.print()" class="inline-flex shrink-0 items-center gap-2 self-start rounded-md bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 no-print">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak Slip
        </button>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.rekap-komisi.slip', ['karyawan' => $karyawan->id]),
        'resetUrl' => route('laporan.rekap-komisi.slip', ['karyawan' => $karyawan->id, 'preset' => 'bulan-ini']),
        'showExtraFilters' => false,
    ])

    <div class="mt-6">
        @include('laporan._slip-pendapatan', [
            'karyawan' => $karyawan,
            'slip' => $slip,
            'dari' => $dari,
            'sampai' => $sampai,
        ])
    </div>
@endsection
