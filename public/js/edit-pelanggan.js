/**
 * AFWO Hair Design - Add / Edit Pelanggan Script
 * Handles dual-mode resolution (Add vs Edit) via query params, form pre-fill, validation, and saving.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Client Data Repository (Database simulation)
  const clientsData = {
    'nr': {
      name: 'nr',
      countryCode: '+62',
      phone: '8123212113',
      instagram: '@username',
      gender: 'Laki-laki',
      hairType: 'Ikal',
      hairCondition: 'Sehat',
      address: 'jw',
      notes: 'po'
    },
    'eleanor-vance': {
      name: 'Eleanor Vance',
      countryCode: '+62',
      phone: '8112233445',
      instagram: '@eleanor.vance',
      gender: 'Perempuan',
      hairType: 'Gelombang',
      hairCondition: 'Agak Rusak',
      address: 'Jl. Senopati No. 45, Jakarta Selatan',
      notes: 'Sensitif terhadap amonia, lebih suka pewarnaan organik balayage.'
    },
    'sarah-chen': {
      name: 'Sarah Chen',
      countryCode: '+62',
      phone: '8198765432',
      instagram: '@sarah.chen',
      gender: 'Perempuan',
      hairType: 'Lurus',
      hairCondition: 'Sehat',
      address: 'Menteng, Jakarta Pusat',
      notes: 'Potongan layer bob, treatment Olaplex rutin tiap 3 minggu.'
    },
    'marcus-sterling': {
      name: 'Marcus Sterling',
      countryCode: '+62',
      phone: '8129988776',
      instagram: '@marcus.s',
      gender: 'Laki-laki',
      hairType: 'Lurus',
      hairCondition: 'Sehat',
      address: 'Kebayoran Baru, Jakarta Selatan',
      notes: 'Signature grooming, styling pomade matte finish.'
    }
  };

  // Query Params
  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode') || 'edit';
  const clientId = urlParams.get('id') || 'nr';

  // Elements
  const pageTitle = document.getElementById('page-title');
  const pageSubtitle = document.getElementById('page-subtitle');
  const form = document.getElementById('form-edit-client');
  const saveBtn = document.getElementById('btn-save-customer');
  const backBtn = document.getElementById('btn-back-nav');
  const cancelBtn = document.getElementById('btn-cancel');
  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');

  const nameInput = document.getElementById('input-customer-name');
  const countryCodeSelect = document.getElementById('select-country-code');
  const phoneInput = document.getElementById('input-customer-phone');
  const instagramInput = document.getElementById('input-customer-instagram');
  const genderSelect = document.getElementById('select-customer-gender');
  const hairTypeSelect = document.getElementById('select-customer-hair-type');
  const hairConditionSelect = document.getElementById('select-customer-hair-condition');
  const addressInput = document.getElementById('input-customer-address');
  const notesInput = document.getElementById('input-customer-notes');

  // Initialize Dual-Mode View
  if (mode === 'edit') {
    const client = clientsData[clientId] || clientsData['nr'];
    if (pageTitle) pageTitle.textContent = 'Edit Pelanggan';
    if (pageSubtitle) pageSubtitle.textContent = `Perbarui data ${client.name}.`;
    if (saveBtn) saveBtn.textContent = 'Simpan Perubahan';

    if (nameInput) nameInput.value = client.name;
    if (countryCodeSelect) countryCodeSelect.value = client.countryCode;
    if (phoneInput) phoneInput.value = client.phone;
    if (instagramInput) instagramInput.value = client.instagram;
    if (genderSelect) genderSelect.value = client.gender;
    if (hairTypeSelect) hairTypeSelect.value = client.hairType;
    if (hairConditionSelect) hairConditionSelect.value = client.hairCondition || 'Sehat';
    if (addressInput) addressInput.value = client.address;
    if (notesInput) notesInput.value = client.notes;
  } else {
    // Mode Add (Tambah Pelanggan Baru)
    if (pageTitle) pageTitle.textContent = 'Tambah Pelanggan';
    if (pageSubtitle) pageSubtitle.textContent = 'Tambahkan pelanggan baru ke sistem.';
    if (saveBtn) saveBtn.textContent = 'Simpan Pelanggan';

    if (nameInput) nameInput.value = '';
    if (phoneInput) phoneInput.value = '';
    if (instagramInput) instagramInput.value = '';
    if (addressInput) addressInput.value = '';
    if (notesInput) notesInput.value = '';
    if (hairConditionSelect) hairConditionSelect.value = 'Sehat';
  }

  // Toast Helper
  function showToast(message, duration = 2500) {
    if (!toastBox) return;
    if (toastMessage) toastMessage.textContent = message;
    toastBox.classList.add('show');
    setTimeout(() => {
      toastBox.classList.remove('show');
    }, duration);
  }

  // Save Handler
  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      handleSave();
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', (e) => {
      e.preventDefault();
      handleSave();
    });
  }

  function handleSave() {
    const name = nameInput ? nameInput.value.trim() : '';
    const phone = phoneInput ? phoneInput.value.trim() : '';
    const countryCode = countryCodeSelect ? countryCodeSelect.value : '+62';
    const instagram = instagramInput ? instagramInput.value.trim() : '';
    const gender = genderSelect ? genderSelect.value : '';
    const hairType = hairTypeSelect ? hairTypeSelect.value : '';
    const address = addressInput ? addressInput.value.trim() : '';
    const notes = notesInput ? notesInput.value.trim() : '';

    if (!name) {
      showToast('⚠️ Nama pelanggan wajib diisi.');
      if (nameInput) nameInput.focus();
      return;
    }

    if (!phone) {
      showToast('⚠️ Nomor WhatsApp wajib diisi.');
      if (phoneInput) phoneInput.focus();
      return;
    }

    // Prepare JSON payload for Backend API (POST/PUT /api/clients)
    const clientPayload = {
      id: mode === 'edit' ? clientId : name.toLowerCase().replace(/\s+/g, '-'),
      name,
      countryCode,
      phone: `${countryCode}${phone}`,
      instagram,
      gender,
      hairType,
      address,
      notes
    };

    console.log(`[AFWO CRM] ${mode === 'edit' ? 'Updating' : 'Creating'} Client Payload (Ready for DB):`, clientPayload);

    const successMsg = mode === 'edit' 
      ? `Data pelanggan "${name}" berhasil diperbarui!`
      : `Pelanggan baru "${name}" berhasil ditambahkan!`;

    showToast(`✓ ${successMsg}`);

    setTimeout(() => {
      window.location.href = 'clients.html';
    }, 1200);
  }
});
