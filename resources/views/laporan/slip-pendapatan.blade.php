@extends('layouts.app')

@section('title', 'Slip Pendapatan — ' . $karyawan->nama)

@section('nav-active', 'reports-commission')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rekap-komisi.css') }}">
@endpush

@section('content')

    <div class="report-intro-section no-print">
        <a href="{{ route('laporan.rekap-komisi', request()->only(['preset', 'dari', 'sampai'])) }}"
           class="btn-view-slip" style="align-self:flex-start; text-decoration:none;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 19l-7-7 7-7"/></svg>
            &nbsp;Kembali ke Rekap Komisi
        </a>
        <h1 class="report-title">Slip Pendapatan — {{ $karyawan->nama }}</h1>
        <p class="report-subtitle">Rincian gaji pokok + komisi untuk periode terpilih.</p>
    </div>

    <div class="no-print">
        @include('laporan._filter-periode', [
            'action' => route('laporan.rekap-komisi.slip', ['karyawan' => $karyawan->id]),
            'resetUrl' => route('laporan.rekap-komisi.slip', ['karyawan' => $karyawan->id, 'preset' => 'bulan-ini']),
            'showExtraFilters' => false,
        ])
    </div>

    @include('laporan._slip-pendapatan', [
        'karyawan' => $karyawan,
        'slip' => $slip,
        'dari' => $dari,
        'sampai' => $sampai,
    ])

@endsection