/* V2: hızlı tanımlama; sunucudaki doğrulamayı tamamlayan küçük kolaylıklar. */
(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  /* Tüm sekmelerde doğrula; hatalı alanı ve kapalı ayrıntısını görünür yap. */
  $$('form[data-dirty-guard]').forEach(form => {
    let dirty = false, sending = false;
    const state = $('[data-save-state]', form);
    const snapshot = () => JSON.stringify([...new FormData(form)].map(([key, value]) =>
      [key, typeof value === 'string' ? value : [value.name, value.size, value.name ? value.lastModified : 0]]));
    const initial = snapshot();
    const changed = () => {
      dirty = snapshot() !== initial;
      if (dirty) form.dataset.dirty = '1'; else delete form.dataset.dirty;
      if (state) state.textContent = dirty ? 'Kaydedilmemiş değişiklikler' : 'Değişiklik yok';
    };
    form.addEventListener('input', changed);
    form.addEventListener('change', changed);
    form.addEventListener('form:changed', changed);
    form.addEventListener('submit', e => {
      const bad = $$('input,select,textarea', form).find(el => !el.disabled && !el.checkValidity());
      if (bad) {
        e.preventDefault();
        for (let p = bad.parentElement; p && p !== form; p = p.parentElement) if (p.tagName === 'DETAILS') p.open = true;
        const step = bad.closest('.step');
        if (step) $(`[aria-controls="${step.id}"]`, form)?.click();
        bad.focus(); bad.reportValidity();
        return;
      }
      sending = true;
      if (state) state.textContent = 'Kaydediliyor…';
    });
    window.addEventListener('beforeunload', e => {
      if (dirty && !sending) { e.preventDefault(); e.returnValue = ''; }
    });
    window.kdrkCanClose = () => {
      if (!dirty || sending) return true;
      if (!confirm('Kaydedilmemiş değişiklikler var. Kaydetmeden çıkılsın mı?')) return false;
      dirty = false;
      delete form.dataset.dirty;
      return true;
    };
    $$('a[data-modal-cancel], .save-bar a.btn-ghost, .page-head .back-link', form.closest('body')).forEach(a => {
      a.addEventListener('click', e => {
        if (!window.kdrkCanClose()) { e.preventDefault(); e.stopImmediatePropagation(); }
        else { dirty = false; delete form.dataset.dirty; }
      }, true);
    });
    // Kapalı sekme/ayrıntı içindeki sunucu hatasını da göster.
    const error = $('[aria-invalid="true"]', form);
    if (error) for (let p = error.parentElement; p && p !== form; p = p.parentElement) if (p.tagName === 'DETAILS') p.open = true;
  });

  const act = $('[data-act-form]');
  if (act) {
    const cat = act.elements.category, none = $('[data-score-none]', act);
    cat.addEventListener('change', () => {
      if (!none || none.disabled) return;
      none.checked = ['egitim', 'sosyal'].includes(cat.value);
      none.dispatchEvent(new Event('change', { bubbles: true }));
    });
    const fee = act.elements.fee, feeMode = $('[data-fee-mode]', act);
    if (fee && feeMode) {
      let paidAmount = parseFloat(fee.value.replace(',', '.')) > 0 ? fee.value : '';
      const draw = (changed = false) => {
        fee.closest('.field').hidden = feeMode.value !== 'paid';
        if (changed) {
          if (parseFloat(fee.value.replace(',', '.')) > 0) paidAmount = fee.value;
          fee.value = feeMode.value === 'free' ? '0' : feeMode.value === 'unknown' ? '' : paidAmount;
          fee.dispatchEvent(new Event('input', { bubbles: true }));
        }
        fee.required = feeMode.value === 'paid';
      };
      feeMode.addEventListener('change', () => draw(true)); draw();
    }
    const limited = $('[data-quota-limited]', act), quota = act.elements.quota;
    if (limited && quota) {
      let last = parseInt(quota.value, 10) || 15;
      const draw = () => { quota.closest('.field').hidden = !limited.checked; quota.min = limited.checked ? '1' : '0'; };
      limited.addEventListener('change', () => {
        if (!limited.checked) { last = parseInt(quota.value, 10) || last; quota.value = '0'; }
        else quota.value = String(last);
        draw(); quota.dispatchEvent(new Event('input', { bubbles: true }));
      }); draw();
    }
    const pv = $('[data-preview]', act);
    if (pv) {
      const title = $('.pv-title', pv);
      const refresh = () => {
        pv.dispatchEvent(new CustomEvent('step:show', { bubbles: true }));
        const compact = $('[data-compact-preview]', act), card = $('.pv-card', pv);
        if (compact && card) compact.replaceChildren(card.cloneNode(true));
      };
      act.addEventListener('input', refresh); act.addEventListener('change', refresh); act.addEventListener('form:changed', refresh); refresh();
      // Sadece önizlemede görüntü güncellenir; form değerleri değişmez.
      if (title) title.setAttribute('aria-live', 'polite');
    }
  }

  const news = $('[data-news-form]');
  if (news) {
    const mode = $('[data-content-mode]', news);
    const body = news.elements.body, activity = news.elements.activity_id, link = news.elements.link_url;
    let previous = mode.value;
    const draw = () => {
      body.closest('.field').hidden = mode.value !== 'article';
      activity.closest('.field').hidden = mode.value === 'link';
      link.closest('.field').hidden = mode.value !== 'link';
      body.required = mode.value === 'article';
      activity.required = mode.value === 'activity';
      link.required = mode.value === 'link';
      const panel = link.closest('section');
      panel.hidden = false;
      // Gizlenen alanlar tarayıcı doğrulamasına takılmaz; sunucu kip önceliğini uygular.
    };
    mode.addEventListener('change', () => {
      if (previous === 'article' && body.value.trim() && mode.value !== previous && !confirm('Bu seçimle kaydedersen yazı metni yerine yönlendirme kullanılacak. Devam edilsin mi?')) {
        mode.value = previous; return;
      }
      previous = mode.value; draw();
    }); draw();
  }

  const gallery = $('[data-gallery-form]');
  if (gallery) {
    const select = gallery.elements.activity_id, name = gallery.elements.title;
    const extra = $('[data-gallery-extra]', gallery), note = $('[data-inherited-note]', gallery);
    let lastAutoName = '';
    const draw = (changed = false) => {
      const option = select.options[select.selectedIndex];
      const title = option?.textContent.split(' · ').slice(1).join(' · ') || '';
      if (changed && title && (!name.value.trim() || name.value === lastAutoName)) { name.value = title; lastAutoName = title; }
      note.textContent = select.value ? 'Boş tarih, kategori ve yer alanları bağlı faaliyetten alınır. Farklı bilgi gerekiyorsa ayrıntıları aç.' : 'Bağımsız albüm: tarih, kategori ve yeri aşağıdan belirleyebilirsin.';
      if (!select.value) extra.open = true;
    };
    select.addEventListener('change', () => draw(true)); draw();
  }
})();
