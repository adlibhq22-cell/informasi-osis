(() => {
  const modal = document.querySelector('[data-member-modal-root]');
  if (!modal) return;
  const content = modal.querySelector('[data-member-modal-content]');
  const closeButtons = modal.querySelectorAll('[data-member-close]');
  let returnFocus = null;
  const close = () => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('modal-open');
    if (returnFocus) returnFocus.focus();
  };
  const escape = (event) => { if (event.key === 'Escape') close(); };
  closeButtons.forEach((button) => button.addEventListener('click', close));
  document.addEventListener('keydown', escape);
  document.querySelectorAll('[data-member-modal]').forEach((trigger) => {
    trigger.addEventListener('click', async () => {
      returnFocus = trigger;
      content.innerHTML = '<p class="modal-loading">Memuat profil anggota…</p>';
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.classList.add('modal-open');
      try {
        const response = await fetch(`../api/anggota-detail.php?id=${encodeURIComponent(trigger.dataset.memberModal)}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Profil tidak ditemukan.');
        const person = await response.json();
        const photo = person.foto_url
          ? `<img src="${escapeHtml(person.foto_url)}" alt="${escapeHtml(person.nama)}">`
          : `<span>${escapeHtml((person.nama || '?').slice(0, 1).toUpperCase())}</span>`;
        content.innerHTML = `<div class="modal-profile-photo">${photo}</div><span class="eyebrow">ANGGOTA OSIS</span><h2 id="member-modal-name">${escapeHtml(person.nama)}</h2><p class="profile-class">${escapeHtml(person.kelas || 'Siswa aktif')}</p><p class="profile-public-note">Bagian dari komunitas aktif OSIS.</p>`;
        modal.querySelector('[data-member-modal-close-focus]')?.focus();
      } catch (error) {
        content.innerHTML = `<p class="modal-loading">${escapeHtml(error.message || 'Gagal memuat profil.')}</p>`;
      }
    });
  });
  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
  }
})();
