@extends('layouts.app')

@section('title', 'Edit Karyawan')
@section('nav-active', 'workers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-worker.css') }}">
@endpush

@section('content')
    <div class="edit-header-wrapper">
        <a href="{{ route('karyawan.index') }}" class="back-arrow-link" aria-label="Kembali ke Data Karyawan">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
        </a>
        <div class="edit-title-group">
            <h1 class="edit-page-title">Edit Karyawan</h1>
            <p class="edit-page-subtitle">Perbarui data {{ $karyawan->nama }}.</p>
        </div>
    </div>

    <form id="form-edit-worker" method="POST" action="{{ route('karyawan.update', $karyawan) }}" class="card edit-form-card">
        @csrf
        @method('PUT')
        @include('karyawans._form', ['submitLabel' => 'Simpan Perubahan'])
    </form>
@endsection