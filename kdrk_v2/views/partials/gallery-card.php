<?php
/**
 * .gallery-card (CLAUDE.md §5) — galeri listesi ve albüm sayfasındaki "diğer albümler" bu partial'ı kullanır.
 * Faaliyet kartıyla aynı kalıp (.activity-card iskeleti):
 * görsel [ay·yıl · paylaş · fotoğraf sayısı] → konum → başlık → tür · zorluk → açıklama → Faaliyet | Fotoğrafları gör
 * @var array  $g     Gallery::decorate() çıktısı
 * @var string $sizes görsel genişliği (srcset sizes)
 */
$sizes ??= '(min-width: 1280px) 400px, (min-width: 1024px) 31vw, (min-width: 641px) 48vw, 100vw';
$about = trim((string) ($g['description'] ?? ''));
?>
<article class="activity-card gallery-card reveal">
  <div class="activity-img">
    <span class="activity-img-fallback" aria-hidden="true"><svg viewBox="0 0 64 40" fill="none"><path d="M2 38 22 8l9 13 6-8 25 25z" fill="currentColor" opacity=".18"/><path d="M2 38 22 8l9 13 6-8 25 25M17 15.5l5 3.5 4-3" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg></span>
    <div class="activity-media"><?= img_tag($g['cover'], $sizes, 900, $g['title']) ?></div>
<?php if ($g['shot_on']): ?>
    <div class="activity-date"><span class="day"><?= e(tr_month($g['shot_on'])) ?></span><span class="month"><?= e(substr($g['shot_on'], 0, 4)) ?></span></div>
<?php endif ?>
    <?= $this->partial('partials/card-share', ['url' => $g['url'], 'title' => $g['title'], 'label' => 'Albümü paylaş']) ?>
    <span class="activity-status is-count"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg><?= (int) $g['photo_count'] ?> fotoğraf</span>
  </div>
  <div class="activity-body">
<?php if ($g['loc']): ?>
    <div class="activity-loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg><span><?= e($g['loc']) ?></span></div>
<?php endif ?>
    <h3><a href="<?= e($g['url']) ?>"><?= e($g['title']) ?></a></h3>
<?php if ($g['category_text'] || $g['difficulty_text']): ?>
    <div class="activity-meta">
<?php if ($g['category_text']): ?><span class="activity-category"><?= e($g['category_text']) ?></span><?php endif ?>
<?php if ($g['category_text'] && $g['difficulty_text']): ?><span class="sep" aria-hidden="true">·</span><?php endif ?>
<?php if ($g['difficulty_text']): ?><span class="activity-level <?= e($g['difficulty_class']) ?>"><?= e($g['difficulty_text']) ?></span><?php endif ?>
    </div>
<?php endif ?>
<?php if ($about !== ''): ?>
    <p><?= e($about) ?></p>
<?php endif ?>
  </div>
  <div class="activity-actions<?= $g['activity_url'] ? '' : ' is-single' ?>">
<?php if ($g['activity_url']): ?>
    <a class="card-btn" href="<?= e($g['activity_url']) ?>" aria-label="<?= e($g['title']) ?>: faaliyet sayfası">Faaliyet</a>
<?php endif ?>
    <a class="card-btn is-primary" href="<?= e($g['url']) ?>" aria-label="<?= e($g['title']) ?>: fotoğrafları gör">Fotoğrafları gör</a>
  </div>
</article>
