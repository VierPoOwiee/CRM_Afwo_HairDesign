@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Data Pelanggan</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $pelanggans->total() }} pelanggan terdaftar.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('pelanggan.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cari nama, no. WA, IG, alamat..."
                       class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <button type="submit"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    Cari
                </button>
            </form>
            <a href="{{ route('pelanggan.create') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                <span class="text-lg leading-none">+</span>
                <span class="hidden sm:inline">Tambah</span>
            </a>
        </div>
    </div>

    @if ($pelanggans->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-text-secondary">Belum ada data pelanggan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-text-muted">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('pelanggan.index') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('pelanggan.create') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    Tambah pelanggan pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($pelanggans as $p)
                <div class="flex flex-col rounded-xl border border-gray-200 bg-card p-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <p class="font-medium text-text-primary">{{ $p->nama }}</p>

                    @if ($p->catatan_khusus)
                        <a href="{{ route('pelanggan.show', $p) }}"
                           class="mt-1.5 block line-clamp-2 rounded-md bg-amber-50 px-2.5 py-1.5 text-xs text-amber-800 hover:bg-amber-100">
                            <span class="font-semibold">Catatan:</span> {{ $p->catatan_khusus }}
                        </a>
                    @endif

                    <dl class="mt-2 flex-1 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">WhatsApp</dt>
                            <dd class="text-right text-text-primary">
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $p->no_wa) }}" target="_blank" class="text-success hover:underline">
                                    {{ $p->no_wa }}
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Instagram</dt>
                            <dd class="text-right text-text-primary">{{ $p->username_instagram ? ltrim($p->username_instagram, '@') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Jenis Kelamin</dt>
                            <dd class="text-right text-text-primary">{{ $p->jenis_kelamin ? ($p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Jenis Rambut</dt>
                            <dd class="text-right text-text-primary">{{ $p->jenis_rambut ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Alamat</dt>
                            <dd class="max-w-[60%] break-words text-right text-text-primary">{{ $p->alamat ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('pelanggan.show', $p) }}" class="font-medium text-blue-600 hover:text-blue-800">
                            Detail
                        </a>
                        <a href="{{ route('pelanggan.edit', $p) }}" class="font-medium text-accent-text hover:text-accent">
                            Edit
                        </a>
                        <form action="{{ route('pelanggan.destroy', $p) }}" method="POST"
                              class="ml-auto"
                              onsubmit="return confirm('Hapus pelanggan &quot;{{ addslashes($p->nama) }}&quot;?')">
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

        @if ($pelanggans->hasPages())
            <div class="mt-4">
                {{ $pelanggans->links() }}
            </div>
        @endif
    @endif
@endsection
