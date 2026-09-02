@extends('layouts.app')

@section('title', 'Tambah Appointment')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900">Tambah Appointment</h1>
    <p class="mt-1 text-sm text-gray-500">Catat janji temu baru untuk pelanggan.</p>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('appointment.store') }}" method="POST">
            @csrf
            @include('appointments._form', ['submitLabel' => 'Simpan Appointment'])
        </form>
    </div>
@endsection