@extends('layouts.app')

@section('title', 'Produk')

@section('nav-active', 'products')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/services.css') }}">
    <link rel="stylesheet" href="{{ asset('css/produk.css') }}">
@endpush

@section('content')
    <!-- Section Intro & Add Button -->
    <div class="products-intro-section">
        <h1 class="products-title">Product Management</h1>
        <p class="products-subtitle">Kelola stok produk retail dan produk pendukung layanan.</p>
    </div>

    <a href="{{ route('produk.create') }}" class="btn-add-full" id="add-product-btn" data-field="add-product-btn">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        <span>Add New Product</span>
    </a>

    <!-- Pill Filter Tabs -->
    <div class="pill-filter-container" id="product-category-filter">
        @php
            $modeTabs = ['all' => 'Semua', 'pcs' => 'Dijual Per Pcs', 'layanan' => 'Produk Layanan'];
        @endphp
        @foreach ($modeTabs as $slug => $label)
            @php
                $url = route('produk.index', array_merge(request()->except(['mode', 'kategori', 'page']), $slug === 'all' ? [] : ['mode' => $slug]));
            @endphp
            <a href="{{ $url }}"
               class="pill-tab-btn {{ $mode === $slug ? 'active' : '' }}"
               data-filter="{{ $slug }}"
               aria-pressed="{{ $mode === $slug ? 'true' : 'false' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($mode === 'all' && $stokMenipis > 0)
        <div class="stock-low-banner">
            <span class="stock-low-dot"></span>
            <span><strong>{{ $stokMenipis }} produk dijual per pcs</strong> punya stok menipis (stok &le; {{ \App\Models\Produk::STOK_MENIPIS }}).</span>
        </div>
    @endif

    {{-- Filter kategori khusus mode layanan --}}
    @if ($mode === 'layanan')
        <div class="pill-filter-container" id="product-subcategory-filter">
            @php
                $subUrl = route('produk.index', array_merge(request()->except(['kategori', 'page']), ['mode' => 'layanan']));
            @endphp
            <a href="{{ $subUrl }}"
               class="pill-tab-btn {{ $kategoriFilter === '' ? 'active' : '' }}"
               data-filter="all">Semua Kategori</a>
            @foreach ($kategoriList as $key => $label)
                <a href="{{ route('produk.index', array_merge(request()->except(['kategori', 'page']), ['mode' => 'layanan', 'kategori' => $key])) }}"
                   class="pill-tab-btn {{ $kategoriFilter === $key ? 'active' : '' }}"
                   data-filter="{{ $key }}">{{ $label }}</a>
            @endforeach
        </div>
    @endif

    <!-- Products List Grid -->
    <div class="products-list-grid" id="product-list" data-field="product-list">
        @forelse ($produks as $p)
            @php
                $isRetail = $p->kategori_produk === 'dijual';
                $tagKey = $isRetail ? 'retail' : 'produk-layanan';
                $tagText = $isRetail ? 'RETAIL' : 'PRODUK LAYANAN';
            @endphp

            <article class="product-card" data-category="{{ $tagKey }}">
                <div class="product-card-top">
                    <span class="product-tag {{ $tagKey }}">{{ $tagText }}</span>
                    <a href="{{ route('produk.edit', $p) }}" class="btn-edit-icon" aria-label="Edit {{ $p->nama_produk }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </a>
                </div>
                <h2 class="product-card-name">{{ $p->nama_produk }}</h2>
                <p class="product-card-desc">
                    @if ($isRetail)
                        @if ($p->aktif)
                            <span class="product-status-pill status-active">Aktif</span>
                        @else
                            <span class="product-status-pill status-inactive">Nonaktif</span>
                        @endif
                        {{ $p->merek ? 'Merek '.$p->merek : 'Produk retail' }} &mdash; dijual per satuan ke pelanggan.
                    @else
                        @if ($p->aktif)
                            <span class="product-status-pill status-active">Aktif</span>
                        @else
                            <span class="product-status-pill status-inactive">Nonaktif</span>
                        @endif
                        {{ $p->merek ? 'Merek '.$p->merek.' &mdash; ' : '' }}kategori {{ $p->labelKategori() }} ({{ $p->satuan }}).
                    @endif
                </p>
                <div class="product-card-bottom">
                    <div class="product-info-box">
                        @if ($isRetail)
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <span>Stok: {{ $p->stok }} pcs
                                @if ($p->aktif && $p->stokMenipis())
                                    <span class="product-stock-low">Menipis</span>
                                @endif
                            </span>
                        @else
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path>
                            </svg>
                            <span>Satuan: {{ $p->satuan }}</span>
                        @endif
                    </div>
                    <span class="product-price-val">{{ $p->labelHarga() }}</span>
                </div>
            </article>
        @empty
            <div id="products-empty-state" class="empty-filter-state show">
                @if ($q !== '')
                    <p>Tidak ditemukan produk untuk kata kunci "<span class="font-medium">{{ $q }}</span>".</p>
                @elseif ($kategoriFilter !== '')
                    <p>Tidak ada produk pada kategori ini.</p>
                @else
                    <p>Belum ada produk. Klik "Add New Product" untuk menambahkan produk pertama.</p>
                @endif
            </div>
        @endforelse
    </div>

    @if ($produks->hasPages())
        <div class="mt-4 mb-6">
            @include('partials.pagination', ['paginator' => $produks])
        </div>
    @endif
@endsection