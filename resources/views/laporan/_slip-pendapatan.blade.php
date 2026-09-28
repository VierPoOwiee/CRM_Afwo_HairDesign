{{-- Slip Pendapatan Karyawan: 1 halaman = 1 karyawan.
     Param: $karyawan, $slip (dari LaporanController::buildSlipData), $dari, $sampai --}}
@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $fmtTgl = function ($dt, $denganTahun = true) use ($bulanIndo) {
        $c = \Carbon\Carbon::parse($dt);
        $tgl = $c->format('d') . ' ' . $bulanIndo[$c->format('n') - 1];
        return $denganTahun ? $tgl . ' ' . $c->format('Y') : $tgl;
    };
    $dariC = \Carbon\Carbon::parse($dari);
    $sampaiC = \Carbon\Carbon::parse($sampai);
    $periodeBulanPenuh = $dariC->isSameMonth($sampaiC)
        && $dariC->isSameDay($dariC->copy()->startOfMonth())
        && $sampaiC->isSameDay($sampaiC->copy()->endOfMonth());
    $catatanGaji = $periodeBulanPenuh
        ? 'Gaji pokok bulanan'
        : 'Gaji pokok bulanan — dibayar utuh, tidak di-pro-rata';
    $isHarian = $slip['sumber'] === 'persen_harian';
@endphp

<div class="slip-paper-document">
    {{-- Header slip --}}
    <div class="slip-brand-header">
        <div class="slip-brand-title">AFWO HAIR DESIGN</div>
        <div class="slip-doc-type">Slip Pendapatan Karyawan</div>
    </div>

    <div class="slip-meta-grid">
        <div class="slip-meta-col">
            <span class="slip-meta-label">Karyawan</span>
            <span class="slip-meta-val" style="font-weight: 800;">{{ $karyawan->nama }}</span>
        </div>
        <div class="slip-meta-col">
            <span class="slip-meta-label">Skema Komisi</span>
            <span class="slip-meta-val">
                @if ($isHarian)
                    Persen Omset Harian ({{ $karyawan->persen_komisi_harian }}%)
                @else
                    Per Layanan
                @endif
            </span>
        </div>
        <div class="slip-meta-col">
            <span class="slip-meta-label">Periode</span>
            <span class="slip-meta-val">{{ $fmtTgl($dariC) }} s/d {{ $fmtTgl($sampaiC) }}</span>
        </div>
    </div>

    <hr class="slip-divider">

    {{-- Gaji pokok --}}
    <div class="slip-gaji-box">
        <div>
            <div class="slip-gaji-label">Gaji Pokok</div>
            <div class="slip-gaji-note">{{ $catatanGaji }}</div>
        </div>
        <div class="slip-gaji-value">{{ $fmt($slip['gaji_pokok']) }}</div>
    </div>

    {{-- Breakdown komisi harian --}}
    <h4 class="slip-section-title">Breakdown Komisi Harian</h4>
    <p class="slip-section-desc">
        @if ($isHarian)
            Sumber: komisi harian spesial (% omset harian).
        @else
            Sumber: komisi per transaksi layanan.
        @endif
    </p>
    @if ($slip['harian']->isEmpty())
        <p class="rekap-detail-empty">Tidak ada komisi pada periode ini.</p>
    @else
        <div class="table-responsive-wrapper">
            <table class="rekap-commission-table" style="min-width: 0;">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        @if ($isHarian)
                            <th style="text-align: right;">Omset Dasar</th>
                            <th style="text-align: right;">Persen</th>
                        @endif
                        <th style="text-align: right;">Komisi</th>
                        <th style="text-align: right;" class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slip['harian'] as $row)
                        <tr>
                            <td>{{ $fmtTgl($row['tanggal']) }}</td>
                            @if ($isHarian)
                                <td style="text-align: right;">{{ $fmt($row['omset_dasar']) }}</td>
                                <td style="text-align: right;">{{ $row['persen'] }}%</td>
                            @endif
                            <td style="text-align: right; font-weight: 700;">{{ $fmt($row['komisi']) }}</td>
                            <td style="text-align: right;" class="no-print">
                                @if (!$isHarian)
                                    @foreach ($row['transaksis'] as $trx)
                                        <a href="{{ route('transaksi.show', $trx) }}" class="btn-slip-action" title="Edit komisi di halaman transaksi">
                                            Edit {{ $trx->no_struk }}
                                        </a>
                                    @endforeach
                                @else
                                    <form action="{{ route('laporan.rekap-komisi.hitung-ulang') }}" method="POST" style="display: inline-flex;">
                                        @csrf
                                        <input type="hidden" name="id_staf" value="{{ $karyawan->id }}">
                                        <input type="hidden" name="tanggal" value="{{ $row['tanggal']->toDateString() }}">
                                        <button type="submit" class="btn-slip-action" title="Hitung ulang komisi harian tanggal ini">Hitung Ulang</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Breakdown komisi mingguan (selalu dihitung ulang dari breakdown harian) --}}
    <h4 class="slip-section-title">Breakdown Komisi Mingguan</h4>
    <p class="slip-section-desc">Minggu Senin&ndash;Minggu, dijumlahkan otomatis dari data harian.</p>
    @if ($slip['mingguan']->isEmpty())
        <p class="rekap-detail-empty">Tidak ada komisi pada periode ini.</p>
    @else
        <div class="table-responsive-wrapper" style="max-width: 480px;">
            <table class="rekap-commission-table" style="min-width: 0;">
                <thead>
                    <tr>
                        <th>Minggu</th>
                        <th style="text-align: right;">Komisi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($slip['mingguan'] as $row)
                        <tr>
                            <td>{{ $fmtTgl($row['mulai'], false) }} &ndash; {{ $fmtTgl($row['sampai']) }}</td>
                            <td style="text-align: right; font-weight: 700;">{{ $fmt($row['komisi']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Total pendapatan --}}
    <div class="slip-total-box">
        <div class="slip-amount-row">
            <span>Total Komisi Periode Ini</span>
            <b>{{ $fmt($slip['total_komisi']) }}</b>
        </div>
        <div class="slip-amount-row">
            <span>Gaji Pokok <small style="color: var(--text-muted);">(bulanan)</small></span>
            <b>{{ $fmt($slip['gaji_pokok']) }}</b>
        </div>
        @if ($slip['uang_makan'] > 0)
            <div class="slip-amount-row">
                <span>Uang Makan <small style="color: var(--text-muted);">(Rp25.000 &times; {{ $slip['jumlah_hadir'] }} hari hadir)</small></span>
                <b>{{ $fmt($slip['uang_makan']) }}</b>
            </div>
        @endif
        <div class="slip-grand-total">
            <span>TOTAL PENDAPATAN</span>
            <b>{{ $fmt($slip['total_pendapatan']) }}</b>
        </div>
    </div>
</div>