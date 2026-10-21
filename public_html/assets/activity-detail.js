/* Faaliyet detayı: başvuru modalı + form, paylaş, lightbox.
   Form JS olmadan da çalışır (normal POST); JS varsa fetch ile gönderilir ve sayfa yenilenmez. */
(function () {
  const trap = (c, e) => window.kdrkTrapFocus && window.kdrkTrapFocus(c, e);

  /* ---------- Başvuru modalı ---------- */
  const backdrop = document.querySelector('[data-modal]');
  if (backdrop) {
    const modal = backdrop.querySelector('.modal');
    const form = document.getElementById('apply-form');
    const vForm = backdrop.querySelector('[data-modal-form]');
    const vOk = backdrop.querySelector('[data-modal-success]');
    const vErr = backdrop.querySelector('[data-modal-error]');
    const alertBox = backdrop.querySelector('[data-form-alert]');
    const submit = form.querySelector('[data-submit]');
    const submitLabel = form.querySelector('[data-submit-label]');
    const submitText = submitLabel.textContent;
    const applyBar = document.querySelector('[data-apply-bar]');
    let lastFocus = null;

    const show = v => [vForm, vOk, vErr].forEach(x => { x.hidden = x !== v; });
    function openModal() {
      lastFocus = document.activeElement;
      show(vForm);
      backdrop.classList.add('is-open');
      backdrop.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      setTimeout(() => form.querySelector('[name="name"]').focus(), 150);
    }
    function closeModal() {
      if (!backdrop.classList.contains('is-open')) return;
      backdrop.classList.remove('is-open');
      backdrop.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (lastFocus) lastFocus.focus();
    }
    document.querySelectorAll('[data-open-apply]').forEach(b => b.addEventListener('click', openModal));
    backdrop.querySelectorAll('[data-modal-close]').forEach(b => b.addEventListener('click', closeModal));
    backdrop.addEventListener('click', e => { if (e.target === backdrop) closeModal(); });
    backdrop.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); else trap(modal, e); });
    backdrop.querySelector('[data-retry]').addEventListener('click', () => { show(vForm); submit.focus(); });
    // Sunucu doğrulaması hatayla döndüyse (JS kapalı akış) modal açık gelir
    if (backdrop.classList.contains('is-open')) {
      document.body.style.overflow = 'hidden';
      const first = form.querySelector('.is-error');
      if (first) first.focus();
    } else if (location.hash === '#basvuru') {
      // Anasayfa slaytındaki "Faaliyete Başvur" buraya gelir; JS kapalıysa yalnız başvuru kutusuna kayar
      openModal();
    }

    /* Girdi biçimlendirme */
    const field = name => form.querySelector('[name="' + name + '"]');
    field('tc').addEventListener('input', e => { e.target.value = e.target.value.replace(/\D/g, '').slice(0, 11); });
    const formatPhone = v => {
      const d = v.replace(/\D/g, '').replace(/^0/, '').slice(0, 10);
      return [d.slice(0, 3), d.slice(3, 6), d.slice(6, 8), d.slice(8, 10)].filter(Boolean).join(' ');
    };
    ['phone', 'emer_phone'].forEach(n => field(n).addEventListener('input', e => { e.target.value = formatPhone(e.target.value); }));

    /* Doğrulama — sunucudaki kurallarla aynı (ApplicationController) */
    const phoneOk = v => /^5\d{9}$/.test(v.replace(/\D/g, '').replace(/^0/, ''));
    const tcOk = v => {
      if (!/^[1-9]\d{10}$/.test(v)) return false;
      const d = v.split('').map(Number);
      const d10 = ((d[0] + d[2] + d[4] + d[6] + d[8]) * 7 - (d[1] + d[3] + d[5] + d[7])) % 10;
      const d11 = d.slice(0, 10).reduce((a, b) => a + b, 0) % 10;
      return ((d10 + 10) % 10) === d[9] && d11 === d[10];
    };
    const rules = [
      ['name', v => v.trim().length >= 3, 'Ad ve soyadını yaz.'],
      ['tc', tcOk, 'Geçerli bir TC Kimlik No yaz (11 hane).'],
      ['phone', phoneOk, '5 ile başlayan 10 haneli telefon numarası yaz.'],
      ['email', v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()), 'Geçerli bir e-posta adresi yaz (ör. ad@ornek.com).'],
      ['emer_name', v => v.trim().length >= 3, 'Acil durumda aranacak kişinin adını yaz.'],
      ['emer_phone', phoneOk, 'Acil durum için 5 ile başlayan 10 haneli numara yaz.'],
    ];
    function setErr(name, msg) {
      const el = field(name);
      const box = document.getElementById('e-' + name);
      el.classList.toggle('is-error', !!msg);
      el.setAttribute('aria-invalid', msg ? 'true' : 'false');
      if (box) { box.textContent = msg || ''; box.hidden = !msg; }
    }
    function showErrors(errs) {
      const names = Object.keys(errs);
      names.forEach(n => setErr(n, errs[n]));
      alertBox.innerHTML = '';
      const strong = document.createElement('strong');
      strong.textContent = names.length + ' alanı düzeltmen gerekiyor:';
      const ul = document.createElement('ul');
      names.forEach(n => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = '#'; a.textContent = errs[n];
        a.addEventListener('click', ev => { ev.preventDefault(); field(n).focus(); });
        li.appendChild(a); ul.appendChild(li);
      });
      alertBox.append(strong, ul);
      alertBox.hidden = false;
      field(names[0]).focus();
    }
    form.addEventListener('input', e => { if (e.target.classList.contains('is-error')) setErr(e.target.name, ''); });
    field('kvkk').addEventListener('change', e => { if (e.target.checked) setErr('kvkk', ''); });

    function busy(on) {
      submit.disabled = on;
      submit.classList.toggle('is-loading', on);
      submitLabel.textContent = on ? 'Gönderiliyor…' : submitText;
    }

    form.addEventListener('submit', async e => {
      e.preventDefault();
      const errs = {};
      rules.forEach(([n, ok, msg]) => { if (!ok(field(n).value)) errs[n] = msg; else setErr(n, ''); });
      if (!field('kvkk').checked) errs.kvkk = 'Devam etmek için KVKK onayını işaretle.';
      if (Object.keys(errs).length) { showErrors(errs); return; }

      alertBox.hidden = true;
      busy(true);
      try {
        const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
        const data = await res.json().catch(() => ({}));
        if (res.ok && data.ok) {
          form.reset();
          if (data.message) vOk.querySelector('[data-success-title]').textContent = data.message;
          show(vOk);
          vOk.querySelector('button').focus();
          if (applyBar) applyBar.hidden = true;
          document.querySelectorAll('[data-open-apply]').forEach(b => { b.disabled = true; });
        } else if (res.status === 422 && data.errors) {
          showErrors(data.errors);
        } else {
          if (data.message) vErr.querySelector('[data-error-text]').textContent = data.message;
          show(vErr);
          vErr.querySelector('[data-retry]').focus();
        }
      } catch (_) {
        show(vErr);
        vErr.querySelector('[data-retry]').focus();
      } finally {
        busy(false);
      }
    });
  }

  /* ---------- Paylaş ---------- */
  const copyBtn = document.querySelector('[data-copy-link]');
  if (copyBtn) {
    const lab = copyBtn.querySelector('[data-copy-label]');
    copyBtn.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(location.href); lab.textContent = 'Kopyalandı'; }
      catch (_) { lab.textContent = 'Kopyalanamadı'; }
      setTimeout(() => { lab.textContent = 'Linki kopyala'; }, 1800);
    });
  }
  const ns = document.querySelector('[data-native-share]');
  if (ns && navigator.share) {
    ns.hidden = false;
    ns.addEventListener('click', () => navigator.share({ title: document.title, url: location.href }).catch(() => {}));
  }

  /* ---------- Lightbox ---------- */
  const lb = document.querySelector('[data-lightbox-modal]');
  if (lb) {
    const img = lb.querySelector('[data-lightbox-img]');
    const cap = lb.querySelector('[data-lb-cap]');
    const tiles = [...document.querySelectorAll('[data-lightbox]')];
    const title = document.querySelector('h1') ? document.querySelector('h1').textContent : '';
    let idx = 0, lbFocus = null;
    const lbShow = i => {
      idx = (i + tiles.length) % tiles.length;
      img.src = tiles[idx].dataset.lightbox;
      img.alt = title + ', fotoğraf ' + (idx + 1);
      cap.textContent = (idx + 1) + ' / ' + tiles.length;
    };
    const lbOpen = i => {
      lbFocus = document.activeElement; lbShow(i);
      lb.classList.add('is-open'); lb.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      lb.querySelector('[data-lb-close]').focus();
    };
    const lbClose = () => {
      lb.classList.remove('is-open'); lb.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (lbFocus) lbFocus.focus();
    };
    tiles.forEach((t, i) => t.addEventListener('click', () => lbOpen(i)));
    lb.querySelector('[data-lb-close]').addEventListener('click', lbClose);
    lb.querySelector('[data-lb-prev]').addEventListener('click', () => lbShow(idx - 1));
    lb.querySelector('[data-lb-next]').addEventListener('click', () => lbShow(idx + 1));
    lb.addEventListener('click', e => { if (e.target === lb) lbClose(); });
    lb.addEventListener('keydown', e => {
      if (e.key === 'Escape') lbClose();
      else if (e.key === 'ArrowLeft') lbShow(idx - 1);
      else if (e.key === 'ArrowRight') lbShow(idx + 1);
      else trap(lb, e);
    });
    let sx = null;
    lb.addEventListener('touchstart', e => { sx = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', e => {
      if (sx === null) return;
      const dx = e.changedTouches[0].clientX - sx;
      if (Math.abs(dx) > 50) lbShow(idx + (dx < 0 ? 1 : -1));
      sx = null;
    });
  }
})();

/* Konum haritası (Leaflet + OpenStreetMap). Harita yalnız görünür olunca yüklenir; JS kapalıyken "Haritada aç" bağlantısı kalır. */
(function () {
  const el = document.querySelector('[data-detail-map]');
  if (!el || !window.L) return;
  const init = () => {
    const ll = [parseFloat(el.dataset.lat), parseFloat(el.dataset.lng)];
    const map = L.map(el, { scrollWheelZoom: false, dragging: !L.Browser.mobile, tap: false }).setView(ll, 13);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '© OpenStreetMap katkıcıları' }).addTo(map);
    L.marker(ll, { keyboard: false }).addTo(map);
  };
  if (!('IntersectionObserver' in window)) { init(); return; }
  const io = new IntersectionObserver(entries => { if (entries[0].isIntersecting) { io.disconnect(); init(); } }, { rootMargin: '200px' });
  io.observe(el);
})();
