/* Anasayfa görselleri: filtre, arama, sıralama ve görünüm iki listede (tablo + kart) ortak çalışır.
   Sunucu aynı parametreleri anlar; JS yalnızca sayfayı yeniden yüklemeden uygular. */
document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-banner-workspace]');
  if (!root) return;
  const $ = (selector, el = root) => el.querySelector(selector);
  const $$ = (selector, el = root) => [...el.querySelectorAll(selector)];
  const normalize = value => value.toLocaleLowerCase('tr-TR').normalize('NFD').replace(/[̀-ͯ]/g, '');
  const collator = new Intl.Collator('tr', { sensitivity: 'base', numeric: true });
  const icon = (name, size) => window.KdrkUI ? window.KdrkUI.icon(name, size) : '';

  const form = $('[data-banner-filters]');
  const search = $('[data-filter-search]');
  const clearSearch = $('[data-search-clear]');
  const sortInput = $('[data-filter-sort]');
  const dirInput = $('[data-filter-dir]');
  const lists = $$('[data-list]');
  const live = $('[data-order-status]');
  const say = (message, error = false) => { if (!live) return; live.textContent = message; live.classList.toggle('is-error', error); };

  const state = {
    group: $('[data-filter-group]').value,
    q: search.value.trim(),
    sort: sortInput.value || 'sira',
    dir: dirInput.value || 'asc',
    view: root.dataset.view,
  };
  const filtered = () => Boolean(state.group || state.q);
  const defaultOrder = () => state.sort === 'sira' && state.dir === 'asc';
  const activeList = () => lists.find(list => list.dataset.list === state.view) || lists[0];

  const compare = (a, b) => {
    const x = a.dataset, y = b.dataset;
    let r = 0;
    if (state.sort === 'baslik') r = collator.compare(x.title, y.title);
    else if (state.sort === 'kategori') r = collator.compare(x.label, y.label);
    else if (state.sort === 'durum') r = (y.state === 'active') - (x.state === 'active');
    else if (state.sort === 'guncelleme') r = x.updated.localeCompare(y.updated);
    r = r || (Number(x.position) - Number(y.position));
    return state.dir === 'desc' ? -r : r;
  };

  const currentUrl = () => {
    const url = new URL(location.href);
    const values = { tur: state.group, q: state.q, sirala: state.sort === 'sira' ? '' : state.sort, yon: state.dir === 'desc' ? 'desc' : '' };
    ['sekme', 'durum', 'gorunum', ...Object.keys(values)].forEach(key => url.searchParams.delete(key));
    Object.entries(values).forEach(([key, value]) => { if (value) url.searchParams.set(key, value); });
    return url;
  };

  /* ---------- Seçim ve toplu işlem ---------- */
  const bulk = $('[data-bulk]');
  const selectAll = $('[data-select-all]');
  const selectedKeys = () => [...new Set($$('[data-select]:checked', activeList()).map(box => box.value))];
  const updateSelection = () => {
    if (!bulk) return;
    const visible = $$('[data-item]:not([hidden]) [data-select]', activeList());
    const count = selectedKeys().length;
    bulk.hidden = count === 0;
    $('[data-bulk-count]').textContent = `${count} slayt seçildi`;
    $('[data-bulk-clear]').hidden = count === 0;
    const checked = visible.filter(box => box.checked).length;
    selectAll.checked = visible.length > 0 && checked === visible.length;
    selectAll.indeterminate = checked > 0 && checked < visible.length;
  };
  const setChecked = (key, checked) => $$('[data-select]').forEach(box => { if (box.value === key) box.checked = checked; });
  root.addEventListener('change', event => {
    if (event.target.matches('[data-select]')) { setChecked(event.target.value, event.target.checked); updateSelection(); }
  });
  selectAll?.addEventListener('change', () => {
    $$('[data-item]:not([hidden]) [data-select]', activeList()).forEach(box => setChecked(box.value, selectAll.checked));
    updateSelection();
  });
  $('[data-bulk-clear]')?.addEventListener('click', () => { $$('[data-select]').forEach(box => { box.checked = false; }); updateSelection(); selectAll.focus(); });
  // Aynı slayt iki görünümde de bulunduğundan gönderimde tek kopya kalır.
  bulk?.addEventListener('submit', () => {
    const keys = selectedKeys();
    $$('[data-select]').forEach(box => { box.disabled = !keys.includes(box.value) || !activeList().contains(box); });
    bulk.querySelectorAll('button[type="submit"]').forEach(button => { button.disabled = true; });
    say('İşlem uygulanıyor…');
  });

  /* ---------- Filtre, arama, sıralama, görünüm ---------- */
  const apply = () => {
    const query = normalize(state.q);
    lists.forEach(list => {
      const items = $$(':scope > [data-item]', list);
      const sorted = [...items].sort(compare);
      if (sorted.some((item, i) => item !== items[i])) sorted.forEach(item => list.appendChild(item));
      items.forEach(item => {
        const d = item.dataset;
        item.hidden = !((!state.group || d.group === state.group) && normalize(d.title).includes(query));
        if (item.hidden) item.querySelectorAll('[data-select]').forEach(box => { box.checked = false; });
      });
    });
    const total = $$(':scope > [data-item]', lists[0] || root).length;
    const visible = $$(':scope > [data-item]:not([hidden])', lists[0] || root).length;
    const count = $('[data-result-count]');
    if (count) count.textContent = `${filtered() ? `${visible} / ${total}` : total} slayt`;
    const empty = $('[data-no-results]');
    if (empty) empty.hidden = visible > 0 || total === 0;

    $$('[data-group-choice]').forEach(button => {
      const on = button.dataset.groupChoice === state.group;
      button.classList.toggle('is-active', on);
      button.setAttribute('aria-pressed', String(on));
    });
    $('[data-filter-group]').value = state.group;
    clearSearch.hidden = search.value === '';
    sortInput.value = state.sort === 'sira' ? '' : state.sort; dirInput.value = state.dir === 'asc' ? '' : state.dir;

    $$('.bw-table th[class^="bw-col-"]').forEach(th => {
      const link = th.querySelector('[data-sort]');
      if (!link) return;
      const on = link.dataset.sort === state.sort;
      if (on) th.setAttribute('aria-sort', state.dir === 'asc' ? 'ascending' : 'descending'); else th.removeAttribute('aria-sort');
      const holder = link.querySelector('.bw-sort-icon');
      if (holder && window.KdrkUI) holder.innerHTML = icon(on ? (state.dir === 'asc' ? 'arrowup' : 'arrowdown') : 'sort', 14);
    });

    root.dataset.view = state.view;
    $$('[data-view-pane]').forEach(pane => { pane.hidden = pane.dataset.viewPane !== state.view; });
    $$('[data-view-choice]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.viewChoice === state.view)));
    $('[data-filter-view]').value = state.view;
    window.KdrkUI?.rememberView?.('slaytlar', state.view);

    const canDrag = !filtered() && defaultOrder();
    $$('[data-drag-handle]').forEach(handle => {
      handle.disabled = !canDrag;
      handle.title = canDrag ? 'Sürükle ya da ok tuşlarıyla taşı' : 'Sırayı değiştirmek için filtreleri temizle ve Sıra’ya göre sırala';
    });

    const url = currentUrl();
    history.replaceState(null, '', url);
    $$('[data-return]').forEach(input => { input.value = url.pathname + url.search; });
    updateSelection();
  };

  form.addEventListener('submit', event => { event.preventDefault(); state.q = search.value.trim(); apply(); });
  $$('[data-group-choice]').forEach(button => button.addEventListener('click', event => { event.preventDefault(); state.group = button.dataset.groupChoice; apply(); }));
  search.addEventListener('input', () => { state.q = search.value.trim(); apply(); });
  clearSearch.addEventListener('click', () => { search.value = ''; state.q = ''; apply(); search.focus(); });
  $$('[data-view-choice]').forEach(button => button.addEventListener('click', event => { event.preventDefault(); state.view = button.dataset.viewChoice; apply(); }));
  $$('[data-sort]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    const key = link.dataset.sort;
    if (state.sort === key) state.dir = state.dir === 'asc' ? 'desc' : 'asc';
    else { state.sort = key; state.dir = key === 'guncelleme' ? 'desc' : 'asc'; }
    apply();
  }));
  $$('[data-filter-reset]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    Object.assign(state, { group: '', q: '', sort: 'sira', dir: 'asc' });
    search.value = '';
    apply();
    search.focus();
  }));

  /* ---------- İşlem menüleri ---------- */
  const menus = () => $$('[data-row-menu][open], [data-create-menu][open]');
  const closeMenus = except => menus().forEach(menu => { if (menu !== except) menu.open = false; });
  const items = menu => $$('.bw-menu-list a, .bw-menu-list button:not(:disabled)', menu);
  root.addEventListener('toggle', event => {
    const menu = event.target;
    if (!menu.matches('[data-row-menu], [data-create-menu]')) return;
    if (!menu.open) { menu.classList.remove('is-up'); return; }
    closeMenus(menu);
    const list = $('.bw-menu-list', menu);
    const bottomBar = document.querySelector('.mobile-nav')?.offsetHeight || 0;
    menu.classList.remove('is-up');
    const rect = list.getBoundingClientRect();
    if (rect.bottom > innerHeight - bottomBar - 8 && rect.height < menu.getBoundingClientRect().top - 8) menu.classList.add('is-up');
  }, true);
  document.addEventListener('click', event => menus().forEach(menu => { if (!menu.contains(event.target)) menu.open = false; }));
  root.addEventListener('keydown', event => {
    const menu = event.target.closest('[data-row-menu], [data-create-menu]');
    if (!menu) return;
    if (event.key === 'Escape' && menu.open) { menu.open = false; menu.querySelector('summary').focus(); event.preventDefault(); return; }
    if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
    if (!menu.open) { if (event.target.matches('summary') && event.key === 'ArrowDown') { menu.open = true; } else return; }
    const list = items(menu);
    if (!list.length) return;
    event.preventDefault();
    const index = list.indexOf(document.activeElement);
    const next = event.key === 'Home' ? 0 : event.key === 'End' ? list.length - 1
      : event.key === 'ArrowDown' ? (index + 1) % list.length : (index <= 0 ? list.length - 1 : index - 1);
    list[next].focus();
  });
  root.addEventListener('focusout', event => {
    const menu = event.target.closest('[data-row-menu][open], [data-create-menu][open]');
    if (menu && event.relatedTarget && !menu.contains(event.relatedTarget)) menu.open = false;
  });

  /* ---------- Sıralama: sürükle-bırak ve klavye ---------- */
  const orderForm = $('[data-order-form]');
  let drag = null;
  let saving = false;
  const saveOrder = async (list, restore) => {
    saving = true; root.setAttribute('aria-busy', 'true'); say('Yeni sıra kaydediliyor…');
    const data = new FormData(orderForm);
    $$(':scope > [data-item]', list).forEach(item => data.append('keys[]', item.dataset.key));
    try {
      const response = await fetch(orderForm.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error(result.message || 'Sıralama kaydedilemedi.');
      say('Sıralama kaydedildi. Liste yenileniyor…');
      location.reload();
    } catch (error) {
      restore(); saving = false; root.removeAttribute('aria-busy');
      say(error.message || 'Bağlantı hatası. Tekrar dene.', true);
    }
  };
  root.addEventListener('pointerdown', event => {
    const handle = event.target.closest('[data-drag-handle]');
    if (!handle || handle.disabled || saving || event.button !== 0 || !orderForm) return;
    const item = handle.closest('[data-item]');
    drag = { handle, item, list: item.parentElement, original: [...item.parentElement.children], x: event.clientX, y: event.clientY, moved: false, id: event.pointerId };
    handle.setPointerCapture(event.pointerId);
    event.preventDefault();
  });
  document.addEventListener('pointermove', event => {
    if (!drag || event.pointerId !== drag.id) return;
    if (!drag.moved && Math.hypot(event.clientX - drag.x, event.clientY - drag.y) < 6) return;
    drag.moved = true; drag.item.classList.add('is-dragging');
    const target = document.elementFromPoint(event.clientX, event.clientY)?.closest('[data-item]');
    if (target && target !== drag.item && target.parentElement === drag.list) {
      const children = [...drag.list.children];
      drag.list.insertBefore(drag.item, children.indexOf(drag.item) < children.indexOf(target) ? target.nextSibling : target);
    }
    const bottomBar = document.querySelector('.mobile-nav')?.offsetHeight || 0;
    if (event.clientY < 90) window.scrollBy(0, -12);
    else if (event.clientY > innerHeight - bottomBar - 60) window.scrollBy(0, 12);
  });
  const finishDrag = event => {
    if (!drag || event.pointerId !== drag.id) return;
    const current = drag; drag = null;
    current.item.classList.remove('is-dragging');
    if (current.handle.hasPointerCapture(event.pointerId)) current.handle.releasePointerCapture(event.pointerId);
    const restore = () => current.original.forEach(item => current.list.appendChild(item));
    if (event.type === 'pointercancel') { restore(); return; }
    if (!current.moved || [...current.list.children].every((item, i) => item === current.original[i])) return;
    saveOrder(current.list, restore);
  };
  document.addEventListener('pointerup', finishDrag);
  document.addEventListener('pointercancel', finishDrag);
  // Klavye: tutamaçta yukarı/aşağı ok, menüdeki "Bir sıra yukarı/aşağı" işlemini gönderir.
  root.addEventListener('keydown', event => {
    const handle = event.target.closest('[data-drag-handle]');
    if (!handle || handle.disabled || saving || !['ArrowUp', 'ArrowDown'].includes(event.key)) return;
    const button = handle.closest('[data-item]').querySelector(`[data-move="${event.key === 'ArrowUp' ? 'up' : 'down'}"]`);
    event.preventDefault();
    if (!button || button.disabled) { say(event.key === 'ArrowUp' ? 'Slayt zaten ilk sırada.' : 'Slayt zaten son sırada.'); return; }
    saving = true; say('Yeni sıra kaydediliyor…');
    button.form.requestSubmit(button);
  });

  apply();

  /* ---------- Silme / slayttan kaldırma onayı ---------- */
  if (typeof HTMLDialogElement === 'function') {
    const confirmDialog = document.createElement('dialog');
    confirmDialog.className = 'bw-confirm-dialog';
    confirmDialog.setAttribute('aria-labelledby', 'bw-confirm-title');
    confirmDialog.setAttribute('aria-describedby', 'bw-confirm-message');
    confirmDialog.innerHTML = '<h2 id="bw-confirm-title">Slaytı kaldır</h2><p data-confirm-name></p><p id="bw-confirm-message"></p><div class="bw-confirm-actions"><button type="button" class="btn btn-ghost" data-cancel>Vazgeç</button><button type="button" class="btn btn-danger" data-accept>Onayla</button></div>';
    document.body.appendChild(confirmDialog);
    let pendingForm, returnFocus, approvedForm;
    confirmDialog.querySelector('[data-cancel]').addEventListener('click', () => confirmDialog.close());
    confirmDialog.addEventListener('close', () => { returnFocus?.focus(); pendingForm = null; });
    confirmDialog.querySelector('[data-accept]').addEventListener('click', () => {
      const target = pendingForm; approvedForm = target; returnFocus = null; confirmDialog.close(); target.requestSubmit();
    });
    $$('[data-slide-remove]').forEach(removeForm => {
      removeForm.querySelector('[data-confirm]')?.removeAttribute('data-confirm');
      removeForm.addEventListener('submit', event => {
        if (approvedForm === removeForm) { approvedForm = null; return; }
        event.preventDefault();
        pendingForm = removeForm;
        const menu = removeForm.closest('[data-row-menu]');
        returnFocus = menu?.querySelector('summary') || removeForm.querySelector('button');
        if (menu) menu.open = false;
        const permanent = removeForm.action.includes('/sil');
        confirmDialog.querySelector('[data-confirm-name]').textContent = removeForm.dataset.removeTitle;
        confirmDialog.querySelector('#bw-confirm-message').textContent = removeForm.dataset.removeMessage;
        confirmDialog.querySelector('#bw-confirm-title').textContent = permanent ? 'Tanıtım slaytını sil' : 'Slayttan kaldır';
        confirmDialog.querySelector('[data-accept]').textContent = permanent ? 'Kalıcı olarak sil' : 'Slayttan kaldır';
        confirmDialog.showModal();
        confirmDialog.querySelector('[data-cancel]').focus();
      });
    });

    /* ---------- Faaliyet / duyuru seçme penceresi ---------- */
    $$('[data-source-picker]').forEach(source => {
      const body = source.querySelector('[data-picker-body]');
      const dialog = document.createElement('dialog');
      dialog.className = 'bw-picker-dialog';
      const title = body.querySelector('h2'); title.id = 'picker-title-' + source.dataset.sourcePicker;
      dialog.setAttribute('aria-labelledby', title.id);
      dialog.appendChild(body); document.body.appendChild(dialog); source.hidden = true;
      body.querySelector('[data-picker-tools]').hidden = false;
      body.querySelector('[data-picker-close]').hidden = false;
      const category = body.querySelector('[data-picker-category]');
      const pickerSearch = body.querySelector('[data-picker-search]');
      const options = [...body.querySelectorAll('[data-picker-option]')];
      const submit = body.querySelector('button[type="submit"]');
      const filterChoices = () => {
        let count = 0;
        options.forEach(option => {
          const hidden = (category.value !== '' && category.value !== option.dataset.category) || !normalize(option.dataset.title).includes(normalize(pickerSearch.value.trim()));
          option.hidden = hidden;
          if (hidden) option.querySelector('input').checked = false;
          if (!hidden) count++;
        });
        body.querySelector('[data-picker-empty]').hidden = count !== 0;
        submit.disabled = !body.querySelector('input[type="radio"]:checked');
      };
      category.addEventListener('change', filterChoices); pickerSearch.addEventListener('input', filterChoices);
      body.addEventListener('change', filterChoices);
      body.querySelector('[data-picker-close]').addEventListener('click', () => dialog.close());
      dialog.addEventListener('close', () => $('[data-create-menu] > summary')?.focus());
      $$(`[data-picker-open="${source.dataset.sourcePicker}"]`).forEach(link => link.addEventListener('click', event => {
        event.preventDefault();
        $('[data-create-menu]').open = false;
        body.querySelectorAll('[data-return]').forEach(input => { input.value = currentUrl().pathname + currentUrl().search; });
        filterChoices(); dialog.showModal(); category.focus();
      }));
      filterChoices();
    });
  }
});
