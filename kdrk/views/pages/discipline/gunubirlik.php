<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep">›</span><a href="<?= e(url('faaliyetler')) ?>">Faaliyetlerimiz</a><span class="sep">›</span><span class="current">Günübirlik Doğa Yürüyüşü</span>
    </nav>
    <span class="eyebrow" style="color:#fff">FAALİYET · 01</span>
    <h1>Günübirlik Doğa Yürüyüşü</h1>
    <p class="lede">En çok katılımlı faaliyetimiz. Yaylalar, göller ve şelaleler arasında tek günlük yürüyüşler — yeni başlayanlara açık.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> YAYLA · GÖL · ŞELALE</div>
  </div>
</section>
<section class="corp-section" data-screen-label="02 Tanım">
  <div class="container">
    <div class="disc-intro-grid">
      <div class="story-text reveal">
        <span class="eyebrow">Bu Disiplin Nedir?</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Doğayla tanışmanın en kolay yolu.</h2>
        <p class="drop">Günübirlik yürüyüşler, sabah çıkıp akşam dönülen, teknik ekipman gerektirmeyen rotalardır. Pokut, Sal, Avusor ve Ayder çevresindeki patikalar başlıca rotalarımızdır.</p>
        <p>Aileler, çocuklar ve ilk kez doğaya çıkacaklar için idealdir. Her yürüyüşte deneyimli bir lider eşlik eder; tempo gruba göre ayarlanır.</p>
      </div>
      <div class="disc-quickfacts reveal">
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21l7-12 4 6 7-9"/><path d="M14 6h7v7"/></svg></span><span class="qf-label">Mesafe</span><span class="qf-value">6–14 km</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span><span class="qf-label">Süre</span><span class="qf-value">4–7 Saat</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg></span><span class="qf-label">Zorluk</span><span class="qf-value">Kolay / Orta</span></div>
        <div class="qf-cell"><span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span><span class="qf-label">Ön Koşul</span><span class="qf-value">Yok</span></div>
      </div>
    </div>
  </div>
</section>
<section class="corp-section alt" data-screen-label="03 Seviyeler">
  <div class="container">
    <div class="corp-head reveal">
      <span class="eyebrow">Rota Tipleri</span>
      <h2>Hangi rota sana göre?</h2>
      <p>Üç farklı zorlukta günübirlik rota seçeneği sunuyoruz.</p>
    </div>
    <div class="level-grid four">
      <div class="level-card reveal">
        <span class="lv-tag education">Eğitim</span>
        <h3>Temel Yürüyüş Eğitimi</h3>
        <p>Yürüyüş tekniği, doğru ekipman, rota okuma ve doğada güvenlik. İlk günübirlik faaliyetten önce.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kulüp üyeliği</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Sağlık beyanı</li>
        </ul>
        <a class="lv-link" href="<?= e(url('disiplin', ['slug' => 'egitim'])) ?>">Eğitim programı →</a>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag easy">Kolay</span>
        <h3>Yayla & Şelale</h3>
        <p>Düz ve kısa, aileye uygun rotalar. Çay molası garantili.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Çocuklara uygun</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Düşük rakım farkı</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag medium">Orta</span>
        <h3>Göl Turu</h3>
        <p>Buzul göllerine orta tempolu, gün boyu süren yürüyüşler.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Temel kondisyon</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Trekking ayakkabısı</li></ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag medium">Orta</span>
        <h3>Sırt & Geçit</h3>
        <p>İki yayla arası sırt yürüyüşü, daha uzun mesafe.</p>
        <ul><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Düzenli yürüyüş</li><li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Gün boyu dayanıklılık</li></ul>
      </div>
    </div>
  </div>
</section>
<section class="corp-section" data-screen-label="04 Yaklaşan">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:40px">
      <div class="section-head-left">
        <span class="eyebrow">Açık Başvurular</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Yaklaşan Günübirlik Yürüyüşler</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('faaliyetler')) ?>?cat=gunubirlik" class="btn-text">Tümünü Gör <span class="arrow">→</span></a>
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
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Günübirlik Doğa Yürüyüşü Anıları</h2>
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
      <span class="eyebrow center" style="color:#fff;justify-content:center">Hadi Çık</span>
      <h2>İlk yürüyüşün bu hafta olabilir.</h2>
      <p>Deneyim gerekmez — sadece rahat ayakkabı ve biraz isteğini getir.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('faaliyetler')) ?>?cat=gunubirlik" class="btn btn-primary">Yürüyüşleri Gör <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('kulup-uyeligi')) ?>" class="btn btn-ghost">Üye Ol</a>
      </div>
    </div>
  </div>
</section>
