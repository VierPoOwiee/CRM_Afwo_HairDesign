@php
    $fmt = fn ($n) => 'Rp' . number_format((float) $n, 0, ',', '.');
    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $waktu = $transaksi->waktu_kunjungan;
    $waktuLabel = $waktu ? $waktu->format('d').' '.$namaBulan[(int) $waktu->format('n') - 1].' '.$waktu->format('Y').' - '.$waktu->format('H:i').' WIB' : '-';
    $detailRows = $transaksi->details->where('subtotal', '>', 0);
    $stylists = $detailRows->pluck('staf.nama')->filter()->unique()->join(', ');
    $isBatal = $transaksi->status === 'batal';
@endphp

<div class="struk-receipt">
    <div class="struk-brand">
        <h2 class="struk-brand-name">Afwo. Hair Design</h2>
        <p class="struk-brand-tagline">Official Salon Payment Receipt</p>
        @if ($isBatal)
            <span class="status-pill status-cancelled struk-cancel-badge">Dibatalkan</span>
        @endif
    </div>

    <dl class="struk-info-list">
        <div class="struk-info-row">
            <dt>No. Transaksi:</dt>
            <dd class="struk-mono" title="{{ $transaksi->no_struk }}">{{ $transaksi->no_struk }}</dd>
        </div>
        <div class="struk-info-row">
            <dt>Waktu:</dt>
            <dd>{{ $waktuLabel }}</dd>
        </div>
        <div class="struk-info-row">
            <dt>Pelanggan:</dt>
            <dd>{{ $transaksi->pelanggan->nama ?? '-' }}{{ $transaksi->pelanggan && $transaksi->pelanggan->no_wa ? ' ('.$transaksi->pelanggan->no_wa.')' : '' }}</dd>
        </div>
        @if ($stylists !== '')
            <div class="struk-info-row">
                <dt>Stylist:</dt>
                <dd>{{ $stylists }}</dd>
            </div>
        @endif
    </dl>

    <div class="struk-items">
        @forelse ($detailRows as $d)
            @php
                if ($d->tipe_item === 'layanan' && $d->layanan) {
                    $dNama = $d->layanan->nama_layanan.($d->varian_dipilih ? ' ('.ucfirst($d->varian_dipilih).')' : '');
                } elseif ($d->tipe_item === 'produk' && $d->produk) {
                    $dNama = $d->produk->nama_produk;
                } else {
                    $dNama = '(Item)';
                }
            @endphp
            <div class="struk-item-row">
                <span>{{ $dNama }}@if ((float) $d->qty != 1) × {{ rtrim(rtrim((string) $d->qty, '0'), '.') }}@endif</span>
                <span>{{ $fmt($d->subtotal) }}</span>
            </div>
            @foreach ($d->produkPenggunaan as $pu)
                <div class="struk-usage-row">
                    <span>├ Pemakaian: {{ $pu->produk->nama_produk ?? 'Produk' }} ({{ rtrim(rtrim((string) $pu->pemakaian_ml, '0'), '.') }} ml)</span>
                    <span>-</span>
                </div>
            @endforeach
        @empty
            <div class="struk-empty">Tidak ada item.</div>
        @endforelse
    </div>

    <div class="struk-total-row">
        <span>TOTAL:</span>
        <span>{{ $fmt($transaksi->total_bayar) }}</span>
    </div>
    <div class="struk-method-row">
        <span>Metode Pembayaran:</span>
        <span>{{ $transaksi->labelMetode() }}</span>
    </div>
</div>
