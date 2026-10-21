document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-banner-workspace]');
  if (!root) return;
  const form = root.querySelector('[data-banner-filters]');
  const search = root.querySelector('[data-banner-search]');
  const type = root.querySelector('[data-banner-type]');
  const cards = [...root.querySelectorAll('[data-banner-card]')];
  const normalize = value => value.toLocaleLowerCase('tr-TR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const filter = () => {
    const query = normalize(search.value.trim());
    let count = 0;
    cards.forEach(card => {
      card.hidden = (type.value !== '' && card.dataset.type !== type.value) || !normalize(card.dataset.title).includes(query);
      if (!card.hidden) count++;
    });
    root.querySelector('[data-result-count]').textContent = `${count} / ${cards.length} görsel`;
    root.querySelector('[data-no-results]').hidden = count > 0 || cards.length === 0;
    const address = new URL(location.href);
    address.searchParams.delete('sekme');
    ['tur', 'q'].forEach((key, i) => {
      const value = i === 0 ? type.value : search.value.trim();
      if (value) address.searchParams.set(key, value); else address.searchParams.delete(key);
    });
    history.replaceState(null, '', address);
  };
  root.querySelector('[data-filter-submit]').hidden = true;
  form.addEventListener('submit', event => { event.preventDefault(); filter(); });
  search.addEventListener('input', filter);
  type.addEventListener('change', filter);
  filter();
});
