@extends('layouts.app')

@section('title', 'Rekap Komisi Staf')

@section('nav-active', 'reports-commission')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rekap-komisi.css') }}">
@endpush

@section('content')

    <div class="report-intro-section">
        <h1 class="report-title">Rekap Komisi Staf</h1>
        <p class="report-subtitle">Total komisi yang harus dibayarkan ke semua staf.</p>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.rekap-komisi'),
        'resetUrl' => route('laporan.rekap-komisi', ['preset' => 'bulan-ini']),
        'showExtraFilters' => false,
    ])

    @include('laporan._rekap-komisi-body')

@endsection