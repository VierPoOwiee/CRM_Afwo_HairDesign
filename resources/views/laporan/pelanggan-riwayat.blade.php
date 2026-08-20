@extends('layouts.app')

@section('title', 'Riwayat ' . $pelanggan->nama)

@section('content')
    <div class="mb-6">
        <a href="{{ route('laporan.pelanggan-aktif', ['preset' => 'bulan-ini']) }}" class="text-sm text-violet-600 hover:text-violet-700">&larr; Kembali ke Pelanggan Aktif</a>
        <div class="mt-2">
            <h1 class="text-2xl font-bold text-gray-900">{{ $pelanggan->nama }}</h1>
            <p class="mt-1 text-sm text-gray-500">Riwayat transaksi lengkap &middot; {{ $pelanggan->no_wa }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-6">
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Total Kunjungan</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">{{ $jumlahKunjungan }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Total Belanja</p>
            <p class="mt-2 text-2xl font-bold text-gray-900">Rp{{ number_format($totalBelanja, 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Kunjungan Terakhir</p>
            <p class="mt-2 text-lg font-bold text-gray-900">
                {{ $transaksis->first() ? $transaksis->first()->waktu_kunjungan->format('d M Y') : '-' }}
            </p>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-semibold text-gray-900">Riwayat Transaksi</h2>
        </div>
        @if ($transaksis->isEmpty())
            <div class="px-6 py-12 text-center">
                <p class="text-sm text-gray-400">Belum ada riwayat transaksi.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">No Struk</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Staf</th>
                            <th class="px-4 py-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($transaksis as $t)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('transaksi.show', $t) }}" class="font-medium text-blue-600 hover:text-blue-800">
                                        {{ $t->no_struk }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-500">{{ $t->waktu_kunjungan->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="space-y-0.5">
                                        @foreach ($t->details as $d)
                                            <p class="text-xs text-gray-600">
                                                @if ($d->tipe_item === 'layanan')
                                                    {{ $d->layanan->nama_layanan ?? '-' }} ({{ $d->varian_dipilih ?? '-' }})
                                                @else
                                                    {{ $d->produk->nama_produk ?? '-' }} &times; {{ $d->qty }}
                                                @endif
                                            </p>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-0.5">
                                        @foreach ($t->details->where('tipe_item', 'layanan') as $d)
                                            @if ($d->staf)
                                                <p class="text-xs text-gray-600">{{ $d->staf->nama }}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format((float) $t->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
