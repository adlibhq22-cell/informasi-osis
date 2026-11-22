(() => {
  const sidebar = document.querySelector('[data-admin-sidebar]');
  const toggle = document.querySelector('[data-admin-toggle]');
  if (sidebar && toggle) {
    toggle.addEventListener('click', () => sidebar.classList.toggle('is-open'));
    document.addEventListener('click', (event) => {
      if (!sidebar.contains(event.target) && !toggle.contains(event.target)) sidebar.classList.remove('is-open');
    });
  }
  document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!window.confirm(form.dataset.confirm || 'Lanjutkan tindakan ini?')) event.preventDefault();
    });
  });
  const markAll = document.querySelector('[data-mark-all-present]');
  if (markAll) markAll.addEventListener('click', () => {
    document.querySelectorAll('.attendance-status-select').forEach((select) => { select.value = 'hadir'; });
  });
})();
