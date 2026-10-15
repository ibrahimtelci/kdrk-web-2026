<?php
/**
 * .gallery-card (CLAUDE.md §5) — galeri listesi ve albüm sayfasındaki "diğer albümler" bu partial'ı kullanır.
 * @var array  $g     Gallery::decorate() çıktısı
 * @var string $sizes görsel genişliği (srcset sizes)
 */
$sizes ??= '(min-width: 1280px) 310px, (min-width: 901px) 25vw, (min-width: 541px) 50vw, 100vw';
?>
<a href="<?= e($g['url']) ?>" class="gallery-card reveal">
  <div class="gallery-card-img">
    <div><?= img_tag($g['cover'], $sizes) ?></div>
    <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> <?= (int) $g['photo_count'] ?> FOTOĞRAF</span>
  </div>
  <div class="gallery-card-body">
    <div>
      <h3><?= e($g['title']) ?></h3>
      <div class="gallery-card-meta">
<?php if ($g['shot_on']): ?>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> <?= e(tr_date($g['shot_on'])) ?></span>
<?php endif ?>
<?php if ($g['loc']): ?>
        <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> <?= e($g['loc']) ?></span>
<?php endif ?>
      </div>
    </div>
<?php if ($g['difficulty_text'] || $g['category_text']): ?>
    <div class="gallery-card-tags">
<?php if ($g['difficulty_text']): ?>
      <span class="activity-difficulty <?= e($g['difficulty_class']) ?> pill"><?= e($g['difficulty_text']) ?></span>
<?php endif ?>
<?php if ($g['category_text']): ?>
      <span class="pill cat"><?= e($g['category_text']) ?></span>
<?php endif ?>
    </div>
<?php endif ?>
  </div>
</a>
