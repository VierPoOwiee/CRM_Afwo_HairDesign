/**
 * AFWO Hair Design - Add / Edit Service Script
 * Handles dual-mode (?mode=add vs ?mode=edit&id=...), dynamic variant rows, Rupiah formatting, validation, and saving.
 */

document.addEventListener('DOMContentLoaded', () => {
  const servicesData = {
    'signature-master-cut': {
      name: 'Signature Master Cut',
      category: 'potong',
      active: true,
      includeCut: true,
      variants: [
        { name: 'Default', priceMin: '450.000', priceMax: '', commMin: '45.000', commMax: '' }
      ]
    },
    'balayage-toner': {
      name: 'Signature Balayage & Tone',
      category: 'warna',
      active: true,
      includeCut: false,
      variants: [
        { name: 'Short (S)', priceMin: '1.450.000', priceMax: '1.650.000', commMin: '145.000', commMax: '165.000' },
        { name: 'Medium (M)', priceMin: '1.850.000', priceMax: '2.100.000', commMin: '185.000', commMax: '210.000' },
        { name: 'Long (L)', priceMin: '2.350.000', priceMax: '2.700.000', commMin: '235.000', commMax: '270.000' }
      ]
    },
    'luxury-scalp-therapy': {
      name: 'Luxury Scalp Therapy',
      category: 'spa',
      active: true,
      includeCut: false,
      variants: [
        { name: 'Default', priceMin: '600.000', priceMax: '', commMin: '60.000', commMax: '' }
      ]
    },
    'blowout-styling': {
      name: 'Blowout & Styling',
      category: 'styling',
      active: true,
      includeCut: false,
      variants: [
        { name: 'Short', priceMin: '250.000', priceMax: '', commMin: '25.000', commMax: '' },
        { name: 'Long', priceMin: '350.000', priceMax: '', commMin: '35.000', commMax: '' }
      ]
    }
  };

  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode') || 'add';
  const serviceId = urlParams.get('id');

  const pageTitle = document.getElementById('edit-layanan-title');
  const pageSubtitle = document.getElementById('edit-layanan-subtitle');
  const nameInput = document.getElementById('input-service-name');
  const categorySelect = document.getElementById('select-category');
  const activeCheckbox = document.getElementById('checkbox-service-active');
  const includeCutCheckbox = document.getElementById('checkbox-include-cut');
  
  const variantsContainer = document.getElementById('variants-rows-container');
  const btnAddVariantRow = document.getElementById('btn-add-variant-row');
  const btnDefaultProductVariant = document.getElementById('btn-default-product-variant');

  const saveBtn = document.getElementById('btn-save-layanan');
  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');

  function formatNumberRupiah(val) {
    let clean = val.replace(/[^0-9]/g, '');
    if (!clean) return '';
    return parseInt(clean, 10).toLocaleString('id-ID');
  }

  function attachRowFormatters(row) {
    const numInputs = row.querySelectorAll('.js-rupiah-input');
    numInputs.forEach(input => {
      input.addEventListener('input', (e) => {
        e.target.value = formatNumberRupiah(e.target.value);
      });
    });

    const delBtn = row.querySelector('.btn-delete-variant');
    if (delBtn) {
      delBtn.addEventListener('click', () => {
        const totalRows = variantsContainer.querySelectorAll('.variant-item-row').length;
        if (totalRows > 1) {
          row.remove();
        } else {
          // Clear inputs if only 1 row left
          row.querySelectorAll('input').forEach(inp => inp.value = '');
          showToast('Baris varian dikosongkan.');
        }
      });
    }
  }

  function createVariantRow(data = {}) {
    const row = document.createElement('div');
    row.className = 'variant-item-row';
    row.innerHTML = `
      <div class="variant-input-box">
        <input type="text" class="variant-input-field js-var-name" placeholder="default / S / M / L / X" value="${data.name || ''}">
      </div>
      <div class="variant-input-box">
        <input type="text" class="variant-input-field js-rupiah-input js-var-pmin" placeholder="150.000" value="${data.priceMin || ''}">
      </div>
      <div class="variant-input-box">
        <input type="text" class="variant-input-field js-rupiah-input js-var-pmax" placeholder="200.000" value="${data.priceMax || ''}">
      </div>
      <div class="variant-input-box">
        <input type="text" class="variant-input-field js-rupiah-input js-var-kmin" placeholder="25.000" value="${data.commMin || ''}">
      </div>
      <div class="variant-input-box">
        <input type="text" class="variant-input-field js-rupiah-input js-var-kmax" placeholder="35.000" value="${data.commMax || ''}">
      </div>
      <button type="button" class="btn-delete-variant" title="Hapus Baris">Hapus</button>
    `;

    attachRowFormatters(row);
    return row;
  }

  // Add row button
  if (btnAddVariantRow) {
    btnAddVariantRow.addEventListener('click', () => {
      const newRow = createVariantRow();
      variantsContainer.appendChild(newRow);
      newRow.querySelector('.js-var-name').focus();
    });
  }

  // Default Produk Varian button
  if (btnDefaultProductVariant) {
    btnDefaultProductVariant.addEventListener('click', () => {
      showToast('💡 Default Produk Varian siap digunakan saat input transaksi.');
    });
  }

  // Populate data in Edit mode
  if (mode === 'edit') {
    if (pageTitle) pageTitle.textContent = 'Edit Layanan';
    if (pageSubtitle) pageSubtitle.textContent = 'Perbarui detail layanan, varian harga & skema komisi.';
    const data = (serviceId && servicesData[serviceId]) ? servicesData[serviceId] : servicesData['balayage-toner'];
    
    if (nameInput) nameInput.value = data.name;
    if (categorySelect) categorySelect.value = data.category;
    if (activeCheckbox) activeCheckbox.checked = data.active !== false;
    if (includeCutCheckbox) includeCutCheckbox.checked = !!data.includeCut;

    if (variantsContainer) {
      variantsContainer.innerHTML = '';
      if (data.variants && data.variants.length > 0) {
        data.variants.forEach(v => {
          variantsContainer.appendChild(createVariantRow(v));
        });
      } else {
        variantsContainer.appendChild(createVariantRow({ name: 'Default', priceMin: data.price || '' }));
      }
    }
  } else {
    if (pageTitle) pageTitle.textContent = 'Tambah Layanan';
    if (pageSubtitle) pageSubtitle.textContent = 'Lengkapi layanan beserta varian harga & komisinya.';
    if (variantsContainer) {
      variantsContainer.innerHTML = '';
      variantsContainer.appendChild(createVariantRow());
    }
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
      const cat = categorySelect ? categorySelect.value : '';

      if (!name) {
        showToast('⚠️ Nama layanan wajib diisi.');
        if (nameInput) nameInput.focus();
        return;
      }

      if (!cat) {
        showToast('⚠️ Kategori layanan wajib dipilih.');
        if (categorySelect) categorySelect.focus();
        return;
      }

      // Check variant rows
      const rows = variantsContainer.querySelectorAll('.variant-item-row');
      let hasValidPrice = false;
      rows.forEach(r => {
        const pmin = r.querySelector('.js-var-pmin').value.trim();
        if (pmin) hasValidPrice = true;
      });

      if (!hasValidPrice) {
        showToast('⚠️ Masukkan minimal satu harga pada varian.');
        return;
      }

      showToast('✅ Layanan berhasil disimpan! Mengalihkan...');
      saveBtn.style.pointerEvents = 'none';
      saveBtn.style.opacity = '0.7';

      setTimeout(() => {
        window.location.href = 'services.html';
      }, 1200);
    });
  }
});