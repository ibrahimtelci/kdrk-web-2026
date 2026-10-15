/* KDRK Galeri — sayfa JS kapalıyken de çalışır; bu dosya yalnızca kolaylık katar. */
(function () {
  /* Liste: yıl/kategori seçilince filtre formu gönderilir */
  const form = document.querySelector('[data-gallery-filter]');
  if (form) {
    form.querySelectorAll('select').forEach(s => s.addEventListener('change', () => form.submit()));
    const btn = form.querySelector('[data-filter-submit]');
    if (btn) btn.hidden = true;
  }

  /* Albüm: lightbox */
  const lb = document.querySelector('[data-lightbox]');
  const dataEl = document.querySelector('[data-lb-data]');
  if (!lb || !dataEl) return;
  let photos;
  try { photos = JSON.parse(dataEl.textContent); } catch (_) { return; }
  if (!photos.length) return;

  const $ = s => lb.querySelector(s);
  const img = $('[data-lb-img]'), cur = $('[data-lb-current]'), cap = $('[data-lb-caption]');
  const dl = $('[data-lb-download]'), closeBtn = $('[data-lb-close]');
  const pad = n => String(n).padStart(2, '0');
  let idx = 0, lastFocus = null;

  function show(i) {
    idx = (i + photos.length) % photos.length;
    const p = photos[idx];
    img.classList.remove('is-loaded');
    img.onload = () => img.classList.add('is-loaded');
    if (p.srcset) { img.srcset = p.srcset; img.sizes = '100vw'; } else { img.removeAttribute('srcset'); }
    img.src = p.src;
    img.alt = p.caption || '';
    cur.textContent = pad(idx + 1);
    cap.textContent = [p.caption, p.credit ? '© ' + p.credit : ''].filter(Boolean).join(' · ');
    dl.href = p.full;
    // Komşu fotoğrafı önceden yükle
    const next = photos[(idx + 1) % photos.length];
    if (next) { const pre = new Image(); pre.src = next.src; }
  }
  function open(i) {
    lastFocus = document.activeElement;
    lb.hidden = false;
    lb.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    show(i);
    requestAnimationFrame(() => { lb.classList.add('is-open'); closeBtn.focus(); });
  }
  function close() {
    lb.classList.remove('is-open');
    lb.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    setTimeout(() => { lb.hidden = true; }, 220);
    if (lastFocus) lastFocus.focus();
  }

  document.querySelectorAll('[data-lb-open]').forEach(a => a.addEventListener('click', e => {
    if (e.metaKey || e.ctrlKey || e.shiftKey) return; // yeni sekmede açmak isteyene dokunma
    e.preventDefault();
    open(+a.dataset.lbOpen);
  }));
  $('[data-lb-prev]').addEventListener('click', () => show(idx - 1));
  $('[data-lb-next]').addEventListener('click', () => show(idx + 1));
  closeBtn.addEventListener('click', close);
  lb.addEventListener('click', e => { if (e.target === lb || e.target.classList.contains('lb-img-wrap')) close(); });

  document.addEventListener('keydown', e => {
    if (lb.hidden) return;
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowLeft') show(idx - 1);
    else if (e.key === 'ArrowRight') show(idx + 1);
    else if (e.key === 'Tab') {
      // Odak lightbox içinde kalır
      const f = [...lb.querySelectorAll('button, a[href]')].filter(el => !el.hidden);
      const first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  // Dokunmatik kaydırma
  const stage = $('.lb-stage');
  let x0 = 0;
  stage.addEventListener('touchstart', e => { x0 = e.touches[0].clientX; }, { passive: true });
  stage.addEventListener('touchend', e => {
    const dx = e.changedTouches[0].clientX - x0;
    if (Math.abs(dx) > 50) show(dx < 0 ? idx + 1 : idx - 1);
  }, { passive: true });
})();
