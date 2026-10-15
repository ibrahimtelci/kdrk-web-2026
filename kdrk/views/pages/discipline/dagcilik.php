<!-- HERO -->
<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep">›</span><a href="<?= e(url('faaliyetler')) ?>">Faaliyetlerimiz</a><span class="sep">›</span><span class="current">Dağcılık</span>
    </nav>
    <span class="eyebrow" style="color:#fff">FAALİYET · 02</span>
    <h1>Dağcılık</h1>
    <p class="lede">Çok günlü zirve tırmanışları, buzul geçişleri ve yüksek irtifa. KDRK'nın en teknik disiplini — hazırlık, ekip ve disiplin ister.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> ZİRVE · İP · KRAMPON</div>
  </div>
</section>

<!-- INTRO + QUICK FACTS -->
<section class="corp-section" data-screen-label="02 Tanım">
  <div class="container">
    <div class="disc-intro-grid">
      <div class="story-text reveal">
        <span class="eyebrow">Bu Disiplin Nedir?</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Zirve, hazırlığın ödülüdür.</h2>
        <p class="drop">Dağcılık, günübirlik yürüyüşün ötesinde; çadırda gecelenen, krampon ve buz baltasıyla aşılan, ip ekibiyle çıkılan çok günlü tırmanışları kapsar. Kaçkar, Verçenik, Bulut Dağı ve çevre zirveler ana sahamızdır.</p>
        <p>Her tırmanış öncesi katılımcı seviyesi değerlendirilir, hava ve rota koşulları izlenir. Amacımız zirveye çıkmak kadar — hatta daha çok — herkesi sağ salim geri getirmektir.</p>
      </div>
      <div class="disc-quickfacts reveal">
        <div class="qf-cell">
          <span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 20l5-9 4 6 3-4 6 7"/><path d="M3 20h18"/></svg></span>
          <span class="qf-label">İrtifa Aralığı</span>
          <span class="qf-value">2.500–3.937 m</span>
        </div>
        <div class="qf-cell">
          <span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
          <span class="qf-label">Süre</span>
          <span class="qf-value">2–4 Gün</span>
        </div>
        <div class="qf-cell">
          <span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg></span>
          <span class="qf-label">Zorluk</span>
          <span class="qf-value">Zor / Çok Zor</span>
        </div>
        <div class="qf-cell">
          <span class="qf-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
          <span class="qf-label">Ön Koşul</span>
          <span class="qf-value">Deneyim Şart</span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- LEVELS -->
<section class="corp-section alt" data-screen-label="03 Seviyeler">
  <div class="container">
    <div class="corp-head reveal">
      <span class="eyebrow">Seviyeler</span>
      <h2>Nereden başlamalı?</h2>
      <p>Dağcılığa adım adım hazırlanırsın. Her seviyenin kendi ön koşulu ve programı var.</p>
    </div>
    <div class="level-grid four">
      <div class="level-card reveal">
        <span class="lv-tag education">Eğitim</span>
        <h3>Yaz Dağcılık Eğitimi</h3>
        <p>TDF onaylı bölgesel eğitim: kayalık geçişler, ip teknikleri, kamp ve rota bulma. Her seviyenin başlangıç noktası.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Kulüp üyeliği</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Temel kondisyon</li>
        </ul>
        <a class="lv-link" href="<?= e(url('disiplin', ['slug' => 'egitim'])) ?>">Eğitim programı →</a>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag easy">Hazırlık</span>
        <h3>Yüksek İrtifa Yürüyüşü</h3>
        <p>Teknik ekipman gerektirmeyen, 2.500 m+ rotalarda kondisyon ve aklimatizasyon.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Düzenli yürüyüş geçmişi</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Temel kamp deneyimi</li>
        </ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag medium">Orta</span>
        <h3>Teknik Tırmanış</h3>
        <p>Krampon, balta ve ip teknikleriyle 3.000 m+ zirveler. Eğitim kampı sonrası.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Yaz dağcılık eğitimi</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> 2+ yüksek irtifa deneyimi</li>
        </ul>
      </div>
      <div class="level-card reveal">
        <span class="lv-tag hard">İleri</span>
        <h3>Zirve Ekibi</h3>
        <p>Buzul aşması, dik kar şutları ve teknik kaya. Kaçkar zirve tırmanışı seviyesi.</p>
        <ul>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> İleri teknik eğitim</li>
          <li><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5L20 7"/></svg> Eğitmen onayı</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- YAKLAŞAN FAALİYETLER -->
<section class="corp-section" data-screen-label="04 Yaklaşan">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:40px">
      <div class="section-head-left">
        <span class="eyebrow">Açık Başvurular</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Yaklaşan Dağcılık Faaliyetleri</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('faaliyetler')) ?>?cat=dagcilik" class="btn-text">Tümünü Gör <span class="arrow">→</span></a>
      </div>
    </div>
    <?= $this->partial('partials/activity-grid') ?>
  </div>
</section>

<!-- GALLERY STRIP -->
<section class="corp-section alt" data-screen-label="05 Galeri">
  <div class="container">
    <div class="section-head reveal" style="margin-bottom:32px">
      <div class="section-head-left">
        <span class="eyebrow">Sahadan</span>
        <h2 style="font-size:clamp(28px,3.4vw,40px)">Dağcılık Anıları</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('galeri')) ?>" class="btn-text">Galeriye Git <span class="arrow">→</span></a>
      </div>
    </div>
    <div class="disc-strip reveal">
      <a href="<?= e(url('galeri-ornek')) ?>"><div style="background-image:url('https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=600&q=80&auto=format&fit=crop')"></div></a>
      <a href="<?= e(url('galeri-ornek')) ?>"><div style="background-image:url('https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?w=600&q=80&auto=format&fit=crop')"></div></a>
      <a href="<?= e(url('galeri-ornek')) ?>"><div style="background-image:url('https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=600&q=80&auto=format&fit=crop')"></div></a>
      <a href="<?= e(url('galeri-ornek')) ?>"><div style="background-image:url('https://images.unsplash.com/photo-1551632811-561732d1e306?w=600&q=80&auto=format&fit=crop')"></div></a>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="corp-section" data-screen-label="06 CTA">
  <div class="container">
    <div class="join-band reveal">
      <div class="topo-watermark"></div>
      <span class="eyebrow center" style="color:#fff;justify-content:center">Hazır mısın?</span>
      <h2>Bir sonraki zirvede yerini ayırt.</h2>
      <p>Dağcılık deneyimin varsa katılım başvurusu yap; yoksa önce eğitim programımızla başla.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('faaliyetler')) ?>?cat=dagcilik" class="btn btn-primary">Dağcılık Faaliyetleri <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('disiplin', ['slug' => 'egitim'])) ?>" class="btn btn-ghost">Eğitim Programı</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
