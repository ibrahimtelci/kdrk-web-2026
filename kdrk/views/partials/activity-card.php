<?php
/**
 * .activity-card (CLAUDE.md §5) — Anasayfa, liste ve disiplin sayfaları bu tek partial'ı kullanır.
 * @var array $a Activity::decorate() çıktısı
 */
$done = $a['status'] === 'done';
$buttons = ['open' => 'Katıl', 'few' => 'Katıl', 'full' => 'Yedek liste', 'done' => 'Raporu gör'];
?>
<a href="<?= e($a['url']) ?>" class="activity-card reveal<?= $done ? ' is-past' : '' ?>" data-cat="<?= e($a['category']) ?>" data-date="<?= e($a['starts_on']) ?>" data-difficulty="<?= (int) $a['difficulty'] ?>" data-fill="<?= number_format($a['fill'], 2, '.', '') ?>" data-status="<?= e($a['status']) ?>">
  <div class="activity-img">
    <div><?= img_tag($a['cover_image'], '(min-width: 1280px) 400px, (min-width: 1024px) 31vw, (min-width: 641px) 48vw, 100vw') ?></div>
    <div class="activity-date">
      <?php if ($done): ?>
        <span class="day"><?= e(tr_month($a['starts_on'])) ?></span><span class="month"><?= e(substr($a['starts_on'], 0, 4)) ?></span>
      <?php else: ?>
        <span class="day"><?= e(substr($a['starts_on'], 8, 2)) ?></span><span class="month"><?= e(tr_month($a['starts_on'])) ?></span>
      <?php endif ?>
    </div>
    <span class="activity-difficulty <?= e($a['difficulty_class']) ?>"><?php if ($a['difficulty_level']): ?><span class="lv"><?= e($a['difficulty_level']) ?></span><?php endif ?><?= e($a['difficulty_text']) ?></span>
    <span class="activity-status <?= e($a['status']) ?>"><?= e($a['status_text']) ?></span>
  </div>
  <div class="activity-body">
    <div class="activity-loc"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg><?= e($a['location']) ?></div>
    <h3><?= e($a['title']) ?></h3>
    <p><?= e($done && $a['report_summary'] ? $a['report_summary'] : $a['summary']) ?></p>
  </div>
  <div class="activity-footer">
    <?php if ($done): ?>
      <span class="past-note">Rapor ve fotoğraflar</span>
    <?php elseif ((int) $a['quota'] > 0): ?>
      <div class="attendees">
        <div class="attendees-line"><span class="count"><?= (int) $a['taken'] ?> / <?= (int) $a['quota'] ?> KİŞİ</span><span><?= (int) round($a['fill'] * 100) ?>%</span></div>
        <div class="progress-bar"><div class="progress-fill<?= in_array($a['status'], ['full', 'closed', 'postponed', 'cancelled'], true) ? ' is-muted' : '' ?>" style="width:<?= (int) round($a['fill'] * 100) ?>%"></div></div>
      </div>
    <?php else: ?>
      <span class="past-note">Kontenjan sınırı yok</span>
    <?php endif ?>
    <span class="btn-text"><?= e($buttons[$a['status']] ?? 'Ayrıntılar') ?> <span class="arrow" aria-hidden="true">→</span></span>
  </div>
</a>
