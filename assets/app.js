document.querySelectorAll('[data-confirm]').forEach((button) =>
  button.addEventListener('click', (event) => {
    if (!confirm(button.dataset.confirm)) event.preventDefault();
  }),
);
document.querySelectorAll('form[method="post"]').forEach((form) =>
  form.addEventListener('submit', () => {
    setTimeout(() => {
      const button = form.querySelector('button');
      if (button) {
        button.disabled = true;
        button.textContent = 'Please wait…';
      }
    }, 0);
  }),
);
