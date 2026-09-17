@extends('layouts.app')

@section('title', 'Restock '.$produk->nama_produk)

@section('content')
    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('produk.show', $produk) }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div class="min-w-0">
            <h1 class="text-2xl font-bold text-gray-900">Restock &mdash; {{ $produk->nama_produk }}</h1>
            <p class="mt-1 text-sm text-gray-500">Catat pembelian stok. Modal rata-rata dihitung otomatis.</p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('produk.index') }}" class="text-sm font-medium text-accent-text hover:text-accent">Data Produk &rarr;</a>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-5">
        <div class="lg:col-span-2 space-y-6">
            {{-- Info produk --}}
            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Kondisi Saat Ini</h2>
                <dl class="mt-3 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Kategori</dt>
                        <dd class="text-right text-gray-900">{{ $produk->labelKategori() }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Satuan</dt>
                        <dd class="text-right text-gray-900">{{ $produk->satuan }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Stok</dt>
                        <dd class="text-right text-gray-900">{{ $produk->stok }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Harga Jual</dt>
                        <dd class="text-right text-gray-900">{{ $produk->labelHarga() }}</dd>
                    </div>
                    <div class="rounded-md bg-accent-light px-3 py-2 flex justify-between gap-4">
                        <dt class="text-accent-text">Modal rata-rata</dt>
                        <dd class="text-right font-semibold text-accent-text">{{ $produk->labelModalPerSatuan() }}</dd>
                    </div>
                    <p class="text-xs text-gray-400">Modal rata-rata (weighted average) &means; campuran harga beli lama &amp; baru. Diperbarui otomatis setiap kali restock.</p>
                </dl>
            </div>

            {{-- Form restock --}}
            <div class="rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900">Form Restock</h2>
                <form action="{{ route('produk.restock', $produk) }}" method="POST" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="qty" class="block text-sm font-medium text-gray-700">
                            Jumlah
                            <span class="text-xs text-gray-400">
                                {{ $produk->kategori_produk === 'dijual' ? '(pcs)' : '(ml)' }}
                            </span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="qty" id="qty" value="{{ old('qty') }}" min="0.01" step="{{ $produk->kategori_produk === 'dijual' ? '1' : '0.5' }}" required
                               class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                        @error('qty') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="harga_beli" class="block text-sm font-medium text-gray-700">
                            Harga Beli per Satuan (Rp)
                            <span class="text-xs text-gray-400">per {{ $produk->satuan }}</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" inputmode="numeric" name="harga_beli" id="harga_beli"
                               value="{{ old('harga_beli') }}" required
                               class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                        @error('harga_beli') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required
                               class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                        @error('tanggal') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="2" maxlength="255"
                                  class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none"
                                  placeholder="Contoh: stok habis, restock bulanan...">{{ old('keterangan') }}</textarea>
                        @error('keterangan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-md bg-gray-50 px-3 py-2 text-sm" id="modalPreviewWrap">
                        <div class="flex justify-between gap-4">
                            <span class="text-gray-500">Modal rata-rata baru</span>
                            <span class="font-semibold text-gray-900" id="modalPreview">--</span>
                        </div>
                        <p class="mt-0.5 text-[11px] text-gray-400">Preview: (stok lama &times; modal lama + qty &times; harga beli) / stok setelah restock.</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                                class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                            Simpan Restock
                        </button>
                        <a href="{{ route('produk.show', $produk) }}"
                           class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Riwayat pembelian --}}
        <div class="lg:col-span-3 rounded-lg border border-gray-200 bg-card p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-900">Riwayat Pembelian / Restock</h2>
            <p class="mt-0.5 text-xs text-gray-400">Terakhir {{ $riwayat->count() <= 0 ? 0 : $riwayat->count() }} catatan. Total stok &amp; modal rata-rata setelah tiap pembelian tercatat di sini.</p>

            @if ($riwayat->isEmpty())
                <div class="mt-4 rounded-xl border border-dashed border-gray-300 bg-card px-6 py-12 text-center">
                    <p class="text-sm font-medium text-text-secondary">Belum ada riwayat pembelian.</p>
                    <p class="mt-1 text-sm text-text-muted">Restock pertama akan mencatat harga beli sebagai modal awal.</p>
                </div>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-400">
                                <th class="py-2 pr-4 font-medium">Tanggal</th>
                                <th class="py-2 pr-4 font-medium">Qty</th>
                                <th class="py-2 pr-4 font-medium">Harga Beli</th>
                                <th class="py-2 pr-4 font-medium">Modal Rata2</th>
                                <th class="py-2 pr-4 font-medium">Stok Akhir</th>
                                <th class="py-2 pr-4 font-medium">Dicatat Oleh</th>
                                <th class="py-2 font-medium">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($riwayat as $pb)
                                <tr>
                                    <td class="py-2.5 pr-4 whitespace-nowrap text-gray-900">{{ $pb->tanggal?->format('d M Y') }}</td>
                                    <td class="py-2.5 pr-4 text-gray-900">{{ $pb->qty }}</td>
                                    <td class="py-2.5 pr-4 text-gray-900">Rp{{ number_format((float) $pb->harga_beli, 0, ',', '.') }}</td>
                                    <td class="py-2.5 pr-4 text-gray-900">Rp{{ number_format((float) $pb->harga_modal_rata_rata, 0, ',', '.') }}</td>
                                    <td class="py-2.5 pr-4 text-gray-900">{{ $pb->stok_setelah }}</td>
                                    <td class="py-2.5 pr-4 text-gray-500">{{ $pb->dicatatOleh?->name ?? '—' }}</td>
                                    <td class="py-2.5 text-gray-500">{{ $pb->keterangan ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script>
    (function () {
        var stokLama = {{ (float) $produk->stok }};
        var modalLama = {{ (float) $produk->harga_modal_rata_rata }};
        var qtyInput = document.getElementById('qty');
        var hargaInput = document.getElementById('harga_beli');
        var preview = document.getElementById('modalPreview');
        var wrap = document.getElementById('modalPreviewWrap');

        function parseNum(str) {
            return parseFloat(String(str).replace(/\./g, '').replace(/,/g, '')) || 0;
        }
        function fmtRp(n) {
            return 'Rp' + Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }
        function updatePreview() {
            var qty = parseFloat(qtyInput.value) || 0;
            var harga = parseNum(hargaInput.value);
            if (qty <= 0 || harga < 0) { preview.textContent = '--'; return; }
            var modalBaru = (stokLama + qty) > 0
                ? ((stokLama * modalLama) + (qty * harga)) / (stokLama + qty)
                : harga;
            preview.textContent = fmtRp(modalBaru);
        }

        function formatHarga() {
            var v = parseNum(hargaInput.value);
            hargaInput.value = v ? v.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
            updatePreview();
        }
        function unformatHarga() {
            hargaInput.value = hargaInput.value.replace(/\./g, '');
        }
        if (hargaInput) {
            hargaInput.addEventListener('focus', unformatHarga);
            hargaInput.addEventListener('blur', formatHarga);
            hargaInput.addEventListener('input', updatePreview);
        }
        if (qtyInput) qtyInput.addEventListener('input', updatePreview);
        if (hargaInput && hargaInput.value) formatHarga();
    })();
    </script>
@endsection