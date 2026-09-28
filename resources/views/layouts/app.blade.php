<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') — AFWO Hair Design</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nav.css') }}">
    @stack('styles')
</head>
<body data-nav-active="@yield('nav-active', 'dashboard')">

    {{-- Navigation Backdrop Overlay (for mobile drawer) --}}
    <div id="nav-overlay" class="nav-overlay" aria-hidden="true"></div>

    <div class="app-layout">

        @include('partials.sidebar')

        {{-- Main Content Area --}}
        <div class="app-main-content">

            {{-- Mobile Top Header Bar (Shown only on Mobile/Tablet <= 1024px) --}}
            <header class="mobile-top-header">
                <a href="{{ route('dashboard') }}" class="brand-logo">Afwo.</a>
                <button type="button" class="icon-btn js-menu-toggle" aria-label="Buka Menu Navigasi" id="btn-open-menu">
                    <svg class="svg-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round">
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
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

    <script src="{{ asset('js/nav.js') }}"></script>
    @stack('scripts')
</body>
</html>