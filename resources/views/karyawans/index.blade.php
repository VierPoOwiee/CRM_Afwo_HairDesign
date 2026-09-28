@extends('layouts.app')

@section('title', 'Data Karyawan')
@section('nav-active', 'workers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/worker.css') }}">
@endpush

@section('content')
    <div class="worker-page-header-row">
        <div class="worker-title-group">
            <div style="display: flex; align-items: center; gap: 10px;">
                <h1 class="worker-page-title">Data Karyawan</h1>
                <span class="worker-count-badge">{{ $karyawans->total() }} Karyawan</span>
            </div>
            <p class="worker-page-subtitle">Kelola tim karyawan, kontak telepon, gaji pokok bulanan, dan pengaturan skema komisi.</p>
        </div>

        <a href="{{ route('karyawan.create') }}" class="btn-primary-gold btn-add-worker-gold" id="btn-add-worker">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Karyawan</span>
        </a>
    </div>

    <div class="worker-toolbar-card">
        <div class="worker-tabs-row">
            @php
                $schemes = [
                    'all' => 'Semua Skema',
                    'persen_omset_harian' => 'Persen Omset Harian',
                    'per_layanan' => 'Per Layanan',
                ];
            @endphp
            @foreach ($schemes as $slug => $label)
                @php
                    $tabUrl = route('karyawan.index', array_filter([
                        'q' => $q,
                        'sort' => $sort,
                        'skema' => $slug === 'all' ? null : $slug,
                    ], fn ($v) => $v !== '' && $v !== null));
                @endphp
                <a href="{{ $tabUrl }}" class="worker-filter-tab {{ $skema === $slug ? 'active' : '' }}" data-scheme="{{ $slug }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('karyawan.index') }}" class="worker-controls-right js-toolbar-form">
            <div class="worker-search-box">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" id="worker-search" name="q" value="{{ $q }}" class="search-input" placeholder="Cari nama atau no. telepon...">
            </div>

            <div class="worker-sort-box">
                <span class="sort-prefix-label">Urutkan:</span>
                <select id="worker-sort-select" name="sort" class="sort-select-control js-auto-submit">
                    <option value="name-asc" @selected($sort === 'name-asc')>Nama (A - Z)</option>
                    <option value="name-desc" @selected($sort === 'name-desc')>Nama (Z - A)</option>
                    <option value="salary-desc" @selected($sort === 'salary-desc')>Gaji Tertinggi</option>
                    <option value="salary-asc" @selected($sort === 'salary-asc')>Gaji Terendah</option>
                    <option value="scheme" @selected($sort === 'scheme')>Skema Komisi</option>
                </select>
            </div>

            <div class="mobile-filter-dropdown-wrap">
                <select id="worker-mobile-scheme-select" name="skema" class="sort-select-control js-auto-submit" aria-label="Filter Skema Komisi">
                    <option value="all" @selected($skema === 'all')>Semua Skema</option>
                    <option value="persen_omset_harian" @selected($skema === 'persen_omset_harian')>Persen Omset Harian</option>
                    <option value="per_layanan" @selected($skema === 'per_layanan')>Per Layanan</option>
                </select>
                <select id="worker-mobile-sort-select" name="sort" class="sort-select-control js-auto-submit" aria-label="Urutkan Karyawan">
                    <option value="name-asc" @selected($sort === 'name-asc')>Nama (A - Z)</option>
                    <option value="name-desc" @selected($sort === 'name-desc')>Nama (Z - A)</option>
                    <option value="salary-desc" @selected($sort === 'salary-desc')>Gaji Tertinggi</option>
                    <option value="salary-asc" @selected($sort === 'salary-asc')>Gaji Terendah</option>
                    <option value="scheme" @selected($sort === 'scheme')>Skema Komisi</option>
                </select>
            </div>
        </form>
    </div>

    @if ($karyawans->isEmpty())
        <div class="worker-directory-card">
            <div class="empty-state-card">
                <div class="empty-state-content">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    @if ($q !== '' || $skema !== 'all')
                        <p class="empty-state-text">Tidak ada data karyawan yang cocok dengan pencarian.</p>
                        <a href="{{ route('karyawan.index') }}" class="btn-worker-action btn-worker-edit" style="border-radius: var(--radius-md); padding: 8px 16px;">Tampilkan Semua</a>
                    @else
                        <p class="empty-state-text">Belum ada data karyawan. Tambahkan karyawan pertama untuk memulai.</p>
                        <a href="{{ route('karyawan.create') }}" class="btn-primary-gold btn-add-worker-gold" style="width: auto;">Tambah Karyawan</a>
                    @endif
                </div>
            </div>
        </div>
    @else
        <div class="worker-directory-card">
            <div class="table-responsive-wrapper">
                <table class="worker-detail-table" id="worker-table">
                    <thead>
                        <tr>
                            <th>NAMA LENGKAP KARYAWAN</th>
                            <th class="col-phone">KONTAK (NO. WA)</th>
                            <th class="col-salary">GAJI POKOK (BULAN)</th>
                            <th class="col-scheme">SKEMA KOMISI</th>
                            <th class="col-rate">RATE / KOMISI</th>
                            <th style="text-align: right; width: 80px;">AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="worker-table-body">
                        @foreach ($karyawans as $k)
                            @php
                                $skemaLabel = $k->skema_komisi === 'persen_omset_harian' ? 'Persen Omset Harian' : 'Per Layanan';
                                $rateText = $k->skema_komisi === 'persen_omset_harian'
                                    ? rtrim(rtrim(number_format($k->persen_komisi_harian, 2), '0'), '.') . '% omset harian'
                                    : 'Per layanan';
                            @endphp
                            <tr data-worker-id="{{ $k->id }}" class="js-worker-row"
                                data-name="{{ $k->nama }}"
                                data-phone="{{ $k->kontak ?: '-' }}"
                                data-salary="Rp {{ number_format($k->gaji_pokok, 0, ',', '.') }}"
                                data-scheme="{{ $skemaLabel }}"
                                data-rate="{{ $rateText }}"
                                data-edit-url="{{ route('karyawan.edit', $k) }}">
                                <td>
                                    <span class="worker-name-title">{{ $k->nama }}</span>
                                </td>
                                <td class="col-phone">
                                    <span class="worker-phone-num">{{ $k->kontak ?: '-' }}</span>
                                </td>
                                <td class="col-salary">
                                    {{ $k->gaji_pokok > 0 ? 'Rp '.number_format($k->gaji_pokok, 0, ',', '.') : '-' }}
                                </td>
                                <td class="col-scheme">
                                    @if ($k->skema_komisi === 'persen_omset_harian')
                                        <span class="client-segment-badge badge-vip">{{ $skemaLabel }}</span>
                                    @else
                                        <span class="client-segment-badge" style="background-color: #EFF6FF; color: #1D4ED8;">{{ $skemaLabel }}</span>
                                    @endif
                                </td>
                                <td class="col-rate">
                                    {{ $rateText }}
                                </td>
                                <td style="text-align: right;" onclick="event.stopPropagation();">
                                    <div class="worker-actions-cell">
                                        <a href="{{ route('karyawan.edit', $k) }}" class="btn-worker-action btn-worker-edit">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @include('partials.pagination', ['paginator' => $karyawans])
    @endif

    <div class="worker-modal-overlay" id="worker-detail-modal" role="dialog" aria-hidden="true">
        <div class="worker-modal-card">
            <div class="worker-modal-header">
                <h3 class="worker-modal-title">Detail Karyawan</h3>
                <button type="button" class="btn-close-modal" id="btn-close-worker-modal" aria-label="Tutup Modal">✕</button>
            </div>

            <div class="worker-modal-body">
                <div class="modal-detail-row">
                    <span class="modal-detail-label">Nama Lengkap</span>
                    <span class="modal-detail-val" id="mdl-worker-name">-</span>
                </div>
                <div class="modal-detail-row">
                    <span class="modal-detail-label">No. Telepon / WA</span>
                    <span class="modal-detail-val" id="mdl-worker-phone">-</span>
                </div>
                <div class="modal-detail-row">
                    <span class="modal-detail-label">Gaji Pokok</span>
                    <span class="modal-detail-val" id="mdl-worker-salary">-</span>
                </div>
                <div class="modal-detail-row">
                    <span class="modal-detail-label">Skema Komisi</span>
                    <span class="modal-detail-val" id="mdl-worker-scheme">-</span>
                </div>
                <div class="modal-detail-row">
                    <span class="modal-detail-label">Besaran Komisi</span>
                    <span class="modal-detail-val" id="mdl-worker-rate">-</span>
                </div>
            </div>

            <div class="worker-modal-footer">
                <a href="#" class="btn-modal-edit" id="mdl-btn-worker-edit">Edit Data</a>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/worker.js') }}"></script>
@endpush