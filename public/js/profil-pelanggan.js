/**
 * AFWO Hair Design - Client Profile Script
 * Loads and renders client profile details, visit history, and preferences based on query parameter (?id=...).
 */

document.addEventListener('DOMContentLoaded', () => {
  const clientsData = {
    'eleanor-vance': {
      name: 'Eleanor Vance',
      phone: 'Phone: (555) 123-4567',
      email: 'Email: eleanor.v@example.com',
      hairType: 'Wavy, thick, natural brown.',
      preferredProducts: 'Olaplex No. 4 & 5, Kerastase Elixir Ultime.',
      stylingInstructions: 'Prefers loose waves, extra volume at roots. Sensitive scalp, avoid harsh tugging during blowout.',
      visits: [
        { date: 'OCT 12, 2023', service: 'Balayage', amount: 'Rp 1.250.000' },
        { date: 'AUG 28, 2023', service: 'Haircut & Style', amount: 'Rp 450.000' },
        { date: 'JUL 15, 2023', service: 'Root Touch-up & Gloss', amount: 'Rp 650.000' }
      ]
    },
    'sarah-chen': {
      name: 'Sarah Chen',
      phone: 'Phone: +62 811 2345 6789',
      email: 'Email: sarah.chen@example.com',
      hairType: 'Straight, fine texture, jet black.',
      preferredProducts: 'L\'Oreal Serie Expert, Moroccanoil Treatment.',
      stylingInstructions: 'Prefers blunt ends, light feathered face framing. Low heat blowouts.',
      visits: [
        { date: 'SEP 28, 2023', service: 'Layered Cut & Gloss', amount: 'Rp 550.000' },
        { date: 'JUN 14, 2023', service: 'Deep Conditioning Treatment', amount: 'Rp 380.000' }
      ]
    },
    'james-sterling': {
      name: 'James Sterling',
      phone: 'Phone: +62 813 9876 5432',
      email: 'Email: james.s@example.com',
      hairType: 'Coarse, dense, dark brown.',
      preferredProducts: 'Reuzel Matte Pomade, Uppercut Deluxe.',
      stylingInstructions: 'Skin fade on sides, textured scissor cut on top, beard oil application.',
      visits: [
        { date: 'SEP 15, 2023', service: 'Signature Grooming & Fade', amount: 'Rp 120.000' },
        { date: 'JUL 20, 2023', service: 'Beard Trim & Hot Towel', amount: 'Rp 85.000' }
      ]
    },
    'maya-wright': {
      name: 'Maya Wright',
      phone: 'Phone: +62 818 5554 3210',
      email: 'Email: maya.w@example.com',
      hairType: 'Naturally curly 3A, medium density.',
      preferredProducts: 'Shea Moisture Curl & Shine, Olaplex No. 7 Bonding Oil.',
      stylingInstructions: 'Diffuse drying only, apply leave-in conditioner liberally, no brushing while dry.',
      visits: [
        { date: 'AUG 02, 2023', service: 'Keratin Smoothing & Cut', amount: 'Rp 750.000' },
        { date: 'MAY 10, 2023', service: 'Balayage Refresher', amount: 'Rp 1.100.000' }
      ]
    }
  };

  const urlParams = new URLSearchParams(window.location.search);
  const clientId = urlParams.get('id') || 'eleanor-vance';
  const client = clientsData[clientId] || clientsData['eleanor-vance'];

  // DOM Elements
  const nameEl = document.getElementById('client-name');
  const phoneEl = document.getElementById('client-phone');
  const emailEl = document.getElementById('client-email');
  const editBtn = document.getElementById('btn-edit-profile');
  const hairTypeEl = document.getElementById('pref-hair-type');
  const preferredProductsEl = document.getElementById('pref-products');
  const stylingInstructionsEl = document.getElementById('pref-styling');
  const visitListEl = document.getElementById('visit-history-list');

  // Populate Identity
  if (nameEl) nameEl.textContent = client.name;
  if (phoneEl) phoneEl.textContent = client.phone;
  if (emailEl) emailEl.textContent = client.email;

  // Set Edit Profile Link
  if (editBtn) {
    editBtn.href = `edit-pelanggan.html?mode=edit&id=${clientId}`;
  }

  // Populate Preferences
  if (hairTypeEl) hairTypeEl.textContent = client.hairType;
  if (preferredProductsEl) preferredProductsEl.textContent = client.preferredProducts;
  if (stylingInstructionsEl) stylingInstructionsEl.textContent = client.stylingInstructions;

  // Populate Visit History
  if (visitListEl && client.visits) {
    visitListEl.innerHTML = client.visits.map(visit => `
      <div class="visit-item" data-field="visit-item">
        <div class="visit-item-left">
          <div class="visit-icon-box">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
          </div>
          <div>
            <div class="visit-date" data-field="visit-date">${visit.date}</div>
            <div class="visit-service" data-field="visit-service">${visit.service}</div>
          </div>
        </div>
        <div class="visit-amount" data-field="visit-amount">${visit.amount}</div>
      </div>
    `).join('');
  }
});