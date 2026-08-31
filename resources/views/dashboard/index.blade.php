@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-text-primary">Dashboard</h1>
        <p class="mt-1.5 text-sm text-text-muted">Ringkasan aktivitas hari ini, {{ now()->translatedFormat('l, d F Y') }}.</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <div class="rounded-xl border border-gray-200 bg-card p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
            <p class="text-sm font-medium text-text-muted">Pemasukan Hari Ini</p>
            <p class="mt-2.5 text-2xl font-bold text-text-primary">Rp{{ number_format($totalPemasukanHariIni, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-card p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
            <p class="text-sm font-medium text-text-muted">Pelanggan Baru</p>
            <p class="mt-2.5 text-2xl font-bold text-text-primary">{{ $pelangganBaruHariIni }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-card p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
            <p class="text-sm font-medium text-text-muted">Transaksi Hari Ini</p>
            <p class="mt-2.5 text-2xl font-bold text-text-primary">{{ $transaksiHariIni->count() }}</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-card p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
            <p class="text-sm font-medium text-text-muted">Produk Stok Menipis</p>
            <p class="mt-2.5 text-2xl font-bold {{ $produkStokMenipis->count() > 0 ? 'text-danger' : 'text-text-primary' }}">
                {{ $produkStokMenipis->count() }}
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Transaksi Hari Ini --}}
        <div class="lg:col-span-2 rounded-xl border border-gray-200 bg-card shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-text-primary">Transaksi Hari Ini</h2>
            </div>
            @if ($transaksiHariIni->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-text-muted">Belum ada transaksi hari ini.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/80 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">
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
                                <tr class="transition-colors duration-150 hover:bg-card-hover">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('transaksi.show', $t) }}" class="font-medium text-accent-text hover:text-accent">
                                            {{ $t->no_struk }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-text-secondary">{{ $t->pelanggan->nama ?? '-' }}</td>
                                    <td class="px-4 py-3 text-text-muted">{{ $t->waktu_kunjungan->format('H:i') }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-text-primary">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full bg-badge-neutral-bg px-2.5 py-0.5 text-xs font-medium text-badge-neutral-text">{{ $t->labelMetode() }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Stok Menipis --}}
        <div class="rounded-xl border border-gray-200 bg-card shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-semibold text-text-primary">Stok Menipis</h2>
                <p class="mt-0.5 text-xs text-text-muted">Produk dengan stok di bawah {{ App\Models\Produk::STOK_MENIPIS }}</p>
            </div>
            @if ($produkStokMenipis->isEmpty())
                <div class="px-6 py-12 text-center">
                    <p class="text-sm text-text-muted">Semua stok produk aman.</p>
                </div>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($produkStokMenipis as $produk)
                        <li class="px-6 py-3 flex items-center justify-between transition-colors duration-150 hover:bg-card-hover">
                            <div>
                                <p class="text-sm font-medium text-text-primary">{{ $produk->nama_produk }}</p>
                                <p class="text-xs text-text-muted">@if ($produk->merek){{ $produk->merek }} &middot; @endif{{ $produk->labelKategori() }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-danger/10 px-2.5 py-0.5 text-xs font-semibold text-danger">
                                {{ $produk->stok }} {{ $produk->satuan }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
