@extends('layouts.app')

@section('title', 'Detail ' . $pelanggan->nama)

@section('content')
    <div class="flex items-center gap-4">
        <a href="{{ route('pelanggan.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Pelanggan</h1>
            <p class="mt-1 text-sm text-gray-500">Informasi lengkap {{ $pelanggan->nama }}.</p>
        </div>
    </div>

    <div class="mt-6 space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Nama</p>
                <p class="mt-1 font-medium text-gray-900">{{ $pelanggan->nama }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">No. WhatsApp</p>
                <p class="mt-1 text-gray-900">
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $pelanggan->no_wa) }}"
                       target="_blank"
                       class="text-green-600 hover:text-green-700 hover:underline">
                        {{ $pelanggan->no_wa }}
                    </a>
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Username Instagram</p>
                <p class="mt-1 text-gray-900">{{ $pelanggan->username_instagram ? ltrim($pelanggan->username_instagram, '@') : '-' }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Kelamin</p>
                <p class="mt-1 text-gray-900">
                    {{ $pelanggan->jenis_kelamin ? ($pelanggan->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') : '-' }}
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Jenis Rambut</p>
                <p class="mt-1 text-gray-900">{{ $pelanggan->jenis_rambut ?? '-' }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kondisi Rambut</p>
                <p class="mt-1 text-gray-900">{{ $pelanggan->kondisi_rambut ?? '-' }}</p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Alamat</p>
                <p class="mt-1 text-gray-900">{{ $pelanggan->alamat ?? '-' }}</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan Khusus</p>
                @if ($pelanggan->catatan_khusus)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800">
                        Ada catatan
                    </span>
                @endif
            </div>
            <p class="mt-2 whitespace-pre-line text-gray-900">
                {{ $pelanggan->catatan_khusus ?: 'Tidak ada catatan khusus.' }}
            </p>
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <a href="{{ route('pelanggan.edit', $pelanggan) }}"
               class="inline-flex justify-center rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                Edit Data
            </a>
            <a href="{{ route('pelanggan.index') }}"
               class="inline-flex justify-center rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </div>
@endsection
