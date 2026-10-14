@extends('layouts.app')

@section('title', 'Appointment')

@section('nav-active', 'appointment')

@section('main-class', 'calendar-main-layout')

@section('container_width', 'app-content-wide calendar-content-stack')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/appointment.css') }}">
@endpush

@php
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $namaBulanSingkat = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $judulBulan = $namaBulan[$bulanAktif->month - 1].' '.$bulanAktif->year;
    $jumlahMinggu = (int) ceil($hariGrid->count() / 7);

    $colorMap = [
        'amber'   => ['color' => 'var(--seg-amber)',   'bg' => 'var(--seg-amber-bg)'],
        'emerald' => ['color' => 'var(--seg-emerald)', 'bg' => 'var(--seg-emerald-bg)'],
        'blue'    => ['color' => 'var(--seg-blue)',    'bg' => 'var(--seg-blue-bg)'],
        'violet'  => ['color' => 'var(--seg-violet)',  'bg' => 'var(--seg-violet-bg)'],
        'pink'    => ['color' => 'var(--seg-pink)',    'bg' => 'var(--seg-pink-bg)'],
        'orange'  => ['color' => 'var(--seg-orange)',  'bg' => 'var(--seg-orange-bg)'],
        'teal'    => ['color' => 'var(--seg-teal)',    'bg' => 'var(--seg-teal-bg)'],
        'rose'    => ['color' => 'var(--seg-rose)',    'bg' => 'var(--seg-rose-bg)'],
    ];

    $statusMap = [
        'terkonfirmasi' => ['pill' => 'confirmed', 'label' => 'Terkonfirmasi'],
        'menunggu'      => ['pill' => 'pending',   'label' => 'Menunggu'],
        'selesai'       => ['pill' => 'done',      'label' => 'Selesai'],
    ];

    $statusColorMap = [
        'terkonfirmasi' => ['color' => '#edb92f', 'bg' => 'rgba(237, 185, 47, 0.18)'],
        'menunggu'      => ['color' => '#f0c445', 'bg' => 'rgba(240, 196, 69, 0.18)'],
        'selesai'       => ['color' => '#1e6b3a', 'bg' => 'rgba(30, 107, 58, 0.18)'],
    ];
@endphp

@section('content')
        <!-- Google Calendar Style Top Toolbar -->
        <header class="calendar-top-toolbar">
            <div class="toolbar-left-group">
                <a href="{{ route('appointment.index') }}" class="btn-cal-today" style="text-decoration: none;">Hari Ini</a>
                <div class="cal-nav-arrows">
                    <a href="{{ route('appointment.index', ['bulan' => $prevBulan]) }}" class="cal-arrow-btn" aria-label="Sebelumnya">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </a>
                    <a href="{{ route('appointment.index', ['bulan' => $nextBulan]) }}" class="cal-arrow-btn" aria-label="Selanjutnya">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </div>
                <h1 class="cal-period-heading">{{ $judulBulan }}</h1>
            </div>

            <div class="toolbar-right-group">
                <div class="cal-search-box">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="cal-quick-search" placeholder="Cari appointment..." aria-label="Cari jadwal appointment">
                </div>
                <button type="button" class="btn-cari btn-cari--white" id="btn-cal-search">Cari</button>

                <a href="{{ route('appointment.create') }}" class="btn btn-primary" id="btn-add-appointment">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>Appointment</span>
                </a>
            </div>
        </header>

        <!-- Main Calendar Split Grid -->
        <div class="calendar-workspace-grid">

            <!-- Tombol Filter (hanya tampil di layar sempit, panel tertutup bawaan) -->
            <button type="button" class="cal-filter-toggle" id="btn-cal-filter" aria-expanded="false" aria-controls="cal-side-panel">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <span>Filter</span>
            </button>

            <!-- SISI KIRI: Panel Filter Klien -->
            <aside class="calendar-side-panel" id="cal-side-panel">

                <div class="filter-section-card">
                    <div class="filter-section-header">
                        <span class="filter-section-title">Klien &amp; Jadwal</span>
                        <button type="button" class="btn-select-all js-hapus-semua-klien">Hapus Semua</button>
                    </div>
                    <div class="client-checkbox-list">
                        @forelse ($klienList as $klien)
                            @php $kColor = $colorMap[$klien['warna']] ?? $colorMap['orange']; @endphp
                            <label class="client-check-row">
                                <input type="checkbox" class="js-filter-klien" data-pelanggan-id="{{ $klien['id'] }}" checked>
                                <span class="client-color-dot" style="background-color: {{ $kColor['color'] }};"></span>
                                <span style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $klien['nama'] }}</span>
                                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $klien['jumlah'] }}</span>
                            </label>
                        @empty
                            <p style="font-size: 0.78rem; color: var(--text-muted);">Belum ada klien terjadwal bulan ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="filter-section-card">
                    <div class="filter-section-header">
                        <span class="filter-section-title">Stylist / Karyawan</span>
                        <button type="button" class="btn-select-all js-hapus-semua-staf">Hapus Semua</button>
                    </div>
                    <div class="client-checkbox-list">
                        @forelse ($stylistList as $staf)
                            @php $sColor = $colorMap[$staf['warna']] ?? $colorMap['orange']; @endphp
                            <label class="client-check-row">
                                <input type="checkbox" class="js-filter-staf" data-karyawan-id="{{ $staf['id'] }}" checked>
                                <span class="client-color-dot" style="background-color: {{ $sColor['color'] }};"></span>
                                <span style="flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $staf['nama'] }}</span>
                                <span style="font-size: 0.72rem; color: var(--text-muted);">{{ $staf['jumlah'] }}</span>
                            </label>
                        @empty
                            <p style="font-size: 0.78rem; color: var(--text-muted);">Belum ada stylist terjadwal bulan ini.</p>
                        @endforelse
                    </div>
                </div>

                <div class="filter-section-card status-legend-card">
                    <span class="filter-section-title">Status Janji Temu</span>
                    <div class="status-legend-list">
                        <label class="client-check-row">
                            <input type="checkbox" class="js-filter-status" data-status="terkonfirmasi" checked>
                            <span class="status-dot dot-confirmed"></span> Terkonfirmasi
                            <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted);">{{ $statusCounts->get('terkonfirmasi', 0) }}</span>
                        </label>
                        <label class="client-check-row">
                            <input type="checkbox" class="js-filter-status" data-status="menunggu" checked>
                            <span class="status-dot dot-pending"></span> Menunggu
                            <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted);">{{ $statusCounts->get('menunggu', 0) }}</span>
                        </label>
                        <label class="client-check-row">
                            <input type="checkbox" class="js-filter-status" data-status="selesai" checked>
                            <span class="status-dot dot-done"></span> Selesai
                            <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted);">{{ $statusCounts->get('selesai', 0) }}</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- SISI KANAN: Calendar Month View -->
            <div class="calendar-viewport-card">
                <div class="calendar-view-panel active" data-view="month" id="view-month">
                    <div class="cal-month" role="grid" aria-label="Kalender {{ $judulBulan }}" style="--weeks: {{ $jumlahMinggu }}">
                        <div class="cal-month__head" role="row">
                            <div role="columnheader">SEN</div>
                            <div role="columnheader">SEL</div>
                            <div role="columnheader">RAB</div>
                            <div role="columnheader">KAM</div>
                            <div role="columnheader">JUM</div>
                            <div role="columnheader">SAB</div>
                            <div role="columnheader">MIN</div>
                        </div>
                        <div class="cal-month__body">
                            @foreach ($hariGrid as $hari)
                                @php
                                    $tanggalSel = $hari['tanggal']->format('Y-m-d');
                                    $daftarHari = $perTanggal->get($tanggalSel, collect());
                                    $cellClass = 'cal-cell';
                                    if (! $hari['dalamBulanIni']) $cellClass .= ' is-outside';
                                    if ($hari['isHariIni']) $cellClass .= ' is-today';
                                    $jumlahHariIni = $daftarHari->count();
                                    $labelAngka = $hari['tanggal']->day === 1
                                        ? '1 '.$namaBulanSingkat[$hari['tanggal']->month - 1]
                                        : (string) $hari['tanggal']->day;
                                    $ariaTanggal = $hari['tanggal']->day.' '.$namaBulan[$hari['tanggal']->month - 1].' '.$hari['tanggal']->year;
                                @endphp
                                <div class="{{ $cellClass }}" role="gridcell" data-date="{{ $tanggalSel }}" data-tanggal="{{ $tanggalSel }}" aria-label="{{ $ariaTanggal }}, {{ $jumlahHariIni }} appointment">
                                    <button type="button" class="cal-cell__num">{{ $labelAngka }}</button>
                                    <div class="cal-cell__events">
                                        @foreach ($daftarHari as $ap)
                                            @php
                                                $sc = $statusColorMap[$ap->status] ?? $statusColorMap['menunggu'];
                                                $namaKlien = $ap->pelanggan?->nama ?? '-';
                                                $namaLayanan = $ap->layanans->pluck('nama_layanan')->implode(', ');
                                                $jamAp = $ap->jam_mulai?->format('H:i') ?? '';
                                                $cari = strtolower($namaKlien.' '.$namaLayanan);
                                                $judulChip = $jamAp.' '.$namaKlien.($namaLayanan !== '' ? ' - '.$namaLayanan : '');
                                            @endphp
                                            <button type="button"
                                                    class="cal-event cal-event--{{ $ap->status }}{{ $ap->status === 'selesai' ? ' is-done' : '' }}"
                                                    data-appointment-id="{{ $ap->id }}"
                                                    data-pelanggan-id="{{ $ap->id_pelanggan }}"
                                                    data-karyawan-id="{{ $ap->id_karyawan }}"
                                                    data-status="{{ $ap->status }}"
                                                    data-search="{{ $cari }}"
                                                    title="{{ $judulChip }}"
                                                    style="--client-color: {{ $sc['color'] }}; --client-bg: {{ $sc['bg'] }}; --client-fg: {{ $sc['color'] }};">
                                                @if ($ap->status === 'selesai')
                                                    <svg class="cal-event__check" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                @endif
                                                <span class="cal-event__time">{{ $jamAp }}</span>
                                                <span class="cal-event__name">{{ $namaKlien }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                    <div class="cal-cell__dots" aria-hidden="true"></div>
                                    <button type="button" class="cal-more" hidden></button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
    
    <!-- Day Schedule Modal -->
    <div id="day-schedule-modal" class="day-schedule-modal-overlay" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="day-schedule-title">
        <div class="day-schedule-dialog">
            <div class="day-schedule-header">
                <div class="day-schedule-header-left">
                    <div class="day-schedule-date-badge">
                        <span class="day-schedule-dow" id="day-schedule-dow">RAB</span>
                        <span class="day-schedule-dom" id="day-schedule-dom">26</span>
                    </div>
                    <div class="day-schedule-title-box">
                        <h2 class="day-schedule-title" id="day-schedule-title">Rabu, 26 Agustus 2026</h2>
                        <div class="day-schedule-meta">
                            <span class="day-schedule-count" id="day-schedule-count">0 Reservasi Terjadwal</span>
                            <span class="day-schedule-tz">GMT+07 (WIB)</span>
                        </div>
                    </div>
                </div>
                <div class="day-schedule-actions">
                    <a href="{{ route('appointment.create') }}" id="btn-add-for-day" class="btn-schedule-add-action">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Appointment</span>
                    </a>
                    <button type="button" id="btn-close-day-schedule" class="btn-close-schedule" aria-label="Tutup Jadwal Harian">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
            </div>
            <div class="day-schedule-body">
                <div class="gcal-timeline-container" id="gcal-timeline-container"></div>
            </div>
            <div class="day-schedule-footer">
                <span class="schedule-hint-text"><strong>Petunjuk:</strong> Klik salah satu kartu reservasi di atas untuk membuka rincian detail klien dan layanan.</span>
            </div>
        </div>
    </div>

    <!-- Appointment Detail Popover / Modal -->
    <div class="appointment-popover-modal" id="appointment-detail-modal" role="dialog" aria-hidden="true">
        <div class="popover-content card" id="popover-content"></div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var COLOR_MAP = @json($colorMap);
    var STATUS_MAP = @json($statusMap);
    var CSRF = '{{ csrf_token() }}';
    var URL_HARI = '{{ route('appointment.hari', ['tanggal' => 'P_TGL']) }}';
    var URL_SHOW = '{{ route('appointment.show', ['appointment' => 'P_ID']) }}';
    var URL_CREATE = '{{ route('appointment.create') }}';
    var URL_EDIT = '{{ route('appointment.edit', ['appointment' => 'P_ID']) }}';
    var URL_SELESAI = '{{ route('appointment.selesai', ['appointment' => 'P_ID']) }}';
    var URL_HAPUS = '{{ route('appointment.destroy', ['appointment' => 'P_ID']) }}';

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }
    function initials(name) {
        var w = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!w.length) return '?';
        var s = (w[0][0] || '').toUpperCase();
        if (w.length > 1 && w[1]) s += (w[1][0] || '').toUpperCase();
        return s || '?';
    }
    function pad2(n) { return (n < 10 ? '0' : '') + n; }
    function phoneDigits(v) { return String(v || '').replace(/\D/g, ''); }
    function colorOf(w) { return COLOR_MAP[w] || COLOR_MAP.orange; }
    function statusOf(s) { return STATUS_MAP[s] || { pill: 'pending', label: 'Menunggu' }; }
    function el(html) {
        var t = document.createElement('template');
        t.innerHTML = html.trim();
        return t.content.firstElementChild;
    }
    function closeOptive(modalEl) { if (modalEl) modalEl.style.display = 'none'; }
    function showModal(modalEl) { if (modalEl) modalEl.style.display = 'flex'; }
    function showDetail(modalEl) { if (modalEl) modalEl.classList.add('show'); }
    function hideDetail(modalEl) { if (modalEl) modalEl.classList.remove('show'); }

    // Notifikasi non-blocking (pengganti alert()).
    function toast(msg) {
        var t = document.createElement('div');
        t.className = 'cal-toast';
        t.setAttribute('role', 'status');
        t.textContent = msg;
        document.body.appendChild(t);
        requestAnimationFrame(function () { t.classList.add('show'); });
        setTimeout(function () {
            t.classList.remove('show');
            setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 220);
        }, 3200);
    }

    // ----- Filters -----
    var searchEl = document.getElementById('cal-quick-search');

    var btnHapusKlien = document.querySelector('.js-hapus-semua-klien');
    var btnHapusStaf = document.querySelector('.js-hapus-semua-staf');

    function boxesOf(selector) {
        return Array.prototype.slice.call(document.querySelectorAll(selector));
    }
    function groupState(selector) {
        var list = boxesOf(selector);
        var checked = list.filter(function (b) { return b.checked; }).length;
        return {
            total: list.length,
            checked: checked,
            none: checked === 0,
            all: list.length > 0 && checked === list.length,
            partial: checked > 0 && checked < list.length,
        };
    }
    function allUnchecked(selector) {
        var list = boxesOf(selector);
        return list.length > 0 && list.every(function (b) { return !b.checked; });
    }

    // Label tombol toggle: "Pilih Semua" saat belum ada yang dipilih,
    // "Hapus Semua" saat sudah ada yang dipilih.
    function updateToggleLabels() {
        if (btnHapusKlien) btnHapusKlien.textContent = allUnchecked('.js-filter-klien') ? 'Pilih Semua' : 'Hapus Semua';
        if (btnHapusStaf) btnHapusStaf.textContent = allUnchecked('.js-filter-staf') ? 'Pilih Semua' : 'Hapus Semua';
    }

    function applyFilters() {
        var q = (searchEl ? searchEl.value : '').toLowerCase().trim();

        var cg = groupState('.js-filter-klien');
        var sg = groupState('.js-filter-staf');

        // Klien dan stylist difilter secara independen lalu digabung (union):
        // appointment tampil jika klien-nya tercentang ATAU stylist-nya tercentang.
        // Grup yang tercentang penuh dianggap tidak menyaring, supaya centang satu
        // klien tetap fokus ke klien itu meski semua stylist tercentang.
        var adaPenyaring = cg.partial || sg.partial;

        document.querySelectorAll('.cal-event').forEach(function (chip) {
            var show;

            if (!adaPenyaring) {
                // Semua tercentang -> tampil semua; keduanya kosong -> tidak ada yang tampil.
                show = !(cg.none && sg.none);
            } else {
                var clientOk = false;
                var stylistOk = false;
                if (cg.partial) {
                    var ck = document.querySelector('.js-filter-klien[data-pelanggan-id="' + chip.dataset.pelangganId + '"]');
                    clientOk = ck ? ck.checked : true;
                }
                if (sg.partial) {
                    var sk = document.querySelector('.js-filter-staf[data-karyawan-id="' + chip.dataset.karyawanId + '"]');
                    stylistOk = sk ? sk.checked : true;
                }
                show = clientOk || stylistOk;
            }

            // Status: hanya tampil kalau statusnya tercentang.
            var ss = document.querySelector('.js-filter-status[data-status="' + chip.dataset.status + '"]');
            if (ss && !ss.checked) show = false;

            if (show && q) show = (chip.dataset.search || '').indexOf(q) !== -1;
            chip.classList.toggle('is-filtered', !show);
        });

        updateToggleLabels();
        scheduleFit();
    }

    // ----- Isi event per sel menyesuaikan tinggi sel (seperti Google Calendar) -----
    function containerWidth() {
        var card = document.querySelector('.calendar-viewport-card');
        if (!card) return 0;
        var cs = window.getComputedStyle(card);
        var pl = parseFloat(cs.paddingLeft) || 0;
        var pr = parseFloat(cs.paddingRight) || 0;
        return card.clientWidth - pl - pr;
    }

    function fitCellEvents(cell, width, height) {
        var events = Array.prototype.slice.call(cell.querySelectorAll('.cal-event'));
        var active = events.filter(function (e) { return !e.classList.contains('is-filtered'); });
        var more = cell.querySelector('.cal-more');
        var dots = cell.querySelector('.cal-cell__dots');
        var i;

        if (width > 0 && width < 560) {
            // Layar sempit: teks disembunyikan, ganti baris titik emas (maks 3).
            events.forEach(function (e) { e.hidden = true; });
            if (more) more.hidden = true;
            if (dots) {
                dots.innerHTML = '';
                var nDots = Math.min(active.length, 3);
                for (i = 0; i < nDots; i++) {
                    var d = document.createElement('span');
                    d.className = 'cal-dot';
                    dots.appendChild(d);
                }
            }
            return;
        }

        if (dots) dots.innerHTML = '';

        var cap;
        if (width > 0 && width < 760) {
            cap = 1;
        } else {
            var space = height - 30 - 18;
            cap = Math.max(0, Math.floor(space / 24));
        }

        active.forEach(function (e, idx) { e.hidden = idx >= cap; });

        if (more) {
            if (active.length > cap) {
                more.hidden = false;
                more.textContent = '+' + (active.length - cap) + ' lagi';
            } else {
                more.hidden = true;
            }
        }
    }

    var fitRaf = null;
    function fitAll() {
        var body = document.querySelector('.cal-month__body');
        if (!body) return;
        var width = containerWidth();
        var cells = Array.prototype.slice.call(document.querySelectorAll('.cal-cell'));
        // Baca dulu semua tinggi, baru tulis (hindari layout thrash).
        var heights = cells.map(function (c) { return c.clientHeight; });
        cells.forEach(function (cell, idx) { fitCellEvents(cell, width, heights[idx]); });
    }
    function scheduleFit() {
        if (fitRaf) return;
        fitRaf = requestAnimationFrame(function () {
            fitRaf = null;
            fitAll();
        });
    }

    if (searchEl) searchEl.addEventListener('input', applyFilters);
    var btnCalSearch = document.getElementById('btn-cal-search');
    if (btnCalSearch) btnCalSearch.addEventListener('click', applyFilters);
    document.addEventListener('change', function (e) {
        if (e.target.matches('.js-filter-klien, .js-filter-staf, .js-filter-status')) applyFilters();
    });

    function bindToggle(btn, selector) {
        if (!btn) return;
        btn.addEventListener('click', function () {
            var pilihSemua = allUnchecked(selector);
            boxesOf(selector).forEach(function (b) { b.checked = pilihSemua; });
            applyFilters();
        });
    }
    bindToggle(btnHapusKlien, '.js-filter-klien');
    bindToggle(btnHapusStaf, '.js-filter-staf');

    // ----- Day Schedule Modal -----
    var dayModal = document.getElementById('day-schedule-modal');
    var dDOW = document.getElementById('day-schedule-dow');
    var dDOM = document.getElementById('day-schedule-dom');
    var dTitle = document.getElementById('day-schedule-title');
    var dCount = document.getElementById('day-schedule-count');
    var dBtnAdd = document.getElementById('btn-add-for-day');
    var gcal = document.getElementById('gcal-timeline-container');
    var detailModal = document.getElementById('appointment-detail-modal');
    var DOW_SHORT = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
    var DOW_FULL = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    function renderDayModal(day) {
        var parts = day.tanggal.split('-');
        var y = parts[0], m = parts[1], d = parts[2];
        var date = new Date(y, m - 1, d);
        dDOW.textContent = DOW_SHORT[date.getDay()];
        dDOM.textContent = d;
        dTitle.textContent = DOW_FULL[date.getDay()] + ', ' + d + ' ' + BULAN[date.getMonth()] + ' ' + y;
        dCount.textContent = day.jumlah + ' Reservasi Terjadwal';
        dBtnAdd.href = URL_CREATE + '?tanggal=' + day.tanggal;

        var byHour = {};
        (day.appointments || []).forEach(function (a) {
            var h = parseInt((a.jam_mulai || '09:00').split(':')[0], 10);
            (byHour[h] = byHour[h] || []).push(a);
        });

        var rows = '';
        for (var h = day.jam_buka; h <= day.jam_tutup; h++) {
            var list = byHour[h] || [];
            var cards = '';
            if (list.length) {
                cards = list.map(function (a) {
                    var c = colorOf(a.warna);
                    var st = statusOf(a.status);
                    return '<div class="gcal-event-card js-gcal-card" data-apt-id="' + a.id + '" style="border-left-color: ' + c.color + ';">' +
                        '<div class="gcal-card-left">' +
                            '<div class="gcal-avatar-box" style="background-color: ' + c.bg + '; color: ' + c.color + ';">' + initials(a.pelanggan) + '</div>' +
                            '<div class="gcal-meta-text">' +
                                '<span class="gcal-client-title">' + esc(a.pelanggan) + '</span>' +
                                '<span class="gcal-service-name">' + esc(a.layanan) + '</span>' +
                                '<span class="gcal-stylist-tag">Stylist: <strong>' + esc(a.karyawan) + '</strong> &bull; ' + a.durasi_menit + ' mnt</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="gcal-card-right">' +
                            '<span class="gcal-time-badge">' + a.jam_mulai + ' WIB</span>' +
                            '<span class="gcal-status-pill ' + st.pill + '">' + st.label + '</span>' +
                        '</div>' +
                    '</div>';
                }).join('');
            }
            rows += '<div class="gcal-hour-row">' +
                '<div class="gcal-hour-time">' + pad2(h) + ':00</div>' +
                '<div class="gcal-hour-slot">' + (cards || '') + '</div>' +
            '</div>';
        }
        if (!(day.appointments || []).length) {
            rows += '<div class="gcal-empty-notice">Tidak ada reservasi pada tanggal ini. Klik <strong>+ Tambah Appointment</strong> untuk membuat jadwal baru.</div>';
        }
        gcal.innerHTML = rows;
        showModal(dayModal);
    }

    function openDayModal(tanggal) {
        hideDetail(detailModal);
        fetch(URL_HARI.replace('P_TGL', tanggal))
            .then(function (r) { return r.json(); })
            .then(renderDayModal)
            .catch(function () { toast('Gagal memuat detail hari.'); });
    }

    document.getElementById('btn-close-day-schedule').addEventListener('click', function () { closeOptive(dayModal); });

    // ----- Detail Popover -----
    function renderDetailModal(a) {
        var c = colorOf(a.warna);
        var st = statusOf(a.status);
        var lamanya = (a.jam_mulai || '') + ' - ' + (a.jam_selesai || '') + ' (' + a.durasi_menit + ' mnt)';
        var infoRows = '' +
            popRow(IX_CLOCK, '<span style="font-weight: 700;">' + lamanya + '</span>') +
            popRow(IX_USER, 'Stylist: <span style="font-weight: 700;">' + esc(a.karyawan) + '</span>') +
            (a.no_wa ? popRow(IX_PHONE, '<a href="https://wa.me/' + phoneDigits(a.no_wa) + '" target="_blank" rel="noopener" style="color: var(--text-link); font-weight: 700;">' + esc(a.no_wa) + '</a>') : '');
        var layanan = (a.layanans && a.layanans.length)
            ? '<div class="pop-notes-box"><span style="font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">Layanan</span>' +
                a.layanans.map(function (n) { return '<div style="margin-top: 4px;">&bull; ' + esc(n) + '</div>'; }).join('') + '</div>'
            : '';
        var preferensi = a.preferensi
            ? '<div class="pop-notes-box"><span style="font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">Preferensi</span><div style="margin-top: 4px; white-space: pre-wrap;">' + esc(a.preferensi) + '</div></div>'
            : '';

        var footer = '';
        footer += '<form method="POST" action="' + URL_HAPUS.replace('P_ID', a.id) + '" class="js-delete-form" style="margin:0;">' +
            '<input type="hidden" name="_token" value="' + CSRF + '">' +
            '<input type="hidden" name="_method" value="DELETE">' +
            '<button type="submit" class="btn btn-danger">Hapus</button>' +
        '</form>' +
        '<a href="' + URL_EDIT.replace('P_ID', a.id) + '" class="btn btn-secondary">Edit</a>';
        if (a.status !== 'selesai') {
            footer += '<form method="POST" action="' + URL_SELESAI.replace('P_ID', a.id) + '" style="margin:0;"><input type="hidden" name="_token" value="' + CSRF + '"><input type="hidden" name="_method" value="PUT"><button type="submit" class="btn btn-primary">' + IX_CHECK + ' Selesaikan</button></form>';
        }

        var html = '<div class="popover-header">' +
            '<div class="popover-client-meta">' +
                '<div class="popover-avatar" style="background-color: ' + c.bg + '; border-color: ' + c.color + '; color: ' + c.color + ';">' + initials(a.pelanggan) + '</div>' +
                '<div>' +
                    '<h3 class="popover-client-name">' + esc(a.pelanggan) + '</h3>' +
                    '<span class="popover-service-name">' + esc(a.layanan) + '</span>' +
                '</div>' +
            '</div>' +
            '<div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">' +
                '<span class="gcal-status-pill ' + st.pill + '">' + st.label + '</span>' +
                '<button type="button" class="popover-close-btn" id="btn-close-popover" aria-label="Tutup Detail">' + IX_X + '</button>' +
            '</div>' +
        '</div>' +
        '<div class="popover-body">' + infoRows + layanan + preferensi + '</div>' +
        '<div class="popover-footer">' + footer + '</div>';

        document.getElementById('popover-content').innerHTML = html;
        document.getElementById('btn-close-popover').addEventListener('click', function () { hideDetail(detailModal); });
        showDetail(detailModal);
    }

    function popRow(icon, innerHtml) {
        return '<div class="pop-info-row">' + icon + '<span style="min-width: 0;">' + innerHtml + '</span></div>';
    }

    var IX_CLOCK = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
    var IX_USER  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
    var IX_PHONE = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';
    var IX_CHECK = '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    var IX_X = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

    function openDetailModal(id) {
        fetch(URL_SHOW.replace('P_ID', id))
            .then(function (r) { return r.json(); })
            .then(renderDetailModal)
            .catch(function () { toast('Gagal memuat detail appointment.'); });
    }

    // ----- Events -----
    document.addEventListener('click', function (e) {
        // Di dalam timeline harian: kartu appointment -> detail appointment.
        // Timeline tetap terbuka di belakang agar menutup detail kembali ke timeline.
        var card = e.target.closest('.js-gcal-card');
        if (card) {
            openDetailModal(card.dataset.aptId);
            return;
        }

        var mini = e.target.closest('.mini-cal-cell');
        if (mini) { openDayModal(mini.dataset.tanggal); return; }

        // Seluruh bagian kotak tanggal (angka, badge janji temu, "+N lagi",
        // area kosong) -> buka timeline detail hari.
        var cell = e.target.closest('.cal-cell');
        if (cell) { openDayModal(cell.dataset.date); return; }

        var bg = e.target.closest('.day-schedule-modal-overlay');
        if (bg && bg === e.target) closeOptive(dayModal);
        var popBg = e.target.closest('.appointment-popover-modal');
        if (popBg && popBg === e.target) hideDetail(detailModal);
    });

    // Konfirmasi hapus tanpa confirm(): klik dua kali pada tombol Hapus.
    document.addEventListener('submit', function (e) {
        var form = e.target.closest('.js-delete-form');
        if (!form) return;
        if (form.dataset.confirm === '1') return;
        e.preventDefault();
        form.dataset.confirm = '1';
        var btn = form.querySelector('button[type="submit"]');
        if (btn) btn.textContent = 'Klik lagi untuk hapus';
        setTimeout(function () {
            if (form) {
                form.dataset.confirm = '';
                if (btn) btn.textContent = 'Hapus';
            }
        }, 4000);
    });

    // Panel filter bisa dilipat pada layar sempit (< 880px).
    var btnCalFilter = document.getElementById('btn-cal-filter');
    var calSidePanel = document.getElementById('cal-side-panel');
    if (btnCalFilter && calSidePanel) {
        btnCalFilter.addEventListener('click', function () {
            var open = calSidePanel.classList.toggle('is-open');
            btnCalFilter.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Hitung ulang jumlah event yang tampil saat ukuran kalender berubah.
    (function observeCalendarFit() {
        var body = document.querySelector('.cal-month__body');
        if (body && window.ResizeObserver) {
            new ResizeObserver(scheduleFit).observe(body);
        }
        window.addEventListener('resize', scheduleFit);
    })();

    applyFilters();
})();
</script>
@endpush