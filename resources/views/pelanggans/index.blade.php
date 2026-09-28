@extends('layouts.app')

@section('title', 'Data Pelanggan')
@section('nav-active', 'customers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/clients.css') }}">
@endpush

@section('content')
    <div class="clients-page-stack">

        <div class="clients-header-row">
            <div class="clients-title-group">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h1 class="clients-title">Data Pelanggan</h1>
                    <span class="clients-count-badge">{{ $pelanggans->total() }} Pelanggan</span>
                </div>
                <p class="clients-subtitle">Database riwayat profil, jenis &amp; kondisi rambut, preferensi formula, dan alamat pelanggan.</p>
            </div>

            <a href="{{ route('pelanggan.create') }}" class="btn-primary-gold btn-add-client-gold" id="btn-add-client">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Pelanggan</span>
            </a>
        </div>

        <div class="clients-toolbar-card">
            <div class="clients-tabs-row">
                @php
                    $tabs = [
                        'all' => 'Semua Pelanggan',
                        'sehat' => 'Rambut Sehat',
                        'agak-rusak' => 'Agak Rusak',
                        'rusak' => 'Rusak',
                    ];
                @endphp
                @foreach ($tabs as $slug => $label)
                    @php
                        $tabUrl = route('pelanggan.index', array_filter([
                            'q' => $q,
                            'sort' => $sort,
                            'kondisi' => $slug === 'all' ? null : $slug,
                        ], fn ($v) => $v !== '' && $v !== null));
                    @endphp
                    <a href="{{ $tabUrl }}" class="client-filter-tab {{ $kondisi === $slug ? 'active' : '' }}" data-filter="{{ $slug }}">{{ $label }}</a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('pelanggan.index') }}" class="clients-controls-right js-toolbar-form">
                <div class="client-search-box">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" id="client-search" name="q" value="{{ $q }}" class="search-input" placeholder="Cari nama, WhatsApp, IG, alamat...">
                </div>

                <div class="client-sort-box">
                    <span class="sort-prefix-label">Urutkan:</span>
                    <select id="client-sort-select" name="sort" class="sort-select-control js-auto-submit">
                        <option value="name-asc" @selected($sort === 'name-asc')>Nama (A - Z)</option>
                        <option value="name-desc" @selected($sort === 'name-desc')>Nama (Z - A)</option>
                        <option value="hair-type" @selected($sort === 'hair-type')>Jenis Rambut</option>
                        <option value="condition" @selected($sort === 'condition')>Kondisi Rambut</option>
                    </select>
                </div>

                <div class="mobile-filter-dropdown-wrap">
                    <select id="client-mobile-condition-select" name="kondisi" class="sort-select-control js-auto-submit" aria-label="Filter Kondisi Rambut">
                        <option value="all" @selected($kondisi === 'all')>Semua Kondisi</option>
                        <option value="sehat" @selected($kondisi === 'sehat')>Rambut Sehat</option>
                        <option value="agak-rusak" @selected($kondisi === 'agak-rusak')>Agak Rusak</option>
                        <option value="rusak" @selected($kondisi === 'rusak')>Rusak</option>
                    </select>
                    <select id="client-mobile-sort-select" name="sort" class="sort-select-control js-auto-submit" aria-label="Urutkan Pelanggan">
                        <option value="name-asc" @selected($sort === 'name-asc')>Nama (A - Z)</option>
                        <option value="name-desc" @selected($sort === 'name-desc')>Nama (Z - A)</option>
                        <option value="hair-type" @selected($sort === 'hair-type')>Jenis Rambut</option>
                        <option value="condition" @selected($sort === 'condition')>Kondisi Rambut</option>
                    </select>
                </div>
            </form>
        </div>

        @if ($pelanggans->isEmpty())
            <div class="clients-directory-card">
                <div class="empty-state-card">
                    <div class="empty-state-content">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9CA3AF" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        @if ($q !== '' || $kondisi !== 'all')
                            <p class="empty-state-text">Tidak ada data pelanggan yang cocok dengan pencarian.</p>
                            <a href="{{ route('pelanggan.index') }}" class="btn-client-action btn-client-edit" style="border-radius: var(--radius-md); padding: 8px 16px;">Tampilkan Semua</a>
                        @else
                            <p class="empty-state-text">Belum ada data pelanggan. Tambahkan pelanggan pertama untuk memulai.</p>
                            <a href="{{ route('pelanggan.create') }}" class="btn-primary-gold btn-add-client-gold" style="width: auto;">Tambah Pelanggan</a>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="clients-directory-card">
                <div class="table-responsive-wrapper">
                    <table class="clients-detail-table" id="clients-table">
                        <thead>
                            <tr>
                                <th>NAMA PELANGGAN</th>
                                <th class="col-phone">NO. WHATSAPP</th>
                                <th class="col-gender">JENIS KELAMIN</th>
                                <th class="col-hair-type">JENIS RAMBUT</th>
                                <th class="col-condition">KONDISI RAMBUT</th>
                                <th class="col-notes">CATATAN</th>
                                <th style="text-align: right; width: 140px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="client-table-body">
                            @foreach ($pelanggans as $p)
                                @php
                                    $gender = $p->jenis_kelamin === 'L' ? 'Laki-laki' : ($p->jenis_kelamin === 'P' ? 'Perempuan' : '-');
                                    $kondisiBadge = match ($p->kondisi_rambut) {
                                        'Rusak' => 'condition-rusak',
                                        'Agak Rusak' => 'condition-agak-rusak',
                                        default => 'condition-sehat',
                                    };
                                    $kondisiDisplay = $p->kondisi_rambut ?? 'Sehat';
                                @endphp
                                <tr data-client-id="{{ $p->id }}" class="js-client-row"
                                    data-name="{{ $p->nama }}"
                                    data-phone="{{ $p->no_wa }}"
                                    data-instagram="{{ $p->username_instagram ?: '-' }}"
                                    data-gender="{{ $gender }}"
                                    data-hair-type="{{ $p->jenis_rambut ?: '-' }}"
                                    data-hair-condition="{{ $kondisiDisplay }}"
                                    data-domicile="{{ $p->alamat ?: '-' }}"
                                    data-notes="{{ $p->catatan_khusus ?: 'Tidak ada catatan khusus.' }}"
                                    data-edit-url="{{ route('pelanggan.edit', $p) }}"
                                    data-appointment-url="{{ route('appointment.create', ['pelanggan' => $p->id]) }}">
                                    <td>
                                        <a href="{{ route('pelanggan.show', $p) }}" class="client-name-title">{{ $p->nama }}</a>
                                    </td>
                                    <td class="col-phone">
                                        <span class="client-phone-num">{{ $p->no_wa }}</span>
                                    </td>
                                    <td class="col-gender">
                                        <span class="gender-tag">{{ $gender }}</span>
                                    </td>
                                    <td class="col-hair-type">
                                        <span class="hair-type-badge">{{ $p->jenis_rambut ?: '-' }}</span>
                                    </td>
                                    <td class="col-condition">
                                        <span class="condition-badge {{ $kondisiBadge }}">{{ $kondisiDisplay }}</span>
                                    </td>
                                    <td class="col-notes">
                                        <span class="client-notes-ellipsis" title="{{ $p->catatan_khusus ?: '-' }}">{{ $p->catatan_khusus ?: '-' }}</span>
                                    </td>
                                    <td style="text-align: right;" onclick="event.stopPropagation();">
                                        <div class="client-actions-cell">
                                            <a href="{{ route('pelanggan.edit', $p) }}" class="btn-client-action btn-client-edit">Edit</a>
                                            <a href="{{ route('appointment.create', ['pelanggan' => $p->id]) }}" class="btn-client-action btn-client-schedule">+ Janji</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @include('partials.pagination', ['paginator' => $pelanggans])
        @endif
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/clients.js') }}"></script>
@endpush