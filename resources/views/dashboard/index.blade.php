@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Ringkasan aktivitas hari ini, {{ now()->translatedFormat('l, d F Y') }}.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Pemasukan Hari Ini</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">Rp{{ number_format($totalPemasukanHariIni, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Pelanggan Baru</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $pelangganBaruHariIni }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Transaksi Hari Ini</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $transaksiHariIni->count() }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Produk Stok Menipis</p>
            <p class="mt-2 text-2xl font-bold {{ $produkStokMenipis->count() > 0 ? 'text-red-600' : 'text-gray-900' }}">
                {{ $produkStokMenipis->count() }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Transaksi Hari Ini --}}
        <div class="lg:col-span-2 rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Transaksi Hari Ini</h2>
            </div>
            @if ($transaksiHariIni->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-400">Belum ada transaksi hari ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">No Struk</th>
                                <th class="px-4 py-3">Pelanggan</th>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th class="px-4 py-3">Metode</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($transaksiHariIni as $t)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('transaksi.show', $t) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                            {{ $t->no_struk }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $t->pelanggan->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $t->waktu_kunjungan->format('H:i') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ $t->labelMetode() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Stok Menipis --}}
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-gray-900">Stok Menipis</h2>
                <p class="mt-0.5 text-xs text-gray-500">Produk dengan stok di bawah {{ App\Models\Produk::STOK_MENIPIS }}</p>
            </div>
            @if ($produkStokMenipis->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-gray-400">Semua stok produk aman.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($produkStokMenipis as $produk)
                        <li class="px-6 py-3 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $produk->nama_produk }}</p>
                                <p class="text-xs text-gray-500">@if ($produk->merek){{ $produk->merek }} &middot; @endif{{ $produk->labelKategori() }}</p>
                            </div>
                            <span class="rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-700">
                                {{ $produk->stok }} {{ $produk->satuan }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
