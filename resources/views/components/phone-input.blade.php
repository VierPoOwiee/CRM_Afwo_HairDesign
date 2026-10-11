@props([
    'name' => 'telepon',
    'label' => null,
    'value' => '',
    'required' => false,
    'placeholder' => '812xxxxxxx',
])

@php
    $countries = [
        ['Indonesia', '62', '🇮🇩'],
        ['Afghanistan', '93', '🇦🇫'],
        ['Amerika Serikat', '1', '🇺🇸'],
        ['Arab Saudi', '966', '🇸🇦'],
        ['Argentina', '54', '🇦🇷'],
        ['Australia', '61', '🇦🇺'],
        ['Belanda', '31', '🇳🇱'],
        ['Belgia', '32', '🇧🇪'],
        ['Brasil', '55', '🇧🇷'],
        ['Brunei', '673', '🇧🇳'],
        ['China', '86', '🇨🇳'],
        ['Filipina', '63', '🇵🇭'],
        ['Hong Kong', '852', '🇭🇰'],
        ['India', '91', '🇮🇳'],
        ['Inggris', '44', '🇬🇧'],
        ['Irak', '964', '🇮🇶'],
        ['Iran', '98', '🇮🇷'],
        ['Irlandia', '353', '🇮🇪'],
        ['Italia', '39', '🇮🇹'],
        ['Jepang', '81', '🇯🇵'],
        ['Jerman', '49', '🇩🇪'],
        ['Kanada', '1', '🇨🇦'],
        ['Kazakhstan', '7', '🇰🇿'],
        ['Kenya', '254', '🇰🇪'],
        ['Korea Selatan', '82', '🇰🇷'],
        ['Kuwait', '965', '🇰🇼'],
        ['Malaysia', '60', '🇲🇾'],
        ['Maroko', '212', '🇲🇦'],
        ['Meksiko', '52', '🇲🇽'],
        ['Mesir', '20', '🇪🇬'],
        ['Myanmar', '95', '🇲🇲'],
        ['Nigeria', '234', '🇳🇬'],
        ['Norwegia', '47', '🇳🇴'],
        ['Pakistan', '92', '🇵🇰'],
        ['Papua Nugini', '675', '🇵🇬'],
        ['Perancis', '33', '🇫🇷'],
        ['Polandia', '48', '🇵🇱'],
        ['Portugal', '351', '🇵🇹'],
        ['Qatar', '974', '🇶🇦'],
        ['Rusia', '7', '🇷🇺'],
        ['Singapura', '65', '🇸🇬'],
        ['Spanyol', '34', '🇪🇸'],
        ['Sri Lanka', '94', '🇱🇰'],
        ['Sudan', '249', '🇸🇩'],
        ['Swiss', '41', '🇨🇭'],
        ['Taiwan', '886', '🇹🇼'],
        ['Thailand', '66', '🇹🇭'],
        ['Timor Leste', '670', '🇹🇱'],
        ['Turki', '90', '🇹🇷'],
        ['Uni Emirat Arab', '971', '🇦🇪'],
        ['Ukraina', '380', '🇺🇦'],
        ['Vietnam', '84', '🇻🇳'],
    ];
@endphp

<div id="{{ $name }}_wrapper">
    @if ($label)
        <label for="{{ $name }}_number" class="form-label">
            {{ $label }} @if ($required)<span class="required-star">*</span>@endif
        </label>
    @endif

    <div class="phone-split-input">
        <div class="country-select-box">
            <select id="{{ $name }}_country" class="country-select" aria-label="Kode negara">
                @foreach ($countries as $c)
                    <option value="{{ $c[1] }}">{{ $c[2] }} {{ $c[0] }} (+{{ $c[1] }})</option>
                @endforeach
            </select>
        </div>
        <div class="phone-number-box">
            <input type="text" inputmode="numeric" autocomplete="tel-national" id="{{ $name }}_number"
                   class="phone-number-field" placeholder="{{ $placeholder }}" maxlength="13"
                   @if ($required) required @endif>
        </div>
    </div>

    <input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}">

    @php
        $phoneError = isset($errors) ? $errors->first($name) : null;
    @endphp
    @if ($phoneError)
        <p class="field-error">{{ $phoneError }}</p>
    @endif
</div>

<script>
    (function () {
        var sel = document.getElementById('{{ $name }}_country');
        var input = document.getElementById('{{ $name }}_number');
        var hidden = document.getElementById('{{ $name }}');
        var dialCodes = @json(collect($countries)->pluck(1)->unique()->sortByDesc(function ($c) {
            return strlen($c);
        })->values());
        var defaultCode = '62';

        function sync() {
            var num = input.value.replace(/\D/g, '').replace(/^0+/, '');
            var full = (sel.value + num).slice(0, 13);
            input.value = full.slice(sel.value.length);
            hidden.value = input.value ? '+' + full : '';
        }

        function init() {
            var raw = (hidden.value || '').trim();
            if (raw !== '') {
                var digits = raw.replace(/\D/g, '');
                var matched = null;
                for (var i = 0; i < dialCodes.length; i++) {
                    if (digits.indexOf(dialCodes[i]) === 0 && digits.length > dialCodes[i].length) {
                        matched = dialCodes[i];
                        break;
                    }
                }
                sel.value = matched || defaultCode;
                input.value = matched ? digits.slice(matched.length).replace(/^0+/, '') : digits;
            }
            sync();
        }

        sel.addEventListener('change', sync);
        input.addEventListener('input', sync);
        init();
    })();
</script>
