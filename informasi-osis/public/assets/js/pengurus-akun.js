(() => {
  const select = document.querySelector('[data-officer-member-select]');
  const fields = document.querySelector('[data-officer-account-fields]');
  if (!select || !fields) return;
  const username = fields.querySelector('[name="username"]');
  const password = fields.querySelector('[name="password"]');
  const update = () => {
    const option = select.options[select.selectedIndex];
    const needsAccount = !!option && option.dataset.hasAccount === '0' && option.value !== '';
    fields.hidden = !needsAccount;
    [username, password].forEach((input) => {
      if (!input) return;
      input.disabled = !needsAccount;
      input.required = needsAccount;
    });
  };
  select.addEventListener('change', update);
  update();
})();
