(() => {
  const body = document.body;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (!body || reduceMotion) return;

  body.classList.add('page-entering');
  window.setTimeout(() => body.classList.remove('page-entering'), 360);

  window.addEventListener('pageshow', () => body.classList.remove('page-leaving'));

  document.addEventListener('click', (event) => {
    if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;

    const link = event.target.closest('a[href]');
    if (!link || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;

    const destination = new URL(link.href, window.location.href);
    if (destination.origin !== window.location.origin || destination.hash && destination.pathname === window.location.pathname && destination.search === window.location.search) return;
    if (destination.href === window.location.href) return;

    event.preventDefault();
    body.classList.add('page-leaving');
    window.setTimeout(() => window.location.assign(destination.href), 150);
  }, true);
})();