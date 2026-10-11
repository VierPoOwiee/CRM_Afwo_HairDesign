@extends('layouts.app')

@section('title', 'Layanan')

@section('nav-active', 'services')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}">
@endpush

@section('content')
    <!-- Section Intro & Add Button -->
    <div class="services-intro-section">
        <h1 class="services-title">Service Management</h1>
        <p class="services-subtitle">Organize and update your premium salon offerings.</p>
    </div>

    <a href="{{ route('layanan.create') }}" class="btn btn-primary page-primary-action" id="add-service-btn" data-field="add-service-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Tambah Layanan</span>
    </a>

    <!-- Pill Filter Tabs -->
    <div class="pill-filter-container" id="service-category-filter">
        @php
            $pillTabs = [
                'all' => 'Semua',
                'potong' => 'Potong',
                'styling' => 'Styling',
                'warna' => 'Warna &amp; Highlights',
                'spa' => 'Spa',
            ];
            $pillValues = ['all', 'potong', 'styling', 'warna', 'spa'];
        @endphp

        @foreach ($pillValues as $slug)
            @php
                $url = route('layanan.index', array_merge(request()->except('kategori', 'page'), $slug === 'all' ? [] : ['kategori' => $slug]));
            @endphp
            <a href="{{ $url }}"
               class="pill-tab-btn {{ $kategoriSlug === $slug ? 'active' : '' }}"
               data-filter="{{ $slug }}"
               aria-pressed="{{ $kategoriSlug === $slug ? 'true' : 'false' }}">
                {!! $pillTabs[$slug] !!}
            </a>
        @endforeach
    </div>

    <!-- Services List Grid -->
    <div class="services-list-grid" id="service-list" data-field="service-list">
        @forelse ($layanans as $l)
            @php
                $catKey = strtolower(preg_replace('/\s+/', '-', $l->kategori));
                if ($catKey === 'warna-rambut') {
                    $catKey = 'warna';
                } elseif ($catKey === 'treatment' || $catKey === 'treatment-rambut') {
                    $catKey = 'spa';
                }
                if (! in_array($catKey, ['potong', 'styling', 'warna', 'spa'])) {
                    $catKey = 'potong';
                }
                $tagTokens = [
                    'potong' => 'HAIR',
                    'styling' => 'STYLING',
                    'warna' => 'COLOR',
                    'spa' => 'SPA',
                ];
                $hargaList = $l->hargaLayanan;
                $firstHarga = $hargaList->first();
                $varianNames = $hargaList->where('varian', '!=', '')->pluck('varian')->take(2)->map(function ($v) {
                    return ucfirst(trim((string) $v));
                });
            @endphp

            <article class="service-card" data-category="{{ $catKey }}">
                <div class="service-card-top">
                    <span class="category-tag {{ $catKey }}">{{ $tagTokens[$catKey] }}</span>
<<<<<<< HEAD
                    <div class="card-actions-group">
                        <a href="{{ route('layanan.edit', $l) }}" class="btn-edit-icon" aria-label="Edit {{ $l->nama_layanan }}">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </a>
                        <form action="{{ route('layanan.destroy', $l) }}" method="POST" style="margin: 0; display: inline-flex;"
                              onsubmit="return confirm('Hapus layanan &quot;{{ addslashes($l->nama_layanan) }}&quot; beserta semua varian harganya?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-delete-icon" aria-label="Hapus {{ $l->nama_layanan }}">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </button>
                        </form>
                    </div>
=======
                    <a href="{{ route('layanan.edit', $l) }}" class="btn-edit-icon" aria-label="Edit {{ $l->nama_layanan }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
>>>>>>> c46369f0ea4073068b460ba005de65051d14d1ec
                </div>
                <h2 class="service-card-name">{{ $l->nama_layanan }}</h2>
                <p class="service-card-desc">
                    @if ($l->aktif)
                        <span class="service-status-pill status-active">Aktif</span>
                    @else
                        <span class="service-status-pill status-inactive">Nonaktif</span>
                    @endif
                    {{ $varianNames->isEmpty() ? ($hargaList->isEmpty() ? 'Belum ada varian harga.' : $hargaList->count().' varian harga.') : 'Varian: '.$varianNames->join(', ').($hargaList->count() > 2 ? ', ...' : '') }}
                </p>
                <div class="service-card-bottom">
                    <div class="service-duration-box">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span>{{ $hargaList->count() }} Varian</span>
                    </div>
                    <span class="service-price-val">{{ $firstHarga ? $firstHarga->labelHargaDasar() : '-' }}</span>
                </div>
            </article>
        @empty
            <div id="services-empty-state" class="empty-filter-state show">
                @if ($q !== '')
                    <p>Tidak ditemukan layanan untuk kata kunci "<strong>{{ $q }}</strong>".</p>
                @elseif ($kategoriSlug !== 'all')
                    <p>Tidak ada layanan pada kategori ini.</p>
                @else
                    <p>Belum ada layanan. Klik "Tambah Layanan" untuk menambahkan layanan pertama.</p>
                @endif
            </div>
        @endforelse
    </div>

    @if ($layanans->hasPages())
        @include('partials.pagination', ['paginator' => $layanans])
    @endif
@endsection