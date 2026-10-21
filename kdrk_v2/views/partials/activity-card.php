<?php
/**
 * .activity-card (CLAUDE.md §5) — Anasayfa, liste ve disiplin sayfaları bu tek partial'ı kullanır.
 * Sıra: görsel [tarih · paylaş · durum] → konum → başlık → tür · zorluk → açıklama → kontenjan → Ayrıntı | Katıl
 * @var array $a Activity::decorate() çıktısı
 */
$done = in_array($a['status'], ['done', 'past'], true);
$summary = trim((string) ($done && $a['report_summary'] ? $a['report_summary'] : $a['summary']));
$level = $a['difficulty_text'] . ($a['difficulty_level'] ? ' · ' . $a['difficulty_level'] : '');
$percent = (int) round($a['fill'] * 100);
// Kart içi durum satırı (takvim panelindeki kompakt kart): katılım durumu düz cümleyle.
$state = ['full' => 'Kontenjan dolu', 'closed' => 'Katılım kapalı'][$a['status']] ?? $a['status_text'];
// Birincil buton: başvuru varsa forma gider, yoksa durumu yazan pasif buton olur (iş kuralı Activity::status).
$primary = match ($a['status']) {
    'open', 'few' => ['Katıl', $a['url'] . '#basvuru'],
    'full'        => ['Yedek liste', $a['url'] . '#basvuru'],
    'done'        => ['Raporu gör', $a['url']],
    default       => [$state, null],
};
?>
<article class="activity-card reveal<?= $done ? ' is-past' : '' ?>" data-cat="<?= e($a['category']) ?>" data-date="<?= e($a['starts_on']) ?>" data-end="<?= e($a['ends_on'] ?: $a['starts_on']) ?>" data-difficulty="<?= (int) ($a['score'] ?? 0) ?>" data-fill="<?= number_format($a['fill'], 2, '.', '') ?>" data-status="<?= e($a['status']) ?>">
  <div class="activity-img">
    <span class="activity-img-fallback" aria-hidden="true"><svg viewBox="0 0 64 40" fill="none"><path d="M2 38 22 8l9 13 6-8 25 25z" fill="currentColor" opacity=".18"/><path d="M2 38 22 8l9 13 6-8 25 25M17 15.5l5 3.5 4-3" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/></svg></span>
    <div class="activity-media"><?= img_tag($a['cover_image'], '(min-width: 1280px) 400px, (min-width: 1024px) 31vw, (min-width: 641px) 48vw, 100vw', 900, $a['title']) ?></div>
    <div class="activity-date">
      <?php if ($done): ?>
        <span class="day"><?= e(tr_month($a['starts_on'])) ?></span><span class="month"><?= e(substr($a['starts_on'], 0, 4)) ?></span>
      <?php else: ?>
        <span class="day"><?= e(substr($a['starts_on'], 8, 2)) ?></span><span class="month"><?= e(tr_month($a['starts_on'])) ?></span>
      <?php endif ?>
    </div>
    <?= $this->partial('partials/social-share-button', ['a' => $a, 'icon' => true]) ?>
    <span class="activity-status <?= e($a['status']) ?>"><?= e($a['status_text']) ?></span>
  </div>
  <div class="activity-body">
    <?php if (trim((string) $a['location']) !== ''): ?><div class="activity-loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg><span><?= e($a['location']) ?></span></div><?php endif ?>
    <h3><a href="<?= e($a['url']) ?>"><?= e($a['title']) ?></a></h3>
    <div class="activity-meta"><span class="activity-category"><?= e($a['category_text']) ?></span><span class="sep" aria-hidden="true">·</span><span class="activity-level <?= e($a['difficulty_class']) ?>"><?= e($level) ?></span></div>
    <?php if ($summary !== ''): ?><p><?= e($summary) ?></p><?php endif ?>
    <div class="activity-quota">
      <span class="quota-status <?= e($a['status']) ?>"><?= e($state) ?></span>
      <?php if ($done): ?>
        <span class="quota-note"><?= $a['report_participants'] ? (int) $a['report_participants'] . ' kişi katıldı' : 'Rapor ve fotoğraflar' ?></span>
      <?php elseif ((int) $a['quota'] > 0): ?>
        <div class="quota-line"><span class="count"><?= (int) $a['taken'] ?> / <?= (int) $a['quota'] ?> kişi</span><span>%<?= $percent ?></span></div>
        <div class="progress-bar" role="img" aria-label="<?= (int) $a['quota'] ?> kişilik kontenjanın yüzde <?= $percent ?>'i dolu"><div class="progress-fill<?= in_array($a['status'], ['full', 'closed', 'postponed', 'cancelled'], true) ? ' is-muted' : '' ?>" style="width:<?= $percent ?>%"></div></div>
      <?php else: ?>
        <span class="quota-note">Kontenjan sınırı yok</span>
      <?php endif ?>
    </div>
  </div>
  <div class="activity-actions">
    <a class="card-btn" href="<?= e($a['url']) ?>" aria-label="<?= e($a['title']) ?> ayrıntıları">Ayrıntı</a>
    <?php if ($primary[1] !== null): ?>
    <a class="card-btn is-primary" href="<?= e($primary[1]) ?>" aria-label="<?= e($a['title']) ?>: <?= e($primary[0]) ?>"><?= e($primary[0]) ?></a>
    <?php else: ?>
    <span class="card-btn is-disabled" aria-disabled="true"><?= e($primary[0]) ?></span>
    <?php endif ?>
  </div>
</article>
