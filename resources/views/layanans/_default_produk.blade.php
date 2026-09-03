@php
    $hasDefaults = count($defaultRows) > 0;
@endphp

<div class="sm:col-span-6">
    <button type="button"
            class="js-toggle-default mt-2 flex w-full items-center justify-between gap-3 rounded-lg border border-dashed border-gray-300 bg-card px-3 py-2 transition-colors hover:border-accent/60 hover:bg-accent-light">
        <span class="inline-flex items-center gap-2 text-xs font-semibold text-gray-700">
            <svg class="js-toggle-icon {{ $hasDefaults ? 'rotate-45' : '' }} h-4 w-4 shrink-0 text-accent-text transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Default Produk Varian
        </span>
        <span class="hidden text-[11px] text-gray-400 sm:block">
            opsional &mdash; baris produk otomatis terisi di form transaksi
        </span>
    </button>

    <div class="js-default-panel {{ $hasDefaults ? '' : 'hidden' }} mt-2 rounded-lg border border-gray-200 bg-card p-4 shadow-sm" data-ri="{{ $pRowIndex }}">
        <p class="text-sm font-medium text-gray-800">Default Produk Pilihan</p>
        <p class="mt-1 text-xs leading-relaxed text-gray-500">
            Saat varian ini dipilih di form transaksi, satu baris produk otomatis terisi per kategori dengan pemakaian default (ml) yang masih bisa diubah.
            Seluruh pemakaian aktual ditagih per 10 ml &mdash; harga dasar varian TIDAK ditambahkan.
        </p>
        <div class="js-default-rows mt-3 space-y-2">
            @foreach ($defaultRows as $d)
                <div class="js-default-row flex flex-wrap items-center gap-2">
                    <select class="js-default-kategori w-full rounded-md border-gray-300 px-2 py-1.5 text-sm focus:border-accent focus:ring-accent/30 sm:w-48">
                        <option value="">-- Kategori --</option>
                        @foreach ($kategoriList as $k)
                            <option value="{{ $k }}" {{ ($d['kategori_produk'] ?? '') === $k ? 'selected' : '' }}>{{ $k }}</option>
                        @endforeach
                    </select>
                    <div class="flex items-center gap-1.5">
                        <input type="number" min="0" step="any" inputmode="decimal"
                               value="{{ $fmtMl($d['default_ml'] ?? null) }}"
                               placeholder="0"
                               class="js-default-ml w-28 rounded-md border-gray-300 px-2 py-1.5 text-right text-sm focus:border-accent focus:ring-accent/30">
                        <span class="w-4 text-[10px] font-medium text-gray-400">ml</span>
                    </div>
                    <button type="button" class="js-remove-default-row text-xs font-medium text-red-600 hover:text-red-800">Hapus</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="js-add-default-row mt-3 text-xs font-medium text-accent-text hover:text-accent">+ Tambah Kategori</button>
        <p class="mt-2 text-[11px] leading-relaxed text-gray-400">Tambahkan semua kategori produk yang terpakai pada varian ini (contoh: Color &amp; Bleaching untuk warna). Kategori tanpa baris di sini tidak otomatis terisi.</p>
    </div>
</div>