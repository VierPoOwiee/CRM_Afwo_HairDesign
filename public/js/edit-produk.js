/**
 * AFWO Hair Design - Add / Edit Product Script
 * Handles dual-mode (?mode=add vs ?mode=edit&id=...), radio category selection, brand addition, Rupiah formatting, and saving.
 */

document.addEventListener('DOMContentLoaded', () => {
  const productsData = {
    'olaplex-no3': {
      name: 'Olaplex No.3 Hair Perfector',
      brand: 'Olaplex',
      category: 'retail',
      unit: '/pcs',
      price: '285.000',
      active: true
    },
    'kerastase-elixir': {
      name: 'Kerastase Elixir Ultime',
      brand: 'Kerastase',
      category: 'retail',
      unit: '/pcs',
      price: '420.000',
      active: true
    },
    'wella-color-charm': {
      name: 'Wella Color Charm',
      brand: 'Wella',
      category: 'color',
      unit: '/10ml',
      price: '15.000',
      active: true
    },
    'loreal-majirel': {
      name: 'L\'Oréal Majirel',
      brand: 'L\'Oréal',
      category: 'color',
      unit: '/10ml',
      price: '18.000',
      active: true
    }
  };

  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode') || 'add';
  const productId = urlParams.get('id');

  const pageTitle = document.getElementById('edit-produk-title');
  const pageSubtitle = document.getElementById('edit-produk-subtitle');
  const nameInput = document.getElementById('input-product-name');
  const brandSelect = document.getElementById('select-product-brand');
  const btnAddBrand = document.getElementById('btn-add-brand');
  const unitInput = document.getElementById('input-unit');
  const unitHelpText = document.getElementById('unit-help-text');
  const priceInput = document.getElementById('input-price');
  const priceHelpText = document.getElementById('price-help-text');
  const activeCheckbox = document.getElementById('checkbox-active');
  const radioInputs = document.querySelectorAll('input[name="product-category"]');

  const saveBtn = document.getElementById('btn-save-produk');
  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');

  // Handle Radio Selection Change
  function updateRadioState() {
    radioInputs.forEach(radio => {
      const parentCard = radio.closest('.category-radio-card');
      if (radio.checked) {
        if (parentCard) parentCard.classList.add('active');
        const unit = radio.getAttribute('data-unit') || '/10ml';
        const help = radio.getAttribute('data-help') || 'Harga modal per 10ml bahan yang digunakan saat layanan.';
        
        if (unitInput) unitInput.value = unit;
        if (unitHelpText) unitHelpText.textContent = `Satuan otomatis: ${unit}`;
        if (priceHelpText) priceHelpText.textContent = help;
      } else {
        if (parentCard) parentCard.classList.remove('active');
      }
    });
  }

  radioInputs.forEach(radio => {
    radio.addEventListener('change', updateRadioState);
  });

  // Add Brand Button
  if (btnAddBrand) {
    btnAddBrand.addEventListener('click', () => {
      const newBrand = prompt('Ketik nama merek baru:');
      if (newBrand && newBrand.trim()) {
        const cleanBrand = newBrand.trim();
        // Check if already exists
        let exists = false;
        Array.from(brandSelect.options).forEach(opt => {
          if (opt.value.toLowerCase() === cleanBrand.toLowerCase()) exists = true;
        });

        if (!exists) {
          const opt = document.createElement('option');
          opt.value = cleanBrand;
          opt.textContent = cleanBrand;
          brandSelect.appendChild(opt);
        }
        brandSelect.value = cleanBrand;
        showToast(`✓ Merek "${cleanBrand}" berhasil ditambahkan!`);
      }
    });
  }

  // Price formatting
  if (priceInput) {
    priceInput.addEventListener('input', (e) => {
      let val = e.target.value.replace(/[^0-9]/g, '');
      if (val) {
        val = parseInt(val, 10).toLocaleString('id-ID');
      }
      e.target.value = val;
    });
  }

  // Populate data in Edit mode
  if (mode === 'edit') {
    if (pageTitle) pageTitle.textContent = 'Edit Produk';
    if (pageSubtitle) pageSubtitle.textContent = 'Perbarui data dan tarif modal produk.';
    const data = (productId && productsData[productId]) ? productsData[productId] : productsData['wella-color-charm'];
    
    if (nameInput) nameInput.value = data.name;
    if (brandSelect) brandSelect.value = data.brand;
    if (priceInput) priceInput.value = data.price;
    if (activeCheckbox) activeCheckbox.checked = data.active !== false;

    // Select matching radio
    radioInputs.forEach(radio => {
      if (radio.value === data.category) {
        radio.checked = true;
      }
    });
    updateRadioState();
  } else {
    if (pageTitle) pageTitle.textContent = 'Tambah Produk';
    if (pageSubtitle) pageSubtitle.textContent = 'Lengkapi data produk baru di bawah ini.';
    updateRadioState();
  }

  function showToast(message, duration = 2500) {
    if (!toastBox) return;
    if (toastMessage) toastMessage.textContent = message;
    toastBox.classList.add('show');
    setTimeout(() => {
      toastBox.classList.remove('show');
    }, duration);
  }

  if (saveBtn) {
    saveBtn.addEventListener('click', (e) => {
      e.preventDefault();

      const name = nameInput ? nameInput.value.trim() : '';
      const brand = brandSelect ? brandSelect.value : '';
      const price = priceInput ? priceInput.value.trim() : '';

      if (!name) {
        showToast('⚠️ Nama produk wajib diisi.');
        if (nameInput) nameInput.focus();
        return;
      }

      if (!brand) {
        showToast('⚠️ Merek produk wajib dipilih.');
        if (brandSelect) brandSelect.focus();
        return;
      }

      if (!price) {
        showToast('⚠️ Harga per satuan wajib diisi.');
        if (priceInput) priceInput.focus();
        return;
      }

      showToast('✅ Produk berhasil disimpan! Mengalihkan...');
      saveBtn.style.pointerEvents = 'none';
      saveBtn.style.opacity = '0.7';

      setTimeout(() => {
        window.location.href = 'produk.html';
      }, 1200);
    });
  }
});