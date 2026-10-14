@php
    use App\Models\Merek;
    use App\Models\Produk;
    $mereks = Merek::orderBy('nama')->get();
    $kategoriVal = old('kategori_produk', $produk->kategori_produk ?? 'Color');
    $satuanVal = old('satuan_penggunaan', $produk->satuan_penggunaan ?? '/10ml');

    $kategoriLayanan = Produk::kategoriLayanan();
<<<<<<< HEAD
    $kategoriPenjualan = ['dijual', 'dipakai_layanan'];
    $kategoriProduk = $kategoriLayanan;
=======
    $radioLeft = array_values(array_intersect(['Bleaching', 'Keratin', 'Hairtreatment', 'Serum', 'Keriting'], $kategoriLayanan));
    $radioRight = array_values(array_intersect(['Color', 'Oxidant', 'Smoothing', 'Creambath', 'Repair', 'Masker'], $kategoriLayanan));
>>>>>>> baa04034f3a7243d0040ab7cb95b48a5fe68f3b4

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
                <label class="form-label" for="brand_trigger">Merek</label>
                <div class="brand-input-row">
                    <div class="brand-combobox" id="brand_combobox" data-field="brand">
                        <input type="hidden" name="merek" id="brand_value" value="{{ old('merek', $produk->merek ?? '') }}">
                        <button type="button" class="brand-combobox-trigger" id="brand_trigger"
                                aria-haspopup="listbox" aria-expanded="false" aria-label="Pilih merek">
                            <span class="brand-combobox-label" id="brand_trigger_label">-- Pilih Merek --</span>
                            <svg class="brand-combobox-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </button>
                        <div class="brand-combobox-panel hidden" id="brand_panel" role="listbox">
                            @forelse ($mereks as $merek)
                                <div class="brand-option-row" data-id="{{ $merek->id }}" data-brand="{{ $merek->nama }}">
                                    <button type="button" class="brand-option-select" data-id="{{ $merek->id }}" data-brand="{{ $merek->nama }}"
                                            role="option" aria-selected="false">{{ $merek->nama }}</button>
                                    <button type="button" class="brand-option-delete" data-id="{{ $merek->id }}" data-brand="{{ $merek->nama }}"
                                            title="Hapus merek {{ $merek->nama }}" aria-label="Hapus merek {{ $merek->nama }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                    </button>
                                </div>
                            @empty
                                <div class="brand-option-empty">Belum ada merek. Klik "+ Baru".</div>
                            @endforelse
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="btn-add-brand">+ Baru</button>
                </div>
                <span class="field-help-text">Butuh merek baru? Klik "+ Baru" lalu ketik namanya. Klik ikon minus untuk menghapus merek dari daftar.</span>
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

        <!-- Baris 2: Kategori Produk (2 Kelompok: Penjualan & Produk) -->
        <div class="form-group-item">
            <label class="form-label">Kategori Produk <span class="required-star">*</span></label>
<<<<<<< HEAD
            <span class="field-help-text" style="margin-top: -2px; margin-bottom: 6px;">Pilih kelompok kategori, lalu jenis kategorinya.</span>

            <div class="category-group">
                <span class="category-group-title">Kategori Penjualan</span>
                <span class="category-group-help">Dijual per pcs ke pelanggan, atau dipakai staf secara umum saat layanan.</span>
                <div class="product-category-radio-grid">
                    @foreach ($kategoriPenjualan as $val)
                        @php
                            [$unit, $help] = $radioHelp[$val] ?? ['/10ml', 'Harga jual per 10ml bahan yang digunakan saat layanan. Modal dihitung otomatis dari riwayat restock.'];
                        @endphp
=======
            <span class="field-help-text" style="margin-top: -2px; margin-bottom: 6px;">Kategori bahan ini dipakai pada layanan tertentu.</span>

            <div class="product-category-radio-grid">
                <div class="radio-col">
                    @foreach ($radioLeft as $val)
>>>>>>> baa04034f3a7243d0040ab7cb95b48a5fe68f3b4
                        <label class="category-radio-card {{ $kategoriVal === $val ? 'active' : '' }}">
                            <input type="radio" name="kategori_produk" value="{{ $val }}"
                                   {{ $kategoriVal === $val ? 'checked' : '' }}>
                            <span class="radio-custom-dot"></span>
                            <span class="radio-card-label">{{ $val }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

<<<<<<< HEAD
            <div class="category-group">
                <span class="category-group-title">Kategori Produk</span>
                <span class="category-group-help">Jenis bahan khusus yang dipakai staf saat treatment (bleaching, warna, keratin, dll).</span>
                <div class="product-category-radio-grid">
                    @foreach ($kategoriProduk as $val)
                        @php
                            [$unit, $help] = $radioHelp[$val] ?? ['/10ml', 'Harga jual per 10ml bahan yang digunakan saat layanan. Modal dihitung otomatis dari riwayat restock.'];
                        @endphp
=======
                <div class="radio-col">
                    @foreach ($radioRight as $val)
>>>>>>> baa04034f3a7243d0040ab7cb95b48a5fe68f3b4
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

    <div id="merek-delete-modal" class="merek-modal-backdrop" role="dialog" aria-modal="true"
         aria-labelledby="merek-delete-title" aria-hidden="true">
        <div class="merek-modal-card">
            <div class="merek-modal-header">
                <span class="merek-modal-warning-icon" aria-hidden="true">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </span>
                <div class="merek-modal-title-wrap">
                    <h3 class="merek-modal-title" id="merek-delete-title">Hapus Merek?</h3>
                    <p class="merek-modal-desc" id="merek-delete-desc"></p>
                </div>
            </div>
            <div class="merek-modal-actions">
                <button type="button" class="btn btn-secondary" id="merek-modal-cancel">Batal</button>
                <button type="button" class="btn btn-danger" id="merek-modal-confirm">Yakin, Hapus</button>
            </div>
        </div>
    </div>
</form>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="kategori_produk"]');
        var merekBaruRow = document.getElementById('merek_baru_row');
        var brandCombobox = document.getElementById('brand_combobox');
        var brandTrigger = document.getElementById('brand_trigger');
        var brandPanel = document.getElementById('brand_panel');
        var brandValue = document.getElementById('brand_value');
        var brandLabel = document.getElementById('brand_trigger_label');
        var btnAddBrand = document.getElementById('btn-add-brand');
        var btnGantiMerek = document.getElementById('btn-ganti-merek-mode');
        var modeMerek = document.getElementById('mode_merek');
        var formEdit = document.getElementById('form-edit-produk');
        var hargaInputs = [
            document.getElementById('input-price-pcs'),
            document.getElementById('input-price-penggunaan')
        ].filter(Boolean);
        var csrfToken = formEdit ? (function () {
            var el = formEdit.querySelector('input[name="_token"]');
            return el ? el.value : '';
        })() : '';
        var merekModal = document.getElementById('merek-delete-modal');
        var merekModalDesc = document.getElementById('merek-delete-desc');
        var merekModalCancel = document.getElementById('merek-modal-cancel');
        var merekModalConfirm = document.getElementById('merek-modal-confirm');
        var pendingDelete = null;

        /* ---------- Combobox Merek ---------- */
        function openBrandPanel() {
            if (!brandPanel) return;
            brandPanel.classList.remove('hidden');
            if (brandTrigger) brandTrigger.setAttribute('aria-expanded', 'true');
        }

        function closeBrandPanel() {
            if (!brandPanel) return;
            brandPanel.classList.add('hidden');
            if (brandTrigger) brandTrigger.setAttribute('aria-expanded', 'false');
        }

        function brandOptionRows() {
            return brandPanel ? Array.prototype.slice.call(brandPanel.querySelectorAll('.brand-option-row')) : [];
        }

        function selectBrand(name) {
            if (brandValue) brandValue.value = name || '';
            if (brandLabel) brandLabel.textContent = name || '-- Pilih Merek --';
            brandOptionRows().forEach(function (row) {
                var btn = row.querySelector('.brand-option-select');
                var active = name !== '' && btn && btn.getAttribute('data-brand') === name;
                row.classList.toggle('active', active);
                if (btn) btn.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        function openMerekModal(id, name) {
            pendingDelete = { id: id, name: name };
            if (merekModalDesc) merekModalDesc.textContent = 'Merek "' + name + '" akan dihapus dari daftar. Produk dengan merek ini tidak berubah.';
            if (merekModal) {
                merekModal.classList.add('show');
                merekModal.setAttribute('aria-hidden', 'false');
            }
        }

        function closeMerekModal() {
            pendingDelete = null;
            if (merekModal) {
                merekModal.classList.remove('show');
                merekModal.setAttribute('aria-hidden', 'true');
            }
        }

        function confirmMerekDelete() {
            if (!pendingDelete) return;
            var id = pendingDelete.id;
            var name = pendingDelete.name;
            closeMerekModal();

            var url = @json(route('produk.merek.destroy', ['__ID__'])).replace('__ID__', id);

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.ok) throw new Error(data.message || 'Gagal menghapus merek.');
                brandOptionRows().forEach(function (row) {
                    if (row.getAttribute('data-id') === String(id)) row.remove();
                });
                if (brandValue && brandValue.value === name) selectBrand('');
            })
            .catch(function (err) {
                window.alert('Gagal menghapus merek: ' + err.message);
            });
        }

        if (merekModal) {
            merekModal.addEventListener('click', function (e) {
                if (e.target === merekModal) closeMerekModal();
            });
        }
        if (merekModalCancel) merekModalCancel.addEventListener('click', closeMerekModal);
        if (merekModalConfirm) merekModalConfirm.addEventListener('click', confirmMerekDelete);

        if (brandTrigger) {
            brandTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                if (brandPanel.classList.contains('hidden')) openBrandPanel();
                else closeBrandPanel();
            });
        }

        if (brandPanel) {
            brandPanel.addEventListener('click', function (e) {
                e.stopPropagation();
                var opt = e.target.closest('.brand-option-select');
                var del = e.target.closest('.brand-option-delete');
                if (opt) {
                    selectBrand(opt.getAttribute('data-brand'));
                    closeBrandPanel();
                } else if (del) {
                    openMerekModal(del.getAttribute('data-id'), del.getAttribute('data-brand'));
                }
            });
        }

        document.addEventListener('click', function () { closeBrandPanel(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (merekModal && merekModal.classList.contains('show')) closeMerekModal();
                else closeBrandPanel();
            }
        });

        /* ---------- Radio Kategori ---------- */
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

        /* ---------- Mode Pilih / Merek Baru ---------- */
        function setMerekMode(mode) {
            modeMerek.value = mode;
            if (mode === 'pilih') {
                merekBaruRow.classList.add('hidden');
                if (brandCombobox) brandCombobox.classList.remove('hidden');
                if (btnAddBrand) btnAddBrand.classList.remove('hidden');
            } else {
                merekBaruRow.classList.remove('hidden');
                if (brandCombobox) brandCombobox.classList.add('hidden');
                if (btnAddBrand) btnAddBrand.classList.add('hidden');
                var input = merekBaruRow.querySelector('input[name="merek_baru"]');
                if (input) input.focus();
            }
        }

        if (btnAddBrand) btnAddBrand.addEventListener('click', function () { setMerekMode('baru'); });
        if (btnGantiMerek) btnGantiMerek.addEventListener('click', function () { setMerekMode('pilih'); });

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
        selectBrand(brandValue ? brandValue.value : '');

        if (modeMerek.value === 'baru') {
            setMerekMode('baru');
            var inputBaru = merekBaruRow.querySelector('input[name="merek_baru"]');
            if (inputBaru) inputBaru.value = @json(old('merek_baru', ''));
        }

        hargaInputs.forEach(function (el) { if (el.value) formatHarga(el); });
    })();
</script>
