@extends('layouts.app')

@section('title', 'Data Transaksi')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-text-primary">Data Transaksi</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $transaksis->total() }} transaksi terdaftar.
            </p>
        </div>

        <a href="{{ route('transaksi.create') }}"
           class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
            <span class="text-lg leading-none">+</span>
            <span>Transaksi Baru</span>
        </a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-card p-4 shadow-sm">
        <form action="{{ route('transaksi.index') }}" method="GET" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6">
            <div>
                <label class="block text-xs font-medium text-text-muted">Cari</label>
                <input type="text" name="q" value="{{ $q }}"
                       placeholder="No struk, nama pelanggan..."
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-text-muted">Layanan</label>
                <select name="layanan"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                    <option value="">Semua Layanan</option>
                    @foreach ($layanans as $l)
                        <option value="{{ $l->id }}" {{ $layananFilter == $l->id ? 'selected' : '' }}>{{ $l->nama_layanan }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-text-muted">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari ?? '' }}"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-text-muted">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai ?? '' }}"
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-text-muted">Status</label>
                <select name="status"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="selesai" {{ $statusFilter === 'selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="batal" {{ $statusFilter === 'batal' ? 'selected' : '' }}>Batal</option>
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:bg-dark-hover">
                    Filter
                </button>
                @if ($q !== '' || $statusFilter !== '' || $layananFilter > 0 || ($dari ?? '') !== '' || ($sampai ?? '') !== '')
                    <a href="{{ route('transaksi.index') }}"
                       class="rounded-lg bg-card px-3 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if ($transaksis->isEmpty())
        <div class="mt-6 rounded-xl border border-dashed border-gray-300 bg-card px-6 py-16 text-center">
            <p class="text-sm font-medium text-text-secondary">Belum ada data transaksi.</p>
            @if ($q !== '' || $statusFilter !== '' || $layananFilter > 0 || ($dari ?? '') !== '' || ($sampai ?? '') !== '')
                <p class="mt-1 text-sm text-text-muted">Tidak ditemukan hasil untuk filter yang dipilih.</p>
                <a href="{{ route('transaksi.index') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    &larr; Tampilkan semua
                </a>
            @else
                <a href="{{ route('transaksi.create') }}" class="mt-4 inline-block text-sm font-medium text-accent-text hover:text-accent">
                    Buat transaksi pertama &rarr;
                </a>
            @endif
        </div>
    @else
        {{-- Desktop table --}}
        <div class="mt-6 hidden overflow-hidden rounded-xl border border-gray-200 bg-card shadow-sm md:block">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-xs font-semibold uppercase tracking-wide text-text-muted">
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
                        <tr class="transition-colors duration-150 hover:bg-card-hover">
                            <td class="px-4 py-3 font-medium text-text-primary">{{ $t->no_struk }}</td>
                            <td class="px-4 py-3 text-text-secondary">{{ $t->pelanggan->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-text-secondary">{{ $t->waktu_kunjungan->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 font-medium text-text-primary">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full bg-badge-neutral-bg px-2.5 py-0.5 text-xs font-medium text-badge-neutral-text">{{ $t->labelMetode() }}</span>
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

        {{-- Mobile cards --}}
        <div class="mt-6 space-y-3 md:hidden">
            @foreach ($transaksis as $t)
                <a href="{{ route('transaksi.show', $t) }}" class="block rounded-xl border border-gray-200 bg-card p-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-text-primary truncate">{{ $t->no_struk }}</p>
                            <p class="mt-0.5 text-xs text-text-muted">{{ $t->pelanggan->nama ?? '-' }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-bold text-text-primary">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-2 text-xs text-text-muted">
                        <span>{{ $t->waktu_kunjungan->format('d M Y H:i') }}</span>
                        <span>&middot;</span>
                        <span class="inline-flex items-center rounded-full bg-badge-neutral-bg px-2 py-0.5 font-medium text-badge-neutral-text">{{ $t->labelMetode() }}</span>
                        @if ($t->status === 'selesai')
                            <span class="rounded-full bg-green-50 px-2 py-0.5 font-medium text-green-700">Selesai</span>
                        @else
                            <span class="rounded-full bg-red-50 px-2 py-0.5 font-medium text-red-700">Batal</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        @if ($transaksis->hasPages())
            <div class="mt-4">
                {{ $transaksis->links() }}
            </div>
        @endif
    @endif
@endsection
