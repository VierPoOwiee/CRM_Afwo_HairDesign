/**
 * AFWO Hair Design - Data Pelanggan Aktif Controller
 * Manages dynamic date-range calculations, transaction aggregation per client,
 * quick period switches (Bulan Ini, Bulan Lalu, 30 Hari, Tahun Ini),
 * live search, multi-criteria sorting, and responsive table/empty state rendering.
 */

document.addEventListener('DOMContentLoaded', () => {

  // =========================================================================
  // 1. DATA REPOSITORY (INTEGRASI TRANSAKSI & PELANGGAN SALON)
  // =========================================================================
  const allClients = [
    {
      id: 'eleanor-vance',
      name: 'Eleanor Vance',
      avatar: 'EV',
      phone: '+62 8112233445',
      segment: 'VIP Active',
      favService: 'Balayage Color Treatment'
    },
    {
      id: 'sari-handayani',
      name: 'Sari Handayani',
      avatar: 'SH',
      phone: '+62 81298765432',
      segment: 'Loyal',
      favService: 'Creambath & Blow Dry'
    },
    {
      id: 'budi-santoso',
      name: 'Budi Santoso',
      avatar: 'BS',
      phone: '+62 81345678901',
      segment: 'Reguler',
      favService: 'Signature Grooming & Cut'
    },
    {
      id: 'melati-putri',
      name: 'Melati Putri',
      avatar: 'MP',
      phone: '+62 81711223344',
      segment: 'VIP Active',
      favService: 'Color Correction & Spa'
    },
    {
      id: 'marcus-sterling',
      name: 'Marcus Sterling',
      avatar: 'MS',
      phone: '+62 81899001122',
      segment: 'Loyal',
      favService: 'Executive Haircut'
    },
    {
      id: 'dewi-anggraini',
      name: 'Dewi Anggraini',
      avatar: 'DA',
      phone: '+62 81233445566',
      segment: 'Reguler',
      favService: 'Keratin Smooth Treatment'
    }
  ];

  // Mock Salon Transaction Database
  const salonTransactions = [
    { id: 'TRX-001', clientId: 'eleanor-vance', date: '2026-08-26T10:00:00', amount: 650000, service: 'Balayage Color Treatment' },
    { id: 'TRX-002', clientId: 'sari-handayani', date: '2026-08-26T13:30:00', amount: 180000, service: 'Creambath & Blow Dry' },
    { id: 'TRX-003', clientId: 'budi-santoso', date: '2026-08-26T15:00:00', amount: 75000, service: 'Signature Grooming & Cut' },
    { id: 'TRX-004', clientId: 'melati-putri', date: '2026-08-25T11:00:00', amount: 850000, service: 'Color Correction & Spa' },
    { id: 'TRX-005', clientId: 'marcus-sterling', date: '2026-08-25T14:30:00', amount: 120000, service: 'Executive Haircut' },
    { id: 'TRX-006', clientId: 'dewi-anggraini', date: '2026-08-24T16:00:00', amount: 750000, service: 'Keratin Smooth Treatment' },
    { id: 'TRX-007', clientId: 'eleanor-vance', date: '2026-08-12T11:30:00', amount: 350000, service: 'Olaplex Hair Repair Spa' },
    { id: 'TRX-008', clientId: 'sari-handayani', date: '2026-08-08T14:00:00', amount: 180000, service: 'Creambath & Blow Dry' },
    { id: 'TRX-009', clientId: 'eleanor-vance', date: '2026-08-02T10:00:00', amount: 650000, service: 'Balayage Color Treatment' },
    { id: 'TRX-010', clientId: 'melati-putri', date: '2026-08-04T15:30:00', amount: 450000, service: 'Hair Toner & Styling' },
    { id: 'TRX-011', clientId: 'budi-santoso', date: '2026-08-10T16:00:00', amount: 75000, service: 'Signature Grooming & Cut' },
    { id: 'TRX-012', clientId: 'marcus-sterling', date: '2026-08-11T13:00:00', amount: 120000, service: 'Executive Haircut' },
    { id: 'TRX-013', clientId: 'sari-handayani', date: '2026-07-28T14:00:00', amount: 180000, service: 'Creambath & Blow Dry' },
    { id: 'TRX-014', clientId: 'eleanor-vance', date: '2026-07-15T10:30:00', amount: 650000, service: 'Balayage Color Treatment' }
  ];

  // DOM Elements
  const selectPeriodPreset = document.getElementById('filter-periode-cepat');
  const inputStartDate = document.getElementById('filter-dari-tanggal');
  const inputEndDate = document.getElementById('filter-sampai-tanggal');
  const btnApplyFilter = document.getElementById('btn-apply-filter');
  const btnResetFilter = document.getElementById('btn-reset-filter');

  const kpiTotalClients = document.getElementById('kpi-total-clients');
  const kpiTotalVisits = document.getElementById('kpi-total-visits');
  const kpiTotalRevenue = document.getElementById('kpi-total-revenue');
  const kpiAvgSpend = document.getElementById('kpi-avg-spend');

  const searchInput = document.getElementById('active-client-search');
  const sortSelect = document.getElementById('sort-active-clients');
  const tableWrapper = document.getElementById('active-table-wrapper');
  const tableTbody = document.getElementById('active-clients-tbody');
  const emptyStateContainer = document.getElementById('empty-state-container');

  // =========================================================================
  // 2. DATE PRESET HELPER FUNCTIONS
  // =========================================================================
  function formatYMD(d) {
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }

  function setDatePreset(preset) {
    // Reference application year is 2026, month is August (07 in 0-indexed)
    const baseDate = new Date(2026, 7, 26); // 26 August 2026

    if (preset === 'bulan-ini') {
      const start = new Date(2026, 7, 1);
      const end = new Date(2026, 7, 31);
      inputStartDate.value = formatYMD(start);
      inputEndDate.value = formatYMD(end);
    } else if (preset === 'bulan-lalu') {
      const start = new Date(2026, 6, 1);
      const end = new Date(2026, 6, 31);
      inputStartDate.value = formatYMD(start);
      inputEndDate.value = formatYMD(end);
    } else if (preset === '30-hari') {
      const start = new Date(2026, 6, 27);
      const end = new Date(2026, 7, 26);
      inputStartDate.value = formatYMD(start);
      inputEndDate.value = formatYMD(end);
    } else if (preset === 'tahun-ini') {
      const start = new Date(2026, 0, 1);
      const end = new Date(2026, 11, 31);
      inputStartDate.value = formatYMD(start);
      inputEndDate.value = formatYMD(end);
    }
  }

  if (selectPeriodPreset) {
    selectPeriodPreset.addEventListener('change', () => {
      if (selectPeriodPreset.value !== 'kustom') {
        setDatePreset(selectPeriodPreset.value);
        applyFilter();
      }
    });
  }

  // =========================================================================
  // 3. FILTERING & AGGREGATION ENGINE
  // =========================================================================
  function formatIDR(val) {
    return 'Rp' + Math.round(val).toLocaleString('id-ID');
  }

  function formatDisplayDate(dateStr) {
    const d = new Date(dateStr);
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const day = d.getDate();
    const month = months[d.getMonth()];
    const year = d.getFullYear();
    const hours = String(d.getHours()).padStart(2, '0');
    const minutes = String(d.getMinutes()).padStart(2, '0');
    return `${day} ${month} ${year}, ${hours}:${minutes}`;
  }

  function getActiveClientsData() {
    const startStr = inputStartDate.value;
    const endStr = inputEndDate.value;
    const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');
    const sortBy = (sortSelect ? sortSelect.value : 'visits-desc');

    if (!startStr || !endStr) return [];

    const startDate = new Date(`${startStr}T00:00:00`);
    const endDate = new Date(`${endStr}T23:59:59`);

    // Filter transactions within range
    const periodTransactions = salonTransactions.filter(trx => {
      const trxDate = new Date(trx.date);
      return trxDate >= startDate && trxDate <= endDate;
    });

    // Group by Client ID
    const clientAggMap = {};

    periodTransactions.forEach(trx => {
      if (!clientAggMap[trx.clientId]) {
        clientAggMap[trx.clientId] = {
          clientId: trx.clientId,
          visits: 0,
          totalSpend: 0,
          lastVisit: trx.date,
          services: []
        };
      }

      clientAggMap[trx.clientId].visits += 1;
      clientAggMap[trx.clientId].totalSpend += trx.amount;
      clientAggMap[trx.clientId].services.push(trx.service);

      // Keep latest visit
      if (new Date(trx.date) > new Date(clientAggMap[trx.clientId].lastVisit)) {
        clientAggMap[trx.clientId].lastVisit = trx.date;
      }
    });

    // Map into client objects
    let result = Object.keys(clientAggMap).map(cId => {
      const clientProfile = allClients.find(c => c.id === cId) || {
        id: cId,
        name: 'Pelanggan',
        avatar: 'PL',
        phone: '-',
        segment: 'Reguler',
        favService: '-'
      };

      const agg = clientAggMap[cId];

      return {
        ...clientProfile,
        visits: agg.visits,
        totalSpend: agg.totalSpend,
        lastVisit: agg.lastVisit,
        periodServices: agg.services
      };
    });

    // Filter by Search Query
    if (query) {
      result = result.filter(c => {
        return c.name.toLowerCase().includes(query) || c.phone.toLowerCase().includes(query);
      });
    }

    // Sort Result
    if (sortBy === 'visits-desc') {
      result.sort((a, b) => b.visits - a.visits || b.totalSpend - a.totalSpend);
    } else if (sortBy === 'spend-desc') {
      result.sort((a, b) => b.totalSpend - a.totalSpend || b.visits - a.visits);
    } else if (sortBy === 'recent-desc') {
      result.sort((a, b) => new Date(b.lastVisit) - new Date(a.lastVisit));
    } else if (sortBy === 'name-asc') {
      result.sort((a, b) => a.name.localeCompare(b.name));
    }

    return result;
  }

  // =========================================================================
  // 4. RENDER VIEW & KPI CARDS
  // =========================================================================
  function renderView() {
    const activeClientsList = getActiveClientsData();

    // 1. Calculate KPI Metrics
    const totalActiveCount = activeClientsList.length;
    let totalVisitsSum = 0;
    let totalRevenueSum = 0;

    activeClientsList.forEach(c => {
      totalVisitsSum += c.visits;
      totalRevenueSum += c.totalSpend;
    });

    const avgSpendVal = totalVisitsSum > 0 ? (totalRevenueSum / totalVisitsSum) : 0;

    if (kpiTotalClients) kpiTotalClients.textContent = totalActiveCount;
    if (kpiTotalVisits) kpiTotalVisits.textContent = totalVisitsSum;
    if (kpiTotalRevenue) kpiTotalRevenue.textContent = formatIDR(totalRevenueSum);
    if (kpiAvgSpend) kpiAvgSpend.textContent = formatIDR(avgSpendVal);

    // 2. Render Table or Empty State
    if (totalActiveCount === 0) {
      if (tableWrapper) tableWrapper.style.display = 'none';
      if (emptyStateContainer) emptyStateContainer.style.display = 'block';
    } else {
      if (tableWrapper) tableWrapper.style.display = 'block';
      if (emptyStateContainer) emptyStateContainer.style.display = 'none';

      if (tableTbody) {
        tableTbody.innerHTML = activeClientsList.map((client, index) => {
          let badgeClass = 'badge-reguler';
          if (client.segment.includes('VIP')) badgeClass = 'badge-vip';
          else if (client.segment.includes('Loyal')) badgeClass = 'badge-loyal';

          return `
            <tr>
              <td style="font-weight: 700; color: var(--text-muted);">${index + 1}</td>
              <td>
                <div class="client-meta-cell">
                  <div>
                    <a href="profil-pelanggan.html?id=${client.id}" class="client-name-link">${client.name}</a>
                    <div><span class="client-segment-badge ${badgeClass}">${client.segment}</span></div>
                  </div>
                </div>
              </td>
              <td>
                <span style="font-weight: 600; color: var(--text-primary); font-family: monospace;">${client.phone}</span>
              </td>
              <td>
                <span class="visit-badge">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="color: #B45309;">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                  </svg>
                  ${client.visits} Kunjungan
                </span>
              </td>
              <td>
                <span class="spend-amount-highlight">${formatIDR(client.totalSpend)}</span>
              </td>
              <td>
                <span style="font-size: 0.83rem; color: var(--text-secondary);">${formatDisplayDate(client.lastVisit)}</span>
              </td>
              <td>
                <span style="font-size: 0.83rem; font-weight: 600; color: var(--text-primary);">${client.favService}</span>
              </td>
              <td style="text-align: right;">
                <div class="action-buttons-cell">
                  <a href="profil-pelanggan.html?id=${client.id}" class="btn-table-action btn-view-profile">Profil</a>
                  <a href="add-appointment.html?clientId=${client.id}" class="btn-table-action btn-view-profile" style="background-color: #FEF3C7; color: #92400E; border-color: #FDE68A;">+ Janji Temu</a>
                </div>
              </td>
            </tr>
          `;
        }).join('');
      }
    }
  }

  function applyFilter() {
    renderView();
  }

  // Event Listeners
  if (btnApplyFilter) {
    btnApplyFilter.addEventListener('click', applyFilter);
  }

  if (btnResetFilter) {
    btnResetFilter.addEventListener('click', () => {
      if (selectPeriodPreset) selectPeriodPreset.value = 'bulan-ini';
      setDatePreset('bulan-ini');
      if (searchInput) searchInput.value = '';
      if (sortSelect) sortSelect.value = 'visits-desc';
      applyFilter();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      renderView();
    });
  }

  if (sortSelect) {
    sortSelect.addEventListener('change', () => {
      renderView();
    });
  }

  // Initial Load
  setDatePreset('bulan-ini');
  renderView();

});
