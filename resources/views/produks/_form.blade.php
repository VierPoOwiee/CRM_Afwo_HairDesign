@php
    use App\Models\Produk;
    $merekExisting = Produk::whereNotNull('merek')
        ->where('merek', '!=', '')
        ->distinct()
        ->orderBy('merek')
        ->pluck('merek')
        ->toArray();
    $merekDefaults = ['Alfaparf', 'Milbon', 'Keaune', 'Omni', 'Matrix'];
    $merekOptions = array_values(array_unique(array_merge($merekExisting, $merekDefaults)));
    $kategoriVal = old('kategori_produk', $produk->kategori_produk ?? 'Color');
    $satuanVal = old('satuan_penggunaan', $produk->satuan_penggunaan ?? '/10ml');

    $kategoriLayanan = Produk::kategoriLayanan();
    $radioLeft = array_values(array_intersect(['Bleaching', 'Keratin', 'Hairtreatment', 'Serum', 'Keriting'], $kategoriLayanan));
    $radioRight = array_values(array_intersect(['Color', 'Oxidant', 'Smoothing', 'Creambath', 'Repair', 'Masker'], $kategoriLayanan));

    $formAction = isset($produk) && $produk->exists
        ? route('produk.update', $produk)
        : route('produk.store');
    $formMethod = isset($produk) && $produk->exists ? 'PUT' : '';
@endphp

<form id="form-edit-produk" class="edit-produk-stack" action="{{ $formAction }}" method="POST">
    @csrf
    @if ($formMethod !== '')
        @method($formMethod)
    @endif

    <article class="card form-section-card" data-field="product-form-card">

        <!-- Baris 1: Nama Produk & Merek -->
        <div class="form-row-2col">
            <div class="form-group-item">
                <label class="form-label" for="input-product-name">Nama Produk <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="text" id="input-product-name" name="nama_produk" data-field="product-name"
                           class="form-input-control" placeholder="Nama produk..." required
                           value="{{ old('nama_produk', $produk->nama_produk ?? '') }}">
                </div>
                @error('nama_produk')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-item" id="merek_wrapper">
                <label class="form-label" for="select-product-brand">Merek</label>
                <div class="brand-input-row">
                    <div class="form-input-box brand-select-box">
                        <select id="select-product-brand" name="merek" data-field="brand" class="form-select-control">
                            <option value="">-- Pilih Merek --</option>
                            @foreach ($merekOptions as $m)
                                <option value="{{ $m }}" {{ old('merek', $produk->merek ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary" id="btn-add-brand">+ Baru</button>
                </div>
                <span class="field-help-text">Butuh merek baru? Klik "+ Baru" lalu ketik namanya.</span>
                @error('merek')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <input type="hidden" name="mode_merek" id="mode_merek" value="{{ old('mode_merek', 'pilih') }}">

        <div class="brand-input-row hidden" id="merek_baru_row">
            <div class="form-input-box brand-select-box">
                <input type="text" name="merek_baru" maxlength="255" autocomplete="off"
                       placeholder="Ketik merek baru..." value="{{ old('merek_baru') }}"
                       class="form-input-control">
            </div>
            <button type="button" class="btn btn-secondary" id="btn-ganti-merek-mode">Pilih Merek</button>
        </div>

        <!-- Baris 2: Kategori Produk (Radio Grid 2 Kolom) -->
        <div class="form-group-item">
            <label class="form-label">Kategori Produk <span class="required-star">*</span></label>
            <span class="field-help-text" style="margin-top: -2px; margin-bottom: 6px;">Kategori bahan ini dipakai pada layanan tertentu.</span>

            <div class="product-category-radio-grid">
                <div class="radio-col">
                    @foreach ($radioLeft as $val)
                        <label class="category-radio-card {{ $kategoriVal === $val ? 'active' : '' }}">
                            <input type="radio" name="kategori_produk" value="{{ $val }}"
                                   {{ $kategoriVal === $val ? 'checked' : '' }}>
                            <span class="radio-custom-dot"></span>
                            <span class="radio-card-label">{{ $val }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="radio-col">
                    @foreach ($radioRight as $val)
                        <label class="category-radio-card {{ $kategoriVal === $val ? 'active' : '' }}">
                            <input type="radio" name="kategori_produk" value="{{ $val }}"
                                   {{ $kategoriVal === $val ? 'checked' : '' }}>
                            <span class="radio-custom-dot"></span>
                            <span class="radio-card-label">{{ $val }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            @error('kategori_produk')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <!-- Baris 3: Dua Harga (per pcs & per penggunaan) -->
        <div class="form-row-2col">
            <div class="form-group-item">
                <label class="form-label" for="input-price-pcs">Harga Jual per Pcs (Rp) <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="text" inputmode="numeric" id="input-price-pcs" name="harga_per_pcs" data-field="price-pcs"
                           class="form-input-control" placeholder="15.000"
                           value="{{ old('harga_per_pcs', isset($produk) && $produk->exists && (float) $produk->harga_per_pcs > 0 ? number_format((float) $produk->harga_per_pcs, 0, ',', '.') : '') }}">
                </div>
                <span class="field-help-text">Harga saat produk dijual satuan (retail). Isi 0 bila tidak dijual per pcs.</span>
                @error('harga_per_pcs')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-item">
                <label class="form-label" for="input-price-penggunaan">Harga Jual per Penggunaan (Rp) <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="text" inputmode="numeric" id="input-price-penggunaan" name="harga_per_penggunaan" data-field="price-penggunaan"
                           class="form-input-control" placeholder="100.000"
                           value="{{ old('harga_per_penggunaan', isset($produk) && $produk->exists && (float) $produk->harga_per_penggunaan > 0 ? number_format((float) $produk->harga_per_penggunaan, 0, ',', '.') : '') }}">
                </div>
                <span class="field-help-text">Harga bahan saat dipakai pada layanan (mengikuti satuan penggunaan di bawah).</span>
                @error('harga_per_penggunaan')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Baris 4: Satuan Penggunaan & Stok -->
        <div class="form-row-2col">
            <div class="form-group-item">
                <label class="form-label" for="input-satuan-penggunaan">Satuan Penggunaan <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="text" id="input-satuan-penggunaan" name="satuan_penggunaan" data-field="satuan-penggunaan"
                           class="form-input-control" placeholder="/10ml" maxlength="50"
                           value="{{ $satuanVal }}">
                </div>
                <span class="field-help-text">Teks bebas, mis. <code>/10ml</code>, <code>/20ml</code>, <code>per gram</code>. Angka di dalamnya dipakai sebagai pembagi biaya pemakaian.</span>
                @error('satuan_penggunaan')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-item" id="stok_wrapper">
                <label class="form-label" for="input-stok">Stok (pcs) <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="number" id="input-stok" name="stok" value="{{ old('stok', $produk->stok ?? 0) }}"
                           min="0" step="1" class="form-input-control">
                </div>
                <span class="field-help-text">Stok &le; {{ \App\Models\Produk::STOK_MENIPIS }} ditandai menipis.</span>
                @error('stok')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Baris 4b: Netto per Pcs -->
        <div class="form-row-2col">
            <div class="form-group-item">
                <label class="form-label" for="input-netto">Netto per Pcs</label>
                <div class="form-input-box">
                    <input type="text" inputmode="decimal" id="input-netto" name="netto" data-field="netto"
                           class="form-input-control" placeholder="100"
                           value="{{ old('netto', isset($produk) && $produk->exists && (float) $produk->netto > 0 ? (float) $produk->netto : '') }}">
                </div>
                <span class="field-help-text">Isi bersih satu pcs dalam satuan penggunaan (mis. <code>100</code> untuk 100&nbsp;ml).</span>
                @error('netto')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-item">
                <label class="form-label">Perkiraan Pemakaian per Pcs</label>
                <div class="modal-note-box">
                    <span class="modal-note-value" id="netto-preview">&mdash;</span>
                    @if (isset($produk) && $produk->exists && (float) $produk->sisa_penggunaan > 0)
                        <span class="field-help-text">Sisa kemasan terbuka saat ini: <strong>{{ (float) $produk->sisa_penggunaan }}</strong> {{ ltrim($produk->satuanPenggunaanLabel(), '/') }}</span>
                    @else
                        <span class="field-help-text">Dihitung dari netto &divide; angka pada satuan penggunaan.</span>
                    @endif
                </div>
            </div>
        </div>

        @if (isset($produk) && $produk->exists)
            <div class="form-group-item">
                <label class="form-label">Modal rata-rata saat ini</label>
                <div class="modal-note-box">
                    <span class="modal-note-value">{{ $produk->labelModalPerSatuan() }}</span>
                    <span class="field-help-text">Modal tidak diedit manual &mdash; otomatis dihitung ulang setiap kali <a href="{{ route('produk.restock-form', $produk) }}" class="modal-note-link">Restock</a> produk ini.</span>
                </div>
            </div>
        @endif

        <!-- Baris 5: Checkbox Aktif -->
        <div class="form-group-item checkbox-row">
            <label class="checkbox-custom-label">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" id="checkbox-active" name="aktif" value="1"
                       @checked(old('aktif', $produk->aktif ?? true))>
                <span class="checkbox-text">Aktif</span>
            </label>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="form-actions-row">
            <a href="{{ route('produk.index') }}" class="btn btn-secondary" id="btn-cancel-produk" data-field="btn-cancel">Batal</a>
            <button type="submit" class="btn btn-primary" id="btn-save-produk" data-field="btn-save-produk">{{ $submitLabel }}</button>
        </div>

    </article>
</form>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="kategori_produk"]');
        var merekBaruRow = document.getElementById('merek_baru_row');
        var merekSelect = document.getElementById('select-product-brand');
        var btnAddBrand = document.getElementById('btn-add-brand');
        var btnGantiMerek = document.getElementById('btn-ganti-merek-mode');
        var modeMerek = document.getElementById('mode_merek');
        var hargaInputs = [
            document.getElementById('input-price-pcs'),
            document.getElementById('input-price-penggunaan')
        ].filter(Boolean);

        function updateRadioState() {
            radios.forEach(function (radio) {
                var card = radio.closest('.category-radio-card');
                if (!card) return;
                if (radio.checked) card.classList.add('active');
                else card.classList.remove('active');
            });
        }

        radios.forEach(function (r) {
            r.addEventListener('change', updateRadioState);
        });

        function setMerekMode(mode) {
            modeMerek.value = mode;
            if (mode === 'pilih') {
                merekBaruRow.classList.add('hidden');
                merekSelect.classList.remove('hidden');
                btnAddBrand.classList.remove('hidden');
                merekSelect.removeAttribute('disabled');
            } else {
                merekBaruRow.classList.remove('hidden');
                merekSelect.classList.add('hidden');
                btnAddBrand.classList.add('hidden');
                merekSelect.setAttribute('disabled', 'disabled');
                var input = merekBaruRow.querySelector('input[name="merek_baru"]');
                if (input) input.focus();
            }
        }

        btnAddBrand.addEventListener('click', function () { setMerekMode('baru'); });
        btnGantiMerek.addEventListener('click', function () { setMerekMode('pilih'); });

        /* Format harga (dua input) */
        function parseFormatted(str) {
            return parseInt(str.replace(/\./g, '').replace(/,/g, ''), 10) || 0;
        }
        function formatNumber(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        function formatHarga(el) {
            var raw = parseFormatted(el.value);
            el.value = raw ? formatNumber(raw) : '';
        }
        function unformatHarga(el) {
            el.value = el.value.replace(/\./g, '');
        }

        hargaInputs.forEach(function (el) {
            el.addEventListener('focus', function () { unformatHarga(this); });
            el.addEventListener('blur', function () { formatHarga(this); });
        });

        /* Preview jumlah pemakaian = netto / pembagi satuan */
        var nettoInput = document.getElementById('input-netto');
        var satuanInput = document.getElementById('input-satuan-penggunaan');
        var nettoPreview = document.getElementById('netto-preview');

        function divisorFromSatuan(str) {
            var m = String(str || '').replace(',', '.').match(/(\d+(?:\.\d+)?)/);
            var d = m ? parseFloat(m[1]) : 0;
            return d > 0 ? d : 10;
        }

        function updateNettoPreview() {
            if (!nettoPreview) return;
            var netto = parseFloat(String(nettoInput ? nettoInput.value : '').replace(',', '.')) || 0;
            if (netto <= 0) { nettoPreview.textContent = '\u2014'; return; }
            var kali = netto / divisorFromSatuan(satuanInput ? satuanInput.value : '');
            nettoPreview.textContent = (Math.round(kali * 100) / 100) + ' kali pemakaian';
        }

        if (nettoInput) nettoInput.addEventListener('input', updateNettoPreview);
        if (satuanInput) satuanInput.addEventListener('input', updateNettoPreview);
        updateNettoPreview();

        document.querySelector('#form-edit-produk').addEventListener('submit', function () {
            hargaInputs.forEach(function (el) { unformatHarga(el); });
        });

        updateRadioState();

        if (modeMerek.value === 'baru') {
            setMerekMode('baru');
            var inputBaru = merekBaruRow.querySelector('input[name="merek_baru"]');
            if (inputBaru) inputBaru.value = @json(old('merek_baru', ''));
        }

        hargaInputs.forEach(function (el) { if (el.value) formatHarga(el); });
    })();
</script>
