<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700">Hari / Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', isset($appointment) && $appointment->tanggal ? $appointment->tanggal->format('Y-m-d') : date('Y-m-d')) }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <div>
            <label for="waktu" class="block text-sm font-medium text-gray-700">Waktu (WITA) <span class="text-red-500">*</span></label>
            <select name="waktu" id="waktu" required
                    class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <option value="">-- Pilih Waktu (WITA) --</option>
            </select>
            <p id="kuota_info" class="mt-1 text-xs font-medium text-text-muted"></p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Nama Pelanggan <span class="text-red-500">*</span></label>
            <div class="mt-1 flex gap-2">
                <div class="relative flex-1 min-w-0">
                    <input type="text" id="nama_search" autocomplete="off" required
                           placeholder="Ketik nama pelanggan..."
                           value="{{ old('nama', $appointment->nama ?? '') }}"
                           class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                    <input type="hidden" name="id_pelanggan" id="nama_id" value="{{ old('id_pelanggan') }}">
                    <input type="hidden" name="nama" id="nama_hidden" value="{{ old('nama', $appointment->nama ?? '') }}" required>
                    <div id="nama_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
                </div>
                <button type="button" id="addPelangganBtn"
                        class="shrink-0 rounded-lg bg-card px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    + Pelanggan Baru
                </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Ketik minimal 2 huruf untuk memilih dari data pelanggan yang ada.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Service / Layanan <span class="text-red-500">*</span></label>
            <div class="mt-1 relative">
                <input type="text" id="service_search" autocomplete="off"
                       placeholder="Ketik nama layanan..."
                       class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <div id="service_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
            </div>
            <div id="service_selected" class="mt-2 flex flex-wrap gap-2"></div>
            <p class="mt-1 text-xs text-gray-500">Ketik minimal 2 huruf, lalu pilih layanan. Bisa memilih beberapa layanan dalam satu booking.</p>
            <p id="kategori_msg" class="mt-1 text-xs font-medium text-danger hidden"></p>
        </div>

        <div>
            <x-phone-input
                name="no_wa"
                label="No. WA"
                :value="$appointment->no_wa ?? ''"
                placeholder="812xxxxxxx" />
            <p class="mt-1 text-xs text-gray-500">Maks. 13 digit. Otomatis terisi saat pelanggan dipilih.</p>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
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
                <input type="text" id="new_pelanggan_nama"
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
        <div id="new_pelanggan_error" class="mt-3 hidden text-sm text-red-600"></div>
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
    var nameInput = document.getElementById('nama_search');
    var nameId    = document.getElementById('nama_id');
    var nameHid   = document.getElementById('nama_hidden');
    var nameRes   = document.getElementById('nama_results');
    var svcInput  = document.getElementById('service_search');
    var svcRes    = document.getElementById('service_results');
    var selectedServices = [];

    function bindSearch(input, hidden, results, url, render, minLen) {
        var t;
        input.addEventListener('input', function() {
            clearTimeout(t);
            var q = this.value.trim();
            hidden.value = q;
            if (q.length < minLen) { results.classList.add('hidden'); return; }
            t = setTimeout(function() {
                fetch(url + encodeURIComponent(q))
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        results.innerHTML = data.length === 0
                            ? '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan. Lanjutkan ketik manual jika perlu.</div>'
                            : data.map(render).join('');
                        results.classList.remove('hidden');
                    });
            }, 300);
        });
        input.addEventListener('blur', function() {
            setTimeout(function() { results.classList.add('hidden'); }, 200);
        });
    }

    bindSearch(nameInput, nameHid, nameRes,
        '{{ route("api.pelanggan.search") }}?q=',
        function(p) {
            var s = p.nama.replace(/'/g, "\\'");
            var wa = (p.no_wa || '').replace(/'/g, "\\'");
            var note = p.no_wa ? ' <span class="text-gray-400">' + p.no_wa + '</span>' : '';
            return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" data-id="' + p.id + '" data-nama="' + s + '" data-wa="' + wa + '">' + p.nama + note + '</div>';
        }, 2);

    nameRes.addEventListener('click', function(e) {
        var el = e.target.closest('[data-id]');
        if (!el) return;
        nameId.value = el.dataset.id;
        nameHid.value = el.dataset.nama;
        nameInput.value = el.dataset.nama;
        nameRes.classList.add('hidden');
        if (el.dataset.wa) {
            var waNumber = document.getElementById('no_wa_number');
            if (waNumber) { waNumber.value = el.dataset.wa.replace(/^\+/, '').replace(/\D/g, ''); waNumber.dispatchEvent(new Event('input')); }
        }
    });
    nameRes.addEventListener('mousedown', function(e) {
        if (e.target.closest('[data-id]')) { e.preventDefault(); }
    });

    function renderSelectedServices() {
        var container = document.getElementById('service_selected');
        container.innerHTML = '';
        selectedServices.forEach(function(service, idx) {
            var chip = document.createElement('div');
            chip.className = 'inline-flex items-center gap-1 rounded-full bg-accent-light px-3 py-1 text-xs font-medium text-accent-text';

            var inputNama = document.createElement('input');
            inputNama.type = 'hidden';
            inputNama.name = 'service[]';
            inputNama.value = service.nama;

            var inputKategori = document.createElement('input');
            inputKategori.type = 'hidden';
            inputKategori.name = 'kategori[]';
            inputKategori.value = service.kategori;

            var label = document.createElement('span');
            label.textContent = service.nama;
            if (service.kategori) {
                var kat = document.createElement('span');
                kat.className = 'text-text-muted';
                kat.textContent = ' (' + service.kategori + ')';
                label.appendChild(kat);
            }

            var btnHapus = document.createElement('button');
            btnHapus.type = 'button';
            btnHapus.className = 'ml-1 text-text-muted hover:text-accent';
            btnHapus.innerHTML = '&times;';
            btnHapus.setAttribute('title', 'Hapus layanan');
            btnHapus.addEventListener('click', function() {
                selectedServices.splice(idx, 1);
                renderSelectedServices();
                kategoriMsg.classList.add('hidden');
            });

            chip.appendChild(inputNama);
            chip.appendChild(inputKategori);
            chip.appendChild(label);
            chip.appendChild(btnHapus);
            container.appendChild(chip);
        });
    }

    function initSelectedServices() {
        var initialServices = @json(old('service', $appointment->service ?? []));
        var initialKategori = @json(old('kategori', $appointment->kategori ?? []));

        if (!Array.isArray(initialServices)) {
            initialServices = initialServices && initialServices !== '' && initialServices !== null ? [initialServices] : [];
        }
        if (!Array.isArray(initialKategori)) {
            initialKategori = initialKategori && initialKategori !== '' && initialKategori !== null ? [initialKategori] : [];
        }

        initialServices.forEach(function(nama, i) {
            if (typeof nama === 'string' && nama.trim() !== '') {
                selectedServices.push({
                    nama: nama.trim(),
                    kategori: typeof initialKategori[i] === 'string' ? initialKategori[i] : ''
                });
            }
        });
        renderSelectedServices();
    }

    var svcTimer;
    svcInput.addEventListener('input', function() {
        clearTimeout(svcTimer);
        var q = this.value.trim();
        if (q.length < 2) { svcRes.classList.add('hidden'); return; }
        svcTimer = setTimeout(function() {
            fetch('{{ route("api.layanan.search") }}?q=' + encodeURIComponent(q))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    svcRes.innerHTML = data.length === 0
                        ? '<div class="px-3 py-2 text-sm text-gray-400">Tidak ditemukan.</div>'
                        : data.map(function(l) {
                            var s = l.nama_layanan.replace(/'/g, "\\'");
                            var k = (l.kategori || '').replace(/'/g, "\\'");
                            var exists = selectedServices.some(function(sv) { return sv.nama === l.nama_layanan; });
                            return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light' + (exists ? ' opacity-40 pointer-events-none' : '') + '" data-nama="' + s + '" data-kategori="' + k + '">'
                                + l.nama_layanan + ' <span class="text-xs text-text-muted">(' + l.kategori + ')</span></div>';
                        }).join('');
                    svcRes.classList.remove('hidden');
                });
        }, 300);
    });

    svcInput.addEventListener('blur', function() {
        setTimeout(function() { svcRes.classList.add('hidden'); }, 200);
    });

    svcRes.addEventListener('click', function(e) {
        var el = e.target.closest('[data-nama]');
        if (!el) return;
        var nama = el.dataset.nama;
        var kategori = el.dataset.kategori || '';
        if (selectedServices.some(function(sv) { return sv.nama === nama; })) return;
        selectedServices.push({ nama: nama, kategori: kategori });
        renderSelectedServices();
        svcInput.value = '';
        svcRes.classList.add('hidden');
        kategoriMsg.classList.add('hidden');
        svcInput.focus();
    });
    svcRes.addEventListener('mousedown', function(e) {
        if (e.target.closest('[data-nama]')) { e.preventDefault(); }
    });

    var tanggInput = document.getElementById('tanggal');
    var waktuInput = document.getElementById('waktu');
    var kuotaInfo = document.getElementById('kuota_info');
    var formEl = document.querySelector('form[data-appointment-id]');
    var kategoriMsg = document.getElementById('kategori_msg');
    var currentWaktu = '{{ old("waktu", $appointment->waktu ?? "") }}';
    if (currentWaktu && currentWaktu.indexOf(':') !== -1) {
        currentWaktu = currentWaktu.split(':').slice(0, 2).join(':');
    }
    var maksGlobal = 0;

    function setKuotaClass(cls) {
        kuotaInfo.className = 'mt-1 text-xs font-medium ' + cls;
    }

    function updateKuotaInfo(waktu, sisa, maks) {
        kuotaInfo.textContent = 'Kuota jam ' + waktu + ' WITA: sisa ' + sisa + ' dari ' + maks;
        var persen = maks > 0 ? sisa / maks : 0;
        if (persen > 0.5) {
            setKuotaClass('text-success');
        } else if (persen > 0.2) {
            setKuotaClass('text-success opacity-60');
        } else {
            setKuotaClass('text-danger');
        }
    }

    function clearKuotaInfo() {
        kuotaInfo.textContent = '';
        setKuotaClass('text-text-muted');
    }

    function renderSlotKuota(data) {
        var slots = data.slots || {};
        var maks = 0;
        for (var w in slots) { if (slots[w] > maks) maks = slots[w]; }
        maksGlobal = maks;

        waktuInput.innerHTML = '<option value="">-- Pilih Waktu (WITA) --</option>';
        for (var w in slots) {
            var sisa = slots[w];
            var opt = document.createElement('option');
            opt.value = w;
            opt.setAttribute('data-sisa', sisa);
            opt.textContent = (sisa <= 0) ? w + ' WITA (habis)' : w + ' WITA';
            if (sisa <= 0 && w !== currentWaktu) {
                opt.disabled = true;
            }
            waktuInput.appendChild(opt);
        }

        if (currentWaktu) {
            var hi = false;
            for (var i = 0; i < waktuInput.options.length; i++) {
                if (waktuInput.options[i].value === currentWaktu) { hi = true; break; }
            }
            if (hi) { waktuInput.value = currentWaktu; }
        }

        var sel = waktuInput.value;
        if (sel && slots[sel] !== undefined) {
            updateKuotaInfo(sel, slots[sel], maks);
        } else {
            clearKuotaInfo();
        }
    }

    function fillFallbackSlots() {
        var slots = {};
        for (var h = 9; h <= 18; h++) {
            var mm = (h < 10 ? '0' : '') + h;
            slots[mm + ':00'] = 10;
            if (h < 18) { slots[mm + ':30'] = 10; }
        }
        renderSlotKuota({ slots: slots });
    }

    function loadSlotKuota() {
        var tanggal = tanggInput.value;
        if (!tanggal) {
            waktuInput.innerHTML = '<option value="">-- Pilih Waktu (WITA) --</option>';
            clearKuotaInfo();
            return;
        }
        var excludeId = formEl ? formEl.getAttribute('data-appointment-id') : '';
        var url = '{{ route("api.appointment.slot-kuota") }}?tanggal=' + encodeURIComponent(tanggal)
            + '&exclude_id=' + encodeURIComponent(excludeId);
        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.slots || Object.keys(data.slots).length === 0) {
                    fillFallbackSlots();
                    return;
                }
                renderSlotKuota(data);
            })
            .catch(fillFallbackSlots);
    }

    waktuInput.addEventListener('change', function() {
        var sel = this.value;
        var opt = this.options[this.selectedIndex];
        var sisa = opt ? parseInt(opt.getAttribute('data-sisa') || '', 10) : null;
        if (sel && !isNaN(sisa)) {
            updateKuotaInfo(sel, sisa, maksGlobal);
        } else {
            clearKuotaInfo();
        }
    });

    tanggInput.addEventListener('change', loadSlotKuota);
    loadSlotKuota();
    initSelectedServices();

    if (formEl) {
        var submitBtn = formEl.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                if (nameHid.value.trim() === '' && nameInput.value.trim() !== '') {
                    nameHid.value = nameInput.value.trim();
                }
            });
        }
        formEl.addEventListener('submit', function(e) {
            if (selectedServices.length === 0) {
                e.preventDefault();
                kategoriMsg.textContent = 'Pilih minimal satu layanan dari daftar yang muncul, supaya kategori & kuota bisa dihitung otomatis';
                kategoriMsg.classList.remove('hidden');
                svcInput.focus();
                return;
            }
            kategoriMsg.classList.add('hidden');
        });
    }

    var modal = document.getElementById('pelangganModal');
    var pelangganErr = document.getElementById('new_pelanggan_error');

    document.getElementById('addPelangganBtn').addEventListener('click', function() {
        pelangganErr.classList.add('hidden');
        modal.style.display = 'flex';
        var namaModal = document.getElementById('new_pelanggan_nama');
        namaModal.value = nameInput.value || '';
        namaModal.focus();
    });

    document.getElementById('cancelPelangganBtn').addEventListener('click', function() {
        modal.style.display = 'none';
    });

    document.getElementById('savePelangganBtn').addEventListener('click', function() {
        var nama = document.getElementById('new_pelanggan_nama').value.trim();
        if (!nama) {
            pelangganErr.textContent = 'Nama wajib diisi.';
            pelangganErr.classList.remove('hidden');
            return;
        }
        pelangganErr.classList.add('hidden');

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
            nameId.value = p.id;
            nameHid.value = p.nama;
            nameInput.value = p.nama;
            nameRes.classList.add('hidden');
            var noWa = document.getElementById('no_wa_number');
            if (noWa && p.no_wa) {
                noWa.value = p.no_wa.replace(/^\+/, '').replace(/\D/g, '');
                noWa.dispatchEvent(new Event('input'));
            }
            modal.style.display = 'none';
            document.getElementById('new_pelanggan_nama').value = '';
            var waNumber = document.getElementById('new_pelanggan_wa_number');
            if (waNumber) {
                waNumber.value = '';
                waNumber.dispatchEvent(new Event('input'));
            }
            document.getElementById('new_pelanggan_kelamin').value = '';
            document.getElementById('new_pelanggan_rambut').value = '';
        })
        .catch(function() {
            pelangganErr.textContent = 'Gagal menyimpan pelanggan.';
            pelangganErr.classList.remove('hidden');
        });
    });
})();
</script>