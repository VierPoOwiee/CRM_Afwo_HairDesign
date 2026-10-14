<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $struk['no_struk'] }} — {{ config('salon.nama') }}</title>
    {{-- Tema dipasang sebelum paint (bar kontrol memakai token app). --}}
    <script>(function(){try{var k="afwo_theme",t=localStorage.getItem(k);if(t!=="light"&&t!=="dark"){t=(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches)?"dark":"light";}document.documentElement.setAttribute("data-theme",t);}catch(e){document.documentElement.setAttribute("data-theme","light");}})();</script>
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/struk.css') }}">
</head>
<body class="{{ $lebar === '58' ? 'struk-print-58' : '' }}">

    <div class="struk-toolbar struk-no-print">
        <button type="button" class="struk-btn struk-btn-primary" id="struk-print" title="Cetak struk" aria-label="Cetak struk">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Cetak
        </button>

        <div class="struk-width" role="group" aria-label="Lebar kertas">
            <a href="{{ route('transaksi.struk', ['transaksi' => $transaksi, 'lebar' => 80]) }}" class="{{ $lebar === '58' ? '' : 'is-active' }}">80 mm</a>
            <a href="{{ route('transaksi.struk', ['transaksi' => $transaksi, 'lebar' => 58]) }}" class="{{ $lebar === '58' ? 'is-active' : '' }}">58 mm</a>
        </div>

        <button type="button" class="struk-btn" id="struk-close" title="Tutup / Kembali">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            Kembali
        </button>
    </div>

    <div class="struk-screen-wrap">
        @include('transaksis._struk', ['struk' => $struk, 'lebar' => $lebar])
    </div>

    <script>
        (function () {
            var autoPrint = {{ ! empty($print) ? 'true' : 'false' }};
            var detailUrl = @json(route('transaksi.show', $transaksi));
            var printBtn = document.getElementById('struk-print');
            var closeBtn = document.getElementById('struk-close');

            function jalankanCetak() {
                window.print();
            }

            if (printBtn) {
                printBtn.addEventListener('click', jalankanCetak);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    if (window.opener && !window.opener.closed) {
                        window.close();
                    } else if (window.history.length > 1) {
                        window.history.back();
                    } else {
                        window.location.href = detailUrl;
                    }
                });
            }

            /* Buka otomatis bila ?print=1: tunggu gambar/logo selesai dimuat
               supaya struk tidak terpotong. Tutup jendela pada afterprint
               hanya bila jendela dibuka lewat skrip (window.opener ada). */
            function setelahGambarSiap(cb) {
                var pending = Array.prototype.slice.call(document.images)
                    .filter(function (img) { return !img.complete; });

                if (!pending.length) { cb(); return; }

                var sisa = pending.length;
                function selesai() { if (--sisa <= 0) { cb(); } }
                pending.forEach(function (img) {
                    img.addEventListener('load', selesai);
                    img.addEventListener('error', selesai);
                });
            }

            if (autoPrint) {
                function mulaiCetak() { setelahGambarSiap(jalankanCetak); }
                if (document.readyState === 'complete') {
                    mulaiCetak();
                } else {
                    window.addEventListener('load', mulaiCetak);
                }
            }

            window.addEventListener('afterprint', function () {
                if (window.opener && !window.opener.closed) {
                    window.close();
                }
            });
        })();
    </script>
</body>
</html>
