@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('nav-active', 'history')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    $today = \Illuminate\Support\Carbon::today();
    $yesterday = (new \Illuminate\Support\Carbon($today))->subDay();

    $grouped = collect($transaksis->items())->groupBy(fn ($t) => $t->waktu_kunjungan ? $t->waktu_kunjungan->toDateString() : '');

    $statusMap = [
        'selesai' => ['pill' => 'status-paid', 'label' => 'Selesai (Paid)'],
        'batal' => ['pill' => 'status-cancelled', 'label' => 'Dibatalkan'],
    ];
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

    <!-- Filter & Search Toolbar -->
    <div class="trans-toolbar-card">
        <div class="trans-tabs-row">
            @php
                $baseQuery = collect(request()->query())->except(['status', 'page'])->all();
                $tabDefs = [
                    'all' => 'Semua Transaksi',
                    'selesai' => 'Selesai',
                    'batal' => 'Dibatalkan',
                ];
            @endphp
            @foreach ($tabDefs as $tabVal => $tabLabel)
                <a href="{{ route('transaksi.index', array_merge($baseQuery, $tabVal === 'all' ? [] : ['status' => $tabVal])) }}"
                   class="trans-filter-tab {{ $statusFilter === $tabVal || ($statusFilter === '' && $tabVal === 'all') ? 'active' : '' }}"
                   data-status="{{ $tabVal }}"
                   aria-pressed="{{ ($statusFilter === $tabVal || ($statusFilter === '' && $tabVal === 'all')) ? 'true' : 'false' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('transaksi.index') }}" class="trans-search-filter-controls js-toolbar-form">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
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

            <div class="trans-date-filter-group">
                <input type="date" id="filter-date-from" name="dari" value="{{ $dari ?? '' }}" class="date-input-inline js-auto-submit" title="Dari Tanggal">
                <span class="date-separator">-</span>
                <input type="date" id="filter-date-to" name="sampai" value="{{ $sampai ?? '' }}" class="date-input-inline js-auto-submit" title="Sampai Tanggal">
                <button type="button" class="btn btn-secondary" id="btn-reset-dates" title="Refresh Filter Tanggal">Refresh</button>
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
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="daily-group-badge">{{ $dayTrxs->count() }} Transaksi</span>
                        <span style="font-size: 0.82rem; font-weight: 800; color: var(--text-link);">{{ $fmt($dayTotal) }}</span>
                    </div>
                </div>

                <div class="table-responsive-wrapper">
                    <table class="daily-trans-table">
                        <thead>
                            <tr>
                                <th>ID / Waktu</th>
                                <th>Pelanggan</th>
                                <th>Layanan &amp; Stylist</th>
                                <th>Total Biaya</th>
                                <th>Metode Bayar</th>
                                <th>Status</th>
                                <th style="text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dayTrxs as $t)
                                @php
                                    $detailRows = $t->details->where('subtotal', '>', 0);
                                    $namaBarang = $detailRows->map(function ($d) {
                                        if ($d->tipe_item === 'layanan' && $d->layanan) {
                                            return $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                                        }
                                        if ($d->tipe_item === 'produk' && $d->produk) {
                                            return $d->produk->nama_produk;
                                        }
                                        return '(Item)';
                                    })->unique()->take(2)->values();
                                    $stafNames = $t->details->pluck('staf.nama')->filter()->unique()->take(2)->values();
                                    $iniNama = collect(preg_split('/\s+/', trim((string) ($t->pelanggan->nama ?? ''))))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
                                    $methodInfo = $metodeMap[$t->metode_pembayaran] ?? ['badge' => '', 'label' => $t->labelMetode()];
                                    $statusInfo = $statusMap[$t->status] ?? ['pill' => '', 'label' => ucfirst($t->status ?? '')];
                                @endphp

                                <tr data-trx-id="{{ $t->id }}">
                                    <td style="width: 110px;">
                                        <a href="{{ route('transaksi.show', $t) }}" class="trans-order-id-link js-open-receipt" data-trx-id="{{ $t->id }}">{{ $t->no_struk }}</a>
                                        <div style="font-size: 0.72rem; color: var(--text-muted);">{{ $t->waktu_kunjungan ? $t->waktu_kunjungan->format('H:i') : '--:--' }} WIB</div>
                                    </td>
                                    <td>
                                        <div class="trans-client-meta">
                                            <span class="trans-client-avatar">{{ $iniNama }}</span>
                                            <div>
                                                <div class="trans-client-name">{{ $t->pelanggan->nama ?? '-' }}</div>
                                                <div class="trans-client-phone">{{ $t->pelanggan->no_wa ?? 'No. WA belum diisi' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="trans-service-title">{{ $namaBarang->join(', ') }}</div>
                                        <div class="trans-worker-tag">Stylist: <strong>{{ $stafNames->join(', ') ?: '-' }}</strong></div>
                                    </td>
                                    <td>
                                        <div class="trans-amount-cell">{{ $fmt($t->total_bayar) }}</div>
                                    </td>
                                    <td>
                                        <span class="payment-method-badge {{ $methodInfo['badge'] }}">{{ $methodInfo['label'] }}</span>
                                    </td>
                                    <td>
                                        <span class="status-pill {{ $statusInfo['pill'] }}">{{ $statusInfo['label'] }}</span>
                                    </td>
                                    <td style="text-align: right;">
<<<<<<< HEAD
                                        <button type="button" class="btn-detail-receipt js-open-receipt"
                                                data-trx-id="{{ $t->id }}"
                                                data-no-struk="{{ $t->no_struk }}"
                                                data-status="{{ $t->status }}"
                                                data-edit-url="{{ route('transaksi.edit', $t) }}"
                                                data-delete-url="{{ route('transaksi.destroy', $t) }}">Detail Struk</button>
=======
                                        <button type="button" class="btn-detail-receipt js-open-receipt" data-trx-id="{{ $t->id }}">Detail Struk</button>
>>>>>>> c46369f0ea4073068b460ba005de65051d14d1ec
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

    <!-- Receipt Templates (server-rendered, dipakai modal) -->
    @foreach (collect($transaksis->items()) as $t)
        <template id="tpl-receipt-{{ $t->id }}">
            @php
                $detailRows = $t->details->where('subtotal', '>', 0);
            @endphp
            <div style="text-align: center; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px; margin-bottom: 12px;">
                <h2 style="font-size: 1.3rem; font-weight: 900; margin: 0; color: var(--text-primary);">Afwo. Hair Design</h2>
                <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0;">Official Salon Payment Receipt</p>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">No. Transaksi:</span>
                <strong style="font-family: monospace;">{{ $t->no_struk }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Waktu:</span>
                <strong>{{ $t->waktu_kunjungan ? $t->waktu_kunjungan->format('d M Y - H:i') : '-' }} WIB</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 8px;">
                <span style="color: var(--text-muted);">Pelanggan:</span>
                <strong>{{ $t->pelanggan->nama ?? '-' }}{{ $t->pelanggan && $t->pelanggan->no_wa ? ' ('.$t->pelanggan->no_wa.')' : '' }}</strong>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 12px;">
                <span style="color: var(--text-muted);">Stylist:</span>
                <strong>{{ $detailRows->pluck('staf.nama')->filter()->unique()->join(', ') ?: '-' }}</strong>
            </div>

            <div style="border-top: 1px dashed var(--border-color); border-bottom: 1px dashed var(--border-color); padding: 10px 0; margin-bottom: 12px;">
                @forelse ($detailRows as $d)
                    @php
                        if ($d->tipe_item === 'layanan' && $d->layanan) {
                            $dNama = $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                        } elseif ($d->tipe_item === 'produk' && $d->produk) {
                            $dNama = $d->produk->nama_produk;
                        } else {
                            $dNama = '(Item)';
                        }
                    @endphp
                    <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 0.88rem; margin-bottom: 4px;">
                        <span>{{ $dNama }}{{ (float) $d->qty != 1 ? ' × '.rtrim(rtrim((string) $d->qty, '0'), '.') : '' }}</span>
                        <span>{{ $fmt($d->subtotal) }}</span>
                    </div>
                    @forelse ($d->produkPenggunaan as $pu)
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin: 2px 0 2px 12px;">
                            <span>├ Pemakaian: {{ $pu->produk->nama_produk ?? 'Produk' }} ({{ rtrim(rtrim((string) $pu->pemakaian_ml, '0'), '.') }} ml)</span>
                            <span>-</span>
                        </div>
                    @empty
                    @endforelse
                @empty
                    <div style="font-size: 0.82rem; color: var(--text-muted);">Tidak ada item.</div>
                @endforelse
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 1.05rem; font-weight: 900; color: var(--text-link); margin-bottom: 8px;">
                <span>TOTAL:</span>
                <span>{{ $fmt($t->total_bayar) }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--text-muted);">
                <span>Metode Pembayaran:</span>
                <span style="font-weight: 700; color: var(--text-primary);">{{ $t->labelMetode() }}</span>
            </div>
        </template>
    @endforeach

    <!-- Detail Transaksi Modal Dialog -->
    <div id="trans-detail-modal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-dialog-card">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Detail Struk Transaksi</h3>
<<<<<<< HEAD
                <button type="button" class="btn-close-modal" id="btn-close-modal" aria-label="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body-content" id="modal-receipt-body"></div>
            <div class="modal-footer-row">
                <a href="#" class="btn-modal-action btn-receipt-edit" id="btn-edit-transaksi" title="Edit transaksi" aria-label="Edit transaksi">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"></path>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"></path>
                    </svg>
                </a>
                <form id="form-hapus-transaksi" method="POST" action="#" style="margin: 0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-modal-action btn-receipt-delete" id="btn-hapus-transaksi" title="Hapus transaksi" aria-label="Hapus transaksi">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="10" y1="11" x2="10" y2="17"></line>
                            <line x1="14" y1="11" x2="14" y2="17"></line>
                        </svg>
                    </button>
                </form>
                <button type="button" class="btn-modal-action btn-receipt-print" id="btn-print-receipt" title="Cetak struk" aria-label="Cetak struk">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                </button>
=======
                <button type="button" class="btn-close-modal" id="btn-close-modal">✕</button>
            </div>
            <div class="modal-body-content" id="modal-receipt-body"></div>
            <div class="modal-footer-row">
                <button type="button" class="btn-print-receipt" id="btn-print-receipt">Cetak Struk</button>
                <button type="button" class="btn-modal-close-action" id="btn-modal-close-action">Tutup</button>
>>>>>>> c46369f0ea4073068b460ba005de65051d14d1ec
            </div>
        </div>
    </div>
@endsection

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

            toolbar.querySelectorAll('.js-auto-submit').forEach(function (el) {
                el.addEventListener('change', submitToolbar);
            });

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

            var modal = document.getElementById('trans-detail-modal');
            var modalBody = document.getElementById('modal-receipt-body');
<<<<<<< HEAD
            var editBtn = document.getElementById('btn-edit-transaksi');
            var hapusForm = document.getElementById('form-hapus-transaksi');
            var hapusBtn = document.getElementById('btn-hapus-transaksi');
=======
>>>>>>> c46369f0ea4073068b460ba005de65051d14d1ec

            document.querySelectorAll('.js-open-receipt').forEach(function (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    var id = btn.getAttribute('data-trx-id');
                    var tpl = document.getElementById('tpl-receipt-' + id);
                    if (!tpl || !modalBody || !modal) return;
<<<<<<< HEAD

                    var status = btn.getAttribute('data-status');
                    if (editBtn) {
                        editBtn.setAttribute('href', btn.getAttribute('data-edit-url') || '#');
                        editBtn.style.display = status === 'batal' ? 'none' : '';
                    }
                    if (hapusForm) hapusForm.setAttribute('action', btn.getAttribute('data-delete-url') || '#');
                    if (hapusBtn) hapusBtn.setAttribute('data-no-struk', btn.getAttribute('data-no-struk') || '');

=======
>>>>>>> c46369f0ea4073068b460ba005de65051d14d1ec
                    modalBody.innerHTML = '';
                    modalBody.appendChild(tpl.content.cloneNode(true));
                    modal.classList.add('show');
                });
            });

<<<<<<< HEAD
            if (hapusForm) {
                hapusForm.addEventListener('submit', function (e) {
                    var no = hapusBtn ? hapusBtn.getAttribute('data-no-struk') : '';
                    if (!confirm('Hapus transaksi "' + no + '"? Stok produk akan dikembalikan.')) {
                        e.preventDefault();
                    }
                });
            }

            var closes = [
                document.getElementById('btn-close-modal')
=======
            var closes = [
                document.getElementById('btn-close-modal'),
                document.getElementById('btn-modal-close-action')
>>>>>>> c46369f0ea4073068b460ba005de65051d14d1ec
            ];
            closes.forEach(function (btn) {
                if (btn) btn.addEventListener('click', function () {
                    if (modal) modal.classList.remove('show');
                });
            });

            var btnPrint = document.getElementById('btn-print-receipt');
            if (btnPrint) btnPrint.addEventListener('click', function () {
                window.print();
            });
        });
    </script>
@endpush