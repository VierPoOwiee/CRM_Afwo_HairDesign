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
    $kategoriOptions = ['dijual' => 'Dijual Per PCS']
        + array_combine(Produk::kategoriLayanan(), Produk::kategoriLayanan())
        + ['dipakai_layanan' => 'Dipakai Layanan (umum)'];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2" id="merek_grid">
        <div>
            <label for="nama_produk" class="block text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
            <input type="text" name="nama_produk" id="nama_produk" value="{{ old('nama_produk', $produk->nama_produk ?? '') }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <div id="merek_wrapper">
            <label for="merek" class="block text-sm font-medium text-gray-700">Merek <span class="text-red-500">*</span></label>
            <div class="mt-1 flex items-start gap-2">
                <select name="merek" id="merek"
                        class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                    <option value="" {{ old('merek', $produk->merek ?? '') === '' ? 'selected' : '' }}>-- Pilih Merek --</option>
                    @foreach ($merekOptions as $m)
                        <option value="{{ $m }}" {{ old('merek', $produk->merek ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                <button type="button" id="btnTambahMerek"
                        class="shrink-0 rounded-lg bg-accent px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-accent-text">
                    + Baru
                </button>
            </div>
            <input type="hidden" name="mode_merek" id="mode_merek" value="{{ old('mode_merek', 'pilih') }}">
            <input type="text" name="merek_baru" id="merek_baru" maxlength="255" autocomplete="off"
                   placeholder="Ketik merek baru..." value="{{ old('merek_baru') }}"
                   class="mt-1 hidden w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
            <p class="mt-1 text-xs text-gray-500">Butuh merek baru? Klik "+ Baru" lalu ketik namanya.</p>
        </div>
    </div>

    <div>
        <span class="block text-sm font-medium text-gray-700">Kategori Produk <span class="text-red-500">*</span></span>
        <p class="mt-0.5 text-xs text-gray-500">Dijual per pcs ke pelanggan, atau dipakai staf saat treatment.</p>
        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach ($kategoriOptions as $val => $label)
                <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    <input type="radio" name="kategori_produk" value="{{ $val }}"
                           {{ $kategoriVal === $val ? 'checked' : '' }}
                           class="h-4 w-4 border-gray-300 text-accent-text focus:ring-accent/30">
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </div>

    {{-- Satuan: otomatis berdasarkan kategori --}}
    <input type="hidden" name="satuan" id="satuan_hidden" value="{{ old('satuan', $produk->satuan ?? '') }}">

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700">Satuan <span class="text-red-500">*</span></label>
            <input type="text" id="satuan_display" readonly required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm cursor-not-allowed">
            <p class="mt-1 text-xs text-gray-500" id="satuan_note"></p>
        </div>

        <div>
            <label for="harga_per_satuan" class="block text-sm font-medium text-gray-700">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
            <input type="text" inputmode="numeric" name="harga_per_satuan" id="harga_per_satuan"
                   value="{{ old('harga_per_satuan', $produk->harga_per_satuan ?? '') }}"
                   required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
            <p class="mt-1 text-xs text-gray-500" id="harga_note"></p>
        </div>
    </div>

    <div id="stok_wrapper">
        <label for="stok" class="block text-sm font-medium text-gray-700">Stok <span class="text-red-500">*</span></label>
        <input type="number" name="stok" id="stok"
               value="{{ old('stok', $produk->stok ?? 0) }}"
               min="0" step="1"
               class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        <p class="mt-1 text-xs text-gray-500">Stok &le; {{ \App\Models\Produk::STOK_MENIPIS }} ditandai menipis.</p>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="aktif" value="0">
        <input type="checkbox" name="aktif" value="1"
               @checked(old('aktif', $produk->aktif ?? true))
               class="h-4 w-4 rounded border-gray-300 text-accent-text focus:ring-accent/30">
        Aktif
    </label>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('produk.index') }}"
           class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="kategori_produk"]');
        var satuanHidden = document.getElementById('satuan_hidden');
        var satuanDisplay = document.getElementById('satuan_display');
        var satuanNote = document.getElementById('satuan_note');
        var hargaNote = document.getElementById('harga_note');
        var stokWrapper = document.getElementById('stok_wrapper');
        var stokInput = document.getElementById('stok');
        var merekGrid = document.getElementById('merek_grid');
        var merekWrapper = document.getElementById('merek_wrapper');
        var merekSelect = document.getElementById('merek');
        var btnTambahMerek = document.getElementById('btnTambahMerek');
        var merekBaru = document.getElementById('merek_baru');
        var modeMerek = document.getElementById('mode_merek');
        var hargaInput = document.getElementById('harga_per_satuan');
        var merekOptions = @json($merekOptions);

        var config = {
            dijual: {
                satuan: 'pcs',
                satuanLabel: 'pcs',
                satuanNote: 'Satuan otomatis: pcs',
                hargaNote: 'Harga jual per 1 pcs produk.',
                showStok: true,
                showMerek: false
            }
        };

        var layananCfg = {
            satuan: '/10ml',
            satuanLabel: '/10ml',
            satuanNote: 'Satuan otomatis: /10ml',
            hargaNote: 'Harga modal per 10ml bahan yang digunakan saat layanan.',
            showStok: false,
            showMerek: true
        };

        config['dipakai_layanan'] = layananCfg;
        var kategoriLayanan = @json(\App\Models\Produk::kategoriLayanan());
        kategoriLayanan.forEach(function (k) { config[k] = layananCfg; });

        function apply() {
            var val = document.querySelector('input[name="kategori_produk"]:checked').value;
            var cfg = config[val];
            satuanHidden.value = cfg.satuan;
            satuanDisplay.value = cfg.satuanLabel;
            satuanNote.textContent = cfg.satuanNote;
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
                merekWrapper.style.display = '';
                merekSelect.setAttribute('required', 'required');
                merekGrid.classList.add('sm:grid-cols-2');
                merekGrid.classList.remove('sm:grid-cols-1');
            } else {
                merekWrapper.style.display = 'none';
                merekSelect.removeAttribute('required');
                merekSelect.value = '';
                modeMerek.value = 'pilih';
                merekSelect.classList.remove('hidden');
                btnTambahMerek.classList.remove('hidden');
                merekBaru.classList.add('hidden');
                merekBaru.value = '';
                merekBaru.removeAttribute('required');
                merekSelect.removeAttribute('disabled');
                merekGrid.classList.remove('sm:grid-cols-2');
                merekGrid.classList.add('sm:grid-cols-1');
            }
        }

        radios.forEach(function (r) {
            r.addEventListener('change', apply);
        });

        function setMerekMode(mode) {
            modeMerek.value = mode;
            if (mode === 'pilih') {
                merekSelect.classList.remove('hidden');
                btnTambahMerek.classList.remove('hidden');
                merekBaru.classList.add('hidden');
                merekBaru.value = '';
                merekSelect.removeAttribute('disabled');
                merekBaru.removeAttribute('required');
                if (merekSelect.value === '') merekSelect.value = merekOptions[0] || '';
            } else {
                merekSelect.classList.add('hidden');
                btnTambahMerek.classList.add('hidden');
                merekBaru.classList.remove('hidden');
                merekSelect.setAttribute('disabled', 'disabled');
                merekBaru.setAttribute('required', 'required');
                merekBaru.focus();
            }
        }

        btnTambahMerek.addEventListener('click', function () {
            setMerekMode('baru');
        });

        apply();

        if (modeMerek.value === 'baru') {
            setMerekMode('baru');
            merekBaru.value = @json(old('merek_baru', ''));
        }

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

        document.querySelector('form').addEventListener('submit', function () {
            if (hargaInput) unformatHarga(hargaInput);
        });

        if (hargaInput && hargaInput.value) formatHarga(hargaInput);
    })();
</script>
