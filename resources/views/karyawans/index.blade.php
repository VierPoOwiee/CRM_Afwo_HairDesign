@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $karyawans->total() }} karyawan terdaftar.
            </p>
        </div>

        <form action="{{ route('karyawan.index') }}" method="GET" class="flex w-full max-w-sm items-center gap-2">
            <input type="text" name="q" value="{{ $q }}"
                   placeholder="Cari nama, kontak..."
                   class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            <button type="submit"
                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cari
            </button>
        </form>
    </div>

    @if ($karyawans->isEmpty())
        <div class="rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data karyawan.</p>
            @if ($q !== '')
                <p class="mt-1 text-sm text-gray-500">Tidak ditemukan hasil untuk "<span class="font-medium">{{ $q }}</span>".</p>
                <a href="{{ route('karyawan.index') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('karyawan.create') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    Tambah karyawan pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($karyawans as $k)
                <div class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="font-medium text-gray-900">{{ $k->nama }}</p>

                    <dl class="mt-2 flex-1 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Kontak</dt>
                            <dd class="text-right text-gray-900">
                                @if ($k->kontak)
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $k->kontak) }}" target="_blank" class="text-green-600 hover:underline">
                                        {{ $k->kontak }}
                                    </a>
                                @else
                                    -
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-gray-500">Skema Komisi</dt>
                            <dd class="text-right text-gray-900">
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    Persen omset ({{ $k->persen_komisi_harian }}%)
                                @else
                                    Per layanan
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-3 flex items-center gap-4 border-t border-gray-100 pt-3 text-sm">
                        <a href="{{ route('karyawan.edit', $k) }}" class="font-medium text-violet-600 hover:text-violet-800">
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
