<?php
/**
 * .news-card — Duyuru & Haber liste kartı (görsel + tür + tarih + başlık + özet).
 * @var array $n News::decorate() çıktısı
 */
?>
<a href="<?= e($n['url']) ?>" class="news-card reveal">
  <div class="news-card-img">
<?php if ($n['cover_image']): ?>
    <div><?= img_tag($n['cover_image'], '(min-width: 1280px) 400px, (min-width: 1024px) 31vw, (min-width: 641px) 48vw, 100vw', 900, $n['title']) ?></div>
<?php else: ?>
    <div class="topo-watermark"></div>
<?php endif ?>
  </div>
  <div class="news-card-body">
    <div class="news-card-meta">
      <span class="pill pill-outline"><?= e($n['category_text']) ?></span>
      <span class="meta"><?= e(tr_date($n['published_on'])) ?><?= $n['minutes'] ? ' · ' . (int) $n['minutes'] . ' DK' : '' ?></span>
    </div>
    <h2 class="news-card-title"><?= e($n['title']) ?></h2>
<?php if ($n['excerpt']): ?>
    <p><?= e($n['excerpt']) ?></p>
<?php endif ?>
    <span class="btn-text">Devamını oku <span class="arrow" aria-hidden="true">→</span></span>
  </div>
</a>
