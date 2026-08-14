@extends('layouts.app')

@section('title', 'Data Layanan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Layanan</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $layanans->total() }} layanan terdaftar.
            </p>
        </div>

        <form action="{{ route('layanan.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Cari nama layanan, kategori..."
                   class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            <button type="submit"
                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cari
            </button>
        </form>
    </div>

    @if ($layanans->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data layanan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-gray-500">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('layanan.index') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('layanan.create') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    Tambah layanan pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($layanans as $l)
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium text-gray-900">{{ $l->nama_layanan }}</p>
                        @if ($l->termasuk_potong)
                            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800">Potong</span>
                        @endif
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 font-medium text-violet-700">{{ $l->kategori }}</span>
                        @if ($l->aktif)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">Aktif</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">Nonaktif</span>
                        @endif
                    </div>

                    <ul class="mt-3 flex-1 space-y-1.5 border-t border-gray-100 pt-3">
                        @forelse ($l->hargaLayanan as $h)
                            <li class="flex flex-col gap-0.5 text-sm">
                                <span class="font-medium text-gray-900">
                                    <span class="text-xs text-gray-400">{{ $h->varian }}:</span>
                                    {{ $h->labelHargaDasar() }}
                                </span>
                                @if ($h->labelKomisi())
                                    <span class="text-xs text-gray-500">Komisi: {{ $h->labelKomisi() }}</span>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-gray-400">Belum ada varian harga.</li>
                        @endforelse
                    </ul>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('layanan.show', $l) }}" class="font-medium text-blue-600 hover:text-blue-800">
                            Detail
                        </a>
                        <a href="{{ route('layanan.edit', $l) }}" class="font-medium text-violet-600 hover:text-violet-800">
                            Edit
                        </a>
                        <form action="{{ route('layanan.destroy', $l) }}" method="POST"
                              class="ml-auto"
                              onsubmit="return confirm('Hapus layanan &quot;{{ addslashes($l->nama_layanan) }}&quot; beserta semua varian harganya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($layanans->hasPages())
            <div class="mt-4">
                {{ $layanans->links() }}
            </div>
        @endif
    @endif
@endsection
