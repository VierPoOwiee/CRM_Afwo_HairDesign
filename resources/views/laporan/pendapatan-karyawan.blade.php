@extends('layouts.app')

@section('title', 'Pendapatan Bulanan Karyawan')

@section('content')
    @include('laporan._nav')

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pendapatan Bulanan Karyawan</h1>
            <p class="mt-1 text-sm text-gray-500">Gaji pokok + komisi yang diterima tiap karyawan dalam satu bulan.</p>
        </div>
        <button onclick="window.print()" class="inline-flex shrink-0 items-center gap-2 self-start rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 no-print sm:self-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak
        </button>
    </div>

    {{-- Filter bulan --}}
    <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm no-print">
        <form action="{{ route('laporan.pendapatan-karyawan') }}" method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500">Bulan</label>
                <select name="bulan"
                        class="mt-1 block rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    @foreach ($pilihanBulan as $p)
                        <option value="{{ $p['value'] }}" {{ $p['value'] === $bulanInput ? 'selected' : '' }}>{{ $p['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                    class="rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                Tampilkan
            </button>
        </form>
    </div>

    {{-- Total keseluruhan --}}
    <div class="mt-6 rounded-lg border-2 border-violet-200 bg-violet-50 p-5 shadow-sm text-center">
        <p class="text-sm font-medium text-violet-700">Total Pendapatan Semua Karyawan — {{ $dariCarbon->format('F Y') }}</p>
        <p class="mt-1 text-3xl font-bold text-violet-900">Rp{{ number_format($grandTotal['total_pendapatan'], 0, ',', '.') }}</p>
        <p class="mt-1 text-xs text-violet-500">Komisi Rp{{ number_format($grandTotal['total_komisi'], 0, ',', '.') }} + Gaji Pokok Rp{{ number_format($grandTotal['gaji_pokok'], 0, ',', '.') }}</p>
    </div>

    {{-- Tabel per karyawan --}}
    <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Karyawan</th>
                        <th class="px-4 py-3">Skema</th>
                        <th class="px-4 py-3 text-right">Komisi per Layanan</th>
                        <th class="px-4 py-3 text-right">Komisi Harian (%)</th>
                        <th class="px-4 py-3 text-right">Total Komisi</th>
                        <th class="px-4 py-3 text-right">Gaji Pokok</th>
                        <th class="px-4 py-3 text-right">Total Pendapatan</th>
                        <th class="px-4 py-3 no-print"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($baris as $row)
                        @php $k = $row['karyawan']; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $k->nama }}</td>
                            <td class="px-4 py-3">
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">% Omset Harian ({{ $k->persen_komisi_harian }}%)</span>
                                @else
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">Per Layanan</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                @if ($row['komisi_per_layanan'] > 0)
                                    Rp{{ number_format($row['komisi_per_layanan'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">
                                @if ($row['komisi_persen_harian'] > 0)
                                    Rp{{ number_format($row['komisi_persen_harian'], 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($row['total_komisi'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp{{ number_format($row['gaji_pokok'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-bold text-violet-700">Rp{{ number_format($row['total_pendapatan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right no-print">
                                <a href="{{ route('laporan.rekap-komisi.slip', [
                                        'karyawan' => $k->id,
                                        'preset' => 'custom',
                                        'dari' => $dariCarbon->toDateString(),
                                        'sampai' => $sampaiCarbon->toDateString(),
                                    ]) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-white px-2.5 py-1.5 text-xs font-medium text-violet-700 ring-1 ring-inset ring-violet-200 hover:bg-violet-50">
                                    Slip Detail
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">Belum ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($baris->isNotEmpty())
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50 text-sm">
                        <tr>
                            <td class="px-4 py-3 font-bold text-gray-900" colspan="2">TOTAL</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['komisi_per_layanan'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['komisi_persen_harian'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp{{ number_format($grandTotal['total_komisi'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp{{ number_format($grandTotal['gaji_pokok'], 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-extrabold text-violet-900">Rp{{ number_format($grandTotal['total_pendapatan'], 0, ',', '.') }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    <p class="mt-3 text-xs text-gray-400">
        Gaji pokok dibayar utuh per bulan (tidak di-pro-rata). Komisi dihitung dari transaksi berstatus selesai pada bulan terpilih.
    </p>
@endsection
