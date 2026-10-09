@extends('layouts.app')

@section('title', 'Rekap Komisi Staf')

@section('nav-active', 'reports-commission')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rekap-komisi.css') }}">
@endpush

@section('content')

    <div class="rekap-header-row">
        <div class="rekap-title-group">
            <h1 class="rekap-title">Rekap Komisi Staf</h1>
            <p class="rekap-subtitle">Total komisi yang harus dibayarkan ke semua staf.</p>
        </div>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.rekap-komisi'),
        'resetUrl' => route('laporan.rekap-komisi', ['preset' => 'bulan-ini']),
        'filterField' => 'rekap-filter-card',
    ])

    @include('laporan._rekap-komisi-body')

@endsection