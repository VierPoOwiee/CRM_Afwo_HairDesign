@php
    $merekOptions = ['Alfaparf', 'Milbon', 'Keaune'];
    $kategoriVal = old('kategori_produk', $produk->kategori_produk ?? 'dipakai_layanan');
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="nama_produk" class="block text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
            <input type="text" name="nama_produk" id="nama_produk" value="{{ old('nama_produk', $produk->nama_produk ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>

        <div>
            <label for="merek" class="block text-sm font-medium text-gray-700">Merek <span class="text-red-500">*</span></label>
            <select name="merek" id="merek" required
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                <option value="" {{ old('merek', $produk->merek ?? '') === '' ? 'selected' : '' }}>-- Pilih Merek --</option>
                @foreach ($merekOptions as $m)
                    <option value="{{ $m }}" {{ old('merek', $produk->merek ?? '') === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div>
        <span class="block text-sm font-medium text-gray-700">Kategori Produk <span class="text-red-500">*</span></span>
        <p class="mt-0.5 text-xs text-gray-500">Dijual per pcs ke pelanggan, atau dipakai staf saat treatment.</p>
        <div class="mt-2 grid grid-cols-1 gap-2 sm:grid-cols-2">
            <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <input type="radio" name="kategori_produk" value="dijual"
                       {{ $kategoriVal === 'dijual' ? 'checked' : '' }}
                       class="h-4 w-4 border-gray-300 text-violet-600 focus:ring-violet-500">
                Dijual Per PCS
            </label>
            <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                <input type="radio" name="kategori_produk" value="dipakai_layanan"
                       {{ $kategoriVal === 'dipakai_layanan' ? 'checked' : '' }}
                       class="h-4 w-4 border-gray-300 text-violet-600 focus:ring-violet-500">
                Dipakai Layanan
            </label>
        </div>
    </div>

    {{-- Satuan: otomatis berdasarkan kategori --}}
    <input type="hidden" name="satuan" id="satuan_hidden" value="{{ old('satuan', $produk->satuan ?? '') }}">

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label class="block text-sm font-medium text-gray-700">Satuan <span class="text-red-500">*</span></label>
            <input type="text" id="satuan_display" readonly required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm cursor-not-allowed">
            <p class="mt-1 text-xs text-gray-500" id="satuan_note"></p>
        </div>

        <div>
            <label for="harga_per_satuan" class="block text-sm font-medium text-gray-700">Harga per Satuan (Rp) <span class="text-red-500">*</span></label>
            <input type="number" name="harga_per_satuan" id="harga_per_satuan"
                   value="{{ old('harga_per_satuan', $produk->harga_per_satuan ?? '') }}"
                   min="0" step="1000" required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            <p class="mt-1 text-xs text-gray-500" id="harga_note"></p>
        </div>
    </div>

    <div id="stok_wrapper">
        <label for="stok" class="block text-sm font-medium text-gray-700">Stok <span class="text-red-500">*</span></label>
        <input type="number" name="stok" id="stok"
               value="{{ old('stok', $produk->stok ?? 0) }}"
               min="0" step="1"
               class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        <p class="mt-1 text-xs text-gray-500">Stok &le; {{ \App\Models\Produk::STOK_MENIPIS }} ditandai menipis.</p>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-700">
        <input type="hidden" name="aktif" value="0">
        <input type="checkbox" name="aktif" value="1"
               @checked(old('aktif', $produk->aktif ?? true))
               class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
        Aktif
    </label>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-md bg-violet-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('produk.index') }}"
           class="rounded-md bg-white px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
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

        var config = {
            dijual: {
                satuan: 'pcs',
                satuanLabel: 'pcs',
                satuanNote: 'Satuan otomatis: pcs',
                hargaNote: 'Harga jual per 1 pcs produk.',
                showStok: true
            },
            dipakai_layanan: {
                satuan: '/10ml',
                satuanLabel: '/10ml',
                satuanNote: 'Satuan otomatis: /10ml',
                hargaNote: 'Harga modal per 10ml bahan yang digunakan saat layanan.',
                showStok: false
            }
        };

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
        }

        radios.forEach(function (r) {
            r.addEventListener('change', apply);
        });

        apply();
    })();
</script>
