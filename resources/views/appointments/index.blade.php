@extends('layouts.app')

@section('title', 'Appointment')

@section('nav-active', 'appointment')

@section('main-class', 'calendar-main-layout')

@section('container_width', 'app-content-wide')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/appointment.css') }}">
@endpush

@php
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $judulBulan = $namaBulan[$bulanAktif->month - 1].' '.$bulanAktif->year;

    $colorMap = [
        'amber'   => ['color' => '#F59E0B', 'bg' => '#FEF3C7'],
        'emerald' => ['color' => '#10B981', 'bg' => '#D1FAE5'],
        'blue'    => ['color' => '#3B82F6', 'bg' => '#DBEAFE'],
        'violet'  => ['color' => '#8B5CF6', 'bg' => '#EDE9FE'],
        'pink'    => ['color' => '#EC4899', 'bg' => '#FCE7F3'],
        'orange'  => ['color' => '#E5A93C', 'bg' => '#FFFBEB'],
        'teal'    => ['color' => '#14B8A6', 'bg' => '#F0FDFA'],
        'rose'    => ['color' => '#E11D48', 'bg' => '#FFE4E6'],
    ];

    $statusMap = [
        'terkonfirmasi' => ['pill' => 'confirmed', 'label' => 'Terkonfirmasi'],
        'menunggu'      => ['pill' => 'pending',   'label' => 'Menunggu'],
        'selesai'       => ['pill' => 'done',      'label' => 'Selesai'],
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

                <a href="{{ route('appointment.create') }}" class="btn-primary-gold btn-schedule-action">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    <span>+ Appointment</span>
                </a>
            </div>
        </header>

        <!-- Main Calendar Split Grid -->
        <div class="calendar-workspace-grid">

            <!-- SISI KIRI: Filter Panel & Mini Calendar -->
            <aside class="calendar-side-panel">

                <div class="mini-calendar-card">
                    <div class="mini-cal-header">
                        <span class="mini-cal-month">{{ $judulBulan }}</span>
                        <div class="mini-cal-arrows">
                            <a href="{{ route('appointment.index', ['bulan' => $prevBulan]) }}" class="mini-arrow-btn" aria-label="Bulan sebelumnya">‹</a>
                            <a href="{{ route('appointment.index', ['bulan' => $nextBulan]) }}" class="mini-arrow-btn" aria-label="Bulan berikutnya">›</a>
                        </div>
                    </div>
                    <div class="mini-cal-grid">
                        <span class="mini-cal-day-label">Sn</span>
                        <span class="mini-cal-day-label">Sl</span>
                        <span class="mini-cal-day-label">Rb</span>
                        <span class="mini-cal-day-label">Km</span>
                        <span class="mini-cal-day-label">Jm</span>
                        <span class="mini-cal-day-label">Sb</span>
                        <span class="mini-cal-day-label">Mg</span>
                        @foreach ($hariGrid as $hari)
                            @php
                                $miniTanggal = $hari['tanggal']->format('Y-m-d');
                                $cls = 'mini-cal-cell';
                                if (! $hari['dalamBulanIni']) $cls .= ' other-month inactive';
                                if ($hari['isHariIni']) $cls .= ' today';
                                if ($hari['dalamBulanIni'] && $perTanggal->has($miniTanggal)) $cls .= ' has-event';
                            @endphp
                            @if ($hari['dalamBulanIni'])
                                <button type="button" class="{{ $cls }}" data-tanggal="{{ $miniTanggal }}">{{ $hari['tanggal']->day }}</button>
                            @else
                                <span class="{{ $cls }}">{{ $hari['tanggal']->day }}</span>
                            @endif
                        @endforeach
                    </div>
                </div>

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
                    <span class="filter-section-title">Status Appointment</span>
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
                    <div class="month-days-header">
                        <span>Sen</span>
                        <span>Sel</span>
                        <span>Rab</span>
                        <span>Kam</span>
                        <span>Jum</span>
                        <span>Sab</span>
                        <span>Min</span>
                    </div>
                    <div class="month-cells-grid">
                        @foreach ($hariGrid as $hari)
                            @php
                                $tanggalSel = $hari['tanggal']->format('Y-m-d');
                                $daftarHari = $perTanggal->get($tanggalSel, collect());
                                $cellClass = 'month-day-cell';
                                if (! $hari['dalamBulanIni']) $cellClass .= ' other-month';
                                if ($hari['isHariIni']) $cellClass .= ' is-today';
                            @endphp
                            <div class="{{ $cellClass }}" data-tanggal="{{ $tanggalSel }}">
                                <div class="cell-top-bar">
                                    <span class="cell-day-num">{{ $hari['tanggal']->day }}</span>
                                </div>
                                @foreach ($daftarHari as $ap)
                                    @php
                                        $warna = $ap->pelanggan ? \App\Support\AppointmentColor::forId((int) $ap->pelanggan->id) : 'orange';
                                        $cc = $colorMap[$warna] ?? $colorMap['orange'];
                                        $cari = strtolower(($ap->pelanggan?->nama ?? '').' '.$ap->layanans->pluck('nama_layanan')->implode(', '));
                                    @endphp
                                    <div class="apt-chip-item"
                                         data-pelanggan-id="{{ $ap->id_pelanggan }}"
                                         data-karyawan-id="{{ $ap->id_karyawan }}"
                                         data-status="{{ $ap->status }}"
                                         data-search="{{ $cari }}"
                                         title="{{ $ap->jam_mulai?->format('H:i') }} {{ $ap->pelanggan?->nama ?? '-' }} - {{ $ap->layanans->pluck('nama_layanan')->implode(', ') }}"
                                         style="background-color: {{ $cc['bg'] }}; border-left-color: {{ $cc['color'] }};">
                                        @if ($ap->status === 'selesai')
                                            <span style="flex-shrink: 0; text-decoration: line-through; opacity: 0.7;">{{ $ap->jam_mulai?->format('H:i') }}</span>
                                        @else
                                            <span class="apt-chip-time" style="color: {{ $cc['color'] }};">{{ $ap->jam_mulai?->format('H:i') }}</span>
                                        @endif
                                        <span class="apt-chip-client">{{ $ap->pelanggan?->nama ?? '-' }}</span>
                                    </div>
                                @endforeach
                                @if ($daftarHari->count() > 3)
                                    <span class="apt-more-badge" style="display: none;" data-tanggal="{{ $tanggalSel }}">+{{ $daftarHari->count() - 3 }} lainnya</span>
                                @endif
                            </div>
                        @endforeach
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
                        <span>+ Tambah Appointment</span>
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

    // ----- Filters -----
    var searchEl = document.getElementById('cal-quick-search');

    function applyFilters() {
        var q = (searchEl ? searchEl.value : '').toLowerCase().trim();
        document.querySelectorAll('.apt-chip-item').forEach(function (chip) {
            var show = true;
            var ck = document.querySelector('.js-filter-klien[data-pelanggan-id="' + chip.dataset.pelangganId + '"]');
            if (ck && !ck.checked) show = false;
            var sk = document.querySelector('.js-filter-staf[data-karyawan-id="' + chip.dataset.karyawanId + '"]');
            if (sk && !sk.checked) show = false;
            var ss = document.querySelector('.js-filter-status[data-status="' + chip.dataset.status + '"]');
            if (ss && !ss.checked) show = false;
            if (show && q) show = (chip.dataset.search || '').indexOf(q) !== -1;
            chip.classList.toggle('hidden', !show);
        });
        refreshDayCells();
    }

    function refreshDayCells() {
        document.querySelectorAll('.month-day-cell').forEach(function (cell) {
            var visible = Array.prototype.slice.call(cell.querySelectorAll('.apt-chip-item'))
                .filter(function (p) { return !p.classList.contains('hidden'); });
            visible.forEach(function (p, i) { p.style.display = (i < 3) ? '' : 'none'; });
            var more = cell.querySelector('.apt-more-badge');
            if (more) {
                var n = visible.length - 3;
                more.textContent = (n > 0) ? '+ ' + n + ' lainnya' : '';
                more.style.display = (n > 0) ? '' : 'none';
            }
        });
    }

    if (searchEl) searchEl.addEventListener('input', applyFilters);
    document.addEventListener('change', function (e) {
        if (e.target.matches('.js-filter-klien, .js-filter-staf, .js-filter-status')) applyFilters();
    });

    var btnHapusKlien = document.querySelector('.js-hapus-semua-klien');
    if (btnHapusKlien) btnHapusKlien.addEventListener('click', function () {
        document.querySelectorAll('.js-filter-klien').forEach(function (i) { i.checked = false; });
        applyFilters();
    });
    var btnHapusStaf = document.querySelector('.js-hapus-semua-staf');
    if (btnHapusStaf) btnHapusStaf.addEventListener('click', function () {
        document.querySelectorAll('.js-filter-staf').forEach(function (i) { i.checked = false; });
        applyFilters();
    });

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
        if (detailModal) detailModal.style.display = 'none';
        fetch(URL_HARI.replace('P_TGL', tanggal))
            .then(function (r) { return r.json(); })
            .then(renderDayModal)
            .catch(function () { alert('Gagal memuat detail hari.'); });
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
            (a.no_wa ? popRow(IX_PHONE, '<a href="https://wa.me/' + phoneDigits(a.no_wa) + '" target="_blank" rel="noopener" style="color: #B45309; font-weight: 700;">' + esc(a.no_wa) + '</a>') : '');
        var layanan = (a.layanans && a.layanans.length)
            ? '<div class="pop-notes-box"><span style="font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">Layanan</span>' +
                a.layanans.map(function (n) { return '<div style="margin-top: 4px;">&bull; ' + esc(n) + '</div>'; }).join('') + '</div>'
            : '';
        var preferensi = a.preferensi
            ? '<div class="pop-notes-box"><span style="font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">Preferensi</span><div style="margin-top: 4px; white-space: pre-wrap;">' + esc(a.preferensi) + '</div></div>'
            : '';

        var footer = '';
        if (a.status !== 'selesai') {
            footer += '<form method="POST" action="' + URL_SELESAI.replace('P_ID', a.id) + '" style="margin:0;"><input type="hidden" name="_token" value="' + CSRF + '"><input type="hidden" name="_method" value="PUT"><button type="submit" class="btn-pop-action btn-pop-complete">&#10003; Selesaikan</button></form>';
        }
        footer += '<a href="' + URL_EDIT.replace('P_ID', a.id) + '" class="btn-pop-action btn-pop-edit">Edit</a>' +
            '<form method="POST" action="' + URL_HAPUS.replace('P_ID', a.id) + '" style="margin:0; margin-left:auto;" onsubmit="return confirm(\'Hapus appointment ini? Tindakan tidak bisa dibatalkan.\')">' +
                '<input type="hidden" name="_token" value="' + CSRF + '">' +
                '<input type="hidden" name="_method" value="DELETE">' +
                '<button type="submit" class="btn-pop-action btn-pop-delete">Hapus</button>' +
            '</form>';

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
                '<button type="button" class="popover-close-btn" id="btn-close-popover" aria-label="Tutup Detail">&#10005;</button>' +
            '</div>' +
        '</div>' +
        '<div class="popover-body">' + infoRows + layanan + preferensi + '</div>' +
        '<div class="popover-footer">' + footer + '</div>';

        document.getElementById('popover-content').innerHTML = html;
        document.getElementById('btn-close-popover').addEventListener('click', function () { closeOptive(detailModal); });
        detailModal.classList.add('show');
    }

    function popRow(icon, innerHtml) {
        return '<div class="pop-info-row">' + icon + '<span style="min-width: 0;">' + innerHtml + '</span></div>';
    }

    var IX_CLOCK = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
    var IX_USER  = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>';
    var IX_PHONE = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>';

    function openDetailModal(id) {
        fetch(URL_SHOW.replace('P_ID', id))
            .then(function (r) { return r.json(); })
            .then(renderDetailModal)
            .catch(function () { alert('Gagal memuat detail appointment.'); });
    }

    // ----- Events -----
    document.addEventListener('click', function (e) {
        var card = e.target.closest('.js-gcal-card');
        if (card) {
            closeOptive(dayModal);
            openDetailModal(card.dataset.aptId);
            return;
        }
        var mini = e.target.closest('.mini-cal-cell');
        if (mini) { openDayModal(mini.dataset.tanggal); return; }
        var cell = e.target.closest('.month-day-cell');
        if (cell) { openDayModal(cell.dataset.tanggal); return; }
        var bg = e.target.closest('.day-schedule-modal-overlay');
        if (bg && bg === e.target) closeOptive(dayModal);
        var popBg = e.target.closest('.appointment-popover-modal');
        if (popBg && popBg === e.target) { closeOptive(detailModal); detailModal.classList.remove('show'); }
    });

    applyFilters();
})();
</script>
@endpush