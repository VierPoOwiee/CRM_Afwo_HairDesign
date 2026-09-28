/**
 * AFWO Hair Design - Services Management Script
 * Manages live horizontal pill category filtering.
 */

document.addEventListener('DOMContentLoaded', () => {
  const pillTabs = document.querySelectorAll('.pill-tab-btn');
  const serviceCards = document.querySelectorAll('.service-card');
  const emptyState = document.getElementById('services-empty-state');

  pillTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      pillTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');

      const filterValue = tab.getAttribute('data-filter') || 'all';
      let visibleCount = 0;

      serviceCards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        if (filterValue === 'all' || cardCategory === filterValue) {
          card.style.display = 'flex';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (emptyState) {
        if (visibleCount === 0) {
          emptyState.classList.add('show');
        } else {
          emptyState.classList.remove('show');
        }
      }
    });
  });
});