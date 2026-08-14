@extends('layouts.app')

@section('title', 'Data Pelanggan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Pelanggan</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $pelanggans->total() }} pelanggan terdaftar.
            </p>
        </div>

        <form action="{{ route('pelanggan.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Cari nama, no. WA, IG, alamat..."
                   class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            <button type="submit"
                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cari
            </button>
        </form>
    </div>

    @if ($pelanggans->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data pelanggan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-gray-500">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('pelanggan.index') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('pelanggan.create') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    Tambah pelanggan pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($pelanggans as $p)
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="font-medium text-gray-900">{{ $p->nama }}</p>

                    @if ($p->catatan_khusus)
                        <a href="{{ route('pelanggan.show', $p) }}"
                           class="mt-1.5 block line-clamp-2 rounded-md bg-amber-50 px-2.5 py-1.5 text-xs text-amber-800 hover:bg-amber-100">
                            <span class="font-semibold">Catatan:</span> {{ $p->catatan_khusus }}
                        </a>
                    @endif

                    <dl class="mt-2 flex-1 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">WhatsApp</dt>
                            <dd class="text-right text-gray-900">
                                <a href="https://wa.me/{{ preg_replace('/\D/', '', $p->no_wa) }}" target="_blank" class="text-green-600 hover:underline">
                                    {{ $p->no_wa }}
                                </a>
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Instagram</dt>
                            <dd class="text-right text-gray-900">{{ $p->username_instagram ? ltrim($p->username_instagram, '@') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Jenis Kelamin</dt>
                            <dd class="text-right text-gray-900">{{ $p->jenis_kelamin ? ($p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Jenis Rambut</dt>
                            <dd class="text-right text-gray-900">{{ $p->jenis_rambut ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Alamat</dt>
                            <dd class="max-w-[60%] break-words text-right text-gray-900">{{ $p->alamat ?? '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('pelanggan.show', $p) }}" class="font-medium text-blue-600 hover:text-blue-800">
                            Detail
                        </a>
                        <a href="{{ route('pelanggan.edit', $p) }}" class="font-medium text-violet-600 hover:text-violet-800">
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
