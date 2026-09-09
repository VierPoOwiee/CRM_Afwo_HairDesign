@extends('layouts.app')

@section('title', 'Arsip Pelanggan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Arsip Pelanggan</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $pelanggans->total() }} pelanggan diarsipkan. Data riwayat transaksi tetap utuh.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('pelanggan.arsip') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cari nama, no. WA, IG, alamat..."
                       class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <button type="submit"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    Cari
                </button>
            </form>
            <a href="{{ route('pelanggan.index') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                <span class="text-lg leading-none">+</span>
                <span class="hidden sm:inline">Kembali</span>
            </a>
        </div>
    </div>

    @if ($pelanggans->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-text-secondary">Tidak ada pelanggan yang diarsipkan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-text-muted">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('pelanggan.arsip') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('pelanggan.index') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Kembali ke data pelanggan
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($pelanggans as $p)
                <div class="flex flex-col rounded-xl border border-gray-200 bg-card p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-2">
                        <p class="font-medium text-text-primary">{{ $p->nama }}</p>
                        <span class="shrink-0 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-text-muted">
                            Diarsipkan {{ $p->deleted_at->format('d M Y') }}
                        </span>
                    </div>

                    <dl class="mt-2 flex-1 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">WhatsApp</dt>
                            <dd class="text-right text-text-primary">{{ $p->no_wa }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Instagram</dt>
                            <dd class="text-right text-text-primary">{{ $p->username_instagram ? ltrim($p->username_instagram, '@') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Alamat</dt>
                            <dd class="max-w-[60%] break-words text-right text-text-primary">{{ $p->alamat ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <form action="{{ route('pelanggan.restore', $p->id) }}" method="POST" class="ml-auto"
                              onsubmit="return confirm('Pulihkan pelanggan &quot;{{ addslashes($p->nama) }}&quot;?')">
                            @csrf
                            <button type="submit" class="font-medium text-green-600 hover:text-green-800">
                                Pulihkan
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