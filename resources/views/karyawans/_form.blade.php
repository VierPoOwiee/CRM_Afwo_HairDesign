@php
    $kontak = old('kontak', $karyawan->kontak ?? '');
    $negaraKode = '+62';
    $kontakLokal = $kontak;
    if (preg_match('/^(\+\d{1,3})(\d+)$/', $kontak, $kontakMatch)) {
        $negaraKode = $kontakMatch[1];
        $kontakLokal = $kontakMatch[2];
    }
    $skemaSaatIni = old('skema_komisi', $karyawan->skema_komisi ?? 'per_layanan');
    $gajiValue = old('gaji_pokok', ($karyawan->gaji_pokok ?? 0) > 0 ? number_format($karyawan->gaji_pokok, 0, ',', '.') : '');
@endphp

<div class="form-fields-stack">

    <div class="form-grid-2col">
        <div class="form-group-item">
            <label class="form-label" for="input-worker-name">Nama <span class="required-star">*</span></label>
            <div class="form-input-box">
                <input type="text" id="input-worker-name" name="nama" class="form-input-field" value="{{ old('nama', $karyawan->nama ?? '') }}" placeholder="Nama Lengkap Karyawan" required>
            </div>
            @error('nama')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group-item">
            <label class="form-label" for="input-worker-phone">Kontak (No. WA / Telepon)</label>
            <div class="phone-split-input">
                <div class="country-select-box">
                    <select id="select-worker-code" class="country-select">
                        <option value="+62" @selected($negaraKode === '+62')>ID Indonesia (+62)</option>
                        <option value="+1" @selected($negaraKode === '+1')>US (+1)</option>
                        <option value="+65" @selected($negaraKode === '+65')>SG (+65)</option>
                        <option value="+60" @selected($negaraKode === '+60')>MY (+60)</option>
                    </select>
                </div>
                <div class="phone-number-box">
                    <input type="tel" id="input-worker-phone" class="form-input-field" value="{{ $kontakLokal }}" placeholder="81112202005">
                </div>
            </div>
            <input type="hidden" name="kontak" id="input-worker-kontak" value="{{ $kontak }}">
            @error('kontak')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-group-item">
        <label class="form-label" for="input-worker-salary">Gaji Pokok (per bulan)</label>
        <div class="currency-input-box">
            <span class="currency-prefix">Rp</span>
            <input type="text" id="input-worker-salary" name="gaji_pokok" class="form-input-field" value="{{ $gajiValue }}" placeholder="4000000,00">
        </div>
        <p class="field-subtext">Gaji pokok bulanan, independen dari skema komisi.</p>
        @error('gaji_pokok')
            <span class="field-error">{{ $message }}</span>
        @enderror
    </div>

    <div class="form-grid-2col">
        <div class="form-group-item">
            <label class="form-label">Skema Komisi <span class="required-star">*</span></label>
            <div class="radio-group-vertical">
                <label class="radio-card-option {{ $skemaSaatIni === 'per_layanan' ? 'active' : '' }}" id="label-scheme-service">
                    <input type="radio" name="skema_komisi" value="per_layanan" id="scheme-per-service" @checked($skemaSaatIni === 'per_layanan')>
                    <span>Per Layanan</span>
                </label>
                <label class="radio-card-option {{ $skemaSaatIni === 'persen_omset_harian' ? 'active' : '' }}" id="label-scheme-daily">
                    <input type="radio" name="skema_komisi" value="persen_omset_harian" id="scheme-daily-percentage" @checked($skemaSaatIni === 'persen_omset_harian')>
                    <span>Persen Omset Harian</span>
                </label>
            </div>
            @error('skema_komisi')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group-item">
            <div id="box-daily-rate">
                <label class="form-label" for="input-worker-commission-rate">Persen Komisi Omset Harian (%)</label>
                <p class="field-subtext" id="subtext-commission-rate">Standar otomatis 10% (dapat diedit jika ada perubahan kebijakan).</p>
                <div class="percent-input-box">
                    <input type="number" step="0.1" min="0" max="100" id="input-worker-commission-rate" name="persen_komisi_harian" class="form-input-field" value="{{ old('persen_komisi_harian', $karyawan->persen_komisi_harian ?? '10') }}" placeholder="10">
                    <span class="percent-suffix">%</span>
                </div>
                @error('persen_komisi_harian')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-actions-row">
        <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Batal</a>
        <button type="submit" id="btn-save-worker" class="btn btn-primary">{{ $submitLabel }}</button>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('form-edit-worker');
            if (!form) return;

            var countrySelect = document.getElementById('select-worker-code');
            var phoneInput = document.getElementById('input-worker-phone');
            var mergedInput = document.getElementById('input-worker-kontak');

            function mergePhone() {
                var local = phoneInput.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
                mergedInput.value = countrySelect.value + local;
            }
            form.addEventListener('submit', mergePhone);

            var radios = document.querySelectorAll('input[name="skema_komisi"]');
            var dailyBox = document.getElementById('box-daily-rate');
            var rateInput = document.getElementById('input-worker-commission-rate');

            function toggleScheme() {
                var checked = document.querySelector('input[name="skema_komisi"]:checked');
                var isDaily = checked && checked.value === 'persen_omset_harian';

                document.querySelectorAll('.radio-card-option').forEach(function (label) {
                    label.classList.toggle('active', label.querySelector('input:checked') !== null);
                });

                if (dailyBox) dailyBox.style.opacity = isDaily ? '1' : '0.45';
                if (rateInput) rateInput.disabled = !isDaily;
            }

            radios.forEach(function (radio) {
                radio.addEventListener('change', toggleScheme);
            });
            toggleScheme();

            var gajiInput = document.getElementById('input-worker-salary');
            function parseFormatted(str) {
                return parseInt(String(str).replace(/\./g, '').replace(/,/g, ''), 10) || 0;
            }
            function formatNumber(n) {
                return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            function unformatHarga(el) {
                el.value = el.value.replace(/\./g, '');
            }
            if (gajiInput) {
                gajiInput.addEventListener('blur', function () {
                    var raw = parseFormatted(this.value);
                    this.value = raw ? formatNumber(raw) : '';
                });
                form.addEventListener('submit', function () { unformatHarga(gajiInput); });
            }
        });
    </script>
@endpush