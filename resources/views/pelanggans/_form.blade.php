@php
    $noWa = old('no_wa', $pelanggan->no_wa ?? '');
    $negaraKode = '+62';
    $noWaLokal = $noWa;
    if (preg_match('/^(\+\d{1,3})(\d+)$/', $noWa, $noWaMatch)) {
        $negaraKode = $noWaMatch[1];
        $noWaLokal = $noWaMatch[2];
    }
    $jenisRambut = ['Ikal', 'Lurus', 'Gelombang', 'Keriting'];
    $kondisiRambut = ['Sehat', 'Agak Rusak', 'Rusak'];
@endphp

<div class="form-fields-stack">

    <div class="form-grid-2col">
        <div class="form-group-item">
            <label class="form-label" for="input-customer-name">Nama <span class="required-star">*</span></label>
            <div class="form-input-box">
                <input type="text" id="input-customer-name" name="nama" class="form-input-field" value="{{ old('nama', $pelanggan->nama ?? '') }}" placeholder="Nama Lengkap" required>
            </div>
            @error('nama')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group-item">
            <label class="form-label" for="input-customer-phone">No. WhatsApp <span class="required-star">*</span></label>
            <div class="phone-split-input">
                <div class="country-select-box">
                    <select id="select-country-code" class="country-select">
                        <option value="+62" @selected($negaraKode === '+62')>ID Indonesia (+62)</option>
                        <option value="+1" @selected($negaraKode === '+1')>US (+1)</option>
                        <option value="+65" @selected($negaraKode === '+65')>SG (+65)</option>
                        <option value="+60" @selected($negaraKode === '+60')>MY (+60)</option>
                    </select>
                </div>
                <div class="phone-number-box">
                    <input type="tel" id="input-customer-phone" class="form-input-field" value="{{ $noWaLokal }}" placeholder="81234567890" required>
                </div>
            </div>
            <input type="hidden" name="no_wa" id="input-no-wa" value="{{ $noWa }}">
            @error('no_wa')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>
    </div>

    <div class="form-grid-2col">
        <div class="form-group-item">
            <label class="form-label" for="input-customer-instagram">Username Instagram</label>
            <div class="form-input-box">
                <input type="text" id="input-customer-instagram" name="username_instagram" class="form-input-field" value="{{ old('username_instagram', ltrim($pelanggan->username_instagram ?? '', '@')) }}" placeholder="@username">
            </div>
        </div>

        <div class="form-group-item">
            <label class="form-label" for="select-customer-gender">Jenis Kelamin</label>
            <div class="form-input-box">
                <select id="select-customer-gender" name="jenis_kelamin" class="form-select-control">
                    <option value="" @selected(old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === '')>Lainnya / Tidak Disebutkan</option>
                    <option value="L" @selected(old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === 'L')>Laki-laki</option>
                    <option value="P" @selected(old('jenis_kelamin', $pelanggan->jenis_kelamin ?? '') === 'P')>Perempuan</option>
                </select>
            </div>
        </div>
    </div>

    <div class="form-grid-2col">
        <div class="form-group-item">
            <label class="form-label" for="select-customer-hair-type">Jenis Rambut</label>
            <div class="form-input-box">
                <select id="select-customer-hair-type" name="jenis_rambut" class="form-select-control">
                    <option value="" @selected(old('jenis_rambut', $pelanggan->jenis_rambut ?? '') === '')>-- Pilih --</option>
                    @foreach ($jenisRambut as $r)
                        <option value="{{ $r }}" @selected(old('jenis_rambut', $pelanggan->jenis_rambut ?? '') === $r)>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-group-item">
            <label class="form-label" for="select-customer-hair-condition">Kondisi Rambut</label>
            <div class="form-input-box">
                <select id="select-customer-hair-condition" name="kondisi_rambut" class="form-select-control">
                    <option value="" @selected(old('kondisi_rambut', $pelanggan->kondisi_rambut ?? '') === '')>-- Pilih --</option>
                    @foreach ($kondisiRambut as $r)
                        <option value="{{ $r }}" @selected(old('kondisi_rambut', $pelanggan->kondisi_rambut ?? '') === $r)>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="form-group-item">
        <label class="form-label" for="input-customer-address">Alamat</label>
        <div class="form-input-box">
            <input type="text" id="input-customer-address" name="alamat" class="form-input-field" value="{{ old('alamat', $pelanggan->alamat ?? '') }}" placeholder="Alamat Domisili">
        </div>
    </div>

    <div class="form-group-item">
        <label class="form-label" for="input-customer-notes">Catatan Khusus</label>
        <div class="form-input-box">
            <textarea id="input-customer-notes" name="catatan_khusus" class="form-textarea" rows="3" placeholder="Catatan preferensi, alergi, atau riwayat formula...">{{ old('catatan_khusus', $pelanggan->catatan_khusus ?? '') }}</textarea>
        </div>
    </div>

    <div class="form-actions-row">
        <button type="submit" id="btn-save-customer" class="btn-primary-gold">{{ $submitLabel }}</button>
        <a href="{{ route('pelanggan.index') }}" class="btn-secondary-cancel">Batal</a>
    </div>

</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('form-edit-client');
            if (!form) return;

            var countrySelect = document.getElementById('select-country-code');
            var phoneInput = document.getElementById('input-customer-phone');
            var mergedInput = document.getElementById('input-no-wa');

            function mergePhone() {
                var local = phoneInput.value.replace(/[^0-9]/g, '').replace(/^0+/, '');
                mergedInput.value = countrySelect.value + local;
            }

            form.addEventListener('submit', mergePhone);
        });
    </script>
@endpush