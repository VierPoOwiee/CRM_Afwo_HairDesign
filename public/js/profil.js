// Profil Saya Handler Script for AFWO Hair Design CRM
document.addEventListener('DOMContentLoaded', () => {
  // Check logged in user or default to Owner Afwo
  const savedUserStr = sessionStorage.getItem('afwo_logged_in_user');
  let currentUser = {
    name: 'Owner Afwo',
    email: 'owner@afwo.com',
    role: 'Owner'
  };

  if (savedUserStr) {
    try {
      currentUser = JSON.parse(savedUserStr);
    } catch (e) {
      console.error(e);
    }
  }

  // Populate user display
  const userNameDisplay = document.getElementById('profil-display-name');
  const userEmailDisplay = document.getElementById('profil-display-email');
  const userAvatarDisplay = document.getElementById('profil-display-avatar');
  const inputName = document.getElementById('profil-input-name');
  const inputEmail = document.getElementById('profil-input-email');

  if (userNameDisplay) userNameDisplay.textContent = currentUser.name;
  if (userEmailDisplay) userEmailDisplay.textContent = currentUser.email;
  if (userAvatarDisplay) userAvatarDisplay.textContent = currentUser.name.charAt(0).toUpperCase();
  if (inputName) inputName.value = currentUser.name;
  if (inputEmail) inputEmail.value = currentUser.email;

  // Form 1: Profil Update
  const formProfile = document.getElementById('form-profil-info');
  const profileAlert = document.getElementById('alert-profil-info');
  const inputCurrPassword1 = document.getElementById('profil-input-curr-password');

  function showProfileAlert(msg, type = 'error') {
    if (!profileAlert) return;
    profileAlert.textContent = msg;
    profileAlert.className = `profil-form-alert is-${type}`;
    profileAlert.style.display = 'block';
  }

  function hideProfileAlert() {
    if (!profileAlert) return;
    profileAlert.style.display = 'none';
  }

  if (formProfile) {
    formProfile.addEventListener('submit', (e) => {
      e.preventDefault();
      hideProfileAlert();

      const newName = inputName ? inputName.value.trim() : '';
      const newEmail = inputEmail ? inputEmail.value.trim() : '';
      const currPass = inputCurrPassword1 ? inputCurrPassword1.value : '';

      if (!newName || !newEmail) {
        showProfileAlert('Nama dan Email wajib diisi!', 'error');
        return;
      }

      if (!currPass) {
        showProfileAlert('Masukkan password Anda saat ini untuk menyimpan perubahan!', 'error');
        inputCurrPassword1?.focus();
        return;
      }

      // Simulate API call to PUT /api/user/profile
      const submitBtn = formProfile.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
      }

      setTimeout(() => {
        currentUser.name = newName;
        currentUser.email = newEmail;
        sessionStorage.setItem('afwo_logged_in_user', JSON.stringify(currentUser));

        if (userNameDisplay) userNameDisplay.textContent = currentUser.name;
        if (userEmailDisplay) userEmailDisplay.textContent = currentUser.email;
        if (userAvatarDisplay) userAvatarDisplay.textContent = currentUser.name.charAt(0).toUpperCase();
        if (inputCurrPassword1) inputCurrPassword1.value = '';

        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Simpan Perubahan';
        }

        showProfileAlert('Profil Anda berhasil diperbarui!', 'success');
      }, 600);
    });
  }

  // Form 2: Password Update
  const formPassword = document.getElementById('form-profil-password');
  const passwordAlert = document.getElementById('alert-profil-password');
  const inputCurrPass2 = document.getElementById('pass-current');
  const inputNewPass = document.getElementById('pass-new');
  const inputConfirmPass = document.getElementById('pass-confirm');

  function showPasswordAlert(msg, type = 'error') {
    if (!passwordAlert) return;
    passwordAlert.textContent = msg;
    passwordAlert.className = `profil-form-alert is-${type}`;
    passwordAlert.style.display = 'block';
  }

  function hidePasswordAlert() {
    if (!passwordAlert) return;
    passwordAlert.style.display = 'none';
  }

  if (formPassword) {
    formPassword.addEventListener('submit', (e) => {
      e.preventDefault();
      hidePasswordAlert();

      const currPass = inputCurrPass2 ? inputCurrPass2.value : '';
      const newPass = inputNewPass ? inputNewPass.value : '';
      const confirmPass = inputConfirmPass ? inputConfirmPass.value : '';

      if (!currPass) {
        showPasswordAlert('Masukkan password saat ini!', 'error');
        inputCurrPass2?.focus();
        return;
      }

      if (!newPass || newPass.length < 8) {
        showPasswordAlert('Password baru minimal 8 karakter!', 'error');
        inputNewPass?.focus();
        return;
      }

      if (newPass !== confirmPass) {
        showPasswordAlert('Konfirmasi password baru tidak cocok!', 'error');
        inputConfirmPass?.focus();
        return;
      }

      // Simulate API call to PUT /api/user/password
      const submitBtn = formPassword.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memperbarui...';
      }

      setTimeout(() => {
        if (inputCurrPass2) inputCurrPass2.value = '';
        if (inputNewPass) inputNewPass.value = '';
        if (inputConfirmPass) inputConfirmPass.value = '';

        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Perbarui Password';
        }

        showPasswordAlert('Password Anda berhasil diperbarui!', 'success');
      }, 600);
    });
  }
});
