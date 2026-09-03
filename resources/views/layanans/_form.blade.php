@php
    $kategoriOptions = ['Potong', 'Styling', 'Treatment Rambut', 'Warna Rambut', 'Treatment'];
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
@endphp

@php
    $inputClass = 'w-full rounded-lg border-gray-300 text-text-primary px-2 py-1.5 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none';
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="nama_layanan" class="block text-sm font-medium text-gray-700">Nama Layanan <span class="text-red-500">*</span></label>
            <input type="text" name="nama_layanan" id="nama_layanan" value="{{ old('nama_layanan', $layanan->nama_layanan ?? '') }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <div>
            <label for="kategori" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori" id="kategori" required
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <option value="">-- Pilih Kategori --</option>
                @foreach ($kategoriOptions as $k)
                    <option value="{{ $k }}" {{ old('kategori', $layanan->kategori ?? '') === $k ? 'selected' : '' }}>{{ $k }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="flex flex-wrap items-start gap-x-8 gap-y-3">
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="aktif" value="0">
            <input type="checkbox" name="aktif" value="1"
                   @checked(old('aktif', $layanan->aktif ?? true))
                   class="h-4 w-4 rounded border-gray-300 text-accent-text focus:ring-accent/30">
            Aktif
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="termasuk_potong" value="0">
            <input type="checkbox" name="termasuk_potong" value="1"
                   @checked(old('termasuk_potong', $layanan->termasuk_potong ?? false))
                   class="h-4 w-4 rounded border-gray-300 text-accent-text focus:ring-accent/30">
            Termasuk Potong
            <span class="text-[11px] text-gray-400">(exclude dari basis komisi persen_omset_harian)</span>
        </label>
    </div>

    <div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Varian Harga &amp; Komisi</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Harga Max boleh dikosongkan (artinya harga tetap = harga min). Isi "Default Produk Varian" hanya untuk varian berbasis produk; varian biasa kosongkan.
                </p>
            </div>
            <button type="button" class="js-add-row shrink-0 rounded-lg bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                + Tambah Baris
            </button>
        </div>

        <div class="mt-4">
            <div class="hidden sm:grid sm:grid-cols-6 sm:gap-3 sm:px-3 sm:pb-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Varian</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Harga Min (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Harga Max (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Komisi Min (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Komisi Max (Rp)</div>
                <div></div>
            </div>

            <div class="js-varian-rows space-y-3 sm:space-y-0">
                @forelse ($rows as $row)
                    <div data-row class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-6 sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:border-t sm:p-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Varian</label>
                            <input type="text" name="varian[]" value="{{ $row['varian'] }}" placeholder="default / S / M / L / XL" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Min (Rp)</label>
                            <input type="text" inputmode="numeric" name="harga_dasar_min[]" value="{{ $row['harga_dasar_min'] }}" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Max (Rp)</label>
                            <input type="text" inputmode="numeric" name="harga_dasar_max[]" value="{{ $row['harga_dasar_max'] }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Min (Rp)</label>
                            <input type="text" inputmode="numeric" name="komisi_min[]" value="{{ $row['komisi_min'] }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Max (Rp)</label>
                            <input type="text" inputmode="numeric" name="komisi_max[]" value="{{ $row['komisi_max'] }}" class="{{ $inputClass }}">
                        </div>
                        <div class="flex items-end sm:justify-end">
                            <button type="button" class="js-remove-row w-full rounded-lg px-2 py-2 text-sm font-medium text-red-600 hover:bg-red-50 sm:w-auto">Hapus</button>
                        </div>
                        @include('layanans._default_produk', [
                            'pRowIndex' => $loop->index,
                            'defaultRows' => $row['default_produk'] ?? [],
                            'kategoriList' => $kategoriList,
                            'fmtMl' => $fmt,
                        ])
                    </div>
                @empty
                    <div data-row class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-6 sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:border-t sm:p-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Varian</label>
                            <input type="text" name="varian[]" placeholder="default / S / M / L / XL" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Min (Rp)</label>
                            <input type="text" inputmode="numeric" name="harga_dasar_min[]" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Max (Rp)</label>
                            <input type="text" inputmode="numeric" name="harga_dasar_max[]" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Min (Rp)</label>
                            <input type="text" inputmode="numeric" name="komisi_min[]" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Max (Rp)</label>
                            <input type="text" inputmode="numeric" name="komisi_max[]" class="{{ $inputClass }}">
                        </div>
                        <div class="flex items-end sm:justify-end">
                            <button type="button" class="js-remove-row w-full rounded-lg px-2 py-2 text-sm font-medium text-red-600 hover:bg-red-50 sm:w-auto">Hapus</button>
                        </div>
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

        <template id="js-varian-row-template">
            <div data-row class="grid grid-cols-1 gap-3 rounded-lg border border-gray-200 p-3 sm:grid-cols-6 sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:border-t sm:p-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Varian</label>
                    <input type="text" name="varian[]" placeholder="default / S / M / L / XL" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Min (Rp)</label>
                    <input type="text" inputmode="numeric" name="harga_dasar_min[]" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Max (Rp)</label>
                    <input type="text" inputmode="numeric" name="harga_dasar_max[]" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Min (Rp)</label>
                    <input type="text" inputmode="numeric" name="komisi_min[]" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Max (Rp)</label>
                    <input type="text" inputmode="numeric" name="komisi_max[]" class="{{ $inputClass }}">
                </div>
                <div class="flex items-end sm:justify-end">
                    <button type="button" class="js-remove-row w-full rounded-lg px-2 py-2 text-sm font-medium text-red-600 hover:bg-red-50 sm:w-auto">Hapus</button>
                </div>
                @include('layanans._default_produk', [
                    'pRowIndex' => '__IDX__',
                    'defaultRows' => [],
                    'kategoriList' => $kategoriList,
                    'fmtMl' => $fmt,
                ])
            </div>
        </template>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('layanan.index') }}"
           class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>

<script>
    (function () {
        var tbody = document.querySelector('.js-varian-rows');
        var template = document.getElementById('js-varian-row-template');
        if (!tbody || !template) return;

        document.querySelectorAll('.js-add-row').forEach(function (btn) {
            btn.addEventListener('click', function () {
                tbody.appendChild(template.content.cloneNode(true));
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
            var rows = tbody.querySelectorAll('[data-row]');
            rows.forEach(function (row, i) {
                var panel = row.querySelector('.js-default-panel');
                if (!panel) return;
                panel.dataset.ri = i;
                panel.querySelectorAll('.js-default-row').forEach(function (drow) {
                    drow.querySelector('.js-default-kategori').name = 'default_produk[' + i + '][kategori][]';
                    drow.querySelector('.js-default-ml').name = 'default_produk[' + i + '][ml][]';
                });
            });
        }

        function addDefaultRow(el) {
            var panel = el.closest('.js-default-panel');
            if (!panel) return;
            var div = document.createElement('div');
            div.className = 'js-default-row flex flex-wrap items-center gap-2';
            div.innerHTML =
                '<select class="js-default-kategori w-full rounded-md border-gray-300 px-2 py-1.5 text-sm focus:border-accent focus:ring-accent/30 sm:w-48">' + renderDefaultKategoriOpts('') + '</select>' +
                '<div class="flex items-center gap-1.5">' +
                    '<input type="number" min="0" step="any" inputmode="decimal" placeholder="0" class="js-default-ml w-28 rounded-md border-gray-300 px-2 py-1.5 text-right text-sm focus:border-accent focus:ring-accent/30">' +
                    '<span class="w-4 text-[10px] font-medium text-gray-400">ml</span>' +
                '</div>' +
                '<button type="button" class="js-remove-default-row text-xs font-medium text-red-600 hover:text-red-800">Hapus</button>';
            panel.querySelector('.js-default-rows').appendChild(div);
            reindexDefaults();
        }

        tbody.addEventListener('click', function (e) {
            var toggleDefault = e.target.closest('.js-toggle-default');
            if (toggleDefault) {
                var defaultPanel = toggleDefault.closest('[data-row]').querySelector('.js-default-panel');
                if (defaultPanel) defaultPanel.classList.toggle('hidden');
                toggleDefault.querySelectorAll('.js-toggle-icon').forEach(function (i) {
                    i.classList.toggle('rotate-45');
                });
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

            var rows = tbody.querySelectorAll('[data-row]');
            if (rows.length <= 1) {
                rows[0].querySelectorAll('input').forEach(function (i) {
                    i.value = '';
                });
            } else {
                btn.closest('[data-row]').remove();
            }
            reindexDefaults();
        });

        /* Format harga */
        var priceNames = ['harga_dasar_min[]','harga_dasar_max[]','komisi_min[]','komisi_max[]'];

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
            document.querySelectorAll('input[name]').forEach(function (el) {
                if (priceNames.indexOf(el.name) !== -1 && el.value) {
                    formatInput(el);
                }
            });
        }

        tbody.addEventListener('focus', function (e) {
            if (e.target.matches('input') && priceNames.indexOf(e.target.name) !== -1) {
                unformatInput(e.target);
            }
        }, true);

        tbody.addEventListener('blur', function (e) {
            if (e.target.matches('input') && priceNames.indexOf(e.target.name) !== -1) {
                formatInput(e.target);
            }
        }, true);

        document.querySelector('form').addEventListener('submit', function () {
            tbody.querySelectorAll('input').forEach(function (el) {
                if (priceNames.indexOf(el.name) !== -1) unformatInput(el);
            });
        });

        formatAllPrices();
        reindexDefaults();
    })();
</script>
