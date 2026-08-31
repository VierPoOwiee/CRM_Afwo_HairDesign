@extends('layouts.app')

@section('title', 'Data Layanan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Data Layanan</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $layanans->total() }} layanan terdaftar.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('layanan.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cari nama layanan..."
                       class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                @if ($kategoriFilter !== '')
                    <input type="hidden" name="kategori" value="{{ $kategoriFilter }}">
                @endif
                <button type="submit"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    Cari
                </button>
            </form>
            <a href="{{ route('layanan.create') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                <span class="text-lg leading-none">+</span>
                <span class="hidden sm:inline">Tambah</span>
            </a>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('layanan.index', array_merge(request()->except('kategori', 'page'), $q !== '' ? ['q' => $q] : [])) }}"
           class="rounded-full px-3 py-1 text-xs font-medium {{ $kategoriFilter === '' ? 'bg-dark text-white' : 'bg-badge-neutral-bg text-badge-neutral-text hover:bg-gray-200' }}">
            Semua
        </a>
        @foreach ($kategoriList as $k)
            <a href="{{ route('layanan.index', array_merge(request()->except('kategori', 'page'), ['kategori' => $k])) }}"
               class="rounded-full px-3 py-1 text-xs font-medium {{ $kategoriFilter === $k ? 'bg-dark text-white' : 'bg-badge-neutral-bg text-badge-neutral-text hover:bg-gray-200' }}">
                {{ $k }}
            </a>
        @endforeach
    </div>

    @if ($layanans->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-text-secondary">Belum ada data layanan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-text-muted">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('layanan.index') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('layanan.create') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    Tambah layanan pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($layanans as $l)
                <div class="flex flex-col rounded-xl border border-gray-200 bg-card p-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium text-text-primary">{{ $l->nama_layanan }}</p>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="rounded-full bg-accent-light px-2 py-0.5 font-medium text-accent-text">{{ $l->kategori }}</span>
                        @if ($l->aktif)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">Aktif</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">Nonaktif</span>
                        @endif
                    </div>

                    <ul class="mt-3 flex-1 space-y-1.5 border-t border-gray-100 pt-3">
                        @forelse ($l->hargaLayanan as $h)
                            <li class="flex flex-col gap-0.5 text-sm">
                                <span class="font-medium text-text-primary">
                                    <span class="text-xs text-text-muted">{{ $h->varian }}:</span>
                                    {{ $h->labelHargaDasar() }}
                                </span>
                                @if ($h->labelKomisi())
                                    <span class="text-xs text-text-muted">Komisi: {{ $h->labelKomisi() }}</span>
                                @endif
                            </li>
                        @empty
                            <li class="text-sm text-text-muted">Belum ada varian harga.</li>
                        @endforelse
                    </ul>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('layanan.show', $l) }}" class="font-medium text-blue-600 hover:text-blue-800">
                            Detail
                        </a>
                        <a href="{{ route('layanan.edit', $l) }}" class="font-medium text-accent-text hover:text-accent">
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
