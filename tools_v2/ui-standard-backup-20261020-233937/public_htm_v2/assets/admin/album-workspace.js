/* V2 albüm çalışma alanı; mevcut POST, CSRF ve yükleme akışları korunur. */
document.addEventListener('DOMContentLoaded', () => {
  const root = document.querySelector('[data-album-workspace]');
  if (!root) return;
  const nav = root.querySelector('[data-album-tabs]');
  const tabs = [...root.querySelectorAll('[data-album-tab]')];
  const panes = [...root.querySelectorAll('[data-album-pane]')];
  const show = (id, focus = false) => {
    panes.forEach(pane => { pane.hidden = pane.id !== id; });
    tabs.forEach(tab => {
      const selected = tab.dataset.albumTab === id;
      tab.setAttribute('aria-selected', String(selected));
      tab.tabIndex = selected ? 0 : -1;
      if (selected && focus) tab.focus();
    });
  };
  if (nav) {
    nav.hidden = false;
    nav.setAttribute('role', 'tablist');
    tabs.forEach((tab, index) => {
      tab.setAttribute('role', 'tab');
      const pane = panes.find(p => p.id === tab.dataset.albumTab);
      pane.setAttribute('role', 'tabpanel');
      pane.setAttribute('aria-labelledby', tab.id);
      tab.addEventListener('click', () => {
        show(tab.dataset.albumTab);
        history.replaceState(null, '', '#' + tab.dataset.albumTab);
      });
      tab.addEventListener('keydown', event => {
        let next;
        if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
        if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
        if (event.key === 'Home') next = 0;
        if (event.key === 'End') next = tabs.length - 1;
        if (next === undefined) return;
        event.preventDefault(); show(tabs[next].dataset.albumTab, true);
      });
    });
    const showFromAddress = () => show(location.hash === '#album-bilgileri' ? 'album-bilgileri' : 'fotograflar');
    if (root.querySelector('[aria-invalid="true"]')) show('album-bilgileri');
    else showFromAddress();
    window.addEventListener('hashchange', showFromAddress);
    root.addEventListener('invalid', event => {
      const pane = event.target.closest('[data-album-pane]');
      if (pane) show(pane.id);
    }, true);
  }
  if (typeof HTMLDialogElement !== 'function') return;
  const items = [...root.querySelectorAll('[data-photo-detail]')];
  if (!items.length) return;
  const dialog = document.createElement('dialog');
  dialog.className = 'album-dialog';
  dialog.setAttribute('aria-labelledby', 'album-photo-title');
  dialog.innerHTML = '<div class="album-dialog-head"><h2 id="album-photo-title"></h2><button type="button" class="btn-icon" data-prev aria-label="Önceki fotoğraf">‹</button><button type="button" class="btn-icon" data-next aria-label="Sonraki fotoğraf">›</button><button type="button" class="btn-icon" data-close aria-label="Ayrıntıları kapat">×</button></div><div data-detail-slot></div>';
  document.body.appendChild(dialog);
  const slot = dialog.querySelector('[data-detail-slot]');
  let active = -1, initial = '', sending = false, opener;
  const snapshot = () => {
    const form = slot.querySelector('form');
    return form ? JSON.stringify([...new FormData(form)]) : '';
  };
  const canLeave = () => {
    if (initial === snapshot()) return true;
    if (!confirm('Fotoğraf açıklamasındaki değişiklikler kaydedilmedi. Kaydetmeden çıkılsın mı?')) return false;
    slot.querySelector('form')?.reset();
    return true;
  };
  const restore = () => {
    if (active >= 0 && slot.firstElementChild) items[active].appendChild(slot.firstElementChild);
  };
  const open = index => {
    if (index < 0 || index >= items.length) return;
    if (dialog.open && !canLeave()) return;
    restore(); active = index;
    const item = items[index];
    opener = item.querySelector('summary');
    slot.appendChild(item.querySelector('[data-photo-body]'));
    initial = snapshot(); sending = false;
    dialog.querySelector('h2').textContent = `Fotoğraf ${index + 1} / ${items.length}`;
    dialog.querySelector('[data-prev]').disabled = index === 0;
    dialog.querySelector('[data-next]').disabled = index === items.length - 1;
    if (!dialog.open) dialog.showModal();
  };
  items.forEach((item, index) => item.querySelector('summary').addEventListener('click', event => {
    event.preventDefault(); open(index);
  }));
  const close = () => { if (canLeave()) dialog.close(); };
  dialog.querySelector('[data-close]').addEventListener('click', close);
  dialog.querySelector('[data-prev]').addEventListener('click', () => open(active - 1));
  dialog.querySelector('[data-next]').addEventListener('click', () => open(active + 1));
  dialog.addEventListener('cancel', event => { event.preventDefault(); close(); });
  dialog.addEventListener('close', () => { restore(); active = -1; opener?.focus({ preventScroll: true }); });
  dialog.addEventListener('submit', event => {
    // Kapak ve sıralama işlemleri açıklama alanlarını kaydetmez.
    if (event.submitter?.value !== 'save' && !canLeave()) {
      event.preventDefault();
      return;
    }
    // Silme onayı panel.js tarafından verilir; iptal edilmiş gönderim beklenmez.
    queueMicrotask(() => { sending = !event.defaultPrevented; });
  });
  window.addEventListener('beforeunload', event => {
    if (dialog.open && !sending && initial !== snapshot()) { event.preventDefault(); event.returnValue = ''; }
  });
});
