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
        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">No. WhatsApp</th>
                            <th class="px-4 py-3">Instagram</th>
                            <th class="px-4 py-3">Jenis Kelamin</th>
                            <th class="px-4 py-3">Jenis Rambut</th>
                            <th class="px-4 py-3">Alamat</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($pelanggans as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $p->nama }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $p->no_wa) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-green-600 hover:text-green-700 hover:underline">
                                        {{ $p->no_wa }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $p->username_instagram ? ltrim($p->username_instagram, '@') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $p->jenis_kelamin ? ($p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $p->jenis_rambut ?? '-' }}</td>
                                <td class="max-w-[180px] truncate px-4 py-3 text-gray-600" title="{{ $p->alamat }}">
                                    {{ $p->alamat ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-3 text-sm">
                                        <a href="{{ route('pelanggan.edit', $p) }}"
                                           class="font-medium text-violet-600 hover:text-violet-800">
                                            Edit
                                        </a>
                                        <form action="{{ route('pelanggan.destroy', $p) }}" method="POST"
                                              onsubmit="return confirm('Hapus pelanggan &quot;{{ addslashes($p->nama) }}&quot;?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 hover:text-red-800">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($pelanggans->hasPages())
                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $pelanggans->links() }}
                </div>
            @endif
        </div>
    @endif
@endsection
