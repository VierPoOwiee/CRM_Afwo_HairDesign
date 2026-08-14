@extends('layouts.app')

@section('title', 'Edit Layanan')

@section('content')
    <div class="flex items-center gap-4">
        <a href="{{ route('layanan.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Layanan</h1>
            <p class="mt-1 text-sm text-gray-500">Perbarui {{ $layanan->nama_layanan }} beserta varian harga &amp; komisinya.</p>
        </div>
    </div>

    <div class="mt-6 max-w-5xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('layanan.update', $layanan) }}" method="POST">
            @csrf
            @method('PUT')
            @include('layanans._form', [
                'submitLabel' => 'Simpan Perubahan',
                'hargaRows' => $layanan->hargaLayanan,
            ])
        </form>
    </div>
@endsection
