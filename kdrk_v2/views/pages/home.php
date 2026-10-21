<?php
/**
 * Anasayfa
 * @var array $banners  tüm aktif slaytlar (Banner::visible); faaliyet slaytında $b['activity'] dolu
 * @var array $upcoming en yakın 3 faaliyet
 * @var array $news     yayındaki son duyurular (ilki öne çıkan)
 */
$arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg>';
$instagram = (string) setting('social_instagram', '');
$whatsapp = (string) setting('social_whatsapp', '');
?>
<?php if ($banners): $total = count($banners); ?>
<section class="hero always-dark" data-hero-slider data-duration="7000" aria-roledescription="carousel" aria-label="Öne çıkanlar">
  <div class="hero-topo tl"></div><div class="hero-topo br"></div>
<?php foreach ($banners as $i => $b): ?>
  <div class="hero-slide<?= $i === 0 ? ' is-active' : '' ?>" data-slide role="group" aria-roledescription="slayt" aria-label="<?= $i + 1 ?> / <?= $total ?>">
    <div class="hero-slide-img"><?= img_tag($b['image'], '100vw', 1600, '', $i > 0) ?></div>
    <div class="hero-slide-overlay"></div>
    <div class="container hero-content">
      <div class="hero-text">
<?php if ($b['eyebrow']): ?>
        <span class="eyebrow"><?= e(fill_stats($b['eyebrow'])) ?></span>
<?php endif ?>
        <?php /* Yalnız ilk slaytta h1 (CLAUDE.md §4) */ ?>
        <<?= $i === 0 ? 'h1' : 'h2' ?> class="hero-title"><?= e(fill_stats($b['title'])) ?></<?= $i === 0 ? 'h1' : 'h2' ?>>
<?php if ($b['text']): ?>
        <p><?= e(fill_stats($b['text'])) ?></p>
<?php endif ?>
<?php if ($a = $b['activity']): ?>
        <div class="hero-meta">
          <span class="activity-difficulty <?= e($a['difficulty_class']) ?>"><?php if ($a['difficulty_level']): ?><span class="lv"><?= e($a['difficulty_level']) ?></span><?php endif ?><?= e($a['difficulty_text']) ?></span>
          <span class="activity-status is-inline <?= e($a['status']) ?>"><?= e($a['status_text']) ?></span>
        </div>
<?php endif ?>
<?php if ($b['cta_url'] || $b['cta2_url']): ?>
        <div class="hero-actions">
<?php if ($b['cta_url']): ?>
          <a href="<?= e(site_url($b['cta_url'])) ?>" class="btn btn-primary"><?= e($b['cta_label']) ?> <?= $arrow ?></a>
<?php endif ?>
<?php if ($b['cta2_url']): ?>
          <a href="<?= e(site_url($b['cta2_url'])) ?>" class="btn btn-ghost"><?= e($b['cta2_label']) ?></a>
<?php endif ?>
        </div>
<?php endif ?>
      </div>
    </div>
    <div class="hero-stamp" aria-hidden="true"><span><?= e(fill_stats($b['stamp_1'])) ?></span><span><?= e(fill_stats($b['stamp_2'])) ?></span><span class="stamp-bar"></span></div>
  </div>
<?php endforeach ?>
<?php if ($total > 1): ?>
  <div class="hero-counter" aria-hidden="true"><span class="current" data-counter-current>01</span><span>/</span><span data-counter-total><?= sprintf('%02d', $total) ?></span></div>
  <button class="hero-arrow prev" data-prev aria-label="Önceki slayt"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
  <button class="hero-arrow next" data-next aria-label="Sonraki slayt"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button>
<?php endif ?>
<?php if ($instagram !== '' || $whatsapp !== ''): ?>
  <div class="hero-join" aria-label="Bize katıl">
    <span class="hero-join-label">Bize katıl</span>
<?php if ($instagram !== ''): ?>
    <a href="<?= e($instagram) ?>" target="_blank" rel="noopener" class="hero-join-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg>Instagram</a>
<?php endif ?>
<?php if ($whatsapp !== ''): ?>
    <a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="hero-join-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z"/></svg>WhatsApp Topluluk</a>
<?php endif ?>
  </div>
<?php endif ?>
<?php if ($total > 1): ?>
  <div class="hero-controls">
    <button class="hero-pause" data-pause aria-pressed="false" aria-label="Otomatik geçişi durdur"><svg class="i-pause" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg><svg class="i-play" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg></button>
    <div class="hero-dots">
<?php for ($i = 1; $i <= $total; $i++): ?>
      <button data-dot aria-label="<?= $i ?>. slayt"><span class="dot-track"><span class="dot-progress"></span></span></button>
<?php endfor ?>
    </div>
  </div>
<?php endif ?>
</section>
<?php endif ?>

<section class="section section-upcoming force-dark" id="takvim" data-screen-label="01 Yaklaşan Faaliyetler">
  <div class="topo-watermark"></div>
  <div class="container">
    <div class="section-head reveal">
      <div class="section-head-left">
        <span class="eyebrow">Başvuruya Açık Faaliyetler</span>
        <h2>Yaklaşan Faaliyetler</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('faaliyetler')) ?>" class="btn-text">Tümünü Gör <span class="arrow">→</span></a>
<?php if ($upcoming):
    $first = $upcoming[0]['starts_on'];
    $last = end($upcoming)['starts_on'];
    $range = substr($first, 0, 4) === substr($last, 0, 4)
        ? tr_month($first) . ($first !== $last && tr_month($first) !== tr_month($last) ? '—' . tr_month($last) : '') . ' ' . substr($last, 0, 4)
        : tr_month($first) . ' ' . substr($first, 0, 4) . '—' . tr_month($last) . ' ' . substr($last, 0, 4);
?>
        <span class="mono"><?= e($range) ?></span>
<?php endif ?>
      </div>
    </div>

<?php if ($upcoming): ?>
    <div class="activities-grid">
<?php foreach ($upcoming as $a): ?>
      <?= $this->partial('partials/activity-card', ['a' => $a]) ?>
<?php endforeach ?>
    </div>
    <div class="activities-more reveal">
      <a href="<?= e(url('faaliyetler')) ?>" class="btn btn-ghost">Tüm Faaliyetleri Gör <span class="arrow" aria-hidden="true">→</span></a>
    </div>
<?php else: ?>
    <div class="activities-empty reveal">
      <p>Şu an başvuruya açık faaliyet yok. Yeni program duyurulduğunda burada görünecek.</p>
      <a href="<?= e(url('faaliyetler', ['tab' => 'gecmis'])) ?>" class="btn-text">Geçmiş faaliyetler <span class="arrow" aria-hidden="true">→</span></a>
    </div>
<?php endif ?>
  </div>
</section>


<!-- ============================================================
     SECTION 2 — Faaliyetlerimiz (LIGHT BENTO)
     ============================================================ -->
<section class="section section-disciplines force-light" id="faaliyetlerimiz" data-screen-label="02 Faaliyetlerimiz">
  <div class="container">
    <div class="head-center reveal">
      <span class="eyebrow center">Ne Yapıyoruz</span>
      <h2>Dağda ve karda.</h2>
      <p>Yedi disiplin, <span data-stat="yil"><?= club_stat('yil') ?></span> yıllık deneyim. Yeni başlayandan deneyimli dağcıya.</p>
    </div>

    <div class="bento reveal">
      <!-- FEATURE: Günübirlik (2x2) -->
      <a href="<?= e(url('disiplin', ['slug' => 'gunubirlik'])) ?>" class="bento-tile feature">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg>
        </span>
        <div class="bento-tile-content">
          <span class="tile-eyebrow">01 · En Çok Katılım</span>
          <h3>Günübirlik Doğa Yürüyüşü</h3>
          <p class="sub">En çok katılımlı faaliyetimiz. Yeni başlayanlara ideal — yayla, göl, şelale rotaları.</p>
        </div>
      </a>

      <!-- Dağcılık -->
      <a href="<?= e(url('disiplin', ['slug' => 'dagcilik'])) ?>" class="bento-tile t-mountain">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></span>
        <div class="bento-tile-content">
          <h3>Dağcılık</h3>
          <p class="sub">Çok günlü zirve tırmanışları, ip teknikleri.</p>
        </div>
      </a>

      <!-- Spor Tırmanış -->
      <a href="<?= e(url('disiplin', ['slug' => 'spor-tirmanis'])) ?>" class="bento-tile t-climb">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1522163182402-834f871fd851', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></span>
        <div class="bento-tile-content">
          <h3>Spor Tırmanış</h3>
          <p class="sub">Kayalık ve salon tırmanış antrenmanları.</p>
        </div>
      </a>

      <!-- Dağ Kayağı (geniş) -->
      <a href="<?= e(url('disiplin', ['slug' => 'dag-kayagi'])) ?>" class="bento-tile t-ski">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1551524559-8af4e6624178', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></span>
        <div class="bento-tile-content">
          <h3>Dağ Kayağı</h3>
          <p class="sub">Kış mevsimi boyunca skitur, tur kayağı ve yamaç inişleri.</p>
        </div>
      </a>

      <!-- Eğitim -->
      <a href="<?= e(url('disiplin', ['slug' => 'egitim'])) ?>" class="bento-tile t-edu">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1551632811-561732d1e306', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></span>
        <div class="bento-tile-content">
          <h3>Eğitim Programı</h3>
          <p class="sub">TDF onaylı yaz/kış dağcılık eğitimleri.</p>
        </div>
      </a>

      <!-- Sosyal -->
      <a href="<?= e(url('disiplin', ['slug' => 'sosyal-kulturel'])) ?>" class="bento-tile t-social">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1496080174650-637e3f22fa03', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></span>
        <div class="bento-tile-content">
          <h3>Sosyal-Kültürel</h3>
          <p class="sub">Söyleşiler, film geceleri, kamp etkinlikleri.</p>
        </div>
      </a>

      <!-- Arama Kurtarma (full-width) -->
      <a href="<?= e(url('disiplin', ['slug' => 'arama-kurtarma'])) ?>" class="bento-tile t-rescue">
        <div class="bento-tile-img"><?= img_tag('https://images.unsplash.com/photo-1605540436563-5bca919ae766', '(min-width: 1024px) 50vw, 100vw') ?></div>
        <span class="arrow-btn" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></span>
        <div class="bento-tile-content">
          <span class="tile-eyebrow">07 · Sertifikalı Ekip</span>
          <h3>Arama Kurtarma</h3>
          <p class="sub">Sertifikalı ekibimizle bölgemizde topluma hizmet — KDRK arama kurtarma birimi.</p>
        </div>
      </a>
    </div>
  </div>
</section>

<!-- ============================================================
     SECTION 3 — Rakamlarla KDRK (DARK PARALLAX)
     ============================================================ -->
<section class="section section-stats" id="rakamlar" data-screen-label="03 Rakamlarla KDRK">
  <div class="stats-mountains" data-parallax></div>
  <div class="container">
    <div class="head-center reveal">
      <span class="eyebrow center">İz Bıraktık</span>
      <h2><span data-stat="yil"><?= club_stat('yil') ?></span> yılda biriken deneyim.</h2>
    </div>

    <div class="stats-row reveal">
      <div class="stat">
        <div class="stat-number"><span data-stat="zirve" data-odometer="<?= club_stat('zirve') ?>">0</span><span class="plus">+</span></div>
        <div class="stat-label">Zirve Tırmanışı</div>
      </div>
      <div class="stat">
        <div class="stat-number"><span data-stat="yuruyus" data-odometer="<?= club_stat('yuruyus') ?>">0</span><span class="plus">+</span></div>
        <div class="stat-label">Doğa Yürüyüşü</div>
      </div>
      
      <div class="stat">
        <div class="stat-number"><span data-stat="aramaKurtarma" data-odometer="<?= club_stat('aramaKurtarma') ?>">0</span><span class="plus">+</span></div>
        <div class="stat-label">Arama-Kurtarma</div>
      </div>
      <div class="stat">
        <div class="stat-number"><span data-stat="yil" data-odometer="<?= club_stat('yil') ?>">0</span></div>
        <div class="stat-label">Yıl Tecrübe</div>
      </div>
    </div>
  </div>
</section>

<?php
$feature = $news[0] ?? null;
$rest = array_slice($news, 1, 4);
?>
<?php if ($feature): ?>
<!-- ============================================================
     SECTION 4 — Duyuru & Haberler (LIGHT EDITORIAL)
     ============================================================ -->
<section class="section section-news force-light" id="haberler" data-screen-label="04 Duyuru & Haberler">
  <div class="container">
    <div class="section-head reveal">
      <div class="section-head-left">
        <span class="eyebrow">Gündem</span>
        <h2>Duyuru &amp; Haberler</h2>
      </div>
      <div class="section-head-right">
        <a href="<?= e(url('duyurular')) ?>" class="btn-text">Tüm Duyuru &amp; Haberler <span class="arrow" aria-hidden="true">→</span></a>
      </div>
    </div>

    <div class="news-grid">
      <a href="<?= e($feature['url']) ?>" class="news-feature reveal">
        <div class="news-feature-cover">
          <div><?= img_tag($feature['cover_image'], '(min-width: 1280px) 700px, (min-width: 1024px) 55vw, 100vw') ?></div>
        </div>
        <div class="flex gap-12 items-center" style="flex-wrap:wrap">
          <span class="pill pill-accent"><?= e($feature['category_text']) ?></span>
          <span class="meta mono"><?= e(tr_date($feature['published_on'])) ?><?= $feature['minutes'] ? ' · ' . (int) $feature['minutes'] . ' DK OKUMA' : '' ?></span>
        </div>
        <h3><?= e($feature['title']) ?></h3>
<?php if ($feature['excerpt']): ?>
        <p style="font-size:16px;line-height:1.6;max-width:560px"><?= e($feature['excerpt']) ?></p>
<?php endif ?>
      </a>

<?php if ($rest): ?>
      <div class="news-list reveal">
<?php foreach ($rest as $n): ?>
        <a href="<?= e($n['url']) ?>" class="news-item">
          <div class="news-thumb"><div><?= img_tag($n['cover_image'], '80px', 400) ?></div></div>
          <div class="news-item-body">
            <span class="pill pill-outline"><?= e($n['category_text']) ?></span>
            <h4><?= e($n['title']) ?></h4>
            <span class="meta"><?= e(tr_date($n['published_on'])) ?></span>
          </div>
        </a>
<?php endforeach ?>
      </div>
<?php endif ?>
    </div>
  </div>
</section>
<?php endif ?>

<?php if ($instagram !== '' || $whatsapp !== ''): ?>
<!-- ============================================================
     SECTION 5 — Bizi Takip Et (DARK FULL-BLEED)
     ============================================================ -->
<section class="section section-follow" id="sosyal-takip" data-screen-label="05 Bizi Takip Et">
  <div class="bg" data-parallax></div>
  <div class="bg-overlay"></div>
  <div class="container">
    <div class="head-center reveal">
      <span class="eyebrow center">Sahadan Canlı</span>
      <h2>Faaliyetlerimizi günü gününe takip et.</h2>
      <p>Sosyal medya hesaplarımızda yayla yürüyüşlerinden zirve anılarına her şey paylaşılır.</p>
    </div>

    <div class="socials-row reveal">
<?php if ($instagram !== ''): ?>
      <a href="<?= e($instagram) ?>" target="_blank" rel="noopener" class="social-block">
        <span class="social-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></span>
        <span class="label">Instagram</span>
      </a>
<?php endif ?>
<?php if ($whatsapp !== ''): ?>
      <a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="social-block">
        <span class="social-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z"/></svg></span>
        <span class="label">WhatsApp Topluluk</span>
      </a>
<?php endif ?>
    </div>
  </div>
</section>
<?php endif ?>


<!-- ============================================================
     FOOTER
     ============================================================ -->

<?php $this->section('scripts') ?>
<script>
  (function(){
    const els = document.querySelectorAll('[data-parallax]');
    if (!els.length || matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    let ticking = false;
    function update() {
      const sy = window.scrollY;
      els.forEach(el => {
        const r = el.getBoundingClientRect();
        const center = r.top + r.height / 2;
        const off = (window.innerHeight / 2 - center) * 0.08;
        el.style.transform = `translateY(${off.toFixed(1)}px)`;
      });
      ticking = false;
    }
    window.addEventListener('scroll', () => {
      if (!ticking) { requestAnimationFrame(update); ticking = true; }
    }, { passive: true });
    update();
  })();
</script>
<?php $this->end() ?>
