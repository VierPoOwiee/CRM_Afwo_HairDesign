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
        <label for="{{ $name }}_number" class="block text-sm font-medium text-gray-700">
            {{ $label }} @if ($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <div class="mt-1 flex gap-2">
        <select id="{{ $name }}_country" aria-label="Kode negara"
                class="w-[45%] max-w-[180px] shrink-0 rounded-md border-gray-300 bg-white px-2 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
            @foreach ($countries as $c)
                <option value="{{ $c[1] }}">{{ $c[2] }} {{ $c[0] }} (+{{ $c[1] }})</option>
            @endforeach
        </select>
        <input type="text" inputmode="numeric" autocomplete="tel-national" id="{{ $name }}_number"
               placeholder="{{ $placeholder }}" @if ($required) required @endif
               class="block w-full flex-1 rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-violet-500 focus:ring-violet-500">
    </div>

    <input type="hidden" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}">

    @php
        $phoneError = isset($errors) ? $errors->first($name) : null;
    @endphp
    @if ($phoneError)
        <p class="mt-1 text-xs text-red-600">{{ $phoneError }}</p>
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
            input.value = num;
            hidden.value = num ? '+' + sel.value + num : '';
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
