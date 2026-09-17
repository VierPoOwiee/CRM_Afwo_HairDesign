@php
    $prePelangganId = old('id_pelanggan', $appointment->id_pelanggan ?? '');
    $prePelangganNama = old('pelanggan_nama', isset($selectedPelanggan) && $selectedPelanggan ? $selectedPelanggan->nama : '');
    $preKaryawanId = old('id_karyawan', $appointment->id_karyawan ?? '');
    $preKaryawanNama = old('karyawan_nama', isset($selectedKaryawan) && $selectedKaryawan ? $selectedKaryawan->nama : '');

    $seedLayanan = collect();
    $seedLayananJson = old('layanan_chips');
    if ($seedLayananJson) {
        $decoded = json_decode($seedLayananJson, true);
        $seedLayanan = collect(is_array($decoded) ? $decoded : []);
    } else {
        $seedLayanan = $appointment->layanans
            ->map(fn ($l) => ['id' => $l->id, 'nama' => $l->nama_layanan])
            ->values();
    }
@endphp

<div class="space-y-6">
    <h2 class="text-base font-semibold text-gray-900">Data Pelanggan</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Cari Pelanggan <span class="text-red-500">*</span></label>
            <div class="mt-1 flex flex-col gap-2 sm:flex-row sm:items-center">
                <div class="relative flex-1">
                    <input type="text" id="pelanggan_search" placeholder="Ketik nama atau no WA..."
                           autocomplete="off"
                           class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                    <input type="hidden" name="id_pelanggan" id="id_pelanggan" value="{{ $prePelangganId }}">
                    <input type="hidden" name="pelanggan_nama" id="pelanggan_nama" value="{{ $prePelangganNama }}">
                    <div id="pelanggan_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
                </div>
                <button type="button" id="addPelangganBtn"
                        class="shrink-0 rounded-lg bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    + Pelanggan Baru
                </button>
            </div>
            <div id="pelanggan_selected" class="mt-2 {{ $prePelangganId && $prePelangganNama ? '' : 'hidden' }}">
                <span class="inline-flex items-center gap-1 rounded-full bg-accent-light px-3 py-1 text-xs font-medium text-accent-text">
                    <span id="pelanggan_nama_label">{{ $prePelangganNama }}</span>
                    <button type="button" id="clearPelangganBtn" class="ml-1 text-text-muted hover:text-accent">&times;</button>
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">Ketik minimal 2 huruf untuk memilih dari data pelanggan yang ada.</p>
        </div>
    </div>

    <h2 class="text-base font-semibold text-gray-900">Detail Appointment</h2>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">

        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Layanan <span class="text-red-500">*</span>
                <span class="font-normal text-gray-400">(pilih lebih dari satu bila perlu)</span>
            </label>
            <div class="relative mt-1">
                <input type="text" id="layanan_search" placeholder="Ketik nama layanan..."
                       autocomplete="off"
                       class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <div id="layanan_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
            </div>
            <div id="layanan_chips" class="mt-2 flex flex-wrap items-center gap-1.5"></div>
            <input type="hidden" name="layanan_chips" id="layanan_chips_json" value="">
            <p class="mt-1 text-xs text-gray-500" id="layanan_help">Pilih minimal satu layanan yang dipesan.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Stylist / Karyawan <span class="text-red-500">*</span></label>
            <div class="relative mt-1">
                <input type="text" id="karyawan_search" placeholder="Ketik nama stylist..."
                       autocomplete="off"
                       class="block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{ $preKaryawanId }}">
                <input type="hidden" name="karyawan_nama" id="karyawan_nama" value="{{ $preKaryawanNama }}">
                <div id="karyawan_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
            </div>
            <div id="karyawan_selected" class="mt-2 {{ $preKaryawanId && $preKaryawanNama ? '' : 'hidden' }}">
                <span class="inline-flex items-center gap-1 rounded-full bg-accent-light px-3 py-1 text-xs font-medium text-accent-text">
                    <span id="karyawan_nama_label">{{ $preKaryawanNama }}</span>
                    <button type="button" id="clearKaryawanBtn" class="ml-1 text-text-muted hover:text-accent">&times;</button>
                </span>
            </div>
            <p class="mt-1 text-xs text-gray-500">Gunakan pencarian, lalu klik hasilnya.</p>
        </div>

        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700">Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" id="tanggal"
                   value="{{ old('tanggal', $appointment->tanggal ? $appointment->tanggal->format('Y-m-d') : date('Y-m-d')) }}" required
                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
        </div>

        <div>
            <label for="jam_mulai" class="block text-sm font-medium text-gray-700">Jam Mulai <span class="text-red-500">*</span></label>
            <input type="time" name="jam_mulai" id="jam_mulai"
                   value="{{ old('jam_mulai', $appointment->jam_mulai ? $appointment->jam_mulai->format('H:i') : '10:00') }}" required
                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
            <p class="mt-1 text-xs text-gray-500">WIB (GMT+07). Jam operasional {{ App\Models\Appointment::JAM_BUKA }}:00 – {{ App\Models\Appointment::JAM_TUTUP }}:00.</p>
        </div>

        <div>
            <label for="durasi_menit" class="block text-sm font-medium text-gray-700">Durasi (menit)</label>
            <input type="number" name="durasi_menit" id="durasi_menit" min="10" max="1440" step="5"
                   value="{{ old('durasi_menit', $appointment->durasi_menit ?? 60) }}"
                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
            <p class="mt-1 text-xs text-gray-500">Dipakai untuk menghitung jam selesai.</p>
        </div>

        <div class="sm:col-span-2">
            <label for="preferensi" class="block text-sm font-medium text-gray-700">Preferensi / Catatan</label>
            <textarea name="preferensi" id="preferensi" rows="3"
                      placeholder="Contoh: Balayage nuansa ash blonde, formula bebas amonia..."
                      class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">{{ old('preferensi', $appointment->preferensi ?? '') }}</textarea>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-accent px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:brightness-95">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('appointment.index') }}"
           class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>

{{-- Modal tambah pelanggan baru --}}
<div id="pelangganModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50" style="display:none;">
    <div class="rounded-lg bg-white p-6 shadow-xl w-full max-w-md">
        <h3 class="mb-4 text-base font-semibold text-gray-900">Tambah Pelanggan Baru</h3>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
                <input type="text" id="new_pelanggan_nama" required
                       class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm placeholder:text-text-muted focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
            </div>
            <div>
                <x-phone-input name="new_pelanggan_wa" label="No. WhatsApp" placeholder="812xxxxxxx" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Kelamin</label>
                <select id="new_pelanggan_kelamin"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Rambut</label>
                <select id="new_pelanggan_rambut"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-card px-3 py-2 text-sm text-text-primary shadow-sm focus:border-accent focus:ring-2 focus:ring-accent/30 focus:outline-none">
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
(function () {
    // ----- Pelanggan combobox (reuse pola dari form Transaksi) -----
    var searchInput = document.getElementById('pelanggan_search');
    var resultsDiv = document.getElementById('pelanggan_results');
    var selectedDiv = document.getElementById('pelanggan_selected');
    var idInput = document.getElementById('id_pelanggan');
    var namaHidden = document.getElementById('pelanggan_nama');
    var namaLabel = document.getElementById('pelanggan_nama_label');
    var debounceTimer;

    function selectPelanggan(id, nama) {
        idInput.value = id;
        namaHidden.value = nama;
        namaLabel.textContent = nama;
        selectedDiv.classList.remove('hidden');
        searchInput.classList.add('hidden');
        resultsDiv.classList.add('hidden');
    }

    @if ($prePelangganId && $prePelangganNama)
    selectPelanggan('{{ $prePelangganId }}', '{{ str_replace("'", "\\'", $prePelangganNama) }}');
    @endif

    document.getElementById('clearPelangganBtn').addEventListener('click', function () {
        idInput.value = '';
        namaHidden.value = '';
        selectedDiv.classList.add('hidden');
        searchInput.classList.remove('hidden');
        searchInput.value = '';
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = this.value.trim();
        if (q.length < 2) { resultsDiv.classList.add('hidden'); return; }
        debounceTimer = setTimeout(function () {
            fetch('{{ route("api.pelanggan.search") }}?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.length) {
                        resultsDiv.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan. Klik "+ Pelanggan Baru" untuk menambah.</div>';
                        resultsDiv.classList.remove('hidden');
                        return;
                    }
                    resultsDiv.innerHTML = data.map(function (p) {
                        var nm = p.nama.replace(/'/g, "\\'");
                        return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" data-id="' + p.id + '" data-nama="' + nm + '">' + p.nama + (p.no_wa ? ' <span class="text-gray-400">(' + p.no_wa + ')</span>' : '') + '</div>';
                    }).join('');
                    resultsDiv.classList.remove('hidden');
                });
        }, 300);
    });

    resultsDiv.addEventListener('click', function (e) {
        var item = e.target.closest('[data-id]');
        if (!item) return;
        selectPelanggan(item.dataset.id, item.dataset.nama);
    });
    searchInput.addEventListener('blur', function () {
        setTimeout(function () { resultsDiv.classList.add('hidden'); }, 200);
    });

    // ----- Pelanggan baru via API -----
    var modal = document.getElementById('pelangganModal');
    document.getElementById('addPelangganBtn').addEventListener('click', function () {
        modal.style.display = 'flex';
        document.getElementById('new_pelanggan_nama').value = searchInput.value || '';
        document.getElementById('new_pelanggan_nama').focus();
    });
    document.getElementById('cancelPelangganBtn').addEventListener('click', function () {
        modal.style.display = 'none';
    });
    document.getElementById('savePelangganBtn').addEventListener('click', function () {
        var namaEl = document.getElementById('new_pelanggan_nama');
        var errEl = document.getElementById('new_pelanggan_error');
        var nama = namaEl.value.trim();
        if (!nama) {
            errEl.textContent = 'Nama wajib diisi.';
            errEl.classList.remove('hidden');
            return;
        }
        errEl.classList.add('hidden');
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
            .then(function (r) { return r.json(); })
            .then(function (p) {
                selectPelanggan(p.id, p.nama);
                modal.style.display = 'none';
                namaEl.value = '';
                var waNumber = document.getElementById('new_pelanggan_wa_number');
                if (waNumber) waNumber.value = '';
                document.getElementById('new_pelanggan_kelamin').value = '';
                document.getElementById('new_pelanggan_rambut').value = '';
            })
            .catch(function () {
                errEl.textContent = 'Gagal menyimpan pelanggan. Pastikan no. WA belum terdaftar.';
                errEl.classList.remove('hidden');
            });
    });

    // ----- Pencarian & pemilihan layanan (multi-select) -----
    var layananSearch = document.getElementById('layanan_search');
    var layananResults = document.getElementById('layanan_results');
    var layananChips = document.getElementById('layanan_chips');
    var layananChipsJson = document.getElementById('layanan_chips_json');
    var layananHelp = document.getElementById('layanan_help');
    var selectedLayanan = [];

    function addLayanan(l) {
        for (var i = 0; i < selectedLayanan.length; i++) {
            if (String(selectedLayanan[i].id) === String(l.id)) return;
        }
        selectedLayanan.push({ id: l.id, nama: l.nama });
        renderLayanan();
        layananSearch.value = '';
        layananResults.classList.add('hidden');
        layananSearch.focus();
    }

    function removeLayanan(id) {
        selectedLayanan = selectedLayanan.filter(function (l) { return String(l.id) !== String(id); });
        renderLayanan();
    }

    function renderLayanan() {
        layananChips.innerHTML = '';
        selectedLayanan.forEach(function (l) {
            var chip = document.createElement('span');
            chip.className = 'inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800';
            var nameSpan = document.createElement('span');
            nameSpan.textContent = l.nama;
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.dataset.removeLayanan = l.id;
            removeBtn.className = 'ml-1 text-amber-700 hover:text-amber-900';
            removeBtn.innerHTML = '&times;';
            chip.appendChild(nameSpan);
            chip.appendChild(removeBtn);
            layananChips.appendChild(chip);

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'id_layanan[]';
            hidden.value = l.id;
            layananChips.appendChild(hidden);
        });
        layananHelp.textContent = selectedLayanan.length ? 'Layanan terpilih: ' + selectedLayanan.length : 'Pilih minimal satu layanan yang dipesan.';
        layananChipsJson.value = JSON.stringify(selectedLayanan);
    }

    // Isi awal dari data server (edit / setelah validasi gagal)
    var initialLayanan = @json($seedLayanan);
    initialLayanan.forEach(addLayanan);

    var layananTimer;
    layananSearch.addEventListener('input', function () {
        clearTimeout(layananTimer);
        var q = this.value.trim();
        if (q.length < 1) { layananResults.classList.add('hidden'); return; }
        layananTimer = setTimeout(function () {
            fetch('{{ route("api.appointment.layanan.search") }}?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var sisa = data.filter(function (l) {
                        return !selectedLayanan.some(function (s) { return String(s.id) === String(l.id); });
                    });
                    if (!sisa.length) {
                        layananResults.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Semua hasil sudah terpilih.</div>';
                    } else {
                        layananResults.innerHTML = sisa.map(function (l) {
                            var namaSafe = String(l.nama_layanan).replace(/'/g, "\\'");
                            return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" data-id="' + l.id + '" data-nama="' + namaSafe + '">' + l.nama_layanan +
                                (l.kategori ? ' <span class="text-gray-400">(' + l.kategori + ')</span>' : '') + '</div>';
                        }).join('');
                    }
                    layananResults.classList.remove('hidden');
                });
        }, 250);
    });

    layananResults.addEventListener('click', function (e) {
        var item = e.target.closest('[data-id]');
        if (!item) return;
        addLayanan({ id: item.dataset.id, nama: item.dataset.nama });
    });
    layananSearch.addEventListener('blur', function () {
        setTimeout(function () { layananResults.classList.add('hidden'); }, 200);
    });
    layananChips.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove-layanan]');
        if (btn) removeLayanan(btn.dataset.removeLayanan);
    });

    // ----- Pencarian & pemilihan karyawan (single) -----
    var karyawanSearch = document.getElementById('karyawan_search');
    var karyawanResults = document.getElementById('karyawan_results');
    var karyawanSelected = document.getElementById('karyawan_selected');
    var karyawanId = document.getElementById('id_karyawan');
    var karyawanNamaHidden = document.getElementById('karyawan_nama');
    var karyawanNamaLabel = document.getElementById('karyawan_nama_label');

    function selectKaryawan(id, nama) {
        karyawanId.value = id;
        karyawanNamaHidden.value = nama;
        karyawanNamaLabel.textContent = nama;
        karyawanSelected.classList.remove('hidden');
        karyawanSearch.classList.add('hidden');
        karyawanResults.classList.add('hidden');
    }

    @if ($preKaryawanId && $preKaryawanNama)
    selectKaryawan('{{ $preKaryawanId }}', '{{ str_replace("'", "\\'", $preKaryawanNama) }}');
    @endif

    document.getElementById('clearKaryawanBtn').addEventListener('click', function () {
        karyawanId.value = '';
        karyawanNamaHidden.value = '';
        karyawanSelected.classList.add('hidden');
        karyawanSearch.classList.remove('hidden');
        karyawanSearch.value = '';
    });

    var karyawanTimer;
    karyawanSearch.addEventListener('input', function () {
        clearTimeout(karyawanTimer);
        var q = this.value.trim();
        if (q.length < 1) { karyawanResults.classList.add('hidden'); return; }
        karyawanTimer = setTimeout(function () {
            fetch('{{ route("api.appointment.karyawan.search") }}?q=' + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.length) {
                        karyawanResults.innerHTML = '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan.</div>';
                    } else {
                        var rows = '';
                        data.forEach(function (k) {
                            var namaSafe = String(k.nama).replace(/'/g, "\\'");
                            rows += '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" data-id="' + k.id + '" data-nama="' + namaSafe + '">' + k.nama + '</div>';
                        });
                        karyawanResults.innerHTML = rows;
                    }
                    karyawanResults.classList.remove('hidden');
                });
        }, 250);
    });

    karyawanResults.addEventListener('click', function (e) {
        var item = e.target.closest('[data-id]');
        if (!item) return;
        selectKaryawan(item.dataset.id, item.dataset.nama);
    });
    karyawanSearch.addEventListener('blur', function () {
        setTimeout(function () { karyawanResults.classList.add('hidden'); }, 200);
    });

    // Simpan snapshot layanan terpilih saat submit, supaya tetap ada bila validasi gagal.
    var form = document.getElementById('appointmentForm');
    if (form) {
        form.addEventListener('submit', function () {
            layananChipsJson.value = JSON.stringify(selectedLayanan);
        });
    }
})();
</script>