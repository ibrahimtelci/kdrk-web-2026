/* KDRK Site Behaviors */
/* Kulüp rakamları: TEK KAYNAK. Sayfalarda data-stat="anahtar" ile kullanılır. */
window.KDRK_STATS = { kurulus: 1998, zirve: 500, yuruyus: 1000, aramaKurtarma: 25 };
window.KDRK_STATS.yil = new Date().getFullYear() - window.KDRK_STATS.kurulus;
(function() {
  const root = document.documentElement;
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select,textarea,summary,[tabindex]:not([tabindex="-1"])';

  /* Ortak: odak tuzağı */
  function trapFocus(container, e) {
    if (e.key !== 'Tab') return;
    const f = [...container.querySelectorAll(FOCUSABLE)].filter(el => el.offsetParent !== null);
    if (!f.length) return;
    const first = f[0], last = f[f.length - 1];
    if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
  }
  window.kdrkTrapFocus = trapFocus;

  /* Tema */
  const THEME_KEY = 'kdrk-theme';
  function setTheme(t) {
    root.setAttribute('data-theme', t);
    try { localStorage.setItem(THEME_KEY, t); } catch(e) {}
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
      btn.setAttribute('aria-label', t === 'dark' ? 'Açık temaya geç' : 'Koyu temaya geç');
      const sun = btn.querySelector('.icon-sun'), moon = btn.querySelector('.icon-moon');
      if (sun && moon) { sun.style.display = t === 'dark' ? 'none' : 'block'; moon.style.display = t === 'dark' ? 'block' : 'none'; }
    });
  }
  function initTheme() {
    let saved = 'dark';
    try { saved = localStorage.getItem(THEME_KEY) || 'dark'; } catch(e) {}
    setTheme(saved);
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => btn.addEventListener('click', () => {
      setTheme((root.getAttribute('data-theme') || 'dark') === 'dark' ? 'light' : 'dark');
    }));
  }

  /* Nav + mobil drawer */
  function initNav() {
    const nav = document.querySelector('[data-nav]');
    if (!nav) return;
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 24);
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const burger = nav.querySelector('[data-burger]');
    const drawer = document.querySelector('[data-drawer]');
    if (!burger || !drawer) return;
    let lastFocus = null;
    function open() {
      lastFocus = document.activeElement;
      drawer.classList.add('is-open');
      drawer.removeAttribute('inert');
      drawer.setAttribute('aria-hidden', 'false');
      burger.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
      setTimeout(() => { const c = drawer.querySelector('[data-drawer-close]'); if (c) c.focus(); }, 50);
    }
    function close() {
      drawer.classList.remove('is-open');
      drawer.setAttribute('inert', '');
      drawer.setAttribute('aria-hidden', 'true');
      burger.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
      if (lastFocus) lastFocus.focus();
    }
    drawer.setAttribute('inert', '');
    burger.addEventListener('click', open);
    drawer.querySelectorAll('[data-drawer-close]').forEach(b => b.addEventListener('click', close));
    drawer.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
    drawer.addEventListener('keydown', e => { if (e.key === 'Escape') close(); else trapFocus(drawer, e); });
    window.addEventListener('resize', () => { if (window.innerWidth >= 1180 && drawer.classList.contains('is-open')) close(); });
  }

  /* Dropdown */
  function initDropdowns() {
    const all = [...document.querySelectorAll('[data-dropdown]')];
    const setOpen = (d, v) => {
      d.classList.toggle('is-open', v);
      const t = d.querySelector('[data-dropdown-trigger]');
      if (t) t.setAttribute('aria-expanded', v ? 'true' : 'false');
    };
    all.forEach(d => {
      let timeout;
      const trigger = d.querySelector('[data-dropdown-trigger]');
      if (window.matchMedia('(hover: hover)').matches) {
        d.addEventListener('mouseenter', () => { clearTimeout(timeout); all.forEach(o => o !== d && setOpen(o, false)); setOpen(d, true); });
        d.addEventListener('mouseleave', () => { timeout = setTimeout(() => setOpen(d, false), 120); });
      }
      if (trigger) trigger.addEventListener('click', e => { e.preventDefault(); const v = !d.classList.contains('is-open'); all.forEach(o => setOpen(o, false)); setOpen(d, v); });
      d.addEventListener('keydown', e => { if (e.key === 'Escape' && d.classList.contains('is-open')) { setOpen(d, false); trigger && trigger.focus(); } });
      d.addEventListener('focusout', e => { if (!d.contains(e.relatedTarget)) setOpen(d, false); });
    });
    document.addEventListener('click', e => all.forEach(d => { if (!d.contains(e.target)) setOpen(d, false); }));
  }

  /* Reveal */
  function initReveal() {
    const els = document.querySelectorAll('.reveal');
    if (reduceMotion || !('IntersectionObserver' in window)) { els.forEach(e => e.classList.add('is-visible')); return; }
    const io = new IntersectionObserver(entries => entries.forEach(e => {
      if (e.isIntersecting) { e.target.classList.add('is-visible'); io.unobserve(e.target); }
    }), { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(el => io.observe(el));
  }

  /* Kulüp rakamlarını uygula */
  function applyStats() {
    document.querySelectorAll('[data-stat]').forEach(el => {
      const v = window.KDRK_STATS[el.getAttribute('data-stat')];
      if (v == null) return;
      if (el.hasAttribute('data-odometer')) el.setAttribute('data-odometer', v); else el.textContent = v;
    });
  }

  /* Sayaç */
  function initOdometer() {
    const els = document.querySelectorAll('[data-odometer]');
    if (reduceMotion || !('IntersectionObserver' in window)) { els.forEach(el => el.textContent = el.getAttribute('data-odometer')); return; }
    const io = new IntersectionObserver(entries => entries.forEach(e => {
      if (!e.isIntersecting) return;
      const el = e.target, target = parseInt(el.getAttribute('data-odometer'), 10) || 0, start = performance.now();
      (function frame(now) {
        const t = Math.min(1, (now - start) / 1200);
        el.textContent = Math.round(target * (1 - Math.pow(1 - t, 3)));
        if (t < 1) requestAnimationFrame(frame);
      })(start);
      io.unobserve(el);
    }), { threshold: 0.3 });
    els.forEach(el => { el.textContent = '0'; io.observe(el); });
  }

  /* Hero slider */
  function initHeroSlider() {
    const slider = document.querySelector('[data-hero-slider]');
    if (!slider) return;
    const slides = slider.querySelectorAll('[data-slide]');
    const dots = slider.querySelectorAll('[data-dot]');
    const counterCur = slider.querySelector('[data-counter-current]');
    const counterTotal = slider.querySelector('[data-counter-total]');
    const pauseBtn = slider.querySelector('[data-pause]');
    let index = 0, timer = null, hoverPaused = false, userPaused = reduceMotion;
    const duration = parseInt(slider.getAttribute('data-duration'), 10) || 7000;
    if (counterTotal) counterTotal.textContent = String(slides.length).padStart(2, '0');
    const isPaused = () => userPaused || hoverPaused;

    function runProgress(ms) {
      dots.forEach((d, k) => {
        const p = d.querySelector('.dot-progress'); if (!p) return;
        p.style.transition = 'none'; p.style.width = k === index && isPaused() ? '100%' : '0%';
        void p.offsetWidth;
        if (k === index && !isPaused()) { p.style.transition = `width ${ms}ms linear`; p.style.width = '100%'; }
      });
    }
    function schedule(ms) { clearTimeout(timer); if (!isPaused()) timer = setTimeout(() => go(index + 1), ms); }
    function go(i) {
      index = (i + slides.length) % slides.length;
      slides.forEach((s, k) => {
        const on = k === index;
        s.classList.toggle('is-active', on);
        s.setAttribute('aria-hidden', on ? 'false' : 'true');
        if (on) s.removeAttribute('inert'); else s.setAttribute('inert', '');
      });
      dots.forEach((d, k) => { d.classList.toggle('is-active', k === index); d.setAttribute('aria-current', k === index ? 'true' : 'false'); });
      if (counterCur) counterCur.textContent = String(index + 1).padStart(2, '0');
      runProgress(duration); schedule(duration);
    }
    function updatePauseBtn() {
      if (!pauseBtn) return;
      pauseBtn.setAttribute('aria-pressed', userPaused ? 'true' : 'false');
      pauseBtn.setAttribute('aria-label', userPaused ? 'Otomatik geçişi başlat' : 'Otomatik geçişi durdur');
      pauseBtn.classList.toggle('is-paused', userPaused);
    }
    slider.querySelector('[data-prev]')?.addEventListener('click', () => go(index - 1));
    slider.querySelector('[data-next]')?.addEventListener('click', () => go(index + 1));
    dots.forEach((d, i) => d.addEventListener('click', () => go(i)));
    if (pauseBtn) pauseBtn.addEventListener('click', () => { userPaused = !userPaused; updatePauseBtn(); runProgress(duration); schedule(duration); });
    slider.addEventListener('keydown', e => {
      if (e.key === 'ArrowLeft') go(index - 1);
      if (e.key === 'ArrowRight') go(index + 1);
    });
    if (window.matchMedia('(hover: hover)').matches) {
      slider.addEventListener('mouseenter', () => { hoverPaused = true; clearTimeout(timer); });
      slider.addEventListener('mouseleave', () => { hoverPaused = false; runProgress(duration); schedule(duration); });
    }
    slider.addEventListener('focusin', () => { hoverPaused = true; clearTimeout(timer); });
    slider.addEventListener('focusout', e => { if (!slider.contains(e.relatedTarget)) { hoverPaused = false; runProgress(duration); schedule(duration); } });
    let touchStart = null;
    slider.addEventListener('touchstart', e => { touchStart = e.touches[0].clientX; }, { passive: true });
    slider.addEventListener('touchend', e => {
      if (touchStart === null) return;
      const dx = e.changedTouches[0].clientX - touchStart;
      if (Math.abs(dx) > 50) go(index + (dx < 0 ? 1 : -1));
      touchStart = null;
    });
    updatePauseBtn();
    go(0);
  }

  document.addEventListener('DOMContentLoaded', () => {
    initTheme(); initNav(); initDropdowns(); initReveal(); applyStats(); initOdometer(); initHeroSlider();
  });
})();
