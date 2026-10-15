<?php
/**
 * Slayt listesi — sıra = Anasayfadaki sıra.
 * @var array $rows Banner::forPanel()
 */
use App\Core\Auth;
use App\Models\Banner;

$canEdit = Auth::canEdit();
$live = array_values(array_filter($rows, static fn ($r) => $r['on_site']));
$extra = count(array_filter($rows, static fn ($r) => $r['state'] === 'Sırada (ilk 3 dışında)'));
$colors = ['Sitede' => 'green', 'Zamanlandı' => 'blue', 'Süresi doldu' => 'gray', 'Kapalı' => 'gray', 'Sırada (ilk 3 dışında)' => 'orange'];
?>
<div class="toolbar">
  <p class="toolbar-note muted">Anasayfada sıradaki ilk <?= Banner::MAX ?> etkin slayt görünür. İlk slayt kulübü tanıtır; yalnız ilk slaytın başlığı sayfanın ana başlığıdır (h1).</p>
  <div class="tb-spacer"></div>
<?php if ($canEdit): ?>
  <a class="btn btn-primary" href="<?= e(url('admin.banners.new')) ?>"><?= icon('plus') ?>Yeni Slayt</a>
<?php endif ?>
</div>

<?php if ($live && $live[0]['show_until']): ?>
<div class="flash flash-error" role="alert"><?= icon('x') ?><span>Sitedeki ilk slaytın bitiş tarihi var (<?= e(tr_date($live[0]['show_until'])) ?>). Tarihi geçebilecek duyuru ilk sıraya konmaz; kulübü tanıtan slaytı en üste taşı.</span></div>
<?php endif ?>
<?php if (!$live): ?>
<div class="flash flash-error" role="alert"><?= icon('x') ?><span>Şu an sitede hiç slayt yok; Anasayfa slaytsız açılıyor.</span></div>
<?php endif ?>
<?php if ($extra): ?>
<div class="flash flash-info"><?= icon('eye') ?><span><?= $extra ?> etkin slayt ilk <?= Banner::MAX ?> dışında kaldığı için sitede görünmüyor.</span></div>
<?php endif ?>

<div class="panel">
<?php if ($rows): ?>
  <ol class="bn-list">
<?php foreach ($rows as $i => $b): ?>
    <li class="bn-item<?= $b['on_site'] ? '' : ' is-off' ?>" id="slayt-<?= (int) $b['id'] ?>">
      <a class="bn-main" href="<?= e(url('admin.banners.edit', ['id' => $b['id']])) ?>">
        <span class="bn-no mono"><?= $i + 1 ?></span>
        <img class="bn-thumb" src="<?= e(img($b['image'], 400)) ?>" alt="" loading="lazy">
        <span class="bn-text">
          <span class="t-title"><?= e(fill_stats($b['title'])) ?></span>
          <span class="t-sub muted"><?= $b['eyebrow'] ? e(fill_stats($b['eyebrow'])) : '—' ?><?php if ($b['show_from'] || $b['show_until']): ?> · <?= $b['show_from'] ? e(tr_date($b['show_from'])) : '…' ?> → <?= $b['show_until'] ? e(tr_date($b['show_until'])) : '…' ?><?php endif ?></span>
        </span>
      </a>
      <span class="chip <?= $colors[$b['state']] ?> dot bn-state"><?= e($b['state']) ?></span>
<?php if ($canEdit): ?>
      <form method="post" action="<?= e(url('admin.banners.move', ['id' => $b['id']])) ?>" class="bn-move">
        <?= csrf_field() ?>
        <button type="submit" name="do" value="up" class="btn-icon" aria-label="<?= e(fill_stats($b['title'])) ?> yukarı taşı"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrowup', 16) ?></button>
        <button type="submit" name="do" value="down" class="btn-icon" aria-label="<?= e(fill_stats($b['title'])) ?> aşağı taşı"<?= $i === count($rows) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button>
      </form>
<?php endif ?>
    </li>
<?php endforeach ?>
  </ol>
<?php else: ?>
  <div class="empty"><?= icon('banner', 40) ?><h4>Henüz slayt yok</h4><p>Anasayfanın üst bölümünde görünecek ilk slaytı ekle. İlk slayt kulübü tanıtmalı.</p></div>
<?php endif ?>
</div>
