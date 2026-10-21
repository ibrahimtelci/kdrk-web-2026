<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1551524559-8af4e6624178?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep" aria-hidden="true">›</span><a href="<?= e(url('faaliyetler')) ?>">Faaliyetler</a><span class="sep" aria-hidden="true">›</span><span class="current">Dağ Kayağı</span>
    </nav>
    <span class="eyebrow" style="color:#fff">FAALİYET · 04</span>
    <h1>Dağ Kayağı</h1>
    <p class="lede">Skitur ve tur kayağı. Pistlerin ötesinde, kendi izini açarak çıktığın zirveden bakir karda iniş.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> SKİTUR · TUR · BAKİR KAR</div>
  </div>
</section>
<section class="corp-section" data-screen-label="02 Tanım">
  <div class="container">
    <div class="disc-intro-grid">
      <div class="story-text reveal">
        <span class="eyebrow">Bu Disiplin Nedir?</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Telesiyej yok, sadece sen ve dağ.</h2>
        <p class="drop">Dağ kayağı (skitur); foklu kayaklarla yamacı tırmanıp, tepeden bakir karda inilen kış disiplinidir. Ovit, Kavron ve Palandöken çevresi başlıca sahalarımızdır.</p>
        <p>Çığ güvenliği bu disiplinin merkezindedir. Her katılımcı transceiver, sonda ve kürek taşır; çıkış öncesi çığ bülteni değerlendirilir.</p>
      </div>
      <div class="disc-quickfacts reveal">
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3l4 8 5-5 5 12H2z"/></svg></span><span class="qf-label">Sezon</span><span class="qf-value">Ara–Nis</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="qf-label">Süre</span><span class="qf-value">1–2 Gün</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg></span><span class="qf-label">Zorluk</span><span class="qf-value">Orta / Zor</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="qf-label">Ön Koşul</span><span class="qf-value">Çığ Eğitimi</span></div>
      </div>
    </div>
  </div>
</section>
<section class="corp-section alt" data-screen-label="03 Seviyeler">
  <div class="container">
    <div class="corp-head reveal">
      <span class="eyebrow">Seviyeler</span>
      <h2>Kara nasıl çıkılır?</h2>
      <p>Skitur, kayak bilgisi ve çığ güvenliği ister. Adım adım hazırlan.</p>
    </div>
    <div class="level-grid four">
      <div class="level-card reveal">
        <span class="lv-tag education">Eğitim</span>
        <h3>Kış Dağcılık Eğitimi</h3>
        <p>TDF onaylı bölgesel eğitim: krampon-balta, kar teknikleri ve çığ güvenliği.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Yaz eğitimi önerilir</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kış ekipmanı</li>
        </ul>
        <a class="lv-link" href="<?= e(url('disiplin', ['slug' => 'egitim'])) ?>">Eğitim programı →</a>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag easy">Hazırlık</span>
        <h3>Kar Yürüyüşü</h3>
        <p>Kar ayakkabısıyla kış doğasına giriş; ekipman gerektirmez.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kış kıyafeti</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Temel kondisyon</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag medium">Orta</span>
        <h3>Skitur Başlangıç</h3>
        <p>Foklu kayakla tırmanış ve temel iniş teknikleri.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kırmızı piste kayabilmek</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Çığ farkındalığı</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag hard">İleri</span>
        <h3>Tur & Zirve</h3>
        <p>Çok saatli turlar, dik yamaç inişleri ve zirve hedefleri.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Çığ güvenliği eğitimi</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kondisyon</li></ul>
      </div>
    </div>
  </div>
</section>
<section class="corp-section" data-screen-label="04 Yaklaşan">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:40px">
      <div class="section-head-left">
        <span class="eyebrow">Kış Programı</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Yaklaşan Kayak Etkinlikleri</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('faaliyetler')) ?>?cat=dag-kayagi" class="btn-text">Tümünü Gör <span class="arrow">→</span></a>
      </div>
    </div>
    <?= $this->partial('partials/activity-grid') ?>
  </div>
</section>
<section class="corp-section alt" data-screen-label="05 Galeri">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:32px">
      <div class="section-head-left">
        <span class="eyebrow">Sahadan</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Dağ Kayağı Anıları</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('galeri', ['kategori' => $category])) ?>" class="btn-text">Galeriye Git <span class="arrow">→</span></a>
      </div>
    </div>
    <?= $this->partial('partials/gallery-strip', ['strip' => $strip]) ?>
  </div>
</section>
<section class="corp-section" data-screen-label="06 CTA">
  <div class="container">
    <div class="join-band reveal">
      <div class="topo-watermark"></div>
      <span class="eyebrow center" style="color:#fff;justify-content:center">Kışı Bekle</span>
      <h2>İlk karda dağda olalım.</h2>
      <p>Çığ güvenliği eğitimimizle başla, sezonu bizimle aç.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('faaliyetler')) ?>?cat=dag-kayagi" class="btn btn-primary">Kayak Etkinlikleri <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('kis-dagcilik')) ?>" class="btn btn-ghost">Kış Notları</a>
      </div>
    </div>
  </div>
</section>
