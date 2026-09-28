@extends('layouts.app')

@section('title', 'Pendapatan Bulanan Karyawan')

@section('nav-active', 'reports-income')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pendapatan-karyawan.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $namaBulan = $bulanIndo[(int) $dariCarbon->format('n') - 1] . ' ' . $dariCarbon->format('Y');
@endphp

@section('content')

    <div class="income-header-row">
        <div class="income-title-group">
            <h1 class="income-title">Pendapatan Bulanan Karyawan</h1>
            <p class="income-subtitle">Gaji pokok + komisi + uang makan yang diterima tiap karyawan dalam satu bulan.</p>
        </div>
    </div>

    {{-- Filter bulan --}}
    <div class="income-filter-card no-print">
        <form action="{{ route('laporan.pendapatan-karyawan') }}" method="GET" class="income-filter-row">
            <div class="filter-month-item">
                <label class="month-label" for="bulan">Bulan</label>
                <div class="month-select-wrapper">
                    <select name="bulan" id="bulan" class="month-select-field">
                        @foreach ($pilihanBulan as $p)
                            <option value="{{ $p['value'] }}" {{ $p['value'] === $bulanInput ? 'selected' : '' }}>{{ $p['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-show-dark">Tampilkan</button>
            @if ($bulanInput !== now()->format('Y-m'))
                <a href="{{ route('laporan.pendapatan-karyawan') }}" class="btn-slip-detail" style="text-decoration:none;">Reset ke Bulan Ini</a>
            @endif
        </form>
    </div>

    {{-- Total keseluruhan --}}
    <div class="income-summary-banner">
        <span class="income-banner-label">Total Pendapatan Semua Karyawan — {{ $namaBulan }}</span>
        <span class="income-banner-amount">{{ $fmt($grandTotal['total_pendapatan']) }}</span>
        <span class="income-banner-subtext">
            Komisi {{ $fmt($grandTotal['total_komisi']) }} &middot; Gaji Pokok {{ $fmt($grandTotal['gaji_pokok']) }} &middot; Uang Makan {{ $fmt($grandTotal['uang_makan']) }}
        </span>
    </div>

    {{-- Tabel per karyawan --}}
    <div class="income-table-card">
        <div class="table-responsive-wrapper">
            <table class="income-detail-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Skema</th>
                        <th style="text-align: right;">Komisi per Layanan</th>
                        <th style="text-align: right;">Komisi Harian (%)</th>
                        <th style="text-align: right;">Total Komisi</th>
                        <th style="text-align: right;">Gaji Pokok</th>
                        <th style="text-align: right;">Hadir (hari)</th>
                        <th style="text-align: right;">Uang Makan</th>
                        <th style="text-align: right;">Total Pendapatan</th>
                        <th style="text-align: right;" class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($baris as $row)
                        @php $k = $row['karyawan']; @endphp
                        <tr>
                            <td style="font-weight: 700;">{{ $k->nama }}</td>
                            <td>
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    <span class="scheme-badge-daily">% Omset Harian ({{ $k->persen_komisi_harian }}%)</span>
                                @else
                                    <span class="scheme-badge-service">Per Layanan</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if ($row['komisi_per_layanan'] > 0)
                                    {{ $fmt($row['komisi_per_layanan']) }}
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if ($row['komisi_persen_harian'] > 0)
                                    {{ $fmt($row['komisi_persen_harian']) }}
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 700;">{{ $fmt($row['total_komisi']) }}</td>
                            <td style="text-align: right;">{{ $fmt($row['gaji_pokok']) }}</td>
                            <td style="text-align: right;">
                                @if ($row['jumlah_hadir'] > 0)
                                    {{ $row['jumlah_hadir'] }}
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if ($row['uang_makan'] > 0)
                                    {{ $fmt($row['uang_makan']) }}
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 800; color: #B45309;">{{ $fmt($row['total_pendapatan']) }}</td>
                            <td style="text-align: right;" class="no-print">
                                <a href="{{ route('laporan.rekap-komisi.slip', [
                                        'karyawan' => $k->id,
                                        'preset' => 'custom',
                                        'dari' => $dariCarbon->toDateString(),
                                        'sampai' => $sampaiCarbon->toDateString(),
                                    ]) }}"
                                   class="btn-slip-detail" style="text-decoration:none;">
                                    Slip Detail
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 40px 16px;">Belum ada data karyawan.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($baris->isNotEmpty())
                    <tfoot>
                        <tr class="income-tfoot-row">
                            <th colspan="2">TOTAL</th>
                            <th style="text-align: right;">{{ $fmt($grandTotal['komisi_per_layanan']) }}</th>
                            <th style="text-align: right;">{{ $fmt($grandTotal['komisi_persen_harian']) }}</th>
                            <th style="text-align: right;">{{ $fmt($grandTotal['total_komisi']) }}</th>
                            <th style="text-align: right;">{{ $fmt($grandTotal['gaji_pokok']) }}</th>
                            <th style="text-align: right;">{{ $grandTotal['jumlah_hadir'] }}</th>
                            <th style="text-align: right;">{{ $fmt($grandTotal['uang_makan']) }}</th>
                            <th style="text-align: right; color: #B45309;">{{ $fmt($grandTotal['total_pendapatan']) }}</th>
                            <th class="no-print"></th>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="income-table-footer-legend">
            <p class="legend-text">
                Gaji pokok dibayar utuh per bulan (tidak di-pro-rata). Komisi dihitung dari transaksi berstatus selesai pada bulan terpilih.
                Uang makan {{ $fmt($uangMakanPerHari) }} per hari hadir dari absensi, hanya untuk karyawan skema per layanan.
            </p>
        </div>
    </div>

@endsection