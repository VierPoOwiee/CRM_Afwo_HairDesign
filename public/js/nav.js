/**
 * AFWO Hair Design - Admin Sidebar & Responsive Navigation Script
 * Manages vertical sidebar interactions, mobile drawer open/close transitions,
 * backdrop blur, and highlights the active route across all pages.
 * Adapted for Laravel: active nav key dibaca dari <body data-nav-active="...">,
 * logout memakai form POST (route('logout')) -> lihat partial sidebar.
 *
 * Catatan: tombol "X" di sidebar sudah dihapus. Drawer ditutup lewat backdrop,
 * tombol Escape, atau menekan salah satu link navigasi.
 */

document.addEventListener('DOMContentLoaded', () => {
  const menuToggles = document.querySelectorAll('.js-menu-toggle');
  const adminSidebar = document.getElementById('admin-sidebar');
  const navOverlay = document.getElementById('nav-overlay');

  function openMenu() {
    if (adminSidebar && navOverlay) {
      adminSidebar.classList.add('is-active');
      navOverlay.classList.add('is-active');
      document.body.classList.add('nav-open');
      menuToggles.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
    }
  }

  function closeMenu() {
    if (adminSidebar && navOverlay) {
      adminSidebar.classList.remove('is-active');
      navOverlay.classList.remove('is-active');
      document.body.classList.remove('nav-open');
      menuToggles.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    }
  }

  // Bind toggle buttons (hamburger di app bar)
  menuToggles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openMenu();
    });
  });

  // Klik backdrop untuk menutup
  if (navOverlay) {
    navOverlay.addEventListener('click', closeMenu);
  }

  // Memilih menu akan menutup drawer di mobile (pengganti tombol X)
  if (adminSidebar) {
    adminSidebar.querySelectorAll('.sidebar-nav-link').forEach(link => {
      link.addEventListener('click', closeMenu);
    });
  }

  // Keyboard Escape key to close
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      closeMenu();
    }
  });

  // =========================================================================
  // DYNAMIC ACTIVE MENU RESOLUTION (VERTICAL ADMIN SIDEBAR)
  // Key aktif ditentukan server-side (Blade) via atribut data-nav-active.
  // =========================================================================
  function updateActiveNavLink() {
    const key = (document.body && document.body.getAttribute('data-nav-active')) || 'dashboard';
    const activeLink = document.getElementById('nav-' + key);
    if (activeLink) activeLink.classList.add('active');
  }

  updateActiveNavLink();

  // Toast otomatis untuk flash message success/error
  const flashToast = document.getElementById('flash-toast');
  if (flashToast) {
    setTimeout(() => flashToast.classList.add('show'), 120);
    setTimeout(() => flashToast.classList.remove('show'), 3200);
  }
});