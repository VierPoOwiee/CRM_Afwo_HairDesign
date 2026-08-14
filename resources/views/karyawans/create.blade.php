@extends('layouts.app')

@section('title', 'Tambah Karyawan')

@section('content')
    <h1 class="text-2xl font-bold text-gray-900">Tambah Karyawan</h1>
    <p class="mt-1 text-sm text-gray-500">Lengkapi data karyawan baru di bawah ini.</p>

    <div class="mt-6 max-w-3xl rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
        <form action="{{ route('karyawan.store') }}" method="POST">
            @csrf
            @include('karyawans._form', ['submitLabel' => 'Simpan Karyawan'])
        </form>
    </div>
@endsection
