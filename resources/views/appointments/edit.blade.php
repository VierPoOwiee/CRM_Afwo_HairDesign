@extends('layouts.app')

@section('title', 'Edit Appointment')

@section('content')
    <div class="flex items-center gap-4">
        <a href="{{ route('appointment.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Appointment</h1>
            <p class="mt-1 text-sm text-gray-500">Perbarui janji temu {{ $appointment->nama }}.</p>
        </div>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('appointment.update', $appointment) }}" method="POST" data-appointment-id="{{ $appointment->id ?? '' }}">
            @csrf
            @method('PUT')
            @include('appointments._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection