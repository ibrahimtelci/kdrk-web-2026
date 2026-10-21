<?php
/**
 * @var array  $rows
 * @var int[]  $onHome  Anasayfada görünen haberlerin id'leri
 * @var string $filter
 * @var array  $filters
 * @var string $q
 */
use App\Core\Auth;
use App\Models\News;

$today = date('Y-m-d');
?>
<?php
$listView = admin_list_view('duyurular');
$uiTabs = [];
foreach ($filters as $key => $label) {
    $uiTabs[] = ['href' => url('admin.news', array_filter(['durum' => $key === 'tumu' ? null : $key, 'q' => $q])), 'label' => $label, 'active' => $filter === $key, 'icon' => admin_filter_icon($key)];
}
echo $this->partial('admin/_list-head', ['description' => 'Haber ve duyuruların içeriklerini ve yayın durumunu yönet.', 'createUrl' => Auth::canEdit() ? url('admin.news.new') : null, 'createLabel' => 'Yeni Duyuru / Haber']);
echo $this->partial('admin/_list-toolbar', ['tabs' => $uiTabs, 'filterLabel' => 'Duyuru ve haber filtresi', 'search' => ['action' => url('admin.news'), 'value' => $q, 'label' => 'Duyuru veya haber ara', 'hidden' => ['durum' => $filter]], 'viewSwitch' => ['key' => 'duyurular', 'current' => $listView]]);
?>
<p class="toolbar-note muted">Anasayfanın "Duyuru &amp; Haberler" bölümünde en fazla 5 kayıt görünür: önce "öne çıkan", sonra en yeniler. Tümü sitedeki /duyurular sayfasında listelenir. "Anasayfa slaytında göster" işaretlenen kayıt slaytta da görünür.</p>

<div class="panel ui-list" data-view-key="duyurular" data-list-view="<?= e($listView) ?>">
<?php if ($rows): ?>
  <table class="tbl tbl-cards">
    <thead><tr><th>Başlık</th><th>Tarih</th><th>Durum</th><th><span class="sr-only">İşlemler</span></th></tr></thead>
    <tbody>
<?php foreach ($rows as $n):
    $scheduled = $n['status'] === 'published' && $n['published_on'] > $today; ?>
      <tr>
        <td data-label="Başlık">
          <a class="cell-main" href="<?= e(url('admin.news.edit', ['id' => $n['id']])) ?>">
            <?php if ($n['cover_image']): ?><img class="t-thumb" src="<?= e(img($n['cover_image'], 400)) ?>" alt="" loading="lazy"><?php else: ?><span class="t-thumb"></span><?php endif ?>
            <span><span class="t-title"><?= e($n['title']) ?></span><span class="t-sub muted"><?= e(News::CATEGORIES[$n['category']]) ?><?= $n['activity_title'] ? ' · ' . e($n['activity_title']) : '' ?></span></span>
          </a>
        </td>
        <td data-label="Tarih" class="mono muted"><?= $n['published_on'] ? e(tr_date($n['published_on'])) : '—' ?></td>
        <td data-label="Durum">
<?php if ($n['status'] === 'draft'): ?>
          <span class="chip gray dot">Taslak</span>
<?php elseif ($scheduled): ?>
          <span class="chip blue dot">Zamanlandı</span>
<?php else: ?>
          <span class="chip green dot">Yayında</span>
<?php endif ?>
<?php if ($n['is_featured']): ?> <span class="chip orange">Öne çıkan</span><?php endif ?>
<?php if (in_array((int) $n['id'], $onHome, true)): ?> <span class="chip gray">Anasayfada</span><?php endif ?>
<?php if ($n['in_slider'] && (!$n['slider_until'] || $n['slider_until'] >= $today)): ?> <span class="chip orange">Slaytta</span><?php endif ?>
        </td>
        <td class="t-actions">
          <a class="btn-icon" href="<?= e(url('admin.news.edit', ['id' => $n['id']])) ?>" aria-label="<?= e($n['title']) ?> düzenle"><?= icon(Auth::canEdit() ? 'edit' : 'eye') ?></a>
        </td>
      </tr>
<?php endforeach ?>
    </tbody>
  </table>
<?php else: ?>
  <div class="empty"><?= icon('news', 40) ?><h4>Kayıt bulunamadı</h4><p><?= $q !== '' ? 'Aramanı değiştirip tekrar dene.' : 'İlk duyuru veya haberi ekle.' ?></p></div>
<?php endif ?>
</div>
