<?php
/**
 * Duyuru / haber sayfası — Bilgi Bankası makale düzeni (.kb-hero + .kb-article).
 * @var array $n      News::decorate()
 * @var array $others diğer haberler
 */
$activityUrl = $n['activity_slug'] && $n['activity_published'] ? url('faaliyet', ['slug' => $n['activity_slug']]) : null;
?>
<section class="kb-hero" data-screen-label="01 Başlık">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep" aria-hidden="true">›</span><a href="<?= e(url('duyurular')) ?>">Duyuru &amp; Haberler</a><span class="sep" aria-hidden="true">›</span><span class="current" aria-current="page"><?= e($n['category_text']) ?></span>
    </nav>
    <span class="kb-kicker"><?= e(tr_upper($n['category_text'])) ?></span>
    <h1><?= e($n['title']) ?></h1>
<?php if ($n['excerpt']): ?>
    <p class="kb-sub"><?= e($n['excerpt']) ?></p>
<?php endif ?>
    <div class="kb-meta corp-hero-stamp">
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> <?= e(tr_upper(tr_date($n['published_on']))) ?></span>
<?php if ($n['minutes']): ?>
      <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> <?= (int) $n['minutes'] ?> DK OKUMA</span>
<?php endif ?>
    </div>
  </div>
</section>

<div class="container">
  <article class="kb-article news-article" data-screen-label="02 Metin">
<?php if ($n['cover_image']): ?>
    <figure class="news-article-cover reveal"><?= img_tag($n['cover_image'], '(min-width: 800px) 720px, 100vw', 1600, '', false) ?></figure>
<?php endif ?>
    <section class="reveal">
      <?= paragraphs($n['body']) ?>
    </section>
    <div class="kb-foot reveal">
<?php if ($activityUrl): ?>
      <div class="kf-text"><h4>Faaliyetin ayrıntıları</h4><p>Program, malzeme listesi ve başvuru bilgileri faaliyet sayfasında.</p></div>
      <a href="<?= e($activityUrl) ?>" class="btn btn-primary">Faaliyete Git <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
<?php else: ?>
      <div class="kf-text"><h4>Yaklaşan faaliyetler</h4><p>Takvime göz at, sana uygun faaliyete başvur.</p></div>
      <a href="<?= e(url('faaliyetler')) ?>" class="btn btn-primary">Takvimi Gör <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
<?php endif ?>
    </div>
  </article>
</div>

<?php if ($others): ?>
<section class="corp-section alt news-others" data-screen-label="03 Diğer Duyuru & Haberler">
  <div class="container">
    <div class="section-head reveal"><div class="section-head-left"><span class="eyebrow">Gündem</span><h2>Diğer duyuru ve haberler</h2></div><div class="section-head-right"><a href="<?= e(url('duyurular')) ?>" class="btn-text">Tüm Duyuru &amp; Haberler <span class="arrow" aria-hidden="true">→</span></a></div></div>
    <div class="news-list reveal">
<?php foreach ($others as $o): ?>
      <a href="<?= e($o['url']) ?>" class="news-item">
        <div class="news-thumb"><div><?= img_tag($o['cover_image'], '80px', 400) ?></div></div>
        <div class="news-item-body">
          <span class="pill pill-outline"><?= e($o['category_text']) ?></span>
          <h4><?= e($o['title']) ?></h4>
          <span class="meta"><?= e(tr_date($o['published_on'])) ?></span>
        </div>
      </a>
<?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>
