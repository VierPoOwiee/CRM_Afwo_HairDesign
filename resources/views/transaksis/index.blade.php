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
                                    <th class="col-status">Status</th>
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
                                        $statusInfo = $statusMap[$t->status] ?? ['pill' => '', 'label' => ucfirst($t->status ?? '')];
                                    @endphp

                                    <tr data-trx-id="{{ $t->id }}"
                                        data-struk-url="{{ route('transaksi.struk', $t) }}"
                                        data-detail-url="{{ route('transaksi.show', $t) }}"
                                        data-edit-url="{{ route('transaksi.edit', $t) }}"
                                        data-delete-url="{{ route('transaksi.destroy', $t) }}"
                                        data-no-struk="{{ $t->no_struk }}"
                                        data-status="{{ $t->status }}">
                                        <td class="col-id">
                                            <a href="{{ route('transaksi.show', $t) }}" class="trans-order-id-link trans-row-link" title="Lihat detail {{ $t->no_struk }}">{{ $t->no_struk }}</a>
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
                                        <td class="col-status">
                                            <span class="status-pill {{ $statusInfo['pill'] }}">{{ $statusInfo['label'] }}</span>
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
    </div>
@endsection

@push('modals')
    {{-- Pop-up Detail Struk Transaksi. Konten struk dimuat dari server
         (endpoint transaksi.struk) agar selalu sinkron dengan data asli. --}}
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

            <div class="modal-body-content" id="modal-receipt-body">
                <div class="struk-loading" role="status" aria-live="polite">
                    <span class="struk-spinner" aria-hidden="true"></span>
                    <span class="sr-only">Memuat struk…</span>
                </div>
            </div>

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
                <button type="button" class="btn-modal-action btn-receipt-print" id="btn-print-receipt" title="Cetak struk" aria-label="Cetak struk">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                </button>
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
            var btnPrint = document.getElementById('btn-print-receipt');
            var lastFocused = null;
            var requestSeq = 0;

            function lockScroll() { document.body.classList.add('modal-open'); }
            function unlockScroll() { document.body.classList.remove('modal-open'); }

            function focusables() {
                return Array.prototype.slice.call(
                    card.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])')
                ).filter(function (el) { return el.offsetParent !== null; });
            }

            function showModal() {
                modal.classList.add('show');
                modal.setAttribute('aria-hidden', 'false');
                lockScroll();
                card.focus();
            }

            function openModal(row, trigger) {
                lastFocused = trigger || document.activeElement;
                var status = row.getAttribute('data-status');

                if (editBtn) {
                    editBtn.setAttribute('href', row.getAttribute('data-edit-url') || '#');
                    editBtn.style.display = status === 'batal' ? 'none' : '';
                }
                if (hapusForm) hapusForm.setAttribute('action', row.getAttribute('data-delete-url') || '#');
                if (hapusBtn) hapusBtn.setAttribute('data-no-struk', row.getAttribute('data-no-struk') || '');

                modalBody.innerHTML = '<div class="struk-loading" role="status" aria-live="polite">'
                    + '<span class="struk-spinner" aria-hidden="true"></span>'
                    + '<span class="sr-only">Memuat struk…</span></div>';
                showModal();

                var url = row.getAttribute('data-struk-url');
                if (!url) { lastFocused = null; return; }

                var seq = ++requestSeq;
                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    credentials: 'same-origin'
                }).then(function (res) {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.text();
                }).then(function (html) {
                    if (seq !== requestSeq) return;
                    modalBody.innerHTML = html;
                }).catch(function () {
                    if (seq !== requestSeq) return;
                    var detailUrl = row.getAttribute('data-detail-url') || '#';
                    modalBody.innerHTML = '<div class="struk-error" role="alert">'
                        + '<p>Gagal memuat struk.</p>'
                        + '<div class="struk-error-actions">'
                        + '<button type="button" class="struk-retry">Coba lagi</button>'
                        + '<a class="struk-open-detail" href="' + detailUrl + '">Buka halaman detail</a>'
                        + '</div></div>';
                    var retry = modalBody.querySelector('.struk-retry');
                    if (retry) {
                        retry.addEventListener('click', function () { openModal(row, trigger); });
                    }
                });
            }

            function closeModal() {
                if (!modal.classList.contains('show')) return;
                modal.classList.remove('show');
                modal.setAttribute('aria-hidden', 'true');
                requestSeq++;
                modalBody.innerHTML = '';
                unlockScroll();
                if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
                lastFocused = null;
            }

            /* Klik baris membuka pop-up. Ctrl/Cmd/Shift/Alt & klik tengah tetap
               membuka halaman detail penuh (transaksi.show) sebagai fallback. */
            var list = document.getElementById('transaction-history-list');
            if (list) {
                list.addEventListener('click', function (e) {
                    var link = e.target.closest ? e.target.closest('.trans-row-link') : null;
                    if (!link) return;
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0) return;
                    var row = link.closest('tr[data-struk-url]');
                    if (!row) return;
                    e.preventDefault();
                    openModal(row, link);
                });
            }

            if (closeBtn) closeBtn.addEventListener('click', closeModal);
            modal.addEventListener('click', function (e) {
                if (e.target === modal) closeModal();
            });

            document.addEventListener('keydown', function (e) {
                if (!modal.classList.contains('show')) return;
                if (e.key === 'Escape') { closeModal(); return; }
                if (e.key === 'Tab') {
                    var f = focusables();
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

            if (hapusForm) {
                hapusForm.addEventListener('submit', function (e) {
                    var no = hapusBtn ? hapusBtn.getAttribute('data-no-struk') : '';
                    if (!confirm('Hapus transaksi "' + no + '"? Stok produk akan dikembalikan.')) {
                        e.preventDefault();
                    }
                });
            }

            if (btnPrint) {
                btnPrint.addEventListener('click', function () {
                    document.body.classList.add('printing-struk');
                    window.print();
                });
            }
            window.addEventListener('afterprint', function () {
                document.body.classList.remove('printing-struk');
            });
        });
    </script>
@endpush
