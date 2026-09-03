@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Data Karyawan</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $karyawans->total() }} karyawan terdaftar.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <form action="{{ route('karyawan.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="Cari nama, kontak..."
                       class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <button type="submit"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    Cari
                </button>
            </form>
            <a href="{{ route('karyawan.create') }}"
               class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                <span class="text-lg leading-none">+</span>
                <span class="hidden sm:inline">Tambah</span>
            </a>
        </div>
    </div>

    @if ($karyawans->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-text-secondary">Belum ada data karyawan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-text-muted">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('karyawan.index') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('karyawan.create') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    Tambah karyawan pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($karyawans as $k)
                <div class="flex flex-col rounded-xl border border-gray-200 bg-card p-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <p class="font-medium text-text-primary">{{ $k->nama }}</p>

                    <dl class="mt-2 flex-1 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Kontak</dt>
                            <dd class="text-right text-text-primary">
                                @if ($k->kontak)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $k->kontak) }}" target="_blank" class="text-success hover:underline">
                                        {{ $k->kontak }}
                                    </a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-text-muted">Skema Komisi</dt>
                            <dd class="text-right text-text-primary">
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    Persen omset ({{ $k->persen_komisi_harian }}%)
                                @else
                                    Per layanan
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('karyawan.edit', $k) }}" class="font-medium text-accent-text hover:text-accent">
                            Edit
                        </a>
                        <form action="{{ route('karyawan.destroy', $k) }}" method="POST"
                              class="ml-auto"
                              onsubmit="return confirm('Hapus karyawan &quot;{{ addslashes($k->nama) }}&quot;?')">
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

        @if ($karyawans->hasPages())
            <div class="mt-4">
                {{ $karyawans->links() }}
            </div>
        @endif
    @endif
@endsection
