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
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>

        <div>
            <label for="kontak" class="block text-sm font-medium text-gray-700">Kontak</label>
            <input type="text" name="kontak" id="kontak" value="{{ old('kontak', $karyawan->kontak ?? '') }}"
                   placeholder="No. WA / telepon"
                   class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <span class="block text-sm font-medium text-gray-700">Skema Komisi <span class="text-red-500">*</span></span>
            <div class="mt-2 space-y-2">
                @foreach ($skemaKomisi as $value => $label)
                    <label class="flex cursor-pointer items-center gap-2 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <input type="radio" name="skema_komisi" value="{{ $value }}"
                               @checked((string) old('skema_komisi', $karyawan->skema_komisi ?? 'per_layanan') === $value)
                               class="h-4 w-4 border-gray-300 text-violet-600 focus:ring-violet-500">
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
                       class="block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                <span class="text-sm text-gray-500">%</span>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-md bg-violet-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
            {{ $submitLabel }}
        </button>
        <a href="{{ route('karyawan.index') }}"
           class="rounded-md bg-white px-5 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Batal
        </a>
    </div>
</div>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="skema_komisi"]');
        var persenField = document.getElementById('persen-field');
        var persenInput = document.getElementById('persen_komisi_harian');

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
    })();
</script>
