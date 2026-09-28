@extends('layouts.app')

@section('title', 'Cetak Laporan + Slip Pendapatan')

@section('nav-active', 'reports-commission')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rekap-komisi.css') }}">
@endpush

@section('content')

    <div class="report-intro-section report-toolbar no-print">
        <div>
            <h1 class="report-title">Cetak Laporan + Slip Pendapatan</h1>
            <p class="report-subtitle">
                Halaman 1 dst: Laporan Penjualan &amp; Rekap Komisi utama. Setelah itu: Slip Pendapatan, 1 halaman per karyawan.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
            <a href="{{ route('laporan.rekap-komisi', request()->only(['preset', 'dari', 'sampai'])) }}" class="btn-view-slip" style="text-decoration:none;">Kembali</a>
            <button onclick="window.print()" class="btn-filter-dark">Cetak Semua</button>
        </div>
    </div>

    {{-- ===== Bagian 1: halaman-halaman laporan utama ===== --}}
    @include('laporan._rekap-komisi-body')

    {{-- ===== Bagian 2: slip pendapatan, 1 halaman per karyawan (page-break sebelum tiap slip) ===== --}}
    @foreach ($slips as $item)
        <div class="page-break">
            @include('laporan._slip-pendapatan', [
                'karyawan' => $item['karyawan'],
                'slip' => $item['slip'],
                'dari' => $dari,
                'sampai' => $sampai,
            ])
        </div>
    @endforeach

@endsection