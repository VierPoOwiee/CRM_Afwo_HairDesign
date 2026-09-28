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
    $kategoriVal = old('kategori_produk', $produk->kategori_produk ?? 'dipakai_layanan');
    $satuanVal = old('satuan', $produk->satuan ?? '');

    $kategoriLayanan = Produk::kategoriLayanan();
    $radioLeft = array_merge(['dijual'], array_values(array_intersect(['Bleaching', 'Keratin', 'Hairtreatment', 'Serum', 'Keriting'], $kategoriLayanan)), ['dipakai_layanan']);
    $radioRight = array_values(array_intersect(['Color', 'Oxidant', 'Smoothing', 'Creambath', 'Repair', 'Masker'], $kategoriLayanan));

    $radioHelp = [
        'dijual' => ['/pcs', 'Harga jual per 1 pcs produk.'],
        'dipakai_layanan' => ['/10ml', 'Harga jual per 10ml bahan yang digunakan saat layanan. Modal dihitung otomatis dari riwayat restock.'],
    ];

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
                <label class="form-label" for="select-product-brand">Merek <span class="required-star">*</span></label>
                <div class="brand-input-row">
                    <div class="form-input-box brand-select-box">
                        <select id="select-product-brand" name="merek" data-field="brand" class="form-select-control">
                            <option value="">-- Pilih Merek --</option>
                            @foreach ($merekOptions as $m)
                                <option value="{{ $m }}" {{ old('merek', $produk->merek ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="btn-add-brand-gold" id="btn-add-brand">+ Baru</button>
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
            <button type="button" class="btn-add-brand-gold" id="btn-ganti-merek-mode">Pilih Merek</button>
        </div>

        <!-- Baris 2: Kategori Produk (Radio Grid 2 Kolom) -->
        <div class="form-group-item">
            <label class="form-label">Kategori Produk <span class="required-star">*</span></label>
            <span class="field-help-text" style="margin-top: -2px; margin-bottom: 6px;">Dijual per pcs ke pelanggan, atau dipakai staf saat treatment.</span>

            <div class="product-category-radio-grid">
                <div class="radio-col">
                    @foreach ($radioLeft as $val)
                        @php
                            [$unit, $help] = $radioHelp[$val] ?? ['/10ml', 'Harga jual per 10ml bahan yang digunakan saat layanan. Modal dihitung otomatis dari riwayat restock.'];
                        @endphp
                        <label class="category-radio-card {{ $kategoriVal === $val ? 'active' : '' }}">
                            <input type="radio" name="kategori_produk" value="{{ $val }}"
                                   data-unit="{{ $unit }}" data-help="{{ $help }}"
                                   {{ $kategoriVal === $val ? 'checked' : '' }}>
                            <span class="radio-custom-dot"></span>
                            <span class="radio-card-label">{{ $val === 'dijual' ? 'Dijual Per PCS' : ($val === 'dipakai_layanan' ? 'Dipakai Layanan (umum)' : $val) }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="radio-col">
                    @foreach ($radioRight as $val)
                        @php
                            [$unit, $help] = $radioHelp[$val] ?? ['/10ml', 'Harga jual per 10ml bahan yang digunakan saat layanan. Modal dihitung otomatis dari riwayat restock.'];
                        @endphp
                        <label class="category-radio-card {{ $kategoriVal === $val ? 'active' : '' }}">
                            <input type="radio" name="kategori_produk" value="{{ $val }}"
                                   data-unit="{{ $unit }}" data-help="{{ $help }}"
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

        <!-- Baris 3: Satuan & Harga per Satuan (Rp) -->
        <div class="form-row-2col">
            <div class="form-group-item">
                <label class="form-label" for="input-unit">Satuan <span class="required-star">*</span></label>
                <div class="form-input-box disabled-like">
                    <input type="text" id="input-unit" class="form-input-control" data-field="unit"
                           value="{{ $satuanVal }}" placeholder="/10ml" readonly>
                </div>
                <input type="hidden" name="satuan" id="satuan_hidden" value="{{ $satuanVal }}">
                <span class="field-help-text" id="unit-help-text">Satuan otomatis mengikuti kategori.</span>
                @error('satuan')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-item">
                <label class="form-label" for="input-price"><span id="harga-label-text">Harga per Satuan</span> (Rp) <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="text" inputmode="numeric" id="input-price" name="harga_per_satuan" data-field="price"
                           class="form-input-control" placeholder="15.000" required
                           value="{{ old('harga_per_satuan', $produk->harga_per_satuan ?? '') }}">
                </div>
                <span class="field-help-text" id="price-help-text">Harga jual per satuan produk.</span>
                @error('harga_per_satuan')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Stok (khusus dijual per pcs) -->
        <div class="form-group-item" id="stok_wrapper" style="display: none;">
            <label class="form-label" for="input-stok">Stok <span class="required-star">*</span></label>
            <div class="form-input-box">
                <input type="number" id="input-stok" name="stok" value="{{ old('stok', $produk->stok ?? 0) }}"
                       min="0" step="1" class="form-input-control">
            </div>
            <span class="field-help-text">Stok &le; {{ \App\Models\Produk::STOK_MENIPIS }} ditandai menipis.</span>
            @error('stok')
                <span class="field-error">{{ $message }}</span>
            @enderror
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

        <!-- Baris 4: Checkbox Aktif -->
        <div class="form-group-item checkbox-row">
            <label class="checkbox-custom-label">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" id="checkbox-active" name="aktif" value="1"
                       @checked(old('aktif', $produk->aktif ?? true))>
                <span class="checkbox-text">Aktif</span>
            </label>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="form-bottom-actions">
            <button type="submit" class="btn-dark-solid" id="btn-save-produk" data-field="btn-save-produk">{{ $submitLabel }}</button>
            <a href="{{ route('produk.index') }}" class="btn-light-outline" id="btn-cancel-produk" data-field="btn-cancel">Batal</a>
        </div>

    </article>
</form>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="kategori_produk"]');
        var satuanHidden = document.getElementById('satuan_hidden');
        var satuanDisplay = document.getElementById('input-unit');
        var satuanNote = document.getElementById('unit-help-text');
        var hargaLabelEl = document.getElementById('harga-label-text');
        var hargaNote = document.getElementById('price-help-text');
        var stokWrapper = document.getElementById('stok_wrapper');
        var stokInput = document.getElementById('input-stok');
        var merekWrapper = document.getElementById('merek_wrapper');
        var merekSelect = document.getElementById('select-product-brand');
        var btnAddBrand = document.getElementById('btn-add-brand');
        var merekBaruRow = document.getElementById('merek_baru_row');
        var btnGantiMerek = document.getElementById('btn-ganti-merek-mode');
        var modeMerek = document.getElementById('mode_merek');
        var hargaInput = document.getElementById('input-price');

        var dijualCfg = {
            satuan: 'pcs',
            satuanLabel: 'pcs',
            satuanNote: 'Satuan otomatis: pcs',
            hargaLabel: 'Harga Jual per 1 Pcs',
            hargaNote: 'Harga jual per 1 pcs produk.',
            showStok: true,
            showMerek: false
        };
        var layananCfg = {
            satuan: '/10ml',
            satuanLabel: '/10ml',
            satuanNote: 'Satuan otomatis: /10ml',
            hargaLabel: 'Harga Jual /10ml',
            hargaNote: 'Harga jual per 10ml bahan yang digunakan saat layanan. Modal dihitung otomatis dari riwayat restock.',
            showStok: false,
            showMerek: true
        };
        var kategoriLayanan = @json($kategoriLayanan);

        function cfgFor(val) {
            if (val === 'dijual') return dijualCfg;
            return layananCfg;
        }

        function updateRadioState() {
            radios.forEach(function (radio) {
                var card = radio.closest('.category-radio-card');
                if (!card) return;
                if (radio.checked) card.classList.add('active');
                else card.classList.remove('active');
            });
            var checked = document.querySelector('input[name="kategori_produk"]:checked');
            if (!checked) return;
            var cfg = cfgFor(checked.value);

            satuanHidden.value = cfg.satuan;
            satuanDisplay.value = cfg.satuanLabel;
            satuanNote.textContent = cfg.satuanNote;
            hargaLabelEl.textContent = cfg.hargaLabel;
            hargaNote.textContent = cfg.hargaNote;

            if (cfg.showStok) {
                stokWrapper.style.display = '';
                stokInput.setAttribute('required', 'required');
            } else {
                stokWrapper.style.display = 'none';
                stokInput.removeAttribute('required');
                stokInput.value = 0;
            }

            if (cfg.showMerek) {
                merekWrapper.classList.remove('hidden');
                merekSelect.setAttribute('required', 'required');
            } else {
                merekWrapper.classList.add('hidden');
                merekSelect.removeAttribute('required');
                merekSelect.value = '';
                setMerekMode('pilih');
            }
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

        /* Format harga */
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

        if (hargaInput) {
            hargaInput.addEventListener('focus', function () { unformatHarga(this); });
            hargaInput.addEventListener('blur', function () { formatHarga(this); });
        }

        document.querySelector('#form-edit-produk').addEventListener('submit', function () {
            if (hargaInput) unformatHarga(hargaInput);
        });

        updateRadioState();

        if (modeMerek.value === 'baru') {
            setMerekMode('baru');
            var inputBaru = merekBaruRow.querySelector('input[name="merek_baru"]');
            if (inputBaru) inputBaru.value = @json(old('merek_baru', ''));
        }

        if (hargaInput && hargaInput.value) formatHarga(hargaInput);
    })();
</script>