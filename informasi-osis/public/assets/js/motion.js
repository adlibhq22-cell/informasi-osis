/* Progressive enhancement: no content is hidden unless an observer is active. */
(() => {
  'use strict';
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)');
  const selectors = '.hero-copy, .redesign-art, .section-heading, .announcement-card, .event-card, .person-card, .album-card, .callout-card, .contact-card, .auth-intro, .auth-card, .panel-card, .dashboard-stat, .report-card';
  let observer;
  const revealAll = () => {
    if (observer) observer.disconnect();
    document.querySelectorAll('.motion-ready').forEach(el => el.classList.add('is-visible'));
  };
  if (!reduce.matches && 'IntersectionObserver' in window) {
    observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('is-visible');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.06, rootMargin: '0px 0px 15px 0px' });
    document.querySelectorAll(selectors).forEach((el, index) => {
      el.style.setProperty('--reveal-delay', `${Math.min(index % 3 * 65, 130)}ms`);
      el.classList.add('motion-ready');
      observer.observe(el);
    });
    // A fallback guards against browser extensions or lifecycle interruptions.
    window.setTimeout(() => document.querySelectorAll('.motion-ready').forEach(el => {
      const box = el.getBoundingClientRect();
      if (box.top < innerHeight && box.bottom > 0) el.classList.add('is-visible');
    }), 1800);
  }
  if (reduce.addEventListener) reduce.addEventListener('change', e => { if (e.matches) revealAll(); });
  document.querySelectorAll('.main-nav a').forEach(link => {
    const linkPath = new URL(link.href, location.href).pathname.replace(/\/$/, '/index.php');
    const currentPath = location.pathname.replace(/\/$/, '/index.php');
    if (linkPath === currentPath) link.setAttribute('aria-current', 'page');
  });
  const toggle = document.querySelector('[data-nav-toggle]');
  const nav = document.querySelector('[data-nav]');
  document.addEventListener('keydown', event => {
    if (event.key === 'Escape' && nav && nav.classList.contains('is-open')) {
      nav.classList.remove('is-open');
      document.body.classList.remove('nav-open');
      if (toggle) { toggle.setAttribute('aria-expanded', 'false'); toggle.focus(); }
    }
  });
  const header = document.querySelector('.site-header');
  if (header) {
    const progress = document.createElement('div');
    progress.className = 'read-progress';
    progress.setAttribute('aria-hidden', 'true');
    header.append(progress);
    let scheduled = false;
    const update = () => {
      scheduled = false;
      const total = document.documentElement.scrollHeight - innerHeight;
      progress.style.width = `${total > 0 ? Math.max(0, Math.min(100, scrollY / total * 100)) : 0}%`;
    };
    addEventListener('scroll', () => { if (!scheduled) { scheduled = true; requestAnimationFrame(update); } }, { passive: true });
    addEventListener('resize', update, { passive: true });
    update();
  }
})();
