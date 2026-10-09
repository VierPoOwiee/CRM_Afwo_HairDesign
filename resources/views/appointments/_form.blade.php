@php
    $prePelangganId = old('id_pelanggan', $appointment->id_pelanggan ?? '');
    $prePelangganNama = old('pelanggan_nama', isset($selectedPelanggan) && $selectedPelanggan ? $selectedPelanggan->nama : '');
    $preKaryawanId = old('id_karyawan', $appointment->id_karyawan ?? '');
    $preKaryawanNama = old('karyawan_nama', isset($selectedKaryawan) && $selectedKaryawan ? $selectedKaryawan->nama : '');
    $preTanggal = old('tanggal', $appointment->tanggal ? $appointment->tanggal->format('Y-m-d') : date('Y-m-d'));
    $preJam = old('jam_mulai', $appointment->jam_mulai ? $appointment->jam_mulai->format('H:i') : '10:00');
    $slotWaktu = ['09:00', '10:00', '10:30', '13:00', '14:30', '16:00', '17:30', '19:00'];

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

<div class="form-fields-stack">

    <!-- Row 1: Pilih Klien / Pelanggan -->
    <div class="form-group-item">
        <div class="label-with-action-row">
            <span class="form-label">Pilih Klien / Pelanggan <span class="required-star">*</span></span>
            <button type="button" id="addPelangganBtn" class="form-inline-action-link" style="background:none;border:none;padding:0;cursor:pointer;">+ Tambah Pelanggan Baru</button>
        </div>
        <div class="form-input-box" id="pelanggan_input_box">
            <div class="appt-combobox-wrap">
                <input type="text" id="pelanggan_search" class="appt-combobox-input" placeholder="-- Pilih Klien Terdaftar / ketik nama atau no WA --" autocomplete="off">
                <div id="pelanggan_results" class="appt-combobox-results"></div>
            </div>
        </div>
        <input type="hidden" name="id_pelanggan" id="id_pelanggan" value="{{ $prePelangganId }}">
        <input type="hidden" name="pelanggan_nama" id="pelanggan_nama" value="{{ $prePelangganNama }}">
        <div id="pelanggan_selected" class="appt-slot-row {{ $prePelangganId && $prePelangganNama ? '' : 'hidden' }}" style="margin-top: 8px;">
            <span class="appt-picked-chip">
                <span id="pelanggan_nama_label">{{ $prePelangganNama }}</span>
                <button type="button" id="clearPelangganBtn" class="remove" title="Hapus pilihan">&times;</button>
            </span>
        </div>
        <span class="appt-form-hint">Ketik minimal 2 huruf untuk memilih dari data pelanggan yang ada.</span>
    </div>

    <!-- Row 2: Layanan & Karyawan (2 Columns) -->
    <div class="form-grid-2col">

        <div class="form-group-item">
            <span class="form-label">Layanan yang Akan Digunakan <span class="required-star">*</span></span>
            <div class="form-input-box">
                <div class="appt-combobox-wrap">
                    <input type="text" id="layanan_search" class="appt-combobox-input" placeholder="-- Pilih Layanan / ketik nama layanan --" autocomplete="off">
                    <div id="layanan_results" class="appt-combobox-results"></div>
                </div>
            </div>
            <div id="layanan_chips" class="appt-slot-row" style="margin-top:8px;"></div>
            <input type="hidden" name="layanan_chips" id="layanan_chips_json" value="">
            <span class="appt-form-hint" id="layanan_help">Pilih minimal satu layanan yang dipesan.</span>
        </div>

        <div class="form-group-item">
            <span class="form-label">Karyawan / Stylist Dikerahkan <span class="required-star">*</span></span>
            <div class="form-input-box" id="karyawan_input_box">
                <div class="appt-combobox-wrap">
                    <input type="text" id="karyawan_search" class="appt-combobox-input" placeholder="-- Pilih Stylist / ketik nama --" autocomplete="off">
                    <div id="karyawan_results" class="appt-combobox-results"></div>
                </div>
            </div>
            <input type="hidden" name="id_karyawan" id="id_karyawan" value="{{ $preKaryawanId }}">
            <input type="hidden" name="karyawan_nama" id="karyawan_nama" value="{{ $preKaryawanNama }}">
            <div id="karyawan_selected" class="appt-slot-row {{ $preKaryawanId && $preKaryawanNama ? '' : 'hidden' }}" style="margin-top: 8px;">
                <span class="appt-picked-chip">
                    <span id="karyawan_nama_label">{{ $preKaryawanNama }}</span>
                    <button type="button" id="clearKaryawanBtn" class="remove" title="Hapus pilihan">&times;</button>
                </span>
            </div>
        </div>

    </div>

    <!-- Row 3: Tanggal & Jam (2 Columns) -->
    <div class="form-grid-2col">

        <div class="form-group-item">
            <label class="form-label" for="tanggal">Tanggal Kehadiran <span class="required-star">*</span></label>
            <div class="form-input-box">
                <input type="date" id="tanggal" name="tanggal" class="form-input-field" value="{{ $preTanggal }}" required>
            </div>
        </div>

        <div class="form-group-item">
            <label class="form-label" for="jam_mulai">Jam Kedatangan <span class="required-star">*</span></label>
            <div class="form-input-box">
                <input type="time" id="jam_mulai" name="jam_mulai" class="form-input-field" value="{{ $preJam }}" required>
            </div>
        </div>

    </div>

    <!-- Quick Time Slot Selection Pills -->
    <div class="form-group-item time-slot-quick-picker">
        <span class="quick-slot-label">Pilihan Slot Waktu Cepat:</span>
        <div class="slot-pills-row">
            @foreach ($slotWaktu as $slot)
                <button type="button" class="slot-pill-btn {{ $preJam === $slot ? 'active' : '' }}" data-time="{{ $slot }}">{{ $slot }}</button>
            @endforeach
        </div>
    </div>

    <!-- Row 4: Estimasi Durasi -->
    <div class="form-grid-2col">
        <div class="form-group-item">
            <label class="form-label" for="durasi_menit">Estimasi Durasi Pengerjaan</label>
            <div class="form-input-box">
                <input type="number" id="durasi_menit" name="durasi_menit" class="form-input-field" value="{{ old('durasi_menit', $appointment->durasi_menit ?? 60) }}" min="10" max="1440" step="5">
                <span class="input-suffix-text">Menit</span>
            </div>
            <span class="appt-form-hint">Dipakai untuk menghitung jam selesai. WIB (GMT+07). Jam operasional {{ App\Models\Appointment::JAM_BUKA }}:00 – {{ App\Models\Appointment::JAM_TUTUP }}:00.</span>
        </div>
    </div>

    <!-- Row 5: Catatan Khusus -->
    <div class="form-group-item">
        <label class="form-label" for="preferensi">Catatan Khusus / Preferensi Klien</label>
        <div class="form-input-box">
            <textarea id="preferensi" name="preferensi" class="form-textarea" rows="3" placeholder="Contoh: Klien minta formula non-amonia, alergi produk tertentu...">{{ old('preferensi', $appointment->preferensi ?? '') }}</textarea>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="form-actions-row">
        <a href="{{ route('appointment.index') }}" id="btn-cancel" class="btn btn-secondary">Batal</a>
        <button type="submit" id="btn-save-appointment" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ $submitLabel }}</span>
        </button>
    </div>

</div>

{{-- Modal tambah pelanggan baru --}}
<div id="pelangganModal" class="modal-backdrop-plain" role="dialog" aria-modal="true" aria-labelledby="modal-title-pelanggan">
    <div class="modal-card-plain">
        <div class="modal-header-row">
            <h3 class="modal-header-title" id="modal-title-pelanggan">Tambah Pelanggan Baru</h3>
            <button type="button" class="btn-close-modal" id="closePelangganModal" aria-label="Tutup">&times;</button>
        </div>
        <div class="form-fields-stack">
            <div class="form-group-item">
                <label class="form-label" for="new_pelanggan_nama">Nama <span class="required-star">*</span></label>
                <input type="text" id="new_pelanggan_nama" class="text-input-field" placeholder="Nama lengkap pelanggan">
            </div>
            <div class="form-group-item">
                <x-phone-input name="new_pelanggan_wa" label="No. WhatsApp" placeholder="812xxxxxxx" />
            </div>
            <div class="form-group-item">
                <label class="form-label" for="new_pelanggan_kelamin">Jenis Kelamin</label>
                <select id="new_pelanggan_kelamin" class="select-dropdown-field">
                    <option value="">-- Pilih --</option>
                    <option value="L">Laki-laki</option>
                    <option value="P">Perempuan</option>
                </select>
            </div>
            <div class="form-group-item">
                <label class="form-label" for="new_pelanggan_rambut">Jenis Rambut</label>
                <select id="new_pelanggan_rambut" class="select-dropdown-field">
                    <option value="">-- Pilih --</option>
                    <option value="Lurus">Lurus</option>
                    <option value="Ikal">Ikal</option>
                    <option value="Bergelombang">Bergelombang</option>
                    <option value="Keriting">Keriting</option>
                </select>
            </div>
        </div>
        <div id="new_pelanggan_error" class="modal-error-plain hidden"></div>
        <div class="modal-actions-plain">
            <button type="button" id="cancelPelangganBtn" class="btn btn-secondary">Batal</button>
            <button type="button" id="savePelangganBtn" class="btn btn-primary">Simpan</button>
        </div>
    </div>
</div>

<script>
(function () {
    var CSRF = '{{ csrf_token() }}';
    var URL_PELANGGAN_SEARCH = '{{ route("api.pelanggan.search") }}?q=';
    var URL_PELANGGAN_STORE = '{{ route("api.pelanggan.store") }}';
    var URL_LAYANAN_SEARCH = '{{ route("api.appointment.layanan.search") }}?q=';
    var URL_KARYAWAN_SEARCH = '{{ route("api.appointment.karyawan.search") }}?q=';

    function showRes(box, show) {
        box.classList.toggle('open', show);
    }

    // ----- Pelanggan combobox -----
    var searchInput = document.getElementById('pelanggan_search');
    var inputBox = document.getElementById('pelanggan_input_box');
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
        inputBox.classList.add('hidden');
        showRes(resultsDiv, false);
        searchInput.value = '';
    }

    @if ($prePelangganId && $prePelangganNama)
    selectPelanggan('{{ $prePelangganId }}', '{{ str_replace("'", "\\'", $prePelangganNama) }}');
    @endif

    document.getElementById('clearPelangganBtn').addEventListener('click', function () {
        idInput.value = '';
        namaHidden.value = '';
        selectedDiv.classList.add('hidden');
        inputBox.classList.remove('hidden');
        searchInput.focus();
    });

    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        var q = this.value.trim();
        if (q.length < 2) { showRes(resultsDiv, false); return; }
        debounceTimer = setTimeout(function () {
            fetch(URL_PELANGGAN_SEARCH + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.length) {
                        resultsDiv.innerHTML = '<div class="appt-combobox-empty">Tidak ditemukan. Klik "+ Tambah Pelanggan Baru".</div>';
                    } else {
                        resultsDiv.innerHTML = data.map(function (p) {
                            var nm = p.nama.replace(/'/g, "\\'");
                            return '<div class="appt-combobox-option" data-id="' + p.id + '" data-nama="' + nm + '">' + p.nama + (p.no_wa ? ' <span class="sub">(' + p.no_wa + ')</span>' : '') + '</div>';
                        }).join('');
                    }
                    showRes(resultsDiv, true);
                });
        }, 300);
    });

    resultsDiv.addEventListener('click', function (e) {
        var item = e.target.closest('[data-id]');
        if (!item) return;
        selectPelanggan(item.dataset.id, item.dataset.nama);
    });
    searchInput.addEventListener('blur', function () {
        setTimeout(function () { showRes(resultsDiv, false); }, 200);
    });

    // ----- Pelanggan baru via API -----
    var modal = document.getElementById('pelangganModal');
    function openModal() { modal.classList.add('show'); }
    function closeModal() { modal.classList.remove('show'); }
    document.getElementById('addPelangganBtn').addEventListener('click', function () {
        document.getElementById('new_pelanggan_nama').value = searchInput.value || '';
        openModal();
        setTimeout(function () { document.getElementById('new_pelanggan_nama').focus(); }, 50);
    });
    document.getElementById('closePelangganModal').addEventListener('click', closeModal);
    document.getElementById('cancelPelangganBtn').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeModal();
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
        fetch(URL_PELANGGAN_STORE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
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
                closeModal();
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

    function textChip(label, removeId) {
        return '<span class="appt-picked-chip" style="background-color:var(--gold-100); border-color:var(--gold-300); color:var(--text-link);">' +
            '<span>' + label + '</span>' +
            '<button type="button" class="remove" data-remove-layanan="' + removeId + '" title="Hapus">&times;</button></span>';
    }

    function addLayanan(l) {
        for (var i = 0; i < selectedLayanan.length; i++) {
            if (String(selectedLayanan[i].id) === String(l.id)) return;
        }
        selectedLayanan.push({ id: l.id, nama: l.nama });
        renderLayanan();
        layananSearch.value = '';
        showRes(layananResults, false);
        layananSearch.focus();
    }

    function removeLayanan(id) {
        selectedLayanan = selectedLayanan.filter(function (l) { return String(l.id) !== String(id); });
        renderLayanan();
    }

    function renderLayanan() {
        layananChips.innerHTML = '';
        selectedLayanan.forEach(function (l) {
            layananChips.innerHTML += textChip(l.nama, l.id);
            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'id_layanan[]';
            hidden.value = l.id;
            layananChips.appendChild(hidden);
        });
        layananHelp.textContent = selectedLayanan.length ? 'Layanan terpilih: ' + selectedLayanan.length : 'Pilih minimal satu layanan yang dipesan.';
        layananChipsJson.value = JSON.stringify(selectedLayanan);
    }

    var initialLayanan = @json($seedLayanan);
    initialLayanan.forEach(addLayanan);

    var layananTimer;
    layananSearch.addEventListener('input', function () {
        clearTimeout(layananTimer);
        var q = this.value.trim();
        if (q.length < 1) { showRes(layananResults, false); return; }
        layananTimer = setTimeout(function () {
            fetch(URL_LAYANAN_SEARCH + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    var sisa = data.filter(function (l) {
                        return !selectedLayanan.some(function (s) { return String(s.id) === String(l.id); });
                    });
                    if (!sisa.length) {
                        layananResults.innerHTML = '<div class="appt-combobox-empty">Semua hasil sudah terpilih.</div>';
                    } else {
                        layananResults.innerHTML = sisa.map(function (l) {
                            var namaSafe = String(l.nama_layanan).replace(/'/g, "\\'");
                            return '<div class="appt-combobox-option" data-id="' + l.id + '" data-nama="' + namaSafe + '">' + l.nama_layanan +
                                (l.kategori ? ' <span class="sub">(' + l.kategori + ')</span>' : '') + '</div>';
                        }).join('');
                    }
                    showRes(layananResults, true);
                });
        }, 250);
    });

    layananResults.addEventListener('click', function (e) {
        var item = e.target.closest('[data-id]');
        if (!item) return;
        addLayanan({ id: item.dataset.id, nama: item.dataset.nama });
    });
    layananSearch.addEventListener('blur', function () {
        setTimeout(function () { showRes(layananResults, false); }, 200);
    });
    layananChips.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-remove-layanan]');
        if (btn) removeLayanan(btn.dataset.removeLayanan);
    });

    // ----- Pencarian & pemilihan karyawan (single) -----
    var karyawanSearch = document.getElementById('karyawan_search');
    var karyawanInputBox = document.getElementById('karyawan_input_box');
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
        karyawanInputBox.classList.add('hidden');
        showRes(karyawanResults, false);
        karyawanSearch.value = '';
    }

    @if ($preKaryawanId && $preKaryawanNama)
    selectKaryawan('{{ $preKaryawanId }}', '{{ str_replace("'", "\\'", $preKaryawanNama) }}');
    @endif

    document.getElementById('clearKaryawanBtn').addEventListener('click', function () {
        karyawanId.value = '';
        karyawanNamaHidden.value = '';
        karyawanSelected.classList.add('hidden');
        karyawanInputBox.classList.remove('hidden');
        karyawanSearch.focus();
    });

    var karyawanTimer;
    karyawanSearch.addEventListener('input', function () {
        clearTimeout(karyawanTimer);
        var q = this.value.trim();
        if (q.length < 1) { showRes(karyawanResults, false); return; }
        karyawanTimer = setTimeout(function () {
            fetch(URL_KARYAWAN_SEARCH + encodeURIComponent(q))
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.length) {
                        karyawanResults.innerHTML = '<div class="appt-combobox-empty">Tidak ditemukan.</div>';
                    } else {
                        var rows = '';
                        data.forEach(function (k) {
                            var namaSafe = String(k.nama).replace(/'/g, "\\'");
                            rows += '<div class="appt-combobox-option" data-id="' + k.id + '" data-nama="' + namaSafe + '">' + k.nama + '</div>';
                        });
                        karyawanResults.innerHTML = rows;
                    }
                    showRes(karyawanResults, true);
                });
        }, 250);
    });

    karyawanResults.addEventListener('click', function (e) {
        var item = e.target.closest('[data-id]');
        if (!item) return;
        selectKaryawan(item.dataset.id, item.dataset.nama);
    });
    karyawanSearch.addEventListener('blur', function () {
        setTimeout(function () { showRes(karyawanResults, false); }, 200);
    });

    // ----- Quick slot waktu -----
    var jamInput = document.getElementById('jam_mulai');
    document.querySelectorAll('.slot-pill-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.slot-pill-btn').forEach(function (b) { b.classList.remove('active'); });
            btn.classList.add('active');
            jamInput.value = btn.dataset.time;
        });
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