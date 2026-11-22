(() => {
  const checkboxes = Array.from(document.querySelectorAll('input[name="anggota_ids[]"]'));
  const counter = document.querySelector('[data-selected-count]');
  const update = () => { if (counter) counter.textContent = `${checkboxes.filter((item) => item.checked).length} dipilih`; };
  checkboxes.forEach((item) => item.addEventListener('change', update));
  update();
})();
