<?php
/**
 * .news-card — Duyuru & Haber liste kartı. Faaliyet kartıyla aynı kalıp (.activity-card iskeleti):
 * görsel [gün·ay · paylaş · tür] → yayın tarihi · okuma süresi → başlık → özet → Faaliyet | Devamını oku
 * @var array $n News::decorate() çıktısı
 */
$activityUrl = $n['activity_slug'] && $n['activity_published'] ? url('faaliyet', ['slug' => $n['activity_slug']]) : null;
// Haberin kendi adresi zaten faaliyet sayfasıysa ikinci buton tekrar olur.
if ($activityUrl === $n['url']) {
    $activityUrl = null;
}
?>
<article class="activity-card news-card reveal">
  <div class="activity-img">
    <span class="activity-img-fallback" aria-hidden="true"><svg viewBox="0 0 64 40" fill="none"><path d="M2 38 22 8l9 13 6-8 25 25z" fill="currentColor" opacity=".18"/><path d="M2 38 22 8l9 13 6-8 25 25M17 15.5l5 3.5 4-3" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg></span>
<?php if ($n['cover_image']): ?>
    <div class="activity-media"><?= img_tag($n['cover_image'], '(min-width: 1280px) 400px, (min-width: 1024px) 31vw, (min-width: 641px) 48vw, 100vw', 900, $n['title']) ?></div>
<?php endif ?>
    <div class="activity-date"><span class="day"><?= e(substr($n['published_on'], 8, 2)) ?></span><span class="month"><?= e(tr_month($n['published_on'])) ?></span></div>
    <?= $this->partial('partials/card-share', ['url' => $n['url'], 'title' => $n['title'], 'label' => $n['category'] === 'duyuru' ? 'Duyuruyu paylaş' : 'Haberi paylaş']) ?>
    <span class="activity-status type-<?= e($n['category']) ?>"><?= e($n['category_text']) ?></span>
  </div>
  <div class="activity-body">
    <div class="activity-loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/></svg><span><?= e(tr_date($n['published_on'])) ?><?= $n['minutes'] ? ' · ' . (int) $n['minutes'] . ' dk okuma' : '' ?></span></div>
    <h2 class="news-card-title"><a class="news-card-link" href="<?= e($n['url']) ?>"><?= e($n['title']) ?></a></h2>
<?php if ($n['excerpt']): ?>
    <p><?= e($n['excerpt']) ?></p>
<?php endif ?>
  </div>
  <div class="activity-actions<?= $activityUrl ? '' : ' is-single' ?>">
<?php if ($activityUrl): ?>
    <a class="card-btn" href="<?= e($activityUrl) ?>" aria-label="<?= e($n['title']) ?>: ilgili faaliyet">Faaliyet</a>
<?php endif ?>
    <a class="card-btn is-primary" href="<?= e($n['url']) ?>" aria-label="<?= e($n['title']) ?>: devamını oku">Devamını oku</a>
  </div>
</article>
