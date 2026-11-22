(() => {
  'use strict';
  document.addEventListener('keydown', (event) => {
    const isM = event.code === 'KeyM' || (event.key && event.key.toLowerCase() === 'm');
    if (!event.ctrlKey || event.altKey || event.metaKey || event.shiftKey || event.repeat || !isM) return;
    const target = document.body && document.body.dataset.adminLoginUrl;
    if (!target) return;
    event.preventDefault();
    window.location.assign(target);
  });
})();
