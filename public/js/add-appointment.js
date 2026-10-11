/**
 * AFWO Hair Design - Add / Edit Appointment Form Controller
 * Manages appointment scheduling, auto-calculating service durations,
 * time slot picking, input validation, and preparing payloads for backend APIs.
 */

document.addEventListener('DOMContentLoaded', () => {

  const appointmentForm = document.getElementById('appointment-form');
  const appointmentIdInput = document.getElementById('appointment-id');
  const formTitle = document.getElementById('appointment-form-title');
  const formSubtitle = document.getElementById('appointment-form-subtitle');

  const clientSelect = document.getElementById('appointment-client');
  const serviceSelect = document.getElementById('appointment-service');
  const workerSelect = document.getElementById('appointment-worker');
  const dateInput = document.getElementById('appointment-date');
  const timeInput = document.getElementById('appointment-time');
  const durationInput = document.getElementById('appointment-duration');
  const statusSelect = document.getElementById('appointment-status');
  const notesTextarea = document.getElementById('appointment-notes');

  const errorClient = document.getElementById('error-client');
  const errorService = document.getElementById('error-service');
  const errorWorker = document.getElementById('error-worker');
  const errorDate = document.getElementById('error-date');
  const errorTime = document.getElementById('error-time');

  const toastBox = document.getElementById('toast-success');
  const toastMessage = document.getElementById('toast-message');
  const slotPills = document.querySelectorAll('.slot-pill-btn');

  // Parse URL query parameters
  const urlParams = new URLSearchParams(window.location.search);
  const mode = urlParams.get('mode') || 'add';
  const editId = urlParams.get('id');
  const fromParam = urlParams.get('from') || urlParams.get('returnUrl');
  const clientParam = urlParams.get('clientId');

  const btnCancel = document.getElementById('btn-cancel');
  const btnBackNav = document.getElementById('btn-back-nav');

  // If navigated from clients page, clicking Cancel or Back returns to clients.html
  if (fromParam === 'clients') {
    if (btnCancel) btnCancel.href = 'clients.html';
    if (btnBackNav) btnBackNav.href = 'clients.html';
  }

  // Pre-select client if passed in URL
  if (clientParam && clientSelect) {
    clientSelect.value = clientParam;
  }

  // Mock Appointment DB for edit pre-population
  const mockAppointments = {
    'apt-1': {
      clientId: 'eleanor-vance',
      service: 'Balayage Color Treatment',
      workerId: 'agus-pratama',
      date: '2026-08-26',
      time: '10:00',
      duration: 120,
      status: 'confirmed',
      notes: 'Preferensi: Balayage nuansa ash blonde, formula bebas amonia.'
    },
    'apt-2': {
      clientId: 'sari-handayani',
      service: 'Creambath & Blow Dry',
      workerId: 'rina',
      date: '2026-08-26',
      time: '13:30',
      duration: 60,
      status: 'confirmed',
      notes: 'Pijatan sedang, hair tonic ginseng.'
    }
  };

  // 1. Check Edit Mode
  if (mode === 'edit' && editId && mockAppointments[editId]) {
    const data = mockAppointments[editId];
    if (formTitle) formTitle.textContent = 'Edit Jadwal Janji Temu';
    if (formSubtitle) formSubtitle.textContent = `Mengubah jadwal kunjungan untuk ID: ${editId}`;
    if (appointmentIdInput) appointmentIdInput.value = editId;

    if (clientSelect) clientSelect.value = data.clientId;
    if (serviceSelect) serviceSelect.value = data.service;
    if (workerSelect) workerSelect.value = data.workerId;
    if (dateInput) dateInput.value = data.date;
    if (timeInput) timeInput.value = data.time;
    if (durationInput) durationInput.value = data.duration;
    if (statusSelect) statusSelect.value = data.status;
    if (notesTextarea) notesTextarea.value = data.notes;
  }

  // 2. Service selection auto-populates duration
  if (serviceSelect) {
    serviceSelect.addEventListener('change', () => {
      const selectedOption = serviceSelect.options[serviceSelect.selectedIndex];
      const duration = selectedOption ? selectedOption.getAttribute('data-duration') : null;
      if (duration && durationInput) {
        durationInput.value = duration;
      }
      if (errorService) errorService.style.display = 'none';
    });
  }

  // Clear validation errors on user interaction
  if (clientSelect) clientSelect.addEventListener('change', () => { if (errorClient) errorClient.style.display = 'none'; });
  if (workerSelect) workerSelect.addEventListener('change', () => { if (errorWorker) errorWorker.style.display = 'none'; });
  if (dateInput) dateInput.addEventListener('input', () => { if (errorDate) errorDate.style.display = 'none'; });
  if (timeInput) timeInput.addEventListener('input', () => { if (errorTime) errorTime.style.display = 'none'; });

  // 3. Quick Time Slot Pills
  slotPills.forEach(pill => {
    pill.addEventListener('click', () => {
      slotPills.forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      const timeVal = pill.getAttribute('data-time');
      if (timeInput && timeVal) {
        timeInput.value = timeVal;
        if (errorTime) errorTime.style.display = 'none';
      }
    });
  });

  // 4. Form Submission and Validation
  if (appointmentForm) {
    appointmentForm.addEventListener('submit', (e) => {
      e.preventDefault();

      let isValid = true;

      // Validate Client
      if (!clientSelect.value) {
        if (errorClient) errorClient.style.display = 'block';
        isValid = false;
      } else {
        if (errorClient) errorClient.style.display = 'none';
      }

      // Validate Service
      if (!serviceSelect.value) {
        if (errorService) errorService.style.display = 'block';
        isValid = false;
      } else {
        if (errorService) errorService.style.display = 'none';
      }

      // Validate Worker
      if (!workerSelect.value) {
        if (errorWorker) errorWorker.style.display = 'block';
        isValid = false;
      } else {
        if (errorWorker) errorWorker.style.display = 'none';
      }

      // Validate Date
      if (!dateInput.value) {
        if (errorDate) errorDate.style.display = 'block';
        isValid = false;
      } else {
        if (errorDate) errorDate.style.display = 'none';
      }

      // Validate Time
      if (!timeInput.value) {
        if (errorTime) errorTime.style.display = 'block';
        isValid = false;
      } else {
        if (errorTime) errorTime.style.display = 'none';
      }

      if (!isValid) return;

      // Prepare Backend JSON Payload
      const payload = {
        id: appointmentIdInput.value || `apt-${Date.now()}`,
        clientId: clientSelect.value,
        service: serviceSelect.value,
        workerId: workerSelect.value,
        date: dateInput.value,
        time: timeInput.value,
        duration: parseInt(durationInput.value, 10) || 60,
        status: statusSelect.value || 'confirmed',
        notes: notesTextarea.value.trim(),
        createdAt: new Date().toISOString()
      };

      console.log('[AFWO CRM] Siap disambungkan ke API Backend:', mode === 'edit' ? 'PUT /api/appointments/' + payload.id : 'POST /api/appointments', payload);

      // Show Toast Notification
      if (toastBox) {
        if (toastMessage) {
          toastMessage.textContent = mode === 'edit' 
            ? '✓ Perubahan jadwal temu berhasil disimpan!' 
            : '✓ Jadwal temu baru berhasil dibuat!';
        }
        toastBox.classList.add('show');
      }

      // Redirect back to appointment calendar after 1.2 seconds
      setTimeout(() => {
        window.location.href = 'appointment.html';
      }, 1200);
    });
  }

});
