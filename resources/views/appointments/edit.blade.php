@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('nav-active', 'appointment')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-pelanggan.css') }}">
    <link rel="stylesheet" href="{{ asset('css/add-appointment.css') }}">
@endpush

@section('content')
        <div class="edit-header-wrapper">
            <a href="{{ route('appointment.index', ['bulan' => $appointment->tanggal?->format('Y-m')]) }}" id="btn-back-nav" class="back-arrow-link" aria-label="Kembali ke Kalender">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="edit-title-group">
                <h1 class="edit-page-title" id="appointment-form-title">Ubah Jadwal Kedatangan</h1>
                <p class="edit-page-subtitle" id="appointment-form-subtitle">Perbarui jadwal temu {{ $appointment->layananNama() }} sesuai kebutuhan terkini.</p>
            </div>
        </div>

        <form action="{{ route('appointment.update', $appointment) }}" method="POST" id="appointmentForm" class="card edit-form-card" novalidate>
            @csrf
            @method('PUT')
            @include('appointments._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
@endsection