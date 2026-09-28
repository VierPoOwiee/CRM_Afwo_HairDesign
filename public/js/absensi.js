/**
 * AFWO Hair Design - Absensi Karyawan Engine
 * Handles daily attendance checking, calculations for uang makan (Rp25.000/hari for per layanan scheme),
 * and monthly attendance aggregation.
 */

document.addEventListener('DOMContentLoaded', () => {
  // Workers list with their schemes
  const workers = [
    { id: 'agus-pratama', name: 'Agus Pratama', scheme: 'daily_percentage', schemeLabel: '% Omset Harian' },
    { id: 'budi-santoso', name: 'Budi Santoso', scheme: 'daily_percentage', schemeLabel: '% Omset Harian' },
    { id: 'dewi-anggraini', name: 'Dewi Anggraini', scheme: 'per_service', schemeLabel: 'Per Layanan' },
    { id: 'rangga', name: 'rangga', scheme: 'per_service', schemeLabel: 'Per Layanan' },
    { id: 'rina-kartika', name: 'Rina Kartika', scheme: 'per_service', schemeLabel: 'Per Layanan' },
    { id: 'sari-melati', name: 'Sari Melati', scheme: 'per_service', schemeLabel: 'Per Layanan' }
  ];

  // Daily attendance state (Default for 17/09/2026 based on reference image)
  const attendanceRecords = {
    '2026-09-17': {
      'agus-pratama': true,
      'budi-santoso': true,
      'dewi-anggraini': true,
      'rangga': true,
      'rina-kartika': false,
      'sari-melati': false
    }
  };

  // Monthly summary initial dummy data (September 2026 based on reference image)
  const monthlySummary = {
    '2026-09': {
      'agus-pratama': 11,
      'budi-santoso': 12,
      'dewi-anggraini': 9,
      'rangga': 1,
      'rina-kartika': 9,
      'sari-melati': 11
    }
  };

  const UANG_MAKAN_PER_DAY = 25000;

  // DOM Elements
  const dateInput = document.getElementById('input-attendance-date');
  const catatTbody = document.getElementById('catat-kehadiran-tbody');
  const rekapTbody = document.getElementById('rekap-kehadiran-tbody');
  const totalHadirEl = document.getElementById('rekap-total-hadir');
  const totalUangMakanEl = document.getElementById('rekap-total-uang-makan');
  const btnSaveAttendance = document.getElementById('btn-save-attendance');
  const selectMonth = document.getElementById('select-rekap-month');
  const btnFilterRekap = document.getElementById('btn-filter-rekap');
  const attendanceToast = document.getElementById('attendance-toast');

  function formatIDR(amount) {
    if (!amount || amount === 0) return '-';
    return 'Rp' + Number(amount).toLocaleString('id-ID');
  }

  function showToast(message) {
    if (!attendanceToast) return;
    attendanceToast.textContent = message;
    attendanceToast.className = 'absensi-toast is-success';
    attendanceToast.style.display = 'block';

    setTimeout(() => {
      attendanceToast.style.display = 'none';
    }, 3000);
  }

  // 1. Render Daily Attendance Table
  function renderDailyAttendance(dateStr) {
    if (!catatTbody) return;

    if (!attendanceRecords[dateStr]) {
      // Default all to true or false
      attendanceRecords[dateStr] = {};
      workers.forEach(w => {
        attendanceRecords[dateStr][w.id] = true;
      });
    }

    const currentDayRecord = attendanceRecords[dateStr];

    catatTbody.innerHTML = workers.map(w => {
      const isPresent = currentDayRecord[w.id] !== false;
      const isOmset = w.scheme === 'daily_percentage';
      const badgeClass = isOmset ? 'badge-skema-omset' : 'badge-skema-layanan';

      return `
        <tr data-worker-id="${w.id}">
          <td>
            <span class="worker-name-bold">${w.name}</span>
          </td>
          <td>
            <span class="${badgeClass}">${w.schemeLabel}</span>
          </td>
          <td style="text-align: center;">
            <div class="absensi-checkbox-wrap">
              <input 
                type="checkbox" 
                class="absensi-checkbox js-attendance-check" 
                data-worker-id="${w.id}" 
                ${isPresent ? 'checked' : ''}
              >
            </div>
          </td>
        </tr>
      `;
    }).join('');
  }

  // 2. Render Monthly Summary Table
  function renderMonthlySummary(monthKey) {
    if (!rekapTbody) return;

    const currentMonthData = monthlySummary[monthKey] || {};
    let totalAllHadir = 0;
    let totalAllUangMakan = 0;

    rekapTbody.innerHTML = workers.map(w => {
      const hadirCount = currentMonthData[w.id] || 0;
      const isOmset = w.scheme === 'daily_percentage';
      const badgeClass = isOmset ? 'badge-skema-omset' : 'badge-skema-layanan';
      
      // Karyawan % omset harian tidak menerima uang makan
      const uangMakan = isOmset ? 0 : (hadirCount * UANG_MAKAN_PER_DAY);

      totalAllHadir += hadirCount;
      totalAllUangMakan += uangMakan;

      return `
        <tr>
          <td>
            <span class="worker-name-bold">${w.name}</span>
          </td>
          <td>
            <span class="${badgeClass}">${w.schemeLabel}</span>
          </td>
          <td style="text-align: center; font-weight: 700;">
            ${hadirCount}
          </td>
          <td style="text-align: right; font-weight: 700; color: var(--text-primary);">
            ${isOmset ? '-' : formatIDR(uangMakan)}
          </td>
        </tr>
      `;
    }).join('');

    if (totalHadirEl) totalHadirEl.textContent = totalAllHadir;
    if (totalUangMakanEl) totalUangMakanEl.textContent = 'Rp' + totalAllUangMakan.toLocaleString('id-ID');
  }

  // Event Listeners
  if (dateInput) {
    dateInput.addEventListener('change', (e) => {
      renderDailyAttendance(e.target.value);
    });
  }

  if (btnSaveAttendance) {
    btnSaveAttendance.addEventListener('click', () => {
      const activeDate = dateInput ? dateInput.value : '2026-09-17';
      if (!attendanceRecords[activeDate]) attendanceRecords[activeDate] = {};

      const checkboxes = document.querySelectorAll('.js-attendance-check');
      checkboxes.forEach(cb => {
        const wId = cb.getAttribute('data-worker-id');
        attendanceRecords[activeDate][wId] = cb.checked;
      });

      showToast(`Kehadiran karyawan untuk tanggal ${activeDate} berhasil disimpan!`);
    });
  }

  if (btnFilterRekap) {
    btnFilterRekap.addEventListener('click', () => {
      const activeMonth = selectMonth ? selectMonth.value : '2026-09';
      renderMonthlySummary(activeMonth);
    });
  }

  // Initial Execution
  const initialDate = dateInput ? dateInput.value : '2026-09-17';
  renderDailyAttendance(initialDate);
  renderMonthlySummary('2026-09');
});
