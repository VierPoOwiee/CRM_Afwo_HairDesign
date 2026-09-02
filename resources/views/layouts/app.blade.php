<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Data Pelanggan') — Afwo</title>
    <link rel="preconnect" href="https://api.fontshare.com">
    <link href="https://api.fontshare.com/v2/css?f[]=general-sans@400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-text-primary antialiased min-h-screen">
    <div class="min-h-screen lg:flex">

        {{-- Mobile overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-40 hidden bg-black/30 backdrop-blur-sm lg:hidden" aria-hidden="true"></div>

        {{-- SIDEBAR --}}
        <aside id="sidebar"
               class="no-print fixed inset-y-0 left-0 z-50 w-64 transform -translate-x-full bg-sidebar border-r border-sidebar-divider flex flex-col justify-between transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 lg:shrink-0 lg:border-r lg:border-sidebar-divider">
            <div>
                <div class="flex items-center gap-3 px-6 py-6">
                    <div class="h-9 w-9 rounded-xl bg-accent flex items-center justify-center font-semibold text-[#3A1820]">A</div>
                    <div>
                        <p class="font-semibold leading-none text-text-primary">Afwo</p>
                        <p class="mt-1 text-[11px] tracking-wide text-accent-text">Hair Design</p>
                    </div>
                </div>

                <nav class="mt-4 px-4 space-y-6">
                    <div>
                        <p class="px-2 text-[11px] font-medium uppercase tracking-wider text-text-muted mb-2">Menu</p>
                        <div class="space-y-1">
                            @if (Auth::check() && Auth::user()->isOwner())
                                <a href="{{ route('dashboard') }}"
                                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                                    Dashboard
                                </a>
                            @endif
                            <a href="{{ route('pelanggan.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('pelanggan.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="9" cy="7" r="3.2"/><path d="M22 21v-1a4 4 0 0 0-3-3.87"/><path d="M16.5 3.13a3.2 3.2 0 0 1 0 6.24"/></svg>
                                Data Pelanggan
                            </a>
                            <a href="{{ route('karyawan.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('karyawan.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 21v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1"/><circle cx="8" cy="7" r="3.2"/><path d="M17 11l1.6 1.6L22 9"/></svg>
                                Karyawan
                            </a>
                            <a href="{{ route('layanan.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('layanan.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="14.47" y1="14.48" x2="20" y2="20"/><line x1="8.12" y1="8.12" x2="12" y2="12"/></svg>
                                Layanan
                            </a>
                            <a href="{{ route('produk.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('produk.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                                Produk
                            </a>
                            <a href="{{ route('transaksi.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('transaksi.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h12v20l-3-2-3 2-3-2-3 2V2Z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg>
                                Transaksi
                            </a>
                            <a href="{{ route('appointment.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('appointment.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M8 2v4M16 2v4M3 9h18M9 14l2 2 4-4"/></svg>
                                Appointment
                            </a>
                            <a href="{{ route('absensi.index') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('absensi.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/><path d="M9 5V3a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2"/><path d="M9.5 13.5 11 15l4-4"/></svg>
                                Absensi
                            </a>
                        </div>
                    </div>

                    @if (Auth::check() && Auth::user()->isOwner())
                        <div>
                            <p class="px-2 text-[11px] font-medium uppercase tracking-wider text-text-muted mb-2">Laporan</p>
                            <div class="space-y-1">
                                <a href="{{ route('laporan.penjualan', ['preset' => 'bulan-ini']) }}"
                                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('laporan.penjualan') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><rect x="7" y="12" width="3" height="6"/><rect x="12.5" y="8" width="3" height="10"/><rect x="18" y="5" width="3" height="13"/></svg>
                                    Laporan Penjualan
                                </a>
                                <a href="{{ route('laporan.pelanggan-aktif') }}"
                                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('laporan.pelanggan-*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.6 1.6 0 0 0-1.82-.33 1.6 1.6 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.6 1.6 0 0 0-1-1.51 1.6 1.6 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.6 1.6 0 0 0 .33-1.82 1.6 1.6 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.6 1.6 0 0 0 1.51-1 1.6 1.6 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.6 1.6 0 0 0 1.82.33h.01a1.6 1.6 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.6 1.6 0 0 0 1 1.51 1.6 1.6 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.6 1.6 0 0 0-.33 1.82v.01a1.6 1.6 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.6 1.6 0 0 0-1.51 1Z"/></svg>
                                    Pelanggan Aktif
                                </a>
                                <a href="{{ route('laporan.rekap-komisi') }}"
                                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('laporan.rekap-komisi*', 'laporan-komisi.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5.5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                    Rekap Komisi &amp; Slip
                                </a>
                                <a href="{{ route('laporan.pendapatan-karyawan') }}"
                                   class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('laporan.pendapatan-karyawan') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8 12 3 3 8l9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/></svg>
                                    Pendapatan Karyawan
                                </a>
                            </div>
                        </div>
                    @endif

                    <div>
                        <p class="px-2 text-[11px] font-medium uppercase tracking-wider text-text-muted mb-2">Akun</p>
                        <div class="space-y-1">
                            <a href="{{ route('profile.show') }}"
                               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('profile.*') ? 'bg-sidebar-active text-text-primary border-l-2 border-accent' : 'text-text-secondary hover:bg-sidebar-active' }}">
                                <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>
                                Profil
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-text-secondary hover:bg-sidebar-active">
                                    <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>
            </div>

            <div class="m-4 rounded-xl bg-sidebar-active border border-sidebar-divider p-4">
                <p class="text-sm font-medium text-text-primary">Butuh bantuan?</p>
                <p class="mt-1 text-xs text-text-muted leading-relaxed">Hubungi admin sistem untuk kendala aplikasi.</p>
                <a href="{{ route('profile.show') }}" class="mt-3 inline-block text-xs font-medium text-accent-text">Hubungi admin →</a>
            </div>
        </aside>

        {{-- MAIN --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Mobile topbar --}}
            <header class="no-print lg:hidden sticky top-0 z-30 flex items-center justify-between bg-card border-b border-sidebar-divider px-4 h-16">
                <button id="sidebar-toggle" type="button" aria-label="Buka menu" class="rounded-lg p-2 text-text-secondary hover:bg-sidebar-active">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="flex items-center gap-2.5">
                    <div class="h-8 w-8 rounded-lg bg-accent flex items-center justify-center text-sm font-semibold text-[#3A1820]">A</div>
                    <span class="text-sm font-semibold text-text-primary">Afwo</span>
                </div>
                @if (Auth::check())
                    <a href="{{ route('profile.show') }}" class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-accent/15 text-xs font-bold text-accent-text">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </a>
                @endif
            </header>

            <main class="flex-1 w-full p-6 sm:p-8">
                @if (session('success'))
                    <div class="mb-6 rounded-xl border border-success/20 bg-success-bg px-4 py-3 text-sm font-medium text-success flex items-center gap-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-success"></span>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-danger/20 bg-danger-bg px-4 py-3 text-sm text-danger">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mx-auto max-w-6xl">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
        (function () {
            var sidebar = document.getElementById('sidebar');
            var overlay = document.getElementById('sidebar-overlay');
            var toggle = document.getElementById('sidebar-toggle');

            function openSidebar() { if (!sidebar || !overlay) return; sidebar.classList.remove('-translate-x-full'); overlay.classList.remove('hidden'); }
            function closeSidebar() { if (!sidebar || !overlay) return; sidebar.classList.add('-translate-x-full'); overlay.classList.add('hidden'); }

            if (toggle) toggle.addEventListener('click', function (e) { e.stopPropagation(); sidebar.classList.contains('-translate-x-full') ? openSidebar() : closeSidebar(); });
            if (overlay) overlay.addEventListener('click', closeSidebar);
            document.addEventListener('click', function (e) { if (window.innerWidth < 1024 && sidebar && !sidebar.contains(e.target) && !sidebar.classList.contains('-translate-x-full') && e.target !== toggle) closeSidebar(); });
        })();
    </script>

    @stack('scripts')
</body>
</html>