@php
    $currentPreset = $preset ?? 'bulan-ini';
@endphp

<div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
    <form action="{{ $action ?? '#' }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Preset Periode --}}
            <div>
                <label class="block text-xs font-medium text-gray-500">Periode Cepat</label>
                <select name="preset" id="preset-select"
                        class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                    <option value="harian" {{ $currentPreset === 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="mingguan" {{ $currentPreset === 'mingguan' ? 'selected' : '' }}>Mingguan (Sen–Min)</option>
                    <option value="bulan-ini" {{ $currentPreset === 'bulan-ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="bulan-lalu" {{ $currentPreset === 'bulan-lalu' ? 'selected' : '' }}>Bulan Lalu</option>
                    <option value="custom" {{ $currentPreset === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            {{-- Dari Tanggal --}}
            <div>
                <label class="block text-xs font-medium text-gray-500">Dari Tanggal</label>
                <input type="date" name="dari" id="filter-dari" value="{{ $dari ?? '' }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            </div>

            {{-- Sampai Tanggal --}}
            <div>
                <label class="block text-xs font-medium text-gray-500">Sampai Tanggal</label>
                <input type="date" name="sampai" id="filter-sampai" value="{{ $sampai ?? '' }}"
                       class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="rounded-md bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-violet-700">
                    Filter
                </button>
                <a href="{{ $resetUrl ?? '#' }}"
                   class="rounded-md bg-white px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    Reset
                </a>
            </div>
        </div>

        {{-- Filter Tambahan --}}
        @if (isset($showExtraFilters) && $showExtraFilters)
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3 border-t border-gray-100 pt-4">
                @if (isset($showJenisPengerjaan) && $showJenisPengerjaan)
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Jenis Pengerjaan</label>
                        <select name="jenis_pengerjaan"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                            <option value="">Semua</option>
                            <option value="sendiri" {{ ($jenisPengerjaan ?? '') === 'sendiri' ? 'selected' : '' }}>Sendiri</option>
                            <option value="berdua" {{ ($jenisPengerjaan ?? '') === 'berdua' ? 'selected' : '' }}>Berdua</option>
                        </select>
                    </div>
                @endif

                @if (isset($karyawans))
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Staf</label>
                        <select name="id_staf"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                            <option value="">Semua Staf</option>
                            @foreach ($karyawans as $k)
                                <option value="{{ $k->id }}" {{ ($stafId ?? '') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if (isset($showMetode) && $showMetode)
                    <div>
                        <label class="block text-xs font-medium text-gray-500">Metode Pembayaran</label>
                        <select name="metode_pembayaran"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
                            <option value="">Semua</option>
                            <option value="cash" {{ ($metode ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qris" {{ ($metode ?? '') === 'qris' ? 'selected' : '' }}>QRIS</option>
                            <option value="debit" {{ ($metode ?? '') === 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="kartu_kredit" {{ ($metode ?? '') === 'kartu_kredit' ? 'selected' : '' }}>Kartu Kredit</option>
                            <option value="transfer" {{ ($metode ?? '') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                @endif
            </div>
        @endif
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const presetSelect = document.getElementById('preset-select');
    const dariInput = document.getElementById('filter-dari');
    const sampaiInput = document.getElementById('filter-sampai');

    if (!presetSelect) return;

    const presets = {
        'harian': () => {
            const today = new Date().toISOString().slice(0, 10);
            dariInput.value = today;
            sampaiInput.value = today;
        },
        'mingguan': () => {
            const now = new Date();
            const day = now.getDay();
            const diffToMonday = day === 0 ? 6 : day - 1;
            const monday = new Date(now);
            monday.setDate(now.getDate() - diffToMonday);
            const sunday = new Date(monday);
            sunday.setDate(monday.getDate() + 6);
            dariInput.value = monday.toISOString().slice(0, 10);
            sampaiInput.value = sunday.toISOString().slice(0, 10);
        },
        'bulan-ini': () => {
            const now = new Date();
            dariInput.value = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0, 10);
            sampaiInput.value = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().slice(0, 10);
        },
        'bulan-lalu': () => {
            const now = new Date();
            dariInput.value = new Date(now.getFullYear(), now.getMonth() - 1, 1).toISOString().slice(0, 10);
            sampaiInput.value = new Date(now.getFullYear(), now.getMonth(), 0).toISOString().slice(0, 10);
        },
    };

    presetSelect.addEventListener('change', function () {
        const val = this.value;
        if (val === 'custom') {
            dariInput.value = '';
            sampaiInput.value = '';
            dariInput.focus();
        } else if (presets[val]) {
            presets[val]();
        }
    });

    // Auto-set to custom if dates are manually changed
    [dariInput, sampaiInput].forEach(input => {
        input.addEventListener('change', function () {
            presetSelect.value = 'custom';
        });
    });
});
</script>
