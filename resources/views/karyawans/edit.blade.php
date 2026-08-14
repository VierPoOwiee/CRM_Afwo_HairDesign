@extends('layouts.app')

@section('title', 'Edit Karyawan')

@section('content')
    <div class="flex items-center gap-4">
        <a href="{{ route('karyawan.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Perbarui data {{ $karyawan->nama }}.</p>
        </div>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('karyawan.update', $karyawan) }}" method="POST">
            @csrf
            @method('PUT')
            @include('karyawans._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
