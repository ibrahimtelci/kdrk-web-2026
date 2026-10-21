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
      card.hidden = (state.value !== '' && card.dataset.state !== state.value) || (type.value !== '' && (type.value === 'news' ? !['haber', 'duyuru'].includes(card.dataset.type) : card.dataset.type !== type.value)) || !normalize(card.dataset.title).includes(query);
      if (!card.hidden) count++;
    });
    root.querySelector('[data-result-count]').textContent = `${count} / ${cards.length} görsel`;
    root.querySelector('[data-no-results]').hidden = count > 0 || cards.length === 0;
    const filtered = Boolean(query || type.value || state.value);
    root.querySelectorAll('[data-drag-handle]').forEach(handle => { handle.disabled = filtered || handle.closest('[data-banner-card]').dataset.locked === '1'; });
    root.querySelector('[data-order-status]').textContent = filtered ? 'Sürükleyerek sıralamak için filtreleri temizle. Okları kullanarak da taşıyabilirsin.' : 'Sıralamak için tutamacı sürükle veya okları kullan.';
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
  const grid = root.querySelector('.bw-grid');
  const setView = value => {
    root.dataset.view = value === 'list' ? 'list' : 'cards';
    root.querySelectorAll('[data-view-choice]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.viewChoice === root.dataset.view)));
    try { localStorage.setItem('kdrk-slider-view', root.dataset.view); } catch (_) { /* Storage can be disabled. */ }
  };
  let savedView; try { savedView = localStorage.getItem('kdrk-slider-view'); } catch (_) {}
  setView(savedView);
  root.querySelectorAll('[data-view-choice]').forEach(button => button.addEventListener('click', () => setView(button.dataset.viewChoice)));
  const orderForm = root.querySelector('[data-order-form]');
  let drag = null, saving = false;
  const status = root.querySelector('[data-order-status]');
  root.querySelectorAll('[data-drag-handle]').forEach(handle => {
    handle.addEventListener('pointerdown', event => {
      if (handle.disabled || saving || event.button !== 0) return;
      drag = { handle, card: handle.closest('[data-banner-card]'), original: [...grid.children], x: event.clientX, y: event.clientY, moved: false };
      handle.setPointerCapture(event.pointerId);
      event.preventDefault();
    });
    document.addEventListener('pointermove', event => {
      if (!drag || drag.handle !== handle) return;
      if (!drag.moved && Math.hypot(event.clientX - drag.x, event.clientY - drag.y) < 6) return;
      drag.moved = true; drag.card.classList.add('is-dragging');
      const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-banner-card]');
      if (target && target !== drag.card && target.parentElement === grid && target.dataset.eligible === '1' && target.dataset.locked !== '1') {
        const children = [...grid.children];
        grid.insertBefore(drag.card, children.indexOf(drag.card) < children.indexOf(target) ? target.nextSibling : target);
      }
      if (event.clientY < 90) window.scrollBy(0, -12);
      else if (event.clientY > innerHeight - 90) window.scrollBy(0, 12);
    });
    const finish = async event => {
      if (!drag || drag.handle !== handle) return;
      const current = drag; drag = null; current.card.classList.remove('is-dragging');
      if (handle.hasPointerCapture(event.pointerId)) handle.releasePointerCapture(event.pointerId);
      const restore = () => current.original.forEach(card => grid.appendChild(card));
      if (event.type === 'pointercancel') { restore(); return; }
      if (!current.moved || [...grid.children].every((card, index) => card === current.original[index])) return;
      saving = true; root.setAttribute('aria-busy', 'true'); status.textContent = 'Yeni sıra kaydediliyor…';
      const data = new FormData(orderForm);
      grid.querySelectorAll('[data-eligible="1"]').forEach(card => data.append('keys[]', card.dataset.key));
      try {
        const response = await fetch(orderForm.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } });
        const result = await response.json();
        if (!response.ok || !result.ok) throw new Error(result.message || 'Sıralama kaydedilemedi.');
        location.reload();
      } catch (error) {
        restore(); saving = false; root.removeAttribute('aria-busy'); status.textContent = error.message || 'Bağlantı hatası. Tekrar dene.';
      }
    };
    document.addEventListener('pointerup', finish); document.addEventListener('pointercancel', finish);
  });
  if (typeof HTMLDialogElement !== 'function') return;
  const confirmDialog = document.createElement('dialog');
  confirmDialog.className = 'bw-confirm-dialog';
  confirmDialog.setAttribute('aria-labelledby', 'bw-confirm-title');
  confirmDialog.setAttribute('aria-describedby', 'bw-confirm-message');
  confirmDialog.innerHTML = '<h2 id="bw-confirm-title">Slaytı kaldır</h2><p data-confirm-name></p><p id="bw-confirm-message"></p><div class="bw-confirm-actions"><button type="button" class="btn btn-ghost" data-cancel>Vazgeç</button><button type="button" class="btn btn-danger" data-accept>Onayla</button></div>';
  document.body.appendChild(confirmDialog);
  let pendingForm, removeButton, approvedForm;
  confirmDialog.querySelector('[data-cancel]').addEventListener('click', () => confirmDialog.close());
  confirmDialog.addEventListener('close', () => { removeButton?.focus(); pendingForm = null; });
  confirmDialog.querySelector('[data-accept]').addEventListener('click', () => {
    const target = pendingForm; approvedForm = target; confirmDialog.close(); target.requestSubmit();
  });
  root.querySelectorAll('[data-slide-remove]').forEach(removeForm => {
    removeForm.querySelector('[data-confirm]').removeAttribute('data-confirm');
    removeForm.addEventListener('submit', event => {
      if (approvedForm === removeForm) { approvedForm = null; return; }
      event.preventDefault(); pendingForm = removeForm; removeButton = removeForm.querySelector('button');
      confirmDialog.querySelector('[data-confirm-name]').textContent = removeForm.dataset.removeTitle;
      confirmDialog.querySelector('#bw-confirm-message').textContent = removeForm.dataset.removeMessage;
      const permanent = removeForm.action.includes('/sil');
      confirmDialog.querySelector('#bw-confirm-title').textContent = permanent ? 'Tanıtım slaytını sil' : 'Slaytı kaldır';
      confirmDialog.querySelector('[data-accept]').textContent = permanent ? 'Kalıcı olarak sil' : 'Slayttan kaldır';
      confirmDialog.showModal(); confirmDialog.querySelector('[data-cancel]').focus();
    });
  });
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
