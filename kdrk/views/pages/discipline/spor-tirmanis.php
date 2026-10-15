<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1522163182402-834f871fd851?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep">›</span><a href="<?= e(url('faaliyetler')) ?>">Faaliyetlerimiz</a><span class="sep">›</span><span class="current">Spor Tırmanış</span>
    </nav>
    <span class="eyebrow" style="color:#fff">FAALİYET · 03</span>
    <h1>Spor Tırmanış</h1>
    <p class="lede">Kaya ve salon tırmanışı. Teknik hareket, güç ve denge — yıl boyu süren düzenli antrenmanlarla gelişen bir disiplin.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> KAYA · SALON · BOULDER</div>
  </div>
</section>
<section class="corp-section" data-screen-label="02 Tanım">
  <div class="container">
    <div class="disc-intro-grid">
      <div class="story-text reveal">
        <span class="eyebrow">Bu Disiplin Nedir?</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Güç değil, teknik kazandırır.</h2>
        <p class="drop">Spor tırmanış; sabit emniyet noktaları olan rotalarda, ip ve emniyet sistemiyle yapılan tırmanıştır. Kış aylarında salon, yaz aylarında doğal kaya alanlarında çalışırız.</p>
        <p>Başlangıç seviyesinden yarışma seviyesine kadar antrenörlerimiz eşliğinde ilerlersin. Lead, top-rope ve boulder çalışmaları düzenli olarak yapılır.</p>
      </div>
      <div class="disc-quickfacts reveal">
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 3l4 8 5-5 5 12H2z"/></svg></span><span class="qf-label">Tip</span><span class="qf-value">Kaya / Salon</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="qf-label">Antrenman</span><span class="qf-value">Haftada 2</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg></span><span class="qf-label">Zorluk</span><span class="qf-value">Tüm Seviyeler</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span><span class="qf-label">Ön Koşul</span><span class="qf-value">Yok</span></div>
      </div>
    </div>
  </div>
</section>
<section class="corp-section alt" data-screen-label="03 Seviyeler">
  <div class="container">
    <div class="corp-head reveal">
      <span class="eyebrow">Seviyeler</span>
      <h2>Nereden başlamalı?</h2>
      <p>Sıfırdan başlayıp yarışma seviyesine kadar ilerleyebilirsin.</p>
    </div>
    <div class="level-grid four">
      <div class="level-card reveal">
        <span class="lv-tag education">Eğitim</span>
        <h3>Tek İp Boylu Kaya Tırmanış Eğitimi</h3>
        <p>TDF onaylı bölgesel eğitim: emniyet alma, düğümler ve tek ip boyu tırmanış. Duvara ve kayaya çıkmadan önce.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kulüp üyeliği</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Temel kondisyon</li>
        </ul>
        <a class="lv-link" href="<?= e(url('disiplin', ['slug' => 'egitim'])) ?>">Eğitim programı →</a>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag easy">Başlangıç</span>
        <h3>Tanışma</h3>
        <p>Temel teknik, düğümler ve güvenlik. Salon top-rope ile başlangıç.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Ekipman sağlanır</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Yaş sınırı 10+</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag medium">Orta</span>
        <h3>Lead & Boulder</h3>
        <p>Öncü tırmanış ve boulder problemleri. Düzenli antrenman grubu.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Temel teknik şart</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kendi ayakkabın</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag hard">İleri</span>
        <h3>Yarışma Takımı</h3>
        <p>Bölge ve ulusal yarışmalara hazırlık. Performans antrenmanı.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Antrenör onayı</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Düzenli katılım</li></ul>
      </div>
    </div>
  </div>
</section>
<section class="corp-section" data-screen-label="04 Yaklaşan">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:40px">
      <div class="section-head-left">
        <span class="eyebrow">Program</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Yaklaşan Tırmanış Etkinlikleri</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('faaliyetler')) ?>?cat=spor-tirmanis" class="btn-text">Tümünü Gör <span class="arrow">→</span></a>
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
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Spor Tırmanış Anıları</h2>
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
      <span class="eyebrow center" style="color:#fff;justify-content:center">Tutun</span>
      <h2>İlk tutuşunda bağımlısı olacaksın.</h2>
      <p>Deneme dersine gel, ekipmanı biz sağlayalım.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('faaliyetler')) ?>?cat=spor-tirmanis" class="btn btn-primary">Etkinlikleri Gör <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('kulup-uyeligi')) ?>" class="btn btn-ghost">Üye Ol</a>
      </div>
    </div>
  </div>
</section>
