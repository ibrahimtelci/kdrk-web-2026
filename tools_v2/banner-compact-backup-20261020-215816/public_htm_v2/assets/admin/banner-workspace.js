document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-banner-workspace]');
  if (!root) return;
  const search = root.querySelector('[data-banner-search]');
  const cards = [...root.querySelectorAll('[data-banner-card]')];
  if (!search || !cards.length) return;
  const normalize = value => value.toLocaleLowerCase('tr-TR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const filter = () => {
    const query = normalize(search.value.trim());
    let count = 0;
    cards.forEach(card => {
      card.hidden = !normalize(card.dataset.title).includes(query);
      if (!card.hidden) count++;
    });
    root.querySelector('[data-result-count]').textContent = `${count} / ${cards.length} görsel`;
    root.querySelector('[data-no-results]').hidden = count > 0;
  };
  root.querySelector('[data-library-tools]').hidden = false;
  search.addEventListener('input', filter);
  filter();
});
