/**
 * AFWO Hair Design - Admin Sidebar & Responsive Navigation Script
 * Manages vertical sidebar interactions, mobile drawer open/close transitions,
 * backdrop blur, and highlights the active route across all pages.
 * Adapted for Laravel: active nav key dibaca dari <body data-nav-active="...">,
 * logout memakai form POST (route('logout')) -> lihat partial sidebar.
 */

document.addEventListener('DOMContentLoaded', () => {
  const menuToggles = document.querySelectorAll('.js-menu-toggle');
  const adminSidebar = document.getElementById('admin-sidebar');
  const navOverlay = document.getElementById('nav-overlay');
  const closeBtn = document.getElementById('nav-close-btn');

  function openMenu() {
    if (adminSidebar && navOverlay) {
      adminSidebar.classList.add('is-active');
      navOverlay.classList.add('is-active');
      document.body.style.overflow = 'hidden';
    }
  }

  function closeMenu() {
    if (adminSidebar && navOverlay) {
      adminSidebar.classList.remove('is-active');
      navOverlay.classList.remove('is-active');
      document.body.style.overflow = '';
    }
  }

  // Bind toggle buttons (mobile header hamburger icon)
  menuToggles.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      openMenu();
    });
  });

  // Close button in sidebar
  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      closeMenu();
    });
  }

  // Click on backdrop to close
  if (navOverlay) {
    navOverlay.addEventListener('click', closeMenu);
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