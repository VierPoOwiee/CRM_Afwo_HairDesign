@php
    $hasDefaults = count($defaultRows) > 0;
@endphp

<div class="default-produk-cell">
    <!-- Sub-button Default Produk Varian -->
    <div class="default-product-variant-box js-toggle-default" id="btn-default-product-variant" role="button" tabindex="0">
        <div class="dpv-left">
            <span class="dpv-plus js-toggle-icon">+</span>
            <span class="dpv-text">Default Produk Varian</span>
        </div>
        <span class="dpv-subtext">opsional &mdash; baris produk otomatis terisi di form transaksi</span>
    </div>

    <div class="js-default-panel {{ $hasDefaults ? '' : 'hidden' }}" data-ri="{{ $pRowIndex }}">
        <p class="dp-panel-title">Default Produk Pilihan</p>
        <p class="variants-help-text">
            Saat varian ini dipilih di form transaksi, satu baris produk otomatis terisi per kategori dengan pemakaian default (ml) yang masih bisa diubah.
            Seluruh pemakaian aktual ditagih per 10 ml &mdash; harga dasar varian TIDAK ditambahkan.
        </p>
        <div class="js-default-rows dp-rows">
            @foreach ($defaultRows as $d)
                <div class="js-default-row dp-row">
                    <div class="variant-input-box dp-input-box">
                        <select class="js-default-kategori variant-input-field">
                            <option value="">-- Kategori --</option>
                            @foreach ($kategoriList as $k)
                                <option value="{{ $k }}" {{ ($d['kategori_produk'] ?? '') === $k ? 'selected' : '' }}>{{ $k }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="variant-input-box dp-input-box">
                        <input type="number" min="0" step="any" inputmode="decimal"
                               value="{{ $fmtMl($d['default_ml'] ?? null) }}"
                               placeholder="0"
                               class="js-default-ml variant-input-field dp-ml-field">
                    </div>
                    <span class="dp-ml-unit">ml</span>
                    <button type="button" class="js-remove-default-row btn-delete-variant">Hapus</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn-add-variant-row js-add-default-row dp-add-row">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Tambah Kategori</span>
        </button>
        <p class="variants-help-text">Tambahkan semua kategori produk yang terpakai pada varian ini (contoh: Color &amp; Bleaching untuk warna). Kategori tanpa baris di sini tidak otomatis terisi.</p>
    </div>
</div>