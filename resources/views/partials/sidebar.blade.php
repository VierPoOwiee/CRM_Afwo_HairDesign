{{-- Sidebar Vertikal Admin (Permanen di Desktop, Drawer di Mobile) --}}
<aside id="admin-sidebar" class="admin-sidebar" aria-label="Menu Navigasi Admin">
    {{-- Sidebar Brand Header --}}
    <div class="sidebar-brand-header">
        <div class="brand-text-group">
            <a href="{{ route('dashboard') }}" class="sidebar-brand-title">Afwo.</a>
            <span class="sidebar-brand-badge">Hair Design CRM</span>
        </div>
        <button type="button" id="nav-close-btn" class="nav-close-btn mobile-only" aria-label="Tutup Menu">
            <svg class="svg-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    {{-- Vertical Navigation Menu with Section Groups --}}
    <nav class="sidebar-nav-container">

        {{-- Section: MENU --}}
        <div class="sidebar-section-label">MENU</div>
        <ul class="sidebar-menu-list">
            @if (Auth::check() && Auth::user()->isOwner())
                <li class="sidebar-nav-item">
                    <a href="{{ route('dashboard') }}" class="sidebar-nav-link" id="nav-dashboard">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="currentColor">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                            <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </li>
            @endif

            <li class="sidebar-nav-item">
                <a href="{{ route('pelanggan.index') }}" class="sidebar-nav-link" id="nav-customers">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>Pelanggan</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('karyawan.index') }}" class="sidebar-nav-link" id="nav-workers">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>Karyawan</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('layanan.index') }}" class="sidebar-nav-link" id="nav-services">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="6" cy="6" r="3"></circle>
                        <circle cx="6" cy="18" r="3"></circle>
                        <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                        <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                        <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                    </svg>
                    <span>Layanan</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('produk.index') }}" class="sidebar-nav-link" id="nav-products">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m7.5 4.27 9 5.15"></path>
                        <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                        <path d="m3.3 7 8.7 5 8.7-5"></path>
                        <path d="M12 22V12"></path>
                    </svg>
                    <span>Produk</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('transaksi.index') }}" class="sidebar-nav-link" id="nav-history">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Transaksi</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('appointment.index') }}" class="sidebar-nav-link" id="nav-appointment">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Appointment</span>
                </a>
            </li>

            <li class="sidebar-nav-item">
                <a href="{{ route('absensi.index') }}" class="sidebar-nav-link" id="nav-absensi">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span>Absensi</span>
                </a>
            </li>
        </ul>

        {{-- Section: LAPORAN (owner only) --}}
        @if (Auth::check() && Auth::user()->isOwner())
            <div class="sidebar-section-label" style="margin-top: 14px;">LAPORAN</div>
            <ul class="sidebar-menu-list">
                <li class="sidebar-nav-item">
                    <a href="{{ route('laporan.penjualan') }}" class="sidebar-nav-link" id="nav-reports-sales">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="20" x2="18" y2="10"></line>
                            <line x1="12" y1="20" x2="12" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="14"></line>
                        </svg>
                        <span>Laporan Penjualan</span>
                    </a>
                </li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('laporan.pelanggan-aktif') }}" class="sidebar-nav-link" id="nav-reports-active-clients">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <polyline points="16 11 18 13 22 9"></polyline>
                        </svg>
                        <span>Pelanggan Aktif</span>
                    </a>
                </li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('laporan.rekap-komisi') }}" class="sidebar-nav-link" id="nav-reports-commission">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                        <span>Rekap Komisi &amp; Slip</span>
                    </a>
                </li>

                <li class="sidebar-nav-item">
                    <a href="{{ route('laporan.pendapatan-karyawan') }}" class="sidebar-nav-link" id="nav-reports-income">
                        <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <span>Pendapatan Karyawan</span>
                    </a>
                </li>
            </ul>
        @endif

    </nav>

    {{-- Sidebar User Footer --}}
    <div class="sidebar-user-footer">
        <a href="{{ route('profile.show') }}" class="sidebar-user-card" style="text-decoration: none; color: inherit;" title="Lihat Profil Saya">
            <div class="user-avatar-circle" id="sidebar-avatar">{{ Auth::check() ? strtoupper(substr(Auth::user()->name, 0, 1)) : 'O' }}</div>
            <div class="sidebar-user-meta">
                <span class="user-name" id="sidebar-user-name">{{ Auth::check() ? Auth::user()->name : 'Owner Afwo' }}</span>
                <span class="user-role">{{ Auth::check() ? (Auth::user()->isOwner() ? 'Administrator' : 'Kasir') : 'Administrator' }}</span>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="sidebar-logout-btn" id="nav-logout-link" title="Keluar" style="background:none;border:none;cursor:pointer;font-family:var(--font-sans);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Keluar</span>
            </button>
        </form>
    </div>
</aside>