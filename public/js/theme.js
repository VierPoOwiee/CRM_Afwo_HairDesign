/**
 * AFWO Hair Design - Theme Engine (Light / Dark)
 * ---------------------------------------------------------------------------
 * Aturan:
 *  - Sumber manual disimpan di localStorage key "afwo_theme".
 *  - Jika belum ada pilihan manual, mengikuti preferensi sistem (prefers-color-scheme).
 *  - Selalu mengikuti perubahan preferensi sistem selama user belum memilih manual.
 *  - Script inline kecil di <head> setiap halaman sudah memasang atribut
 *    data-theme SEBELUM paint, sehingga tidak ada flash-of-wrong-theme.
 *  - Durasi transisi 200ms (dihormati prefers-reduced-motion).
 *
 * Tombol toggle memakai class `js-theme-toggle` dan otomatis mendapat
 * aria-label + aria-pressed yang benar.
 */

(function () {
  'use strict';

  var STORAGE_KEY = 'afwo_theme';
  var root = document.documentElement;

  function readStored() {
    try {
      var v = localStorage.getItem(STORAGE_KEY);
      return (v === 'light' || v === 'dark') ? v : null;
    } catch (e) {
      return null;
    }
  }

  function systemTheme() {
    try {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    } catch (e) {
      return 'light';
    }
  }

  function resolve() {
    return readStored() || systemTheme();
  }

  function currentTheme() {
    return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
  }

  // Warna address bar / UI browser ikut mode tema.
  // Nilainya sama dengan --bg-page di styles/global.css.
  var THEME_COLOR = { light: '#F7F6F3', dark: '#141414' };

  function syncThemeColor(theme) {
    var meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', THEME_COLOR[theme] || THEME_COLOR.light);
  }

  /**
   * Pasang tema. `animate` menambah class sementara supaya transisi 200ms
   * berjalan pada pergantian manual, tetapi tidak pada render awal.
   */
  function apply(theme, animate) {
    var next = theme === 'dark' ? 'dark' : 'light';
    syncThemeColor(next);
    if (currentTheme() === next) {
      syncToggles(next);
      return;
    }

    var finish = function () {
      root.classList.remove('theme-anim');
      root.removeEventListener('transitionend', finish);
    };

    if (animate) {
      root.classList.add('theme-anim');
      window.setTimeout(finish, 260);
    }

    root.setAttribute('data-theme', next);
    syncToggles(next);
    notify(next);
  }

  /**
   * Beri tahu halaman bahwa tema berganti. Canvas (Chart.js / ctx.fillStyle)
   * tidak bisa membaca var(--x) langsung, jadi grafik harus menggambar ulang
   * dengan nilai token yang sudah di-resolve.
   */
  function notify(theme) {
    document.dispatchEvent(new CustomEvent('afwo:themechange', { detail: { theme: theme } }));
  }

  function toggle() {
    var next = currentTheme() === 'dark' ? 'light' : 'dark';
    try {
      localStorage.setItem(STORAGE_KEY, next);
    } catch (e) { /* storage diblokir - tema tetap berlaku untuk sesi ini */ }
    apply(next, true);
  }

  var LABEL = {
    light: 'Ganti ke mode gelap',
    dark: 'Ganti ke mode terang'
  };

  function syncToggles(theme) {
    var nodes = document.querySelectorAll('.js-theme-toggle');
    for (var i = 0; i < nodes.length; i++) {
      var btn = nodes[i];
      var isDark = theme === 'dark';
      btn.setAttribute('aria-label', isDark ? LABEL.dark : LABEL.light);
      btn.setAttribute('title', isDark ? LABEL.dark : LABEL.light);
      // aria-pressed = true saat mode gelap aktif
      btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
    }
  }

  function onSystemChange(e) {
    // Jangan menimpa pilihan manual user.
    if (readStored()) return;
    apply(e.matches ? 'dark' : 'light', true);
  }

  //-linking publik (dipakai inline bila perlu)
  window.AFWOTheme = {
    toggle: toggle,
    get: currentTheme,
    /**
     * Ambil nilai nyata dari sebuah custom property.
     * Diperlukan untuk canvas/Chart.js yang tidak mendukung var(--x).
     */
    token: function (name) {
      var v = getComputedStyle(root).getPropertyValue(name);
      return v ? v.trim() : '';
    },
    /** fn('--a', '--b') -> nilai resolved dari beberapa token sekaligus. */
    tokens: function () {
      var out = [];
      for (var i = 0; i < arguments.length; i++) {
        out.push(window.AFWOTheme.token(arguments[i]));
      }
      return out;
    },
    set: function (theme) {
      try { localStorage.setItem(STORAGE_KEY, theme === 'dark' ? 'dark' : 'light'); } catch (e) {}
      apply(theme, true);
    },
    clear: function () {
      try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
      apply(systemTheme(), true);
    }
  };

  function init() {
    apply(resolve(), false);
    syncToggles(currentTheme());

    document.addEventListener('click', function (e) {
      var btn = e.target.closest ? e.target.closest('.js-theme-toggle') : null;
      if (!btn) return;
      e.preventDefault();
      toggle();
    });

    try {
      var mq = window.matchMedia('(prefers-color-scheme: dark)');
      if (mq.addEventListener) {
        mq.addEventListener('change', onSystemChange);
      } else if (mq.addListener) {
        mq.addListener(onSystemChange);
      }
    } catch (e) { /* tidak didukung */ }

    // Jaga agar beberapa tab tetap sinkron.
    window.addEventListener('storage', function (e) {
      if (e.key === STORAGE_KEY) apply(resolve(), true);
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
