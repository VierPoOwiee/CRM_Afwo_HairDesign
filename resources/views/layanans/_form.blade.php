@php
    $kategoriOptions = ['Potong', 'Styling', 'Treatment Rambut', 'Warna Rambut', 'Treatment'];

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
                'tarif_kelebihan_per_10gr' => $fmt($hr->tarif_kelebihan_per_10gr),
                'komisi_min' => $fmt($hr->komisi_min),
                'komisi_max' => $fmt($hr->komisi_max),
            ];
        }
    } elseif (old('varian')) {
        foreach (old('varian') as $i => $varian) {
            $rows[] = [
                'varian' => $varian,
                'harga_dasar_min' => old("harga_dasar_min.$i", ''),
                'harga_dasar_max' => old("harga_dasar_max.$i", ''),
                'tarif_kelebihan_per_10gr' => old("tarif_kelebihan_per_10gr.$i", ''),
                'komisi_min' => old("komisi_min.$i", ''),
                'komisi_max' => old("komisi_max.$i", ''),
            ];
        }
    }
@endphp

@php
    $inputClass = 'w-full rounded-md border-gray-300 px-2 py-1.5 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500';
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="nama_layanan" class="block text-sm font-medium text-gray-700">Nama Layanan <span class="text-red-500">*</span></label>
            <input type="text" name="nama_layanan" id="nama_layanan" value="{{ old('nama_layanan', $layanan->nama_layanan ?? '') }}" required
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>

        <div>
            <label for="kategori" class="block text-sm font-medium text-gray-700">Kategori <span class="text-red-500">*</span></label>
            <select name="kategori" id="kategori" required
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
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
                   class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
            Aktif
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="termasuk_potong" value="0">
            <input type="checkbox" name="termasuk_potong" value="1"
                   @checked(old('termasuk_potong', $layanan->termasuk_potong ?? false))
                   class="h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500">
            Termasuk Potong
            <span class="text-[11px] text-gray-400">(exclude dari basis komisi persen_omset_harian)</span>
        </label>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700">Produk yang Digunakan</label>
        <p class="mt-0.5 text-xs text-gray-500">Pilih produk (merk) apa saja yang bisa dipakai untuk layanan ini. Bisa lebih dari satu.</p>
        @php
            $selectedProdukIds = old('produk_ids', isset($layanan) ? $layanan->produk->pluck('id')->toArray() : []);
        @endphp
        <select name="produk_ids[]" multiple
                class="mt-2 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500"
                style="min-height: 120px;">
            @foreach ($produkList as $p)
                <option value="{{ $p->id }}" {{ in_array($p->id, $selectedProdukIds) ? 'selected' : '' }}>
                    {{ $p->merek }} — {{ $p->nama_produk }} ({{ $p->labelHarga() }})
                </option>
            @endforeach
        </select>
        <p class="mt-1 text-[11px] text-gray-400">Tahan Ctrl/Cmd untuk memilih lebih dari satu. Kosongkan jika layanan ini tidak menggunakan produk.</p>
    </div>

    <div>
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-gray-900">Varian Harga &amp; Komisi</h2>
                <p class="mt-0.5 text-xs text-gray-500">
                    Harga Max boleh dikosongkan (artinya harga tetap = harga min). Kelebihan per 10gr hanya untuk layanan warna berentang.
                </p>
            </div>
            <button type="button" class="js-add-row shrink-0 rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                + Tambah Baris
            </button>
        </div>

        <div class="mt-4">
            <div class="hidden sm:grid sm:grid-cols-7 sm:gap-3 sm:px-3 sm:pb-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Varian</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Harga Min (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Harga Max (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Kelebihan /10gr (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Komisi Min (Rp)</div>
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Komisi Max (Rp)</div>
                <div></div>
            </div>

            <div class="js-varian-rows space-y-3 sm:space-y-0">
                @forelse ($rows as $row)
                    <div data-row class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 sm:grid-cols-7 sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:border-t sm:p-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Varian</label>
                            <input type="text" name="varian[]" value="{{ $row['varian'] }}" placeholder="default / S / M / L / XL" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Min (Rp)</label>
                            <input type="number" name="harga_dasar_min[]" value="{{ $row['harga_dasar_min'] }}" min="0" step="1000" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Max (Rp)</label>
                            <input type="number" name="harga_dasar_max[]" value="{{ $row['harga_dasar_max'] }}" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Kelebihan /10gr (Rp)</label>
                            <input type="number" name="tarif_kelebihan_per_10gr[]" value="{{ $row['tarif_kelebihan_per_10gr'] }}" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Min (Rp)</label>
                            <input type="number" name="komisi_min[]" value="{{ $row['komisi_min'] }}" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Max (Rp)</label>
                            <input type="number" name="komisi_max[]" value="{{ $row['komisi_max'] }}" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div class="flex items-end sm:justify-end">
                            <button type="button" class="js-remove-row w-full rounded-md px-2 py-2 text-sm font-medium text-red-600 hover:bg-red-50 sm:w-auto">Hapus</button>
                        </div>
                    </div>
                @empty
                    <div data-row class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 sm:grid-cols-7 sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:border-t sm:p-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Varian</label>
                            <input type="text" name="varian[]" placeholder="default / S / M / L / XL" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Min (Rp)</label>
                            <input type="number" name="harga_dasar_min[]" min="0" step="1000" required class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Max (Rp)</label>
                            <input type="number" name="harga_dasar_max[]" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Kelebihan /10gr (Rp)</label>
                            <input type="number" name="tarif_kelebihan_per_10gr[]" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Min (Rp)</label>
                            <input type="number" name="komisi_min[]" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Max (Rp)</label>
                            <input type="number" name="komisi_max[]" min="0" step="1000" class="{{ $inputClass }}">
                        </div>
                        <div class="flex items-end sm:justify-end">
                            <button type="button" class="js-remove-row w-full rounded-md px-2 py-2 text-sm font-medium text-red-600 hover:bg-red-50 sm:w-auto">Hapus</button>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        <template id="js-varian-row-template">
            <div data-row class="grid grid-cols-1 gap-3 rounded-md border border-gray-200 p-3 sm:grid-cols-7 sm:items-center sm:gap-3 sm:rounded-none sm:border-0 sm:border-t sm:p-3">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Varian</label>
                    <input type="text" name="varian[]" placeholder="default / S / M / L / XL" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Min (Rp)</label>
                    <input type="number" name="harga_dasar_min[]" min="0" step="1000" required class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Harga Max (Rp)</label>
                    <input type="number" name="harga_dasar_max[]" min="0" step="1000" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Kelebihan /10gr (Rp)</label>
                    <input type="number" name="tarif_kelebihan_per_10gr[]" min="0" step="1000" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Min (Rp)</label>
                    <input type="number" name="komisi_min[]" min="0" step="1000" class="{{ $inputClass }}">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 sm:hidden">Komisi Max (Rp)</label>
                    <input type="number" name="komisi_max[]" min="0" step="1000" class="{{ $inputClass }}">
                </div>
                <div class="flex items-end sm:justify-end">
                    <button type="button" class="js-remove-row w-full rounded-md px-2 py-2 text-sm font-medium text-red-600 hover:bg-red-50 sm:w-auto">Hapus</button>
                </div>
            </div>
        </template>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-md bg-violet-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('layanan.index') }}"
           class="rounded-md bg-white px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
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
            });
        });

        tbody.addEventListener('click', function (e) {
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
        });
    })();
</script>
