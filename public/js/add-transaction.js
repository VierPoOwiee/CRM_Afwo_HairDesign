/**
 * AFWO Hair Design - Add Transaction / Edit Reservation Script
 * Manages:
 * 1. Searchable client combobox (type-to-filter dropdown with auto phone population)
 * 2. Dynamic calculations (services, hair length surcharges, 11% tax, total)
 * 3. Multi-service duplication & deletion
 * 4. Multi-worker assignment & multi-product dosage stepper
 * 5. Commission hints, validation, discard, and saving
 */

document.addEventListener('DOMContentLoaded', () => {
  // Client Directory (Database Simulation with Unique Phone Numbers)
  const salonClients = [
    { name: 'Jane Smith', phone: '+62 812 3456 7890' },
    { name: 'Jane Smith', phone: '+62 813 9876 5432' },
    { name: 'Eleanor Vance', phone: '+62 811 2233 4455' },
    { name: 'Sarah Chen', phone: '+62 819 8765 4321' },
    { name: 'James Sterling', phone: '+62 813 5566 7788' },
    { name: 'Maya Wright', phone: '+62 818 1122 3344' },
    { name: 'Vivianne Westwood', phone: '+62 812 9900 1122' },
    { name: 'Marcus Sterling', phone: '+62 812 4455 6677' },
    { name: 'Mia Wong', phone: '+62 817 3344 5566' },
    { name: 'Elena Rossi', phone: '+62 815 6677 8899' }
  ];

  // Helper to extract initials
  function getInitials(name) {
    if (!name) return '??';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].substring(0, 2).toUpperCase();
  }

  // Name Combobox Elements
  const customerNameInput = document.getElementById('customer-name-input');
  const comboboxToggleBtn = document.getElementById('btn-toggle-combobox');
  const nameDropdown = document.getElementById('combobox-dropdown');

  // Phone Combobox Elements
  const customerPhoneInput = document.getElementById('customer-phone-input');
  const phoneToggleBtn = document.getElementById('btn-toggle-phone-combobox');
  const phoneDropdown = document.getElementById('phone-combobox-dropdown');

  // =========================================================================
  // 1. NAME SUGGESTIONS DROPDOWN
  // =========================================================================
  function renderNameOptions(filterQuery = '') {
    if (!nameDropdown) return;
    const query = filterQuery.toLowerCase().trim();
    const digitsQuery = query.replace(/[^0-9]/g, '');

    const matches = salonClients.filter(c => {
      const cDigits = c.phone.replace(/[^0-9]/g, '');
      return c.name.toLowerCase().includes(query) || 
             (digitsQuery && cDigits.includes(digitsQuery)) ||
             c.phone.toLowerCase().includes(query);
    });

    if (matches.length === 0) {
      nameDropdown.innerHTML = `
        <div class="combobox-no-results">
          <strong style="color: var(--text-primary);">Pelanggan tidak ditemukan.</strong>
          <span style="display:block; font-size: 0.76rem; margin-top: 2px; color: var(--text-muted);">Nomor telepon dapat diisi manual sebagai data klien baru.</span>
        </div>`;
    } else {
      nameDropdown.innerHTML = matches.map(c => `
        <div class="combobox-option" data-name="${c.name}" data-phone="${c.phone}">
          <div class="combobox-option-left">
            <div class="combobox-option-main">
              <span class="combobox-option-name">${c.name}</span>
              <span class="combobox-option-phone">ID Telp: ${c.phone}</span>
            </div>
          </div>
          <span class="combobox-badge-pill">${c.phone}</span>
        </div>
      `).join('');

      nameDropdown.querySelectorAll('.combobox-option').forEach(opt => {
        opt.addEventListener('click', () => {
          const selectedName = opt.getAttribute('data-name');
          const selectedPhone = opt.getAttribute('data-phone');

          if (customerNameInput) customerNameInput.value = selectedName;
          if (customerPhoneInput) customerPhoneInput.value = selectedPhone;

          closeNameDropdown();
        });
      });
    }
  }

  function openNameDropdown() {
    if (nameDropdown) {
      renderNameOptions(customerNameInput ? customerNameInput.value : '');
      nameDropdown.classList.add('show');
      if (comboboxToggleBtn) comboboxToggleBtn.classList.add('open');
      closePhoneDropdown();
    }
  }

  function closeNameDropdown() {
    if (nameDropdown) {
      nameDropdown.classList.remove('show');
      if (comboboxToggleBtn) comboboxToggleBtn.classList.remove('open');
    }
  }

  if (customerNameInput) {
    customerNameInput.addEventListener('focus', openNameDropdown);
    customerNameInput.addEventListener('input', (e) => {
      renderNameOptions(e.target.value);
      nameDropdown.classList.add('show');
      if (comboboxToggleBtn) comboboxToggleBtn.classList.add('open');
    });
  }

  if (comboboxToggleBtn) {
    comboboxToggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (nameDropdown && nameDropdown.classList.contains('show')) {
        closeNameDropdown();
      } else {
        openNameDropdown();
        if (customerNameInput) customerNameInput.focus();
      }
    });
  }

  // =========================================================================
  // 2. PHONE SUGGESTIONS DROPDOWN (Interconnected with Name)
  // =========================================================================
  function renderPhoneOptions(filterQuery = '') {
    if (!phoneDropdown) return;
    const rawQuery = filterQuery.toLowerCase().trim();
    const digitsQuery = rawQuery.replace(/[^0-9]/g, '');

    const matches = salonClients.filter(c => {
      const cDigits = c.phone.replace(/[^0-9]/g, '');
      return (digitsQuery && cDigits.includes(digitsQuery)) || 
             c.phone.toLowerCase().includes(rawQuery) || 
             c.name.toLowerCase().includes(rawQuery);
    });

    if (matches.length === 0) {
      phoneDropdown.innerHTML = `
        <div class="combobox-no-results">
          <strong style="color: var(--text-primary);">Nomor belum terdaftar di database.</strong>
          <span style="display:block; font-size: 0.76rem; margin-top: 2px; color: var(--text-muted);">Nomor ini akan tersimpan otomatis saat transaksi dibuat.</span>
        </div>`;
    } else {
      phoneDropdown.innerHTML = matches.map(c => `
        <div class="combobox-option" data-name="${c.name}" data-phone="${c.phone}">
          <div class="combobox-option-left">
            <div class="combobox-option-main">
              <span class="combobox-option-name">${c.phone}</span>
              <span class="combobox-option-phone">Klien: ${c.name}</span>
            </div>
          </div>
          <span class="combobox-badge-pill">${c.name}</span>
        </div>
      `).join('');

      phoneDropdown.querySelectorAll('.combobox-option').forEach(opt => {
        opt.addEventListener('click', () => {
          const selectedName = opt.getAttribute('data-name');
          const selectedPhone = opt.getAttribute('data-phone');

          if (customerPhoneInput) customerPhoneInput.value = selectedPhone;
          if (customerNameInput) customerNameInput.value = selectedName;

          closePhoneDropdown();
        });
      });
    }
  }

  function openPhoneDropdown() {
    if (phoneDropdown) {
      renderPhoneOptions(customerPhoneInput ? customerPhoneInput.value : '');
      phoneDropdown.classList.add('show');
      if (phoneToggleBtn) phoneToggleBtn.classList.add('open');
      closeNameDropdown();
    }
  }

  function closePhoneDropdown() {
    if (phoneDropdown) {
      phoneDropdown.classList.remove('show');
      if (phoneToggleBtn) phoneToggleBtn.classList.remove('open');
    }
  }

  if (customerPhoneInput) {
    customerPhoneInput.addEventListener('focus', openPhoneDropdown);
    customerPhoneInput.addEventListener('input', (e) => {
      renderPhoneOptions(e.target.value);
      phoneDropdown.classList.add('show');
      if (phoneToggleBtn) phoneToggleBtn.classList.add('open');
    });
  }

  if (phoneToggleBtn) {
    phoneToggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      if (phoneDropdown && phoneDropdown.classList.contains('show')) {
        closePhoneDropdown();
      } else {
        openPhoneDropdown();
        if (customerPhoneInput) customerPhoneInput.focus();
      }
    });
  }

  // Close dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('#name-combobox-wrapper')) {
      closeNameDropdown();
    }
    if (!e.target.closest('#phone-combobox-wrapper')) {
      closePhoneDropdown();
    }
  });

  // =========================================================================
  // TRANSACTION CALCULATION & DYNAMIC SERVICES
  // =========================================================================
  const container = document.getElementById('service-items-container');
  const addServiceBtn = document.getElementById('btn-add-service');
  const subtotalEl = document.getElementById('summary-subtotal');
  const taxEl = document.getElementById('summary-tax');
  const totalEl = document.getElementById('summary-total');
  const saveChangesBtn = document.getElementById('btn-save-changes');
  const discardBtn = document.getElementById('btn-discard');
  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');

  function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
  }

  function showToast(message, duration = 2500) {
    if (!toastBox) return;
    if (toastMessage) toastMessage.textContent = message;
    toastBox.classList.add('show');
    setTimeout(() => {
      toastBox.classList.remove('show');
    }, duration);
  }

  function recalculateAll() {
    const serviceCards = document.querySelectorAll('.service-item-card');
    let totalSubtotal = 0;

    serviceCards.forEach((card, index) => {
      const serviceSelect = card.querySelector('.service-select');
      let basePrice = 1200000;
      if (serviceSelect && serviceSelect.selectedIndex >= 0) {
        const option = serviceSelect.options[serviceSelect.selectedIndex];
        basePrice = parseInt(option.getAttribute('data-price') || '0', 10);
      }

      const checkedHairLength = card.querySelector('input[data-field="hair-length"]:checked');
      let hairSurcharge = 0;
      if (checkedHairLength) {
        hairSurcharge = parseInt(checkedHairLength.getAttribute('data-surcharge') || '0', 10);
      }

      const itemSubtotal = basePrice + hairSurcharge;
      totalSubtotal += itemSubtotal;

      const hintEl = card.querySelector('.commission-suggestion');
      if (hintEl) {
        const suggested = Math.round(itemSubtotal * 0.15);
        hintEl.textContent = `Saran komisi untuk layanan ini: Rp ${formatNumber(suggested)} (15% dari subtotal)`;
      }
    });

    const tax = Math.round(totalSubtotal * 0.11);
    const total = totalSubtotal + tax;

    if (subtotalEl) subtotalEl.textContent = formatNumber(totalSubtotal);
    if (taxEl) taxEl.textContent = `Rp ${formatNumber(tax)}`;
    if (totalEl) totalEl.textContent = formatNumber(total);

    const deleteBtns = document.querySelectorAll('.btn-delete-service');
    deleteBtns.forEach(btn => {
      btn.style.display = serviceCards.length > 1 ? 'inline-flex' : 'none';
    });
  }

  function renumberServiceItems() {
    const serviceCards = document.querySelectorAll('.service-item-card');
    serviceCards.forEach((card, index) => {
      const num = index + 1;
      card.id = `service-item-${num}`;
      const titleLabel = card.querySelector('.service-item-number');
      if (titleLabel) {
        titleLabel.textContent = `SERVICE ITEM #${num}`;
      }

      const lengthRadios = card.querySelectorAll('input[data-field="hair-length"]');
      lengthRadios.forEach(radio => {
        radio.name = `hair-length-${num}`;
      });

      const thicknessRadios = card.querySelectorAll('input[data-field="hair-thickness"]');
      thicknessRadios.forEach(radio => {
        radio.name = `hair-thickness-${num}`;
      });
    });
  }

  function attachCardListeners(card) {
    const serviceSelect = card.querySelector('.service-select');
    if (serviceSelect) {
      serviceSelect.addEventListener('change', recalculateAll);
    }

    const lengthLabels = card.querySelectorAll('.segmented-pill-label');
    lengthLabels.forEach(label => {
      label.addEventListener('click', () => {
        const parentGroup = label.closest('.segmented-pill-group');
        if (parentGroup) {
          parentGroup.querySelectorAll('.segmented-pill-label').forEach(l => l.classList.remove('active'));
        }
        label.classList.add('active');
        const radio = label.querySelector('input[type="radio"]');
        if (radio) {
          radio.checked = true;
        }
        recalculateAll();
      });
    });

    const steppers = card.querySelectorAll('.dosage-stepper');
    steppers.forEach(stepper => {
      const minusBtn = stepper.querySelector('.btn-step-minus');
      const plusBtn = stepper.querySelector('.btn-step-plus');
      const input = stepper.querySelector('.stepper-input');

      if (minusBtn && input) {
        minusBtn.onclick = (e) => {
          e.preventDefault();
          let val = parseInt(input.value || '30', 10);
          if (val > 10) val -= 10;
          input.value = val;
        };
      }

      if (plusBtn && input) {
        plusBtn.onclick = (e) => {
          e.preventDefault();
          let val = parseInt(input.value || '30', 10);
          val += 10;
          input.value = val;
        };
      }
    });

    const addWorkerBtn = card.querySelector('[data-action="add-worker"]');
    const extraWorkersContainer = card.querySelector('.extra-stylists-container');
    if (addWorkerBtn && extraWorkersContainer) {
      addWorkerBtn.onclick = (e) => {
        e.preventDefault();
        const row = document.createElement('div');
        row.className = 'sub-row-item';
        row.innerHTML = `
          <div class="input-container-icon" style="flex: 1;">
            <svg class="input-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <select class="select-dropdown-field" data-field="stylist-name">
              <option value="ada-wong">Ada Wong (Lead Hair Stylist)</option>
              <option value="budi">Budi (Color Specialist)</option>
              <option value="rina">Rina (Hair Spa & Treatment)</option>
              <option value="dimas">Dimas (Barber & Grooming)</option>
              <option value="agus-pratama">Agus Pratama (Senior Stylist)</option>
            </select>
          </div>
          <button type="button" class="btn-remove-row" aria-label="Hapus Karyawan">✕</button>
        `;
        row.querySelector('.btn-remove-row').onclick = () => row.remove();
        extraWorkersContainer.appendChild(row);
      };
    }

    const addProductBtn = card.querySelector('[data-action="add-product"]');
    const extraProductsContainer = card.querySelector('.extra-products-container');
    if (addProductBtn && extraProductsContainer) {
      addProductBtn.onclick = (e) => {
        e.preventDefault();
        const row = document.createElement('div');
        row.className = 'sub-row-item';
        row.innerHTML = `
          <div class="input-container-icon" style="flex: 1;">
            <select class="select-dropdown-field" data-field="product-brand">
              <option value="kerastase">Kerastase Elixir Ultime</option>
              <option value="olaplex">Olaplex No.3</option>
              <option value="wella">Wella Color Charm</option>
              <option value="loreal">L'Oréal Majirel</option>
            </select>
          </div>
          <div class="dosage-stepper">
            <button type="button" class="btn-step btn-step-minus">−</button>
            <input type="number" step="10" min="10" value="20" class="stepper-input" data-field="product-amount-ml">
            <button type="button" class="btn-step btn-step-plus">+</button>
            <span class="unit-label">ml</span>
          </div>
          <button type="button" class="btn-remove-row" aria-label="Hapus Produk">✕</button>
        `;
        const mBtn = row.querySelector('.btn-step-minus');
        const pBtn = row.querySelector('.btn-step-plus');
        const inp = row.querySelector('.stepper-input');
        if (mBtn) mBtn.onclick = () => { let v = parseInt(inp.value, 10); if (v > 10) inp.value = v - 10; };
        if (pBtn) pBtn.onclick = () => { let v = parseInt(inp.value, 10); inp.value = v + 10; };
        row.querySelector('.btn-remove-row').onclick = () => row.remove();
        extraProductsContainer.appendChild(row);
      };
    }

    const deleteBtn = card.querySelector('[data-action="delete-service"]');
    if (deleteBtn) {
      deleteBtn.onclick = (e) => {
        e.preventDefault();
        const currentCards = document.querySelectorAll('.service-item-card');
        if (currentCards.length > 1) {
          card.remove();
          renumberServiceItems();
          recalculateAll();
        }
      };
    }
  }

  if (addServiceBtn && container) {
    addServiceBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const firstCard = container.querySelector('.service-item-card');
      if (!firstCard) return;

      const newCard = firstCard.cloneNode(true);

      const extraStylists = newCard.querySelector('.extra-stylists-container');
      if (extraStylists) extraStylists.innerHTML = '';

      const extraProducts = newCard.querySelector('.extra-products-container');
      if (extraProducts) extraProducts.innerHTML = '';

      const lengthLabels = newCard.querySelectorAll('.segmented-pill-label');
      lengthLabels.forEach((lbl, idx) => {
        const radio = lbl.querySelector('input[type="radio"]');
        if (idx === 0) {
          lbl.classList.add('active');
          if (radio) radio.checked = true;
        } else {
          lbl.classList.remove('active');
          if (radio) radio.checked = false;
        }
      });

      container.appendChild(newCard);
      renumberServiceItems();
      attachCardListeners(newCard);
      recalculateAll();
    });
  }

  const paymentOptions = document.querySelectorAll('.payment-option-item');
  paymentOptions.forEach(option => {
    option.addEventListener('click', () => {
      paymentOptions.forEach(opt => opt.classList.remove('active'));
      option.classList.add('active');
      const radio = option.querySelector('input[type="radio"]');
      if (radio) radio.checked = true;
    });
  });

  if (discardBtn) {
    discardBtn.addEventListener('click', (e) => {
      e.preventDefault();
      window.location.href = 'riwayat.html';
    });
  }

  if (saveChangesBtn) {
    saveChangesBtn.addEventListener('click', (e) => {
      e.preventDefault();

      const customerName = customerNameInput ? customerNameInput.value.trim() : '';
      const customerPhone = customerPhoneInput ? customerPhoneInput.value.trim() : '';

      if (!customerName) {
        showToast('⚠️ Mohon lengkapi nama pelanggan.');
        if (customerNameInput) customerNameInput.focus();
        return;
      }

      if (!customerPhone) {
        showToast('⚠️ Mohon lengkapi nomor telepon.');
        if (customerPhoneInput) customerPhoneInput.focus();
        return;
      }

      showToast('✅ Perubahan reservasi berhasil disimpan! Mengalihkan...');
      saveChangesBtn.style.pointerEvents = 'none';
      saveChangesBtn.style.opacity = '0.7';

      setTimeout(() => {
        window.location.href = 'riwayat.html';
      }, 1200);
    });
  }

  const initialCards = document.querySelectorAll('.service-item-card');
  initialCards.forEach(card => attachCardListeners(card));
  renumberServiceItems();
  recalculateAll();
});