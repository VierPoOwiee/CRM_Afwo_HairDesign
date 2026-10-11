@php
    $currentPreset = $preset ?? 'bulan-ini';
@endphp

<section class="card report-filter-card" data-field="{{ $filterField ?? 'report-filters' }}">
    <div class="card__header">
        <div class="card__title-group">
            <h2 class="card__title">Filter Laporan</h2>
            <p class="card__subtitle">Tentukan periode, jenis pengerjaan, dan karyawan yang dihitung.</p>
        </div>
    </div>
    <form action="{{ $action ?? '#' }}" method="GET">
        <div class="report-filter-grid">

            {{-- Preset Periode --}}
            <div class="form-group-item">
                <label class="form-label" for="preset-select">Periode</label>
                <div class="form-input-box">
                    <select name="preset" id="preset-select" class="form-select-control">
                        <option value="harian" {{ $currentPreset === 'harian' ? 'selected' : '' }}>Harian</option>
                        <option value="mingguan" {{ $currentPreset === 'mingguan' ? 'selected' : '' }}>Mingguan (Sen–Min)</option>
                        <option value="bulan-ini" {{ $currentPreset === 'bulan-ini' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="bulan-lalu" {{ $currentPreset === 'bulan-lalu' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="custom" {{ $currentPreset === 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                </div>
            </div>

            {{-- Dari Tanggal --}}
            <div class="form-group-item">
                <label class="form-label" for="filter-dari">Dari Tanggal</label>
                <div class="form-input-box">
                    <input type="date" name="dari" id="filter-dari" value="{{ $dari ?? '' }}" class="form-input-control">
                </div>
            </div>

            {{-- Sampai Tanggal --}}
            <div class="form-group-item">
                <label class="form-label" for="filter-sampai">Sampai Tanggal</label>
                <div class="form-input-box">
                    <input type="date" name="sampai" id="filter-sampai" value="{{ $sampai ?? '' }}" class="form-input-control">
                </div>
            </div>

            {{-- Jenis Pengerjaan (opsional) --}}
            @if (isset($showJenisPengerjaan) && $showJenisPengerjaan)
                <div class="form-group-item">
                    <label class="form-label" for="filter-jenis">Jenis Pengerjaan</label>
                    <div class="form-input-box">
                        <select name="jenis_pengerjaan" id="filter-jenis" class="form-select-control">
                            <option value="">Semua</option>
                            <option value="sendiri" {{ ($jenisPengerjaan ?? '') === 'sendiri' ? 'selected' : '' }}>Sendiri</option>
                            <option value="berdua" {{ ($jenisPengerjaan ?? '') === 'berdua' ? 'selected' : '' }}>Berdua</option>
                        </select>
                    </div>
                </div>
            @endif

            {{-- Staf (jika ada daftar karyawan) --}}
            @if (isset($karyawans))
                <div class="form-group-item">
                    <label class="form-label" for="filter-staf">Staf</label>
                    <div class="form-input-box">
                        <select name="id_staf" id="filter-staf" class="form-select-control">
                            <option value="">Semua Staf</option>
                            @foreach ($karyawans as $k)
                                <option value="{{ $k->id }}" {{ ($stafId ?? '') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            {{-- Metode Pembayaran (opsional) --}}
            @if (isset($showMetode) && $showMetode)
                <div class="form-group-item">
                    <label class="form-label" for="filter-metode">Metode Pembayaran</label>
                    <div class="form-input-box">
                        <select name="metode_pembayaran" id="filter-metode" class="form-select-control">
                            <option value="">Semua</option>
                            <option value="cash" {{ ($metode ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="qris_bni" {{ ($metode ?? '') === 'qris_bni' ? 'selected' : '' }}>QRIS BNI</option>
                            <option value="qris_bri" {{ ($metode ?? '') === 'qris_bri' ? 'selected' : '' }}>QRIS BRI</option>
                            <option value="debit" {{ ($metode ?? '') === 'debit' ? 'selected' : '' }}>Debit</option>
                            <option value="kartu_kredit" {{ ($metode ?? '') === 'kartu_kredit' ? 'selected' : '' }}>Kartu Kredit</option>
                            <option value="transfer" {{ ($metode ?? '') === 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                </div>
            @endif

        </div>

        <div class="report-filter-actions">
            <a href="{{ $resetUrl ?? '#' }}" class="btn btn--secondary btn-filter-reset">Refresh</a>
            <button type="submit" class="btn btn--primary btn-filter-apply">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                </svg>
                Terapkan Filter
            </button>
        </div>
    </form>
</section>

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