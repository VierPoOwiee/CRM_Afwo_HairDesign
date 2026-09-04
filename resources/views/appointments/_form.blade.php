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
            <div class="mt-1 relative">
                <input type="text" id="nama_search" autocomplete="off" required
                       placeholder="Ketik nama pelanggan..."
                       value="{{ old('nama', $appointment->nama ?? '') }}"
                       class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <input type="hidden" name="id_pelanggan" id="nama_id" value="{{ old('id_pelanggan') }}">
                <input type="hidden" name="nama" id="nama_hidden" value="{{ old('nama', $appointment->nama ?? '') }}" required>
                <div id="nama_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500">Ketik minimal 2 huruf untuk memilih dari data pelanggan yang ada.</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Service / Layanan <span class="text-red-500">*</span></label>
            <div class="mt-1 relative">
                <input type="text" id="service_search" autocomplete="off" required
                       placeholder="Ketik nama layanan..."
                       value="{{ old('service', $appointment->service ?? '') }}"
                       class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <input type="hidden" name="service" id="service_hidden" value="{{ old('service', $appointment->service ?? '') }}" required>
                <input type="hidden" name="kategori" id="kategori_hidden" value="{{ old('kategori', $appointment->kategori ?? '') }}">
                <div id="service_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500">Ketik minimal 2 huruf untuk memilih dari daftar layanan.</p>
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

<script>
(function() {
    var nameInput = document.getElementById('nama_search');
    var nameId    = document.getElementById('nama_id');
    var nameHid   = document.getElementById('nama_hidden');
    var nameRes   = document.getElementById('nama_results');
    var svcInput  = document.getElementById('service_search');
    var svcHid    = document.getElementById('service_hidden');
    var svcRes    = document.getElementById('service_results');

    function bindSearch(input, hidden, results, url, render, minLen) {
        var t;
        input.addEventListener('input', function() {
            clearTimeout(t);
            var q = this.value.trim();
            hidden.value = (q.length < minLen) ? q : '';
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

    bindSearch(svcInput, svcHid, svcRes,
        '{{ route("api.layanan.search") }}?q=',
        function(l) {
            var s = l.nama_layanan.replace(/'/g, "\\'");
            var k = (l.kategori || '').replace(/'/g, "\\'");
            return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" data-nama="' + s + '" data-kategori="' + k + '">' + l.nama_layanan + ' <span class="text-xs text-text-muted">(' + l.kategori + ')</span></div>';
        }, 2);

    svcRes.addEventListener('click', function(e) {
        var el = e.target.closest('[data-nama]');
        if (!el) return;
        svcInput.value = el.dataset.nama;
        svcHid.value = el.dataset.nama;
        document.getElementById('kategori_hidden').value = el.dataset.kategori || '';
        svcRes.classList.add('hidden');
    });

    var tanggInput = document.getElementById('tanggal');
    var waktuInput = document.getElementById('waktu');
    var kuotaInfo = document.getElementById('kuota_info');
    var formEl = document.querySelector('form[data-appointment-id]');
    var kategoriHid = document.getElementById('kategori_hidden');
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
            .then(renderSlotKuota);
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

    if (formEl) {
        formEl.addEventListener('submit', function(e) {
            if (!kategoriHid.value) {
                e.preventDefault();
                kategoriMsg.textContent = 'Pilih layanan dari daftar yang muncul, supaya kategori & kuota bisa dihitung otomatis';
                kategoriMsg.classList.remove('hidden');
                svcInput.focus();
                return;
            }
            kategoriMsg.classList.add('hidden');
        });
    }
})();
</script>