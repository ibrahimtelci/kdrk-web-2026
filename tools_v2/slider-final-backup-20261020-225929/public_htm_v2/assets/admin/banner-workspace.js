document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-banner-workspace]');
  if (!root) return;
  const form = root.querySelector('[data-banner-filters]');
  const search = root.querySelector('[data-banner-search]');
  const type = root.querySelector('[data-banner-type]');
  const state = root.querySelector('[data-banner-state]');
  const cards = [...root.querySelectorAll('[data-banner-card]')];
  const normalize = value => value.toLocaleLowerCase('tr-TR').normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  const filter = () => {
    const query = normalize(search.value.trim());
    let count = 0;
    cards.forEach(card => {
      card.hidden = (state.value !== '' && card.dataset.state !== state.value) || (type.value !== '' && card.dataset.type !== type.value) || !normalize(card.dataset.title).includes(query);
      if (!card.hidden) count++;
    });
    root.querySelector('[data-result-count]').textContent = `${count} / ${cards.length} görsel`;
    root.querySelector('[data-no-results]').hidden = count > 0 || cards.length === 0;
    const address = new URL(location.href);
    address.searchParams.delete('sekme');
    ['tur', 'q', 'durum'].forEach((key, i) => {
      const value = i === 0 ? type.value : (i === 1 ? search.value.trim() : state.value);
      if (value) address.searchParams.set(key, value); else address.searchParams.delete(key);
    });
    history.replaceState(null, '', address);
    root.querySelectorAll('[data-type-choice]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.typeChoice === type.value)));
  };
  root.querySelector('[data-filter-submit]').hidden = true;
  form.addEventListener('submit', event => { event.preventDefault(); filter(); });
  search.addEventListener('input', filter);
  type.addEventListener('change', filter);
  state.addEventListener('change', filter);
  root.querySelectorAll('[data-type-choice]').forEach(button => button.addEventListener('click', () => { type.value = button.dataset.typeChoice; filter(); }));
  filter();
  const disclosures = [...root.querySelectorAll('.bw-card-menu, [data-create-menu]')];
  document.addEventListener('click', event => disclosures.forEach(box => { if (!box.contains(event.target)) box.open = false; }));
  document.addEventListener('keydown', event => { if (event.key === 'Escape') disclosures.forEach(box => { if (box.open) { box.open = false; box.querySelector('summary').focus(); } }); });
  if (typeof HTMLDialogElement !== 'function') return;
  root.querySelectorAll('[data-source-picker]').forEach(source => {
    const body = source.querySelector('[data-picker-body]');
    const dialog = document.createElement('dialog');
    dialog.className = 'bw-picker-dialog';
    const title = body.querySelector('h2'); title.id = 'picker-title-' + source.dataset.sourcePicker;
    dialog.setAttribute('aria-labelledby', title.id);
    dialog.appendChild(body); document.body.appendChild(dialog); source.hidden = true;
    body.querySelector('[data-picker-tools]').hidden = false;
    body.querySelector('[data-picker-close]').hidden = false;
    const category = body.querySelector('[data-picker-category]');
    const search = body.querySelector('[data-picker-search]');
    const options = [...body.querySelectorAll('[data-picker-option]')];
    const submit = body.querySelector('button[type="submit"]');
    let opener;
    const filterChoices = () => {
      let count = 0;
      options.forEach(option => {
        const hidden = (category.value !== '' && category.value !== option.dataset.category) || !normalize(option.dataset.title).includes(normalize(search.value.trim()));
        option.hidden = hidden;
        if (hidden) option.querySelector('input').checked = false;
        if (!hidden) count++;
      });
      body.querySelector('[data-picker-empty]').hidden = count !== 0;
      submit.disabled = !body.querySelector('input[type="radio"]:checked');
    };
    category.addEventListener('change', filterChoices); search.addEventListener('input', filterChoices);
    body.addEventListener('change', filterChoices);
    body.querySelector('[data-picker-close]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => root.querySelector('[data-create-menu] > summary')?.focus());
    root.querySelectorAll(`[data-picker-open="${source.dataset.sourcePicker}"]`).forEach(button => button.addEventListener('click', event => {
      event.preventDefault(); opener = button; root.querySelector('[data-create-menu]').open = false;
      filterChoices(); dialog.showModal(); category.focus();
    }));
    filterChoices();
  });
});
