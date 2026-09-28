@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('nav-active', 'history')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/riwayat.css') }}">
@endpush

@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $waktu = $transaksi->waktu_kunjungan;
    $waktuLabel = $waktu ? $waktu->format('d').' '.$namaBulan[$waktu->format('n') - 1].' '.$waktu->format('Y').' - '.$waktu->format('H:i').' WIB' : '-';
    $stylists = $transaksi->details->pluck('staf.nama')->filter()->unique()->join(', ') ?: '-';
    $totalDiskon = $transaksi->details->sum(fn ($d) => (float) $d->diskon);
    $subtotalItems = $transaksi->details->sum(fn ($d) => ((float) $d->subtotal) + (float) $d->diskon);
    $detailsWithKomisi = $transaksi->details->filter(fn ($d) => $d->komisi_nominal !== null);
    $isSelesai = $transaksi->status === 'selesai';
@endphp

@section('content')
    <!-- Header Row -->
    <div class="trans-page-header-row">
        <div class="trans-header-title-group" style="flex-direction: row; align-items: center;">
            <a href="{{ route('transaksi.index') }}" class="btn-back-ghost" aria-label="Kembali ke Riwayat">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="trans-page-title">Detail Transaksi</h1>
                <p class="trans-page-subtitle">{{ $transaksi->no_struk }}</p>
            </div>
        </div>

        <div class="page-detail-actions">
            <span class="status-pill {{ $isSelesai ? 'status-paid' : 'status-cancelled' }}">
                {{ $isSelesai ? 'Selesai (Paid)' : 'Dibatalkan' }}
            </span>

            @if ($isSelesai)
                <form action="{{ route('transaksi.cancel', $transaksi) }}" method="POST"
                      onsubmit="return confirm('Batalkan transaksi &quot;{{ addslashes($transaksi->no_struk) }}&quot;? Stok produk akan dikembalikan.')">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn-action-danger">Batalkan Transaksi</button>
                </form>
            @endif

            <form action="{{ route('transaksi.destroy', $transaksi) }}" method="POST"
                  onsubmit="return confirm('Hapus transaksi &quot;{{ addslashes($transaksi->no_struk) }}&quot;? Stok produk akan dikembalikan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-action-danger">Hapus</button>
            </form>

            <button type="button" onclick="window.print()" class="btn-print-receipt">Cetak Struk</button>
        </div>
    </div>

    <div class="detail-section-grid">
        <!-- KIRI: Info + Item -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="detail-card">
                <h2 class="detail-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    Informasi Transaksi
                </h2>
                <div class="detail-def-list">
                    <div class="detail-def">
                        <span class="detail-def-label">Waktu Kunjungan</span>
                        <span class="detail-def-value">{{ $waktuLabel }}</span>
                    </div>
                    <div class="detail-def">
                        <span class="detail-def-label">Metode Pembayaran</span>
                        <span class="detail-def-value">{{ $transaksi->labelMetode() }}</span>
                    </div>
                    <div class="detail-def">
                        <span class="detail-def-label">Pelanggan</span>
                        <span class="detail-def-value">{{ $transaksi->pelanggan->nama ?? '-' }}{{ $transaksi->pelanggan && $transaksi->pelanggan->no_wa ? ' ('.$transaksi->pelanggan->no_wa.')' : '' }}</span>
                    </div>
                    <div class="detail-def">
                        <span class="detail-def-label">Stylist</span>
                        <span class="detail-def-value">{{ $stylists }}</span>
                    </div>
                    <div class="detail-def">
                        <span class="detail-def-label">Jenis Pengerjaan</span>
                        <span class="detail-def-value">{{ $transaksi->jenis_pengerjaan ? ucfirst($transaksi->jenis_pengerjaan) : '-' }}</span>
                    </div>
                    <div class="detail-def">
                        <span class="detail-def-label">No. Transaksi</span>
                        <span class="detail-def-value" style="font-family: monospace;">{{ $transaksi->no_struk }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-card" style="padding: 0; overflow: hidden;">
                <div style="padding: 18px 18px 12px;">
                    <h2 class="detail-card-title" style="margin-bottom: 0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                        Item Transaksi
                    </h2>
                </div>
                <div style="overflow-x: auto;">
                    <table class="detail-items-table">
                        <thead>
                            <tr>
                                <th style="width: 28px;">#</th>
                                <th>Item</th>
                                <th>Staf</th>
                                <th style="text-align: right;">Harga</th>
                                <th style="text-align: right;">Qty</th>
                                <th style="text-align: right;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($transaksi->details as $i => $d)
                                @php
                                    if ($d->tipe_item === 'layanan' && $d->layanan) {
                                        $dNama = $d->layanan->nama_layanan;
                                        $dSub = ($d->varian_dipilih ? 'Varian: '.ucfirst($d->varian_dipilih) : '');
                                        $dSub = trim(($dSub ? $dSub.' · ' : '').($d->ketebalan_rambut ? 'Rambut '.ucfirst($d->ketebalan_rambut) : ''));
                                    } elseif ($d->tipe_item === 'produk' && $d->produk) {
                                        $dNama = $d->produk->nama_produk;
                                        $dSub = 'Produk Retail';
                                    } else {
                                        $dNama = '(Item)';
                                        $dSub = '';
                                    }
                                @endphp
                                <tr>
                                    <td style="color: var(--text-muted);">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="detail-item-name">{{ $dNama }}</div>
                                        @if ($dSub)
                                            <div class="detail-item-sub">{{ $dSub }}</div>
                                        @endif
                                        @if ($d->catatan)
                                            <div class="detail-item-sub" style="font-style: italic;">"{{ $d->catatan }}"</div>
                                        @endif
                                        @forelse ($d->produkPenggunaan as $pu)
                                            <div class="usage-sub-line">
                                                <span>├ Pemakaian: {{ $pu->produk->nama_produk ?? 'Produk' }} ({{ rtrim(rtrim((string) $pu->pemakaian_ml, '0'), '.') }} ml)</span>
                                                <span>{{ $fmt($pu->subtotal) }}</span>
                                            </div>
                                        @empty
                                        @endforelse
                                    </td>
                                    <td style="white-space: nowrap;">{{ $d->staf->nama ?? '-' }}</td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        {{ $fmt($d->harga_saat_transaksi) }}
                                        @if ((float) $d->diskon > 0)
                                            <div style="font-size: 0.72rem; color: #DC2626;">-Diskon {{ $fmt($d->diskon) }}</div>
                                        @endif
                                    </td>
                                    <td style="text-align: right;">{{ rtrim(rtrim((string) $d->qty, '0'), '.') }}</td>
                                    <td style="text-align: right; font-weight: 800; white-space: nowrap;">{{ $fmt($d->subtotal) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">Tidak ada item.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="detail-total-row">
                    <span>Total Bayar</span>
                    <span>{{ $fmt($transaksi->total_bayar) }}</span>
                </div>
            </div>
        </div>

        <!-- KANAN: Harga + Komisi -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="detail-card">
                <h2 class="detail-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);">
                        <circle cx="8" cy="21" r="1"></circle>
                        <circle cx="19" cy="21" r="1"></circle>
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                    </svg>
                    Rincian Harga
                </h2>
                <div style="display: flex; flex-direction: column; gap: 8px; font-size: 0.85rem;">
                    <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
                        <span>Subtotal Item</span>
                        <span style="font-weight: 600; color: var(--text-primary);">{{ $fmt($subtotalItems) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; color: var(--text-secondary);">
                        <span>Total Diskon</span>
                        <span style="font-weight: 600; color: #DC2626;">- {{ $fmt($totalDiskon) }}</span>
                    </div>
                    <hr style="border: none; border-top: 1px dashed var(--border-color); margin: 6px 0;">
                    <div style="display: flex; justify-content: space-between; font-weight: 900; font-size: 0.95rem; color: #B45309;">
                        <span>Total Bayar</span>
                        <span>{{ $fmt($transaksi->total_bayar) }}</span>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <h2 class="detail-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                    Komisi per Staf
                </h2>

                @if ($transaksi->komisiTransaksi->isEmpty())
                    <p style="font-size: 0.84rem; color: var(--text-muted);">Belum ada data komisi.</p>
                @else
                    @foreach ($transaksi->komisiTransaksi as $kt)
                        @php
                            $staf = $kt->staf;
                            $isPersen = $staf && $staf->skema_komisi === 'persen_omset_harian';
                        @endphp
                        <div class="komisi-entry">
                            <div class="komisi-entry-head">
                                <div>
                                    <span class="komisi-entry-name">{{ $staf->nama ?? 'Staf #'.$kt->id_staf }}</span>
                                    <div class="komisi-entry-scheme">
                                        @if ($isPersen)
                                            Skema: {{ $staf->persen_komisi_harian }}% dari omset harian
                                        @else
                                            Skema: per layanan
                                        @endif
                                    </div>
                                </div>
                                <span style="font-weight: 800; color: #B45309;">{{ $fmt($kt->jumlah_komisi) }}</span>
                            </div>
                            <form action="{{ route('transaksi.komisi-staf.update', $transaksi) }}" method="POST" class="komisi-entry-form">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="komisi_staf_id" value="{{ $kt->id }}">
                                <div style="flex: 1; min-width: 0;">
                                    <label class="form-label" style="font-size: 0.7rem;">Jumlah Komisi (Rp)</label>
                                    <input type="text" inputmode="numeric" name="jumlah_komisi" value="{{ $kt->jumlah_komisi }}"
                                           class="native-picker-input" style="width: 100%;">
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <label class="form-label" style="font-size: 0.7rem;">Keterangan</label>
                                    <input type="text" name="keterangan" value="{{ $kt->keterangan }}" placeholder="Opsional"
                                           class="native-picker-input" style="width: 100%;">
                                </div>
                                <button type="submit" class="btn-modal-close-action" style="padding: 7px 14px;">Simpan</button>
                            </form>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="detail-card">
                <h2 class="detail-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted);">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                    </svg>
                    Detail Komisi per Item
                </h2>

                @if ($detailsWithKomisi->isEmpty())
                    <p style="font-size: 0.84rem; color: var(--text-muted);">Belum ada data komisi per item.</p>
                @else
                    @foreach ($detailsWithKomisi->groupBy('id_staf') as $stafId => $details)
                        @php
                            $staf = $details->first()->staf;
                            $dTotal = $details->sum(fn ($x) => (float) $x->komisi_nominal);
                        @endphp
                        <div class="komisi-entry">
                            <div class="komisi-entry-head">
                                <span class="komisi-entry-name">{{ $staf->nama ?? 'Staf #'.$stafId }}</span>
                                <span style="font-weight: 800; color: #B45309;">{{ $fmt($dTotal) }}</span>
                            </div>
                            @foreach ($details as $d)
                                <div class="komisi-item-line">
                                    <span style="color: var(--text-secondary);">
                                        {{ $d->tipe_item === 'layanan' ? ($d->layanan->nama_layanan ?? 'Layanan') : ($d->produk->nama_produk ?? 'Produk') }}
                                        @if ($d->varian_dipilih)
                                            <span style="color: var(--text-muted);">({{ ucfirst($d->varian_dipilih) }})</span>
                                        @endif
                                    </span>
                                    <form action="{{ route('transaksi.komisi.update', $transaksi) }}" method="POST"
                                          style="display: inline-flex; align-items: center; gap: 6px;">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="detail_id" value="{{ $d->id }}">
                                        <input type="text" inputmode="numeric" name="komisi_nominal" value="{{ $d->komisi_nominal }}"
                                               class="native-picker-input" style="width: 96px; text-align: right; padding: 4px 8px; font-size: 0.8rem;">
                                        <button type="submit" style="background: none; border: none; color: #B45309; font-weight: 800; cursor: pointer; font-size: 0.8rem;">OK</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

    <style>
        @media print {
            .page-detail-actions, .btn-back-ghost, form {
                display: none !important;
            }
            body { background: #FFFFFF !important; }
            .detail-card { box-shadow: none; }
        }
    </style>
@endsection