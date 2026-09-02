<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="tanggal" class="block text-sm font-medium text-gray-700">Hari / Tanggal <span class="text-red-500">*</span></label>
            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', isset($appointment) && $appointment->tanggal ? $appointment->tanggal->format('Y-m-d') : date('Y-m-d')) }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <div>
            <label for="waktu" class="block text-sm font-medium text-gray-700">Waktu <span class="text-red-500">*</span></label>
            <input type="time" name="waktu" id="waktu" value="{{ old('waktu', $appointment->waktu ?? '') }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
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
                <div id="service_results" class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-card shadow-sm max-h-48 overflow-y-auto hidden"></div>
            </div>
            <p class="mt-1 text-xs text-gray-500">Ketik minimal 2 huruf untuk memilih dari daftar layanan.</p>
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
            return '<div class="cursor-pointer px-3 py-2 text-sm hover:bg-accent-light" data-nama="' + s + '">' + l.nama_layanan + ' <span class="text-xs text-text-muted">(' + l.kategori + ')</span></div>';
        }, 2);

    svcRes.addEventListener('click', function(e) {
        var el = e.target.closest('[data-nama]');
        if (!el) return;
        svcInput.value = el.dataset.nama;
        svcHid.value = el.dataset.nama;
        svcRes.classList.add('hidden');
    });
})();
</script>