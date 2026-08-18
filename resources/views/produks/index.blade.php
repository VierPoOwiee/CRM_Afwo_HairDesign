@extends('layouts.app')

@section('title', 'Data Produk')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Produk</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $produks->total() }} produk terdaftar.
            </p>
        </div>

        <form action="{{ route('produk.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Cari nama produk, merek..."
                   class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            @if ($kategoriFilter !== '')
                <input type="hidden" name="kategori" value="{{ $kategoriFilter }}">
            @endif
            <button type="submit"
                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cari
            </button>
        </form>
    </div>

    <div class="mb-4 flex flex-wrap items-center gap-2">
        <a href="{{ route('produk.index', array_merge(request()->except('kategori', 'page'), $q !== '' ? ['q' => $q] : [])) }}"
           class="rounded-full px-3 py-1 text-xs font-medium {{ $kategoriFilter === '' ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
            Semua
        </a>
        @foreach ($kategoriList as $key => $label)
            <a href="{{ route('produk.index', array_merge(request()->except('kategori', 'page'), ['kategori' => $key])) }}"
               class="rounded-full px-3 py-1 text-xs font-medium {{ $kategoriFilter === $key ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($stokMenipis > 0)
        <div class="mb-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <span class="font-semibold">{{ $stokMenipis }} produk stok menipis</span> (stok &le; {{ \App\Models\Produk::STOK_MENIPIS }}).
        </div>
    @endif

    @if ($produks->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data produk.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-gray-500">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('produk.index') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('produk.create') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    Tambah produk pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($produks as $p)
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm {{ $p->aktif && $p->kategori_produk === 'dijual' && $p->stokMenipis() ? 'border-amber-300 ring-1 ring-amber-200' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium text-gray-900">{{ $p->nama_produk }}</p>
                        @if ($p->aktif && $p->kategori_produk === 'dijual' && $p->stokMenipis())
                            <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-medium text-amber-800">Menipis</span>
                        @endif
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-1.5 text-xs">
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 font-medium text-violet-700">{{ $p->merek }}</span>
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 font-medium text-blue-700">{{ $p->labelKategori() }}</span>
                        @if ($p->aktif)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">Aktif</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">Nonaktif</span>
                        @endif
                    </div>

                    <dl class="mt-2 flex-1 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Harga</dt>
                            <dd class="text-right font-medium text-gray-900">{{ $p->labelHarga() }}</dd>
                        </div>
                        @if ($p->kategori_produk === 'dijual')
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Stok</dt>
                            <dd class="text-right text-gray-900">
                                {{ $p->stok }}
                                @if ($p->aktif && $p->stokMenipis())
                                    <span class="text-amber-600">(menipis)</span>
                                @endif
                            </dd>
                        </div>
                        @endif
                    </dl>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('produk.show', $p) }}" class="font-medium text-blue-600 hover:text-blue-800">
                            Detail
                        </a>
                        <a href="{{ route('produk.edit', $p) }}" class="font-medium text-violet-600 hover:text-violet-800">
                            Edit
                        </a>
                        <form action="{{ route('produk.destroy', $p) }}" method="POST"
                              class="ml-auto"
                              onsubmit="return confirm('Hapus produk &quot;{{ addslashes($p->nama_produk) }}&quot;?')">
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

        @if ($produks->hasPages())
            <div class="mt-4">
                {{ $produks->links() }}
            </div>
        @endif
    @endif
@endsection
