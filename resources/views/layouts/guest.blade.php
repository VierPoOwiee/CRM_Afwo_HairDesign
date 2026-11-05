<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title', 'AFWO Hair Design')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    @stack('styles')
    {{-- Tema dipasang SEBELUM paint supaya tidak ada flash-of-wrong-theme. --}}
    <script>(function(){try{var k="afwo_theme",t=localStorage.getItem(k);if(t!=="light"&&t!=="dark"){t=(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches)?"dark":"light";}document.documentElement.setAttribute("data-theme",t);}catch(e){document.documentElement.setAttribute("data-theme","light");}})();</script>
</head>
<body class="login-page-body">

    <div class="login-screen-container">

        {{-- Theme Toggle (halaman tamu tidak punya sidebar) --}}
        <button type="button" class="js-theme-toggle login-theme-toggle" aria-label="Ganti tema">
            <svg class="icon-sun login-theme-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="12" cy="12" r="4.2"></circle>
                <path d="M12 2.5v2.2M12 19.3v2.2M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M2.5 12h2.2M19.3 12h2.2M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"></path>
            </svg>
            <svg class="icon-moon login-theme-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M20.5 14.3A8.5 8.5 0 1 1 9.7 3.5a6.8 6.8 0 0 0 10.8 10.8Z"></path>
            </svg>
        </button>

        {{-- Brand Header --}}
        <div class="login-brand-header">
            <h1 class="login-brand-title">Afwo<span class="login-brand-dot">.</span></h1>
            <p class="login-brand-subtitle">@yield('subtitle', 'AFWO Hair Design')</p>
        </div>

        {{-- Kartu utama --}}
        <div class="login-card">
            @if (session('status'))
                <div class="login-alert is-success" role="status">{{ session('status') }}</div>
            @elseif (session('success'))
                <div class="login-alert is-success" role="status">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="login-alert is-error" role="alert">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </div>

    </div>

    <script src="{{ asset('js/theme.js') }}"></script>
    @stack('scripts')
</body>
</html>
