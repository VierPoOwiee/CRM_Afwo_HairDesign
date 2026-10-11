/**
 * AFWO Hair Design - Appointment Calendar Script
 * Full Google Calendar-inspired interactive calendar with Month, Week, Day, and Year views,
 * client & worker checkbox filtering, quick jump mini calendar, and appointment detail popovers.
 */

document.addEventListener('DOMContentLoaded', () => {
  
  // =========================================================================
  // 1. DATA REPOSITORY (MOCK DATABASE READY FOR API INTEGRATION)
  // =========================================================================
  const clientsData = [
    { id: 'eleanor-vance', name: 'Eleanor Vance', color: '#F59E0B', bg: '#FEF3C7', avatar: 'EV', phone: '+62 8112233445' },
    { id: 'sari-handayani', name: 'Sari Handayani', color: '#10B981', bg: '#D1FAE5', avatar: 'SH', phone: '+62 81298765432' },
    { id: 'budi-santoso', name: 'Budi Santoso', color: '#3B82F6', bg: '#DBEAFE', avatar: 'BS', phone: '+62 81345678901' },
    { id: 'melati-putri', name: 'Melati Putri', color: '#8B5CF6', bg: '#EDE9FE', avatar: 'MP', phone: '+62 81711223344' },
    { id: 'marcus-sterling', name: 'Marcus Sterling', color: '#EC4899', bg: '#FCE7F3', avatar: 'MS', phone: '+62 81899001122' },
    { id: 'dewi-anggraini', name: 'Dewi Anggraini', color: '#E5A93C', bg: '#FFFBEB', avatar: 'DA', phone: '+62 81233445566' }
  ];

  const workersData = [
    { id: 'agus-pratama', name: 'Agus Pratama', specialty: 'Senior Stylist', color: '#E5A93C', bg: '#FFFBEB' },
    { id: 'ada-wong', name: 'Ada Wong', specialty: 'Lead Hair Stylist', color: '#EC4899', bg: '#FCE7F3' },
    { id: 'budi', name: 'Budi', specialty: 'Color Specialist', color: '#3B82F6', bg: '#DBEAFE' },
    { id: 'rina', name: 'Rina', specialty: 'Hair Spa & Treatment', color: '#10B981', bg: '#D1FAE5' },
    { id: 'dimas', name: 'Dimas', specialty: 'Barber & Grooming', color: '#8B5CF6', bg: '#EDE9FE' }
  ];

  let appointmentsData = [
    {
      id: 'apt-1',
      clientId: 'eleanor-vance',
      service: 'Balayage Color Treatment',
      workerId: 'agus-pratama',
      date: '2026-08-26',
      time: '10:00',
      duration: 120, // minutes
      status: 'confirmed',
      notes: 'Preferensi: Balayage nuansa ash blonde, formula bebas amonia.'
    },
    {
      id: 'apt-2',
      clientId: 'sari-handayani',
      service: 'Creambath & Blow Dry',
      workerId: 'rina',
      date: '2026-08-26',
      time: '13:30',
      duration: 60,
      status: 'confirmed',
      notes: 'Pijatan sedang, hair tonic ginseng.'
    },
    {
      id: 'apt-3',
      clientId: 'budi-santoso',
      service: 'Signature Grooming & Cut',
      workerId: 'dimas',
      date: '2026-08-27',
      time: '11:00',
      duration: 45,
      status: 'confirmed',
      notes: 'Taper fade samping, styling pomade matte.'
    },
    {
      id: 'apt-4',
      clientId: 'melati-putri',
      service: 'Color Correction & Spa',
      workerId: 'budi',
      date: '2026-08-28',
      time: '14:00',
      duration: 150,
      status: 'confirmed',
      notes: 'Neutralisasi undertone kemerahan, deep conditioning mask.'
    },
    {
      id: 'apt-5',
      clientId: 'marcus-sterling',
      service: 'Executive Haircut & Styling',
      workerId: 'ada-wong',
      date: '2026-08-28',
      time: '16:30',
      duration: 60,
      status: 'pending',
      notes: 'Konfirmasi ulang 2 jam sebelum jadwal.'
    },
    {
      id: 'apt-6',
      clientId: 'dewi-anggraini',
      service: 'Keratin Smooth Treatment',
      workerId: 'agus-pratama',
      date: '2026-08-29',
      time: '09:30',
      duration: 120,
      status: 'confirmed',
      notes: 'Perawatan rambut mengembang sehabis smoothing tahun lalu.'
    },
    {
      id: 'apt-7',
      clientId: 'eleanor-vance',
      service: 'Toner Touch-up & Blow',
      workerId: 'ada-wong',
      date: '2026-09-02',
      time: '15:00',
      duration: 60,
      status: 'confirmed',
      notes: 'Sesi follow-up balayage 1 minggu kemudian.'
    },
    {
      id: 'apt-8',
      clientId: 'sari-handayani',
      service: 'Olaplex Hair Repair Spa',
      workerId: 'rina',
      date: '2026-09-05',
      time: '11:00',
      duration: 90,
      status: 'confirmed',
      notes: 'Treatment Olaplex No. 1 & 2 lengkap.'
    },
    {
      id: 'apt-9',
      clientId: 'budi-santoso',
      service: 'Head Massage & Hair Wash',
      workerId: 'rina',
      date: '2026-08-26',
      time: '16:00',
      duration: 45,
      status: 'confirmed',
      notes: 'Relaksasi sore.'
    }
  ];

  // =========================================================================
  // 2. STATE MANAGEMENT
  // =========================================================================
  // Set active date: August 26, 2026
  let currentDate = new Date(2026, 7, 26);
  let currentView = 'month'; // 'month' | 'week' | 'day' | 'year'
  let activeClients = new Set(clientsData.map(c => c.id));
  let activeWorkers = new Set(workersData.map(w => w.id));
  let searchQuery = '';

  const monthNames = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];

  const dayNamesShort = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
  const dayNamesFull = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

  // DOM Elements
  const periodHeading = document.getElementById('cal-period-heading');
  const todayBtn = document.getElementById('btn-cal-today');
  const prevBtn = document.getElementById('btn-cal-prev');
  const nextBtn = document.getElementById('btn-cal-next');
  const viewToggleBtn = document.getElementById('btn-view-toggle');
  const viewMenu = document.getElementById('cal-view-menu');
  const currentViewLabel = document.getElementById('current-view-label');
  const quickSearchInput = document.getElementById('cal-quick-search');

  const viewPanels = {
    month: document.getElementById('view-month'),
    week: document.getElementById('view-week'),
    day: document.getElementById('view-day'),
    year: document.getElementById('view-year')
  };

  const miniCalGrid = document.getElementById('mini-cal-grid');
  const miniCalTitle = document.getElementById('mini-cal-title');
  const miniPrevBtn = document.getElementById('btn-mini-prev');
  const miniNextBtn = document.getElementById('btn-mini-next');

  const clientCheckboxList = document.getElementById('client-checkbox-list');
  const workerCheckboxList = document.getElementById('worker-checkbox-list');
  const btnToggleAllClients = document.getElementById('btn-toggle-all-clients');
  const btnToggleAllWorkers = document.getElementById('btn-toggle-all-workers');

  // Google Calendar Daily Schedule Modal Elements
  const dayScheduleModal = document.getElementById('day-schedule-modal');
  const dayScheduleDow = document.getElementById('day-schedule-dow');
  const dayScheduleDom = document.getElementById('day-schedule-dom');
  const dayScheduleTitle = document.getElementById('day-schedule-title');
  const dayScheduleCount = document.getElementById('day-schedule-count');
  const btnAddForDay = document.getElementById('btn-add-for-day');
  const btnCloseDaySchedule = document.getElementById('btn-close-day-schedule');
  const gcalTimelineContainer = document.getElementById('gcal-timeline-container');

  // Popover elements
  const popoverModal = document.getElementById('appointment-detail-modal');
  const popCloseBtn = document.getElementById('btn-close-popover');
  const popAvatar = document.getElementById('pop-avatar');
  const popClientName = document.getElementById('pop-client-name');
  const popServiceName = document.getElementById('pop-service-name');
  const popTimeRange = document.getElementById('pop-time-range');
  const popStylistName = document.getElementById('pop-stylist-name');
  const popPhone = document.getElementById('pop-phone');
  const popPriceVal = document.getElementById('pop-price-val');
  const popNotes = document.getElementById('pop-notes');
  const popCompleteBtn = document.getElementById('btn-pop-complete');
  const popEditLink = document.getElementById('btn-pop-edit');
  const popDeleteBtn = document.getElementById('btn-pop-delete');
  let selectedAppointmentId = null;
  let activeScheduleDate = null;

  // Toast Helper
  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');

  function showToast(msg) {
    if (!toastBox) return;
    if (toastMessage) toastMessage.textContent = msg;
    toastBox.classList.add('show');
    setTimeout(() => toastBox.classList.remove('show'), 2500);
  }

  // =========================================================================
  // 3. FILTERING & DATA HELPERS
  // =========================================================================
  function getFilteredAppointments() {
    const noClients = (activeClients.size === 0);
    const noWorkers = (activeWorkers.size === 0);

    if (noClients && noWorkers) return [];

    return appointmentsData.filter(apt => {
      const clientMatch = noClients ? true : activeClients.has(apt.clientId);
      const workerMatch = noWorkers ? true : activeWorkers.has(apt.workerId);

      if (!clientMatch || !workerMatch) return false;

      if (searchQuery) {
        const client = clientsData.find(c => c.id === apt.clientId);
        const worker = workersData.find(w => w.id === apt.workerId);
        const clientName = client ? client.name.toLowerCase() : '';
        const serviceName = apt.service.toLowerCase();
        const workerName = worker ? worker.name.toLowerCase() : '';
        const q = searchQuery.toLowerCase();
        if (!clientName.includes(q) && !serviceName.includes(q) && !workerName.includes(q)) {
          return false;
        }
      }
      return true;
    });
  }

  function getClient(id) {
    return clientsData.find(c => c.id === id) || { name: 'Klien', color: '#6B7280', bg: '#F3F4F6', avatar: 'KL', phone: '' };
  }

  function getWorker(id) {
    return workersData.find(w => w.id === id) || { name: 'Stylist', color: '#E5A93C', bg: '#FFFBEB' };
  }

  function formatDateStr(year, month, day) {
    const m = String(month + 1).padStart(2, '0');
    const d = String(day).padStart(2, '0');
    return `${year}-${m}-${d}`;
  }

  function formatIDR(val) {
    if (!val) return 'Rp0';
    return 'Rp ' + Math.round(val).toLocaleString('id-ID');
  }

  // =========================================================================
  // 4. VIEW RENDERERS
  // =========================================================================
  function updatePeriodHeading() {
    const y = currentDate.getFullYear();
    const m = currentDate.getMonth();

    if (currentView === 'year') {
      if (periodHeading) periodHeading.textContent = `Tahun ${y}`;
    } else if (currentView === 'month') {
      if (periodHeading) periodHeading.textContent = `${monthNames[m]} ${y}`;
    } else if (currentView === 'week') {
      const startOfWeek = new Date(currentDate);
      const day = startOfWeek.getDay();
      const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1);
      startOfWeek.setDate(diff);

      const endOfWeek = new Date(startOfWeek);
      endOfWeek.setDate(startOfWeek.getDate() + 6);

      const startMonth = monthNames[startOfWeek.getMonth()].substring(0, 3);
      const endMonth = monthNames[endOfWeek.getMonth()].substring(0, 3);

      if (periodHeading) {
        if (startOfWeek.getMonth() === endOfWeek.getMonth()) {
          periodHeading.textContent = `${startOfWeek.getDate()} - ${endOfWeek.getDate()} ${monthNames[m]} ${y}`;
        } else {
          periodHeading.textContent = `${startOfWeek.getDate()} ${startMonth} - ${endOfWeek.getDate()} ${endMonth} ${y}`;
        }
      }
    } else if (currentView === 'day') {
      const dName = dayNamesFull[currentDate.getDay()];
      if (periodHeading) periodHeading.textContent = `${dName}, ${currentDate.getDate()} ${monthNames[m]} ${y}`;
    }
  }

  /**
   * 4A. MONTH VIEW RENDERER (User Directive: Click date cell opens Day Schedule)
   */
  function renderMonthView() {
    const grid = document.getElementById('month-cells-grid');
    if (!grid) return;

    grid.innerHTML = '';
    const filteredApts = getFilteredAppointments();

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const firstDayIndex = new Date(year, month, 1).getDay();
    const adjustedFirstDay = (firstDayIndex === 0 ? 6 : firstDayIndex - 1);
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    const todayStr = '2026-08-26';

    // 1. Previous Month Overflow Days
    for (let i = adjustedFirstDay - 1; i >= 0; i--) {
      const dayNum = daysInPrevMonth - i;
      const prevMonthIdx = month === 0 ? 11 : month - 1;
      const prevYear = month === 0 ? year - 1 : year;
      const dateStr = formatDateStr(prevYear, prevMonthIdx, dayNum);

      const cell = document.createElement('div');
      cell.className = 'month-day-cell other-month';
      cell.innerHTML = `
        <div class="cell-top-bar">
          <span class="cell-day-num">${dayNum}</span>
        </div>
      `;
      cell.addEventListener('click', () => openDayScheduleModal(dateStr));
      grid.appendChild(cell);
    }

    // 2. Current Month Days
    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = formatDateStr(year, month, d);
      const isToday = dateStr === todayStr;

      const cell = document.createElement('div');
      cell.className = `month-day-cell ${isToday ? 'is-today' : ''}`;
      
      const dayApts = filteredApts.filter(a => a.date === dateStr);

      let aptsHtml = '';
      const maxDisplay = 3;
      dayApts.slice(0, maxDisplay).forEach(apt => {
        const client = getClient(apt.clientId);
        aptsHtml += `
          <div class="apt-chip-item" style="background-color: ${client.bg}; color: #111827; border-left-color: ${client.color};" title="${apt.time} ${client.name} - ${apt.service}">
            <span class="apt-chip-time" style="color: ${client.color};">${apt.time}</span>
            <span class="apt-chip-client">${client.name}</span>
          </div>
        `;
      });

      if (dayApts.length > maxDisplay) {
        aptsHtml += `<div class="apt-more-badge">+${dayApts.length - maxDisplay} lainnya</div>`;
      }

      cell.innerHTML = `
        <div class="cell-top-bar">
          <span class="cell-day-num">${d}</span>
        </div>
        ${aptsHtml}
      `;

      // User directive: Clicking ANY part of date opens Daily Google Calendar schedule
      cell.addEventListener('click', () => {
        openDayScheduleModal(dateStr);
      });

      grid.appendChild(cell);
    }

    // 3. Next Month Overflow Days
    const totalCells = adjustedFirstDay + daysInMonth;
    const remaining = totalCells <= 35 ? (35 - totalCells) : (42 - totalCells);
    for (let nextD = 1; nextD <= remaining; nextD++) {
      const nextMonthIdx = month === 11 ? 0 : month + 1;
      const nextYear = month === 11 ? year + 1 : year;
      const dateStr = formatDateStr(nextYear, nextMonthIdx, nextD);

      const cell = document.createElement('div');
      cell.className = 'month-day-cell other-month';
      cell.innerHTML = `
        <div class="cell-top-bar">
          <span class="cell-day-num">${nextD}</span>
        </div>
      `;
      cell.addEventListener('click', () => openDayScheduleModal(dateStr));
      grid.appendChild(cell);
    }
  }

  /**
   * 4B. WEEK VIEW RENDERER
   */
  function renderWeekView() {
    const container = document.getElementById('week-grid-container');
    if (!container) return;

    const filteredApts = getFilteredAppointments();
    const startOfWeek = new Date(currentDate);
    const day = startOfWeek.getDay();
    const diff = startOfWeek.getDate() - day + (day === 0 ? -6 : 1);
    startOfWeek.setDate(diff);

    const weekDays = [];
    for (let i = 0; i < 7; i++) {
      const d = new Date(startOfWeek);
      d.setDate(startOfWeek.getDate() + i);
      weekDays.push(d);
    }

    const hours = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
    const todayStr = '2026-08-26';

    let headerHtml = `
      <div class="week-grid-header-row">
        <div class="week-head-cell" style="color: var(--text-muted);">GMT+7</div>
        ${weekDays.map(d => {
          const dStr = formatDateStr(d.getFullYear(), d.getMonth(), d.getDate());
          const isToday = dStr === todayStr;
          return `
            <div class="week-head-cell ${isToday ? 'is-today' : ''}" style="cursor: pointer;" data-date="${dStr}">
              <span>${dayNamesShort[(d.getDay() === 0 ? 6 : d.getDay() - 1)]}</span>
              <span class="head-day-num">${d.getDate()}</span>
            </div>
          `;
        }).join('')}
      </div>
    `;

    let bodyHtml = `
      <div class="week-time-grid-body">
        <div class="time-axis-col">
          ${hours.map(h => `<div class="time-axis-slot">${h}</div>`).join('')}
        </div>
        ${weekDays.map(d => {
          const dStr = formatDateStr(d.getFullYear(), d.getMonth(), d.getDate());
          const dayApts = filteredApts.filter(a => a.date === dStr);

          let aptsHtml = '';
          dayApts.forEach(apt => {
            const client = getClient(apt.clientId);
            const worker = getWorker(apt.workerId);
            
            const [h, m] = apt.time.split(':').map(Number);
            const hourOffset = (h - 8) + (m / 60);
            const topPx = Math.max(0, hourOffset * 52);
            const heightPx = Math.max(42, (apt.duration / 60) * 52 - 4);

            aptsHtml += `
              <div class="week-apt-block" style="top: ${topPx}px; height: ${heightPx}px; background-color: ${client.bg}; border-left-color: ${client.color}; color: #111827; pointer-events: none;">
                <div class="week-apt-time" style="color: ${client.color};">${apt.time}</div>
                <div class="week-apt-title">${client.name}</div>
                <div class="week-apt-stylist">${worker.name}</div>
              </div>
            `;
          });

          return `
            <div class="week-day-col" style="cursor: pointer;" data-date="${dStr}">
              ${hours.map(() => `<div class="week-hour-slot"></div>`).join('')}
              ${aptsHtml}
            </div>
          `;
        }).join('')}
      </div>
    `;

    container.innerHTML = headerHtml + bodyHtml;

    container.querySelectorAll('[data-date]').forEach(el => {
      el.addEventListener('click', () => {
        const dStr = el.getAttribute('data-date');
        openDayScheduleModal(dStr);
      });
    });
  }

  /**
   * 4C. DAY VIEW RENDERER (Directly opens Day Schedule)
   */
  function renderDayView() {
    const dStr = formatDateStr(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
    openDayScheduleModal(dStr);
  }

  /**
   * 4D. YEAR VIEW RENDERER (12 Mini Month Grids Sesuai Gambar 1)
   */
  function renderYearView() {
    const grid = document.getElementById('year-months-grid');
    if (!grid) return;

    grid.innerHTML = '';
    const filteredApts = getFilteredAppointments();
    const year = currentDate.getFullYear();

    for (let m = 0; m < 12; m++) {
      const monthBox = document.createElement('div');
      monthBox.className = 'year-month-box';

      const firstDay = (new Date(year, m, 1).getDay() === 0 ? 6 : new Date(year, m, 1).getDay() - 1);
      const totalDays = new Date(year, m + 1, 0).getDate();

      let cellsHtml = `
        <span class="year-mini-day-label">S</span>
        <span class="year-mini-day-label">S</span>
        <span class="year-mini-day-label">R</span>
        <span class="year-mini-day-label">K</span>
        <span class="year-mini-day-label">J</span>
        <span class="year-mini-day-label">S</span>
        <span class="year-mini-day-label">M</span>
      `;

      // Blank slots
      for (let i = 0; i < firstDay; i++) {
        cellsHtml += `<span class="year-mini-cell other-month"></span>`;
      }

      // Month days
      for (let d = 1; d <= totalDays; d++) {
        const dStr = formatDateStr(year, m, d);
        const hasApt = filteredApts.some(a => a.date === dStr);
        cellsHtml += `<span class="year-mini-cell ${hasApt ? 'has-event' : ''}" data-date="${dStr}" style="cursor: pointer;">${d}</span>`;
      }

      monthBox.innerHTML = `
        <span class="year-month-title">${monthNames[m]}</span>
        <div class="year-mini-grid">${cellsHtml}</div>
      `;

      grid.appendChild(monthBox);
    }

    // Clicking any day in Year View opens the Google Calendar Day Schedule Modal
    grid.querySelectorAll('.year-mini-cell:not(.other-month)').forEach(cell => {
      cell.addEventListener('click', (e) => {
        e.stopPropagation();
        const dStr = cell.getAttribute('data-date');
        openDayScheduleModal(dStr);
      });
    });
  }

  /**
   * 4E. MINI CALENDAR WIDGET RENDERER
   */
  function renderMiniCalendar() {
    if (!miniCalGrid) return;
    const y = currentDate.getFullYear();
    const m = currentDate.getMonth();

    if (miniCalTitle) miniCalTitle.textContent = `${monthNames[m]} ${y}`;

    const firstDay = (new Date(y, m, 1).getDay() === 0 ? 6 : new Date(y, m, 1).getDay() - 1);
    const totalDays = new Date(y, m + 1, 0).getDate();
    const prevTotalDays = new Date(y, m, 0).getDate();

    const filteredApts = getFilteredAppointments();
    const todayStr = '2026-08-26';
    const activeDateStr = formatDateStr(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());

    let html = `
      <span class="mini-cal-day-label">S</span>
      <span class="mini-cal-day-label">S</span>
      <span class="mini-cal-day-label">R</span>
      <span class="mini-cal-day-label">K</span>
      <span class="mini-cal-day-label">J</span>
      <span class="mini-cal-day-label">S</span>
      <span class="mini-cal-day-label">M</span>
    `;

    for (let i = firstDay - 1; i >= 0; i--) {
      html += `<span class="mini-cal-cell other-month">${prevTotalDays - i}</span>`;
    }

    for (let d = 1; d <= totalDays; d++) {
      const dStr = formatDateStr(y, m, d);
      const isToday = dStr === todayStr;
      const isActive = dStr === activeDateStr;
      const hasEvent = filteredApts.some(a => a.date === dStr);

      html += `
        <span class="mini-cal-cell ${isToday ? 'today' : ''} ${isActive ? 'active-selected' : ''} ${hasEvent ? 'has-event' : ''}" data-date="${dStr}" style="cursor: pointer;">
          ${d}
        </span>
      `;
    }

    miniCalGrid.innerHTML = html;

    miniCalGrid.querySelectorAll('.mini-cal-cell:not(.other-month)').forEach(cell => {
      cell.addEventListener('click', () => {
        const dStr = cell.getAttribute('data-date');
        openDayScheduleModal(dStr);
      });
    });
  }

  // =========================================================================
  // 5. GOOGLE CALENDAR DAILY SCHEDULE POPUP (TIMELINE LIST BERDASARKAN JAM)
  // =========================================================================
  function openDayScheduleModal(dateStr) {
    if (!dayScheduleModal) return;
    activeScheduleDate = dateStr;

    const [y, m, d] = dateStr.split('-').map(Number);
    const targetDate = new Date(y, m - 1, d);

    const dowIndex = targetDate.getDay();
    const dowShort = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'][dowIndex];
    const dowFull = dayNamesFull[dowIndex];
    const monthName = monthNames[m - 1];

    if (dayScheduleDow) dayScheduleDow.textContent = dowShort;
    if (dayScheduleDom) dayScheduleDom.textContent = d;
    if (dayScheduleTitle) dayScheduleTitle.textContent = `${dowFull}, ${d} ${monthName} ${y}`;
    if (btnAddForDay) btnAddForDay.href = `add-appointment.html?date=${dateStr}`;

    const dayAppointments = appointmentsData.filter(a => a.date === dateStr && activeClients.has(a.clientId) && activeWorkers.has(a.workerId));

    if (dayScheduleCount) {
      dayScheduleCount.textContent = `${dayAppointments.length} Reservasi Terjadwal`;
    }

    const hours = [
      { label: '7 AM', timeKey: '07' },
      { label: '8 AM', timeKey: '08' },
      { label: '9 AM', timeKey: '09' },
      { label: '10 AM', timeKey: '10' },
      { label: '11 AM', timeKey: '11' },
      { label: '12 PM', timeKey: '12' },
      { label: '1 PM', timeKey: '13' },
      { label: '2 PM', timeKey: '14' },
      { label: '3 PM', timeKey: '15' },
      { label: '4 PM', timeKey: '16' },
      { label: '5 PM', timeKey: '17' },
      { label: '6 PM', timeKey: '18' },
      { label: '7 PM', timeKey: '19' },
      { label: '8 PM', timeKey: '20' },
      { label: '9 PM', timeKey: '21' }
    ];

    let timelineHtml = '';

    hours.forEach(h => {
      const matchedApts = dayAppointments.filter(a => a.time.startsWith(h.timeKey));

      let aptsCardsHtml = '';
      if (matchedApts.length > 0) {
        aptsCardsHtml = matchedApts.map(apt => {
          const client = getClient(apt.clientId);
          const worker = getWorker(apt.workerId);
          const statusLabel = apt.status === 'confirmed' ? 'Terkonfirmasi' : (apt.status === 'pending' ? 'Menunggu' : 'Selesai');
          const statusClass = apt.status;

          return `
            <div class="gcal-event-card js-gcal-card" data-apt-id="${apt.id}" style="border-left-color: ${client.color};">
              <div class="gcal-card-left">
                <div class="gcal-avatar-box" style="background-color: ${client.bg}; color: ${client.color};">
                  ${client.avatar}
                </div>
                <div class="gcal-meta-text">
                  <span class="gcal-client-title">${client.name}</span>
                  <span class="gcal-service-name">${apt.service}</span>
                  <span class="gcal-stylist-tag">Stylist: <strong>${worker.name}</strong> • ${apt.duration} mnt</span>
                </div>
              </div>
              <div class="gcal-card-right">
                <span class="gcal-time-badge">${apt.time} WIB</span>
                <span class="gcal-status-pill ${statusClass}">${statusLabel}</span>
              </div>
            </div>
          `;
        }).join('');
      }

      timelineHtml += `
        <div class="gcal-hour-row">
          <div class="gcal-hour-time">${h.label}</div>
          <div class="gcal-hour-slot">
            ${aptsCardsHtml}
          </div>
        </div>
      `;
    });

    if (dayAppointments.length === 0) {
      timelineHtml += `
        <div class="gcal-empty-notice">
          <span>Tidak ada reservasi pada tanggal ini. Klik <strong>+ Tambah Appointment</strong> untuk membuat jadwal baru.</span>
        </div>
      `;
    }

    if (gcalTimelineContainer) {
      gcalTimelineContainer.innerHTML = timelineHtml;

      // Attach click events on reservation cards inside Day Schedule Popup
      gcalTimelineContainer.querySelectorAll('.js-gcal-card').forEach(card => {
        card.addEventListener('click', (e) => {
          e.stopPropagation();
          const id = card.getAttribute('data-apt-id');
          // Tutup pop up list reservasi dan langsung buka pop up detail reservasi
          if (dayScheduleModal) dayScheduleModal.style.display = 'none';
          openAppointmentDetail(id);
        });
      });
    }

    dayScheduleModal.style.display = 'flex';
  }

  function closeDayScheduleModal() {
    activeScheduleDate = null;
    if (dayScheduleModal) dayScheduleModal.style.display = 'none';
  }

  if (btnCloseDaySchedule) btnCloseDaySchedule.addEventListener('click', closeDayScheduleModal);
  if (dayScheduleModal) {
    dayScheduleModal.addEventListener('click', (e) => {
      if (e.target === dayScheduleModal) closeDayScheduleModal();
    });
  }

  // =========================================================================
  // 6. APPOINTMENT DETAIL POPOVER MODAL
  // =========================================================================
  function openAppointmentDetail(aptId) {
    const apt = appointmentsData.find(a => a.id === aptId);
    if (!apt || !popoverModal) return;

    selectedAppointmentId = aptId;
    const client = getClient(apt.clientId);
    const worker = getWorker(apt.workerId);

    if (popAvatar) {
      popAvatar.textContent = client.avatar;
      popAvatar.style.color = client.color;
      popAvatar.style.borderColor = client.color;
    }
    if (popClientName) popClientName.textContent = client.name;
    if (popServiceName) popServiceName.textContent = apt.service;
    if (popTimeRange) popTimeRange.textContent = `${apt.time} WIB (${apt.duration} mnt)`;
    if (popStylistName) popStylistName.textContent = `Stylist: ${worker.name}`;
    if (popPhone) popPhone.textContent = client.phone;
    if (popPriceVal) popPriceVal.textContent = `Estimasi Biaya: ${formatIDR(apt.price)}`;
    if (popNotes) popNotes.textContent = apt.notes || 'Tidak ada catatan khusus.';

    if (popEditLink) popEditLink.href = `add-appointment.html?mode=edit&id=${apt.id}`;

    popoverModal.style.display = 'flex';
    popoverModal.classList.add('show');
  }

  function closeAppointmentDetail() {
    if (popoverModal) {
      popoverModal.style.display = 'none';
      popoverModal.classList.remove('show');
    }
    selectedAppointmentId = null;

    // Kembali ke pop up list jadwal harian untuk tanggal yang sedang aktif
    if (activeScheduleDate) {
      const returnDate = activeScheduleDate;
      openDayScheduleModal(returnDate);
    }
  }

  if (popCloseBtn) popCloseBtn.addEventListener('click', closeAppointmentDetail);
  if (popoverModal) {
    popoverModal.addEventListener('click', (e) => {
      if (e.target === popoverModal) closeAppointmentDetail();
    });
  }

  if (popCompleteBtn) {
    popCompleteBtn.addEventListener('click', () => {
      if (!selectedAppointmentId) return;
      const apt = appointmentsData.find(a => a.id === selectedAppointmentId);
      if (apt) {
        apt.status = 'done';
        showToast(`✓ Appointment ${getClient(apt.clientId).name} diselesaikan!`);
        closeAppointmentDetail();
        renderCalendar();
      }
    });
  }

  if (popDeleteBtn) {
    popDeleteBtn.addEventListener('click', () => {
      if (!selectedAppointmentId) return;
      const apt = appointmentsData.find(a => a.id === selectedAppointmentId);
      appointmentsData = appointmentsData.filter(a => a.id !== selectedAppointmentId);
      showToast(`Appointment berhasil dibatalkan.`);
      closeAppointmentDetail();
      renderCalendar();
    });
  }

  // =========================================================================
  // 7. CLIENT & WORKER FILTER LIST RENDERING
  // =========================================================================
  function renderFilterLists() {
    if (clientCheckboxList) {
      clientCheckboxList.innerHTML = clientsData.map(client => `
        <label class="client-check-row">
          <input type="checkbox" value="${client.id}" ${activeClients.has(client.id) ? 'checked' : ''} data-client-id="${client.id}">
          <span class="client-color-dot" style="background-color: ${client.color};"></span>
          <span>${client.name}</span>
        </label>
      `).join('');

      clientCheckboxList.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
          const cId = cb.getAttribute('data-client-id');
          if (cb.checked) {
            activeClients.add(cId);
          } else {
            activeClients.delete(cId);
          }
          renderCalendar();
        });
      });
    }

    if (workerCheckboxList) {
      workerCheckboxList.innerHTML = workersData.map(worker => `
        <label class="client-check-row">
          <input type="checkbox" value="${worker.id}" ${activeWorkers.has(worker.id) ? 'checked' : ''} data-worker-id="${worker.id}">
          <span class="client-color-dot" style="background-color: ${worker.color};"></span>
          <span>${worker.name}</span>
        </label>
      `).join('');

      workerCheckboxList.querySelectorAll('input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
          const wId = cb.getAttribute('data-worker-id');
          if (cb.checked) {
            activeWorkers.add(wId);
          } else {
            activeWorkers.delete(wId);
          }
          renderCalendar();
        });
      });
    }
  }

  if (btnToggleAllClients) {
    btnToggleAllClients.addEventListener('click', () => {
      if (activeClients.size === clientsData.length) {
        activeClients.clear();
      } else {
        activeClients = new Set(clientsData.map(c => c.id));
      }
      renderFilterLists();
      renderCalendar();
    });
  }

  if (btnToggleAllWorkers) {
    btnToggleAllWorkers.addEventListener('click', () => {
      if (activeWorkers.size === workersData.length) {
        activeWorkers.clear();
      } else {
        activeWorkers = new Set(workersData.map(w => w.id));
      }
      renderFilterLists();
      renderCalendar();
    });
  }

  // =========================================================================
  // 8. MASTER CALENDAR DISPATCHER
  // =========================================================================
  function renderCalendar() {
    updatePeriodHeading();
    renderMiniCalendar();

    Object.keys(viewPanels).forEach(v => {
      if (viewPanels[v]) {
        if (v === currentView) {
          viewPanels[v].classList.add('active');
        } else {
          viewPanels[v].classList.remove('active');
        }
      }
    });

    if (currentView === 'month') {
      renderMonthView();
    } else if (currentView === 'week') {
      renderWeekView();
    } else if (currentView === 'day') {
      renderDayView();
    } else if (currentView === 'year') {
      renderYearView();
    }
  }

  function switchView(viewName) {
    currentView = viewName;
    const labels = { month: 'Bulanan', week: 'Mingguan', day: 'Harian', year: 'Tahunan' };
    if (currentViewLabel) currentViewLabel.textContent = labels[viewName] || 'Bulanan';

    document.querySelectorAll('.cal-view-option').forEach(opt => {
      opt.classList.toggle('active', opt.getAttribute('data-view') === viewName);
    });

    renderCalendar();
  }

  // Prev / Next Navigation
  if (todayBtn) {
    todayBtn.addEventListener('click', () => {
      currentDate = new Date(2026, 7, 26);
      renderCalendar();
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      if (currentView === 'year') {
        currentDate.setFullYear(currentDate.getFullYear() - 1);
      } else if (currentView === 'month') {
        currentDate.setMonth(currentDate.getMonth() - 1);
      } else if (currentView === 'week') {
        currentDate.setDate(currentDate.getDate() - 7);
      } else if (currentView === 'day') {
        currentDate.setDate(currentDate.getDate() - 1);
      }
      renderCalendar();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      if (currentView === 'year') {
        currentDate.setFullYear(currentDate.getFullYear() + 1);
      } else if (currentView === 'month') {
        currentDate.setMonth(currentDate.getMonth() + 1);
      } else if (currentView === 'week') {
        currentDate.setDate(currentDate.getDate() + 7);
      } else if (currentView === 'day') {
        currentDate.setDate(currentDate.getDate() + 1);
      }
      renderCalendar();
    });
  }

  if (miniPrevBtn) {
    miniPrevBtn.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      renderCalendar();
    });
  }
  if (miniNextBtn) {
    miniNextBtn.addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      renderCalendar();
    });
  }

  // View Dropdown Toggle
  if (viewToggleBtn && viewMenu) {
    viewToggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      viewMenu.classList.toggle('show');
    });

    document.addEventListener('click', (e) => {
      if (!viewToggleBtn.contains(e.target) && !viewMenu.contains(e.target)) {
        viewMenu.classList.remove('show');
      }
    });

    viewMenu.querySelectorAll('.cal-view-option').forEach(opt => {
      opt.addEventListener('click', () => {
        const view = opt.getAttribute('data-view');
        switchView(view);
        viewMenu.classList.remove('show');
      });
    });
  }

  // Quick Search Filter
  if (quickSearchInput) {
    quickSearchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim();
      renderCalendar();
    });
  }

  // Keyboard Shortcuts
  document.addEventListener('keydown', (e) => {
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
    const key = e.key.toUpperCase();
    if (key === 'D') switchView('day');
    if (key === 'W') switchView('week');
    if (key === 'M') switchView('month');
    if (key === 'Y') switchView('year');
    if (e.key === 'Escape') {
      if (popoverModal && (popoverModal.classList.contains('show') || popoverModal.style.display === 'flex')) {
        closeAppointmentDetail();
      } else if (dayScheduleModal && dayScheduleModal.style.display === 'flex') {
        closeDayScheduleModal();
      }
    }
  });

  // Initial Boot
  renderFilterLists();
  renderCalendar();
});
