{{-- Body laporan utama Rekap Komisi — dipakai oleh laporan/rekap-komisi.blade.php
     dan laporan/cetak-rekap-komisi.blade.php --}}
@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $bulanIndo = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tglIndo = function ($dt, $denganJam = false) use ($bulanIndo) {
        if (!$dt) return '-';
        $c = \Carbon\Carbon::parse($dt);
        $tgl = $c->format('d') . ' ' . $bulanIndo[$c->format('n') - 1] . ' ' . $c->format('Y');
        return $denganJam ? $tgl . ' &middot; ' . $c->format('H:i') : $tgl;
    };
@endphp

<div class="rekap-summary-banner" data-field="rekap-summary-banner">
    <span class="summary-banner-label">Total Komisi yang Harus Dibayar</span>
    <div class="summary-banner-amount" id="summary-total-commission">{{ $fmt($totalBayarSemuaStaf) }}</div>
    <span class="summary-banner-period" id="summary-period-label">Periode: {{ $dari }} s/d {{ $sampai }}</span>
</div>

{{-- Section Pendapatan Karyawan (navigasi ke slip per karyawan) — SELALU tampil,
     apa pun isi datanya. Tidak ikut tercetak, karena slip aslinya menyusul
     sebagai halaman tersendiri saat cetak gabungan --}}
<section class="rekap-table-card no-print" data-field="rekap-table-card">
    <div class="rekap-card-header">
        <h2 class="rekap-card-title">Pendapatan Karyawan</h2>
        <p class="rekap-card-desc">
            Gaji pokok + komisi per karyawan untuk periode yang sama. Klik salah satu staf untuk membuka slip pendapatannya,
            atau lihat <a href="{{ route('laporan.pendapatan-karyawan') }}" class="inline-link-gold">ringkasan pendapatan bulanan</a>.
        </p>
    </div>
    @if ($pendapatanStaf->isEmpty())
        <div class="rekap-empty-card">
            <p class="rekap-empty-text">Belum ada data karyawan.</p>
        </div>
    @else
        <div class="table-responsive-wrapper">
            <table class="rekap-commission-table" id="commission-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Skema</th>
                        <th style="text-align: right;">Gaji Pokok (bulanan)</th>
                        <th style="text-align: right;">Total Komisi</th>
                        <th style="text-align: right;">Total Pendapatan</th>
                        <th style="text-align: right;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pendapatanStaf as $item)
                        @php $k = $item['karyawan']; @endphp
                        <tr>
                            <td style="font-weight: 700;">{{ $k->nama }}</td>
                            <td>
                                @if ($k->skema_komisi === 'persen_omset_harian')
                                    <span class="scheme-badge-daily">% Omset Harian ({{ $k->persen_komisi_harian }}%)</span>
                                @else
                                    <span class="scheme-badge-service">Per Layanan</span>
                                @endif
                            </td>
                            <td style="text-align: right;">{{ $fmt($item['gaji_pokok']) }}</td>
                            <td style="text-align: right;">{{ $fmt($item['total_komisi']) }}</td>
                            <td style="text-align: right; font-weight: 800; color: var(--text-link);">{{ $fmt($item['total_pendapatan']) }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('laporan.rekap-komisi.slip', array_merge(['karyawan' => $k->id], request()->only(['preset', 'dari', 'sampai']))) }}"
                                   class="btn-view-slip">
                                    Lihat Slip
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@if ($grandTotal->isEmpty())
    <section class="rekap-table-card">
        <div class="rekap-empty-card" id="rekap-empty-box">
            <p class="rekap-empty-text">Belum ada data komisi untuk periode ini.</p>
        </div>
    </section>
@else
    {{-- Rekap Tabel --}}
    <section class="rekap-table-card">
        <div class="rekap-card-header">
            <h2 class="rekap-card-title">Rekap per Staf</h2>
            <p class="rekap-card-desc">Klik baris untuk melihat rincian komisi per tanggal / per transaksi.</p>
        </div>
        <div class="table-responsive-wrapper">
            <table class="rekap-commission-table">
                <thead>
                    <tr>
                        <th>Staf</th>
                        <th>Skema</th>
                        <th style="text-align: right;">Komisi per Layanan</th>
                        <th style="text-align: right;">Komisi Harian (%)</th>
                        <th style="text-align: right;">Total Komisi</th>
                        <th style="text-align: right;"></th>
                    </tr>
                </thead>
                <tbody id="rekap-staf-tbody">
                    @foreach ($grandTotal as $row)
                        <tr class="rekap-staf-row" data-target="detail-{{ $row['id_staf'] }}">
                            <td style="font-weight: 700;">{{ $row['nama_staf'] }}</td>
                            <td>
                                @if ($row['skema'] === 'persen_omset_harian')
                                    <span class="scheme-badge-daily">% Omset Harian</span>
                                @else
                                    <span class="scheme-badge-service">Per Layanan</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if ($row['total_per_layanan'] > 0)
                                    {{ $fmt($row['total_per_layanan']) }}
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if ($row['total_persen_harian'] > 0)
                                    {{ $fmt($row['total_persen_harian']) }}
                                @else
                                    <span style="color: var(--text-muted);">—</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-weight: 800;">{{ $fmt($row['total_keseluruhan']) }}</td>
                            <td style="text-align: right;">
                                <span class="rekap-chevron">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M19 9l-7 7-7-7"/></svg>
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    {{-- Detail per Staf (expandable) --}}
    <div class="rekap-detail-stack">
        @foreach ($grandTotal as $row)
            <div id="detail-{{ $row['id_staf'] }}" class="rekap-detail-card hidden">
                <div class="rekap-detail-header">
                    <div>
                        <div class="rekap-detail-title">{{ $row['nama_staf'] }}</div>
                        <div class="rekap-detail-sub">
                            Skema:
                            @if ($row['skema'] === 'persen_omset_harian')
                                Persen Omset Harian
                            @else
                                Per Layanan
                            @endif
                        </div>
                    </div>
                    <div class="rekap-detail-total">Total: {{ $fmt($row['total_keseluruhan']) }}</div>
                </div>

                @if ($row['skema'] === 'persen_omset_harian' && $row['id_staf'])
                    <div class="rekap-hitung-ulang no-print">
                        <form action="{{ route('laporan.rekap-komisi.hitung-ulang') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_staf" value="{{ $row['id_staf'] }}">
                            <input type="hidden" name="preset" value="{{ $preset }}">
                            <div>
                                <label>Hitung Ulang Komisi Harian</label>
                                <input type="date" name="tanggal" value="{{ $sampai }}" class="rekap-hitung-input">
                            </div>
                            <button type="submit" class="btn-hitung-ulang">Hitung Ulang</button>
                        </form>
                    </div>
                @endif

                @if ($row['skema'] === 'persen_omset_harian')
                    @php $harianItems = $komisiHarian->get($row['id_staf'], collect()); @endphp
                    @if ($harianItems->isNotEmpty())
                        <div class="table-responsive-wrapper">
                            <table class="rekap-commission-table">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th style="text-align: right;">Omset Dasar</th>
                                        <th style="text-align: right;">Persen</th>
                                        <th style="text-align: right;">Komisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($harianItems as $h)
                                        <tr>
                                            <td>{{ $tglIndo($h->tanggal) }}</td>
                                            <td style="text-align: right;">{{ $fmt($h->total_omset_dasar) }}</td>
                                            <td style="text-align: right;">{{ $h->persen }}%</td>
                                            <td style="text-align: right; font-weight: 700;">{{ $fmt($h->jumlah_komisi) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rekap-detail-empty">Tidak ada data komisi harian untuk periode ini.</p>
                    @endif
                @else
                    @php $layananItems = $komisiPerLayanan->get($row['id_staf'], collect()); @endphp
                    @if ($layananItems->isNotEmpty())
                        <div class="table-responsive-wrapper">
                            <table class="rekap-commission-table">
                                <thead>
                                    <tr>
                                        <th>No Struk</th>
                                        <th>Tanggal</th>
                                        <th style="text-align: right;">Komisi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($layananItems as $kt)
                                        <tr>
                                            <td>
                                                <a href="{{ route('transaksi.show', $kt->transaksi) }}" class="rekap-struk-link">
                                                    {{ $kt->transaksi->no_struk ?? '-' }}
                                                </a>
                                            </td>
                                            <td>{{ $tglIndo($kt->transaksi->waktu_kunjungan ?? null, true) }}</td>
                                            <td style="text-align: right; font-weight: 700;">{{ $fmt($kt->jumlah_komisi) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="rekap-detail-empty">Tidak ada data komisi per layanan untuk periode ini.</p>
                    @endif
                @endif
            </div>
        @endforeach
    </div>
@endif

<script>
(function () {
    document.querySelectorAll('.rekap-staf-row').forEach(function (row) {
        row.addEventListener('click', function () {
            var target = document.getElementById(row.dataset.target);
            if (!target) return;
            var open = !target.classList.contains('hidden');
            target.classList.toggle('hidden', open);
            row.classList.toggle('rekap-detail-open', !open);
        });
    });
})();
</script>
