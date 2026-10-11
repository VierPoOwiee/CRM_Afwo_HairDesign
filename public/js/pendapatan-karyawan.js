/**
 * AFWO Hair Design - Pendapatan Bulanan Karyawan Script
 * Handles monthly income breakdown calculations, month selector,
 * dynamic footer aggregation, and individual salary slip modal preview & printing.
 */

document.addEventListener('DOMContentLoaded', () => {

  // Database Simulation: Monthly Staff Income
  const monthlyDataStore = {
    '2026-09': {
      monthLabel: 'September 2026',
      records: [
        {
          id: 'agus-pratama',
          name: 'Agus Pratama',
          schemeType: 'daily',
          schemeLabel: '% Omset Harian (10.00%)',
          commService: 0,
          commDaily: 0,
          salary: 0,
          attendanceDays: 0,
          mealAllowance: 0
        },
        {
          id: 'budi-santoso',
          name: 'Budi Santoso',
          schemeType: 'daily',
          schemeLabel: '% Omset Harian (8.00%)',
          commService: 0,
          commDaily: 0,
          salary: 0,
          attendanceDays: 1,
          mealAllowance: 0
        },
        {
          id: 'dewi-anggraini',
          name: 'Dewi Anggraini',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 0,
          commDaily: 0,
          salary: 2750000,
          attendanceDays: 1,
          mealAllowance: 25000
        },
        {
          id: 'nara',
          name: 'nara',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 0,
          commDaily: 0,
          salary: 2800000,
          attendanceDays: 0,
          mealAllowance: 0
        },
        {
          id: 'rafi',
          name: 'rafi',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 0,
          commDaily: 0,
          salary: 3200000,
          attendanceDays: 0,
          mealAllowance: 0
        },
        {
          id: 'rina-kartika',
          name: 'Rina Kartika',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 0,
          commDaily: 0,
          salary: 3500000,
          attendanceDays: 0,
          mealAllowance: 0
        },
        {
          id: 'sari-melati',
          name: 'Sari Melati',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 0,
          commDaily: 0,
          salary: 3000000,
          attendanceDays: 0,
          mealAllowance: 0
        }
      ]
    },
    '2026-08': {
      monthLabel: 'Agustus 2026',
      records: [
        {
          id: 'agus-pratama',
          name: 'Agus Pratama',
          schemeType: 'daily',
          schemeLabel: '% Omset Harian (10.00%)',
          commService: 0,
          commDaily: 3250000,
          salary: 4000000,
          attendanceDays: 24,
          mealAllowance: 0
        },
        {
          id: 'budi-santoso',
          name: 'Budi Santoso',
          schemeType: 'daily',
          schemeLabel: '% Omset Harian (8.00%)',
          commService: 0,
          commDaily: 2400000,
          salary: 4200000,
          attendanceDays: 22,
          mealAllowance: 0
        },
        {
          id: 'dewi-anggraini',
          name: 'Dewi Anggraini',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 1850000,
          commDaily: 0,
          salary: 2750000,
          attendanceDays: 23,
          mealAllowance: 575000
        },
        {
          id: 'nara',
          name: 'nara',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 1600000,
          commDaily: 0,
          salary: 2800000,
          attendanceDays: 20,
          mealAllowance: 500000
        },
        {
          id: 'rafi',
          name: 'rafi',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 1450000,
          commDaily: 0,
          salary: 3200000,
          attendanceDays: 21,
          mealAllowance: 525000
        },
        {
          id: 'rina-kartika',
          name: 'Rina Kartika',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 1900000,
          commDaily: 0,
          salary: 3500000,
          attendanceDays: 22,
          mealAllowance: 550000
        },
        {
          id: 'sari-melati',
          name: 'Sari Melati',
          schemeType: 'service',
          schemeLabel: 'Per Layanan',
          commService: 1100000,
          commDaily: 0,
          salary: 3000000,
          attendanceDays: 18,
          mealAllowance: 450000
        }
      ]
    }
  };

  // DOM Elements
  const monthPicker = document.getElementById('select-month-picker');
  const btnShowMonth = document.getElementById('btn-show-month');
  const summaryMonthTitle = document.getElementById('summary-month-title');
  const summaryTotalAmount = document.getElementById('summary-total-all-income');
  const summaryBreakdown = document.getElementById('summary-breakdown-subtext');

  const tableBody = document.getElementById('monthly-income-body');
  const footCommService = document.getElementById('foot-comm-service');
  const footCommDaily = document.getElementById('foot-comm-daily');
  const footTotalComm = document.getElementById('foot-total-comm');
  const footTotalSalary = document.getElementById('foot-total-salary');
  const footTotalAttendance = document.getElementById('foot-total-attendance');
  const footTotalMeal = document.getElementById('foot-total-meal');
  const footGrandTotal = document.getElementById('foot-grand-total');

  // Modal Elements
  const modalOverlay = document.getElementById('modal-slip-overlay');
  const btnCloseSlip = document.getElementById('btn-close-slip');
  const btnCancelSlip = document.getElementById('btn-cancel-slip');
  const btnPrintSlipAction = document.getElementById('btn-print-slip-action');

  const slipWorkerName = document.getElementById('slip-worker-name');
  const slipMonthVal = document.getElementById('slip-month-val');
  const slipSchemeVal = document.getElementById('slip-scheme-val');
  const slipDaysVal = document.getElementById('slip-days-val');
  const slipSalaryVal = document.getElementById('slip-salary-val');
  const slipCommVal = document.getElementById('slip-comm-val');
  const slipMealVal = document.getElementById('slip-meal-val');
  const slipNetTotal = document.getElementById('slip-net-total');
  const slipSigWorkerName = document.getElementById('slip-sig-worker-name');

  function formatIDR(val) {
    if (!val || val === 0) return 'Rp0';
    return 'Rp' + Math.round(val).toLocaleString('id-ID');
  }

  function renderMonthlyIncome() {
    const selectedMonth = monthPicker ? monthPicker.value : '2026-09';
    const monthData = monthlyDataStore[selectedMonth] || monthlyDataStore['2026-09'];

    let aggCommService = 0;
    let aggCommDaily = 0;
    let aggTotalComm = 0;
    let aggTotalSalary = 0;
    let aggAttendance = 0;
    let aggMeal = 0;
    let aggGrandTotal = 0;

    tableBody.innerHTML = monthData.records.map(r => {
      const totalComm = r.commService + r.commDaily;
      const totalIncome = r.salary + totalComm + r.mealAllowance;

      aggCommService += r.commService;
      aggCommDaily += r.commDaily;
      aggTotalComm += totalComm;
      aggTotalSalary += r.salary;
      aggAttendance += r.attendanceDays;
      aggMeal += r.mealAllowance;
      aggGrandTotal += totalIncome;

      const badgeClass = r.schemeType === 'daily' ? 'scheme-badge-daily' : 'scheme-badge-service';

      return `
        <tr>
          <td>
            <strong style="color: var(--text-primary); font-size: 0.88rem;">${r.name}</strong>
          </td>
          <td>
            <span class="${badgeClass}">${r.schemeLabel}</span>
          </td>
          <td>
            <span style="color: var(--text-secondary);">${r.commService > 0 ? formatIDR(r.commService) : '-'}</span>
          </td>
          <td>
            <span style="color: var(--text-secondary);">${r.commDaily > 0 ? formatIDR(r.commDaily) : '-'}</span>
          </td>
          <td>
            <span style="font-weight: 700; color: ${totalComm > 0 ? '#B45309' : 'var(--text-muted)'};">${formatIDR(totalComm)}</span>
          </td>
          <td>
            <span style="font-weight: 600; color: var(--text-primary);">${r.salary > 0 ? formatIDR(r.salary) : 'Rp0'}</span>
          </td>
          <td>
            <span style="color: var(--text-primary);">${r.attendanceDays > 0 ? r.attendanceDays : '-'}</span>
          </td>
          <td>
            <span style="color: var(--text-secondary);">${r.mealAllowance > 0 ? formatIDR(r.mealAllowance) : '-'}</span>
          </td>
          <td>
            <strong style="color: var(--text-primary); font-size: 0.9rem;">${formatIDR(totalIncome)}</strong>
          </td>
          <td style="text-align: right;">
            <button type="button" class="btn-slip-detail js-open-monthly-slip" data-id="${r.id}">
              <span>Slip Detail</span>
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
              </svg>
            </button>
          </td>
        </tr>
      `;
    }).join('');

    // Update Footer Row
    if (footCommService) footCommService.textContent = aggCommService > 0 ? formatIDR(aggCommService) : 'Rp0';
    if (footCommDaily) footCommDaily.textContent = aggCommDaily > 0 ? formatIDR(aggCommDaily) : 'Rp0';
    if (footTotalComm) footTotalComm.textContent = formatIDR(aggTotalComm);
    if (footTotalSalary) footTotalSalary.textContent = formatIDR(aggTotalSalary);
    if (footTotalAttendance) footTotalAttendance.textContent = aggAttendance.toString();
    if (footTotalMeal) footTotalMeal.textContent = formatIDR(aggMeal);
    if (footGrandTotal) footGrandTotal.textContent = formatIDR(aggGrandTotal);

    // Update Top Highlight Banner
    if (summaryMonthTitle) {
      summaryMonthTitle.textContent = `Total Pendapatan Semua Karyawan — ${monthData.monthLabel}`;
    }
    if (summaryTotalAmount) {
      summaryTotalAmount.textContent = formatIDR(aggGrandTotal);
    }
    if (summaryBreakdown) {
      summaryBreakdown.textContent = `Komisi ${formatIDR(aggTotalComm)} + Gaji Pokok ${formatIDR(aggTotalSalary)} + Uang Makan ${formatIDR(aggMeal)}`;
    }

    // Attach click handlers to slip detail buttons
    document.querySelectorAll('.js-open-monthly-slip').forEach(btn => {
      btn.addEventListener('click', () => {
        const staffId = btn.getAttribute('data-id');
        openMonthlySlipModal(staffId, monthData);
      });
    });
  }

  function openMonthlySlipModal(staffId, monthData) {
    const record = monthData.records.find(r => r.id === staffId) || monthData.records[0];
    const totalComm = record.commService + record.commDaily;
    const totalBersih = record.salary + totalComm + record.mealAllowance;

    if (slipWorkerName) slipWorkerName.textContent = record.name;
    if (slipSigWorkerName) slipSigWorkerName.textContent = record.name;
    if (slipMonthVal) slipMonthVal.textContent = monthData.monthLabel;
    if (slipSchemeVal) slipSchemeVal.textContent = record.schemeLabel;
    if (slipDaysVal) slipDaysVal.textContent = `${record.attendanceDays} Hari Hadir (Uang Makan ${formatIDR(record.mealAllowance)})`;
    if (slipSalaryVal) slipSalaryVal.textContent = formatIDR(record.salary);
    if (slipCommVal) slipCommVal.textContent = formatIDR(totalComm);
    if (slipMealVal) slipMealVal.textContent = formatIDR(record.mealAllowance);
    if (slipNetTotal) slipNetTotal.textContent = formatIDR(totalBersih);

    if (modalOverlay) modalOverlay.style.display = 'flex';
  }

  function closeSlipModal() {
    if (modalOverlay) modalOverlay.style.display = 'none';
  }

  if (btnShowMonth) btnShowMonth.addEventListener('click', renderMonthlyIncome);
  if (monthPicker) monthPicker.addEventListener('change', renderMonthlyIncome);

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
  renderMonthlyIncome();

});
