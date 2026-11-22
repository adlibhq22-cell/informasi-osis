(() => {
  const lightbox = document.querySelector('[data-lightbox]');
  const gallery = document.querySelector('[data-lightbox-gallery]');
  if (!lightbox || !gallery) return;
  const tiles = Array.from(gallery.querySelectorAll('[data-lightbox-src]'));
  const image = lightbox.querySelector('[data-lightbox-image]');
  const caption = lightbox.querySelector('[data-lightbox-caption-output]');
  let index = 0;
  let previousFocus = null;
  const show = (nextIndex) => {
    if (!tiles.length) return;
    index = (nextIndex + tiles.length) % tiles.length;
    const tile = tiles[index];
    image.src = tile.dataset.lightboxSrc;
    image.alt = tile.dataset.lightboxCaption || 'Foto galeri';
    caption.textContent = tile.dataset.lightboxCaption || '';
  };
  const open = (nextIndex, trigger) => {
    previousFocus = trigger;
    show(nextIndex);
    lightbox.classList.add('is-open');
    lightbox.setAttribute('aria-hidden', 'false');
    document.body.classList.add('lightbox-open');
    lightbox.querySelector('[data-lightbox-close]').focus();
  };
  const close = () => {
    lightbox.classList.remove('is-open');
    lightbox.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('lightbox-open');
    image.removeAttribute('src');
    if (previousFocus) previousFocus.focus();
  };
  tiles.forEach((tile, i) => tile.addEventListener('click', () => open(i, tile)));
  lightbox.querySelector('[data-lightbox-close]').addEventListener('click', close);
  lightbox.querySelector('[data-lightbox-prev]').addEventListener('click', () => show(index - 1));
  lightbox.querySelector('[data-lightbox-next]').addEventListener('click', () => show(index + 1));
  lightbox.addEventListener('click', (event) => { if (event.target === lightbox) close(); });
  document.addEventListener('keydown', (event) => {
    if (!lightbox.classList.contains('is-open')) return;
    if (event.key === 'Escape') close();
    if (event.key === 'ArrowLeft') show(index - 1);
    if (event.key === 'ArrowRight') show(index + 1);
  });
})();
