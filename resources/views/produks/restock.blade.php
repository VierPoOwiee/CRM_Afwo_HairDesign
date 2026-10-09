@extends('layouts.app')

@section('title', 'Restock '.$produk->nama_produk)

@section('nav-active', 'products')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/edit-produk.css') }}">
@endpush

@section('content')
    <div class="edit-produk-header">
        <h1 class="edit-produk-title" id="edit-produk-title">Restock Produk</h1>
        <p class="edit-produk-subtitle" id="edit-produk-subtitle">Catat pembelian stok &mdash; modal rata-rata dihitung otomatis.</p>
    </div>

    <div class="restock-layout">
        <div class="restock-left">

            {{-- Info produk / kondisi saat ini --}}
            <article class="card form-section-card">
                <h2 class="restock-panel-title">Kondisi Saat Ini</h2>
                <div class="info-panel-list">
                    <div class="info-panel-item">
                        <span class="info-panel-label">Produk</span>
                        <span class="info-panel-value">{{ $produk->nama_produk }}</span>
                    </div>
                    <div class="info-panel-item">
                        <span class="info-panel-label">Kategori</span>
                        <span class="info-panel-value">{{ $produk->labelKategori() }}</span>
                    </div>
                    <div class="info-panel-item">
                        <span class="info-panel-label">Satuan</span>
                        <span class="info-panel-value">{{ $produk->satuan }}</span>
                    </div>
                    <div class="info-panel-item">
                        <span class="info-panel-label">Stok</span>
                        <span class="info-panel-value {{ $produk->aktif && $produk->kategori_produk === 'dijual' && $produk->stokMenipis() ? 'highlight' : '' }}">
                            {{ $produk->stok }}
                            @if ($produk->aktif && $produk->kategori_produk === 'dijual' && $produk->stokMenipis())
                                &nbsp;(menipis)
                            @endif
                        </span>
                    </div>
                    <div class="info-panel-item">
                        <span class="info-panel-label">Harga Jual</span>
                        <span class="info-panel-value">{{ $produk->labelHarga() }}</span>
                    </div>
                    <div class="info-panel-item">
                        <span class="info-panel-label">Modal rata-rata</span>
                        <span class="info-panel-value highlight">{{ $produk->labelModalPerSatuan() }}</span>
                    </div>
                    <span class="field-help-text">Modal rata-rata (weighted average) &mdash; campuran harga beli lama &amp; baru. Diperbarui otomatis setiap kali restock.</span>
                </div>
            </article>

            {{-- Form restock --}}
            <article class="card form-section-card">
                <h2 class="restock-panel-title">Form Restock</h2>
                <form id="form-restock" class="edit-produk-stack" action="{{ route('produk.restock', $produk) }}" method="POST">
                    @csrf

                    <div class="form-group-item">
                        <label class="form-label" for="qty">Jumlah ({{ $produk->kategori_produk === 'dijual' ? 'pcs' : 'ml' }}) <span class="required-star">*</span></label>
                        <div class="form-input-box">
                            <input type="number" name="qty" id="qty" value="{{ old('qty') }}" min="1" step="1" required class="form-input-control">
                        </div>
                        @error('qty')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group-item">
                        <label class="form-label" for="harga_beli">Harga Beli per Satuan (Rp) <span class="required-star">*</span></label>
                        <div class="form-input-box">
                            <input type="text" inputmode="numeric" name="harga_beli" id="harga_beli" value="{{ old('harga_beli') }}" required class="form-input-control">
                        </div>
                        <span class="field-help-text">Harga beli per {{ $produk->satuan }}.</span>
                        @error('harga_beli')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group-item">
                        <label class="form-label" for="tanggal">Tanggal <span class="required-star">*</span></label>
                        <div class="form-input-box">
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required class="form-input-control">
                        </div>
                        @error('tanggal')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group-item">
                        <label class="form-label" for="keterangan">Keterangan</label>
                        <div class="form-input-box">
                            <textarea name="keterangan" id="keterangan" rows="2" maxlength="255" class="form-input-control" style="resize: vertical;"
                                      placeholder="Contoh: stok habis, restock bulanan...">{{ old('keterangan') }}</textarea>
                        </div>
                        @error('keterangan')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="modal-note-box" id="modalPreviewWrap">
                        <span class="info-panel-label">Modal rata-rata baru</span>
                        <span class="modal-note-value" id="modalPreview">--</span>
                    </div>

                    <div class="form-actions-row">
                        <a href="{{ route('produk.show', $produk) }}" class="btn btn-secondary" id="btn-cancel-restock">Batal</a>
                        <button type="submit" class="btn btn-primary" id="btn-save-restock">Simpan Restock</button>
                    </div>
                </form>
            </article>
        </div>

        {{-- Riwayat pembelian --}}
        <article class="card form-section-card">
            <h2 class="restock-panel-title">Riwayat Pembelian / Restock</h2>
            <span class="field-help-text">Terakhir {{ $riwayat->count() }} catatan. Total stok &amp; modal rata-rata setelah tiap pembelian tercatat di sini.</span>

            @if ($riwayat->isEmpty())
                <div class="restock-empty">
                    <p style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">Belum ada riwayat pembelian.</p>
                    <p class="field-help-text">Restock pertama akan mencatat harga beli sebagai modal awal.</p>
                </div>
            @else
                <div class="restock-table-wrapper">
                    <table class="restock-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Qty</th>
                                <th>Harga Beli</th>
                                <th>Modal Rata2</th>
                                <th>Stok Akhir</th>
                                <th>Dicatat Oleh</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($riwayat as $pb)
                                <tr>
                                    <td>{{ $pb->tanggal?->format('d M Y') }}</td>
                                    <td><span class="restock-qty-badge">+{{ $pb->qty }}</span></td>
                                    <td>Rp{{ number_format((float) $pb->harga_beli, 0, ',', '.') }}</td>
                                    <td>Rp{{ number_format((float) $pb->harga_modal_rata_rata, 0, ',', '.') }}</td>
                                    <td>{{ $pb->stok_setelah }}</td>
                                    <td class="muted">{{ $pb->dicatatOleh?->name ?? '—' }}</td>
                                    <td class="muted">{{ $pb->keterangan ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
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