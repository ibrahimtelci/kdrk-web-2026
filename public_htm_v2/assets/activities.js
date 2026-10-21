/* Faaliyetler sayfası: sekme, kategori, arama, sıralama, kart/takvim görünümü.
   Kaynak, sunucunun bastığı .activity-card'lardır (data-date, data-end, data-cat, data-status…).
   Tarihler "YYYY-AA-GG" metni olarak karşılaştırılır; Date nesnesi yalnız yerel saatle kurulur,
   UTC dönüşümü yapılmaz — faaliyet günü kaymaz. "Bugün" sunucudan (Europe/Istanbul) gelir. */
(function () {
  'use strict';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const store = { get: k => { try { return localStorage.getItem(k); } catch (_) { return null; } }, set: (k, v) => { try { localStorage.setItem(k, v); } catch (_) {} } };

  const MO = ['OCA', 'ŞUB', 'MAR', 'NİS', 'MAY', 'HAZ', 'TEM', 'AĞU', 'EYL', 'EKİ', 'KAS', 'ARA'];
  const MONTHS = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
  const DAYS = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];

  const params = new URLSearchParams(location.search);
  const grid = $('[data-grid]'), cards = $$('.activity-card', grid), empty = $('[data-empty]');
  const pills = $$('.filter-pill'), tabs = $$('.list-tab'), search = $('[data-search]'), viewBtns = $$('[data-view]');
  const cal = $('[data-cal]');

  let view = params.get('view') === 'takvim' ? 'takvim' : (store.get('kdrk-faaliyet-view') || 'kart');
  let tab = params.get('tab') === 'gecmis' ? 'gecmis' : 'yaklasan';
  let filter = params.get('cat') || 'all';
  let sort = tab === 'gecmis' ? 'date-desc' : 'date-asc';
  let term = '';

  const isPast = c => ['done', 'past'].includes(c.dataset.status);
  const inTab = c => isPast(c) === (tab === 'gecmis');
  const textOf = c => ['h3', 'p', '.activity-loc'].map(s => $(s, c)?.textContent || '').join(' ').toLocaleLowerCase('tr');
  const fmt = d => MO[+d.slice(5, 7) - 1] + ' ' + d.slice(0, 4);

  /* ---------------- Takvim ---------------- */
  const pad = n => String(n).padStart(2, '0');
  const keyOf = (y, m, d) => y + '-' + pad(m + 1) + '-' + pad(d);
  const today = cal.dataset.today;
  const icons = (() => { try { return JSON.parse(cal.dataset.icons); } catch (_) { return {}; } })();
  const ico = cat => icons[cat] ? '<svg class="ce-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + icons[cat] + '</svg>' : '';
  const esc = s => String(s ?? '').replace(/[&<>"]/g, ch => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]));
  const dayName = key => { const [y, m, d] = key.split('-').map(Number); return d + ' ' + MONTHS[m - 1] + ' ' + DAYS[new Date(y, m - 1, d).getDay()]; };
  const els = {
    title: $('[data-cal-title]'), days: $('[data-cal-grid]'), summary: $('[data-cal-summary]'), note: $('[data-cal-note]'),
    pTitle: $('[data-panel-title]'), pAll: $('[data-panel-all]'), pList: $('[data-panel-list]'),
    pEmpty: $('[data-panel-empty]'), pEmptyText: $('[data-panel-empty-text]'), pEmptyAction: $('[data-panel-empty-action]'),
  };
  let calY = null, calM = null, selected = null, events = [], focusKey = null;

  const eventOf = c => ({
    card: c, start: c.dataset.date, end: c.dataset.end || c.dataset.date, cat: c.dataset.cat, st: c.dataset.status,
    title: $('h3', c).textContent.trim(),
  });
  const overlaps = (e, from, to) => e.start <= to && e.end >= from;
  const monthRange = () => [keyOf(calY, calM, 1), keyOf(calY, calM, new Date(calY, calM + 1, 0).getDate())];
  const monthEvents = () => { const [a, b] = monthRange(); return events.filter(e => overlaps(e, a, b)); };

  // İlk ay: bugünün ayında faaliyet varsa o ay; yoksa sıradaki faaliyetin ayı (geçmişte en yenisi).
  function pickMonth() {
    const set = k => { calY = +k.slice(0, 4); calM = +k.slice(5, 7) - 1; };
    set(today);
    if (monthEvents().length || !events.length) return;
    const next = events.find(e => e.start >= today);
    set(tab === 'gecmis' || !next ? events[events.length - 1].start : next.start);
  }

  function chip(e, key, dow) {
    const multi = e.start !== e.end;
    const cls = ['cal-ev', 'st-' + e.st];
    if (multi) {
      cls.push('is-multi');
      if (e.start < key && dow !== 0) cls.push('cont-l');
      if (e.end > key && dow !== 6) cls.push('cont-r');
    }
    // Çok günlü şeritte başlık yalnız parçanın başında yazılır (başlangıç günü, haftanın ilk günü ya da ayın 1'i).
    const head = !multi || e.start === key || dow === 0 || key.endsWith('-01');
    return '<span class="' + cls.join(' ') + '">' + (head ? ico(e.cat) + '<span class="t">' + esc(e.title) + '</span>' : '<span class="t is-cont" aria-hidden="true">' + esc(e.title) + '</span>') + '</span>';
  }

  function renderDays() {
    const [first] = monthRange();
    const offset = (new Date(calY, calM, 1).getDay() + 6) % 7;
    const count = new Date(calY, calM + 1, 0).getDate();
    const cells = Math.ceil((offset + count) / 7) * 7;
    els.title.textContent = MONTHS[calM] + ' ' + calY;
    els.days.setAttribute('aria-label', MONTHS[calM] + ' ' + calY + ' takvimi');
    if (!focusKey || !focusKey.startsWith(first.slice(0, 7))) focusKey = selected || (today.startsWith(first.slice(0, 7)) ? today : first);
    let html = '';
    for (let i = 0; i < cells; i++) {
      const d = i - offset + 1, dow = i % 7;
      const wk = dow >= 5 ? ' is-weekend' : '';
      if (d < 1 || d > count) { html += '<span class="cal-cell is-out' + wk + '" aria-hidden="true"></span>'; continue; }
      const key = keyOf(calY, calM, d);
      const list = events.filter(e => overlaps(e, key, key))
        .sort((a, b) => (b.start !== b.end) - (a.start !== a.end) || a.start.localeCompare(b.start) || a.title.localeCompare(b.title, 'tr'));
      const more = list.length - 2;
      const label = dayName(key) + (key === today ? ', bugün' : '') + ', ' + (list.length ? list.length + ' faaliyet: ' + list.map(e => e.title).join(', ') : 'faaliyet yok');
      html += '<button type="button" class="cal-cell' + wk + (key === today ? ' is-today' : '') + (list.length ? ' has-ev' : '') + (key === selected ? ' is-selected' : '') + '"'
        + ' data-day="' + key + '" aria-pressed="' + (key === selected) + '" aria-label="' + esc(label) + '" tabindex="' + (key === focusKey ? 0 : -1) + '">'
        + '<span class="cal-num" aria-hidden="true">' + d + '</span>'
        + (list.length ? '<span class="cal-evs" aria-hidden="true">' + list.slice(0, 2).map(e => chip(e, key, dow)).join('') + (more > 0 ? '<span class="cal-more">+' + more + ' faaliyet</span>' : '') + '</span>'
          + '<span class="cal-dots" aria-hidden="true">' + list.slice(0, 3).map(e => '<i class="st-' + e.st + '"></i>').join('') + (list.length > 3 ? '<b>' + list.length + '</b>' : '') + '</span>' : '')
        + '</button>';
    }
    els.days.innerHTML = html;
  }

  function cloneCard(c) {
    const n = c.cloneNode(true);
    n.hidden = false; n.style.order = ''; n.classList.add('is-compact', 'is-visible'); n.classList.remove('reveal');
    // Paylaş butonu social-share.js çalışınca görünür; kopya o andan önce alınmış olabilir.
    $$('[data-social-share]', n).forEach(b => { b.hidden = typeof HTMLDialogElement !== 'function'; });
    return n;
  }

  function nextEvent(after) { return events.find(e => e.start > after); }

  function renderPanel() {
    const list = selected ? events.filter(e => overlaps(e, selected, selected)) : monthEvents();
    els.pTitle.textContent = selected ? dayName(selected) : 'Bu ayki faaliyetler';
    els.pAll.hidden = !selected;
    els.pList.replaceChildren(...list.map(e => cloneCard(e.card)));
    els.pList.hidden = !list.length;
    els.pEmpty.hidden = !!list.length;
    if (!list.length) {
      if (selected) {
        els.pEmptyText.textContent = 'Bu tarihte faaliyet bulunmuyor.';
        els.pEmptyAction.textContent = 'Ayın tümünü göster';
        els.pEmptyAction.dataset.act = 'all'; els.pEmptyAction.hidden = false;
      } else {
        const nx = nextEvent(monthRange()[1]);
        els.pEmptyText.textContent = 'Bu ay faaliyet bulunmuyor.' + (nx ? '' : ' Filtrelere uyan sonraki faaliyet de yok.');
        els.pEmptyAction.textContent = 'Sonraki faaliyete git';
        els.pEmptyAction.dataset.act = 'next'; els.pEmptyAction.hidden = !nx;
      }
    }
  }

  function renderSummary() {
    const n = monthEvents().length;
    els.summary.innerHTML = 'Toplam <strong>' + events.length + '</strong> ' + (tab === 'gecmis' ? 'geçmiş' : 'yaklaşan') + ' faaliyet'
      + '<span class="sep" aria-hidden="true">·</span>' + MONTHS[calM] + ' ' + calY + ': <strong>' + n + '</strong> faaliyet';
  }

  function renderCal(auto) {
    // Filtre değişince görüntülenen ayda sonuç kalmadıysa uygun aya geçilir; ay değişirse gün seçimi kalkar.
    if (auto && (calY === null || !monthEvents().length)) { pickMonth(); selected = null; }
    renderDays(); renderPanel(); renderSummary();
  }

  function setMonth(y, m, sel) {
    calY = y; calM = m; selected = sel || null; focusKey = sel || null;
    els.note.hidden = true;
    renderCal(false);
  }
  const shiftMonth = n => { const d = new Date(calY, calM + n, 1); setMonth(d.getFullYear(), d.getMonth()); };
  const goDay = (key, focus) => { setMonth(+key.slice(0, 4), +key.slice(5, 7) - 1, key); if (focus) $('[data-day="' + key + '"]', els.days)?.focus(); };

  $('[data-cal-prev]').addEventListener('click', () => shiftMonth(-1));
  $('[data-cal-next]').addEventListener('click', () => shiftMonth(1));
  $('[data-cal-today]').addEventListener('click', () => goDay(today, true));
  $('[data-cal-nextev]').addEventListener('click', () => {
    // Seçili gün varsa ondan, yoksa bugünden sonraki ilk faaliyet (mevcut filtrelerle).
    const nx = selected ? events.find(e => e.start > selected) : events.find(e => e.start >= today);
    if (nx) { goDay(nx.start, true); return; }
    els.note.textContent = 'Seçili filtrelere uyan sonraki faaliyet yok.';
    els.note.hidden = false;
  });
  // Buton gizlendiği için odak panel başlığına taşınır (tabindex="-1").
  const showMonth = () => { selected = null; renderCal(false); els.pTitle.focus(); };
  els.pAll.addEventListener('click', showMonth);
  els.pEmptyAction.addEventListener('click', () => {
    if (els.pEmptyAction.dataset.act === 'all') { showMonth(); return; }
    const nx = nextEvent(monthRange()[1]); if (nx) goDay(nx.start, true);
  });
  els.days.addEventListener('click', e => {
    const b = e.target.closest('[data-day]'); if (!b) return;
    const key = b.dataset.day;
    selected = selected === key ? null : key; focusKey = key; els.note.hidden = true;
    renderCal(false);
    $('[data-day="' + key + '"]', els.days)?.focus();
  });
  // Ok tuşları günler arasında gezer (aynı ay içinde); Enter/Boşluk seçer.
  els.days.addEventListener('keydown', e => {
    const step = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
    const b = e.target.closest('[data-day]'); if (!step || !b) return;
    const btns = $$('[data-day]', els.days), i = btns.indexOf(b) + step;
    if (i < 0 || i >= btns.length) return;
    e.preventDefault(); b.tabIndex = -1; btns[i].tabIndex = 0; btns[i].focus(); focusKey = btns[i].dataset.day;
  });

  /* ---------------- Liste ---------------- */
  viewBtns.forEach(b => b.addEventListener('click', () => { view = b.dataset.view; store.set('kdrk-faaliyet-view', view); render(); }));

  function render() {
    const q = term.toLocaleLowerCase('tr').trim();
    const base = cards.filter(c => inTab(c) && (!q || textOf(c).includes(q)));
    pills.forEach(p => {
      const k = p.dataset.filter, on = k === filter;
      $('.count', p).textContent = base.filter(c => k === 'all' || c.dataset.cat === k).length;
      p.classList.toggle('is-active', on); p.setAttribute('aria-pressed', on);
    });
    tabs.forEach(t => {
      const on = t.dataset.tab === tab;
      t.classList.toggle('is-active', on); t.setAttribute('aria-pressed', on);
      $('.count', t).textContent = cards.filter(c => isPast(c) === (t.dataset.tab === 'gecmis')).length;
    });
    const vis = base.filter(c => filter === 'all' || c.dataset.cat === filter);
    vis.sort((a, b) => sort === 'date-asc' ? a.dataset.date.localeCompare(b.dataset.date)
      : sort === 'date-desc' ? b.dataset.date.localeCompare(a.dataset.date)
      : sort === 'difficulty' ? a.dataset.difficulty - b.dataset.difficulty : b.dataset.fill - a.dataset.fill);
    cards.forEach(c => { c.hidden = true; });
    vis.forEach((c, i) => { c.hidden = false; c.style.order = i; c.classList.add('is-visible'); });

    empty.hidden = vis.length > 0;
    grid.hidden = !vis.length || view === 'takvim';
    cal.hidden = !vis.length || view !== 'takvim';
    viewBtns.forEach(b => { const on = b.dataset.view === view; b.classList.toggle('is-active', on); b.setAttribute('aria-pressed', on); });
    events = vis.map(eventOf).sort((a, b) => a.start.localeCompare(b.start) || a.title.localeCompare(b.title, 'tr'));
    if (view === 'takvim' && vis.length) renderCal(true);

    $('[data-empty-title]').textContent = tab === 'gecmis' ? 'Bu seçimde geçmiş faaliyet kaydı yok.' : 'Bu seçimde yaklaşan faaliyet yok.';
    $('[data-result-count]').textContent = vis.length;
    $('[data-result-text]').textContent = tab === 'gecmis' ? 'GEÇMİŞ FAALİYET' : 'YAKLAŞAN FAALİYET';
    const all = cards.filter(inTab).map(c => c.dataset.date).sort();
    $('[data-header-meta]').textContent = all.length ? all.length + (tab === 'gecmis' ? ' GEÇMİŞ' : ' YAKLAŞAN') + ' · ' + fmt(all[0]) + ' → ' + fmt(all[all.length - 1]) : '';
    $$('[data-sort-by]').forEach(b => b.classList.toggle('is-selected', b.dataset.sortBy === sort));
    $('[data-sort-label]').textContent = $('[data-sort-by="' + sort + '"]').textContent;
    const u = new URLSearchParams();
    if (tab === 'gecmis') u.set('tab', 'gecmis');
    if (filter !== 'all') u.set('cat', filter);
    if (view === 'takvim') u.set('view', 'takvim');
    history.replaceState(null, '', location.pathname + (u.toString() ? '?' + u : ''));
  }

  pills.forEach(p => p.addEventListener('click', () => { filter = p.dataset.filter; render(); }));
  tabs.forEach(t => t.addEventListener('click', () => { tab = t.dataset.tab; sort = tab === 'gecmis' ? 'date-desc' : 'date-asc'; calY = null; selected = null; render(); }));
  let timer;
  search.addEventListener('input', e => { clearTimeout(timer); timer = setTimeout(() => { term = e.target.value; render(); }, 150); });

  const dd = $('[data-sort]'), trig = $('[data-sort-trigger]', dd);
  const setDD = v => { dd.classList.toggle('is-open', v); trig.setAttribute('aria-expanded', v); };
  trig.addEventListener('click', e => { e.stopPropagation(); setDD(!dd.classList.contains('is-open')); });
  document.addEventListener('click', () => setDD(false));
  dd.addEventListener('keydown', e => { if (e.key === 'Escape') { setDD(false); trig.focus(); } });
  $$('[data-sort-by]', dd).forEach(b => b.addEventListener('click', () => { sort = b.dataset.sortBy; setDD(false); trig.focus(); render(); }));
  $('[data-clear-filters]').addEventListener('click', () => { filter = 'all'; term = ''; search.value = ''; render(); });

  render();
})();
