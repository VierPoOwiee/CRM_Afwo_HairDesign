/**
 * AFWO Hair Design - Rekap Komisi & Slip Script
 * Manages commission summary calculations, date filters, employee breakdown tables,
 * and individual salary/commission slip modal preview with print capabilities.
 */

document.addEventListener('DOMContentLoaded', () => {

  // Database Simulation: Employee Commission & Salary Data
  const commissionStaffData = [
    {
      id: 'agus-pratama',
      name: 'Agus Pratama',
      avatar: 'AP',
      schemeType: 'daily',
      schemeLabel: '% Omset Harian (10.00%)',
      salary: 4000000,
      commission: 3250000,
      mealAllowance: 25000
    },
    {
      id: 'budi-santoso',
      name: 'Budi Santoso',
      avatar: 'BS',
      schemeType: 'daily',
      schemeLabel: '% Omset Harian (8.00%)',
      salary: 4200000,
      commission: 2400000,
      mealAllowance: 0
    },
    {
      id: 'dewi-anggraini',
      name: 'Dewi Anggraini',
      avatar: 'DA',
      schemeType: 'service',
      schemeLabel: 'Per Layanan',
      salary: 2750000,
      commission: 1850000,
      mealAllowance: 25000
    },
    {
      id: 'nara',
      name: 'Nara',
      avatar: 'NR',
      schemeType: 'service',
      schemeLabel: 'Per Layanan',
      salary: 2800000,
      commission: 1600000,
      mealAllowance: 0
    },
    {
      id: 'rafi',
      name: 'Rafi',
      avatar: 'RF',
      schemeType: 'service',
      schemeLabel: 'Per Layanan',
      salary: 3200000,
      commission: 1450000,
      mealAllowance: 0
    },
    {
      id: 'rina-kartika',
      name: 'Rina Kartika',
      avatar: 'RK',
      schemeType: 'service',
      schemeLabel: 'Per Layanan',
      salary: 3500000,
      commission: 1900000,
      mealAllowance: 0
    },
    {
      id: 'sari-melati',
      name: 'Sari Melati',
      avatar: 'SM',
      schemeType: 'service',
      schemeLabel: 'Per Layanan',
      salary: 3000000,
      commission: 0,
      mealAllowance: 0
    }
  ];

  // DOM Elements
  const quickPeriodSelect = document.getElementById('filter-quick-period');
  const dateFromInput = document.getElementById('filter-date-from');
  const dateToInput = document.getElementById('filter-date-to');
  const applyFilterBtn = document.getElementById('btn-apply-filter');
  const resetFilterBtn = document.getElementById('btn-reset-filter');

  const summaryAmountEl = document.getElementById('summary-total-commission');
  const summaryPeriodEl = document.getElementById('summary-period-label');
  const tableBody = document.getElementById('commission-table-body');
  const emptyBox = document.getElementById('rekap-empty-box');

  // Modal Elements
  const modalOverlay = document.getElementById('modal-slip-overlay');
  const btnCloseSlip = document.getElementById('btn-close-slip');
  const btnCancelSlip = document.getElementById('btn-cancel-slip');
  const btnPrintSlipAction = document.getElementById('btn-print-slip-action');

  const slipWorkerName = document.getElementById('slip-worker-name');
  const slipPeriodVal = document.getElementById('slip-period-val');
  const slipSchemeVal = document.getElementById('slip-scheme-val');
  const slipSalaryVal = document.getElementById('slip-salary-val');
  const slipCommVal = document.getElementById('slip-comm-val');
  const slipMealVal = document.getElementById('slip-meal-val');
  const slipNetTotal = document.getElementById('slip-net-total');
  const slipSigWorkerName = document.getElementById('slip-sig-worker-name');

  function formatIDR(val) {
    return 'Rp' + Math.round(val).toLocaleString('id-ID');
  }

  function renderCommissionTable() {
    let totalCommissionAll = 0;

    tableBody.innerHTML = commissionStaffData.map(staff => {
      totalCommissionAll += staff.commission;
      const totalIncome = staff.salary + staff.commission;
      const badgeClass = staff.schemeType === 'daily' ? 'scheme-badge-daily' : 'scheme-badge-service';

      return `
        <tr>
          <td>
            <div style="display: flex; align-items: center; gap: 10px;">
              <strong style="color: var(--text-primary); font-size: 0.9rem;">${staff.name}</strong>
            </div>
          </td>
          <td>
            <span class="${badgeClass}">${staff.schemeLabel}</span>
          </td>
          <td>
            <span style="font-weight: 600; color: var(--text-primary);">${staff.salary > 0 ? formatIDR(staff.salary) : 'Rp0'}</span>
          </td>
          <td>
            <span style="font-weight: 700; color: ${staff.commission > 0 ? '#B45309' : 'var(--text-muted)'};">${formatIDR(staff.commission)}</span>
          </td>
          <td>
            <strong style="color: var(--text-primary); font-size: 0.92rem;">${formatIDR(totalIncome)}</strong>
          </td>
          <td style="text-align: right;">
            <button type="button" class="btn-view-slip js-open-slip" data-id="${staff.id}">
              <span>Lihat Slip</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </td>
        </tr>
      `;
    }).join('');

    // Update Summary Banner
    if (summaryAmountEl) {
      summaryAmountEl.textContent = formatIDR(totalCommissionAll);
    }

    if (summaryPeriodEl && dateFromInput && dateToInput) {
      summaryPeriodEl.textContent = `Periode: ${dateFromInput.value} s/d ${dateToInput.value}`;
    }

    // Attach click events to "Lihat Slip" buttons
    document.querySelectorAll('.js-open-slip').forEach(btn => {
      btn.addEventListener('click', () => {
        const staffId = btn.getAttribute('data-id');
        openSlipModal(staffId);
      });
    });
  }

  function openSlipModal(staffId) {
    const staff = commissionStaffData.find(s => s.id === staffId) || commissionStaffData[0];
    const totalBersih = staff.salary + staff.commission + staff.mealAllowance;

    if (slipWorkerName) slipWorkerName.textContent = staff.name;
    if (slipSigWorkerName) slipSigWorkerName.textContent = staff.name;
    if (slipSchemeVal) slipSchemeVal.textContent = staff.schemeLabel;
    if (slipSalaryVal) slipSalaryVal.textContent = formatIDR(staff.salary);
    if (slipCommVal) slipCommVal.textContent = formatIDR(staff.commission);
    if (slipMealVal) slipMealVal.textContent = formatIDR(staff.mealAllowance);
    if (slipNetTotal) slipNetTotal.textContent = formatIDR(totalBersih);

    if (modalOverlay) modalOverlay.style.display = 'flex';
  }

  function closeSlipModal() {
    if (modalOverlay) modalOverlay.style.display = 'none';
  }

  // Quick Period Switcher Handler
  if (quickPeriodSelect) {
    quickPeriodSelect.addEventListener('change', () => {
      const val = quickPeriodSelect.value;
      if (val === 'bulan-ini') {
        dateFromInput.value = '2026-09-01';
        dateToInput.value = '2026-09-30';
      } else if (val === 'bulan-lalu') {
        dateFromInput.value = '2026-08-01';
        dateToInput.value = '2026-08-31';
      } else if (val === '30-hari') {
        dateFromInput.value = '2026-08-10';
        dateToInput.value = '2026-09-09';
      } else if (val === 'tahun-ini') {
        dateFromInput.value = '2026-01-01';
        dateToInput.value = '2026-12-31';
      }
      renderCommissionTable();
    });
  }

  if (applyFilterBtn) {
    applyFilterBtn.addEventListener('click', renderCommissionTable);
  }

  if (resetFilterBtn) {
    resetFilterBtn.addEventListener('click', () => {
      if (quickPeriodSelect) quickPeriodSelect.value = 'bulan-ini';
      if (dateFromInput) dateFromInput.value = '2026-09-01';
      if (dateToInput) dateToInput.value = '2026-09-30';
      renderCommissionTable();
    });
  }

  // Modal Handlers
  if (btnCloseSlip) btnCloseSlip.addEventListener('click', closeSlipModal);
  if (btnCancelSlip) btnCancelSlip.addEventListener('click', closeSlipModal);

  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) closeSlipModal();
    });
  }

  if (btnPrintSlipAction) {
    btnPrintSlipAction.addEventListener('click', () => {
      window.print();
    });
  }

  // Initial Render
  renderCommissionTable();

});
