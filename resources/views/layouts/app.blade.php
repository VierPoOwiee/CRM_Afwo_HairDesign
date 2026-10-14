<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#F7F6F3">
    <title>@yield('title', 'Dashboard') — AFWO Hair Design</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nav.css') }}">
    {{-- Tema dipasang SEBELUM paint supaya tidak ada flash-of-wrong-theme. --}}
    <script>(function(){try{var k="afwo_theme",t=localStorage.getItem(k);if(t!=="light"&&t!=="dark"){t=(window.matchMedia&&window.matchMedia("(prefers-color-scheme: dark)").matches)?"dark":"light";}document.documentElement.setAttribute("data-theme",t);}catch(e){document.documentElement.setAttribute("data-theme","light");}})();</script>
    @stack('styles')
</head>
<body data-nav-active="@yield('nav-active', 'dashboard')">

    {{-- Navigation Backdrop Overlay (for mobile drawer) --}}
    <div id="nav-overlay" class="nav-overlay" aria-hidden="true"></div>

    <div class="app-layout">

        @include('partials.sidebar')

        {{-- Main Content Area --}}
        <div class="app-main-content">

            {{-- App Bar: sticky di semua ukuran layar. Hanya berisi kontrol yang
                 benar-benar berfungsi (menu drawer, tema, profil). --}}
            <header class="app-bar">
                <div class="app-bar__left">
                    <button type="button" class="icon-btn js-menu-toggle mobile-only" id="btn-open-menu" aria-label="Buka menu navigasi" aria-controls="admin-sidebar" aria-expanded="false" aria-haspopup="true">
                        <svg class="svg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true" focusable="false">
                            <line x1="3" y1="6.5" x2="21" y2="6.5"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="17.5" x2="21" y2="17.5"></line>
                        </svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="brand-logo mobile-only">Afwo<span class="brand-logo__dot">.</span></a>
                    <span class="app-bar__title desktop-only">AFWO Hair Design CRM</span>
                </div>
                <div class="app-bar__right">
                    <button type="button" class="icon-btn theme-toggle js-theme-toggle" aria-label="Ganti ke mode gelap" aria-pressed="false">
                        <svg class="svg-icon icon-sun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <circle cx="12" cy="12" r="4.2"></circle>
                            <path d="M12 1.8v2.2M12 20v2.2M4.2 4.2l1.6 1.6M18.2 18.2l1.6 1.6M1.8 12h2.2M20 12h2.2M4.2 19.8l1.6-1.6M18.2 5.8l1.6-1.6"></path>
                        </svg>
                        <svg class="svg-icon icon-moon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="M21 12.8A8.6 8.6 0 1 1 11.2 3a6.7 6.7 0 0 0 9.8 9.8Z"></path>
                        </svg>
                    </button>
                    <a href="{{ route('profile.show') }}" class="app-bar__avatar" id="app-bar-avatar" aria-label="Profil saya" title="Profil saya">{{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'O' }}</a>
                </div>
            </header>

            {{-- Main Content --}}
            <main class="app-main @yield('main-class')">
                {{-- Centered content column. Halaman yang butuh lebih lebar cukup
                     menulis @section('container_width', 'app-content-wide'). --}}
                <div class="app-content @yield('container_width')">
                    @yield('content')
                </div>
            </main>

        </div>
    </div>

    {{-- Flash toast notification (success/error) --}}
    @if (session('success'))
        <div class="toast-box" id="flash-toast" role="status" aria-live="polite">
            <span>{{ session('success') }}</span>
        </div>
    @elseif ($errors->any())
        <div class="toast-box" id="flash-toast" role="status" aria-live="polite">
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <script src="{{ asset('js/theme.js') }}"></script>
    <script src="{{ asset('js/nav.js') }}"></script>
    @stack('modals')
    @stack('scripts')
</body>
</html>