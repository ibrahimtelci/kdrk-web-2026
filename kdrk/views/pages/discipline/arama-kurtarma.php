<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1605540436563-5bca919ae766?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep">›</span><a href="<?= e(url('faaliyetler')) ?>">Faaliyetlerimiz</a><span class="sep">›</span><span class="current">Arama Kurtarma</span>
    </nav>
    <span class="eyebrow" style="color:#fff">FAALİYET · 07</span>
    <h1>Arama Kurtarma</h1>
    <p class="lede">1998’den bu yana deprem, sel, çığ ve dağda kaybolma vakalarında arama-kurtarma çalışmalarına katılıyoruz.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> 1998’DEN BERİ · <span data-stat="aramaKurtarma"><?= club_stat('aramaKurtarma') ?></span>+ OPERASYON · GÖNÜLLÜ</div>
  </div>
</section>
<section class="corp-section" data-screen-label="02 Tanım">
  <div class="container">
    <div class="disc-intro-grid">
      <div class="story-text reveal">
        <span class="eyebrow">Bu Disiplin Nedir?</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Dağda biri kaybolduğunda biz oradayız.</h2>
        <p class="drop">Kulübümüz kurulduğu 1998 yılından bu yana deprem, sel ve çığ gibi doğal afetlerde ve dağda kaybolan kişilerin aranmasında görev aldı. Bu süreçte <span data-stat="aramaKurtarma"><?= club_stat('aramaKurtarma') ?></span>’ten fazla arama-kurtarma çalışmasına katıldık.</p>
        <p>2025 yılından bu yana AFAD akreditasyonu için çalışıyoruz. Amacımız, birimimizin resmi afet müdahale sistemi içinde akredite bir ekip olarak görev almasıdır.</p>
        <p>Düzenli tatbikatlar, ilk yardım eğitimleri ve teknik kurtarma çalışmaları yaparız. Birime katılmak isteyen üyeler, kapsamlı bir eğitim ve değerlendirme sürecinden geçer.</p>
      </div>
      <div class="disc-quickfacts reveal">
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span><span class="qf-label">Deneyim</span><span class="qf-value">1998’den Beri</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span><span class="qf-label">Operasyon</span><span class="qf-value"><span data-stat="aramaKurtarma"><?= club_stat('aramaKurtarma') ?></span>+</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></span><span class="qf-label">Tatbikat</span><span class="qf-value">Ayda 1</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg></span><span class="qf-label">AFAD</span><span class="qf-value">Akreditasyon Sürecinde</span></div>
      </div>
    </div>
  </div>
</section>
<section class="corp-section alt" data-screen-label="03 Seviyeler">
  <div class="container">
    <div class="corp-head reveal">
      <span class="eyebrow">Birim Yapısı</span>
      <h2>Nasıl çalışıyoruz?</h2>
      <p>Üç temel çalışma alanında eğitim ve operasyon yürütürüz.</p>
    </div>
    <div class="level-grid">
      <div class="level-card reveal">
        <span class="lv-tag education">Eğitim</span>
        <h3>İlk Yardım & Temel</h3>
        <p>Tüm üyelere açık ilk yardım ve dağ güvenliği eğitimleri.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Herkese açık</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Sertifikalı eğitmen</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag medium">Operasyon</span>
        <h3>Saha Ekibi</h3>
        <p>Arama, sedye taşıma ve tahliye operasyonları.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Birim eğitimi şart</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Fiziksel yeterlilik</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag hard">İleri</span>
        <h3>Teknik Kurtarma</h3>
        <p>İp sistemleriyle yüksek açı ve uçurum kurtarma.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> İleri sertifika</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Düzenli tatbikat</li></ul>
      </div>
    </div>
  </div>
</section>
<section class="corp-section" data-screen-label="04 Yaklaşan">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:40px">
      <div class="section-head-left">
        <span class="eyebrow">Birim</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Yaklaşan Tatbikat & Eğitimler</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('faaliyetler')) ?>?cat=arama-kurtarma" class="btn-text">Tümünü Gör <span class="arrow">→</span></a>
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
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Arama Kurtarma Anıları</h2>
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
      <span class="eyebrow center" style="color:#fff;justify-content:center">Gönüllü Ol</span>
      <h2>Bir hayat kurtarmak için eğitil.</h2>
      <p>Arama-kurtarma birimimize katılmak ya da ilk yardım eğitimine gelmek için bize ulaş.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('iletisim')) ?>" class="btn btn-primary">Birime Katıl <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('faaliyetler')) ?>?cat=arama-kurtarma" class="btn btn-ghost">Tatbikatlar</a>
      </div>
    </div>
  </div>
</section>
