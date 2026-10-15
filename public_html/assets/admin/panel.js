/* KDRK Panel — küçük etkileşimler. Panel sunucu tarafında çalışır; bu dosya yalnızca kolaylık katar. */
(function () {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const root = document.documentElement;

  /* Tema */
  const SUN = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>';
  const MOON = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>';
  const syncTheme = () => $$('[data-theme-btn]').forEach(b => {
    const dark = root.getAttribute('data-theme') !== 'light';
    b.innerHTML = dark ? SUN : MOON;
    b.setAttribute('aria-label', dark ? 'Açık temaya geç' : 'Koyu temaya geç');
  });
  $$('[data-theme-btn]').forEach(b => b.addEventListener('click', () => {
    const t = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
    root.setAttribute('data-theme', t);
    try { localStorage.setItem('kdrk-admin-theme', t); } catch (e) {}
    syncTheme();
  }));
  syncTheme();

  /* Mobil kenar çubuğu */
  const sidebar = $('[data-sidebar]'), scrim = $('[data-sidebar-scrim]'), menuBtn = $('[data-menu]');
  const setMenu = open => {
    if (!sidebar) return;
    sidebar.classList.toggle('open', open);
    if (scrim) scrim.hidden = !open;
    if (menuBtn) menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) { const first = sidebar.querySelector('a'); if (first) first.focus(); }
  };
  if (menuBtn) menuBtn.addEventListener('click', () => setMenu(!sidebar.classList.contains('open')));
  if (scrim) scrim.addEventListener('click', () => setMenu(false));
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && sidebar && sidebar.classList.contains('open')) { setMenu(false); menuBtn.focus(); } });

  /* Onay isteyen butonlar */
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-confirm]');
    if (b && !confirm(b.dataset.confirm)) e.preventDefault();
  });

  /* Gönderimde butonu kilitle — çift tıklamayla iki kayıt olmasın */
  document.addEventListener('submit', e => {
    const btn = e.submitter && e.submitter.matches('[data-busy]') ? e.submitter : e.target.querySelector('[data-busy]');
    if (btn) { setTimeout(() => { btn.disabled = true; btn.lastChild.textContent = btn.dataset.busy; }, 0); }
  });

  /* Kapak önizlemesi */
  const coverInput = $('[data-cover-input]'), coverPrev = $('[data-cover-prev]');
  if (coverInput && coverPrev) coverInput.addEventListener('change', () => {
    const f = coverInput.files[0];
    if (!f) return;
    if (f.size > 15 * 1024 * 1024) { alert('Görsel en fazla 15 MB olabilir.'); coverInput.value = ''; return; }
    coverPrev.style.backgroundImage = 'url(' + URL.createObjectURL(f) + ')';
  });

  /* Galeri yükleyici: her dosya ayrı istekte gider (sunucunun toplam istek sınırına takılmaz) */
  const upForm = $('[data-photo-upload]');
  if (upForm && window.fetch && window.FormData) {
    const input = $('[data-photo-input]', upForm), zone = $('[data-dropzone]', upForm), prog = $('[data-up-progress]', upForm);
    const submitRow = $('[data-up-submit]', upForm);
    const token = upForm.querySelector('input[name="_csrf"]').value;
    const OK = ['image/jpeg', 'image/png', 'image/webp'];
    if (submitRow) submitRow.hidden = true;
    let busy = false;
    const say = (html) => { prog.hidden = false; prog.innerHTML = html; };
    const esc = s => String(s).replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    async function send(files) {
      if (busy || !files.length) return;
      busy = true;
      const errors = [];
      const list = [...files].filter(f => {
        if (!OK.includes(f.type)) { errors.push(f.name + ': yalnızca JPG, PNG veya WebP yüklenebilir.'); return false; }
        if (f.size > 15 * 1024 * 1024) { errors.push(f.name + ': dosya 15 MB’tan büyük.'); return false; }
        return true;
      });
      let done = 0;
      for (const f of list) {
        say('Yükleniyor: <b>' + done + ' / ' + list.length + '</b> · ' + esc(f.name));
        const fd = new FormData();
        fd.append('_csrf', token);
        fd.append('photos[]', f);
        try {
          const r = await fetch(upForm.action, { method: 'POST', body: fd, headers: { Accept: 'application/json' }, credentials: 'same-origin' });
          const j = await r.json().catch(() => ({ ok: false, errors: ['Sunucu yanıt vermedi (' + r.status + ').'] }));
          if (j.ok) done++; else errors.push(...(j.errors || [f.name + ': yüklenemedi.']));
        } catch (_) {
          errors.push(f.name + ': bağlantı kesildi, tekrar dene.');
        }
      }
      busy = false;
      input.value = '';
      if (!errors.length) {
        say('<b>' + done + ' fotoğraf yüklendi.</b> Sayfa yenileniyor…');
        location.href = location.pathname + '#fotograflar';
        location.reload();
        return;
      }
      say((done ? '<b>' + done + ' fotoğraf yüklendi.</b> ' : '') + errors.map(e => '<span class="f-err">' + esc(e) + '</span>').join('') +
        (done ? ' <a href="' + location.pathname + '#fotograflar" onclick="location.reload()">Sayfayı yenile</a>' : ''));
    }
    input.addEventListener('change', () => send(input.files));
    ['dragenter', 'dragover'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.add('drag'); }));
    ['dragleave', 'drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); zone.classList.remove('drag'); }));
    zone.addEventListener('drop', e => send(e.dataTransfer.files));
  }

  /* Ulaşım: kulüp aracı seçilince biniş noktası görünür */
  $$('[data-transport]').forEach(sel => {
    const pick = sel.closest('form').querySelector('[data-pickup]');
    sel.addEventListener('change', () => { if (pick) pick.hidden = sel.value !== 'club'; });
  });

  /* Program düzenleyici: gün başlığı + tarih + saatli maddeler → gizli JSON alanı */
  const editor = $('[data-program-editor]'), store = $('[data-program-json]');
  if (editor && store) {
    const readonly = !!editor.closest('fieldset[disabled]');
    let days;
    try { days = JSON.parse(store.value || '[]'); } catch (_) { days = []; }
    const el = (tag, attrs = {}, kids = []) => {
      const n = document.createElement(tag);
      Object.entries(attrs).forEach(([k, v]) => { if (k === 'text') n.textContent = v; else n.setAttribute(k, v); });
      kids.forEach(k => n.appendChild(k));
      return n;
    };
    const iconBtn = (label, onClick) => {
      const b = el('button', { type: 'button', class: 'btn-icon del', 'aria-label': label, title: label });
      b.innerHTML = '<svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>';
      b.addEventListener('click', onClick);
      if (readonly) b.disabled = true;
      return b;
    };
    const save = () => { store.value = JSON.stringify(days); };
    function draw() {
      editor.innerHTML = '';
      if (!days.length) {
        editor.appendChild(el('p', { class: 'f-hint', text: 'Henüz program yok. Çok günlü faaliyetlerde her gün için ayrı blok ekle.' }));
      }
      days.forEach((d, di) => {
        d.items = d.items || [];
        const title = el('input', { type: 'text', placeholder: 'Günün başlığı (ör. Zirve tırmanışı)', 'aria-label': (di + 1) + '. gün başlığı', maxlength: '120' });
        title.value = d.title || '';
        title.addEventListener('input', () => { d.title = title.value; save(); });
        const date = el('input', { type: 'date', 'aria-label': (di + 1) + '. gün tarihi' });
        date.value = d.date || '';
        date.addEventListener('input', () => { d.date = date.value || null; save(); });
        const items = el('div', { class: 'pe-items' });
        d.items.forEach((it, ii) => {
          const t = el('input', { type: 'text', inputmode: 'numeric', placeholder: '06:30', 'aria-label': 'Saat', maxlength: '5' });
          t.value = it.time || '';
          t.addEventListener('input', () => { it.time = t.value; save(); });
          const x = el('input', { type: 'text', placeholder: 'Ne yapılacak', 'aria-label': 'Açıklama', maxlength: '200' });
          x.value = it.text || '';
          x.addEventListener('input', () => { it.text = x.value; save(); });
          items.appendChild(el('div', { class: 'pe-item' }, [t, x, iconBtn('Maddeyi sil', () => { d.items.splice(ii, 1); save(); draw(); })]));
        });
        const addItem = el('button', { type: 'button', class: 'btn btn-ghost btn-sm pe-add-item', text: '+ Saat ekle' });
        addItem.addEventListener('click', () => { d.items.push({ time: '', text: '' }); save(); draw(); editor.querySelectorAll('.pe-day')[di].querySelector('.pe-item:last-child input').focus(); });
        if (readonly) addItem.hidden = true;
        editor.appendChild(el('div', { class: 'pe-day' }, [
          el('div', { class: 'pe-day-head' }, [el('span', { class: 'pe-day-no', text: (di + 1) + '. GÜN' }), title, date, iconBtn((di + 1) + '. günü sil', () => { if (confirm((di + 1) + '. gün silinsin mi?')) { days.splice(di, 1); save(); draw(); } })]),
          items, addItem,
        ]));
      });
    }
    const addDay = $('[data-add-day]');
    if (addDay) addDay.addEventListener('click', () => {
      days.push({ title: '', date: null, items: [{ time: '', text: '' }] });
      save(); draw();
      const all = editor.querySelectorAll('.pe-day');
      all[all.length - 1].querySelector('input').focus();
    });
    draw();
  }
})();
