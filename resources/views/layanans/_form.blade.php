@php
    $kategoriOptions = ['Potong', 'Styling', 'Treatment Rambut', 'Warna Rambut', 'Treatment'];
    $kategoriLabels = [
        'Potong' => 'Potong Rambut',
        'Styling' => 'Styling &amp; Blowout',
        'Treatment Rambut' => 'Hair Treatment &amp; Spa',
        'Warna Rambut' => 'Color &amp; Highlights',
        'Treatment' => 'Hair Treatment &amp; Spa',
    ];
    $kategoriList = \App\Models\Produk::kategoriLayanan();

    $fmt = function ($v) {
        if ($v === null || $v === '') {
            return '';
        }
        $s = (string) $v;
        if (str_contains($s, '.')) {
            $s = rtrim(rtrim($s, '0'), '.');
        }
        return $s;
    };

    $rows = [];
    if (isset($hargaRows)) {
        foreach ($hargaRows as $hr) {
            $rows[] = [
                'varian' => $hr->varian,
                'harga_dasar_min' => $fmt($hr->harga_dasar_min),
                'harga_dasar_max' => $fmt($hr->harga_dasar_max),
                'komisi_min' => $fmt($hr->komisi_min),
                'komisi_max' => $fmt($hr->komisi_max),
                'default_produk' => collect($hr->defaultProduk)->map(function ($d) use ($fmt) {
                    return [
                        'kategori_produk' => $d->kategori_produk,
                        'default_ml' => $fmt($d->default_ml),
                    ];
                })->all(),
            ];
        }
    } elseif (old('varian')) {
        foreach (old('varian') as $i => $varian) {
            $defaultList = [];
            $katOld = old("default_produk.$i.kategori", []);
            $mlOld = old("default_produk.$i.ml", []);
            foreach ($katOld as $di => $kat) {
                if ($kat === null || $kat === '') {
                    continue;
                }
                $defaultList[] = [
                    'kategori_produk' => $kat,
                    'default_ml' => $mlOld[$di] ?? '',
                ];
            }

            $rows[] = [
                'varian' => $varian,
                'harga_dasar_min' => old("harga_dasar_min.$i", ''),
                'harga_dasar_max' => old("harga_dasar_max.$i", ''),
                'komisi_min' => old("komisi_min.$i", ''),
                'komisi_max' => old("komisi_max.$i", ''),
                'default_produk' => $defaultList,
            ];
        }
    }

    $formAction = isset($layanan) && $layanan->exists
        ? route('layanan.update', $layanan)
        : route('layanan.store');
    $formMethod = isset($layanan) && $layanan->exists ? 'PUT' : '';
@endphp

<form id="form-edit-layanan" class="edit-layanan-stack" action="{{ $formAction }}" method="POST">
    @csrf
    @if ($formMethod !== '')
        @method($formMethod)
    @endif

    <article class="card form-section-card" data-field="service-form-card">

        <!-- Baris 1: Nama Layanan & Kategori -->
        <div class="form-row-2col">
            <div class="form-group-item">
                <label class="form-label" for="input-service-name">Nama Layanan <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <input type="text" id="input-service-name" name="nama_layanan" data-field="service-name"
                           class="form-input-control" placeholder="Nama layanan..." required
                           value="{{ old('nama_layanan', $layanan->nama_layanan ?? '') }}">
                </div>
                @error('nama_layanan')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group-item">
                <label class="form-label" for="select-category">Kategori <span class="required-star">*</span></label>
                <div class="form-input-box">
                    <select id="select-category" name="kategori" data-field="category" class="form-select-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach ($kategoriOptions as $k)
                            <option value="{{ $k }}" {{ old('kategori', $layanan->kategori ?? '') === $k ? 'selected' : '' }}>
                                {!! $kategoriLabels[$k] !!}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('kategori')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <!-- Baris 2: Checkboxes Aktif & Termasuk Potong -->
        <div class="form-checkboxes-row">
            <label class="checkbox-custom-label">
                <input type="hidden" name="aktif" value="0">
                <input type="checkbox" id="checkbox-service-active" name="aktif" value="1"
                       @checked(old('aktif', $layanan->aktif ?? true))>
                <span class="checkbox-text">Aktif</span>
            </label>

            <label class="checkbox-custom-label">
                <input type="hidden" name="termasuk_potong" value="0">
                <input type="checkbox" id="checkbox-include-cut" name="termasuk_potong" value="1"
                       @checked(old('termasuk_potong', $layanan->termasuk_potong ?? false))>
                <span class="checkbox-text">Termasuk Potong <span class="muted-note">(exclude dari basis komisi persen_omset_harian)</span></span>
            </label>
        </div>

        <!-- Baris 3: Varian Harga & Komisi -->
        <div class="variants-section">
            <div class="variants-header-bar">
                <h2 class="variants-title">Varian Harga &amp; Komisi</h2>
                <button type="button" class="btn-add-variant-row js-add-row" id="btn-add-variant-row">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>+ Tambah Baris</span>
                </button>
            </div>

            <p class="variants-help-text">Harga Max boleh dikosongkan (artinya harga tetap = harga min). Isi "Default Produk Varian" hanya untuk varian berbasis produk; varian biasa kosongkan.</p>

            <!-- Table / Grid of Variants -->
            <div class="variants-table-wrapper">
                <div class="variants-grid-header">
                    <div class="v-col">VARIAN</div>
                    <div class="v-col">HARGA MIN (RP)</div>
                    <div class="v-col">HARGA MAX (RP)</div>
                    <div class="v-col">KOMISI MIN (RP)</div>
                    <div class="v-col">KOMISI MAX (RP)</div>
                    <div class="v-col"></div>
                </div>

                <div class="variants-rows-container js-varian-rows" id="variants-rows-container">
                    @forelse ($rows as $row)
                        <div data-row class="variant-item-row">
                            <div class="variant-input-box">
                                <input type="text" name="varian[]" value="{{ $row['varian'] }}" placeholder="Varian (default / S / M / L)" required class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="harga_dasar_min[]" value="{{ $row['harga_dasar_min'] }}" placeholder="Harga Min" required class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="harga_dasar_max[]" value="{{ $row['harga_dasar_max'] }}" placeholder="Harga Max" class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="komisi_min[]" value="{{ $row['komisi_min'] }}" placeholder="Komisi Min" class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="komisi_max[]" value="{{ $row['komisi_max'] }}" placeholder="Komisi Max" class="variant-input-field">
                            </div>
                            <button type="button" class="js-remove-row btn-delete-variant">Hapus</button>

                            @include('layanans._default_produk', [
                                'pRowIndex' => $loop->index,
                                'defaultRows' => $row['default_produk'] ?? [],
                                'kategoriList' => $kategoriList,
                                'fmtMl' => $fmt,
                            ])
                        </div>
                    @empty
                        <div data-row class="variant-item-row">
                            <div class="variant-input-box">
                                <input type="text" name="varian[]" placeholder="Varian (default / S / M / L)" required class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="harga_dasar_min[]" placeholder="Harga Min" required class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="harga_dasar_max[]" placeholder="Harga Max" class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="komisi_min[]" placeholder="Komisi Min" class="variant-input-field">
                            </div>
                            <div class="variant-input-box">
                                <input type="text" inputmode="numeric" name="komisi_max[]" placeholder="Komisi Max" class="variant-input-field">
                            </div>
                            <button type="button" class="js-remove-row btn-delete-variant">Hapus</button>

                            @include('layanans._default_produk', [
                                'pRowIndex' => 0,
                                'defaultRows' => [],
                                'kategoriList' => $kategoriList,
                                'fmtMl' => $fmt,
                            ])
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Bottom Action Buttons -->
        <div class="form-bottom-actions">
            <button type="submit" class="btn-dark-solid" id="btn-save-layanan" data-field="btn-save-layanan">{{ $submitLabel }}</button>
            <a href="{{ route('layanan.index') }}" class="btn-light-outline" id="btn-cancel-layanan" data-field="btn-cancel">Batal</a>
        </div>

    </article>

    <!-- Template Baris Varian Baru (di-inject oleh JS) -->
    <template id="js-varian-row-template">
        <div data-row class="variant-item-row">
            <div class="variant-input-box">
                <input type="text" name="varian[]" placeholder="Varian (default / S / M / L)" required class="variant-input-field">
            </div>
            <div class="variant-input-box">
                <input type="text" inputmode="numeric" name="harga_dasar_min[]" placeholder="Harga Min" required class="variant-input-field">
            </div>
            <div class="variant-input-box">
                <input type="text" inputmode="numeric" name="harga_dasar_max[]" placeholder="Harga Max" class="variant-input-field">
            </div>
            <div class="variant-input-box">
                <input type="text" inputmode="numeric" name="komisi_min[]" placeholder="Komisi Min" class="variant-input-field">
            </div>
            <div class="variant-input-box">
                <input type="text" inputmode="numeric" name="komisi_max[]" placeholder="Komisi Max" class="variant-input-field">
            </div>
            <button type="button" class="js-remove-row btn-delete-variant">Hapus</button>

            @include('layanans._default_produk', [
                'pRowIndex' => '__IDX__',
                'defaultRows' => [],
                'kategoriList' => $kategoriList,
                'fmtMl' => $fmt,
            ])
        </div>
    </template>
</form>

<script>
    (function () {
        var container = document.querySelector('.js-varian-rows');
        var template = document.getElementById('js-varian-row-template');
        if (!container || !template) return;

        document.querySelectorAll('.js-add-row').forEach(function (btn) {
            btn.addEventListener('click', function () {
                container.appendChild(template.content.cloneNode(true));
                reindexDefaults();
                formatAllPrices();
            });
        });

        var defaultKategoriOpts = @json(\App\Models\Produk::kategoriLayanan());

        function renderDefaultKategoriOpts(selected) {
            var html = '<option value="">-- Kategori --</option>';
            defaultKategoriOpts.forEach(function (k) {
                html += '<option value="' + k + '"' + (String(selected) === String(k) ? ' selected' : '') + '>' + k + '</option>';
            });
            return html;
        }

        function reindexDefaults() {
            container.querySelectorAll('[data-row]').forEach(function (row, i) {
                var panel = row.querySelector('.js-default-panel');
                if (!panel) return;
                panel.dataset.ri = i;
                panel.querySelectorAll('.js-default-row').forEach(function (drow) {
                    drow.querySelector('.js-default-kategori').name = 'default_produk[' + i + '][kategori][]';
                    drow.querySelector('.js-default-ml').name = 'default_produk[' + i + '][ml][]';
                });
            });
        }

        function setToggleIcon(toggle, open) {
            var icon = toggle.querySelector('.js-toggle-icon');
            if (icon) icon.textContent = open ? '\u00D7' : '+';
        }

        function addDefaultRow(el) {
            var panel = el.closest('.js-default-panel');
            if (!panel) return;
            var div = document.createElement('div');
            div.className = 'js-default-row dp-row';
            div.innerHTML =
                '<div class="variant-input-box dp-input-box">' +
                    '<select class="js-default-kategori variant-input-field">' + renderDefaultKategoriOpts('') + '</select>' +
                '</div>' +
                '<div class="variant-input-box dp-input-box">' +
                    '<input type="number" min="0" step="any" inputmode="decimal" placeholder="0" class="js-default-ml variant-input-field dp-ml-field">' +
                '</div>' +
                '<span class="dp-ml-unit">ml</span>' +
                '<button type="button" class="js-remove-default-row btn-delete-variant">Hapus</button>';
            panel.querySelector('.js-default-rows').appendChild(div);
            reindexDefaults();
        }

        container.addEventListener('click', function (e) {
            var toggleDefault = e.target.closest('.js-toggle-default');
            if (toggleDefault) {
                var panel = toggleDefault.closest('[data-row]').querySelector('.js-default-panel');
                if (panel) {
                    var open = panel.classList.toggle('hidden') === false;
                    setToggleIcon(toggleDefault, open);
                }
                return;
            }

            var addDefault = e.target.closest('.js-add-default-row');
            if (addDefault) {
                addDefaultRow(addDefault);
                return;
            }

            var removeDefault = e.target.closest('.js-remove-default-row');
            if (removeDefault) {
                removeDefault.closest('.js-default-row').remove();
                reindexDefaults();
                return;
            }

            var btn = e.target.closest('.js-remove-row');
            if (!btn) return;

            var rows = container.querySelectorAll('[data-row]');
            if (rows.length <= 1) {
                rows[0].querySelectorAll('input').forEach(function (i) {
                    i.value = '';
                });
            } else {
                btn.closest('[data-row]').remove();
            }
            reindexDefaults();
        });

        /* Format harga Indonesia (Rp) */
        var priceNames = ['varian[]','harga_dasar_min[]','harga_dasar_max[]','komisi_min[]','komisi_max[]'];
        var priceInputs = ['harga_dasar_min[]','harga_dasar_max[]','komisi_min[]','komisi_max[]'];

        function parseFormatted(str) {
            return parseInt(str.replace(/\./g, '').replace(/,/g, ''), 10) || 0;
        }

        function formatNumber(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function formatInput(el) {
            var raw = parseFormatted(el.value);
            el.value = raw ? formatNumber(raw) : '';
        }

        function unformatInput(el) {
            el.value = el.value.replace(/\./g, '');
        }

        function formatAllPrices() {
            container.querySelectorAll('input[name]').forEach(function (el) {
                if (priceInputs.indexOf(el.name) !== -1 && el.value) {
                    formatInput(el);
                }
            });
        }

        container.addEventListener('focus', function (e) {
            if (e.target.matches('input') && priceInputs.indexOf(e.target.name) !== -1) {
                unformatInput(e.target);
            }
        }, true);

        container.addEventListener('blur', function (e) {
            if (e.target.matches('input') && priceInputs.indexOf(e.target.name) !== -1) {
                formatInput(e.target);
            }
        }, true);

        var form = document.getElementById('form-edit-layanan');
        if (form) {
            form.addEventListener('submit', function () {
                container.querySelectorAll('input').forEach(function (el) {
                    if (priceInputs.indexOf(el.name) !== -1) unformatInput(el);
                });
            });
        }

        /* Toggle icon awal untuk panel yang sudah terbuka */
        container.querySelectorAll('.js-toggle-default').forEach(function (toggle) {
            var panel = toggle.closest('[data-row]').querySelector('.js-default-panel');
            setToggleIcon(toggle, panel && !panel.classList.contains('hidden'));
        });

        formatAllPrices();
        reindexDefaults();
    })();
</script>