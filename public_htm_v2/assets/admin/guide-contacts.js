(() => {
  const dialog = document.querySelector('[data-guide-dialog]');
  if (!dialog) return;
  const form = dialog.querySelector('form'), error = dialog.querySelector('[data-guide-error]'), submit = form.querySelector('[type=submit]');
  const selects = [...document.querySelectorAll('[data-guide-select]')];
  const previous = new Map(selects.map(s => [s, s.value]));
  let active = null;
  selects.forEach(select => select.addEventListener('change', () => {
    if (select.value !== '__new') { previous.set(select, select.value); return; }
    select.value = previous.get(select) || ''; active = select; form.reset(); error.hidden = true;
    if (typeof dialog.showModal !== 'function') { location.href = form.action + '/yeni'; return; }
    dialog.showModal(); form.elements.name.focus();
  }));
  dialog.querySelectorAll('[data-guide-close]').forEach(b => b.addEventListener('click', () => dialog.close()));
  dialog.addEventListener('close', () => { if (active) active.focus(); });
  form.addEventListener('submit', async e => {
    e.preventDefault(); if (!form.reportValidity()) return;
    error.hidden = true; submit.disabled = true; submit.textContent = 'Kaydediliyor…';
    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const data = await res.json().catch(() => ({}));
      if (!res.ok || !data.ok) throw Error(data.errors ? Object.values(data.errors).join(' ') : 'Kayıt yapılamadı. Oturumunu ve bağlantını kontrol edip yeniden dene.');
      selects.forEach(select => {
        const option = document.createElement('option'); option.value = data.person.id; option.textContent = data.person.name + ' · ' + data.person.phone;
        select.insertBefore(option, select.querySelector('[value="__new"]'));
      });
      active.value = String(data.person.id); active.dispatchEvent(new Event('change', { bubbles: true }));
      active.dispatchEvent(new CustomEvent('form:changed', { bubbles: true })); dialog.close();
    } catch (ex) { error.textContent = ex.message; error.hidden = false; }
    finally { submit.disabled = false; submit.textContent = 'Kaydet ve seç'; }
  });
})();
