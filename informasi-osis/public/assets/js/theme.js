(() => {
  'use strict';
  const key = 'osis-theme';
  const valid = (value) => value === 'dark' || value === 'light';
  const readSaved = () => {
    try { return localStorage.getItem(key); } catch (_) { return null; }
  };
  const save = (theme) => {
    try { localStorage.setItem(key, theme); } catch (_) { /* Theme still works for this page. */ }
  };
  const systemTheme = () => window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  const updateMeta = (theme) => {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', theme === 'dark' ? '#121d1f' : '#f5f6ef');
  };
  const apply = (theme) => {
    const chosen = valid(theme) ? theme : 'light';
    document.documentElement.setAttribute('data-theme', chosen);
    updateMeta(chosen);
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      const dark = chosen === 'dark';
      button.setAttribute('aria-pressed', dark ? 'true' : 'false');
      button.setAttribute('aria-label', dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
      button.title = dark ? 'Beralih ke mode terang' : 'Beralih ke mode gelap';
      const icon = button.querySelector('[data-theme-icon]');
      const label = button.querySelector('[data-theme-label]');
      if (icon) icon.textContent = dark ? '☀' : '☾';
      if (label) label.textContent = dark ? 'Mode terang' : 'Mode gelap';
    });
  };
  const saved = readSaved();
  apply(valid(saved) ? saved : systemTheme());

  const bind = () => {
    apply(document.documentElement.getAttribute('data-theme'));
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        apply(next);
        save(next);
      });
    });
  };
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind, { once: true });
  else bind();

  window.addEventListener('storage', (event) => {
    if (event.key === key && valid(event.newValue)) apply(event.newValue);
  });
})();
