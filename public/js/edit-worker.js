/**
 * AFWO Hair Design - Add / Edit Karyawan Script
 * Handles dual-mode resolution (Add vs Edit) via query params, form pre-fill, validation, and saving.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Worker / Karyawan Data Repository (Database simulation)
  const workersData = {
    'agus-pratama': {
      name: 'Agus Pratama',
      countryCode: '+62',
      phone: '81112202005',
      salary: '4000000,00',
      commissionScheme: 'daily_percentage',
      commissionRate: '10,00'
    },
    'ada-wong': {
      name: 'Ada Wong',
      countryCode: '+62',
      phone: '81234567890',
      salary: '4500000,00',
      commissionScheme: 'daily_percentage',
      commissionRate: '12,50'
    },
    'budi': {
      name: 'Budi (Color Specialist)',
      countryCode: '+62',
      phone: '81398765432',
      salary: '4200000,00',
      commissionScheme: 'per_service',
      commissionRate: '15,00'
    },
    'rina': {
      name: 'Rina',
      countryCode: '+62',
      phone: '81855543210',
      salary: '3800000,00',
      commissionScheme: 'daily_percentage',
      commissionRate: '10,00'
    },
    'dimas': {
      name: 'Dimas',
      countryCode: '+62',
      phone: '81900112233',
      salary: '3800000,00',
      commissionScheme: 'per_service',
      commissionRate: '10,00'
    }
  };

  // Query Params
  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode') || 'edit';
  const workerId = urlParams.get('id') || 'agus-pratama';

  // Elements
  const pageTitle = document.getElementById('page-title');
  const pageSubtitle = document.getElementById('page-subtitle');
  const form = document.getElementById('form-edit-worker');
  const saveBtn = document.getElementById('btn-save-worker');
  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');

  const nameInput = document.getElementById('input-worker-name');
  const countryCodeSelect = document.getElementById('select-worker-code');
  const phoneInput = document.getElementById('input-worker-phone');
  const salaryInput = document.getElementById('input-worker-salary');
  const commissionRateInput = document.getElementById('input-worker-commission-rate');
  const labelCommissionRate = document.getElementById('label-commission-rate');
  const subtextCommissionRate = document.getElementById('subtext-commission-rate');

  const boxDailyRate = document.getElementById('box-daily-rate');
  const boxServiceRateInfo = document.getElementById('box-service-rate-info');
  const radioSchemeDaily = document.getElementById('scheme-daily-percentage');
  const radioSchemeService = document.getElementById('scheme-per-service');
  const labelSchemeDaily = document.getElementById('label-scheme-daily');
  const labelSchemeService = document.getElementById('label-scheme-service');

  // Radio scheme toggle handler
  function updateCommissionSchemeUI() {
    if (radioSchemeDaily && radioSchemeDaily.checked) {
      if (labelSchemeDaily) labelSchemeDaily.classList.add('active');
      if (labelSchemeService) labelSchemeService.classList.remove('active');
      if (boxDailyRate) boxDailyRate.style.display = 'block';
      if (boxServiceRateInfo) boxServiceRateInfo.style.display = 'none';
      if (commissionRateInput && !commissionRateInput.value) {
        commissionRateInput.value = '10';
      }
    } else {
      if (labelSchemeService) labelSchemeService.classList.add('active');
      if (labelSchemeDaily) labelSchemeDaily.classList.remove('active');
      if (boxDailyRate) boxDailyRate.style.display = 'none';
      if (boxServiceRateInfo) boxServiceRateInfo.style.display = 'block';
    }
  }

  if (radioSchemeDaily) radioSchemeDaily.addEventListener('change', updateCommissionSchemeUI);
  if (radioSchemeService) radioSchemeService.addEventListener('change', updateCommissionSchemeUI);

  // Initialize Dual-Mode View
  if (mode === 'edit') {
    const worker = workersData[workerId] || workersData['agus-pratama'];
    if (pageTitle) pageTitle.textContent = 'Edit Karyawan';
    if (pageSubtitle) pageSubtitle.textContent = `Perbarui data ${worker.name}.`;
    if (saveBtn) saveBtn.textContent = 'Simpan Perubahan';

    if (nameInput) nameInput.value = worker.name;
    if (countryCodeSelect) countryCodeSelect.value = worker.countryCode;
    if (phoneInput) phoneInput.value = worker.phone;
    if (salaryInput) salaryInput.value = worker.salary;
    if (commissionRateInput) commissionRateInput.value = worker.commissionRate || '10';

    if (worker.commissionScheme === 'per_service') {
      if (radioSchemeService) radioSchemeService.checked = true;
    } else {
      if (radioSchemeDaily) radioSchemeDaily.checked = true;
    }
    updateCommissionSchemeUI();
  } else {
    // Mode Add (Tambah Karyawan Baru)
    if (pageTitle) pageTitle.textContent = 'Tambah Karyawan';
    if (pageSubtitle) pageSubtitle.textContent = 'Tambahkan karyawan baru ke sistem.';
    if (saveBtn) saveBtn.textContent = 'Simpan Karyawan';

    if (nameInput) nameInput.value = '';
    if (phoneInput) phoneInput.value = '';
    if (salaryInput) salaryInput.value = '4000000,00';
    if (commissionRateInput) commissionRateInput.value = '10';
    if (radioSchemeDaily) radioSchemeDaily.checked = true;
    updateCommissionSchemeUI();
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
    const salary = salaryInput ? salaryInput.value.trim() : '0';
    const commissionScheme = (radioSchemeDaily && radioSchemeDaily.checked) ? 'daily_percentage' : 'per_service';
    const commissionRate = commissionRateInput ? commissionRateInput.value.trim() : '0';

    if (!name) {
      showToast('⚠️ Nama karyawan wajib diisi.');
      if (nameInput) nameInput.focus();
      return;
    }

    // Prepare JSON payload for Backend API (POST/PUT /api/workers)
    const workerPayload = {
      id: mode === 'edit' ? workerId : name.toLowerCase().replace(/\s+/g, '-'),
      name,
      countryCode,
      phone: phone ? `${countryCode}${phone}` : '',
      salary,
      commissionScheme,
      commissionRate
    };

    console.log(`[AFWO CRM] ${mode === 'edit' ? 'Updating' : 'Creating'} Worker Payload (Ready for DB):`, workerPayload);

    const successMsg = mode === 'edit' 
      ? `Data karyawan "${name}" berhasil diperbarui!`
      : `Karyawan baru "${name}" berhasil ditambahkan!`;

    showToast(`✓ ${successMsg}`);

    setTimeout(() => {
      window.location.href = 'worker.html';
    }, 1200);
  }
});
