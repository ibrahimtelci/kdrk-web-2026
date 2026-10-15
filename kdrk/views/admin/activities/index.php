<?php
/**
 * @var array  $rows
 * @var string $filter
 * @var array  $filters
 * @var string $q
 */
use App\Core\Auth;
use App\Models\Activity;

$S = require dirname(__DIR__) . '/_status.php';
$chip = static fn (array $c, string $extra = ''): string => '<span class="chip ' . $c[1] . ' dot">' . e($c[0]) . $extra . '</span>';
?>
<div class="toolbar">
  <form class="search-box" method="get" action="<?= e(url('admin.activities')) ?>" role="search">
    <?= icon('search') ?>
    <input type="hidden" name="durum" value="<?= e($filter) ?>">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Faaliyet veya yer ara…" aria-label="Faaliyet ara">
  </form>
  <nav class="seg" aria-label="Faaliyet filtresi">
<?php foreach ($filters as $k => $label): ?>
    <a href="<?= e(url('admin.activities', array_filter(['durum' => $k, 'q' => $q]))) ?>" class="<?= $filter === $k ? 'is-active' : '' ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach ?>
  </nav>
  <div class="tb-spacer"></div>
<?php if (Auth::canEdit()): ?>
  <a class="btn btn-primary" href="<?= e(url('admin.activities.new')) ?>"><?= icon('plus') ?>Yeni Faaliyet</a>
<?php endif ?>
</div>

<div class="panel">
<?php if ($rows): ?>
  <table class="tbl tbl-cards">
    <thead><tr><th>Faaliyet</th><th>Tarih</th><th>Durum</th><th>Başvuru</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
    <tbody>
<?php foreach ($rows as $a): ?>
      <tr>
        <td data-label="Faaliyet">
          <a class="cell-main" href="<?= e(url('admin.activities.edit', ['id' => $a['id']])) ?>">
            <?php if ($a['cover_image']): ?><img class="t-thumb" src="<?= e(img($a['cover_image'], 400)) ?>" alt="" loading="lazy"><?php else: ?><span class="t-thumb"></span><?php endif ?>
            <span><span class="t-title"><?= e($a['title']) ?></span><span class="t-sub muted"><?= e(Activity::CATEGORIES[$a['category']]) ?> · <?= e($a['location']) ?><?= $a['is_published'] ? '' : ' · <b>Yayında değil</b>' ?></span></span>
          </a>
        </td>
        <td data-label="Tarih" class="mono muted"><?= e(tr_range($a['starts_on'], $a['ends_on'])) ?></td>
        <td data-label="Durum"><?= $chip($S['activity'][$a['status']]) ?><?= $a['manual_status'] ? ' <span class="chip gray">elle</span>' : '' ?></td>
        <td data-label="Başvuru">
          <a class="app-count" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>">
            <span class="mono"><?= (int) $a['taken'] ?><?= $a['quota'] ? ' / ' . (int) $a['quota'] : '' ?></span>
            <?php if ($a['pending_count']): ?><span class="chip orange"><?= (int) $a['pending_count'] ?> yeni</span><?php endif ?>
          </a>
        </td>
        <td class="t-actions">
          <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>"><?= icon('users', 14) ?>Başvurular</a>
          <a class="btn-icon" href="<?= e(url('admin.activities.edit', ['id' => $a['id']])) ?>" aria-label="<?= e($a['title']) ?> düzenle"><?= icon(Auth::canEdit() ? 'edit' : 'eye') ?></a>
        </td>
      </tr>
<?php endforeach ?>
    </tbody>
  </table>
<?php else: ?>
  <div class="empty"><?= icon('activities', 40) ?><h4>Faaliyet bulunamadı</h4><p><?= $q !== '' ? 'Aramanı değiştirip tekrar dene.' : 'Bu filtrede faaliyet yok.' ?></p></div>
<?php endif ?>
</div>
