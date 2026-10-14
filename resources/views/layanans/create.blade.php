@extends('layouts.app')

@section('title', 'Tambah Layanan')

@section('nav-active', 'services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-layanan.css') }}">
@endpush

@section('content')
    <div class="edit-header-wrapper">
        <a href="{{ route('layanan.index') }}" class="back-arrow-link" aria-label="Kembali ke Data Layanan">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        <div class="edit-title-group">
            <h1 class="edit-layanan-title" id="edit-layanan-title">Tambah Layanan</h1>
            <p class="edit-layanan-subtitle" id="edit-layanan-subtitle">Lengkapi layanan beserta varian harga &amp; komisinya.</p>
        </div>
    </div>

    @include('layanans._form', ['submitLabel' => 'Simpan Layanan'])
@endsection