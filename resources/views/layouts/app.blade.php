<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Data Pelanggan') — Afwo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-text-primary antialiased min-h-screen flex flex-col">
    <header class="bg-card border-b border-gray-200/80 sticky top-0 z-30">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-3 sm:gap-8">
                @if (Auth::check() && Auth::user()->isOwner())
                    <a href="{{ route('dashboard') }}" class="shrink-0 text-lg font-bold tracking-tight text-text-primary">
                        Afwo<span class="text-accent">.</span>
                    </a>
                @else
                    <a href="{{ route('pelanggan.index') }}" class="shrink-0 text-lg font-bold tracking-tight text-text-primary">
                        Afwo<span class="text-accent">.</span>
                    </a>
                @endif
                <nav class="flex items-center gap-0.5 overflow-x-auto text-sm sm:gap-1">
                    @if (Auth::check() && Auth::user()->isOwner())
                        <a href="{{ route('dashboard') }}"
                           class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('dashboard') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                            Dashboard
                        </a>
                    @endif
                    <a href="{{ route('pelanggan.index') }}"
                       class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('pelanggan.*') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                        Data Pelanggan
                    </a>
                    <a href="{{ route('karyawan.index') }}"
                       class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('karyawan.*') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                        Karyawan
                    </a>
                    <a href="{{ route('layanan.index') }}"
                       class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('layanan.*') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                        Layanan
                    </a>
                    <a href="{{ route('produk.index') }}"
                       class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('produk.*') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                        Produk
                    </a>
                    <a href="{{ route('transaksi.index') }}"
                       class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('transaksi.*') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                        Transaksi
                    </a>
                    @if (Auth::check() && Auth::user()->isOwner())
                        <a href="{{ route('laporan.penjualan', ['preset' => 'bulan-ini']) }}"
                           class="whitespace-nowrap rounded-lg px-3 py-2 transition-colors duration-150 {{ request()->routeIs('laporan.*') ? 'bg-accent/10 text-accent-text font-medium' : 'text-text-secondary hover:text-text-primary hover:bg-gray-100/80' }}">
                            Laporan
                        </a>
                    @endif
                </nav>
            </div>

            <div class="flex items-center gap-3">
                @if (Auth::check())
                    {{-- User area --}}
                    <div class="relative flex items-center gap-2 no-print">
                        <a href="{{ route('profile.show') }}"
                           class="inline-flex items-center gap-2 rounded-lg px-2 py-1.5 text-sm text-text-secondary transition-colors duration-150 hover:bg-gray-100 {{ request()->routeIs('profile.*') ? 'bg-accent/10 text-accent-text' : '' }}">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent/15 text-xs font-bold text-accent-text">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span class="max-w-[10rem] truncate hidden sm:inline">{{ Auth::user()->name }}</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="rounded-lg bg-gray-100 px-3 py-1.5 text-xs font-medium text-text-secondary transition-colors duration-150 hover:bg-gray-200">
                                Keluar
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-accent-text hover:text-accent">Masuk</a>
                @endif
            </div>
        </div>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 sm:px-6 lg:px-8 py-8">
        @if (session('success'))
            <div class="mb-6 rounded-lg border border-success/20 bg-success-bg px-4 py-3 text-sm font-medium text-success flex items-center gap-2">
                <span class="inline-block h-2 w-2 rounded-full bg-success"></span>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-danger/20 bg-danger-bg px-4 py-3 text-sm text-danger">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-gray-200/80 bg-card no-print">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-4 text-center text-xs text-text-muted">
            Afwo Website
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
