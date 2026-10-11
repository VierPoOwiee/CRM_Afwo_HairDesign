/**
 * AFWO Hair Design - Transaction History Controller (Reference 1 Match)
 * Groups transactions per day in a structured detail table, handles status tabs,
 * dynamic search, date range filters, real-time KPI updates, and receipt modal previews.
 */

document.addEventListener('DOMContentLoaded', () => {

  // Mock Comprehensive Transaction Repository
  const transactionsData = [
    // Today: 26 August 2026
    {
      id: 'TRX-121091',
      date: '2026-08-26',
      time: '10:00',
      client: { id: 'eleanor-vance', name: 'Eleanor Vance', phone: '+62 8112233445', avatar: 'EV' },
      service: 'Balayage Color Treatment',
      worker: 'Agus Pratama',
      amount: 650000,
      paymentMethod: 'QRIS',
      status: 'paid', // paid, pending, cancelled
      items: [
        { name: 'Balayage Color Treatment', price: 650000, qty: 1 }
      ]
    },
    {
      id: 'TRX-121090',
      date: '2026-08-26',
      time: '13:30',
      client: { id: 'sari-handayani', name: 'Sari Handayani', phone: '+62 81298765432', avatar: 'SH' },
      service: 'Creambath & Blow Dry',
      worker: 'Rina',
      amount: 180000,
      paymentMethod: 'Cash',
      status: 'paid',
      items: [
        { name: 'Creambath & Blow Dry', price: 180000, qty: 1 }
      ]
    },
    {
      id: 'TRX-121058',
      date: '2026-08-26',
      time: '15:00',
      client: { id: 'budi-santoso', name: 'Budi Santoso', phone: '+62 81345678901', avatar: 'BS' },
      service: 'Signature Grooming & Cut',
      worker: 'Dimas',
      amount: 75000,
      paymentMethod: 'QRIS',
      status: 'pending',
      items: [
        { name: 'Signature Grooming & Cut', price: 75000, qty: 1 }
      ]
    },

    // Yesterday: 25 August 2026
    {
      id: 'TRX-120999',
      date: '2026-08-25',
      time: '11:00',
      client: { id: 'melati-putri', name: 'Melati Putri', phone: '+62 81711223344', avatar: 'MP' },
      service: 'Color Correction & Spa',
      worker: 'Ada Wong',
      amount: 850000,
      paymentMethod: 'Transfer BCA',
      status: 'paid',
      items: [
        { name: 'Color Correction & Spa', price: 850000, qty: 1 }
      ]
    },
    {
      id: 'TRX-121049',
      date: '2026-08-25',
      time: '14:30',
      client: { id: 'marcus-sterling', name: 'Marcus Sterling', phone: '+62 81899001122', avatar: 'MS' },
      service: 'Executive Haircut & Styling',
      worker: 'Dimas',
      amount: 120000,
      paymentMethod: 'QRIS',
      status: 'paid',
      items: [
        { name: 'Executive Haircut & Styling', price: 120000, qty: 1 }
      ]
    },

    // 24 August 2026
    {
      id: 'TRX-121094',
      date: '2026-08-24',
      time: '16:00',
      client: { id: 'dewi-anggraini', name: 'Dewi Anggraini', phone: '+62 81233445566', avatar: 'DA' },
      service: 'Keratin Smooth Treatment',
      worker: 'Budi',
      amount: 750000,
      paymentMethod: 'QRIS',
      status: 'paid',
      items: [
        { name: 'Keratin Smooth Treatment', price: 750000, qty: 1 }
      ]
    },
    {
      id: 'TRX-121001',
      date: '2026-08-24',
      time: '17:30',
      client: { id: 'sari-handayani', name: 'Sari Handayani', phone: '+62 81298765432', avatar: 'SH' },
      service: 'Hair Tonic Treatment',
      worker: 'Rina',
      amount: 95000,
      paymentMethod: 'Cash',
      status: 'cancelled',
      items: [
        { name: 'Hair Tonic Treatment', price: 95000, qty: 1 }
      ]
    }
  ];

  // DOM Elements
  const searchInput = document.getElementById('transaction-search');
  const filterDateFrom = document.getElementById('filter-date-from');
  const filterDateTo = document.getElementById('filter-date-to');
  const btnResetDates = document.getElementById('btn-reset-dates');
  const filterTabs = document.querySelectorAll('.trans-filter-tab');
  const listContainer = document.getElementById('transaction-history-list');
  const emptyState = document.getElementById('history-empty-state');

  const kpiTotalOrders = document.getElementById('kpi-total-orders');
  const kpiCompletedOrders = document.getElementById('kpi-completed-orders');
  const kpiPendingOrders = document.getElementById('kpi-pending-orders');
  const kpiTotalIncome = document.getElementById('kpi-total-income');

  // Modal Elements
  const modal = document.getElementById('trans-detail-modal');
  const modalBody = document.getElementById('modal-receipt-body');
  const btnCloseModal = document.getElementById('btn-close-modal');
  const btnModalCloseAction = document.getElementById('btn-modal-close-action');
  const btnPrintReceipt = document.getElementById('btn-print-receipt');

  let activeStatus = 'all';

  function formatIDR(val) {
    return 'Rp' + Math.round(val).toLocaleString('id-ID');
  }

  function getDayHeading(dateStr) {
    const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const d = new Date(dateStr + 'T00:00:00');
    const dayName = days[d.getDay()];
    const dayNum = d.getDate();
    const monthName = months[d.getMonth()];
    const year = d.getFullYear();

    if (dateStr === '2026-08-26') return `Hari Ini — ${dayName}, ${dayNum} ${monthName} ${year}`;
    if (dateStr === '2026-08-25') return `Kemarin — ${dayName}, ${dayNum} ${monthName} ${year}`;
    return `${dayName}, ${dayNum} ${monthName} ${year}`;
  }

  function renderTransactions() {
    const query = (searchInput ? searchInput.value.toLowerCase().trim() : '');
    const dateFrom = filterDateFrom ? filterDateFrom.value : '';
    const dateTo = filterDateTo ? filterDateTo.value : '';

    // Filter transactions
    const filtered = transactionsData.filter(trx => {
      // Status Filter
      if (activeStatus !== 'all' && trx.status !== activeStatus) return false;

      // Search Query
      if (query) {
        const matchId = trx.id.toLowerCase().includes(query);
        const matchClient = trx.client.name.toLowerCase().includes(query) || trx.client.phone.includes(query);
        const matchService = trx.service.toLowerCase().includes(query);
        const matchWorker = trx.worker.toLowerCase().includes(query);
        if (!matchId && !matchClient && !matchService && !matchWorker) return false;
      }

      // Date Range
      if (dateFrom && trx.date < dateFrom) return false;
      if (dateTo && trx.date > dateTo) return false;

      return true;
    });

    // Update KPI Counters
    let totalIncome = 0;
    let completedCount = 0;
    let pendingCount = 0;

    transactionsData.forEach(t => {
      if (t.status === 'paid') {
        totalIncome += t.amount;
        completedCount++;
      } else if (t.status === 'pending') {
        pendingCount++;
      }
    });

    if (kpiTotalOrders) kpiTotalOrders.textContent = transactionsData.length;
    if (kpiCompletedOrders) kpiCompletedOrders.textContent = completedCount;
    if (kpiPendingOrders) kpiPendingOrders.textContent = pendingCount;
    if (kpiTotalIncome) kpiTotalIncome.textContent = formatIDR(totalIncome);

    // If empty
    if (filtered.length === 0) {
      if (listContainer) listContainer.innerHTML = '';
      if (emptyState) emptyState.style.display = 'block';
      return;
    }

    if (emptyState) emptyState.style.display = 'none';

    // Group by Date
    const grouped = {};
    filtered.forEach(trx => {
      if (!grouped[trx.date]) grouped[trx.date] = [];
      grouped[trx.date].push(trx);
    });

    // Sort dates descending
    const sortedDates = Object.keys(grouped).sort().reverse();

    let html = '';

    sortedDates.forEach(dateStr => {
      const dayTrxs = grouped[dateStr];
      const dayTotal = dayTrxs.reduce((sum, t) => sum + (t.status === 'paid' ? t.amount : 0), 0);

      let rowsHtml = dayTrxs.map(trx => {
        let statusBadge = `<span class="status-pill status-paid">Selesai (Paid)</span>`;
        if (trx.status === 'pending') statusBadge = `<span class="status-pill status-pending">Menunggu (Pending)</span>`;
        else if (trx.status === 'cancelled') statusBadge = `<span class="status-pill status-cancelled">Dibatalkan</span>`;

        let methodBadge = `<span class="payment-method-badge badge-qris">QRIS</span>`;
        if (trx.paymentMethod.toLowerCase().includes('cash')) methodBadge = `<span class="payment-method-badge badge-cash">Tunai</span>`;
        else if (trx.paymentMethod.toLowerCase().includes('transfer')) methodBadge = `<span class="payment-method-badge badge-transfer">Transfer</span>`;

        return `
          <tr data-trx-id="${trx.id}">
            <td style="width: 110px;">
              <a href="#" class="trans-order-id-link js-open-receipt" data-trx-id="${trx.id}">${trx.id}</a>
              <div style="font-size: 0.72rem; color: var(--text-muted);">${trx.time} WIB</div>
            </td>
            <td>
              <div class="trans-client-meta">
                <div>
                  <div class="trans-client-name">${trx.client.name}</div>
                  <div class="trans-client-phone">${trx.client.phone}</div>
                </div>
              </div>
            </td>
            <td>
              <div class="trans-service-title">${trx.service}</div>
              <div class="trans-worker-tag">Stylist: <strong>${trx.worker}</strong></div>
            </td>
            <td>
              <div class="trans-amount-cell">${formatIDR(trx.amount)}</div>
            </td>
            <td>
              ${methodBadge}
            </td>
            <td>
              ${statusBadge}
            </td>
            <td style="text-align: right;">
              <button type="button" class="btn-detail-receipt js-open-receipt" data-trx-id="${trx.id}">Detail Struk</button>
            </td>
          </tr>
        `;
      }).join('');

      html += `
        <div class="daily-group-card">
          <div class="daily-group-header">
            <div class="daily-group-title">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#B45309" stroke-width="2.5">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <span>${getDayHeading(dateStr)}</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
              <span class="daily-group-badge">${dayTrxs.length} Transaksi</span>
              <span style="font-size: 0.82rem; font-weight: 800; color: #B45309;">${formatIDR(dayTotal)}</span>
            </div>
          </div>

          <div class="table-responsive-wrapper">
            <table class="daily-trans-table">
              <thead>
                <tr>
                  <th>ID / Waktu</th>
                  <th>Pelanggan</th>
                  <th>Layanan &amp; Stylist</th>
                  <th>Total Biaya</th>
                  <th>Metode Bayar</th>
                  <th>Status</th>
                  <th style="text-align: right;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                ${rowsHtml}
              </tbody>
            </table>
          </div>
        </div>
      `;
    });

    if (listContainer) listContainer.innerHTML = html;

    // Attach receipt modal click handlers
    document.querySelectorAll('.js-open-receipt').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const trxId = btn.getAttribute('data-trx-id');
        openReceiptModal(trxId);
      });
    });
  }

  function openReceiptModal(id) {
    const trx = transactionsData.find(t => t.id === id);
    if (!trx || !modalBody || !modal) return;

    modalBody.innerHTML = `
      <div style="text-align: center; border-bottom: 1px dashed var(--border-color); padding-bottom: 12px; margin-bottom: 12px;">
        <h2 style="font-size: 1.3rem; font-weight: 900; margin: 0; color: var(--text-primary);">Afwo. Hair Design</h2>
        <p style="font-size: 0.78rem; color: var(--text-muted); margin: 2px 0 0 0;">Official Salon Payment Receipt</p>
      </div>

      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 8px;">
        <span style="color: var(--text-muted);">No. Transaksi:</span>
        <strong style="font-family: monospace;">${trx.id}</strong>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 8px;">
        <span style="color: var(--text-muted);">Waktu:</span>
        <strong>${trx.date} - ${trx.time} WIB</strong>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 8px;">
        <span style="color: var(--text-muted);">Pelanggan:</span>
        <strong>${trx.client.name} (${trx.client.phone})</strong>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; margin-bottom: 12px;">
        <span style="color: var(--text-muted);">Stylist:</span>
        <strong>${trx.worker}</strong>
      </div>

      <div style="border-top: 1px dashed var(--border-color); border-bottom: 1px dashed var(--border-color); padding: 10px 0; margin-bottom: 12px;">
        <div style="display: flex; justify-content: space-between; font-weight: 700; font-size: 0.88rem; margin-bottom: 4px;">
          <span>${trx.service}</span>
          <span>${formatIDR(trx.amount)}</span>
        </div>
      </div>

      <div style="display: flex; justify-content: space-between; font-size: 1.05rem; font-weight: 900; color: #B45309; margin-bottom: 8px;">
        <span>TOTAL:</span>
        <span>${formatIDR(trx.amount)}</span>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: var(--text-muted);">
        <span>Metode Pembayaran:</span>
        <span style="font-weight: 700; color: var(--text-primary);">${trx.paymentMethod}</span>
      </div>
    `;

    modal.classList.add('show');
  }

  // Event Listeners
  if (searchInput) searchInput.addEventListener('input', renderTransactions);
  if (filterDateFrom) filterDateFrom.addEventListener('change', renderTransactions);
  if (filterDateTo) filterDateTo.addEventListener('change', renderTransactions);

  if (btnResetDates) {
    btnResetDates.addEventListener('click', () => {
      if (filterDateFrom) filterDateFrom.value = '';
      if (filterDateTo) filterDateTo.value = '';
      renderTransactions();
    });
  }

  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      activeStatus = tab.getAttribute('data-status') || 'all';
      renderTransactions();
    });
  });

  if (btnCloseModal) {
    btnCloseModal.addEventListener('click', () => {
      if (modal) modal.classList.remove('show');
    });
  }

  if (btnModalCloseAction) {
    btnModalCloseAction.addEventListener('click', () => {
      if (modal) modal.classList.remove('show');
    });
  }

  if (btnPrintReceipt) {
    btnPrintReceipt.addEventListener('click', () => {
      window.print();
    });
  }

  // Initial Render
  renderTransactions();

});
