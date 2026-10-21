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
    if (e.defaultPrevented) return;
    const btn = e.submitter && e.submitter.matches('[data-busy]') ? e.submitter : e.target.querySelector('[data-busy]');
    if (btn) { setTimeout(() => { btn.disabled = true; btn.lastChild.textContent = btn.dataset.busy; }, 0); }
  });

  /* Kapak: seçilince kırpma penceresi açılır (sürükle, yakınlaştır).
     Seçilen alan 0–1 oranlarıyla gizli alana yazılır ("x,y,w,h"); sunucu özgün görselden kırpar (Image::parseCrop).
     JS kapalıysa ya da <dialog> yoksa görsel kırpılmadan yüklenir. */
  const coverInput = $('[data-cover-input]'), coverPrev = $('[data-cover-prev]'), cropValue = $('[data-crop-value]');
  if (coverInput && coverPrev) {
    const prevBg = coverPrev.style.backgroundImage;
    const reset = () => { coverInput.value = ''; if (cropValue) cropValue.value = ''; coverPrev.style.backgroundImage = prevBg; coverInput.dispatchEvent(new Event('change', { bubbles: true })); };
    coverInput.addEventListener('change', () => {
      const f = coverInput.files[0];
      if (!f) return;
      if (f.size > 15 * 1024 * 1024) { alert('Görsel en fazla 15 MB olabilir.'); reset(); return; }
      const url = URL.createObjectURL(f);
      if (!cropValue || typeof HTMLDialogElement !== 'function') { coverPrev.style.backgroundImage = 'url(' + url + ')'; return; }
      openCropper(url, coverInput.dataset.cropRatio, coverInput.dataset.cropLabel, (crop, preview) => {
        cropValue.value = crop;
        coverPrev.style.backgroundImage = 'url(' + preview + ')';
        coverInput.dispatchEvent(new CustomEvent('form:changed', { bubbles: true }));
      }, reset);
    });
  }

  function openCropper(url, ratioText, label, onApply, onCancel) {
    const [rw, rh] = (ratioText || '16/10').split('/').map(Number);
    // Anasayfa slaytı tam ekrandır; telefonda (≈390×844) görselin yalnız ortadaki dikey şeridi görünür
    const portrait = rw === 4 && rh === 5;
    const phone = Math.min(100, 100 * (390 / 844) / (rw / rh)).toFixed(1);
    const esc = s => String(s || '').replace(/[&<>"]/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
    const dlg = document.createElement('dialog');
    dlg.className = 'crop-dialog' + (portrait ? ' crop-dialog-portrait' : '');
    dlg.setAttribute('aria-labelledby', 'crop-title');
    dlg.innerHTML =
      '<div class="crop-head"><h2 id="crop-title">Kapağı ayarla</h2><span class="muted">' + esc(label) + '</span></div>' +
      '<div class="crop-stage" tabindex="0" role="application" aria-label="Kırpma alanı. Ok tuşlarıyla kaydır, artı ve eksi ile yakınlaştır." style="aspect-ratio:' + rw + '/' + rh + '">' +
        '<img alt="" draggable="false"><span class="crop-grid" aria-hidden="true"></span>' +
        (portrait
          ? '<span class="crop-card-guide" aria-hidden="true"><span>Yatay site kartı · 16:10</span></span><span class="crop-caption-guide" aria-hidden="true"><span>Gönderi başlığı için boşluk bırak</span></span>'
          : '<span class="crop-phone" style="width:' + phone + '%" aria-hidden="true"><span>Telefonda slayt</span></span>') + '</div>' +
      '<div class="crop-zoom"><label for="crop-zoom">Yakınlaştır</label><input id="crop-zoom" type="range" min="1" max="4" step="0.01" value="1"></div>' +
      '<p class="f-hint">Fotoğrafı sürükleyerek kaydır; kaydırıcı, fare tekerleği ya da iki parmakla yakınlaştır. Çerçevenin içi kapakta görünür. ' +
        (portrait
          ? 'Çerçevenin tamamı 4:5 dikey kapaktır. Kesik çizgiler yatay site kartında görünen orta alanı gösterir. Ana konuyu bu alanda, alt kısmı başlık için sade tut. Kılavuzlar fotoğrafa eklenmez.'
          : 'Anasayfa slaytı ekranı kapladığı için telefonda yalnız kesik çizgili alan görünür; önemli kısmı oraya getir.') + '</p><p class="f-hint" data-crop-quality role="status"></p>' +
      '<div class="crop-foot"><button type="button" class="btn btn-ghost" data-crop-reset>Sıfırla</button><div class="tb-spacer"></div>' +
        '<button type="button" class="btn btn-ghost" data-crop-cancel>Vazgeç</button><button type="button" class="btn btn-primary" data-crop-apply>Uygula</button></div>';
    document.body.appendChild(dlg);
    const stage = $('.crop-stage', dlg), img = $('img', dlg), zoomIn = $('#crop-zoom', dlg);
    let nw = 0, nh = 0, sw = 0, sh = 0, base = 1, zoom = 1, ox = 0, oy = 0;
    const scale = () => base * zoom;
    const clamp = () => {
      ox = Math.min(0, Math.max(sw - nw * scale(), ox));
      oy = Math.min(0, Math.max(sh - nh * scale(), oy));
    };
    const draw = () => {
      clamp();
      img.style.width = (nw * scale()) + 'px';
      img.style.transform = 'translate(' + ox + 'px,' + oy + 'px)';
      zoomIn.value = zoom;
      if (portrait) {
        const w = Math.round(sw / scale()), h = Math.round(sh / scale());
        $('[data-crop-quality]', dlg).textContent = 'Seçilen alan: ' + w + ' × ' + h + ' piksel.' + (w < 1080 || h < 1350 ? ' Daha net bir gönderi için daha büyük fotoğraf seç veya yakınlaştırmayı azalt.' : ' Dikey gönderi için çözünürlük yeterli.');
      }
    };
    const zoomAt = (z, cx, cy) => {
      const s0 = scale(), px = (cx - ox) / s0, py = (cy - oy) / s0;
      zoom = Math.min(4, Math.max(1, z));
      ox = cx - px * scale();
      oy = cy - py * scale();
      draw();
    };
    // Çerçeve boyutu değişince (döndürme, pencere) görünen alanın merkezi korunur
    const layout = (center) => {
      const r = stage.getBoundingClientRect();
      const c = center || (sw ? [(sw / 2 - ox) / scale(), (sh / 2 - oy) / scale()] : [nw / 2, nh / 2]);
      sw = r.width; sh = r.height;
      base = Math.max(sw / nw, sh / nh);
      ox = sw / 2 - c[0] * scale();
      oy = sh / 2 - c[1] * scale();
      draw();
    };
    const close = (apply) => {
      if (apply) {
        const s = scale(), f = n => Math.max(0, Math.min(1, n)).toFixed(4);
        const x = -ox / s, y = -oy / s, w = sw / s, h = sh / s;
        const cv = document.createElement('canvas');
        cv.width = 480; cv.height = Math.round(480 * rh / rw);
        cv.getContext('2d').drawImage(img, x, y, w, h, 0, 0, cv.width, cv.height);
        onApply([f(x / nw), f(y / nh), f(w / nw), f(h / nh)].join(','), cv.toDataURL('image/jpeg', 0.85));
      } else {
        onCancel();
      }
      window.removeEventListener('resize', onResize);
      dlg.close();
      dlg.remove();
      URL.revokeObjectURL(url);
      coverInput.focus();
    };
    const onResize = () => layout();
    window.addEventListener('resize', onResize);

    // Sürükleme ve iki parmakla yakınlaştırma (Pointer Events: fare, dokunma, kalem)
    const pts = new Map();
    let pinch = null;
    const local = e => { const r = stage.getBoundingClientRect(); return [e.clientX - r.left, e.clientY - r.top]; };
    stage.addEventListener('pointerdown', e => {
      stage.setPointerCapture(e.pointerId);
      pts.set(e.pointerId, local(e));
      if (pts.size === 2) {
        const [a, b] = [...pts.values()];
        pinch = { d: Math.hypot(a[0] - b[0], a[1] - b[1]), z: zoom };
      }
    });
    stage.addEventListener('pointermove', e => {
      if (!pts.has(e.pointerId)) return;
      const prev = pts.get(e.pointerId), cur = local(e);
      pts.set(e.pointerId, cur);
      if (pts.size === 1) {
        ox += cur[0] - prev[0];
        oy += cur[1] - prev[1];
        draw();
      } else if (pinch && pts.size === 2) {
        const [a, b] = [...pts.values()];
        zoomAt(pinch.z * Math.hypot(a[0] - b[0], a[1] - b[1]) / pinch.d, (a[0] + b[0]) / 2, (a[1] + b[1]) / 2);
      }
    });
    const up = e => { pts.delete(e.pointerId); if (pts.size < 2) pinch = null; };
    stage.addEventListener('pointerup', up);
    stage.addEventListener('pointercancel', up);
    stage.addEventListener('wheel', e => {
      e.preventDefault();
      const [cx, cy] = local(e);
      zoomAt(zoom * Math.exp(-e.deltaY * 0.0015), cx, cy);
    }, { passive: false });
    stage.addEventListener('keydown', e => {
      const step = e.shiftKey ? 40 : 10;
      const moves = { ArrowLeft: [step, 0], ArrowRight: [-step, 0], ArrowUp: [0, step], ArrowDown: [0, -step] };
      if (moves[e.key]) { ox += moves[e.key][0]; oy += moves[e.key][1]; draw(); }
      else if (e.key === '+' || e.key === '=') zoomAt(zoom * 1.1, sw / 2, sh / 2);
      else if (e.key === '-') zoomAt(zoom / 1.1, sw / 2, sh / 2);
      else return;
      e.preventDefault();
    });
    zoomIn.addEventListener('input', () => zoomAt(parseFloat(zoomIn.value), sw / 2, sh / 2));
    $('[data-crop-reset]', dlg).addEventListener('click', () => { zoom = 1; layout([nw / 2, nh / 2]); });
    $('[data-crop-cancel]', dlg).addEventListener('click', () => close(false));
    $('[data-crop-apply]', dlg).addEventListener('click', () => close(true));
    dlg.addEventListener('cancel', e => { e.preventDefault(); close(false); }); // Esc

    img.onload = () => {
      nw = img.naturalWidth; nh = img.naturalHeight;
      dlg.showModal();
      layout([nw / 2, nh / 2]);
      stage.focus();
    };
    img.onerror = () => { dlg.remove(); URL.revokeObjectURL(url); alert('Görsel açılamadı; JPG, PNG veya WebP seç.'); onCancel(); };
    img.src = url;
  }

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

  /* Açılır pencere: [data-modal-link] düğmesi hedef formu ?pencere=1 ile bir <dialog> içindeki iframe'de açar.
     Pencerede kayıt bitince sunucu (admin/_done) "go" mesajı yollar; ana sayfa yeni adrese gider.
     JS kapalıyken ya da <dialog> yoksa bağlantı normal sayfa olarak açılır. */
  const withModal = href => href + (href.includes('?') ? '&' : '?') + 'pencere=1';
  let frameDlg = null;
  const closeFrame = () => {
    if (!frameDlg) return true;
    const child = frameDlg.querySelector('iframe')?.contentWindow;
    if (child?.kdrkCanClose && !child.kdrkCanClose()) return false;
    frameDlg.close(); frameDlg.remove(); frameDlg = null; return true;
  };
  document.addEventListener('click', e => {
    const a = e.target.closest('[data-modal-link]');
    if (!a || typeof HTMLDialogElement !== 'function' || e.ctrlKey || e.metaKey || e.shiftKey) return;
    e.preventDefault();
    if (!closeFrame()) return;
    const title = a.dataset.modalTitle || a.textContent.trim();
    frameDlg = document.createElement('dialog');
    frameDlg.className = 'frame-dialog';
    frameDlg.setAttribute('aria-label', title);
    frameDlg.innerHTML = '<div class="frame-head"><h2></h2><button type="button" class="btn-icon" data-frame-close aria-label="Pencereyi kapat">' +
      '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button></div>' +
      '<iframe></iframe>';
    $('h2', frameDlg).textContent = title;
    $('iframe', frameDlg).title = title;
    document.body.appendChild(frameDlg);
    $('[data-frame-close]', frameDlg).addEventListener('click', closeFrame);
    frameDlg.addEventListener('cancel', ev => { ev.preventDefault(); closeFrame(); });
    $('iframe', frameDlg).src = withModal(a.getAttribute('href'));
    frameDlg.showModal();
  });
  window.addEventListener('message', e => {
    if (e.origin !== location.origin || !e.data || !e.data.kdrk) return;
    if (e.data.kdrk === 'close') closeFrame();
    if (e.data.kdrk === 'go' && typeof e.data.url === 'string') {
      const u = new URL(e.data.url, location.href);
      if (u.origin === location.origin) location.href = u.href;
    }
  });
  // Pencerenin içi: formlar kipi korusun, "Vazgeç" pencereyi kapatsın
  if (document.body.hasAttribute('data-modal-page')) {
    $$('form').forEach(f => {
      if (f.querySelector('input[name="_pencere"]')) return;
      const h = document.createElement('input');
      h.type = 'hidden'; h.name = '_pencere'; h.value = '1';
      f.appendChild(h);
    });
    $$('[data-modal-cancel], .save-bar a.btn-ghost, .page-head .back-link').forEach(a => a.addEventListener('click', e => {
      if (window.parent === window) return;
      e.preventDefault();
      window.parent.postMessage({ kdrk: 'close' }, location.origin);
    }));
  }

  /* Sekmeler ve sihirbaz: [data-steps] içindeki .step panelleri.
     data-steps="tabs"   → üstteki sekmelerle serbest geçiş (düzenleme)
     data-steps="wizard" → İleri/Geri; zorunlu alanlar dolmadan sonraki adıma geçilmez, son adımda Kaydet (yeni kayıt)
     JS kapalıyken tüm paneller alt alta görünür. Sunucu hatası varsa hatalı alanın paneli açılır. */
  $$('[data-steps]').forEach(box => {
    const wizard = box.dataset.steps === 'wizard';
    const steps = $$('.step', box);
    const nav = $('.steps-nav', box);
    if (!steps.length || !nav) return;
    box.classList.add('is-js');
    const tabs = steps.map((s, i) => {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'steps-tab';
      b.id = 'tab-' + s.id;
      b.setAttribute('role', 'tab');
      b.setAttribute('aria-controls', s.id);
      b.innerHTML = (wizard ? '<span class="steps-no">' + (i + 1) + '</span>' : '') + '<span></span>';
      b.lastChild.textContent = s.dataset.title;
      s.setAttribute('role', 'tabpanel');
      s.setAttribute('aria-labelledby', b.id);
      nav.appendChild(b);
      return b;
    });
    nav.setAttribute('role', 'tablist');
    const prev = $('[data-step-prev]', box), next = $('[data-step-next]', box), submit = $('[data-step-submit]', box);
    let cur = 0;
    const valid = i => {
      const bad = $$('input, select, textarea', steps[i]).find(el => !el.disabled && !el.checkValidity());
      if (bad) { bad.reportValidity(); bad.focus(); return false; }
      return true;
    };
    const show = (i, focus) => {
      cur = Math.max(0, Math.min(steps.length - 1, i));
      steps.forEach((s, k) => { s.hidden = k !== cur; });
      tabs.forEach((t, k) => {
        t.setAttribute('aria-selected', k === cur ? 'true' : 'false');
        t.tabIndex = k === cur ? 0 : -1;
        t.classList.toggle('is-done', wizard && k < cur);
      });
      if (wizard) {
        if (prev) prev.hidden = cur === 0;
        if (next) next.hidden = cur === steps.length - 1;
        if (submit) submit.hidden = cur !== steps.length - 1;
      }
      steps[cur].dispatchEvent(new CustomEvent('step:show', { bubbles: true }));
      if (focus) { const f = $('input:not([type=hidden]):not([type=range]), select, textarea', steps[cur]); if (f) f.focus({ preventScroll: true }); }
      try { if (!wizard) sessionStorage.setItem('kdrk-tab-' + location.pathname, String(cur)); } catch (e) {}
    };
    tabs.forEach((t, i) => t.addEventListener('click', () => {
      if (wizard && i > cur) { for (let k = cur; k < i; k++) if (!valid(k)) { show(k); return; } }
      show(i, true);
    }));
    nav.addEventListener('keydown', e => {
      const d = { ArrowRight: 1, ArrowLeft: -1 }[e.key];
      if (!d) return;
      e.preventDefault();
      tabs[(cur + d + tabs.length) % tabs.length].click();
      tabs[cur].focus();
    });
    if (prev) prev.addEventListener('click', () => show(cur - 1, true));
    if (next) next.addEventListener('click', () => { if (valid(cur)) show(cur + 1, true); });
    // Sunucu hatası → ilgili panel; yoksa düzenlemede son açılan sekme
    const errAt = steps.findIndex(s => s.querySelector('[aria-invalid="true"]'));
    let start = 0;
    if (errAt >= 0) start = errAt;
    else if (!wizard && box.dataset.resetStep !== '1') { try { start = parseInt(sessionStorage.getItem('kdrk-tab-' + location.pathname) || '0', 10) || 0; } catch (e) {} }
    show(start);
    // Gönderimde gizli paneldeki geçersiz alan sessiz kalmasın
    const form = box.closest('form');
    if (form) form.addEventListener('invalid', e => {
      const i = steps.findIndex(s => s.contains(e.target));
      if (i >= 0 && i !== cur) show(i);
    }, true);
  });

  /* Zorluk kaydırıcısı: 1–10 puan, bant rengi ve adı canlı güncellenir. "Zorluk yok" işaretliyse kaydırıcı kapanır. */
  const BANDS = [[2, 'Kolay', 'easy'], [4, 'Kolay-Orta', 'medium'], [6, 'Orta', 'medium'], [8, 'Orta-Zor', 'hard'], [10, 'Zor', 'very-hard']];
  $$('[data-score]').forEach(box => {
    const range = $('input[type=range]', box), out = $('[data-score-out]', box), none = $('[data-score-none]', box);
    const draw = () => {
      const v = parseInt(range.value, 10);
      const b = BANDS.find(x => v <= x[0]);
      const off = !!(none && none.checked);
      box.dataset.band = off ? 'none' : b[2];
      out.textContent = off ? 'Zorluk derecesi yok' : b[1] + ' · ' + v + '/10';
      range.style.setProperty('--fill', ((v - 1) / 9 * 100) + '%');
      range.disabled = off;
      range.setAttribute('aria-valuetext', out.textContent);
    };
    range.addEventListener('input', draw);
    if (none) none.addEventListener('change', draw);
    draw();
  });

  /* Haritadan konum: Leaflet + OpenStreetMap. Tıklayınca ya da işaret sürüklenince nokta düşer, enlem-boylam gizli alanlara yazılır.
     Harita yalnız panel görünür olunca kurulur (sekmeli formda gizli panelde boyut 0 olur). */
  $$('[data-map-picker]').forEach(box => {
    const lat = $('[data-map-lat]', box), lng = $('[data-map-lng]', box), el = $('.map-canvas', box), clear = $('[data-map-clear]', box), out = $('[data-map-out]', box);
    let map = null, marker = null;
    const set = ll => {
      lat.value = ll.lat.toFixed(6); lng.value = ll.lng.toFixed(6);
      lat.dispatchEvent(new Event('input', { bubbles: true }));
      if (!marker) { marker = L.marker(ll, { draggable: true, keyboard: true, title: 'Faaliyet konumu' }).addTo(map); marker.on('dragend', () => set(marker.getLatLng())); }
      else marker.setLatLng(ll);
      out.textContent = 'Seçilen konum: ' + ll.lat.toFixed(5) + ', ' + ll.lng.toFixed(5);
      if (clear) clear.hidden = false;
    };
    const init = () => {
      if (map) { map.invalidateSize(); return; }
      if (!window.L || el.offsetWidth === 0) return;
      const has = lat.value !== '' && lng.value !== '';
      const start = has ? [parseFloat(lat.value), parseFloat(lng.value)] : [40.85, 41.05]; // Kaçkarlar
      map = L.map(el, { scrollWheelZoom: false }).setView(start, has ? 13 : 9);
      L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18, attribution: '© OpenStreetMap katkıcıları' }).addTo(map);
      map.on('click', e => set(e.latlng));
      if (has) set(L.latLng(start[0], start[1]));
      else out.textContent = 'Konumu seçmek için haritaya tıkla.';
    };
    if (clear) clear.addEventListener('click', () => {
      lat.value = ''; lng.value = '';
      lat.dispatchEvent(new Event('input', { bubbles: true }));
      if (marker) { marker.remove(); marker = null; }
      out.textContent = 'Konum seçilmedi.';
      clear.hidden = true;
    });
    const step = box.closest('.step');
    if (step) step.addEventListener('step:show', () => setTimeout(init, 0));
    box.closest('details')?.addEventListener('toggle', init);
    if (document.readyState === 'complete') init(); else window.addEventListener('load', init);
  });
  /* Faaliyet formu: kategori seçilince malzeme notu; son adımda sitedeki kartın ve bilgilerin önizlemesi */
  const actForm = $('[data-act-form]');
  if (actForm) {
    let gear = {};
    try { gear = JSON.parse(actForm.dataset.gear || '{}'); } catch (e) {}
    const payNote = actForm.dataset.payNote || '';
    const field = n => actForm.elements[n];
    const val = n => (field(n) && field(n).value || '').trim();
    const MONTHS = ['OCA', 'ŞUB', 'MAR', 'NİS', 'MAY', 'HAZ', 'TEM', 'AĞU', 'EYL', 'EKİ', 'KAS', 'ARA'];
    const trDate = s => { if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return ''; const [y, m, d] = s.split('-'); return d + ' ' + MONTHS[+m - 1] + ' ' + y; };
    const gearNote = $('[data-gear-note]', actForm);
    const catSel = field('category');
    const drawGear = () => {
      if (!gearNote) return;
      const items = gear[catSel.value] || [];
      gearNote.textContent = !catSel.value ? 'Kategori seçince o kategorinin malzeme ve kontrol notu burada görünür.'
        : items.length ? 'Sitede otomatik eklenecek: ' + items.join(' · ') : 'Bu kategori için Genel Ayarlar\'da not yazılmamış; sitede bu bölüm görünmez.';
    };
    if (catSel) { catSel.addEventListener('change', drawGear); drawGear(); }

    const pv = $('[data-preview]', actForm);
    const drawPreview = () => {
      const scoreBox = $('[data-score]', actForm);
      const none = $('[data-score-none]', actForm);
      const score = none && none.checked ? null : parseInt(val('difficulty_score'), 10);
      const band = score === null ? null : [[2, 'Kolay', '#34D399'], [4, 'Kolay-Orta', '#FB923C'], [6, 'Orta', '#FB923C'], [8, 'Orta-Zor', '#F87171'], [10, 'Zor', '#DC2626']].find(b => score <= b[0]);
      const prev = $('[data-cover-prev]', actForm);
      $('[data-pv-img]', pv).style.backgroundImage = prev ? prev.style.backgroundImage : '';
      $('[data-pv-date]', pv).textContent = trDate(val('starts_on')) || 'Tarih yok';
      const diff = $('[data-pv-diff]', pv);
      diff.textContent = band ? band[1] + ' ' + score + '/10' : (catSel.value === 'egitim' ? 'Eğitim' : 'Herkese açık');
      diff.style.setProperty('--band', band ? band[2] : '#38BDF8');
      $('[data-pv-loc]', pv).textContent = val('location') || 'Yer yazılmadı';
      $('[data-pv-title]', pv).textContent = val('title') || 'Faaliyet adı';
      $('[data-pv-sum]', pv).textContent = val('summary');
      const fee = val('fee').replace(',', '.');
      const items = [
        ['Kategori', catSel.selectedIndex > 0 ? catSel.options[catSel.selectedIndex].text : ''],
        ['Tarih', trDate(val('starts_on')) + (val('ends_on') ? ' – ' + trDate(val('ends_on')) : '')],
        ['Zorluk', band ? band[1] + ' · ' + score + '/10' : 'Yok'],
        ['Mesafe', val('distance_km') ? val('distance_km') + ' km' : ''],
        ['Konum', val('lat') ? 'Haritada işaretli' : ''],
        ['Buluşma', val('meeting_text')],
        ['Ücret', fee === '' ? '' : (parseFloat(fee) > 0 ? '₺' + fee : 'Ücretsiz') + (payNote ? '\n' + payNote : '')],
        ['Kontenjan', val('quota') === '0' ? 'Sınır yok' : (val('quota') ? val('quota') + ' kişi' : '')],
        ['Malzeme ve kontroller', [...(gear[catSel.value] || []), val('gear_extra')].filter(Boolean).join('\n')],
        ['Plan', val('body').length > 280 ? val('body').slice(0, 280) + '…' : val('body')],
      ];
      const dl = $('[data-pv-facts]', pv);
      dl.textContent = '';
      items.forEach(([k, v]) => {
        const row = document.createElement('div');
        const dt = document.createElement('dt'); dt.textContent = k;
        const dd = document.createElement('dd');
        if (v) dd.textContent = v; else { dd.textContent = 'Boş — sitede gösterilmez'; dd.className = 'pv-empty'; }
        row.append(dt, dd);
        dl.appendChild(row);
      });
      if (scoreBox) scoreBox.dispatchEvent(new Event('input'));
    };
    if (pv) pv.addEventListener('step:show', drawPreview);
  }
})();
