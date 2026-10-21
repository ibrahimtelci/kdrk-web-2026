<?php
/**
 * Albüm sayfası: masonry + lightbox. Kutucuklar gerçek linktir (JS kapalıyken büyük görsel açılır);
 * gallery.js lightbox'a çevirir.
 * @var array $album   Gallery::decorate()
 * @var array $photos  gallery_photos satırları
 * @var array $related aynı yıldan diğer albümler
 */
$n = count($photos);
$eyebrow = implode(' · ', array_filter([$album['year'], $album['category_text'] ? tr_upper($album['category_text']) : null, $album['shot_on'] ? tr_upper(tr_date($album['shot_on'])) : null]));
$lb = array_map(static fn (array $p, int $i) => [
    'src'     => img($p['path'], 1600),
    'srcset'  => img_srcset($p['path']),
    'full'    => img($p['path'], 2400),
    'caption' => $p['caption'] ?? '',
    'credit'  => $p['credit'] ?? '',
], $photos, array_keys($photos));
?>
<section class="gd-hero" data-screen-label="01 Hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a>
      <span class="sep" aria-hidden="true">›</span>
      <a href="<?= e(url('galeri')) ?>">Galeri</a>
<?php if ($album['year']): ?>
      <span class="sep" aria-hidden="true">›</span>
      <a href="<?= e(url('galeri', ['yil' => $album['year']])) ?>"><?= $album['year'] ?></a>
<?php endif ?>
      <span class="sep" aria-hidden="true">›</span>
      <span class="current" aria-current="page"><?= e($album['title']) ?></span>
    </nav>
    <div class="gd-hero-grid">
      <div>
        <span class="eyebrow">Faaliyet Galerisi</span>
        <h1><?= e($album['title']) ?></h1>
<?php if ($album['description']): ?>
        <p class="gd-lede"><?= e($album['description']) ?></p>
<?php endif ?>
        <div class="gd-meta-line corp-hero-stamp">
<?php if ($album['shot_on']): ?>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> <strong><?= e(tr_date_long($album['shot_on'])) ?></strong></span>
<?php endif ?>
<?php if ($album['loc']): ?>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> <?= e($album['loc']) ?></span>
<?php endif ?>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> <strong><?= $n ?></strong> fotoğraf</span>
        </div>
<?php if ($tags = \App\Models\Gallery::tagList($album['tags'] ?? null)): ?>
        <ul class="gd-tags" aria-label="Etiketler">
<?php foreach ($tags as $t): ?>
          <li><a href="<?= e(url('galeri', ['q' => $t])) ?>"><?= e($t) ?></a></li>
<?php endforeach ?>
        </ul>
<?php endif ?>
      </div>
<?php if ($album['activity_url']): ?>
      <a href="<?= e($album['activity_url']) ?>" class="gd-cta">Faaliyet Detayını Gör <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
<?php endif ?>
    </div>
  </div>
</section>

<section class="gd-gallery" data-screen-label="02 Fotoğraflar">
  <div class="container">
    <div class="masonry">
<?php foreach ($photos as $i => $p):
    $alt = $p['caption'] ?: $album['title'] . ' · ' . ($i + 1) . '. fotoğraf';
    $ratio = $p['width'] && $p['height'] ? (int) $p['width'] . ' / ' . (int) $p['height'] : '4 / 3'; ?>
      <a class="masonry-tile" href="<?= e(img($p['path'], 2400)) ?>" data-lb-open="<?= $i ?>" style="aspect-ratio:<?= e($ratio) ?>" aria-label="Fotoğrafı büyüt: <?= e($alt) ?>">
        <img src="<?= e(img($p['path'], 900)) ?>"<?php if ($ss = img_srcset($p['path'])): ?> srcset="<?= e($ss) ?>" sizes="(min-width: 1025px) 400px, (min-width: 601px) 50vw, 100vw"<?php endif ?> alt="<?= e($alt) ?>" loading="<?= $i < 3 ? 'eager' : 'lazy' ?>" decoding="async">
<?php if ($p['credit']): ?>
        <span class="masonry-tile-credit">© <?= e($p['credit']) ?></span>
<?php endif ?>
        <span class="masonry-tile-overlay" aria-hidden="true"><span class="masonry-tile-expand"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></span></span>
      </a>
<?php endforeach ?>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="gd-related" data-screen-label="03 Diğer Albümler">
  <div class="container">
    <h2 class="gd-related-title"><?= (int) $album['year'] ?>'den diğer albümler</h2>
    <div class="gd-related-grid">
<?php foreach ($related as $g): ?>
      <?= $this->partial('partials/gallery-card', ['g' => $g, 'sizes' => '(min-width: 901px) 33vw, 100vw']) ?>
<?php endforeach ?>
    </div>
  </div>
</section>
<?php endif ?>

<div class="lightbox" data-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Fotoğraf görüntüleyici" hidden>
  <div class="lb-topbar">
    <div class="lb-title-block">
<?php if ($eyebrow !== ''): ?>
      <span class="eyebrow"><?= e($eyebrow) ?></span>
<?php endif ?>
      <h3><?= e($album['title']) ?></h3>
    </div>
    <div class="lb-counter"><strong data-lb-current>01</strong> <span aria-hidden="true">/</span> <span data-lb-total><?= sprintf('%02d', $n) ?></span></div>
    <div class="lb-actions">
      <a class="lb-action" data-lb-download href="<?= e($lb[0]['full'] ?? url('galeri')) ?>" download aria-label="Fotoğrafı indir" title="İndir"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg></a>
      <button class="lb-action" type="button" data-lb-close aria-label="Kapat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    </div>
  </div>
  <div class="lb-stage">
    <button class="lb-arrow prev" type="button" data-lb-prev aria-label="Önceki fotoğraf"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
    <div class="lb-img-wrap">
      <img class="lb-img" data-lb-img alt="">
      <div class="lb-caption" data-lb-caption aria-live="polite"></div>
    </div>
    <button class="lb-arrow next" type="button" data-lb-next aria-label="Sonraki fotoğraf"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
  </div>
</div>
<script type="application/json" data-lb-data><?= json_encode($lb, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<?php $this->section('scripts') ?>
<script src="<?= e(asset('gallery.js')) ?>" defer></script>
<?php $this->end() ?>
