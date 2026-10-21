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
<?php
$listView = admin_list_view('faaliyetler');
$uiTabs = [];
foreach ($filters as $key => $label) {
    $uiTabs[] = ['href' => url('admin.activities', array_filter(['durum' => $key === 'tumu' ? null : $key, 'q' => $q])), 'label' => $label, 'active' => $filter === $key, 'icon' => admin_filter_icon($key)];
}
echo $this->partial('admin/_list-head', ['description' => 'Faaliyetleri, başvuruları ve yayın durumunu yönet.', 'createUrl' => Auth::canEdit() ? url('admin.activities.new') : null, 'createLabel' => 'Yeni Faaliyet']);
echo $this->partial('admin/_list-toolbar', ['tabs' => $uiTabs, 'filterLabel' => 'Faaliyet filtresi', 'search' => ['action' => url('admin.activities'), 'value' => $q, 'label' => 'Faaliyet veya yer ara', 'hidden' => ['durum' => $filter]], 'viewSwitch' => ['key' => 'faaliyetler', 'current' => $listView]]);
?>

<div class="panel ui-list" data-view-key="faaliyetler" data-list-view="<?= e($listView) ?>">
<?php if ($rows): ?>
  <table class="tbl tbl-cards">
    <thead><tr><th>Faaliyet</th><th>Tarih</th><th>Durum</th><th>Başvuru</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
    <tbody>
<?php foreach ($rows as $a): ?>
      <tr>
        <td data-label="Faaliyet">
          <a class="cell-main" href="<?= e(url('admin.activities.edit', ['id' => $a['id']])) ?>">
            <?php if ($a['cover_image']): ?><img class="t-thumb" src="<?= e(img($a['cover_image'], 400)) ?>" alt="" loading="lazy"><?php else: ?><span class="t-thumb"></span><?php endif ?>
            <span><span class="t-title"><?= e($a['title']) ?></span><span class="t-sub muted"><?= e(Activity::CATEGORIES[$a['category']]) ?> · <?= e($a['location']) ?><?= $a['is_published'] ? '' : ' · <b>Yayında değil</b>' ?><?= $a['in_slider'] && !in_array($a['status'], ['done', 'past'], true) ? ' · <b>Anasayfa slaytında</b>' : '' ?></span></span>
          </a>
        </td>
        <td data-label="Tarih" class="mono muted"><?= e(tr_range($a['starts_on'], $a['ends_on'])) ?></td>
        <td data-label="Durum"><?= $chip($S['activity'][$a['status']]) ?><?= $a['manual_status'] ? ' <span class="chip gray">elle</span>' : '' ?></td>
        <td data-label="Başvuru">
          <a class="app-count" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>">
            <span class="mono"><?= (int) $a['taken'] ?><?= $a['quota'] ? ' / ' . (int) $a['quota'] : '' ?></span>
            <?php if ($a['pending_count']): ?><span class="chip orange"><?= (int) $a['pending_count'] ?> yeni</span><?php endif ?>
          </a>
<?php if ($a['quota']): ?><meter class="quota-meter" min="0" max="<?= (int) $a['quota'] ?>" value="<?= min((int) $a['taken'], (int) $a['quota']) ?>" aria-label="Kontenjan doluluğu"></meter><span class="f-hint"><?= (int) $a['seats_left'] ?> yer kaldı</span><?php endif ?>
        </td>
        <td class="t-actions">
          <?= $this->partial('partials/social-share-button', ['a' => $a]) ?>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>"><?= icon('users', 14) ?>Başvurular</a>
          <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications.checklist', ['id' => $a['id']])) ?>"><?= icon('checklist', 14) ?>Yoklama</a>
<?php if (Auth::canEdit()): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('admin.activities.new', ['kopya' => $a['id']])) ?>">Benzerini oluştur</a><?php endif ?>
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
