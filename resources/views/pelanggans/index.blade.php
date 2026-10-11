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

            <a href="{{ route('pelanggan.create') }}" class="btn btn-primary page-primary-action" id="btn-add-client">
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
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="var(--text-3)" stroke-width="1.8">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        @if ($q !== '' || $kondisi !== 'all')
                            <p class="empty-state-text">Tidak ada data pelanggan yang cocok dengan pencarian.</p>
                            <a href="{{ route('pelanggan.index') }}" class="btn-client-action btn-client-edit" style="border-radius: var(--radius-md); padding: 8px 16px;">Tampilkan Semua</a>
                        @else
                            <p class="empty-state-text">Belum ada data pelanggan. Tambahkan pelanggan pertama untuk memulai.</p>
                            <a href="{{ route('pelanggan.create') }}" class="btn btn-primary" style="width: auto;">Tambah Pelanggan</a>
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
<th style="text-align: right; width: 130px;">AKSI</th>
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
                                            <a href="{{ route('pelanggan.edit', $p) }}" class="btn-client-action btn-client-edit is-icon-only" title="Edit pelanggan" aria-label="Edit {{ $p->nama }}">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </a>
                                            <a href="{{ route('appointment.create', ['pelanggan' => $p->id]) }}" class="btn-client-action btn-client-schedule is-icon-only" title="Buat janji" aria-label="Buat janji untuk {{ $p->nama }}">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                    <line x1="12" y1="14" x2="12" y2="18"></line>
                                                    <line x1="10" y1="16" x2="14" y2="16"></line>
                                                </svg>
                                            </a>
                                            <form action="{{ route('pelanggan.destroy', $p) }}" method="POST" style="margin: 0; display: inline-flex;"
                                                  onsubmit="return confirm('Hapus pelanggan &quot;{{ addslashes($p->nama) }}&quot;? Data akan dipindahkan ke arsip.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-client-action btn-client-delete is-icon-only" title="Hapus pelanggan" aria-label="Hapus {{ $p->nama }}">
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                        <polyline points="3 6 5 6 21 6"></polyline>
                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                                    </svg>
                                                </button>
                                            </form>
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