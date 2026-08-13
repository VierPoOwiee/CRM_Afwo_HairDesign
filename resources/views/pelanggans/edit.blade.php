@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content')
    <div class="flex items-center gap-4">
        <a href="{{ route('pelanggan.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Pelanggan</h1>
            <p class="mt-1 text-sm text-gray-500">Perbarui data {{ $pelanggan->nama }}.</p>
        </div>
    </div>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('pelanggan.update', $pelanggan) }}" method="POST">
            @csrf
            @method('PUT')
            @include('pelanggans._form', ['submitLabel' => 'Simpan Perubahan'])
        </form>
    </div>
@endsection
