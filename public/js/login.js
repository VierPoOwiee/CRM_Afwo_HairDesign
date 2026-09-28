// Login Handler Script for AFWO Hair Design CRM (Laravel)
// Form login disubmit normal ke POST /login oleh Laravel.
// Script ini hanya mengelola: toggle password + remember-username (localStorage).
document.addEventListener('DOMContentLoaded', () => {
  const usernameInput = document.getElementById('login-username');
  const rememberCheckbox = document.getElementById('login-remember');
  const togglePasswordBtn = document.getElementById('btn-toggle-password');
  const passwordInput = document.getElementById('login-password');

  // Check saved remember me
  const savedUsername = localStorage.getItem('afwo_remember_username');
  if (savedUsername && usernameInput) {
    usernameInput.value = savedUsername;
    if (rememberCheckbox) rememberCheckbox.checked = true;
  }

  // Toggle Password Visibility
  if (togglePasswordBtn && passwordInput) {
    togglePasswordBtn.addEventListener('click', () => {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);

      const eyeIcon = togglePasswordBtn.querySelector('svg');
      if (type === 'text') {
        eyeIcon.innerHTML = `
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
          <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
      } else {
        eyeIcon.innerHTML = `
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
          <circle cx="12" cy="12" r="3"></circle>
        `;
      }
    });
  }

  // Simpan username saat checkbox "Ingat saya" dicentang (bila form disubmit)
  if (usernameInput && rememberCheckbox) {
    const form = document.getElementById('form-login');
    if (form) {
      form.addEventListener('submit', () => {
        if (rememberCheckbox.checked) {
          localStorage.setItem('afwo_remember_username', usernameInput.value.trim());
        } else {
          localStorage.removeItem('afwo_remember_username');
        }
      });
    }
  }
});