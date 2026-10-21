<?php
$phone = (string) setting('contact_phone', '');
$tel = preg_replace('/[^\d+]/', '', $phone);
$email = (string) setting('contact_email', '');
$whatsapp = (string) setting('social_whatsapp', '');
$socials = array_filter(['Instagram' => setting('social_instagram'), 'YouTube' => setting('social_youtube'), 'Strava' => setting('social_strava')]);
?>
<!-- HERO -->
<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep" aria-hidden="true">›</span><span class="current">İletişim</span>
    </nav>
    <span class="eyebrow" style="color:#fff">BİZE ULAŞ</span>
    <h1>İletişim</h1>
    <p class="lede">Faaliyetler, üyelik veya iş birliği — aklındaki her şey için buradayız. Lokalimize uğra, ara ya da formu doldur.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> RİZE MERKEZ · 41°01'N 40°31'E</div>
  </div>
</section>

<!-- CONTACT LAYOUT -->
<section data-screen-label="02 İletişim">
  <div class="container">
    <div class="contact-layout">

      <!-- Info -->
      <div class="contact-info reveal">
        <span class="eyebrow">Kulüp İletişim</span>
        <h2>Bizi bulabileceğin yerler</h2>
        <p>Lokalimiz hafta içi akşamları ve hafta sonu üyelere açıktır. Faaliyet öncesi toplanma noktaları ayrıca duyurulur.</p>

        <div class="ci-card">
          <span class="ci-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
          <div class="ci-body">
            <span class="ci-label">Adres</span>
            <span class="ci-value"><?= e(setting('contact_address', '')) ?></span>
            <span class="ci-sub">Rize Merkez / Rize · 53100</span>
          </div>
        </div>

        <div class="ci-card">
          <span class="ci-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3 19.5 19.5 0 01-6-6 19.8 19.8 0 01-3-8.6A2 2 0 014.1 2h3a2 2 0 012 1.7c.1.9.3 1.8.6 2.6a2 2 0 01-.4 2.1L8 9.6a16 16 0 006 6l1.2-1.2a2 2 0 012.1-.5c.8.3 1.7.5 2.6.6a2 2 0 011.7 2z"/></svg></span>
          <div class="ci-body">
            <span class="ci-label">Telefon</span>
            <span class="ci-value"><a href="tel:<?= e($tel) ?>"><?= e($phone) ?></a></span>
            <span class="ci-sub">Hafta içi 10:00–18:00</span>
          </div>
        </div>

        <div class="ci-card">
          <span class="ci-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/></svg></span>
          <div class="ci-body">
            <span class="ci-label">E-posta</span>
            <span class="ci-value"><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></span>
            <span class="ci-sub">Üyelik: uyelik@kdrk.org.tr</span>
          </div>
        </div>

<?php if ($whatsapp !== ''): ?>
        <div class="ci-card">
          <span class="ci-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z"/></svg></span>
          <div class="ci-body">
            <span class="ci-label">WhatsApp Topluluk</span>
            <span class="ci-value"><a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener">Topluluğa katıl</a></span>
            <span class="ci-sub">Faaliyet duyuruları için katıl</span>
          </div>
        </div>
<?php endif ?>

<?php if ($socials): $icons = [
        'Instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>',
        'YouTube' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.08C5.12 19.54 12 19.54 12 19.54s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.33 29 29 0 00-.46-5.33z"/><path d="M9.75 15.02V8.48l5.75 3.27z" fill="currentColor"/></svg>',
        'Strava' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"><path d="M9 5l5 10h-3l-2-4-2 4H4z"/><path d="M14 15l3 6 3-6h-2.5l-.5 1-.5-1z"/></svg>',
    ]; ?>
        <div class="contact-socials">
<?php foreach ($socials as $label => $href): ?>
          <a href="<?= e($href) ?>" target="_blank" rel="noopener" aria-label="<?= e($label) ?>"><?= $icons[$label] ?></a>
<?php endforeach ?>
        </div>
<?php endif ?>
      </div>

      <!-- Form -->
      <div class="contact-form-card reveal" id="iletisim-formu">
        <h3>Bize mesaj gönder</h3>
        <p class="form-intro">Formu doldur, ekibimiz 1–2 iş günü içinde dönüş yapsın.</p>
<?php
$old += ['name' => '', 'phone' => '', 'email' => '', 'topic' => 'genel', 'message' => ''];
$fe = static fn (string $f): string => $errors[$f] ?? '';
$attrs = static fn (string $f): string => ($fe($f) !== '' ? ' class="is-error" aria-invalid="true"' : '') . ' aria-describedby="ce-' . $f . '"';
?>
        <form id="contact-form" method="post" action="<?= e(url('iletisim.gonder')) ?>" novalidate<?= $sent ? ' hidden' : '' ?>>
          <?= csrf_field() ?>
          <div class="hp-field" aria-hidden="true"><label for="c-website">Web sitesi</label><input type="text" id="c-website" name="website" tabindex="-1" autocomplete="off"></div>
          <p class="form-note">Yıldızlı (*) alanlar zorunludur.</p>
          <div class="cf-row">
            <div class="cf-field">
              <label for="c-name">Ad Soyad <span class="req" aria-hidden="true">*</span></label>
              <input type="text" id="c-name" name="name" value="<?= e($old['name']) ?>" required aria-required="true" autocomplete="name" autocapitalize="words"<?= $attrs('name') ?>>
              <span class="cf-err" id="ce-name"<?= $fe('name') ? '' : ' hidden' ?>><?= e($fe('name')) ?></span>
            </div>
            <div class="cf-field">
              <label for="c-phone">Telefon <span class="opt">(isteğe bağlı)</span></label>
              <input type="tel" id="c-phone" name="phone" value="<?= e($old['phone']) ?>" inputmode="tel" autocomplete="tel-national" placeholder="5XX XXX XX XX"<?= $attrs('phone') ?>>
              <span class="cf-err" id="ce-phone"<?= $fe('phone') ? '' : ' hidden' ?>><?= e($fe('phone')) ?></span>
            </div>
          </div>
          <div class="cf-field">
            <label for="c-email">E-posta <span class="req" aria-hidden="true">*</span></label>
            <input type="email" id="c-email" name="email" value="<?= e($old['email']) ?>" required aria-required="true" inputmode="email" autocomplete="email" autocapitalize="off"<?= $attrs('email') ?>>
            <span class="cf-err" id="ce-email"<?= $fe('email') ? '' : ' hidden' ?>><?= e($fe('email')) ?></span>
          </div>
          <div class="cf-field">
            <label for="c-topic">Konu</label>
            <select id="c-topic" name="topic">
<?php foreach (\App\Controllers\Site\ContactController::TOPICS as $k => $label): ?>
              <option value="<?= e($k) ?>"<?= $old['topic'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
            </select>
          </div>
          <div class="cf-field">
            <label for="c-message">Mesajın <span class="req" aria-hidden="true">*</span></label>
            <textarea id="c-message" name="message" rows="5" required aria-required="true" maxlength="5000" placeholder="Bize iletmek istediğin her şey…"<?= $attrs('message') ?>><?= e($old['message']) ?></textarea>
            <span class="cf-err" id="ce-message"<?= $fe('message') ? '' : ' hidden' ?>><?= e($fe('message')) ?></span>
          </div>
          <div class="form-alert" data-form-alert role="alert" hidden></div>
          <button type="submit" class="btn btn-primary cf-submit" data-submit>
            <span data-submit-label>Mesajı Gönder</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
          </button>
        </form>
        <div class="cf-success<?= $sent ? ' show' : '' ?>" data-success role="status" tabindex="-1">
          <span class="ck"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span>
          <h4>Mesajın bize ulaştı.</h4>
          <p>Teşekkürler. Ekibimiz 1–2 iş günü içinde e-posta ile dönüş yapacak.</p>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- MAP + HOURS -->
<section class="corp-section alt" data-screen-label="03 Konum">
  <div class="container">
    <div class="corp-head reveal" style="margin-bottom:32px">
      <span class="eyebrow">Konum &amp; Saatler</span>
      <h2>Lokalimize bekleriz</h2>
    </div>
    <div class="map-band reveal">
      <div class="map-wrap">
        <iframe
          title="KDRK Konum"
          src="https://maps.google.com/maps?q=41.0239479,40.5262278&amp;z=17&amp;hl=tr&amp;output=embed"
          loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        <div class="map-pin-badge">
          <span class="mp-co">41°01'N · 40°31'E</span>
          <strong>KDRK Kulüp Lokali</strong>
          <span>Yeniköy Mah. Cumhuriyet Cad. Rize İş Merkezi No:184/410, Rize Merkez</span>
          <a href="https://www.google.com/maps/search/?api=1&amp;query=41.0239479%2C40.5262278" target="_blank" rel="noopener">Google Haritalar'da aç →</a>
        </div>
      </div>
    </div>

    <div style="margin-top:24px" class="reveal">
      <div class="hours-grid">
        <div class="hours-cell"><span class="hc-day">Pazartesi</span><span class="hc-time closed">Kapalı</span></div>
        <div class="hours-cell"><span class="hc-day">Salı</span><span class="hc-time">18:00 – 21:00</span></div>
        <div class="hours-cell"><span class="hc-day">Çarşamba</span><span class="hc-time">18:00 – 21:00</span></div>
        <div class="hours-cell"><span class="hc-day">Perşembe</span><span class="hc-time">18:00 – 21:00</span></div>
        <div class="hours-cell"><span class="hc-day">Cuma</span><span class="hc-time open">18:00 – 22:00</span></div>
        <div class="hours-cell"><span class="hc-day">Cumartesi – Pazar</span><span class="hc-time">Faaliyet günleri</span></div>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->

<?php $this->section('scripts') ?>
<script>
/* İletişim formu: JS olmadan normal POST; JS varsa fetch ile gönderilir. Kurallar ContactController ile aynı. */
(function () {
  const form = document.getElementById('contact-form');
  if (!form) return;
  const success = document.querySelector('[data-success]');
  const alertBox = form.querySelector('[data-form-alert]');
  const submit = form.querySelector('[data-submit]');
  const label = form.querySelector('[data-submit-label]');
  const f = n => form.elements[n];
  function setErr(name, msg) {
    const el = f(name), box = document.getElementById('ce-' + name);
    el.classList.toggle('is-error', !!msg);
    el.setAttribute('aria-invalid', msg ? 'true' : 'false');
    if (box) { box.textContent = msg || ''; box.hidden = !msg; }
  }
  f('phone').addEventListener('input', e => {
    const d = e.target.value.replace(/\D/g, '').replace(/^0/, '').slice(0, 10);
    e.target.value = [d.slice(0, 3), d.slice(3, 6), d.slice(6, 8), d.slice(8, 10)].filter(Boolean).join(' ');
  });
  form.addEventListener('input', e => { if (e.target.classList.contains('is-error')) setErr(e.target.name, ''); });
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const errs = {};
    if (f('name').value.trim().length < 3) errs.name = 'Ad ve soyadını yaz.';
    const ph = f('phone').value.replace(/\D/g, '');
    if (ph && !/^5\d{9}$/.test(ph)) errs.phone = '5 ile başlayan 10 haneli numara yaz ya da boş bırak.';
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f('email').value.trim())) errs.email = 'Geçerli bir e-posta adresi yaz (ör. ad@ornek.com).';
    if (f('message').value.trim().length < 10) errs.message = 'Mesajın çok kısa; en az 10 karakter yaz.';
    ['name', 'phone', 'email', 'message'].forEach(n => setErr(n, errs[n] || ''));
    const first = Object.keys(errs)[0];
    if (first) { f(first).focus(); return; }

    alertBox.hidden = true;
    submit.disabled = true;
    label.textContent = 'Gönderiliyor…';
    try {
      const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.ok) {
        form.reset();
        form.hidden = true;
        success.classList.add('show');
        success.focus();
      } else if (res.status === 422 && data.errors) {
        Object.keys(data.errors).forEach(n => setErr(n, data.errors[n]));
        f(Object.keys(data.errors)[0]).focus();
      } else {
        throw new Error();
      }
    } catch (_) {
      alertBox.textContent = 'Mesaj gönderilemedi. Yazdıkların kaybolmadı; bağlantını kontrol edip tekrar dene.';
      alertBox.hidden = false;
    } finally {
      submit.disabled = false;
      label.textContent = 'Mesajı Gönder';
    }
  });
})();
</script>
<?php $this->end() ?>
