{{-- Slip Pendapatan Karyawan: 1 halaman = 1 karyawan.
     Param: $karyawan, $slip (dari LaporanController::buildSlipData), $dari, $sampai --}}
@php
    $dariC = \Carbon\Carbon::parse($dari);
    $sampaiC = \Carbon\Carbon::parse($sampai);
    $periodeBulanPenuh = $dariC->isSameMonth($sampaiC)
        && $dariC->isSameDay($dariC->copy()->startOfMonth())
        && $sampaiC->isSameDay($sampaiC->copy()->endOfMonth());
    $catatanGaji = $periodeBulanPenuh
        ? 'Gaji pokok bulanan'
        : 'Gaji pokok bulanan — dibayar utuh, tidak di-pro-rata';
@endphp

<div class="rounded-lg border border-gray-200 bg-white shadow-sm overflow-hidden">
    {{-- Header slip --}}
    <div class="border-b-2 border-gray-200 px-6 py-4">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Slip Pendapatan Karyawan</p>
                <h3 class="mt-0.5 text-xl font-bold text-gray-900">{{ $karyawan->nama }}</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Skema komisi:
                    @if ($karyawan->skema_komisi === 'persen_omset_harian')
                        <span class="font-medium">Persen Omset Harian ({{ $karyawan->persen_komisi_harian }}%)</span>
                    @else
                        <span class="font-medium">Per Layanan</span>
                    @endif
                </p>
            </div>
            <div class="sm:text-right">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Periode</p>
                <p class="text-sm font-semibold text-gray-900">{{ $dariC->format('d M Y') }} s/d {{ $sampaiC->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Gaji pokok --}}
    <div class="px-6 py-4 border-b border-gray-100">
        <div class="flex items-center justify-between gap-4 rounded-md bg-gray-50 px-4 py-3">
            <div>
                <p class="text-sm font-medium text-gray-900">Gaji Pokok</p>
                <p class="text-xs text-gray-500">{{ $catatanGaji }}</p>
            </div>
            <p class="text-lg font-semibold text-gray-900">Rp{{ number_format($slip['gaji_pokok'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Breakdown komisi harian --}}
    <div class="px-6 py-4">
        <h4 class="text-sm font-semibold text-gray-900">Breakdown Komisi Harian</h4>
        @if ($slip['sumber'] === 'persen_harian')
            <p class="mt-0.5 text-xs text-gray-500">Sumber: komisi harian spesial (% omset harian).</p>
        @else
            <p class="mt-0.5 text-xs text-gray-500">Sumber: komisi per transaksi layanan.</p>
        @endif

        <div class="mt-3 overflow-x-auto rounded-md border border-gray-100">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Tanggal</th>
                        @if ($slip['sumber'] === 'persen_harian')
                            <th class="px-4 py-2 text-right">Omset Dasar</th>
                            <th class="px-4 py-2 text-right">Persen</th>
                        @endif
                        <th class="px-4 py-2 text-right">Komisi</th>
                        <th class="px-4 py-2 no-print w-28"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($slip['harian'] as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">{{ $row['tanggal']->format('d M Y') }}</td>
                            @if ($slip['sumber'] === 'persen_harian')
                                <td class="px-4 py-2 text-right text-gray-900">Rp{{ number_format($row['omset_dasar'], 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ $row['persen'] }}%</td>
                            @endif
                            <td class="px-4 py-2 text-right font-medium text-gray-900">Rp{{ number_format($row['komisi'], 0, ',', '.') }}</td>
                            <td class="px-4 py-2 no-print">
                                @if ($slip['sumber'] === 'per_layanan')
                                    {{-- Reuse mekanisme edit komisi yang sudah ada di modul Transaksi --}}
                                    @foreach ($row['transaksis'] as $trx)
                                        <a href="{{ route('transaksi.show', $trx) }}"
                                           class="mr-1 inline-flex items-center rounded bg-blue-50 px-1.5 py-0.5 text-[11px] font-medium text-blue-700 hover:bg-blue-100"
                                           title="Edit komisi di halaman transaksi">
                                            Edit {{ $trx->no_struk }}
                                        </a>
                                    @endforeach
                                @else
                                    {{-- Reuse mekanisme hitung-ulang yang sudah ada di modul Laporan --}}
                                    <form action="{{ route('laporan.rekap-komisi.hitung-ulang') }}" method="POST" class="flex justify-end">
                                        @csrf
                                        <input type="hidden" name="id_staf" value="{{ $karyawan->id }}">
                                        <input type="hidden" name="tanggal" value="{{ $row['tanggal']->toDateString() }}">
                                        <button type="submit"
                                                class="inline-flex items-center rounded bg-blue-50 px-1.5 py-0.5 text-[11px] font-medium text-blue-700 hover:bg-blue-100"
                                                title="Hitung ulang komisi harian tanggal ini">
                                            Hitung Ulang
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $slip['sumber'] === 'persen_harian' ? 5 : 3 }}" class="px-4 py-4 text-center text-sm text-gray-400">
                                Tidak ada komisi pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Breakdown komisi mingguan (selalu dihitung ulang dari breakdown harian) --}}
    <div class="border-t border-gray-100 px-6 py-4">
        <h4 class="text-sm font-semibold text-gray-900">Breakdown Komisi Mingguan</h4>
        <p class="mt-0.5 text-xs text-gray-500">Minggu Senin–Minggu, dijumlahkan otomatis dari data harian.</p>

        <div class="mt-3 overflow-x-auto rounded-md border border-gray-100">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Minggu</th>
                        <th class="px-4 py-2 text-right">Komisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($slip['mingguan'] as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-700">{{ $row['mulai']->format('d M') }} – {{ $row['sampai']->format('d M Y') }}</td>
                            <td class="px-4 py-2 text-right font-medium text-gray-900">Rp{{ number_format($row['komisi'], 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-4 text-center text-sm text-gray-400">Tidak ada komisi pada periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Total pendapatan --}}
    <div class="border-t-2 border-accent/30 bg-accent-light px-6 py-5">
        <table class="w-full text-sm">
            <tbody>
                <tr>
                    <td class="py-1 text-gray-700">Total Komisi Periode Ini</td>
                    <td class="py-1 text-right font-medium text-gray-900">Rp{{ number_format($slip['total_komisi'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-gray-700">Gaji Pokok <span class="text-xs text-gray-400">(bulanan)</span></td>
                    <td class="py-1 text-right font-medium text-gray-900">Rp{{ number_format($slip['gaji_pokok'], 0, ',', '.') }}</td>
                </tr>
                @if ($slip['uang_makan'] > 0)
                    <tr>
                        <td class="py-1 text-gray-700">Uang Makan <span class="text-xs text-gray-400">(Rp25.000 &times; {{ $slip['jumlah_hadir'] }} hari hadir)</span></td>
                        <td class="py-1 text-right font-medium text-gray-900">Rp{{ number_format($slip['uang_makan'], 0, ',', '.') }}</td>
                    </tr>
                @endif
                    <tr class="border-t-2 border-accent/30">
                        <td class="pt-3 text-base font-bold text-text-primary">TOTAL PENDAPATAN</td>
                        <td class="pt-3 text-right text-2xl font-extrabold text-text-primary">Rp{{ number_format($slip['total_pendapatan'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
