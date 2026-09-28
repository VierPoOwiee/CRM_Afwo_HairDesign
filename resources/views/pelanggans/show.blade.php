@extends('layouts.app')

@section('title', 'Profil ' . $pelanggan->nama)
@section('nav-active', 'customers')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/profil-pelanggan.css') }}">
@endpush

@section('content')
    @php
        $preferensiProduk = collect();
        foreach ($pelanggan->transaksiKunjungan as $trx) {
            foreach ($trx->details as $d) {
                foreach ($d->produkPenggunaan as $pp) {
                    if ($pp->produk) {
                        $preferensiProduk->push($pp->produk->nama_produk);
                    }
                }
            }
        }
        $preferensiProduk = $preferensiProduk->unique()->values();
    @endphp

    <section class="profile-hero">
        <div class="profile-avatar-lg">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
        </div>
        <h1 id="client-name" class="profile-name">{{ $pelanggan->nama }}</h1>
        <p class="profile-meta-line">WhatsApp: {{ $pelanggan->no_wa }}</p>
        <p class="profile-meta-line">Instagram: {{ $pelanggan->username_instagram ? '@' . ltrim($pelanggan->username_instagram, '@') : '-' }}</p>
    </section>

    <div class="profile-actions">
        <a href="{{ route('pelanggan.edit', $pelanggan) }}" class="btn-outline btn-profile-action" data-field="btn-edit-profile">
            <span>EDIT PROFILE</span>
        </a>
        <a href="{{ route('appointment.create', ['pelanggan' => $pelanggan->id]) }}" class="btn-dark btn-profile-action" data-field="btn-book-appointment">
            <span>BOOK APPOINTMENT</span>
        </a>
    </div>

    <div class="profile-cards-grid">

        <article class="card visit-history-card" data-field="visit-history-card">
            <h2 class="visit-history-title">Riwayat Kunjungan</h2>

            <div id="visit-history-list" class="visit-list">
                @forelse ($pelanggan->transaksiKunjungan as $trx)
                    @php
                        $namaItem = $trx->details
                            ->map(fn ($d) => $d->tipe_item === 'produk'
                                ? ($d->produk?->nama_produk ?? 'Produk')
                                : ($d->layanan?->nama_layanan ?? 'Layanan'))
                            ->filter()
                            ->unique()
                            ->take(3)
                            ->implode(' + ');
                    @endphp
                    <a href="{{ route('transaksi.show', $trx) }}" class="visit-item" style="text-decoration: none; color: inherit;">
                        <div class="visit-item-left">
                            <div class="visit-icon-box">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                            </div>
                            <div>
                                <div class="visit-date">{{ strtoupper($trx->waktu_kunjungan->format('d M Y')) }}</div>
                                <div class="visit-service">{{ $namaItem ?: $trx->no_struk }}</div>
                            </div>
                        </div>
                        <div class="visit-amount">Rp {{ number_format($trx->total_bayar, 0, ',', '.') }}</div>
                    </a>
                @empty
                    <p style="font-size: 0.88rem; color: var(--text-muted); text-align: center; padding: 12px 0;">
                        Belum ada riwayat kunjungan untuk pelanggan ini.
                    </p>
                @endforelse
            </div>

            <a href="{{ route('transaksi.index', ['q' => $pelanggan->nama]) }}" class="visit-view-all" data-field="view-all-history">Lihat Semua Riwayat</a>
        </article>

        <article class="card-cream preferences-card" data-field="preferences-card">
            <div class="preferences-header">
                <svg class="preferences-heart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <h2 class="preferences-title">Preferensi Klien</h2>
            </div>

            <div class="preference-field-group">
                <span class="label-caps">TIPE RAMBUT</span>
                <div id="pref-hair-type" class="preference-box">
                    {{ $pelanggan->jenis_rambut ?: '-' }}{{ $pelanggan->kondisi_rambut ? ' — ' . $pelanggan->kondisi_rambut : '' }}
                </div>
            </div>

            <div class="preference-field-group">
                <span class="label-caps">PRODUK YANG DIPAKAI</span>
                <div id="pref-products" class="preference-box">
                    {{ $preferensiProduk->isNotEmpty() ? $preferensiProduk->implode(', ') : 'Belum ada produk yang tercatat.' }}
                </div>
            </div>

            <div class="preference-field-group">
                <span class="label-caps">CATATAN KHUSUS</span>
                <div id="pref-styling" class="preference-box">
                    {{ $pelanggan->catatan_khusus ?: 'Belum ada catatan khusus.' }}
                </div>
            </div>
        </article>

    </div>
@endsection