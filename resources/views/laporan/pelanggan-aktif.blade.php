@extends('layouts.app')

@section('title', 'Data Pelanggan Aktif')

@section('content')
    @include('laporan._nav')
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Data Pelanggan Aktif</h1>
        <p class="mt-1 text-sm text-gray-500">Pelanggan dengan minimal 1 transaksi dalam periode yang dipilih.</p>
    </div>

    @include('laporan._filter-periode', [
        'action' => route('laporan.pelanggan-aktif'),
        'resetUrl' => route('laporan.pelanggan-aktif', ['preset' => 'bulan-ini']),
    ])

    <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
        @if ($pelanggans->isEmpty())
            <div class="px-6 py-16 text-center">
                <p class="text-sm font-medium text-gray-700">Tidak ada pelanggan aktif untuk periode ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Kontak</th>
                            <th class="px-4 py-3 text-right">Jumlah Kunjungan</th>
                            <th class="px-4 py-3 text-right">Total Belanja</th>
                            <th class="px-4 py-3">Kunjungan Terakhir</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($pelanggans as $p)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $p->nama }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $p->no_wa }}</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ $p->jumlah_kunjungan }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">
                                    Rp{{ number_format((float) ($p->total_belanja ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $p->kunjungan_terakhir ? \Carbon\Carbon::parse($p->kunjungan_terakhir)->format('d M Y H:i') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('laporan.pelanggan-riwayat', $p) }}"
                                       class="font-medium text-blue-600 hover:text-blue-800">
                                        Riwayat &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
