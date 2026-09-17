@extends('layouts.app')

@section('title', 'Appointment')

@php
    // Semua class di bawah ditulis LITERAL supaya ter-compile oleh Tailwind JIT.
    $colorClasses = [
        'amber'   => ['bg' => 'bg-amber-300',   'text' => 'text-amber-900', 'border' => 'border-amber-500', 'dot' => 'bg-amber-500'],
        'emerald' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-800', 'border' => 'border-emerald-500', 'dot' => 'bg-emerald-500'],
        'blue'    => ['bg' => 'bg-blue-200',    'text' => 'text-blue-900',   'border' => 'border-blue-500',   'dot' => 'bg-blue-500'],
        'violet'  => ['bg' => 'bg-violet-100',  'text' => 'text-violet-700', 'border' => 'border-violet-400', 'dot' => 'bg-violet-500'],
        'pink'    => ['bg' => 'bg-pink-100',    'text' => 'text-pink-700',   'border' => 'border-pink-400',   'dot' => 'bg-pink-500'],
        'orange'  => ['bg' => 'bg-orange-100',  'text' => 'text-orange-700', 'border' => 'border-orange-400', 'dot' => 'bg-orange-500'],
        'teal'    => ['bg' => 'bg-teal-100',    'text' => 'text-teal-700',   'border' => 'border-teal-400',   'dot' => 'bg-teal-500'],
        'rose'    => ['bg' => 'bg-rose-100',    'text' => 'text-rose-700',   'border' => 'border-rose-400',   'dot' => 'bg-rose-500'],
    ];
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $judulBulan = $namaBulan[$bulanAktif->month - 1].' '.$bulanAktif->year;
@endphp

@section('content')
    <div class="mb-6 grid gap-4 xl:grid-cols-[1fr_auto] xl:items-end">
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-text-primary">Appointment</h1>
            <p class="mt-1 text-sm text-text-muted">
                {{ $jumlahBulan }} janji temu di {{ $judulBulan }}.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('appointment.index') }}"
               class="inline-flex shrink-0 items-center rounded-lg bg-card px-3.5 py-2 text-sm font-medium text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                Hari Ini
            </a>
            <div class="flex shrink-0 items-center gap-0.5">
                <a href="{{ route('appointment.index', ['bulan' => $prevBulan]) }}" aria-label="Bulan sebelumnya"
                   class="inline-flex rounded-lg bg-card p-2 text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
                <a href="{{ route('appointment.index', ['bulan' => $nextBulan]) }}" aria-label="Bulan berikutnya"
                   class="inline-flex rounded-lg bg-card p-2 text-text-secondary ring-1 ring-inset ring-gray-300 hover:bg-card-hover">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                </a>
            </div>

            <div class="relative order-last w-full min-w-0 sm:order-none sm:w-72">
                <input type="text" id="search-appointment" placeholder="Cari appointment..."
                       class="block w-full rounded-lg border border-gray-300 bg-card py-2 pl-3 pr-9 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <svg class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
            </div>

            <select id="period-select" aria-label="Periode"
                    class="shrink-0 rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <option value="bulanan">Bulanan</option>
            </select>

            <a href="{{ route('appointment.create') }}"
               class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-accent px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors duration-150 hover:brightness-95">
                <span class="text-lg leading-none">+</span>
                <span>Appointment</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[280px_1fr]">

        {{-- KOLOM KIRI: sidebar-dalam-halaman (~280px) --}}
        <div class="space-y-4">

            {{-- 1. MINI KALENDER --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <p class="mb-3 text-center text-sm font-semibold text-gray-900">{{ $judulBulan }}</p>
                <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-semibold uppercase tracking-wide text-gray-400">
                    <div>Sn</div>
                    <div>Sl</div>
                    <div>Rb</div>
                    <div>Km</div>
                    <div>Jm</div>
                    <div>Sb</div>
                    <div>Mg</div>
                </div>
                <div class="mt-1 grid grid-cols-7 gap-1 text-center text-xs">
                    @foreach ($hariGrid as $hari)
                        @php $miniTanggal = $hari['tanggal']->format('Y-m-d'); @endphp
                        <button type="button"
                                class="js-buka-hari relative mx-auto flex h-7 w-7 items-center justify-center rounded-full transition
                                {{ $hari['dalamBulanIni']
                                    ? ($hari['isHariIni'] ? 'bg-afwo-gold font-bold text-white' : 'text-gray-700 hover:bg-gray-100')
                                    : 'text-gray-300' }}"
                                data-tanggal="{{ $miniTanggal }}">
                            {{ $hari['tanggal']->day }}
                            @if ($perTanggal->has($miniTanggal) && ! $hari['isHariIni'])
                                <span class="absolute bottom-0.5 h-1 w-1 rounded-full bg-afwo-gold"></span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- 2. PANEL KLIEN & JADWAL --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900">Klien &amp; Jadwal</span>
                    <button type="button" class="js-hapus-semua-klien rounded-full bg-afwo-pill px-2 py-0.5 text-xs font-medium text-gray-600 hover:bg-amber-100">Hapus Semua</button>
                </div>
                <div class="space-y-1.5">
                    @foreach ($klienList as $klien)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" class="js-filter-klien" data-pelanggan-id="{{ $klien['id'] }}" checked>
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-{{ $klien['warna'] }}-500"></span>
                            <span class="min-w-0 truncate">{{ $klien['nama'] }}</span>
                            <span class="ml-auto shrink-0 text-xs text-gray-400">{{ $klien['jumlah'] }}</span>
                        </label>
                    @endforeach
                    @if ($klienList->isEmpty())
                        <p class="text-sm text-gray-400">Belum ada klien terjadwal bulan ini.</p>
                    @endif
                </div>
            </div>

            {{-- 3. PANEL STYLIST / KARYAWAN --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-semibold text-gray-900">Stylist / Karyawan</span>
                    <button type="button" class="js-hapus-semua-staf rounded-full bg-afwo-pill px-2 py-0.5 text-xs font-medium text-gray-600 hover:bg-amber-100">Hapus Semua</button>
                </div>
                <div class="space-y-1.5">
                    @foreach ($stylistList as $staf)
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" class="js-filter-staf" data-karyawan-id="{{ $staf['id'] }}" checked>
                            <span class="h-2.5 w-2.5 shrink-0 rounded-full bg-{{ $staf['warna'] }}-500"></span>
                            <span class="min-w-0 truncate">{{ $staf['nama'] }}</span>
                            <span class="ml-auto shrink-0 text-xs text-gray-400">{{ $staf['jumlah'] }}</span>
                        </label>
                    @endforeach
                    @if ($stylistList->isEmpty())
                        <p class="text-sm text-gray-400">Belum ada stylist terjadwal bulan ini.</p>
                    @endif
                </div>
            </div>

            {{-- 4. PANEL STATUS APPOINTMENT --}}
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <span class="text-sm font-semibold text-gray-900">Status Appointment</span>
                <div class="mt-2 space-y-1.5">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" class="js-filter-status" data-status="terkonfirmasi" checked>
                        <span class="h-2 w-2 shrink-0 rounded-full bg-green-500"></span>
                        Terkonfirmasi
                        <span class="ml-auto shrink-0 text-xs text-gray-400">{{ $statusCounts->get('terkonfirmasi', 0) }}</span>
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" class="js-filter-status" data-status="selesai" checked>
                        <span class="h-2 w-2 shrink-0 rounded-full bg-gray-400"></span>
                        Selesai
                        <span class="ml-auto shrink-0 text-xs text-gray-400">{{ $statusCounts->get('selesai', 0) }}</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- KOLOM KANAN: kalender utama --}}
        <div>

            {{-- Baris nama hari (wajib horizontal, grid 7 kolom) --}}
            <div class="grid grid-cols-7 gap-1 border-b border-gray-100 pb-2 text-center text-xs font-semibold uppercase tracking-wide text-gray-400">
                <div>Sen</div>
                <div>Sel</div>
                <div>Rab</div>
                <div>Kam</div>
                <div>Jum</div>
                <div>Sab</div>
                <div>Min</div>
            </div>

            {{-- Grid tanggal (7 kolom, beberapa baris) --}}
            <div class="mt-1 grid grid-cols-7 gap-1">
                @foreach ($hariGrid as $hari)
                    @php
                        $tanggalSel = $hari['tanggal']->format('Y-m-d');
                        $daftarHari = $perTanggal->get($tanggalSel, collect());
                    @endphp
                    <button type="button"
                            data-tanggal="{{ $tanggalSel }}"
                            class="js-buka-hari min-h-[110px] rounded-lg border p-2 text-left align-top
                            {{ $hari['dalamBulanIni'] ? 'border-gray-100 bg-white' : 'border-transparent bg-gray-50 text-gray-300' }}
                            {{ $hari['isHariIni'] ? 'ring-2 ring-afwo-gold' : '' }}">
                        <span class="text-sm font-medium {{ $hari['dalamBulanIni'] ? 'text-gray-900' : 'text-gray-300' }}">
                            {{ $hari['tanggal']->day }}
                        </span>

                        <div class="mt-1.5 space-y-1">
                            @foreach ($daftarHari as $ap)
                                @php
                                    $warna = $ap->pelanggan ? App\Support\AppointmentColor::forId((int) $ap->pelanggan->id) : 'orange';
                                    $cc = $colorClasses[$warna] ?? $colorClasses['orange'];
                                    $namaKlien = $ap->pelanggan?->nama ?? '-';
                                    $cari = strtolower($namaKlien.' '.$ap->layanans->pluck('nama_layanan')->implode(', '));
                                @endphp
                                <button type="button"
                                        class="appointment-pill flex w-full items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] leading-tight transition hover:brightness-95
                                        {{ $cc['bg'] }} {{ $cc['text'] }}
                                        {{ $ap->status === 'selesai' ? 'opacity-60' : '' }}"
                                        data-appointment-id="{{ $ap->id }}"
                                        data-pelanggan-id="{{ $ap->id_pelanggan }}"
                                        data-karyawan-id="{{ $ap->id_karyawan }}"
                                        data-status="{{ $ap->status }}"
                                        data-search="{{ $cari }}"
                                        data-tanggal="{{ $tanggalSel }}">
                                    <span class="shrink-0 font-semibold">{{ $ap->jam_mulai?->format('H:i') }}</span>
                                    <span class="min-w-0 truncate">{{ $namaKlien }}</span>
                                </button>
                            @endforeach

                            @if ($daftarHari->count() > 3)
                                <button type="button"
                                        class="more-link flex w-full items-center justify-center rounded-md border border-dashed border-gray-200 px-1 py-0.5 text-[10px] font-medium text-gray-400 transition hover:border-gray-300 hover:text-gray-600"
                                        style="display:none" data-tanggal="{{ $tanggalSel }}">
                                    +{{ $daftarHari->count() - 3 }} lainnya
                                </button>
                            @endif
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ROOT MODAL --}}
    <div id="day-modal-root"></div>
    <div id="detail-modal-root"></div>
@endsection

@push('scripts')
<script>
(function () {
    var COLOR_MAP = @json($colorClasses);
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
    function el(html) {
        var t = document.createElement('template');
        t.innerHTML = html.trim();
        return t.content.firstElementChild;
    }

    function openFromRoot(id, node) {
        var root = document.getElementById(id);
        root.innerHTML = '';
        root.appendChild(node);
    }
    function closeModal(modalEl) { if (modalEl) modalEl.remove(); }

    // ----- filter -----
    var searchEl = document.getElementById('search-appointment');

    function applyFilters() {
        var q = (searchEl ? searchEl.value : '').toLowerCase().trim();
        document.querySelectorAll('.appointment-pill').forEach(function (pill) {
            var show = true;

            var ck = document.querySelector('.js-filter-klien[data-pelanggan-id="' + pill.dataset.pelangganId + '"]');
            if (ck && !ck.checked) show = false;

            var sk = document.querySelector('.js-filter-staf[data-karyawan-id="' + pill.dataset.karyawanId + '"]');
            if (sk && !sk.checked) show = false;

            var ss = document.querySelector('.js-filter-status[data-status="' + pill.dataset.status + '"]');
            if (ss && !ss.checked) show = false;

            if (show && q) show = (pill.dataset.search || '').indexOf(q) !== -1;
            pill.classList.toggle('hidden', !show);
        });
        refreshDayCells();
    }

    function refreshDayCells() {
        document.querySelectorAll('.js-buka-hari').forEach(function (cell) {
            if (!cell.querySelectorAll) return;
            var visible = Array.prototype.slice.call(cell.querySelectorAll('.appointment-pill'))
                .filter(function (p) { return !p.classList.contains('hidden'); });
            visible.forEach(function (p, i) { p.style.display = (i < 3) ? '' : 'none'; });
            var more = cell.querySelector('.more-link');
            if (more) {
                var count = visible.length - 3;
                more.textContent = (count > 0) ? '+ ' + count + ' lainnya' : '';
                more.style.display = (count > 0) ? '' : 'none';
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

    // ----- icons -----
    var IX_CLOSE = '<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    var IX_CLOCK = '<svg class="h-4 w-4 shrink-0 text-accent-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>';
    var IX_USER = '<svg class="h-4 w-4 shrink-0 text-accent-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="9" cy="7" r="3.2"/></svg>';
    var IX_PHONE = '<svg class="h-4 w-4 shrink-0 text-accent-text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7A2 2 0 0 1 22 16.9Z"/></svg>';

    // ----- modal hari -----
    function cardHtml(a) {
        var c = COLOR_MAP[a.warna] || COLOR_MAP.orange;
        var statusBdg = a.status === 'selesai'
            ? '<span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600">Selesai</span>'
            : '<span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-semibold text-green-700">Terkonfirmasi</span>';
        return '<button type="button" class="appointment-card w-full rounded-lg border border-gray-200 border-l-4 ' + c.border + ' bg-white p-3 text-left shadow-sm transition hover:shadow-md">' +
            '<span class="hidden" data-slot="nama">' + esc(a.pelanggan) + '</span>' +
            '<div class="flex items-center gap-3">' +
                '<span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full ' + c.dot + ' text-xs font-bold text-white">' + initials(a.pelanggan) + '</span>' +
                '<div class="min-w-0 flex-1">' +
                    '<p class="truncate text-sm font-semibold text-gray-900">' + esc(a.pelanggan) + '</p>' +
                    '<p class="truncate text-xs text-gray-500">' + esc(a.layanan) + '</p>' +
                '</div>' +
            '</div>' +
            '<div class="mt-2 flex items-center justify-between gap-2">' +
                '<p class="min-w-0 truncate text-xs text-gray-500">Stylist: ' + esc(a.karyawan) + ' &bull; ' + a.durasi_menit + ' mnt</p>' +
                '<div class="flex shrink-0 flex-col items-end gap-1">' +
                    '<span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-700">' + a.jam_mulai + ' WIB</span>' +
                    statusBdg +
                '</div>' +
            '</div>' +
        '</button>';
    }

    function renderDayModal(d) {
        var byHour = {};
        (d.appointments || []).forEach(function (a) {
            var h = parseInt((a.jam_mulai || '09:00').split(':')[0], 10);
            (byHour[h] = byHour[h] || []).push(a);
        });
        var timeline = '';
        for (var h = d.jam_buka; h <= d.jam_tutup; h++) {
            var list = byHour[h] || [];
            var cards = list.map(cardHtml).join('');
            timeline += '<div class="flex gap-3">' +
                '<div class="w-14 shrink-0 pt-1.5 text-right text-xs font-medium text-gray-400">' + pad2(h) + ':00</div>' +
                '<div class="min-w-0 flex-1 space-y-1.5 pb-3">' +
                    (cards || '<p class="rounded-lg border border-dashed border-gray-200 px-3 py-2 text-xs text-gray-300">Kosong</p>') +
                '</div></div>';
        }
        var badge = d.jumlah + ' Reservasi Terjadwal';
        var html = '<div class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:p-6" data-modal="day">' +
            '<div class="fixed inset-0 bg-black/40 backdrop-blur-sm" data-close></div>' +
            '<div class="relative z-10 mt-6 w-full max-w-2xl rounded-2xl bg-white shadow-2xl">' +
                '<div class="flex flex-wrap items-start justify-between gap-3 rounded-t-2xl border-b border-gray-100 bg-white p-5 sm:p-6">' +
                    '<div class="flex items-center gap-4">' +
                        '<div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-xl bg-amber-100">' +
                            '<span class="text-[11px] font-bold uppercase tracking-wide text-amber-800">' + esc(d.hari_pendek) + '</span>' +
                            '<span class="text-2xl font-bold leading-none text-gray-900">' + esc(d.hari) + '</span>' +
                        '</div>' +
                        '<div>' +
                            '<h3 class="text-lg font-bold text-gray-900">' + esc(d.judul) + '</h3>' +
                            '<span class="mt-1 inline-block rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">' + badge + '</span>' +
                            '<p class="mt-1 text-xs text-gray-500">GMT+07 (WIB)</p>' +
                        '</div>' +
                    '</div>' +
                    '<div class="flex items-center gap-2">' +
                        '<a href="' + URL_CREATE + '?tanggal=' + d.tanggal + '" class="inline-flex items-center gap-1 rounded-lg bg-accent px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:brightness-95">' +
                            '<span class="text-base leading-none">+</span> Tambah Appointment</a>' +
                        '<button type="button" data-close class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100">' + IX_CLOSE + '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="max-h-[56vh] space-y-1 overflow-y-auto bg-gray-50 p-5">' + timeline + '</div>' +
                '<div class="rounded-b-2xl border-t border-gray-100 px-5 py-3">' +
                    '<p class="text-xs text-gray-500">Klik salah satu kartu reservasi di atas untuk membuka rincian detail klien dan layanan.</p>' +
                '</div>' +
            '</div></div>';
        openFromRoot('day-modal-root', el(html));
        document.getElementById('detail-modal-root').innerHTML = '';
    }

    function openDayModal(tanggal) {
        fetch(URL_HARI.replace('P_TGL', tanggal))
            .then(function (r) { return r.json(); })
            .then(renderDayModal)
            .catch(function () { alert('Gagal memuat detail hari.'); });
    }

    // ----- modal detail appointment -----
    function infoRow(icon, text) {
        return '<div class="flex items-center gap-3 text-sm text-gray-700">' + icon +
            '<span class="min-w-0">' + text + '</span></div>';
    }

    function renderDetailModal(a) {
        var wa = a.no_wa ? infoRow(IX_PHONE,
            '<a href="https://wa.me/' + phoneDigits(a.no_wa) + '" target="_blank" rel="noopener" class="font-medium text-success underline hover:brightness-90">' + esc(a.no_wa) + '</a>')
            : '';
        var preferensi = a.preferensi
            ? '<div class="rounded-lg bg-amber-50 p-3">' +
                '<p class="text-xs font-semibold text-amber-800">Preferensi</p>' +
                '<p class="mt-0.5 whitespace-pre-wrap text-sm text-gray-700">' + esc(a.preferensi) + '</p>' +
              '</div>'
            : '';
        var layananDetail = (a.layanans && a.layanans.length)
            ? '<div class="rounded-lg bg-amber-50 p-3">' +
                '<p class="text-xs font-semibold text-amber-800">Layanan</p>' +
                a.layanans.map(function (n) { return '<p class="mt-0.5 text-sm text-gray-700">&bull; ' + esc(n) + '</p>'; }).join('') +
              '</div>'
            : '';

        var selesalkan = (a.status !== 'selesai')
            ? '<form method="POST" action="' + URL_SELESAI.replace('P_ID', a.id) + '" class="shrink-0">' +
                '<input type="hidden" name="_token" value="' + CSRF + '">' +
                '<input type="hidden" name="_method" value="PUT">' +
                '<button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-success px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-95">' +
                    '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg> Selesaikan</button>' +
              '</form>'
            : '';

        var html = '<div class="fixed inset-0 z-[60] flex items-center justify-center p-4" data-modal="detail">' +
            '<div class="fixed inset-0 bg-black/50 backdrop-blur-sm" data-close></div>' +
            '<div class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl">' +
                '<div class="flex items-start justify-between gap-3 rounded-t-2xl border-b border-gray-100 p-5">' +
                    '<div class="min-w-0">' +
                        '<h3 class="truncate text-lg font-bold text-gray-900">' + esc(a.pelanggan) + '</h3>' +
                        '<p class="truncate text-sm text-gray-500">' + esc(a.layanan) + '</p>' +
                    '</div>' +
                    '<button type="button" data-close class="shrink-0 rounded-lg p-2 text-gray-400 transition hover:bg-gray-100">' + IX_CLOSE + '</button>' +
                '</div>' +
                '<div class="space-y-3 p-5">' +
                    infoRow(IX_CLOCK, '<span class="font-medium">' + a.jam_mulai + ' WIB</span> &nbsp;(' + a.durasi_menit + ' mnt)') +
                    infoRow(IX_USER, 'Stylist: <span class="font-medium">' + esc(a.karyawan) + '</span>') +
                    wa +
                    layananDetail +
                    preferensi +
                '</div>' +
                '<div class="flex flex-wrap items-center gap-2 rounded-b-2xl border-t border-gray-100 p-5">' +
                    selesalkan +
                    '<a href="' + URL_EDIT.replace('P_ID', a.id) + '" class="shrink-0 rounded-lg bg-white px-3.5 py-2 text-sm font-semibold text-gray-600 ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50">Edit</a>' +
                    '<form method="POST" action="' + URL_HAPUS.replace('P_ID', a.id) + '" class="ml-auto shrink-0" onsubmit="return confirm(\'Hapus appointment ini? Tindakan tidak bisa dibatalkan.\')">' +
                        '<input type="hidden" name="_token" value="' + CSRF + '">' +
                        '<input type="hidden" name="_method" value="DELETE">' +
                        '<button type="submit" class="rounded-lg bg-danger-bg px-3.5 py-2 text-sm font-semibold text-danger transition hover:brightness-95">Hapus</button>' +
                    '</form>' +
                '</div>' +
            '</div></div>';
        openFromRoot('detail-modal-root', el(html));
    }

    function openDetailModal(id) {
        fetch(URL_SHOW.replace('P_ID', id))
            .then(function (r) { return r.json(); })
            .then(renderDetailModal)
            .catch(function () { alert('Gagal memuat detail appointment.'); });
    }

    // ----- event delegation (urut: pill sebelum sel, supaya klik pill tidak membuka modal hari) -----
    document.addEventListener('click', function (e) {
        var closeBtn = e.target.closest('[data-close]');
        if (closeBtn) { closeModal(closeBtn.closest('[data-modal]')); return; }

        var card = e.target.closest('.appointment-card');
        if (card) { openDetailModal(card.dataset.id); return; }

        var pill = e.target.closest('.appointment-pill');
        if (pill) { openDetailModal(pill.dataset.appointmentId); return; }

        var more = e.target.closest('.more-link');
        if (more) { openDayModal(more.dataset.tanggal); return; }

        var hari = e.target.closest('.js-buka-hari');
        if (hari) { openDayModal(hari.dataset.tanggal); return; }
    });

    applyFilters();
})();
</script>
@endpush