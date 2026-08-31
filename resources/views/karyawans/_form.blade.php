@php
    $skemaKomisi = [
        'per_layanan' => 'Per Layanan',
        'persen_omset_harian' => 'Persen Omset Harian',
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="nama" class="block text-sm font-medium text-gray-700">Nama <span class="text-red-500">*</span></label>
            <input type="text" name="nama" id="nama" value="{{ old('nama', $karyawan->nama ?? '') }}" required
                   class="mt-1 block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>

        <x-phone-input
            name="kontak"
            label="Kontak (No. WA / Telepon)"
            :value="$karyawan->kontak ?? ''"
            placeholder="812xxxxxxx" />
    </div>

    <div>
        <label for="gaji_pokok" class="block text-sm font-medium text-gray-700">Gaji Pokok (per bulan)</label>
        <div class="mt-1 flex items-center gap-2">
            <span class="text-sm text-gray-500">Rp</span>
            <input type="text" inputmode="numeric" name="gaji_pokok" id="gaji_pokok"
                   value="{{ old('gaji_pokok', $karyawan->gaji_pokok ?? '') }}"
                   placeholder="3000000"
                   class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
        </div>
        <p class="mt-0.5 text-xs text-gray-500">Gaji pokok bulanan, independen dari skema komisi.</p>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <span class="block text-sm font-medium text-gray-700">Skema Komisi <span class="text-red-500">*</span></span>
            <div class="mt-2 space-y-2">
                @foreach ($skemaKomisi as $value => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <input type="radio" name="skema_komisi" value="{{ $value }}"
                               @checked((string) old('skema_komisi', $karyawan->skema_komisi ?? 'per_layanan') === $value)
                               class="h-4 w-4 border-gray-300 text-accent-text focus:ring-accent/30">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div id="persen-field" class="hidden">
            <label for="persen_komisi_harian" class="block text-sm font-medium text-gray-700">
                Persen Komisi Harian <span class="text-red-500">*</span>
            </label>
            <p class="mt-0.5 text-xs text-gray-500">Komisi dari persentase total omset harian.</p>
            <div class="mt-1 flex items-center gap-2">
                <input type="number" name="persen_komisi_harian" id="persen_komisi_harian"
                       value="{{ old('persen_komisi_harian', $karyawan->persen_komisi_harian ?? '') }}"
                       min="0" max="100" step="0.01"
                       class="block w-full rounded-lg border-gray-300 bg-white text-text-primary px-3 py-2 text-sm shadow-sm focus:border-accent focus:ring-accent/30 focus:outline-none">
                <span class="text-sm text-gray-500">%</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-dark px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-dark-hover">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('karyawan.index') }}"
           class="rounded-lg bg-card px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="skema_komisi"]');
        var persenField = document.getElementById('persen-field');
        var persenInput = document.getElementById('persen_komisi_harian');
        var gajiInput = document.getElementById('gaji_pokok');

        function togglePersen() {
            var checked = document.querySelector('input[name="skema_komisi"]:checked');
            var isPersen = checked && checked.value === 'persen_omset_harian';
            persenField.classList.toggle('hidden', !isPersen);
            persenInput.required = isPersen;
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', togglePersen);
        });

        togglePersen();

        /* Format harga gaji pokok */
        function parseFormatted(str) {
            return parseInt(str.replace(/\./g, '').replace(/,/g, ''), 10) || 0;
        }

        function formatNumber(n) {
            return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function formatHarga(el) {
            var raw = parseFormatted(el.value);
            el.value = raw ? formatNumber(raw) : '';
        }

        function unformatHarga(el) {
            el.value = el.value.replace(/\./g, '');
        }

        if (gajiInput) {
            gajiInput.addEventListener('focus', function () { unformatHarga(this); });
            gajiInput.addEventListener('blur', function () { formatHarga(this); });
        }

        document.querySelector('form').addEventListener('submit', function () {
            if (gajiInput) unformatHarga(gajiInput);
        });

        if (gajiInput && gajiInput.value) formatHarga(gajiInput);
    })();
</script>
