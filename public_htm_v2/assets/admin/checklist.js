/* Faaliyet yoklaması: butonlar ve tutar formu fetch ile gönderilir; satır, ilerleme çubukları ve özet
   sayfa yenilenmeden güncellenir. İstek başarısız olursa form normal gönderilir (veri kaybolmaz).
   Geldi ve Ödedi switch'tir (role="switch", aria-checked). Ödedi açılınca tutar alanı faaliyet ücretiyle
   dolu gelir; Enter / ✓ ile kaydedilir. Geldi kapatılınca işaret kaldırılır (gelmedi / bakılmadı).
   Arama isim ya da telefonla süzer ("0532 111" yazmak yeter); süzgeç yalnız görünümü değiştirir. */
(() => {
  'use strict';
  const list = document.querySelector('.ck-list');
  if (!list) return;
  const rows = [...list.querySelectorAll('[data-ck-row]')];
  const tools = document.querySelector('[data-ck-tools]');
  const search = document.querySelector('[data-ck-search]');
  const none = document.querySelector('[data-ck-none]');
  const $sum = k => document.querySelector('[data-sum="' + k + '"]');
  const $track = k => document.querySelector('[data-track="' + k + '"]');
  let filter = 'all';
  if (tools) tools.hidden = false;

  function paint(row, r) {
    row.dataset.attended = r.attended === null ? '' : String(r.attended);
    row.dataset.paid = String(r.paid);
    const came = row.querySelector('.ck-switch.is-came');
    if (came) { came.setAttribute('aria-checked', String(r.attended === 1)); came.value = r.attended === 1 ? '' : '1'; }
    const pay = row.querySelector('.ck-switch.is-paid');
    if (pay) { pay.setAttribute('aria-checked', String(r.paid === 1)); pay.value = r.paid ? '0' : '1'; }
    const box = row.querySelector('[data-ck-amount]');
    if (!box) return null;
    const opened = box.hidden && r.paid === 1;
    box.hidden = r.paid !== 1;
    const input = box.querySelector('input[name="amount"]');
    input.value = r.amountText || '';
    input.removeAttribute('aria-invalid');
    const msg = box.querySelector('.ck-amount-msg');
    if (msg.classList.contains('is-error')) { msg.textContent = ''; msg.classList.remove('is-error'); }
    box.dataset.saved = input.value;
    return opened ? input : null; // yeni açıldıysa odak tutara gider
  }
  function summary(s) {
    if (!s) return;
    const set = (k, v) => { const el = $sum(k); if (el) el.textContent = v; };
    const width = (k, n) => { const el = $track(k); if (el) el.style.width = (s.total ? n / s.total * 100 : 0) + '%'; };
    set('came', s.came); set('paid', s.paid); set('open', s.total - s.came);
    if (s.payText) set('paytext', s.payText);
    width('came', s.came); width('paid', s.paid);
  }
  function apply() {
    const q = (search?.value || '').toLocaleLowerCase('tr').trim();
    const digits = q.replace(/\D/g, '').replace(/^0/, '');
    let shown = 0;
    rows.forEach(r => {
      const hit = !q || r.dataset.name.includes(q) || (digits.length >= 3 && r.dataset.name.includes(digits));
      const ok = hit && (filter === 'all'
        || (filter === 'open' && r.dataset.attended !== '1')
        || (filter === 'unpaid' && r.dataset.paid === '0'));
      r.hidden = !ok; if (ok) shown++;
    });
    if (none) none.hidden = shown > 0;
  }
  // Yalnız değişen alan gönderilir; JSON gelmezse (oturum, ağ) form klasik yolla gönderilir.
  async function send(form, fields, row) {
    const data = new FormData();
    data.set('_csrf', form.querySelector('[name="_csrf"]').value);
    Object.entries(fields).forEach(([k, v]) => data.set(k, v));
    row.classList.add('is-saving');
    try {
      const res = await fetch(form.action, { method: 'POST', body: data, headers: { Accept: 'application/json' } });
      const json = await res.json().catch(() => ({}));
      if (res.status === 422 && json.message) return { error: json.message };
      if (!res.ok || !json.ok) throw new Error('kaydedilemedi');
      const focus = paint(row, json);
      summary(json.summary);
      apply();
      if (focus) { focus.focus(); focus.select(); }
      return { ok: true };
    } catch (_) {
      Object.entries(fields).forEach(([k, v]) => {
        const h = document.createElement('input');
        h.type = 'hidden'; h.name = k; h.value = v; form.append(h);
      });
      form.submit();
      return { ok: false };
    } finally {
      row.classList.remove('is-saving');
    }
  }

  list.addEventListener('click', e => {
    const btn = e.target.closest('.ck-form button[name]');
    if (!btn) return;
    e.preventDefault();
    send(btn.form, { [btn.name]: btn.value }, btn.closest('[data-ck-row]'));
  });

  // Tutar: Enter / ✓ ile gönderilir, alandan çıkınca değiştiyse kaydedilir.
  list.querySelectorAll('[data-ck-amount]').forEach(form => {
    const input = form.querySelector('input[name="amount"]');
    const msg = form.querySelector('.ck-amount-msg');
    const row = form.closest('[data-ck-row]');
    form.dataset.saved = input.value;
    const save = async () => {
      if (input.value.trim() === (form.dataset.saved || '').trim()) return;
      msg.textContent = '';
      const r = await send(form, { paid: '1', amount: input.value }, row);
      if (r.error) { input.setAttribute('aria-invalid', 'true'); msg.textContent = r.error; msg.classList.add('is-error'); input.focus(); return; }
      if (r.ok) { msg.textContent = 'Kaydedildi'; setTimeout(() => { if (msg.textContent === 'Kaydedildi') msg.textContent = ''; }, 1800); }
    };
    form.addEventListener('submit', e => { e.preventDefault(); save(); });
    input.addEventListener('change', save);
  });

  search?.addEventListener('input', apply);
  document.querySelectorAll('[data-ck-filter]').forEach(b => b.addEventListener('click', () => {
    filter = b.dataset.ckFilter;
    document.querySelectorAll('[data-ck-filter]').forEach(x => { const on = x === b; x.classList.toggle('is-active', on); x.setAttribute('aria-pressed', on); });
    apply();
  }));
})();
