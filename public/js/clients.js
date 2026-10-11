/**
 * AFWO Hair Design - Client Directory Script (Laravel)
 * Row detail modal + auto-submit toolbar selects.
 */

document.addEventListener('DOMContentLoaded', () => {
  const toolbarForm = document.querySelector('.js-toolbar-form');

  toolbarForm.querySelectorAll('.js-auto-submit').forEach((sel) => {
    sel.addEventListener('change', () => {
      if (toolbarForm.checkValidity()) toolbarForm.submit();
    });
  });

  const detailModal = document.getElementById('client-detail-modal');
  const btnCloseModal = document.getElementById('btn-close-client-modal');
  const mdl = {
    name: document.getElementById('mdl-client-name'),
    phone: document.getElementById('mdl-client-phone'),
    instagram: document.getElementById('mdl-client-instagram'),
    gender: document.getElementById('mdl-client-gender'),
    hairType: document.getElementById('mdl-client-hair-type'),
    hairCondition: document.getElementById('mdl-client-hair-condition'),
    domicile: document.getElementById('mdl-client-domicile'),
    notes: document.getElementById('mdl-client-notes'),
  };
  const mdlBtnEdit = document.getElementById('mdl-btn-edit');
  const mdlBtnAppointment = document.getElementById('mdl-btn-appointment');

  function openDetailModal(row) {
    if (!detailModal) return;
    if (!row) return;

    const set = (el, val) => {
      if (el) el.textContent = val || '-';
    };

    set(mdl.name, row.dataset.name);
    set(mdl.phone, row.dataset.phone);
    set(mdl.instagram, row.dataset.instagram);
    set(mdl.gender, row.dataset.gender);
    set(mdl.hairType, row.dataset.hairType);
    set(mdl.hairCondition, row.dataset.hairCondition);
    set(mdl.domicile, row.dataset.domicile);
    set(mdl.notes, row.dataset.notes || 'Tidak ada catatan khusus.');

    if (mdlBtnEdit && row.dataset.editUrl) mdlBtnEdit.href = row.dataset.editUrl;
    if (mdlBtnAppointment && row.dataset.appointmentUrl) mdlBtnAppointment.href = row.dataset.appointmentUrl;

    detailModal.classList.add('is-open');
  }

  function closeDetailModal() {
    if (detailModal) detailModal.classList.remove('is-open');
  }

  if (btnCloseModal) btnCloseModal.addEventListener('click', closeDetailModal);
  if (detailModal) {
    detailModal.addEventListener('click', (e) => {
      if (e.target === detailModal) closeDetailModal();
    });
  }

  document.querySelectorAll('.js-client-row').forEach((row) => {
    row.addEventListener('click', () => openDetailModal(row));
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetailModal();
  });
});