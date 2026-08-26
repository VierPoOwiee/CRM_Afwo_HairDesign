@extends('layouts.app')

@section('title', 'Cetak Laporan + Slip Pendapatan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between no-print">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Cetak Laporan + Slip Pendapatan</h1>
            <p class="mt-1 text-sm text-gray-500">
                Halaman 1 dst: Laporan Penjualan &amp; Rekap Komisi utama. Setelah itu: Slip Pendapatan, 1 halaman per karyawan.
            </p>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ route('laporan.rekap-komisi', request()->only(['preset', 'dari', 'sampai'])) }}"
               class="inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Kembali
            </a>
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-md bg-violet-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Semua
            </button>
        </div>
    </div>

    {{-- ===== Bagian 1: halaman-halaman laporan utama ===== --}}
    @include('laporan._rekap-komisi-body')

    {{-- ===== Bagian 2: slip pendapatan, 1 halaman per karyawan (page-break sebelum tiap slip) ===== --}}
    @foreach ($slips as $item)
        <div class="page-break mt-10">
            @include('laporan._slip-pendapatan', [
                'karyawan' => $item['karyawan'],
                'slip' => $item['slip'],
                'dari' => $dari,
                'sampai' => $sampai,
            ])
        </div>
    @endforeach
@endsection
