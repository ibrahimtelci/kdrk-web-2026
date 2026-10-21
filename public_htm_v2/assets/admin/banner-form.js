document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-cta-choice]').forEach(choice => {
    const box = document.querySelector(`[data-cta-activity="${choice.dataset.ctaChoice}"]`);
    const draw = () => { const needsActivity = ['apply', 'details'].includes(choice.value); box.hidden = !needsActivity; box.querySelector('select').required = needsActivity; };
    choice.addEventListener('change', draw); draw();
  });
});
