@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('nav-active', 'history')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
    <style>
        /* Footer pop-up: dua tombol ikon (edit & hapus), rata tengah, jarak 8px. */
        #trans-detail-modal .modal-footer-row {
            justify-content: center;
            gap: 8px;
        }
        #trans-detail-modal .btn-modal-action {
            width: 42px;
            height: 42px;
        }

        /* Pop-up konfirmasi hapus tampil di atas pop-up struk. */
        #trans-hapus-confirm {
            z-index: 10001;
        }

        /* Baris label (kiri) - nilai (kanan) pada isi struk. */
        .receipt-line {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 12px;
        }
        .receipt-line > * {
            min-width: 0;
            overflow-wrap: anywhere;
        }
    </style>
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $today = \Illuminate\Support\Carbon::today();
    $yesterday = (new \Illuminate\Support\Carbon($today))->subDay();

    $grouped = collect($transaksis->items())->groupBy(fn ($t) => $t->waktu_kunjungan ? $t->waktu_kunjungan->toDateString() : '');

    $metodeMap = [
        'cash' => ['badge' => 'badge-cash', 'label' => 'Tunai'],
        'qris_bni' => ['badge' => 'badge-qris', 'label' => 'QRIS'],
        'qris_bri' => ['badge' => 'badge-qris', 'label' => 'QRIS'],
        'transfer' => ['badge' => 'badge-transfer', 'label' => 'Transfer'],
        'debit' => ['badge' => 'badge-transfer', 'label' => 'Debit'],
        'kartu_kredit' => ['badge' => 'badge-qris', 'label' => 'Kartu Kredit'],
    ];
@endphp

@section('content')
    {{-- Kontainer query untuk seluruh halaman: grid KPI dan tabel menyesuaikan
         lebar yang TERSEDIA (dipengaruhi sidebar), bukan lebar viewport. --}}
    <div class="trans-page-container">
        <!-- Header Row with "+ Tambah Transaksi" Action -->
        <div class="trans-page-header-row">
            <div class="trans-header-title-group">
                <h1 class="trans-page-title">Daftar Transaksi</h1>
                <p class="trans-page-subtitle">Kelola dan tinjau seluruh riwayat penjualan salon per hari.</p>
            </div>

            <a href="{{ route('transaksi.create') }}" class="btn btn-primary page-primary-action" id="btn-add-transaction">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Transaksi</span>
            </a>
        </div>

        <!-- Mini KPI Sparkline Cards -->
        <div class="trans-kpi-summary-grid">
            <div class="trans-kpi-card">
                <div class="trans-kpi-info">
                    <span class="trans-kpi-label">Total Transaksi</span>
                    <span class="trans-kpi-num">{{ $kpi['total'] }}</span>
                </div>
                <div class="trans-kpi-chart">
                    <svg viewBox="0 0 80 32" class="sparkline-svg sparkline-blue">
                        <path d="M0 24 Q 15 10, 30 20 T 60 8 T 80 14" fill="none" stroke="var(--status-progress-fg)" stroke-width="2.4"/>
                        <path d="M0 24 Q 15 10, 30 20 T 60 8 T 80 14 L 80 32 L 0 32 Z" fill="var(--status-progress-bg)"/>
                    </svg>
                </div>
            </div>

            <div class="trans-kpi-card">
                <div class="trans-kpi-info">
                    <span class="trans-kpi-label">Selesai (Paid)</span>
                    <span class="trans-kpi-num">{{ $kpi['selesai'] }}</span>
                </div>
                <div class="trans-kpi-chart">
                    <svg viewBox="0 0 80 32" class="sparkline-svg sparkline-green">
                        <path d="M0 20 Q 20 6, 40 16 T 70 8 T 80 6" fill="none" stroke="var(--status-ok)" stroke-width="2.4"/>
                        <path d="M0 20 Q 20 6, 40 16 T 70 8 T 80 6 L 80 32 L 0 32 Z" fill="var(--status-ok-bg)"/>
                    </svg>
                </div>
            </div>

            <div class="trans-kpi-card">
                <div class="trans-kpi-info">
                    <span class="trans-kpi-label">Dibatalkan</span>
                    <span class="trans-kpi-num">{{ $kpi['batal'] }}</span>
                </div>
                <div class="trans-kpi-chart">
                    <svg viewBox="0 0 80 32" class="sparkline-svg sparkline-orange">
                        <path d="M0 16 Q 25 24, 50 12 T 80 20" fill="none" stroke="var(--gold-500)" stroke-width="2.4"/>
                        <path d="M0 16 Q 25 24, 50 12 T 80 20 L 80 32 L 0 32 Z" fill="var(--gold-100)"/>
                    </svg>
                </div>
            </div>

            <div class="trans-kpi-card featured-revenue-card">
                <div class="trans-kpi-info">
                    <span class="trans-kpi-label">Total Pemasukan</span>
                    <span class="trans-kpi-num">{{ $fmt($kpi['pemasukan']) }}</span>
                </div>
                <div class="trans-kpi-chart">
                    <svg viewBox="0 0 80 32" class="sparkline-svg sparkline-gold">
                        <path d="M0 22 Q 20 18, 40 8 T 65 14 T 80 4" fill="none" stroke="var(--accent)" stroke-width="2.4"/>
                        <path d="M0 22 Q 20 18, 40 8 T 65 14 T 80 4 L 80 32 L 0 32 Z" fill="var(--gold-a18)"/>
                    </svg>
                </div>
            </div>
        </div>

        @php
            $baseQuery = collect(request()->query())->except(['status', 'page'])->all();
            $tabDefs = [
                'all' => 'Semua Transaksi',
                'selesai' => 'Selesai',
                'batal' => 'Dibatalkan',
            ];
            $statusActive = $statusFilter === '' ? 'all' : $statusFilter;
        @endphp

        <!-- Filter & Search Toolbar -->
        <div class="trans-toolbar-card trans-history-toolbar">
            <form method="GET" action="{{ route('transaksi.index') }}" class="trans-history-form js-toolbar-form">
                <!-- Baris 1: filter status + rentang tanggal + Refresh -->
                <div class="trans-filter-row">
                    {{-- Nilai status selalu ikut terkirim lewat hidden input ini,
                         sehingga pencarian/tanggal tetap mempertahankan filter
                         status baik pada mode pil (desktop) maupun dropdown. --}}
                    <input type="hidden" name="status" value="{{ $statusActive === 'all' ? '' : $statusActive }}">

                    <div class="trans-status-pills" role="group" aria-label="Filter status transaksi">
                        @foreach ($tabDefs as $tabVal => $tabLabel)
                            <a href="{{ route('transaksi.index', array_merge($baseQuery, $tabVal === 'all' ? [] : ['status' => $tabVal])) }}"
                               class="trans-filter-tab {{ $statusActive === $tabVal ? 'active' : '' }}"
                               data-status="{{ $tabVal }}"
                               aria-pressed="{{ $statusActive === $tabVal ? 'true' : 'false' }}">
                                {{ $tabLabel }}
                            </a>
                        @endforeach
                    </div>

                    <div class="trans-status-select-group">
                        <label for="filter-status-select" class="sr-only">Filter status transaksi</label>
                        <select id="filter-status-select" class="trans-status-select js-status-select">
                            @foreach ($tabDefs as $tabVal => $tabLabel)
                                <option value="{{ $tabVal }}" @selected($statusActive === $tabVal)>{{ $tabLabel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="trans-date-filter-group">
                        <input type="date" id="filter-date-from" name="dari" value="{{ $dari ?? '' }}" class="date-input-inline js-auto-submit" title="Dari Tanggal" aria-label="Dari tanggal">
                        <span class="date-separator">-</span>
                        <input type="date" id="filter-date-to" name="sampai" value="{{ $sampai ?? '' }}" class="date-input-inline js-auto-submit" title="Sampai Tanggal" aria-label="Sampai tanggal">
                        <button type="button" class="btn btn-secondary" id="btn-reset-dates" title="Refresh Filter Tanggal">Refresh</button>
                    </div>
                </div>

                <!-- Baris 2: pencarian + tombol Cari -->
                <div class="trans-search-row">
                    @if ($layananFilter > 0)
                        <input type="hidden" name="layanan" value="{{ $layananFilter }}">
                    @endif
                    @if ($metodeFilter !== '')
                        <input type="hidden" name="metode" value="{{ $metodeFilter }}">
                    @endif

                    <div class="trans-search-box">
                        <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="transaction-search" name="q" value="{{ $q }}" class="search-input" placeholder="Cari ID, nama pelanggan, layanan...">
                    </div>
                    <button type="submit" class="btn-cari" id="btn-transaction-search">Cari</button>
                </div>
            </form>
        </div>

        <!-- Grouped Transaction Container per Day -->
        <div class="trans-daily-groups-container" id="transaction-history-list" data-field="transaction-history-list">
            @forelse ($grouped as $tanggal => $dayTrxs)
                @php
                    $dateObj = \Illuminate\Support\Carbon::parse($tanggal);
                    $dayName = $namaHari[$dateObj->dayOfWeek];
                    $dateLabel = $dayName.', '.$dateObj->day.' '.$namaBulan[$dateObj->month - 1].' '.$dateObj->year;
                    if ($dateObj->isSameDay($today)) {
                        $heading = 'Hari Ini — '.$dateLabel;
                    } elseif ($dateObj->isSameDay($yesterday)) {
                        $heading = 'Kemarin — '.$dateLabel;
                    } else {
                        $heading = $dateLabel;
                    }
                    $dayTotal = $dayTrxs->sum(fn ($t) => $t->status === 'selesai' ? (float) $t->total_bayar : 0);
                @endphp

                <div class="daily-group-card">
                    <div class="daily-group-header">
                        <div class="daily-group-title">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--text-link)" stroke-width="2.5">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>{{ $heading }}</span>
                        </div>
                        <div class="daily-group-summary">
                            <span class="daily-group-badge">{{ $dayTrxs->count() }} Transaksi</span>
                            <span class="daily-total-amount">{{ $fmt($dayTotal) }}</span>
                        </div>
                    </div>

                    <div class="table-responsive-wrapper">
                        <table class="daily-trans-table">
                            <thead>
                                <tr>
                                    <th class="col-id">ID / Waktu</th>
                                    <th class="col-client">Pelanggan</th>
                                    <th class="col-service">Layanan &amp; Stylist</th>
                                    <th class="col-total">Total Biaya</th>
                                    <th class="col-method">Metode Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($dayTrxs as $t)
                                    @php
                                        $detailRows = $t->details->where('subtotal', '>', 0);
                                        $daftarLayanan = $detailRows->map(function ($d) {
                                            if ($d->tipe_item === 'layanan' && $d->layanan) {
                                                return $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                                            }
                                            if ($d->tipe_item === 'produk' && $d->produk) {
                                                return $d->produk->nama_produk;
                                            }
                                            return '(Item)';
                                        })->unique()->values();
                                        $layananUtama = $daftarLayanan->first() ?? '-';
                                        $layananSisa = max(0, $daftarLayanan->count() - 1);
                                        $daftarStaf = $t->details->pluck('staf.nama')->filter()->unique()->values();
                                        $iniNama = collect(preg_split('/\s+/', trim((string) ($t->pelanggan->nama ?? ''))))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
                                        $methodInfo = $metodeMap[$t->metode_pembayaran] ?? ['badge' => '', 'label' => $t->labelMetode()];
                                    @endphp

                                    <tr data-trx-id="{{ $t->id }}"
                                        data-edit-url="{{ route('transaksi.edit', $t) }}"
                                        data-delete-url="{{ route('transaksi.destroy', $t) }}"
                                        data-no-struk="{{ $t->no_struk }}"
                                        data-status="{{ $t->status }}">
                                        <td class="col-id">
                                            <span class="trans-order-id-link trans-row-link" title="Buka struk {{ $t->no_struk }}">{{ $t->no_struk }}</span>
                                            <div class="trans-time-val">{{ $t->waktu_kunjungan ? $t->waktu_kunjungan->format('H:i') : '--:--' }} WIB</div>
                                        </td>
                                        <td class="col-client">
                                            <div class="trans-client-meta">
                                                <span class="trans-client-avatar">{{ $iniNama }}</span>
                                                <div class="trans-client-text">
                                                    <div class="trans-client-name" title="{{ $t->pelanggan->nama ?? '-' }}">{{ $t->pelanggan->nama ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="col-service">
                                            <div class="trans-service-title" title="{{ $daftarLayanan->join(', ') }}">
                                                <span class="trans-service-name">{{ $layananUtama }}</span>
                                                @if ($layananSisa > 0)<span class="trans-service-more">+{{ $layananSisa }}</span>@endif
                                            </div>
                                            <div class="trans-worker-tag" title="Stylist: {{ $daftarStaf->join(', ') }}">Stylist: <strong>{{ $daftarStaf->first() ?: '-' }}</strong>@if ($daftarStaf->count() > 1)<span class="trans-service-more">+{{ $daftarStaf->count() - 1 }}</span>@endif</div>
                                        </td>
                                        <td class="col-total">
                                            <div class="trans-amount-cell">{{ $fmt($t->total_bayar) }}</div>
                                        </td>
                                        <td class="col-method">
                                            <span class="payment-method-badge {{ $methodInfo['badge'] }}">{{ $methodInfo['label'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="empty-state-card">
                    <div class="empty-state-content">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        @if ($q !== '' || ($dari ?? '') !== '' || ($sampai ?? '') !== '' || $statusFilter !== '' || $layananFilter > 0)
                            <p class="empty-state-text">Tidak ada transaksi yang cocok dengan filter yang dipilih.</p>
                        @else
                            <p class="empty-state-text">Belum ada transaksi. Klik "Tambah Transaksi" untuk membuat transaksi pertama.</p>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        @if ($transaksis->hasPages())
            @include('partials.pagination', ['paginator' => $transaksis])
        @endif

        {{-- Isi struk per transaksi (server-rendered). Pop-up hanya menyalin
             template ini, jadi tampilannya selalu sinkron dengan data asli. --}}
        @foreach (collect($transaksis->items()) as $t)
            <template id="tpl-receipt-{{ $t->id }}">
                @php
                    $tplDetails = $t->details->where('subtotal', '>', 0);
                    $tplStaf = $tplDetails->pluck('staf.nama')->filter()->unique();
                @endphp

                <div style="text-align: center; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px; margin-bottom: 12px;">
                    <h2 style="font-size: 1.3rem; font-weight: 900; margin: 0; color: var(--text-primary);">Afwo. Hair Design</h2>
                    <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0;">Official Salon Payment Receipt</p>
                </div>

                <div class="receipt-line" style="font-size: 0.82rem; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">No. Transaksi:</span>
                    <strong style="font-family: monospace; text-align: right;">{{ $t->no_struk }}</strong>
                </div>
                <div class="receipt-line" style="font-size: 0.82rem; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Waktu:</span>
                    <strong style="text-align: right;">{{ $t->waktu_kunjungan ? $t->waktu_kunjungan->format('d M Y - H:i') : '-' }} WIB</strong>
                </div>
                <div class="receipt-line" style="font-size: 0.82rem; margin-bottom: 8px;">
                    <span style="color: var(--text-muted);">Pelanggan:</span>
                    <strong style="text-align: right;">{{ $t->pelanggan->nama ?? '-' }}{{ $t->pelanggan && $t->pelanggan->no_wa ? ' ('.$t->pelanggan->no_wa.')' : '' }}</strong>
                </div>
                <div class="receipt-line" style="font-size: 0.82rem; margin-bottom: 12px;">
                    <span style="color: var(--text-muted);">Stylist:</span>
                    <strong style="text-align: right;">{{ $tplStaf->join(' & ') ?: '-' }}</strong>
                </div>

                <div style="border-top: 1px dashed var(--border-color); border-bottom: 1px dashed var(--border-color); padding: 10px 0; margin-bottom: 12px;">
                    @forelse ($tplDetails as $d)
                        @php
                            if ($d->tipe_item === 'layanan' && $d->layanan) {
                                $dNama = $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                            } elseif ($d->tipe_item === 'produk' && $d->produk) {
                                $dNama = $d->produk->nama_produk;
                            } else {
                                $dNama = '(Item)';
                            }
                        @endphp
                        <div class="receipt-line" style="font-weight: 700; font-size: 0.88rem; margin-bottom: 4px;">
                            <span>{{ $dNama }}{{ (float) $d->qty != 1 ? ' × '.rtrim(rtrim((string) $d->qty, '0'), '.') : '' }}</span>
                            <span style="text-align: right;">{{ $fmt($d->subtotal) }}</span>
                        </div>
                        @forelse ($d->produkPenggunaan as $pu)
                            <div class="receipt-line" style="font-size: 0.75rem; color: var(--text-muted); margin: 2px 0 2px 12px;">
                                <span>├ Pemakaian: {{ $pu->produk->nama_produk ?? 'Produk' }} ({{ rtrim(rtrim((string) $pu->pemakaian_ml, '0'), '.') }} ml)</span>
                                <span>-</span>
                            </div>
                        @empty
                        @endforelse
                    @empty
                        <div style="font-size: 0.82rem; color: var(--text-muted);">Tidak ada item.</div>
                    @endforelse
                </div>

                <div class="receipt-line" style="font-size: 1.05rem; font-weight: 900; color: var(--text-link); margin-bottom: 8px;">
                    <span>TOTAL:</span>
                    <span>{{ $fmt($t->total_bayar) }}</span>
                </div>
                <div class="receipt-line" style="font-size: 0.82rem; color: var(--text-muted);">
                    <span>Metode Pembayaran:</span>
                    <span style="font-weight: 700; color: var(--text-primary);">{{ $t->labelMetode() }}</span>
                </div>
            </template>
        @endforeach
    </div>
@endsection

@push('modals')
    {{-- Pop-up Detail Struk Transaksi. Isinya disalin dari template
         #tpl-receipt-<id> agar tanpa permintaan ke server. --}}
    <div id="trans-detail-modal" class="modal-backdrop" role="dialog" aria-modal="true"
         aria-labelledby="trans-detail-modal-title" aria-hidden="true">
        <div class="modal-dialog-card" tabindex="-1">
            <div class="modal-header-row">
                <h3 class="modal-header-title" id="trans-detail-modal-title">Detail Struk Transaksi</h3>
                <button type="button" class="btn-close-modal" id="btn-close-modal" aria-label="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body-content" id="modal-receipt-body"></div>

            <div class="modal-footer-row">
                <a href="#" class="btn-modal-action btn-receipt-edit" id="btn-edit-transaksi" title="Edit transaksi" aria-label="Edit transaksi">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                    </svg>
                </a>
                <form id="form-hapus-transaksi" method="POST" action="#" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-modal-action btn-receipt-delete" id="btn-hapus-transaksi" title="Hapus transaksi" aria-label="Hapus transaksi">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Konfirmasi hapus memakai komponen modal aplikasi (bukan confirm bawaan). --}}
    <div id="trans-hapus-confirm" class="modal-backdrop" role="dialog" aria-modal="true"
         aria-labelledby="trans-hapus-confirm-title" aria-hidden="true">
        <div class="modal-dialog-card" tabindex="-1">
            <div class="modal-header-row">
                <h3 class="modal-header-title" id="trans-hapus-confirm-title">Hapus Transaksi?</h3>
                <button type="button" class="btn-close-modal" id="btn-close-confirm-hapus" aria-label="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div class="modal-body-content">
                <p style="font-size: 0.88rem; color: var(--text-secondary); margin: 0; line-height: 1.6;">
                    Transaksi <strong id="confirm-no-struk" style="font-family: monospace;"></strong> akan dihapus. Stok produk akan dikembalikan.
                </p>
            </div>

            <div class="modal-footer-row">
                <button type="button" class="btn btn-secondary" id="btn-batal-hapus">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-konfirmasi-hapus">Ya, Hapus</button>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toolbar = document.querySelector('.js-toolbar-form');

            function submitToolbar() {
                if (toolbar) toolbar.submit();
            }

            var debounceTimer = null;
            var searchInput = document.getElementById('transaction-search');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(submitToolbar, 600);
                });
            }

            if (toolbar) {
                toolbar.querySelectorAll('.js-auto-submit').forEach(function (el) {
                    el.addEventListener('change', submitToolbar);
                });
            }

            var statusSelect = document.querySelector('.js-status-select');
            if (statusSelect) {
                statusSelect.addEventListener('change', function () {
                    var tab = document.querySelector('.trans-filter-tab[data-status="' + statusSelect.value + '"]');
                    if (tab && tab.href) {
                        window.location.href = tab.href;
                    } else {
                        submitToolbar();
                    }
                });
            }

            var btnResetDates = document.getElementById('btn-reset-dates');
            if (btnResetDates) {
                btnResetDates.addEventListener('click', function () {
                    var from = document.getElementById('filter-date-from');
                    var to = document.getElementById('filter-date-to');
                    if (from) from.value = '';
                    if (to) to.value = '';
                    submitToolbar();
                });
            }

            /* ---- Pop-up Detail Struk Transaksi ---- */
            var modal = document.getElementById('trans-detail-modal');
            if (!modal) return;

            var card = modal.querySelector('.modal-dialog-card');
            var modalBody = document.getElementById('modal-receipt-body');
            var editBtn = document.getElementById('btn-edit-transaksi');
            var hapusForm = document.getElementById('form-hapus-transaksi');
            var hapusBtn = document.getElementById('btn-hapus-transaksi');
            var closeBtn = document.getElementById('btn-close-modal');

            var confirmModal = document.getElementById('trans-hapus-confirm');
            var confirmCard = confirmModal ? confirmModal.querySelector('.modal-dialog-card') : null;
            var confirmNo = document.getElementById('confirm-no-struk');
            var confirmBtn = document.getElementById('btn-konfirmasi-hapus');
            var batalBtn = document.getElementById('btn-batal-hapus');
            var confirmClose = document.getElementById('btn-close-confirm-hapus');

            var lastFocused = null;

            function lockScroll() { document.body.classList.add('modal-open'); }
            function unlockScroll() { document.body.classList.remove('modal-open'); }

            function focusables(scope) {
                if (!scope) return [];
                return Array.prototype.slice.call(
                    scope.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])')
                ).filter(function (el) { return el.offsetParent !== null; });
            }

            function openModal(row, trigger) {
                lastFocused = trigger || document.activeElement;
                var status = row.getAttribute('data-status');
                var tpl = document.getElementById('tpl-receipt-' + row.getAttribute('data-trx-id'));
                if (!tpl) return;

                if (editBtn) {
                    editBtn.setAttribute('href', row.getAttribute('data-edit-url') || '#');
                    editBtn.style.display = status === 'batal' ? 'none' : '';
                }
                if (hapusForm) hapusForm.setAttribute('action', row.getAttribute('data-delete-url') || '#');
                if (hapusBtn) hapusBtn.setAttribute('data-no-struk', row.getAttribute('data-no-struk') || '');

                modalBody.innerHTML = '';
                modalBody.appendChild(tpl.content.cloneNode(true));

                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                lockScroll();
                card.focus();
            }

            function closeConfirm() {
                if (!confirmModal || !confirmModal.classList.contains('show')) return;
                confirmModal.classList.remove('show');
                confirmModal.setAttribute('aria-hidden', 'true');
                if (hapusBtn) hapusBtn.focus();
            }

            function closeModal() {
                if (!modal.classList.contains('show')) return;
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                modalBody.innerHTML = '';
                closeConfirm();
                unlockScroll();
                if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
                lastFocused = null;
            }

            function openConfirm() {
                if (!confirmModal || !confirmCard) {
                    if (hapusForm) hapusForm.submit();
                    return;
                }
                if (confirmNo) confirmNo.textContent = hapusBtn ? (hapusBtn.getAttribute('data-no-struk') || '') : '';
                confirmModal.classList.add('show');
                confirmModal.setAttribute('aria-hidden', 'false');
                confirmCard.focus();
            }

            /* Klik no. struk pada baris membuka pop-up struk. */
            var list = document.getElementById('transaction-history-list');
            if (list) {
                list.addEventListener('click', function (e) {
                    var link = e.target.closest ? e.target.closest('.trans-row-link') : null;
                    if (!link) return;
                    var row = link.closest('tr[data-trx-id]');
                    if (!row) return;
                    openModal(row, link);
                });
            }

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            if (confirmModal) {
                confirmModal.addEventListener('click', function (e) {
                    if (e.target === confirmModal) closeConfirm();
                });
            }
            if (batalBtn) batalBtn.addEventListener('click', closeConfirm);
            if (confirmClose) confirmClose.addEventListener('click', closeConfirm);
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (hapusForm) hapusForm.submit();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (!modal.classList.contains('show')) return;
                var confirmOpen = confirmModal && confirmModal.classList.contains('show');

                if (e.key === 'Escape') {
                    if (confirmOpen) { closeConfirm(); } else { closeModal(); }
                    return;
                }

                if (e.key === 'Tab') {
                    var f = focusables(confirmOpen ? confirmCard : card);
                    if (!f.length) return;
                    var first = f[0];
                    var last = f[f.length - 1];
                    if (e.shiftKey && document.activeElement === first) {
                        e.preventDefault();
                        last.focus();
                    } else if (!e.shiftKey && document.activeElement === last) {
                        e.preventDefault();
                        first.focus();
                    }
                }
            });

            /* Hapus dikonfirmasi lewat pop-up aplikasi, bukan confirm() bawaan. */
            if (hapusForm) {
                hapusForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    openConfirm();
                });
            }
        });
    </script>
@endpush
