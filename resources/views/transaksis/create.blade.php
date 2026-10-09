@extends('layouts.app')

@section('title', 'Tambah Transaksi')

@section('nav-active', 'history')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/add-transaction.css') }}">
@endpush

@section('content')
    <!-- Page Heading -->
    <div class="form-page-head">
        <div class="form-page-title-group">
            <a href="{{ route('transaksi.index') }}" class="btn-back-ghost" aria-label="Kembali ke Riwayat Transaksi">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 12H5"></path>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div class="trans-header-title-group">
                <h1 class="form-page-title">Tambah Transaksi</h1>
                <p class="form-page-subtitle">Catat kunjungan layanan / penjualan produk pelanggan.</p>
            </div>
        </div>
    </div>

    <form id="form-add-transaction" class="form-stack" action="{{ route('transaksi.store') }}" method="POST">
        @csrf

        {{-- Card: Interconnected Searchable Combobox untuk Pilih/Tambah Pelanggan --}}
        <article class="card customer-combobox-card" data-field="customer-card">
            <div class="combobox-group-item">
                <label class="form-label" for="pelanggan_search">NAMA PELANGGAN</label>
                <div class="combobox-wrapper" id="name-combobox-wrapper">
                    <div class="combobox-input-box">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <input type="text" id="pelanggan_search" class="combobox-input" autocomplete="off" placeholder="Ketik atau cari nama pelanggan...">
                        <input type="hidden" name="id_pelanggan" id="id_pelanggan" value="{{ old('id_pelanggan') }}">
                        <button type="button" id="btn-toggle-combobox" class="combobox-toggle-btn" aria-label="Buka pilihan pelanggan">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                    </div>
                    <div id="pelanggan_results" class="search-results-dropdown"></div>
                </div>
                <div id="pelanggan_selected" class="selected-client-chip hidden">
                    <span id="pelanggan_nama"></span>
                    <span class="chip-actions">
                        <button type="button" onclick="clearPelanggan()" class="chip-clear" title="Hapus pilihan">&times;</button>
                        <button type="button" id="addPelangganBtn" class="chip-addmore">+ Pelanggan Baru</button>
                    </span>
                </div>
            </div>

            <div class="combobox-group-item">
                <label class="form-label" for="customer-phone-input">PHONE NUMBER <span class="unique-key-hint">(ID Unik Database)</span></label>
                <div class="combobox-wrapper" id="phone-combobox-wrapper">
                    <div class="combobox-input-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <input type="tel" id="customer-phone-input" class="combobox-input" autocomplete="off" placeholder="Nomor WhatsApp pelanggan...">
                    </div>
                </div>
            </div>
        </article>

        {{-- Native Date & Time Pickers Row --}}
        <div class="date-time-grid">
            <div>
                <label class="form-label" for="input-date">DATE</label>
                <div class="input-container-icon">
                    <svg class="input-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <input type="date" id="input-date" class="native-picker-input">
                </div>
            </div>
            <div>
                <label class="form-label" for="input-time">TIME</label>
                <div class="input-container-icon">
                    <svg class="input-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <input type="time" id="input-time" class="native-picker-input">
                </div>
                <input type="hidden" name="waktu_kunjungan" value="{{ old('waktu_kunjungan') }}">
            </div>
        </div>

        {{-- Jenis Pengerjaan (sendiri / berdua) --}}
        <article class="card" data-field="pengerjaan-card">
            <div class="card-section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <h2 class="card-section-title">Jenis Pengerjaan</h2>
                <span class="unique-key-hint" style="margin-left: auto;">Pilih sendiri atau berdua</span>
            </div>
            <div class="segmented-pill-group" style="margin-top: 12px;">
                <label class="segmented-pill-label {{ old('jenis_pengerjaan', 'sendiri') === 'sendiri' ? 'active' : '' }}">
                    <input type="radio" name="jenis_pengerjaan" value="sendiri" {{ old('jenis_pengerjaan', 'sendiri') === 'sendiri' ? 'checked' : '' }} onchange="onJenisChange()">
                    <span>Sendiri</span>
                </label>
                <label class="segmented-pill-label {{ old('jenis_pengerjaan') === 'berdua' ? 'active' : '' }}">
                    <input type="radio" name="jenis_pengerjaan" value="berdua" {{ old('jenis_pengerjaan') === 'berdua' ? 'checked' : '' }} onchange="onJenisChange()">
                    <span>Berdua</span>
                </label>
            </div>
        </article>

        {{-- Isi dari Appointment --}}
        <article class="card" data-field="appointment-card">
            <div class="card-section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <h2 class="card-section-title">Isi dari Appointment</h2>
                <button type="button" id="appt_toggle" class="btn btn-secondary" style="margin-left: auto;">Sembunyikan</button>
            </div>
            <div id="appt_picker_body" class="appt-picker-body">
                <div class="appt-picker-toolbar">
                    <div>
                        <label class="form-label" for="appt_date">Tanggal</label>
                        <input type="date" id="appt_date" value="{{ date('Y-m-d') }}" class="native-picker-input">
                    </div>
                    <button type="button" id="appt_load_btn" class="btn btn-secondary" style="padding: 9px 16px;">Muat Jadwal</button>
                </div>
                <div id="appt_list" class="appt-list"></div>
                <p id="appt_empty" class="appt-empty-text hidden">Tidak ada appointment pada tanggal ini.</p>
            </div>
        </article>

        {{-- Service Items Container (Multi-Service Support) --}}
        <div id="service-items-container" class="service-items-container">
        </div>

        {{-- Full-Width ADD SERVICE Button --}}
        <button type="button" id="btn-add-service" class="btn btn-secondary btn-block" data-action="add-service">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="16"></line>
                <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span>ADD SERVICE</span>
        </button>

        {{-- Card: Special Notes (Optional) --}}
        <article class="card" data-field="notes-card">
            <label class="form-label" for="notes-textarea">SPECIAL NOTES (OPTIONAL)</label>
            <textarea id="notes-textarea" class="notes-textarea" rows="3" placeholder="Catatan umum untuk transaksi ini (diteruskan ke riwayat)..."></textarea>
        </article>

        {{-- Card: Service Summary --}}
        <article class="card" data-field="service-summary-card">
            <div class="card-section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <h2 class="card-section-title">Service Summary</h2>
                <span class="unique-key-hint" style="margin-left: auto;">Total dikalkulasi otomatis</span>
            </div>
            <div class="summary-rows">
                <div class="summary-row summary-row-subtle">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay" class="summary-val">Rp0</span>
                </div>
                <div class="summary-row summary-row-subtle">
                    <span>Total Diskon</span>
                    <span id="diskonDisplay" class="summary-val is-minus">-Rp0</span>
                </div>

                <div id="itemSummary" class="summary-detail-list"></div>

                <hr class="summary-divider">

                <div class="summary-total-row">
                    <span class="summary-total-label">Total Bayar</span>
                    <span id="totalDisplay" class="summary-total-val">Rp0</span>
                </div>
            </div>
        </article>

        {{-- Card: Payment Method --}}
        <article class="card" data-field="payment-method-card">
            <div class="card-section-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                    <line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
                <h2 class="card-section-title">Payment Method</h2>
            </div>

            <div class="payment-methods-list" id="payment-methods-group">
                <label class="payment-option-item active" for="pay-qris-bni">
                    <div class="payment-option-left">
                        <div class="payment-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                                <rect x="14" y="14" width="3" height="3"></rect>
                                <rect x="18" y="18" width="3" height="3"></rect>
                                <rect x="14" y="18" width="3" height="3"></rect>
                                <rect x="18" y="14" width="3" height="3"></rect>
                            </svg>
                        </div>
                        <div class="payment-texts">
                            <span class="payment-title">QRIS BNI</span>
                            <span class="payment-subtitle">Scan to pay instantly</span>
                        </div>
                    </div>
                    <input type="radio" id="pay-qris-bni" name="metode_pembayaran" value="qris_bni" @checked(old('metode_pembayaran', 'qris_bni') === 'qris_bni') required>
                    <div class="radio-indicator"><div class="radio-indicator-dot"></div></div>
                </label>

                <label class="payment-option-item" for="pay-qris-bri">
                    <div class="payment-option-left">
                        <div class="payment-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                                <rect x="14" y="14" width="3" height="3"></rect>
                                <rect x="18" y="18" width="3" height="3"></rect>
                                <rect x="14" y="18" width="3" height="3"></rect>
                                <rect x="18" y="14" width="3" height="3"></rect>
                            </svg>
                        </div>
                        <div class="payment-texts">
                            <span class="payment-title">QRIS BRI</span>
                            <span class="payment-subtitle">Scan to pay instantly</span>
                        </div>
                    </div>
                    <input type="radio" id="pay-qris-bri" name="metode_pembayaran" value="qris_bri" @checked(old('metode_pembayaran') === 'qris_bri')>
                    <div class="radio-indicator"><div class="radio-indicator-dot"></div></div>
                </label>

                <label class="payment-option-item" for="pay-debit">
                    <div class="payment-option-left">
                        <div class="payment-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </div>
                        <div class="payment-texts">
                            <span class="payment-title">Debit</span>
                            <span class="payment-subtitle">Kartu debit / EDC</span>
                        </div>
                    </div>
                    <input type="radio" id="pay-debit" name="metode_pembayaran" value="debit" @checked(old('metode_pembayaran') === 'debit')>
                    <div class="radio-indicator"><div class="radio-indicator-dot"></div></div>
                </label>

                <label class="payment-option-item" for="pay-kartu-kredit">
                    <div class="payment-option-left">
                        <div class="payment-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </div>
                        <div class="payment-texts">
                            <span class="payment-title">Kartu Kredit</span>
                            <span class="payment-subtitle">Credit card / Manual Entry</span>
                        </div>
                    </div>
                    <input type="radio" id="pay-kartu-kredit" name="metode_pembayaran" value="kartu_kredit" @checked(old('metode_pembayaran') === 'kartu_kredit')>
                    <div class="radio-indicator"><div class="radio-indicator-dot"></div></div>
                </label>

                <label class="payment-option-item" for="pay-cash">
                    <div class="payment-option-left">
                        <div class="payment-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                <circle cx="12" cy="12" r="2"></circle>
                                <path d="M6 12h.01M18 12h.01"></path>
                            </svg>
                        </div>
                        <div class="payment-texts">
                            <span class="payment-title">Cash</span>
                            <span class="payment-subtitle">Pay at front desk</span>
                        </div>
                    </div>
                    <input type="radio" id="pay-cash" name="metode_pembayaran" value="cash" @checked(old('metode_pembayaran') === 'cash')>
                    <div class="radio-indicator"><div class="radio-indicator-dot"></div></div>
                </label>

                <label class="payment-option-item" for="pay-transfer">
                    <div class="payment-option-left">
                        <div class="payment-icon-box">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                        <div class="payment-texts">
                            <span class="payment-title">Transfer</span>
                            <span class="payment-subtitle">Transfer bank / manual</span>
                        </div>
                    </div>
                    <input type="radio" id="pay-transfer" name="metode_pembayaran" value="transfer" @checked(old('metode_pembayaran') === 'transfer')>
                    <div class="radio-indicator"><div class="radio-indicator-dot"></div></div>
                </label>
            </div>
        </article>

        {{-- Bottom Dual Actions: BATAL & SIMPAN --}}
        <div class="form-actions-row">
            <a href="{{ route('transaksi.index') }}" id="btn-discard" class="btn btn-secondary" data-field="btn-discard">
                <span>Batal</span>
            </a>
            <button type="submit" id="btn-save-changes" class="btn btn-primary" data-field="btn-save-changes">
                <span>Simpan Transaksi</span>
            </button>
        </div>
    </form>

    {{-- Modal tambah pelanggan baru --}}
    <div id="pelangganModal" class="modal-backdrop-plain" aria-hidden="true">
        <div class="modal-card-plain">
            <div class="modal-header-row">
                <h3 class="modal-header-title">Tambah Pelanggan Baru</h3>
                <button type="button" id="cancelPelangganBtn" class="btn-close-modal">✕</button>
            </div>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label class="form-label" for="new_pelanggan_nama">Nama <span style="color: var(--status-danger);">*</span></label>
                    <input type="text" id="new_pelanggan_nama" class="text-input-field">
                </div>
                <div>
                    <x-phone-input name="new_pelanggan_wa" label="No. WhatsApp" placeholder="812xxxxxxx" />
                </div>
                <div>
                    <label class="form-label" for="new_pelanggan_kelamin">Jenis Kelamin</label>
                    <select id="new_pelanggan_kelamin" class="select-dropdown-field" style="width: 100%;">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="form-label" for="new_pelanggan_rambut">Jenis Rambut</label>
                    <select id="new_pelanggan_rambut" class="select-dropdown-field" style="width: 100%;">
                        <option value="">-- Pilih --</option>
                        <option value="Lurus">Lurus</option>
                        <option value="Ikal">Ikal</option>
                        <option value="Bergelombang">Bergelombang</option>
                        <option value="Keriting">Keriting</option>
                    </select>
                </div>
                <div id="new_pelanggan_error" class="modal-error-plain hidden"></div>
            </div>
            <div class="modal-actions-plain">
                <button type="button" id="cancelPelangganBtn2" class="btn btn-secondary">Batal</button>
                <button type="button" id="savePelangganBtn" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var karyawans = @json($karyawans);
        var allProduks = @json($produks);
        var itemIndex = 0;

        // --- Helpers ---
        function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); }
        function formatMl(n) {
            n = parseFloat(n) || 0;
            return (n % 1 === 0) ? String(n) : String(Math.round(n * 100) / 100);
        }
        function getJenis() { var r = document.querySelector('input[name="jenis_pengerjaan"]:checked'); return r ? r.value : 'sendiri'; }
        function isBerdua() { return getJenis() === 'berdua'; }

        function stafOptsHtml(selectedId, extraClass) {
            var html = '<option value="">-- Pilih Staf --</option>';
            karyawans.forEach(function(k) {
                var sel = (k.id == selectedId) ? ' selected' : '';
                html += '<option value="' + k.id + '" data-skema="' + k.skema_komisi + '"' + sel + '>' + k.nama + '</option>';
            });
            return html;
        }

        function komisiNoteHtml(komisiMin, komisiMax, hargaNow) {
            var min = parseFloat(komisiMin) || 0;
            var max = parseFloat(komisiMax) || 0;
            if (min > 0 || max > 0) {
                if (min === max) return 'Saran komisi: Rp' + fmt(min);
                return 'Saran komisi: Rp' + fmt(min) + ' - Rp' + fmt(max);
            }
            var saran = Math.round((hargaNow || 0) * 0.08 / 1000) * 1000;
            if (saran > 0) return 'Saran komisi (~8%): Rp' + fmt(saran);
            return 'Saran komisi: ~8% dari harga layanan (diisi manual)';
        }

        // =========================================================================
        // DATE & TIME -> hidden waktu_kunjungan
        // =========================================================================
        var dateInput = document.getElementById('input-date');
        var timeInput = document.getElementById('input-time');
        var waktuInput = document.querySelector('input[name="waktu_kunjungan"]');

        function syncWaktu() {
            if (dateInput && timeInput && waktuInput) {
                var d = dateInput.value, t = timeInput.value;
                waktuInput.value = (d && t) ? d + 'T' + t : '';
            }
        }
        function initWaktu() {
            var now = new Date();
            function two(n){ return String(n).padStart(2, '0'); }
            var defDate = two(now.getFullYear()) + '-' + two(now.getMonth() + 1) + '-' + two(now.getDate());
            var defTime = two(now.getHours()) + ':' + two(now.getMinutes());
            var raw = '';
            if (waktuInput) raw = waktuInput.value || '';
            if (raw) {
                var p = raw.split('T');
                if (dateInput) dateInput.value = p[0] || defDate;
                if (timeInput) timeInput.value = (p[1] && p[1].slice(0, 5)) || defTime;
            } else {
                if (dateInput) dateInput.value = defDate;
                if (timeInput) timeInput.value = defTime;
                syncWaktu();
            }
        }
        if (dateInput) dateInput.addEventListener('change', syncWaktu);
        if (timeInput) timeInput.addEventListener('change', syncWaktu);
        initWaktu();

        // =========================================================================
        // PELANGGAN COMBOBOX (interconnected name <-> phone)
        // =========================================================================
        var searchInput = document.getElementById('pelanggan_search');
        var resultsDiv = document.getElementById('pelanggan_results');
        var selectedDiv = document.getElementById('pelanggan_selected');
        var idInput = document.getElementById('id_pelanggan');
        var namaSpan = document.getElementById('pelanggan_nama');
        var phoneInput = document.getElementById('customer-phone-input');
        var debounceTimer;

        function renderResults(data) {
            if (!data || data.length === 0) {
                resultsDiv.innerHTML = '<div class="search-result-noresult">Pelanggan tidak ditemukan. Nomor telepon dapat diisi manual sebagai data klien baru.</div>';
                resultsDiv.classList.add('show');
                return;
            }
            resultsDiv.innerHTML = data.map(function(p) {
                var wa = p.no_wa || '';
                var safeName = String(p.nama).replace(/'/g, "\\'").replace(/"/g, '&quot;');
                var safeWa = String(wa).replace(/'/g, "\\'");
                return '<div class="search-result-option" data-name="' + safeName + '" data-wa="' + safeWa + '" data-id="' + p.id + '">' +
                    '<span>' + p.nama + '</span>' +
                    '<span class="search-result-muted">' + (wa || 'No. WA belum diisi') + '</span>' +
                '</div>';
            }).join('');
            resultsDiv.querySelectorAll('.search-result-option').forEach(function(el) {
                el.addEventListener('click', function() {
                    selectPelanggan(el.getAttribute('data-id'), el.getAttribute('data-name'), el.getAttribute('data-wa'));
                });
            });
            resultsDiv.classList.add('show');
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var q = this.value.trim();
            if (q.length < 2) { resultsDiv.classList.remove('show'); return; }
            debounceTimer = setTimeout(function() {
                fetch('{{ route("api.pelanggan.search") }}?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(renderResults)
                    .catch(function() { resultsDiv.innerHTML = '<div class="search-result-noresult">Gagal memuat data.</div>'; resultsDiv.classList.add('show'); });
            }, 300);
        });

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length >= 2) resultsDiv.classList.add('show');
        });

        window.selectPelanggan = function(id, nama, wa) {
            idInput.value = id;
            namaSpan.textContent = nama;
            selectedDiv.classList.remove('hidden');
            searchInput.classList.add('hidden');
            if (phoneInput) phoneInput.value = wa || '';
            resultsDiv.classList.remove('show');
            validateForm();
        };

        window.clearPelanggan = function() {
            idInput.value = '';
            selectedDiv.classList.add('hidden');
            searchInput.classList.remove('hidden');
            searchInput.value = '';
            if (phoneInput) phoneInput.value = '';
            validateForm();
        };

        // --- Modal Pelanggan Baru ---
        var modal = document.getElementById('pelangganModal');
        var modalFields = ['new_pelanggan_nama', 'new_pelanggan_wa', 'new_pelanggan_kelamin', 'new_pelanggan_rambut'];
        function openModal() {
            modal.classList.add('show');
            document.getElementById('new_pelanggan_nama').value = searchInput.value || '';
            document.getElementById('new_pelanggan_nama').focus();
        }
        function closeModal() {
            modal.classList.remove('show');
        }
        document.getElementById('addPelangganBtn').addEventListener('click', openModal);
        document.getElementById('cancelPelangganBtn').addEventListener('click', closeModal);
        document.getElementById('cancelPelangganBtn2').addEventListener('click', closeModal);
        document.getElementById('savePelangganBtn').addEventListener('click', function() {
            var nama = document.getElementById('new_pelanggan_nama').value.trim();
            if (!nama) {
                var err = document.getElementById('new_pelanggan_error');
                err.textContent = 'Nama wajib diisi.';
                err.classList.remove('hidden');
                return;
            }
            document.getElementById('new_pelanggan_error').classList.add('hidden');

            fetch('{{ route("api.pelanggan.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    nama: nama,
                    no_wa: document.getElementById('new_pelanggan_wa').value.trim(),
                    jenis_kelamin: document.getElementById('new_pelanggan_kelamin').value,
                    jenis_rambut: document.getElementById('new_pelanggan_rambut').value
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(p) {
                selectPelanggan(p.id, p.nama, p.no_wa || '');
                closeModal();
                document.getElementById('new_pelanggan_nama').value = '';
                var waNumberInput = document.getElementById('new_pelanggan_wa_number');
                if (waNumberInput) waNumberInput.value = '';
                document.getElementById('new_pelanggan_kelamin').value = '';
                document.getElementById('new_pelanggan_rambut').value = '';
            })
            .catch(function(e) {
                var err = document.getElementById('new_pelanggan_error');
                err.textContent = 'Gagal menyimpan pelanggan. Pastikan nomor WA valid (contoh: +6281234567890).';
                err.classList.remove('hidden');
            });
        });

        // =========================================================================
        // JENIS PENGERJAAN (sendiri / berdua)
        // =========================================================================
        window.onJenisChange = function() {
            var berdua = isBerdua();
            document.querySelectorAll('.staf2-section').forEach(function(el) {
                el.style.display = berdua ? '' : 'none';
                if (!berdua) {
                    var select = el.querySelector('.staf2-select');
                    if (select) select.value = '';
                    var komisiInput = el.querySelector('.komisi-input-2');
                    if (komisiInput) komisiInput.value = '';
                    var note2 = el.querySelector('.komisi-note-2');
                    if (note2) note2.textContent = '';
                }
            });
        };

        // =========================================================================
        // ITEM MANAGEMENT
        // =========================================================================
        document.getElementById('btn-add-service').addEventListener('click', function() {
            addItem();
        });

        function addItem() {
            var idx = itemIndex++;
            var html = buildItemHtml(idx);
            document.getElementById('service-items-container').insertAdjacentHTML('beforeend', html);
            initItemEvents(idx);
        }

        function buildItemHtml(idx) {
            return '<article class="item-row card service-item-card" data-idx="' + idx + '">' +
                '<div class="service-item-header">' +
                    '<span class="label-caps service-item-number">SERVICE ITEM #' + (idx + 1) + '</span>' +
                    '<div class="item-type-toggle">' +
                        '<label class="segmented-pill-label' + ' active' + '">' +
                            '<input type="radio" name="tipe-' + idx + '" value="layanan" checked onchange="switchTipe(' + idx + ', \'layanan\')">' +
                            '<span>Layanan</span>' +
                        '</label>' +
                        '<label class="segmented-pill-label">' +
                            '<input type="radio" name="tipe-' + idx + '" value="produk" onchange="switchTipe(' + idx + ', \'produk\')">' +
                            '<span>Produk</span>' +
                        '</label>' +
                    '</div>' +
                    '<button type="button" class="btn-delete-service" data-action="delete-service" onclick="removeItem(this)" title="Hapus Service Item Ini" style="display: none;">' +
                        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                            '<polyline points="3 6 5 6 21 6"></polyline>' +
                            '<path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>' +
                            '<line x1="10" y1="11" x2="10" y2="17"></line>' +
                            '<line x1="14" y1="11" x2="14" y2="17"></line>' +
                        '</svg>' +
                    '</button>' +
                '</div>' +
                '<div class="item-body" id="item-body-' + idx + '"></div>' +
                '<input type="hidden" name="items[' + idx + '][tipe_item]" class="tipe-input">' +
                '<input type="hidden" name="items[' + idx + '][id_layanan]" class="id-layanan-input">' +
                '<input type="hidden" name="items[' + idx + '][id_produk]" class="id-produk-input">' +
                '<input type="hidden" name="items[' + idx + '][varian_dipilih]" class="varian-input">' +
                '<input type="hidden" name="items[' + idx + '][harga_saat_transaksi]" class="harga-input">' +
                '<input type="hidden" name="items[' + idx + '][qty]" class="qty-input" value="1">' +
            '</article>';
        }

        function initItemEvents(idx) {
            syncDeleteVisibility();
            switchTipe(idx, 'layanan');
            validateForm();
        }

        function syncDeleteVisibility() {
            var count = document.querySelectorAll('.item-row').length;
            document.querySelectorAll('.btn-delete-service').forEach(function(b) {
                b.style.display = count > 1 ? 'inline-flex' : 'none';
            });
        }

        window.switchTipe = function(idx, tipe) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            row.querySelector('.tipe-input').value = tipe;
            row.querySelector('.id-layanan-input').value = '';
            row.querySelector('.id-produk-input').value = '';
            row.querySelector('.varian-input').value = '';
            row.querySelector('.harga-input').value = '';
            row.querySelector('.qty-input').value = '1';

            var typeLabels = row.querySelectorAll('.item-type-toggle .segmented-pill-label');
            typeLabels.forEach(function(l) { l.classList.remove('active'); });
            row.querySelectorAll('.item-type-toggle .segmented-pill-label input').forEach(function(r) {
                if (r.value === tipe) { r.closest('.segmented-pill-label').classList.add('active'); }
            });

            var body = document.getElementById('item-body-' + idx);
            if (tipe === 'layanan') {
                body.innerHTML = buildLayananBody(idx);
                initSearchLayanan(idx);
            } else {
                body.innerHTML = buildProdukBody(idx);
                initSearchProduk(idx);
            }
            onJenisChange();
        };

        function buildLayananBody(idx) {
            return '<div style="display: flex; flex-direction: column; gap: 16px;">' +

                // SERVICE
                '<div class="has-dropdown">' +
                    '<label class="form-label">SERVICE</label>' +
                    '<div class="input-container-icon">' +
                        '<svg class="input-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                            '<circle cx="6" cy="6" r="3"></circle><circle cx="6" cy="18" r="3"></circle>' +
                            '<line x1="20" y1="4" x2="8.12" y2="15.88"></line>' +
                            '<line x1="14.47" y1="14.48" x2="20" y2="20"></line>' +
                            '<line x1="8.12" y1="8.12" x2="12" y2="12"></line>' +
                        '</svg>' +
                        '<input type="text" class="search-layanan" placeholder="Cari layanan..." autocomplete="off" style="border: none; background: transparent; outline: none; flex: 1; font-size: 0.92rem; font-family: var(--font-sans); color: var(--text-primary);">' +
                    '</div>' +
                    '<div class="search-results-dropdown search-layanan-results"></div>' +
                '</div>' +

                // Layanan info (varian, staf, produk digunakan, ketebalan, komisi, harga)
                '<div class="layanan-info hidden" style="display: flex; flex-direction: column; gap: 16px;">' +

                    '<div class="form-grid-2">' +
                        '<div>' +
                            '<label class="form-label">VARIAN HARGA</label>' +
                            '<select name="items[' + idx + '][varian_dipilih]" class="varian-select select-dropdown-field" style="width: 100%;" onchange="onVarianChange(' + idx + ')"></select>' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label">HARGA SAAT TRANSAKSI (RP)</label>' +
                            '<div class="input-container-icon">' +
                                '<span style="font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">Rp</span>' +
                                '<input type="number" min="0" step="1000" class="harga-display" style="border: none; background: transparent; outline: none; flex: 1; padding: 8px 0;" onchange="syncHarga(' + idx + ')">' +
                            '</div>' +
                            '<p class="commission-suggestion harga-saran-note"></p>' +
                        '</div>' +
                    '</div>' +

                    // ASSIGNED STYLIST
                    '<div class="assigned-stylists-group">' +
                        '<label class="form-label">ASSIGNED STYLIST / KARYAWAN</label>' +
                        '<div class="input-container-icon">' +
                            '<svg class="input-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                                '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle>' +
                            '</svg>' +
                            '<select name="items[' + idx + '][id_staf_1]" class="staf1-select select-dropdown-field" style="width: 100%;" required onchange="onStafChange(' + idx + ', 1)">' + stafOptsHtml() + '</select>' +
                        '</div>' +
                        '<button type="button" class="btn-dashed-mini" data-action="add-worker">' +
                            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>' +
                            '<span>+ ADD KARYAWAN</span>' +
                        '</button>' +
                    '</div>' +

                    // Staf 2 (berdua)
                    '<div class="staf2-section" style="display: none;">' +
                        '<div class="form-grid-2">' +
                            '<div>' +
                                '<label class="form-label">STAF 2 (BANTUAN)</label>' +
                                '<select name="items[' + idx + '][id_staf_2]" class="staf2-select select-dropdown-field" style="width: 100%;" onchange="onStafChange(' + idx + ', 2)">' + stafOptsHtml() + '</select>' +
                            '</div>' +
                            '<div>' +
                                '<label class="form-label">KOMISI STAF 2 (RP)</label>' +
                                '<div class="input-container-icon">' +
                                    '<span style="font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">Rp</span>' +
                                    '<input type="text" inputmode="numeric" name="items[' + idx + '][komisi_nominal_2]" class="komisi-input-2" style="border: none; background: transparent; outline: none; flex: 1; padding: 8px 0;">' +
                                '</div>' +
                                '<p class="commission-suggestion komisi-note-2"></p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    // PRODUCT / BRAND (produk yang dipakai)
                    '<div class="products-group">' +
                        '<label class="form-label">PRODUCT / BRAND (PRODUK YANG DIPAKAI)</label>' +
                        '<div class="produk-usage-list" style="display: flex; flex-direction: column; gap: 12px;"></div>' +
                        '<button type="button" class="btn-dashed-mini add-produk-usage" data-action="add-product">' +
                            '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>' +
                            '<span>ADD PRODUCT</span>' +
                        '</button>' +
                    '</div>' +

                    // HAIR THICKNESS
                    '<div>' +
                        '<label class="form-label">HAIR THICKNESS (KETEBALAN RAMBUT)</label>' +
                        '<div class="segmented-pill-group is-static">' +
                            '<label class="segmented-pill-label">' +
                                '<input type="radio" name="items[' + idx + '][ketebalan_rambut]" value="Tipis">' +
                                '<span>Tipis</span>' +
                            '</label>' +
                            '<label class="segmented-pill-label active">' +
                                '<input type="radio" name="items[' + idx + '][ketebalan_rambut]" value="Sedang" checked>' +
                                '<span>Sedang</span>' +
                            '</label>' +
                            '<label class="segmented-pill-label">' +
                                '<input type="radio" name="items[' + idx + '][ketebalan_rambut]" value="Tebal">' +
                                '<span>Tebal</span>' +
                            '</label>' +
                        '</div>' +
                    '</div>' +

                    // WORKER COMMISSION & HINT
                    '<div class="form-grid-2">' +
                        '<div>' +
                            '<label class="form-label">WORKER COMMISSION (KOMISI STAF 1)</label>' +
                            '<div class="input-container-icon">' +
                                '<span style="font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">Rp</span>' +
                                '<input type="text" inputmode="numeric" name="items[' + idx + '][komisi_nominal_1]" class="komisi-input-1" style="border: none; background: transparent; outline: none; flex: 1; padding: 8px 0;">' +
                            '</div>' +
                            '<p class="commission-suggestion komisi-note-1"></p>' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label">DISKON (RP)</label>' +
                            '<div class="input-container-icon">' +
                                '<span style="font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">Rp</span>' +
                                '<input type="number" name="items[' + idx + '][diskon]" value="0" min="0" step="1000" placeholder="0" class="diskon-input" style="border: none; background: transparent; outline: none; flex: 1; padding: 8px 0;" oninput="recalcTotal()">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    // CATATAN
                    '<div>' +
                        '<label class="form-label">CATATAN ITEM (OPTIONAL)</label>' +
                        '<textarea name="items[' + idx + '][catatan]" rows="2" class="notes-textarea" placeholder="Detail hasil treatment / tambahan..."></textarea>' +
                    '</div>' +

                '</div>' +
            '</div>';
        }

        function buildProdukBody(idx) {
            return '<div style="display: flex; flex-direction: column; gap: 16px;">' +
                '<div class="has-dropdown">' +
                    '<label class="form-label">PRODUCT (DIJUAL PER PCS)</label>' +
                    '<div class="input-container-icon">' +
                        '<svg class="input-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
                            '<path d="m7.5 4.27 9 5.15"></path><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>' +
                            '<path d="m3.3 7 8.7 5 8.7-5"></path><path d="M12 22V12"></path>' +
                        '</svg>' +
                        '<input type="text" class="search-produk" placeholder="Cari produk..." autocomplete="off" style="border: none; background: transparent; outline: none; flex: 1; font-size: 0.92rem; font-family: var(--font-sans); color: var(--text-primary);">' +
                    '</div>' +
                    '<div class="search-results-dropdown search-produk-results"></div>' +
                '</div>' +

                '<div class="produk-info hidden" style="display: flex; flex-direction: column; gap: 16px;">' +
                    '<div class="form-grid-3">' +
                        '<div>' +
                            '<label class="form-label">HARGA / SATUAN</label>' +
                            '<p class="commission-suggestion produk-harga-display" style="font-weight: 800; color: var(--text-primary); font-size: 1rem;">-</p>' +
                            '<p class="commission-suggestion">Stok: <strong><span class="produk-stok-display">0</span><span class="produk-satuan-display"></span></strong></p>' +
                            '<p class="stok-warning stok-warning-inline hidden"></p>' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label qty-label">Jumlah dibeli</label>' +
                            '<input type="number" name="items[' + idx + '][qty]" value="1" min="1" step="1" class="qty-field stepper-input" style="width: 100%;" onchange="syncQty(' + idx + ')">' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label">DISKON (RP)</label>' +
                            '<div class="input-container-icon">' +
                                '<span style="font-weight: 700; color: var(--text-muted); font-size: 0.88rem;">Rp</span>' +
                                '<input type="number" name="items[' + idx + '][diskon]" value="0" min="0" step="1000" placeholder="0" class="diskon-input" style="border: none; background: transparent; outline: none; flex: 1; padding: 8px 0;" oninput="recalcTotal()">' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div style="background-color: var(--accent-light, var(--gold-50)); border-radius: var(--radius-md); padding: 12px 14px;">' +
                        '<label class="form-label">TOTAL HARGA</label>' +
                        '<p class="produk-subtotal-display" style="font-size: 1.15rem; font-weight: 900; color: var(--text-primary); margin: 0;">Rp0</p>' +
                    '</div>' +
                    '<div>' +
                        '<label class="form-label">CATATAN (OPTIONAL)</label>' +
                        '<textarea name="items[' + idx + '][catatan]" rows="2" class="notes-textarea" placeholder="Detail produk..."></textarea>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        // =========================================================================
        // LAYANAN SEARCH & SELECTION
        // =========================================================================
        function initSearchLayanan(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var input = row.querySelector('.search-layanan');
            var results = row.querySelector('.search-layanan-results');
            var timer;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.classList.remove('show'); return; }
                timer = setTimeout(function() {
                    fetch('{{ route("api.layanan.search") }}?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.length === 0) { results.innerHTML = '<div class="search-result-noresult">Tidak ditemukan</div>'; results.classList.add('show'); return; }
                            results.innerHTML = data.map(function(l) {
                                return '<div class="search-result-option" onclick="selectLayanan(' + idx + ',' + l.id + ')">' +
                                    '<span>' + l.nama_layanan + '</span>' +
                                    '<span class="search-result-muted">' + l.kategori + '</span>' +
                                '</div>';
                            }).join('');
                            results.classList.add('show');
                        });
                }, 300);
            });
        }

        window.selectLayanan = function(idx, layananId, idStaf) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            row.querySelector('.id-layanan-input').value = layananId;
            row.querySelector('.search-layanan-results').classList.remove('show');
            var selInput = row.querySelector('.search-layanan');
            if (selInput) selInput.blur();

            return fetch('{{ route("api.layanan.search") }}?q=' + encodeURIComponent(row.querySelector('.search-layanan').value))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var layanan = data.find(function(l) { return l.id === layananId; });
                    if (!layanan) return;

                    row.querySelector('.search-layanan').value = layanan.nama_layanan;
                    row.dataset.namaLayanan = layanan.nama_layanan;

                    var varianSelect = row.querySelector('.varian-select');
                    varianSelect.innerHTML = '';
                    layanan.harga_layanan.forEach(function(h) {
                        var opt = document.createElement('option');
                        opt.value = h.varian;
                        var label = h.varian + ' \u2014 Rp' + fmt(h.harga_dasar_min);
                        if (parseFloat(h.harga_dasar_max) !== parseFloat(h.harga_dasar_min)) {
                            label += ' - Rp' + fmt(h.harga_dasar_max);
                        }
                        opt.textContent = label;
                        opt.dataset.hargaMin = h.harga_dasar_min;
                        opt.dataset.hargaMax = h.harga_dasar_max;
                        opt.dataset.komisiMin = h.komisi_min || 0;
                        opt.dataset.komisiMax = h.komisi_max || 0;
                        opt.dataset.defaultProduk = JSON.stringify(h.default_produk || []);
                        varianSelect.appendChild(opt);
                    });

                    row.querySelector('.layanan-info').classList.remove('hidden');

                    row.dataset.produkList = JSON.stringify(layanan.produk || []);

                    var produkSection = row.querySelector('.produk-section-container');
                    if (!produkSection) produkSection = row.querySelector('.products-group');
                    delete row.dataset.prevVarian;
                    onVarianChange(idx);

                    if (idStaf) {
                        var stafSel = row.querySelector('.staf1-select');
                        if (stafSel) { stafSel.value = '' + idStaf; onStafChange(idx, 1); }
                    }
                });
        };

        window.addProdukUsageRow = function(idx, kategori, defaultMl) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var usageList = row.querySelector('.produk-usage-list');
            var pIdx = usageList.children.length;
            var isDefault = kategori !== undefined && kategori !== null && kategori !== '';

            var head = '<div style="display: flex; align-items: flex-start; gap: 10px;">' +
                    '<div class="has-dropdown" style="flex: 1; min-width: 0;">' +
                        '<label class="form-label" style="font-size: 0.7rem;">Merk / Produk' + (isDefault ? ' <span class="unique-key-hint">(' + kategori + ')</span>' : '') + '</label>' +
                        '<div class="input-container-icon" style="padding: 6px 12px;">' +
                            '<input type="text" placeholder="Cari merk/produk..." autocomplete="off" class="produk-search" style="border: none; background: transparent; outline: none; flex: 1; font-size: 0.86rem; font-family: var(--font-sans); color: var(--text-primary);">' +
'<div class="produk-selected" style="display: none; flex: 1; align-items: center; gap: 6px; min-width: 0;">' +
                                    '<span class="produk-selected-name" onclick="clearProdukUsage(this)" title="Klik untuk ganti merk/produk" style="min-width: 0; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.86rem; font-weight: 600; color: var(--text-primary); cursor: pointer;"></span>' +
                                '<button type="button" onclick="clearProdukUsage(this)" title="Ganti pilihan" style="border: none; background: none; cursor: pointer; color: var(--text-muted); font-size: 1rem;">&times;</button>' +
                            '</div>' +
                        '</div>' +
                        '<input type="hidden" name="items[' + idx + '][produk_penggunaan][' + pIdx + '][id_produk]" class="produk-id-input">' +
                        '<div class="search-results-dropdown produk-search-results"></div>' +
                        '<p class="ml-usage-hint commission-suggestion' + (isDefault ? ' hidden' : '') + '">Pilih produk terlebih dahulu untuk mengisi pemakaian.</p>' +
                    '</div>' +
                    '<button type="button" onclick="removeProdukUsage(this, ' + idx + ')" class="btn-remove-row" style="margin-top: 6px;">✕</button>' +
                '</div>';

            var html;
            if (isDefault) {
                var mlVal = (parseFloat(defaultMl) || 0);
                html = '<div class="produk-usage-item" data-pidx="' + pIdx + '" data-mode="default" data-kategori="' + String(kategori).replace(/"/g, '&quot;') + '" data-default-ml="' + mlVal + '" style="border: 1px solid var(--gold-200); background: var(--accent-soft); border-radius: var(--radius-md); padding: 10px 12px;">' +
                    head +
                    '<div class="usage-detail" style="margin-top: 10px; display: grid; grid-template-columns: 1fr; gap: 10px;">' +
                        '<div>' +
                            '<label class="form-label" style="font-size: 0.7rem;">Pemakaian (ml)</label>' +
                            '<div class="dosage-stepper">' +
                                '<button type="button" class="btn btn-secondary btn-icon btn-step-minus" onclick="stepMl(this, -10)">−</button>' +
                                '<input type="number" name="items[' + idx + '][produk_penggunaan][' + pIdx + '][pemakaian_ml]" value="' + mlVal + '" min="0" step="1" class="pemakaian-ml-input stepper-input" oninput="recalcHarga(' + idx + ')">' +
                                '<button type="button" class="btn btn-secondary btn-icon btn-step-plus" onclick="stepMl(this, 10)">+</button>' +
                                '<span class="unit-label">ml</span>' +
                            '</div>' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label" style="font-size: 0.7rem;">Biaya</label>' +
                            '<p class="produk-subtotal-display usage-cost-line" style="margin: 4px 0 0;">Rp0</p>' +
                            '<p class="produk-unit-harga commission-suggestion" style="margin: 2px 0 0;">Rp0 / 10ml</p>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            } else {
                html = '<div class="produk-usage-item" data-pidx="' + pIdx + '" style="border: 1px solid var(--border-color); background: var(--surface); border-radius: var(--radius-md); padding: 10px 12px;">' +
                    head +
                    '<div class="usage-detail hidden" style="margin-top: 10px; display: grid; grid-template-columns: 1fr; gap: 10px;">' +
                        '<div>' +
                            '<label class="form-label" style="font-size: 0.7rem;">Pemakaian (ml)</label>' +
                            '<div class="dosage-stepper">' +
                                '<button type="button" class="btn btn-secondary btn-icon btn-step-minus" onclick="stepMl(this, -10)">−</button>' +
                                '<input type="number" name="items[' + idx + '][produk_penggunaan][' + pIdx + '][pemakaian_ml]" value="0" min="0" step="1" class="pemakaian-ml-input stepper-input" oninput="recalcHarga(' + idx + ')">' +
                                '<button type="button" class="btn btn-secondary btn-icon btn-step-plus" onclick="stepMl(this, 10)">+</button>' +
                                '<span class="unit-label">ml</span>' +
                            '</div>' +
                        '</div>' +
                        '<div>' +
                            '<label class="form-label" style="font-size: 0.7rem;">Biaya</label>' +
                            '<p class="produk-subtotal-display usage-cost-line" style="margin: 4px 0 0;">Rp0</p>' +
                            '<p class="produk-unit-harga commission-suggestion" style="margin: 2px 0 0;">Rp0 / 10ml</p>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            }

            usageList.insertAdjacentHTML('beforeend', html);
            var item = usageList.lastElementChild;
            if (!isDefault) {
                item.querySelector('.usage-detail').classList.add('hidden');
            }
            initProdukUsageSearch(idx, pIdx);
            if (isDefault && kategori) {
                autoSelectDefaultProduk(idx, pIdx, kategori, mlVal);
            }
        };

        // Stepper helper for dynamic ml rows
        window.stepMl = function(btn, delta) {
            var stepper = btn.closest('.dosage-stepper');
            var input = stepper.querySelector('.pemakaian-ml-input');
            if (!input) return;
            var val = parseFloat(input.value || '0');
            val = Math.max(0, val + delta);
            input.value = val;
            var idx = btn.closest('.item-row').dataset.idx;
            recalcHarga(idx);
        };

        window.autoSelectDefaultProduk = function(idx, pIdx, kategori, mlVal) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var item = row.querySelector('.produk-usage-item[data-pidx="' + pIdx + '"]');
            if (!item) return;

            var list = [];
            try { list = JSON.parse(row.dataset.produkList || '[]'); } catch (e) {}
            var pool = list.length > 0 ? list : allProduks;

            var match = null;
            for (var i = 0; i < pool.length; i++) {
                if (String(pool[i].kategori_produk) === String(kategori)) {
                    match = pool[i];
                    break;
                }
            }
            if (!match && pool !== allProduks) {
                for (var j = 0; j < allProduks.length; j++) {
                    if (String(allProduks[j].kategori_produk) === String(kategori)) {
                        match = allProduks[j];
                        break;
                    }
                }
            }
            if (!match) {
                item.querySelector('.produk-subtotal-display').textContent = '';
                return;
            }

            var hargaPerUnit = parseFloat(match.harga_per_satuan) || 0;
            item.querySelector('.produk-id-input').value = match.id;
            var sel = item.querySelector('.produk-selected');
            if (sel) {
                var input = item.querySelector('.produk-search');
                input.value = match.nama_produk;
                input.style.display = 'none';
                sel.style.display = 'flex';
                sel.classList.remove('hidden');
                var selName = sel.querySelector('.produk-selected-name');
                selName.textContent = match.nama_produk;
                selName.setAttribute('title', match.nama_produk);
            }
            item.dataset.harga = hargaPerUnit;
            item.querySelector('.produk-unit-harga').textContent = 'Rp' + fmt(hargaPerUnit) + ' / 10ml';
            if (mlVal > 0 && hargaPerUnit > 0) {
                item.querySelector('.produk-subtotal-display').textContent = 'Rp' + fmt((mlVal / 10) * hargaPerUnit);
            } else {
                item.querySelector('.produk-subtotal-display').textContent = 'Rp0';
            }
            var detail = item.querySelector('.usage-detail');
            if (detail) { detail.classList.remove('hidden'); detail.style.display = 'grid'; }
            var hint = item.querySelector('.ml-usage-hint');
            if (hint) hint.classList.add('hidden');
            recalcHarga(idx);
        };

        function initProdukUsageSearch(idx, pIdx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var item = row.querySelector('.produk-usage-item[data-pidx="' + pIdx + '"]');
            var input = item.querySelector('.produk-search');
            var results = item.querySelector('.produk-search-results');
            var timer;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.classList.remove('show'); return; }
                timer = setTimeout(function() {
                    fetch('{{ route("api.produk.search") }}?mode=pakai&q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.length === 0) { results.innerHTML = '<div class="search-result-noresult">Tidak ditemukan</div>'; results.classList.add('show'); return; }
                            results.innerHTML = data.map(function(p) {
                                return '<div class="search-result-option" data-idx="' + idx + '" data-pidx="' + pIdx + '" data-id="' + p.id + '" data-nama="' + escAttr(p.nama_produk) + '" data-harga="' + p.harga_per_satuan + '" data-stok="' + p.stok + '">' +
                                    '<span>' + p.nama_produk + '</span>' +
                                    '<span class="search-result-muted">' + p.merek + '</span>' +
                                '</div>';
                            }).join('');
                            results.querySelectorAll('.search-result-option').forEach(function(optEl) {
                                optEl.addEventListener('click', function() {
                                    selectProdukUsage(optEl, optEl.getAttribute('data-idx'), optEl.getAttribute('data-pidx'),
                                        optEl.getAttribute('data-id'), optEl.getAttribute('data-nama') || '',
                                        optEl.getAttribute('data-harga'), optEl.getAttribute('data-stok'));
                                });
                            });
                            results.classList.add('show');
                        });
                }, 300);
            });
        }

        window.selectProdukUsage = function(el, idx, pIdx, produkId, nama, harga, stok) {
            var item = el.closest('.produk-usage-item');
            item.querySelector('.produk-id-input').value = produkId;
            var input = item.querySelector('.produk-search');
            input.value = nama;
            input.style.display = 'none';
            var sel = item.querySelector('.produk-selected');
            sel.style.display = 'flex';
            sel.classList.remove('hidden');
            var selName = sel.querySelector('.produk-selected-name');
            selName.textContent = nama;
            selName.setAttribute('title', nama);
            item.querySelector('.produk-search-results').classList.remove('show');
            item.dataset.harga = harga;
            item.querySelector('.produk-unit-harga').textContent = 'Rp' + fmt(harga) + ' / 10ml';
            var detail = item.querySelector('.usage-detail');
            detail.classList.remove('hidden');
            detail.style.display = 'grid';
            var hint = item.querySelector('.ml-usage-hint');
            if (hint) hint.classList.add('hidden');
            recalcHarga(idx);
        };

        window.clearProdukUsage = function(btn) {
            var item = btn.closest('.produk-usage-item');
            if (!item) return;
            item.querySelector('.produk-id-input').value = '';
            var sel = item.querySelector('.produk-selected');
            if (sel) sel.style.display = 'none';
            var input = item.querySelector('.produk-search');
            if (input) {
                input.value = '';
                input.style.display = '';
                input.focus();
            }
            item.querySelector('.produk-search-results').classList.remove('show');
            delete item.dataset.harga;
            item.querySelector('.produk-unit-harga').textContent = 'Rp0 / 10ml';
            item.querySelector('.produk-subtotal-display').textContent = 'Rp0';
            var detail = item.querySelector('.usage-detail');
            if (detail) { detail.style.display = 'grid'; }
            var hint = item.querySelector('.ml-usage-hint');
            if (hint) hint.classList.remove('hidden');
            if (item.dataset.mode === 'default' && hint) hint.classList.add('hidden');
            var idx = item.closest('.item-row').dataset.idx;
            recalcHarga(idx);
        };

        window.removeProdukUsage = function(btn, idx) {
            btn.closest('.produk-usage-item').remove();
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var usageList = row.querySelector('.produk-usage-list');
            var items = usageList.querySelectorAll('.produk-usage-item');
            items.forEach(function(item, newIdx) {
                item.dataset.pidx = newIdx;
                item.querySelector('.produk-id-input').name = 'items[' + idx + '][produk_penggunaan][' + newIdx + '][id_produk]';
                item.querySelector('.pemakaian-ml-input').name = 'items[' + idx + '][produk_penggunaan][' + newIdx + '][pemakaian_ml]';
            });
            recalcHarga(idx);
        };

        window.onVarianChange = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var select = row.querySelector('.varian-select');
            var opt = select.selectedOptions[0];

            var newVarian = opt ? opt.value : '';
            var prevVarian = row.dataset.prevVarian || '';

            if (!opt && !prevVarian) {
                var usageList = row.querySelector('.produk-usage-list');
                usageList.innerHTML = '';
                addProdukUsageRow(idx);
                recalcHarga(idx);
                updateKomisiNotes(idx);
                return;
            }

            row.dataset.prevVarian = newVarian;
            row.querySelector('.varian-input').value = newVarian;

            var usageList = row.querySelector('.produk-usage-list');
            usageList.innerHTML = '';

            if (opt) {
                var defaults = [];
                try { defaults = JSON.parse(opt.dataset.defaultProduk || '[]'); } catch (e) {}
                if (defaults.length > 0) {
                    defaults.forEach(function(d) {
                        addProdukUsageRow(idx, d.kategori_produk, parseFloat(d.default_ml) || 0);
                    });
                } else {
                    addProdukUsageRow(idx);
                }
            } else {
                addProdukUsageRow(idx);
            }

            delete row.dataset.manualHarga;
            var hargaMin = parseFloat(opt ? opt.dataset.hargaMin : 0) || 0;
            row.querySelector('.harga-display').value = hargaMin;
            row.querySelector('.harga-input').value = hargaMin;

            recalcHarga(idx);
            updateKomisiNotes(idx);
        };

        function updateKomisiNotes(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var varianOpt = row.querySelector('.varian-select')?.selectedOptions[0];
            if (!varianOpt) return;
            var komisiMin = parseFloat(varianOpt.dataset.komisiMin) || 0;
            var komisiMax = parseFloat(varianOpt.dataset.komisiMax) || 0;
            var harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;

            var note1 = row.querySelector('.komisi-note-1');
            if (note1) note1.textContent = komisiNoteHtml(komisiMin, komisiMax, harga);

            var note2 = row.querySelector('.komisi-note-2');
            if (note2) note2.textContent = komisiNoteHtml(komisiMin, komisiMax, harga);
        }

        window.recalcHarga = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var opt = row.querySelector('.varian-select')?.selectedOptions[0];
            if (!opt) return;

            var hargaMin = parseFloat(opt.dataset.hargaMin) || 0;
            var hargaMax = parseFloat(opt.dataset.hargaMax) || hargaMin;

            var usageList = row.querySelector('.produk-usage-list');
            var usageItems = usageList ? usageList.querySelectorAll('.produk-usage-item') : [];
            var extraCost = 0;
            var productCosts = [];

            usageItems.forEach(function(item) {
                var idInput = item.querySelector('.produk-id-input');
                var mlInput = item.querySelector('.pemakaian-ml-input');
                var subtotalEl = item.querySelector('.produk-subtotal-display');
                var unitEl = item.querySelector('.produk-unit-harga');
                var hargaPerUnit = item.dataset.harga ? (parseFloat(item.dataset.harga) || 0) : 0;
                var ml = mlInput ? (parseFloat(mlInput.value) || 0) : 0;
                var isDefault = item.dataset.mode === 'default';
                var defaultMl = isDefault ? (parseFloat(item.dataset.defaultMl) || 0) : 0;

                if (idInput && mlInput && idInput.value) {
                    if (unitEl) unitEl.textContent = 'Rp' + fmt(hargaPerUnit) + ' / 10ml';
                    var cost = 0;
                    var nameEl = item.querySelector('.produk-search');
                    var prodName = nameEl && nameEl.value ? nameEl.value : (item.dataset.kategori || 'Produk');

                    if (isDefault) {
                        var extraMl = Math.max(0, ml - defaultMl);
                        if (extraMl > 0 && hargaPerUnit > 0) {
                            cost = (extraMl / 10) * hargaPerUnit;
                            productCosts.push(prodName + ' + Rp' + fmt(cost) + ' (' + formatMl(extraMl) + 'ml tambahan)');
                        }
                    } else {
                        if (ml > 0 && hargaPerUnit > 0) {
                            cost = (ml / 10) * hargaPerUnit;
                            productCosts.push(prodName + ' ' + formatMl(ml) + 'ml Rp' + fmt(cost));
                        }
                    }

                    extraCost += cost;
                    if (subtotalEl) subtotalEl.textContent = cost > 0 ? 'Rp' + fmt(cost) : 'Rp0';
                } else {
                    if (unitEl) unitEl.textContent = 'Rp0 / 10ml';
                    if (subtotalEl) subtotalEl.textContent = 'Rp0';
                }
            });

            if (!row.dataset.manualHarga) {
                var finalHarga = hargaMin + extraCost;
                row.querySelector('.harga-display').value = Math.round(finalHarga);
                row.querySelector('.harga-input').value = Math.round(finalHarga);
            }

            var note = row.querySelector('.harga-saran-note');
            var parts = ['Dasar Rp' + fmt(hargaMin)];
            if (productCosts.length > 0) {
                parts = parts.concat(productCosts);
            }
            note.textContent = parts.join(' + ');

            updateKomisiNotes(idx);
            recalcTotal();
        };

        window.syncHarga = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var val = row.querySelector('.harga-display').value || 0;
            row.querySelector('.harga-input').value = val;
            row.dataset.manualHarga = 'true';
            updateKomisiNotes(idx);
            recalcTotal();
        };

        // =========================================================================
        // PRODUK SEARCH & SELECTION (standalone retail)
        // =========================================================================
        function escAttr(s) {
            return String(s == null ? '' : s)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function initSearchProduk(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var input = row.querySelector('.search-produk');
            var results = row.querySelector('.search-produk-results');
            var timer;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.classList.remove('show'); return; }
                timer = setTimeout(function() {
                    fetch('{{ route("api.produk.search") }}?mode=dijual&q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.length === 0) { results.innerHTML = '<div class="search-result-noresult">Tidak ditemukan</div>'; results.classList.add('show'); return; }
                            results.innerHTML = data.map(function(p) {
                                var stokClass = p.stok <= 0 ? ' color: var(--status-danger);' : '';
                                return '<div class="search-result-option" data-idx="' + idx + '" data-id="' + p.id + '" data-nama="' + escAttr(p.nama_produk) + '" data-harga="' + p.harga_per_satuan + '" data-stok="' + p.stok + '" data-satuan="' + escAttr(p.satuan) + '">' +
                                    '<span>' + p.nama_produk + '</span>' +
                                    '<span class="search-result-muted" style="' + stokClass + '">(' + p.merek + ') Stok: ' + p.stok + '</span>' +
                                '</div>';
                            }).join('');
                            results.querySelectorAll('.search-result-option').forEach(function(optEl) {
                                optEl.addEventListener('click', function() {
                                    selectProduk(optEl.getAttribute('data-idx'), optEl.getAttribute('data-id'),
                                        optEl.getAttribute('data-nama') || '', optEl.getAttribute('data-harga'),
                                        optEl.getAttribute('data-stok'), optEl.getAttribute('data-satuan') || '');
                                });
                            });
                            results.classList.add('show');
                        });
                }, 300);
            });
        }

        window.selectProduk = function(idx, produkId, nama, harga, stok, satuan) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            row.querySelector('.id-produk-input').value = produkId;
            row.querySelector('.search-produk').value = nama;
            row.querySelector('.search-produk-results').classList.remove('show');
            row.querySelector('.produk-info').classList.remove('hidden');
            row.querySelector('.produk-info').style.display = 'flex';
            row.querySelector('.produk-harga-display').textContent = 'Rp' + fmt(harga);
            row.querySelector('.produk-stok-display').textContent = stok;
            row.querySelector('.produk-satuan-display').textContent = '/' + satuan;
            row.querySelector('.harga-input').value = harga;
            row.querySelector('.qty-field').value = 1;
            row.querySelector('.qty-input').value = 1;

            var produkSubtotal = row.querySelector('.produk-subtotal-display');
            if (produkSubtotal) produkSubtotal.textContent = 'Rp' + fmt(harga);

            var qtyLabel = row.querySelector('.qty-label');
            if (qtyLabel) {
                if (satuan === 'pcs') {
                    qtyLabel.textContent = 'Jumlah dibeli (pcs)';
                } else {
                    qtyLabel.textContent = 'Jumlah (' + satuan + ')';
                }
            }

            var stokWarning = row.querySelector('.stok-warning');
            if (stok <= 0) {
                stokWarning.textContent = 'Stok habis!';
                stokWarning.classList.remove('hidden');
            } else if (stok <= 2) {
                stokWarning.textContent = 'Stok hampir habis';
                stokWarning.classList.remove('hidden');
            } else {
                stokWarning.classList.add('hidden');
            }

            syncQty(idx);
            recalcTotal();
        };

        window.syncQty = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var qty = parseInt(row.querySelector('.qty-field').value) || 1;
            if (qty < 1) qty = 1;
            row.querySelector('.qty-field').value = qty;
            row.querySelector('.qty-input').value = qty;

            var harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;
            var produkSubtotal = row.querySelector('.produk-subtotal-display');
            if (produkSubtotal) {
                var total = harga * qty;
                produkSubtotal.textContent = 'Rp' + fmt(total);
            }

            var stokDisplay = row.querySelector('.produk-stok-display');
            var stokWarning = row.querySelector('.stok-warning');
            if (stokDisplay && stokWarning) {
                var stok = parseFloat(stokDisplay.textContent) || 0;
                if (parseFloat(qty) > stok) {
                    stokWarning.textContent = 'Qty melebihi stok!';
                    stokWarning.classList.remove('hidden');
                } else if (stok <= 2 && parseFloat(qty) > 0) {
                    stokWarning.textContent = 'Stok hampir habis';
                    stokWarning.classList.remove('hidden');
                } else {
                    stokWarning.classList.add('hidden');
                }
            }

            recalcTotal();
        };

        window.onStafChange = function(idx, stafNum) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var selClass = stafNum === 1 ? '.staf1-select' : '.staf2-select';
            var noteClass = stafNum === 1 ? '.komisi-note-1' : '.komisi-note-2';
            var inputClass = stafNum === 1 ? '.komisi-input-1' : '.komisi-input-2';

            var stafSelect = row.querySelector(selClass);
            var stafId = stafSelect.value;
            var staf = karyawans.find(function(k) { return k.id == stafId; });

            if (staf && staf.skema_komisi === 'persen_omset_harian') {
                var input = row.querySelector(inputClass);
                if (input) { input.value = ''; input.disabled = true; input.placeholder = 'Otomatis'; }
                var note = row.querySelector(noteClass);
                if (note) { note.textContent = 'Komisi staf ini dihitung otomatis dari omset harian.'; note.classList.add('is-auto'); }
            } else {
                var input = row.querySelector(inputClass);
                if (input) { input.disabled = false; input.placeholder = ''; }
                var note = row.querySelector(noteClass);
                if (note) note.classList.remove('is-auto');
                updateKomisiNotes(idx);
            }
        };

        window.removeItem = function(btn) {
            btn.closest('.item-row').remove();
            var items = document.querySelectorAll('.item-row');
            items.forEach(function(row, i) {
                var num = row.querySelector('.service-item-number');
                if (num) num.textContent = 'SERVICE ITEM #' + (i + 1);
            });
            syncDeleteVisibility();
            recalcTotal();
            validateForm();
        };

        // ADD KARYAWAN button -> reveal staf2 (berdua)
        document.addEventListener('click', function(e) {
            var addWorker = e.target.closest('[data-action="add-worker"]');
            if (addWorker) {
                e.preventDefault();
                var row = addWorker.closest('.item-row');
                var berduaRadio = document.querySelector('input[name="jenis_pengerjaan"][value="berdua"]');
                if (berduaRadio) { berduaRadio.checked = true; }
                var staf2 = row ? row.querySelector('.staf2-section') : null;
                if (staf2) staf2.style.display = '';
            }
        });

        // ADD PRODUCT button (produk usage)
        document.addEventListener('click', function(e) {
            var addProduct = e.target.closest('.add-produk-usage');
            if (addProduct) {
                e.preventDefault();
                var row = addProduct.closest('.item-row');
                if (!row) return;
                var idx = row.dataset.idx;
                addProdukUsageRow(idx);
            }
        });

        function recalcTotal() {
            var total = 0;
            var subtotalTotal = 0;
            var diskonTotal = 0;
            var summaryHtml = '';
            document.querySelectorAll('.item-row').forEach(function(row, i) {
                var harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;
                var qty = parseFloat(row.querySelector('.qty-input')?.value) || 1;
                var diskon = row.querySelector('.diskon-input') ? (parseFloat(row.querySelector('.diskon-input').value) || 0) : 0;
                var subtotal = harga * qty;
                var lineTotal = Math.max(0, subtotal - diskon);
                subtotalTotal += subtotal;
                diskonTotal += diskon;
                total += lineTotal;

                var nama = row.dataset.namaLayanan || (row.querySelector('.produk-harga-display') ? row.querySelector('.search-produk')?.value : '') || 'Item';
                var detailNote = row.querySelector('.harga-saran-note') ? row.querySelector('.harga-saran-note').textContent : '';

                summaryHtml += '<div class="summary-item-line"><span class="summary-line-name">#' + (i + 1) + ' ' + nama + '</span><span>Rp' + fmt(subtotal) + '</span></div>';
                if (qty > 1) {
                    summaryHtml += '<span class="summary-line-note">Rp' + fmt(harga) + ' &times; ' + qty + '</span>';
                }
                if (detailNote) {
                    summaryHtml += '<span class="summary-line-note">' + detailNote + '</span>';
                }
                if (diskon > 0) {
                    summaryHtml += '<span class="summary-line-note is-discount">- diskon Rp' + fmt(diskon) + '</span>';
                }
            });
            document.getElementById('itemSummary').innerHTML = summaryHtml;
            document.getElementById('subtotalDisplay').textContent = 'Rp' + fmt(subtotalTotal);
            document.getElementById('diskonDisplay').textContent = diskonTotal > 0 ? '-Rp' + fmt(diskonTotal) : '-Rp0';
            document.getElementById('totalDisplay').textContent = 'Rp' + fmt(total);
            validateForm();
        }

        function validateForm() {
            var hasPelanggan = document.getElementById('id_pelanggan').value !== '';
            var hasItems = document.querySelectorAll('.item-row').length > 0;
            var submitBtn = document.getElementById('btn-save-changes');
            if (submitBtn) submitBtn.disabled = !(hasPelanggan && hasItems);
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            ['.search-layanan-results', '.search-produk-results', '.produk-search-results', '#pelanggan_results'].forEach(function(sel) {
                if (!e.target.closest(sel) && !e.target.closest('.search-layanan') && !e.target.closest('.search-produk') && !e.target.closest('.produk-search') && !e.target.closest('#pelanggan_search')) {
                    document.querySelectorAll(sel).forEach(function(el) { el.classList.remove('show'); });
                }
            });
        });

        // Payment method active state
        var paymentOptions = document.querySelectorAll('.payment-option-item');
        paymentOptions.forEach(function(option) {
            option.addEventListener('click', function() {
                paymentOptions.forEach(function(opt) { opt.classList.remove('active'); });
                option.classList.add('active');
            });
        });

        // Segmented pill radio: sinkronkan class active dengan radio yang dipilih
        // (jenis_pengerjaan sendiri/berdua, ketebalan rambut, dll) sehingga hanya
        // satu pill yang menyala dan mengikuti pilihan terbaru.
        document.addEventListener('change', function(e) {
            var target = e.target;
            if (!target || target.type !== 'radio') return;
            var group = target.closest('.segmented-pill-group');
            if (!group) return;
            group.querySelectorAll('.segmented-pill-label').forEach(function(l) {
                l.classList.remove('active');
            });
            var label = target.closest('.segmented-pill-label');
            if (label) label.classList.add('active');
        });

        // Pilih produk dari dropdown hasil search (retail & pemakaian) via delegasi
        // event, sehingga seluruh baris hasil — termasuk teks merk — selalu bisa
        // diklik apa pun isi nama produknya.
        document.addEventListener('click', function(e) {
            var opt = e.target.closest ? e.target.closest('.search-produk-results .search-result-option') : null;
            if (opt) {
                selectProduk(opt.getAttribute('data-idx'), opt.getAttribute('data-id'),
                    opt.getAttribute('data-nama') || '', opt.getAttribute('data-harga'),
                    opt.getAttribute('data-stok'), opt.getAttribute('data-satuan') || '');
                return;
            }
            var optUsage = e.target.closest ? e.target.closest('.produk-search-results .search-result-option') : null;
            if (optUsage) {
                selectProdukUsage(optUsage, optUsage.getAttribute('data-idx'), optUsage.getAttribute('data-pidx'),
                    optUsage.getAttribute('data-id'), optUsage.getAttribute('data-nama') || '',
                    optUsage.getAttribute('data-harga'), optUsage.getAttribute('data-stok'));
            }
        });

        // =========================================================================
        // APPOINTMENT PICKER
        // =========================================================================
        var apptCache = {};
        var apptApi = '{{ route("appointment.hari", "TGL") }}';

        function loadAppointments() {
            var d = document.getElementById('appt_date').value;
            if (!d) return;
            fetch(apptApi.replace('TGL', d))
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    apptCache = {};
                    var list = document.getElementById('appt_list');
                    var empty = document.getElementById('appt_empty');
                    list.innerHTML = '';
                    empty.classList.add('hidden');
                    (res.appointments || []).forEach(function(a) {
                        apptCache[a.id] = a;
                        var isBatal = a.status === 'batal';
                        var el = document.createElement('div');
                        el.className = 'appt-item-row';
                        el.innerHTML =
                            '<span class="appt-item-time">' + a.jam_mulai + '</span>' +
                            '<span class="appt-item-name">' + (a.pelanggan || 'Tanpa pelanggan') + '</span>' +
                            '<span class="appt-item-service">' + (a.layanan || '') + '</span>' +
                            '<button type="button" onclick="applyAppointment(' + a.id + ')"' + (isBatal ? ' disabled' : '') + ' class="appt-item-apply">Isikan</button>';
                        list.appendChild(el);
                    });
                    if ((res.appointments || []).length === 0) empty.classList.remove('hidden');
                })
                .catch(function() { alert('Gagal memuat jadwal appointment.'); });
        }

        window.applyAppointment = function(id) {
            var a = apptCache[id];
            if (!a) return;
            if (!a.id_pelanggan) { alert('Appointment ini belum punya pelanggan.'); return; }
            if (!a.id_karyawan) { alert('Appointment ini belum punya stylist.'); return; }
            if (a.status === 'batal') { alert('Appointment berstatus batal.'); return; }
            if (!a.id_layanan || a.id_layanan.length === 0) { alert('Appointment ini belum punya layanan.'); return; }

            document.querySelectorAll('.item-row').forEach(function(row) { row.remove(); });
            itemIndex = 0;
            selectPelanggan(a.id_pelanggan, a.pelanggan, a.no_wa || '');

            if (waktuInput) {
                var dt = a.tanggal + 'T' + a.jam_mulai;
                waktuInput.value = dt;
                dateInput.value = a.tanggal;
                timeInput.value = a.jam_mulai.slice(0, 5);
            }

            a.id_layanan.forEach(function(lid, i) {
                addItem();
                var idx = itemIndex - 1;
                var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
                var nama = (a.layanans && a.layanans[i]) ? a.layanans[i] : '';
                row.querySelector('.search-layanan').value = nama;
                selectLayanan(idx, lid, a.id_karyawan);
            });

            recalcTotal();
            validateForm();
        };

        document.getElementById('appt_load_btn').addEventListener('click', loadAppointments);
        document.getElementById('appt_date').addEventListener('change', loadAppointments);
        loadAppointments();
        document.getElementById('appt_toggle').addEventListener('click', function() {
            var body = document.getElementById('appt_picker_body');
            var hidden = body.classList.toggle('hidden');
            this.textContent = hidden ? 'Tampilkan' : 'Sembunyikan';
        });

        // Pre-add one item row
        addItem();
        onJenisChange();
        validateForm();
    })();
    </script>
@endsection