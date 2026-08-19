@extends('layouts.app')

@section('title', 'Data Transaksi')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Transaksi</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $transaksis->total() }} transaksi terdaftar.
            </p>
        </div>

        <a href="{{ route('transaksi.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-md bg-violet-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
            <span class="text-lg leading-none">+</span>
            <span>Transaksi Baru</span>
        </a>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
        <form action="{{ route('transaksi.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-gray-500">Cari</label>
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="No struk, nama pelanggan..."
                       class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari ?? '' }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai ?? '' }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500">Status</label>
                <select name="status"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    <option value="">Semua</option>
                    <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="batal" {{ $statusFilter === 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                    Filter
                </button>
                @if ($q !== '' || $statusFilter !== '' || ($dari ?? '') !== '' || ($sampai ?? '') !== '')
                    <a href="{{ route('transaksi.index') }}"
                       class="rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($transaksis->isEmpty())
        <div class="mt-6 rounded-lg border border-dashed border-gray-300 bg-white px-6 py-16 text-center">
            <p class="text-sm font-medium text-gray-700">Belum ada data transaksi.</p>
            @if ($q !== '' || $statusFilter !== '' || ($dari ?? '') !== '' || ($sampai ?? '') !== '')
                <p class="mt-1 text-sm text-gray-500">Tidak ditemukan hasil untuk filter yang dipilih.</p>
                <a href="{{ route('transaksi.index') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('transaksi.create') }}" class="mt-4 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                    Buat transaksi pertama &rarr;
                </a>
            @endif
        </div>
    @else
        <div class="mt-6 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">No Struk</th>
                        <th class="px-4 py-3">Pelanggan</th>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Metode</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($transaksis as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $t->no_struk }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $t->pelanggan->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $t->waktu_kunjungan->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $t->labelMetode() }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($t->status === 'selesai')
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Selesai</span>
                                @else
                                    <span class="rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700">Batal</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('transaksi.show', $t) }}" class="font-medium text-blue-600 hover:text-blue-800">Detail</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($transaksis->hasPages())
            <div class="mt-4">
                {{ $transaksis->links() }}
            </div>
        @endif
    @endif
@endsection
