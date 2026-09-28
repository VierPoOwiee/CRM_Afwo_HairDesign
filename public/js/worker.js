/**
 * AFWO Hair Design - Worker Directory Script (Laravel)
 * Row detail modal + auto-submit toolbar selects.
 */

document.addEventListener('DOMContentLoaded', () => {
  const toolbarForm = document.querySelector('.js-toolbar-form');

  if (toolbarForm) {
    toolbarForm.querySelectorAll('.js-auto-submit').forEach((sel) => {
      sel.addEventListener('change', () => {
        if (toolbarForm.checkValidity()) toolbarForm.submit();
      });
    });
  }

  const detailModal = document.getElementById('worker-detail-modal');
  const btnCloseModal = document.getElementById('btn-close-worker-modal');
  const mdl = {
    name: document.getElementById('mdl-worker-name'),
    phone: document.getElementById('mdl-worker-phone'),
    salary: document.getElementById('mdl-worker-salary'),
    scheme: document.getElementById('mdl-worker-scheme'),
    rate: document.getElementById('mdl-worker-rate'),
  };
  const mdlBtnEdit = document.getElementById('mdl-btn-worker-edit');

  function openDetailModal(row) {
    if (!detailModal || !row) return;

    const set = (el, val) => {
      if (el) el.textContent = val || '-';
    };

    set(mdl.name, row.dataset.name);
    set(mdl.phone, row.dataset.phone);
    set(mdl.salary, row.dataset.salary);
    set(mdl.scheme, row.dataset.scheme);
    set(mdl.rate, row.dataset.rate);

    if (mdlBtnEdit && row.dataset.editUrl) mdlBtnEdit.href = row.dataset.editUrl;

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

  document.querySelectorAll('.js-worker-row').forEach((row) => {
    row.addEventListener('click', () => openDetailModal(row));
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeDetailModal();
  });
});