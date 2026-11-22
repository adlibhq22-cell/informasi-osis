(() => {
  document.querySelectorAll('form[data-validate-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        return;
      }
      const fileInputs = form.querySelectorAll('input[type="file"]');
      for (const input of fileInputs) {
        const maxBytes = Number(input.dataset.maxSize || 5242880);
        const maxFiles = Number(input.dataset.maxFiles || 0);
        const files = Array.from(input.files || []);
        if (maxFiles && files.length > maxFiles) {
          event.preventDefault();
          window.alert(`Pilih maksimal ${maxFiles} file.`);
          input.focus();
          return;
        }
        for (const file of files) {
          if (file.size > maxBytes) {
            event.preventDefault();
            const maxMb = Math.round(maxBytes / 1048576);
            window.alert(`File "${file.name}" terlalu besar. Maksimal ${maxMb} MB per file.`);
            input.focus();
            return;
          }
        }
      }
    });
  });
})();
