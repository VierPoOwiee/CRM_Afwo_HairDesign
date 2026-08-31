@extends('layouts.app')

@section('title', 'Transaksi Baru')

@section('content')
    <div class="flex flex-wrap items-center gap-4">
        <a href="{{ route('transaksi.index') }}" class="text-gray-400 hover:text-gray-600">&larr;</a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transaksi Baru</h1>
            <p class="mt-1 text-sm text-gray-500">Isi data kunjungan pelanggan.</p>
        </div>
    </div>

    <form action="{{ route('transaksi.store') }}" method="POST" id="transaksiForm" class="mt-6 max-w-4xl space-y-6 px-0 sm:px-0">
        @csrf

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-900">Data Pelanggan</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Cari Pelanggan <span class="text-red-500">*</span></label>
                    <div class="mt-1 flex gap-2">
                        <div class="relative flex-1">
                            <input type="text" id="pelanggan_search" placeholder="Ketik nama atau no WA..."
                                   autocomplete="off"
                                   class="block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                            <input type="hidden" name="id_pelanggan" id="id_pelanggan" value="{{ old('id_pelanggan') }}">
                            <div id="pelanggan_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
                        </div>
                        <button type="button" id="addPelangganBtn"
                                class="shrink-0 rounded-lg bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                            + Pelanggan Baru
                        </button>
                    </div>
                    <div id="pelanggan_selected" class="mt-2 hidden">
                        <span class="inline-flex items-center gap-1 rounded-full bg-accent-light px-3 py-1 text-xs font-medium text-accent-text">
                            <span id="pelanggan_nama"></span>
                            <button type="button" onclick="clearPelanggan()" class="ml-1 text-text-muted hover:text-accent">&times;</button>
                        </span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Waktu Kunjungan <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="waktu_kunjungan" value="{{ old('waktu_kunjungan', date('Y-m-d\TH:i')) }}" required
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Pengerjaan <span class="text-red-500">*</span></label>
                    <div class="mt-1 flex gap-4">
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="radio" name="jenis_pengerjaan" value="sendiri" {{ old('jenis_pengerjaan', 'sendiri') === 'sendiri' ? 'checked' : '' }} class="text-accent" required onchange="onJenisChange()">
                            Sendiri
                        </label>
                        <label class="flex items-center gap-1.5 text-sm">
                            <input type="radio" name="jenis_pengerjaan" value="berdua" {{ old('jenis_pengerjaan') === 'berdua' ? 'checked' : '' }} class="text-accent" onchange="onJenisChange()">
                            Berdua
                        </label>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Metode Pembayaran <span class="text-red-500">*</span></label>
                    <select name="metode_pembayaran" required
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                        <option value="">-- Pilih --</option>
                        <option value="cash" {{ old('metode_pembayaran') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="qris" {{ old('metode_pembayaran') === 'qris' ? 'selected' : '' }}>QRIS</option>
                        <option value="debit" {{ old('metode_pembayaran') === 'debit' ? 'selected' : '' }}>Debit</option>
                        <option value="kartu_kredit" {{ old('metode_pembayaran') === 'kartu_kredit' ? 'selected' : '' }}>Kartu Kredit</option>
                        <option value="transfer" {{ old('metode_pembayaran') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Item Transaksi</h2>
                    <p class="mt-0.5 text-xs text-gray-500">Tambahkan layanan atau produk yang dikerjakan.</p>
                </div>
                <button type="button" id="addItemBtn"
                        class="rounded-lg bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    + Tambah Item
                </button>
            </div>

            <div id="itemsContainer" class="mt-4 space-y-4">
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-3 text-base font-semibold text-gray-900">Ringkasan</h2>
            <div id="itemSummary" class="mb-2 space-y-1"></div>
            <div id="summaryBreakdown" class="mb-3 space-y-1 border-t border-gray-100 pt-2">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Subtotal</span>
                    <span id="subtotalDisplay" class="font-semibold text-gray-800">Rp0</span>
                </div>
                <div class="flex justify-between text-sm text-red-500">
                    <span>Total Diskon</span>
                    <span id="diskonDisplay">-Rp0</span>
                </div>
            </div>
            <div class="flex justify-between text-sm">
                <span class="font-semibold text-gray-700">Total Bayar</span>
                <span id="totalDisplay" class="text-lg font-bold text-gray-900">Rp0</span>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit" id="submitBtn"
                    class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover disabled:opacity-50 disabled:cursor-not-allowed">
                Simpan Transaksi
            </button>
            <a href="{{ route('transaksi.index') }}"
               class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Batal
            </a>
        </div>
    </form>

    {{-- Inline pelanggan creation modal --}}
    <div id="pelangganModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" style="display:none;">
        <div class="rounded-lg bg-white p-6 shadow-xl w-full max-w-md">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Tambah Pelanggan Baru</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                    <input type="text" id="new_pelanggan_nama" required
                           class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                </div>
                <div>
                    <x-phone-input name="new_pelanggan_wa" label="No. WhatsApp" placeholder="812xxxxxxx" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                    <select id="new_pelanggan_kelamin"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Rambut</label>
                    <select id="new_pelanggan_rambut"
                            class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30">
                        <option value="">-- Pilih --</option>
                        <option value="Lurus">Lurus</option>
                        <option value="Ikal">Ikal</option>
                        <option value="Bergelombang">Bergelombang</option>
                        <option value="Keriting">Keriting</option>
                    </select>
                </div>
            </div>
            <div id="new_pelanggan_error" class="mt-3 text-sm text-red-600 hidden"></div>
            <div class="mt-4 flex items-center justify-end gap-2">
                <button type="button" id="cancelPelangganBtn"
                        class="rounded-lg bg-card px-4 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Batal
                </button>
                <button type="button" id="savePelangganBtn"
                        class="rounded-lg bg-dark px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
                    Simpan
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var karyawans = @json($karyawans);
        var allProduks = @json($produks);
        var itemIndex = 0;

        // --- Helpers ---
        function fmt(n) { return new Intl.NumberFormat('id-ID').format(Math.round(n)); }
        function getJenis() { return document.querySelector('input[name="jenis_pengerjaan"]:checked').value; }
        function isBerdua() { return getJenis() === 'berdua'; }

        function stafOptsHtml(selectedId, extraClass) {
            var html = '<option value="">-- Pilih Staf --</option>';
            karyawans.forEach(function(k) {
                var sel = (k.id == selectedId) ? ' selected' : '';
                html += '<option value="' + k.id + '" data-skema="' + k.skema_komisi + '"' + sel + '>' + k.nama + '</option>';
            });
            return html;
        }

        function komisiNoteHtml(komisiMin, komisiMax, hargaNow) {
            var min = parseFloat(komisiMin) || 0;
            var max = parseFloat(komisiMax) || 0;
            if (min > 0 || max > 0) {
                if (min === max) return 'Saran komisi: Rp' + fmt(min);
                return 'Saran komisi: Rp' + fmt(min) + ' - Rp' + fmt(max);
            }
            var saran = Math.round((hargaNow || 0) * 0.08 / 1000) * 1000;
            if (saran > 0) return 'Saran komisi (~8%): Rp' + fmt(saran);
            return 'Saran komisi: ~8% dari harga layanan (diisi manual)';
        }

        // --- Pelanggan Search ---
        var searchInput = document.getElementById('pelanggan_search');
        var resultsDiv = document.getElementById('pelanggan_results');
        var selectedDiv = document.getElementById('pelanggan_selected');
        var idInput = document.getElementById('id_pelanggan');
        var namaSpan = document.getElementById('pelanggan_nama');
        var debounceTimer;

        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            var q = this.value.trim();
            if (q.length < 2) { resultsDiv.classList.add('hidden'); return; }
            debounceTimer = setTimeout(function() {
                fetch('{{ route("api.pelanggan.search") }}?q=' + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        if (data.length === 0) {
                            resultsDiv.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan. Klik "+ Pelanggan Baru" untuk menambah.</div>';
                            resultsDiv.classList.remove('hidden');
                            return;
                        }
                        resultsDiv.innerHTML = data.map(function(p) {
                            return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" onclick="selectPelanggan(' + p.id + ', \'' + p.nama.replace(/'/g, "\\'") + '\')">' + p.nama + (p.no_wa ? ' <span class="text-gray-400">(' + p.no_wa + ')</span>' : '') + '</div>';
                        }).join('');
                        resultsDiv.classList.remove('hidden');
                    });
            }, 300);
        });

        window.selectPelanggan = function(id, nama) {
            idInput.value = id;
            namaSpan.textContent = nama;
            selectedDiv.classList.remove('hidden');
            searchInput.classList.add('hidden');
            resultsDiv.classList.add('hidden');
            validateForm();
        };

        window.clearPelanggan = function() {
            idInput.value = '';
            selectedDiv.classList.add('hidden');
            searchInput.classList.remove('hidden');
            searchInput.value = '';
            validateForm();
        };

        // --- Inline Pelanggan Creation ---
        var modal = document.getElementById('pelangganModal');
        document.getElementById('addPelangganBtn').addEventListener('click', function() {
            modal.style.display = 'flex';
            document.getElementById('new_pelanggan_nama').value = searchInput.value || '';
            document.getElementById('new_pelanggan_nama').focus();
        });
        document.getElementById('cancelPelangganBtn').addEventListener('click', function() {
            modal.style.display = 'none';
        });
        document.getElementById('savePelangganBtn').addEventListener('click', function() {
            var nama = document.getElementById('new_pelanggan_nama').value.trim();
            if (!nama) { document.getElementById('new_pelanggan_error').textContent = 'Nama wajib diisi.'; document.getElementById('new_pelanggan_error').classList.remove('hidden'); return; }
            document.getElementById('new_pelanggan_error').classList.add('hidden');

            fetch('{{ route("api.pelanggan.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    nama: nama,
                    no_wa: document.getElementById('new_pelanggan_wa').value.trim(),
                    jenis_kelamin: document.getElementById('new_pelanggan_kelamin').value,
                    jenis_rambut: document.getElementById('new_pelanggan_rambut').value
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(p) {
                selectPelanggan(p.id, p.nama);
                modal.style.display = 'none';
                document.getElementById('new_pelanggan_nama').value = '';
                var waNumberInput = document.getElementById('new_pelanggan_wa_number');
                waNumberInput.value = '';
                waNumberInput.dispatchEvent(new Event('input'));
                document.getElementById('new_pelanggan_kelamin').value = '';
                document.getElementById('new_pelanggan_rambut').value = '';
            })
            .catch(function() {
                document.getElementById('new_pelanggan_error').textContent = 'Gagal menyimpan pelanggan.';
                document.getElementById('new_pelanggan_error').classList.remove('hidden');
            });
        });

        // --- Jenis Pengerjaan Change ---
        window.onJenisChange = function() {
            var berdua = isBerdua();
            document.querySelectorAll('.staf2-section').forEach(function(el) {
                el.style.display = berdua ? '' : 'none';
                if (!berdua) {
                    var select = el.querySelector('select');
                    if (select) select.value = '';
                    var komisiInput = el.querySelector('.komisi-input-2');
                    if (komisiInput) komisiInput.value = '';
                    var note2 = el.querySelector('.komisi-note-2');
                    if (note2) note2.textContent = '';
                }
            });
        };

        // --- Item Management ---
        document.getElementById('addItemBtn').addEventListener('click', function() {
            addItem();
        });

        function addItem() {
            var idx = itemIndex++;
            var html = buildItemHtml(idx);
            document.getElementById('itemsContainer').insertAdjacentHTML('beforeend', html);
            initItemEvents(idx);
        }

        function buildItemHtml(idx) {
            return '<div class="item-row rounded-lg border border-gray-200 p-4" data-idx="' + idx + '">' +
                '<div class="flex items-start justify-between gap-2 mb-3">' +
                    '<div class="flex items-center gap-3">' +
                        '<span class="item-number text-xs font-semibold text-gray-400">#' + (idx + 1) + '</span>' +
                        '<div class="flex gap-2">' +
                            '<label class="flex items-center gap-1 text-sm"><input type="radio" name="items[' + idx + '][tipe_item]" value="layanan" checked onchange="switchTipe(' + idx + ', \'layanan\')" class="text-accent"> Layanan</label>' +
                            '<label class="flex items-center gap-1 text-sm"><input type="radio" name="items[' + idx + '][tipe_item]" value="produk" onchange="switchTipe(' + idx + ', \'produk\')" class="text-accent"> Produk</label>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" onclick="removeItem(this)" class="text-sm font-medium text-red-600 hover:text-red-800">Hapus</button>' +
                '</div>' +
                '<div class="item-body" id="item-body-' + idx + '">' +
                '</div>' +
                '<input type="hidden" name="items[' + idx + '][tipe_item]" class="tipe-input">' +
                '<input type="hidden" name="items[' + idx + '][id_layanan]" class="id-layanan-input">' +
                '<input type="hidden" name="items[' + idx + '][id_produk]" class="id-produk-input">' +
                '<input type="hidden" name="items[' + idx + '][varian_dipilih]" class="varian-input">' +
                '<input type="hidden" name="items[' + idx + '][harga_saat_transaksi]" class="harga-input">' +
                '<input type="hidden" name="items[' + idx + '][qty]" class="qty-input" value="1">' +
            '</div>';
        }

        function initItemEvents(idx) {
            switchTipe(idx, 'layanan');
            validateForm();
        }

        window.switchTipe = function(idx, tipe) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            row.querySelector('.tipe-input').value = tipe;
            row.querySelector('.id-layanan-input').value = '';
            row.querySelector('.id-produk-input').value = '';
            row.querySelector('.varian-input').value = '';
            row.querySelector('.harga-input').value = '';
            row.querySelector('.qty-input').value = '1';

            var body = document.getElementById('item-body-' + idx);
            if (tipe === 'layanan') {
                body.innerHTML = buildLayananBody(idx);
                initSearchLayanan(idx);
            } else {
                body.innerHTML = buildProdukBody(idx);
                initSearchProduk(idx);
            }
            onJenisChange();
        };

        function buildLayananBody(idx) {
            return '<div class="border-b border-gray-100 pb-3 mb-3">' +
                '<label class="block text-xs font-semibold uppercase tracking-wide text-accent-text mb-1">Layanan</label>' +
                '<input type="text" placeholder="Cari layanan..." class="search-layanan block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm shadow-sm placeholder:text-text-muted focus:border-accent focus:outline-none focus:ring-accent/30" autocomplete="off">' +
                '<div class="search-layanan-results mt-1 hidden rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto"></div>' +
                '<div class="layanan-info mt-2 hidden">' +
                    '<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-gray-500">Varian</label>' +
                            '<select name="items[' + idx + '][varian_dipilih]" class="varian-select mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm placeholder:text-text-muted" onchange="onVarianChange(' + idx + ')"></select>' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-gray-500">Staf 1 (Pengerjaan) <span class="text-red-500">*</span></label>' +
                            '<select name="items[' + idx + '][id_staf_1]" class="staf1-select mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm placeholder:text-text-muted" required onchange="onStafChange(' + idx + ', 1)">' + stafOptsHtml() + '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="staf2-section mt-3" style="display:none">' +
                        '<div class="grid grid-cols-1 gap-3 sm:grid-cols-2">' +
                            '<div>' +
                                '<label class="block text-xs font-medium text-gray-500">Staf 2 (Bantuan) <span class="text-red-500">*</span></label>' +
                                '<select name="items[' + idx + '][id_staf_2]" class="staf2-select mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm placeholder:text-text-muted" onchange="onStafChange(' + idx + ', 2)">' + stafOptsHtml() + '</select>' +
                            '</div>' +
                            '<div>' +
                                '<label class="block text-xs font-medium text-gray-500">Komisi Staf 2 (Rp)</label>' +
                                '<input type="text" inputmode="numeric" name="items[' + idx + '][komisi_nominal_2]" class="komisi-input-2 mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm placeholder:text-text-muted">' +
                                '<p class="mt-0.5 text-[11px] text-gray-400 komisi-note-2"></p>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>' +
            '<div class="border-b border-gray-100 pb-3 mb-3 produk-section-container hidden">' +
                '<label class="block text-xs font-semibold uppercase tracking-wide text-emerald-600 mb-1">Produk yang Digunakan</label>' +
                '<p class="text-[11px] text-gray-400 mb-2">Pilih merk produk dan masukkan pemakaian (ml). Harga otomatis dihitung /10ml.</p>' +
                '<div class="produk-usage-list space-y-2"></div>' +
                '<button type="button" class="add-produk-usage mt-2 text-xs font-medium text-accent-text hover:text-accent" onclick="addProdukUsageRow(' + idx + ')">+ Tambah Produk</button>' +
            '</div>' +
            '<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 pb-3 mb-3">' +
                '<div>' +
                    '<label class="block text-xs font-medium text-gray-500">Ketebalan Rambut</label>' +
                    '<input type="text" name="items[' + idx + '][ketebalan_rambut]" placeholder="Contoh: Tipis, Sedang, Tebal..." class="mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm placeholder:text-text-muted">' +
                '</div>' +
                '<div class="gram-section hidden">' +
                    '<label class="block text-xs font-medium text-gray-500">Gram Pemakaian Tambahan</label>' +
                    '<input type="number" name="items[' + idx + '][gram_pemakaian_tambahan]" value="0" min="0" step="1" class="gram-input mt-1 block w-full rounded-lg border-gray-300 bg-card text-text-primary px-3 py-2 text-sm placeholder:text-text-muted" oninput="recalcHarga(' + idx + ')">' +
                    '<p class="mt-0.5 text-[11px] text-gray-400">Kelebihan gram dari pemakaian normal</p>' +
                '</div>' +
            '</div>' +
            '<div class="grid grid-cols-1 gap-3 sm:grid-cols-3">' +
                '<div>' +
                    '<label class="block text-xs font-medium text-gray-500">Harga Saat Transaksi (Rp)</label>' +
                    '<input type="number" name="items[' + idx + '][harga_saat_transaksi]" min="0" step="1000" class="harga-display mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm" onchange="syncHarga(' + idx + ')">' +
                    '<p class="mt-0.5 text-[11px] text-gray-400 harga-saran-note"></p>' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs font-medium text-gray-500">Diskon (Rp)</label>' +
                    '<input type="number" name="items[' + idx + '][diskon]" value="0" min="0" step="1000" placeholder="0" class="diskon-input mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm" oninput="recalcTotal()">' +
                '</div>' +
                '<div>' +
                    '<label class="block text-xs font-medium text-gray-500">Komisi Staf 1 (Rp)</label>' +
                    '<input type="text" inputmode="numeric" name="items[' + idx + '][komisi_nominal_1]" class="komisi-input-1 mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm">' +
                    '<p class="mt-0.5 text-[11px] text-gray-400 komisi-note-1"></p>' +
                '</div>' +
            '</div>' +
            '<div class="mt-3">' +
                '<label class="block text-xs font-medium text-gray-500">Catatan</label>' +
                '<textarea name="items[' + idx + '][catatan]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm" placeholder="Detail hasil treatment..."></textarea>' +
            '</div>';
        }

        function buildProdukBody(idx) {
            return '<input type="text" placeholder="Cari produk..." class="search-produk mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none" autocomplete="off">' +
                '<div class="search-produk-results mt-1 hidden rounded-md border border-gray-200 bg-white shadow-sm max-h-48 overflow-y-auto"></div>' +
                '<div class="produk-info mt-2 hidden">' +
                    '<div class="grid grid-cols-1 gap-3 sm:grid-cols-3">' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-gray-500">Harga / Satuan</label>' +
                            '<p class="mt-1 text-sm font-medium text-gray-900 produk-harga-display">-</p>' +
                            '<p class="mt-0.5 text-[11px] text-gray-400">Stok: <span class="produk-stok-display">0</span> <span class="produk-satuan-display"></span></p>' +
                            '<p class="mt-0.5 text-[11px] text-yellow-600 stok-warning hidden">Stok tidak mencukupi!</p>' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-gray-500 qty-label">Jumlah</label>' +
                            '<input type="number" name="items[' + idx + '][qty]" value="1" min="1" step="1" class="qty-field mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm" onchange="syncQty(' + idx + ')">' +
                        '</div>' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-gray-500">Diskon (Rp)</label>' +
                            '<input type="number" name="items[' + idx + '][diskon]" value="0" min="0" step="1000" placeholder="0" class="diskon-input mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm" oninput="recalcTotal()">' +
                        '</div>' +
                        '<div class="sm:col-span-3 rounded-lg bg-accent-light p-3">' +
                            '<label class="block text-xs font-medium text-accent-text">Total Harga</label>' +
                            '<p class="mt-1 text-lg font-bold text-text-primary produk-subtotal-display">Rp0</p>' +
                        '</div>' +
                        '<div class="sm:col-span-3">' +
                            '<label class="block text-xs font-medium text-gray-500">Catatan</label>' +
                            '<textarea name="items[' + idx + '][catatan]" rows="2" class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm" placeholder="Detail produk..."></textarea>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        }

        // --- Layanan Search & Selection ---
        function initSearchLayanan(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var input = row.querySelector('.search-layanan');
            var results = row.querySelector('.search-layanan-results');
            var timer;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.classList.add('hidden'); return; }
                timer = setTimeout(function() {
                    fetch('{{ route("api.layanan.search") }}?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.length === 0) { results.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan</div>'; results.classList.remove('hidden'); return; }
                            results.innerHTML = data.map(function(l) {
                                return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" onclick="selectLayanan(' + idx + ',' + l.id + ')">' + l.nama_layanan + ' <span class="text-xs text-text-muted">(' + l.kategori + ')</span></div>';
                            }).join('');
                            results.classList.remove('hidden');
                        });
                }, 300);
            });
        }

        window.selectLayanan = function(idx, layananId) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            row.querySelector('.id-layanan-input').value = layananId;
            row.querySelector('.search-layanan-results').classList.add('hidden');

            fetch('{{ route("api.layanan.search") }}?q=' + encodeURIComponent(row.querySelector('.search-layanan').value))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var layanan = data.find(function(l) { return l.id === layananId; });
                    if (!layanan) return;

                    row.querySelector('.search-layanan').value = layanan.nama_layanan;
                    row.dataset.namaLayanan = layanan.nama_layanan;

                    // Populate varian with labelHarga format
                    var varianSelect = row.querySelector('.varian-select');
                    varianSelect.innerHTML = '';
                    layanan.harga_layanan.forEach(function(h) {
                        var opt = document.createElement('option');
                        opt.value = h.varian;
                        var label = h.varian + ' \u2014 Rp' + fmt(h.harga_dasar_min);
                        if (parseFloat(h.harga_dasar_max) !== parseFloat(h.harga_dasar_min)) {
                            label += ' - Rp' + fmt(h.harga_dasar_max);
                        }
                        if (h.tarif_kelebihan_per_10gr) {
                            label += ' (+Rp' + fmt(h.tarif_kelebihan_per_10gr) + '/10gr kelebihan)';
                        }
                        opt.textContent = label;
                        opt.dataset.hargaMin = h.harga_dasar_min;
                        opt.dataset.hargaMax = h.harga_dasar_max;
                        opt.dataset.tarif = h.tarif_kelebihan_per_10gr || 0;
                        opt.dataset.komisiMin = h.komisi_min || 0;
                        opt.dataset.komisiMax = h.komisi_max || 0;
                        varianSelect.appendChild(opt);
                    });

                    row.querySelector('.layanan-info').classList.remove('hidden');

                    // Store produk list BEFORE adding rows so dropdown gets populated
                    row.dataset.produkList = JSON.stringify(layanan.produk || []);

                    // Populate produk section
                    var produkSection = row.querySelector('.produk-section-container');
                    var usageList = row.querySelector('.produk-usage-list');
                    usageList.innerHTML = '';

                    produkSection.classList.remove('hidden');
                    addProdukUsageRow(idx);

                    onVarianChange(idx);
                });
        };

        window.addProdukUsageRow = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var usageList = row.querySelector('.produk-usage-list');
            var pIdx = usageList.children.length;

            var html = '<div class="produk-usage-item grid grid-cols-1 gap-2 rounded-md bg-gray-50 p-2 sm:grid-cols-12 sm:items-end" data-pidx="' + pIdx + '">' +
                '<div class="sm:col-span-5">' +
                    '<label class="block whitespace-nowrap text-[11px] font-medium text-gray-500">Merk / Produk</label>' +
                    '<div class="produk-field relative mt-1">' +
                        '<input type="text" placeholder="Cari merk/produk..." autocomplete="off" class="produk-search block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-sm">' +
                        '<div class="produk-selected hidden flex min-w-0 items-center gap-1 rounded-md border border-gray-300 bg-white pr-1">' +
                            '<span class="produk-selected-name min-w-0 flex-1 truncate px-2 py-1.5 text-sm text-gray-900"></span>' +
                            '<button type="button" onclick="clearProdukUsage(this)" title="Ganti pilihan" class="shrink-0 px-1 text-base font-medium text-gray-400 hover:text-accent">&times;</button>' +
                        '</div>' +
                        '<input type="hidden" name="items[' + idx + '][produk_penggunaan][' + pIdx + '][id_produk]" class="produk-id-input">' +
                        '<div class="produk-search-results absolute z-10 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-sm max-h-40 overflow-y-auto hidden"></div>' +
                    '</div>' +
                    '<p class="ml-usage-hint mt-1 text-[11px] text-gray-400">Pilih produk terlebih dahulu untuk mengisi pemakaian.</p>' +
                '</div>' +
                '<div class="usage-detail hidden w-full sm:col-span-2">' +
                    '<label class="block whitespace-nowrap text-[11px] font-medium text-gray-500">Pemakaian (/10ml)</label>' +
                    '<input type="number" name="items[' + idx + '][produk_penggunaan][' + pIdx + '][pemakaian_ml]" value="0" min="0" step="1" class="pemakaian-ml-input mt-1 block w-full rounded-md border-gray-300 bg-white px-2 py-1.5 text-sm" oninput="recalcHarga(' + idx + ')">' +
                '</div>' +
                '<div class="w-full sm:col-span-2">' +
                    '<label class="block whitespace-nowrap text-[11px] font-medium text-gray-500">Harga/10ml</label>' +
                    '<p class="mt-1.5 whitespace-nowrap text-xs font-medium text-gray-700 produk-unit-harga">Rp0</p>' +
                '</div>' +
                '<div class="w-full sm:col-span-2">' +
                    '<label class="block whitespace-nowrap text-[11px] font-medium text-gray-500">Subtotal</label>' +
                    '<p class="mt-1.5 whitespace-nowrap text-xs font-bold text-emerald-700 produk-subtotal-display">Rp0</p>' +
                '</div>' +
                '<div class="flex items-center sm:col-span-1 mb-0.5">' +
                    '<button type="button" onclick="removeProdukUsage(this, ' + idx + ')" class="text-xs font-medium text-red-500 hover:text-red-700">Hapus</button>' +
                '</div>' +
            '</div>';

            usageList.insertAdjacentHTML('beforeend', html);
            initProdukUsageSearch(idx, pIdx);
        };

        function initProdukUsageSearch(idx, pIdx) {
            var item = document.querySelector('.produk-usage-item[data-pidx="' + pIdx + '"]');
            var input = item.querySelector('.produk-search');
            var results = item.querySelector('.produk-search-results');
            var timer;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.classList.add('hidden'); return; }
                timer = setTimeout(function() {
                    fetch('{{ route("api.produk.search") }}?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.length === 0) { results.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan</div>'; results.classList.remove('hidden'); return; }
                            results.innerHTML = data.map(function(p) {
                                var name = p.nama_produk.replace(/'/g, "\\'");
                                return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" onclick="selectProdukUsage(this,' + idx + ',' + pIdx + ',' + p.id + ',\'' + name + '\',' + p.harga_per_satuan + ',' + p.stok + ')">' +
                                    p.nama_produk + ' <span class="text-xs text-gray-400">(' + p.merek + ')</span>' +
                                '</div>';
                            }).join('');
                            results.classList.remove('hidden');
                        });
                }, 300);
            });
        }

        window.selectProdukUsage = function(el, idx, pIdx, produkId, nama, harga, stok) {
            var item = el.closest('.produk-usage-item');
            item.querySelector('.produk-id-input').value = produkId;
            var input = item.querySelector('.produk-search');
            input.value = nama;
            input.classList.add('hidden');
            var sel = item.querySelector('.produk-selected');
            sel.classList.remove('hidden');
            var selName = sel.querySelector('.produk-selected-name');
            selName.textContent = nama;
            selName.setAttribute('title', nama);
            item.querySelector('.produk-search-results').classList.add('hidden');
            item.dataset.harga = harga;
            item.querySelector('.produk-unit-harga').textContent = 'Rp' + fmt(harga);
            var detail = item.querySelector('.usage-detail');
            if (detail) detail.classList.remove('hidden');
            var hint = item.querySelector('.ml-usage-hint');
            if (hint) hint.classList.add('hidden');
            var ml = parseFloat(item.querySelector('.pemakaian-ml-input').value) || 0;
            var sub = ml > 0 ? (ml / 10) * harga : 0;
            item.querySelector('.produk-subtotal-display').textContent = 'Rp' + fmt(sub);
            recalcHarga(idx);
        };

        window.clearProdukUsage = function(btn) {
            var item = btn.closest('.produk-usage-item');
            if (!item) return;
            item.querySelector('.produk-id-input').value = '';
            var sel = item.querySelector('.produk-selected');
            if (sel) sel.classList.add('hidden');
            var input = item.querySelector('.produk-search');
            if (input) {
                input.value = '';
                input.classList.remove('hidden');
                input.focus();
            }
            item.querySelector('.produk-search-results').classList.add('hidden');
            delete item.dataset.harga;
            item.querySelector('.produk-unit-harga').textContent = 'Rp0';
            item.querySelector('.produk-subtotal-display').textContent = 'Rp0';
            var detail = item.querySelector('.usage-detail');
            if (detail) detail.classList.add('hidden');
            var hint = item.querySelector('.ml-usage-hint');
            if (hint) hint.classList.remove('hidden');
            var idx = item.closest('.item-row').dataset.idx;
            recalcHarga(idx);
        };

        window.removeProdukUsage = function(btn, idx) {
            btn.closest('.produk-usage-item').remove();
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var usageList = row.querySelector('.produk-usage-list');
            var items = usageList.querySelectorAll('.produk-usage-item');
            items.forEach(function(item, newIdx) {
                item.dataset.pidx = newIdx;
                item.querySelector('.produk-id-input').name = 'items[' + idx + '][produk_penggunaan][' + newIdx + '][id_produk]';
                item.querySelector('.pemakaian-ml-input').name = 'items[' + idx + '][produk_penggunaan][' + newIdx + '][pemakaian_ml]';
            });
            recalcHarga(idx);
        };

        window.onVarianChange = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var opt = row.querySelector('.varian-select').selectedOptions[0];
            if (!opt) return;

            row.querySelector('.varian-input').value = opt.value;
            recalcHarga(idx);

            // Show/hide gram section based on tarif availability
            var tarif = parseFloat(opt.dataset.tarif) || 0;
            var gramSection = row.querySelector('.gram-section');
            if (tarif > 0) {
                gramSection.classList.remove('hidden');
            } else {
                gramSection.classList.add('hidden');
                var gramInput = row.querySelector('.gram-input');
                if (gramInput) gramInput.value = 0;
            }

            // Update komisi notes
            updateKomisiNotes(idx);
        };

        function updateKomisiNotes(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var varianOpt = row.querySelector('.varian-select')?.selectedOptions[0];
            if (!varianOpt) return;
            var komisiMin = parseFloat(varianOpt.dataset.komisiMin) || 0;
            var komisiMax = parseFloat(varianOpt.dataset.komisiMax) || 0;
            var harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;

            var note1 = row.querySelector('.komisi-note-1');
            if (note1) note1.textContent = komisiNoteHtml(komisiMin, komisiMax, harga);

            var note2 = row.querySelector('.komisi-note-2');
            if (note2) note2.textContent = komisiNoteHtml(komisiMin, komisiMax, harga);
        }

        window.recalcHarga = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var opt = row.querySelector('.varian-select')?.selectedOptions[0];
            if (!opt) return;

            var hargaMin = parseFloat(opt.dataset.hargaMin) || 0;
            var tarif = parseFloat(opt.dataset.tarif) || 0;
            var gram = parseFloat(row.querySelector('.gram-input')?.value) || 0;

            var harga = hargaMin;
            if (gram > 0 && tarif > 0) {
                var kelipatan = Math.ceil(gram / 10);
                harga = hargaMin + (kelipatan * tarif);
            }

            // Add product usage costs
            var usageList = row.querySelector('.produk-usage-list');
            if (usageList) {
                var usageItems = usageList.querySelectorAll('.produk-usage-item');
                usageItems.forEach(function(item) {
                    var idInput = item.querySelector('.produk-id-input');
                    var mlInput = item.querySelector('.pemakaian-ml-input');
                    var subtotalEl = item.querySelector('.produk-subtotal-display');
                    if (idInput && mlInput) {
                        if (idInput.value) {
                            var hargaPerUnit = parseFloat(item.dataset.harga) || 0;
                            var ml = parseFloat(mlInput.value) || 0;
                            if (ml > 0 && hargaPerUnit > 0) {
                                var unit = ml / 10;
                                var lineCost = unit * hargaPerUnit;
                                harga += lineCost;
                                if (subtotalEl) subtotalEl.textContent = 'Rp' + fmt(lineCost);
                            } else {
                                if (subtotalEl) subtotalEl.textContent = 'Rp0';
                            }
                        } else {
                            if (subtotalEl) subtotalEl.textContent = 'Rp0';
                        }
                    }
                });
            }

            row.querySelector('.harga-display').value = Math.round(harga);
            row.querySelector('.harga-input').value = Math.round(harga);

            var note = row.querySelector('.harga-saran-note');
            var parts = [];
            if (hargaMin > 0) parts.push('Dasar Rp' + fmt(hargaMin));
            if (gram > 0 && tarif > 0) {
                var kelipatan = Math.ceil(gram / 10);
                parts.push('Kelebihan Rp' + fmt(kelipatan * tarif));
            }
            if (usageList) {
                var usageItems = usageList.querySelectorAll('.produk-usage-item');
                usageItems.forEach(function(item) {
                    var idInput = item.querySelector('.produk-id-input');
                    var mlInput = item.querySelector('.pemakaian-ml-input');
                    if (idInput && mlInput && idInput.value) {
                        var hargaPerUnit = parseFloat(item.dataset.harga) || 0;
                        var ml = parseFloat(mlInput.value) || 0;
                        if (ml > 0 && hargaPerUnit > 0) {
                            var unit = ml / 10;
                            var cost = unit * hargaPerUnit;
                            parts.push(item.querySelector('.produk-search').value + ' ' + ml + 'ml Rp' + fmt(cost));
                        }
                    }
                });
            }
            note.textContent = parts.length > 0 ? parts.join(' + ') : '';

            updateKomisiNotes(idx);
            recalcTotal();
        };

        window.syncHarga = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var val = row.querySelector('.harga-display').value || 0;
            row.querySelector('.harga-input').value = val;
            updateKomisiNotes(idx);
            recalcTotal();
        };

        // --- Produk Search & Selection (standalone) ---
        function initSearchProduk(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var input = row.querySelector('.search-produk');
            var results = row.querySelector('.search-produk-results');
            var timer;

            input.addEventListener('input', function() {
                clearTimeout(timer);
                var q = this.value.trim();
                if (q.length < 2) { results.classList.add('hidden'); return; }
                timer = setTimeout(function() {
                    fetch('{{ route("api.produk.search") }}?q=' + encodeURIComponent(q))
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.length === 0) { results.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan</div>'; results.classList.remove('hidden'); return; }
                            results.innerHTML = data.map(function(p) {
                                var stokClass = p.stok <= 0 ? 'text-red-500' : '';
                                return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" onclick="selectProduk(' + idx + ',' + p.id + ',\'' + p.nama_produk.replace(/'/g, "\\'") + '\',' + p.harga_per_satuan + ',' + p.stok + ',\'' + p.satuan + '\')">' +
                                    p.nama_produk + ' <span class="text-xs text-gray-400">(' + p.merek + ')</span>' +
                                    ' <span class="text-xs ' + stokClass + '">Stok: ' + p.stok + '</span>' +
                                '</div>';
                            }).join('');
                            results.classList.remove('hidden');
                        });
                }, 300);
            });
        }

        window.selectProduk = function(idx, produkId, nama, harga, stok, satuan) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            row.querySelector('.id-produk-input').value = produkId;
            row.querySelector('.search-produk').value = nama;
            row.querySelector('.search-produk-results').classList.add('hidden');
            row.querySelector('.produk-info').classList.remove('hidden');
            row.querySelector('.produk-harga-display').textContent = 'Rp' + fmt(harga);
            row.querySelector('.produk-stok-display').textContent = stok;
            row.querySelector('.produk-satuan-display').textContent = '/' + satuan;
            row.querySelector('.harga-input').value = harga;
            row.querySelector('.qty-field').value = 1;
            row.querySelector('.qty-input').value = 1;

            var produkSubtotal = row.querySelector('.produk-subtotal-display');
            if (produkSubtotal) produkSubtotal.textContent = 'Rp' + fmt(harga);

            var qtyLabel = row.querySelector('.qty-label');
            if (satuan === 'pcs') {
                qtyLabel.textContent = 'Jumlah dibeli (pcs)';
            } else {
                qtyLabel.textContent = 'Jumlah (' + satuan + ')';
            }

            var stokWarning = row.querySelector('.stok-warning');
            if (stok <= 0) {
                stokWarning.textContent = 'Stok habis!';
                stokWarning.classList.remove('hidden');
            } else if (stok <= 2) {
                stokWarning.textContent = 'Stok hampir habis';
                stokWarning.classList.remove('hidden');
            } else {
                stokWarning.classList.add('hidden');
            }

            syncQty(idx);
            recalcTotal();
        };

        window.syncQty = function(idx) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var qty = parseInt(row.querySelector('.qty-field').value) || 1;
            if (qty < 1) qty = 1;
            row.querySelector('.qty-field').value = qty;
            row.querySelector('.qty-input').value = qty;

            var harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;
            var produkSubtotal = row.querySelector('.produk-subtotal-display');
            if (produkSubtotal) {
                var total = harga * qty;
                produkSubtotal.textContent = 'Rp' + fmt(total);
            }

            // Check stock warning
            var stokDisplay = row.querySelector('.produk-stok-display');
            var stokWarning = row.querySelector('.stok-warning');
            if (stokDisplay && stokWarning) {
                var stok = parseFloat(stokDisplay.textContent) || 0;
                if (parseFloat(qty) > stok) {
                    stokWarning.textContent = 'Qty melebihi stok!';
                    stokWarning.classList.remove('hidden');
                } else if (stok <= 2 && parseFloat(qty) > 0) {
                    stokWarning.textContent = 'Stok hampir habis';
                    stokWarning.classList.remove('hidden');
                } else {
                    stokWarning.classList.add('hidden');
                }
            }

            recalcTotal();
        };

        window.onStafChange = function(idx, stafNum) {
            var row = document.querySelector('.item-row[data-idx="' + idx + '"]');
            var selClass = stafNum === 1 ? '.staf1-select' : '.staf2-select';
            var noteClass = stafNum === 1 ? '.komisi-note-1' : '.komisi-note-2';
            var inputClass = stafNum === 1 ? '.komisi-input-1' : '.komisi-input-2';

            var stafSelect = row.querySelector(selClass);
            var stafId = stafSelect.value;
            var staf = karyawans.find(function(k) { return k.id == stafId; });

            if (staf && staf.skema_komisi === 'persen_omset_harian') {
                var input = row.querySelector(inputClass);
                if (input) { input.value = ''; input.closest('div').style.display = 'none'; }
                var note = row.querySelector(noteClass);
                if (note) note.textContent = 'Komisi staf ini dihitung otomatis dari omset harian.';
            } else {
                var input = row.querySelector(inputClass);
                if (input) input.closest('div').style.display = '';
                updateKomisiNotes(idx);
            }
        };

        window.removeItem = function(btn) {
            btn.closest('.item-row').remove();
            var items = document.querySelectorAll('.item-row');
            items.forEach(function(row, i) {
                var num = row.querySelector('.item-number');
                if (num) num.textContent = '#' + (i + 1);
            });
            recalcTotal();
            validateForm();
        };

        function recalcTotal() {
            var total = 0;
            var subtotalTotal = 0;
            var diskonTotal = 0;
            var summaryHtml = '';
            document.querySelectorAll('.item-row').forEach(function(row, i) {
                var harga = parseFloat(row.querySelector('.harga-input')?.value) || 0;
                var qty = parseFloat(row.querySelector('.qty-input')?.value) || 1;
                var diskon = row.querySelector('.diskon-input') ? (parseFloat(row.querySelector('.diskon-input').value) || 0) : 0;
                var subtotal = harga * qty;
                var lineTotal = Math.max(0, subtotal - diskon);
                subtotalTotal += subtotal;
                diskonTotal += diskon;
                total += lineTotal;

                var nama = row.dataset.namaLayanan || (row.querySelector('.produk-harga-display') ? row.querySelector('.search-produk')?.value : '') || 'Item';
                var detailNote = row.querySelector('.harga-saran-note') ? row.querySelector('.harga-saran-note').textContent : '';

                summaryHtml += '<div class="flex justify-between text-xs text-gray-700"><span>#' + (i + 1) + ' ' + nama + '</span><span>Rp' + fmt(subtotal) + '</span></div>';
                if (qty > 1) {
                    summaryHtml += '<div class="pl-3 text-[11px] text-gray-400">Rp' + fmt(harga) + ' &times; ' + qty + '</div>';
                }
                if (detailNote) {
                    summaryHtml += '<div class="pl-3 text-[11px] text-gray-400">' + detailNote + '</div>';
                }
                if (diskon > 0) {
                    summaryHtml += '<div class="flex justify-between pl-3 text-[11px] text-red-500"><span>- diskon</span><span>-Rp' + fmt(diskon) + '</span></div>';
                }
            });
            document.getElementById('itemSummary').innerHTML = summaryHtml;
            document.getElementById('subtotalDisplay').textContent = 'Rp' + fmt(subtotalTotal);
            document.getElementById('diskonDisplay').textContent = diskonTotal > 0 ? '-Rp' + fmt(diskonTotal) : '-Rp0';
            document.getElementById('totalDisplay').textContent = 'Rp' + fmt(total);
            validateForm();
        }

        function validateForm() {
            var hasPelanggan = document.getElementById('id_pelanggan').value !== '';
            var hasItems = document.querySelectorAll('.item-row').length > 0;
            var submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = !(hasPelanggan && hasItems);
        }

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-layanan') && !e.target.closest('.search-layanan-results')) {
                document.querySelectorAll('.search-layanan-results').forEach(function(el) { el.classList.add('hidden'); });
            }
            if (!e.target.closest('.search-produk') && !e.target.closest('.search-produk-results')) {
                document.querySelectorAll('.search-produk-results').forEach(function(el) { el.classList.add('hidden'); });
            }
            if (!e.target.closest('.produk-search') && !e.target.closest('.produk-search-results')) {
                document.querySelectorAll('.produk-search-results').forEach(function(el) { el.classList.add('hidden'); });
            }
            if (!e.target.closest('#pelanggan_results') && !e.target.closest('#pelanggan_search')) {
                document.getElementById('pelanggan_results').classList.add('hidden');
            }
        });

        // Pre-add one item row
        addItem();
        validateForm();
    })();
    </script>
@endsection
