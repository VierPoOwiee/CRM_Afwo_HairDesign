@extends('layouts.app')

@section('title', 'Rekap Komisi Staf')

@section('content')
    @include('laporan._nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rekap Komisi Staf</h1>
            <p class="mt-1 text-sm text-gray-500">Total komisi yang harus dibayarkan ke semua staf.</p>
        </div>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.rekap-komisi'),
        'resetUrl' => route('laporan.rekap-komisi', ['preset' => 'bulan-ini']),
        'showExtraFilters' => false,
    ])

    @include('laporan._rekap-komisi-body')
@endsection
