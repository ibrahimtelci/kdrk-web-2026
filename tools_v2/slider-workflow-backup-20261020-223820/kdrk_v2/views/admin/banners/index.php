<?php
use App\Core\Auth;
use App\Core\Settings;
use App\Models\Banner;
$canEdit = Auth::canEdit();
$ordered = Banner::orderedCandidates();
$manual = Settings::get('slider_order', '[]') !== '[]';
$items = []; $positions = [];
foreach ($ordered as $i => $slide) $positions[$slide['kind'] . ':' . $slide['source_id']] = $i;
foreach (['fixed' => $fixed, 'activity' => $activities, 'news' => $news] as $kind => $rows) {
    foreach ($rows as $r) {
        $key = $kind . ':' . $r['id'];
        $type = $kind === 'fixed' ? 'tanitim' : ($kind === 'activity' ? 'faaliyet' : ($r['category'] === 'haber' ? 'haber' : 'duyuru'));
        $items[$key] = ['row' => $r, 'kind' => $kind, 'type' => $type, 'position' => $positions[$key] ?? null];
    }
}
uasort($items, static fn ($a, $b) => ($a['position'] ?? PHP_INT_MAX) <=> ($b['position'] ?? PHP_INT_MAX));
$types = ['' => 'Tümü', 'faaliyet' => 'Faaliyet', 'haber' => 'Haber', 'duyuru' => 'Duyuru', 'tanitim' => 'Tanıtım'];
$filter = array_key_exists($_GET['tur'] ?? '', $types) ? ($_GET['tur'] ?? '') : '';
$query = trim((string) ($_GET['q'] ?? ''));
$editUrl = static fn ($kind, $id) => url(match ($kind) { 'fixed' => 'admin.banners.edit', 'activity' => 'admin.activities.edit', default => 'admin.news.edit' }, ['id' => $id]);
?>
<link rel="stylesheet" href="<?= e(asset('admin/banner-workspace.css')) ?>">
<div class="banner-workspace" data-banner-workspace>
  <div class="bw-heading">
    <p class="muted">İlk <?= Banner::MAX ?> görsel yayında. Oklarla yayın sırasını değiştirebilirsin.</p>
    <div class="bw-top-actions">
      <a class="btn btn-ghost" href="<?= e(url('home')) ?>" target="_blank" rel="noopener"><?= icon('ext', 16) ?>Anasayfayı aç</a>
<?php if ($canEdit): ?><a class="btn btn-primary" href="<?= e(url('admin.banners.new')) ?>" data-modal-link><?= icon('plus', 16) ?>Tanıtım ekle</a><?php endif ?>
    </div>
  </div>
<?php if ($canEdit): ?>
  <details class="bw-add-content"><summary>Faaliyet veya duyuru / haber ekle</summary><div class="bw-add-forms">
<?php foreach (['activity' => $activityCandidates, 'news' => $newsCandidates] as $source => $choices): $isAct = $source === 'activity'; ?>
    <form method="post" action="<?= e(url($isAct ? 'admin.banners.activity' : 'admin.banners.news')) ?>" class="bw-add">
      <?= csrf_field() ?><input type="hidden" name="do" value="add">
      <div class="field"><label for="add-<?= e($source) ?>"><?= $isAct ? 'Faaliyet' : 'Duyuru / haber' ?></label>
      <select id="add-<?= e($source) ?>" name="<?= $isAct ? 'activity_id' : 'news_id' ?>" required<?= !$choices ? ' disabled' : '' ?>><option value=""><?= $choices ? 'İçerik seç…' : 'Eklenebilecek içerik yok' ?></option>
<?php foreach ($choices as $c): ?><option value="<?= (int) $c['id'] ?>"<?= $c['cover_image'] ? '' : ' disabled' ?>><?= e($c['title'] . ($c['cover_image'] ? '' : ' (kapak görseli yok)')) ?></option><?php endforeach ?>
      </select></div><button class="btn btn-primary"<?= !$choices ? ' disabled' : '' ?>>Ekle</button>
    </form>
<?php endforeach ?>
  </div><p class="f-hint">Başlık ve görsel içerikten alınır. Eklenen görsel ilk <?= Banner::MAX ?> dışında kalırsa sırada bekler. Yeniden eklenen duyurunun eski gösterim bitiş tarihi temizlenir.</p></details>
<?php endif ?>
  <div class="bw-filter-bar">
    <form method="get" action="<?= e(url('admin.banners')) ?>" class="bw-filters" data-banner-filters role="search">
      <div class="field"><label for="bw-type">İçerik türü</label><select id="bw-type" name="tur" data-banner-type><?php foreach ($types as $value => $label): ?><option value="<?= e($value) ?>"<?= $filter === $value ? ' selected' : '' ?>><?= e($label) ?> (<?= $value === '' ? count($items) : count(array_filter($items, static fn ($item) => $item['type'] === $value)) ?>)</option><?php endforeach ?></select></div>
      <div class="field bw-search"><label for="bw-search">Görsel ara</label><input id="bw-search" name="q" type="search" placeholder="Başlığa göre ara" value="<?= e($query) ?>" data-banner-search></div>
      <button type="submit" class="btn btn-ghost" data-filter-submit>Filtrele</button>
    </form>
    <div class="bw-filter-meta"><span class="chip green dot"><?= min(count($ordered), Banner::MAX) ?> yayında</span><span class="f-hint" data-result-count role="status" aria-live="polite"><?= count($items) ?> görsel</span></div>
  </div>
  <div class="bw-order-bar"><span class="f-hint"><?= $manual ? 'Elle sıralama' : 'Otomatik sıralama' ?> · İlk tanıtım başta sabit kalır.</span>
<?php if ($canEdit && $manual): ?><form method="post" action="<?= e(url('admin.banners.order')) ?>"><?= csrf_field() ?><button class="btn btn-ghost" name="do" value="reset" data-confirm="Otomatik yayın sırasına dönülsün mü?">Otomatik sıraya dön</button></form><?php endif ?>
  </div>
  <ol class="bw-grid">
<?php foreach ($items as $key => $item):
    ['row' => $r, 'kind' => $kind, 'type' => $type, 'position' => $position] = $item;
    $title = fill_stats($r['title']);
    $image = $kind === 'fixed' ? $r['image'] : $r['cover_image'];
    $live = $position !== null && $position < Banner::MAX;
    $locked = $position === 0 && $kind === 'fixed';
    $state = $position !== null ? ($live ? 'Yayında' : 'Sırada') : ($kind === 'fixed' ? $r['state'] : $r['slide_state']);
    $hidden = ($filter !== '' && $filter !== $type) || ($query !== '' && mb_stripos($title, $query) === false);
?>
    <li class="bw-card<?= $live ? ' is-live' : '' ?>" data-banner-card data-type="<?= e($type) ?>" data-title="<?= e($title) ?>" id="<?= $kind === 'fixed' ? 'slayt' : ($kind === 'activity' ? 'faaliyet' : 'haber') ?>-<?= (int) $r['id'] ?>"<?= $hidden ? ' hidden' : '' ?>>
      <a class="bw-image" href="<?= e($editUrl($kind, $r['id'])) ?>" aria-label="<?= e($title) ?>: <?= $canEdit ? 'düzenle' : 'incele' ?>">
<?php if ($image): ?><img src="<?= e(img($image, 400)) ?>" alt="" loading="lazy" decoding="async"><?php else: ?><span class="bw-placeholder">Görsel yok</span><?php endif ?>
        <span class="bw-type"><?= e($types[$type]) ?></span>
<?php if ($position !== null): ?><span class="bw-position"><?= $position + 1 ?></span><?php endif ?>
      </a>
      <div class="bw-card-body">
        <span class="chip <?= $live ? 'green' : 'gray' ?> dot"><?= e($state) ?><?= $locked ? ' · Sabit' : '' ?></span>
        <h2><a href="<?= e($editUrl($kind, $r['id'])) ?>"><?= e($title) ?></a></h2>
        <div class="bw-card-actions">
          <a class="btn btn-ghost" href="<?= e($editUrl($kind, $r['id'])) ?>"><?= $canEdit ? 'Düzenle' : 'İncele' ?></a>
<?php if ($canEdit && $position !== null): ?>
          <form method="post" action="<?= e(url('admin.banners.order')) ?>" class="bw-arrows"><?= csrf_field() ?><input type="hidden" name="key" value="<?= e($key) ?>">
            <button class="btn-icon" name="do" value="up" aria-label="<?= e($title) ?>: bir sıra öne taşı"<?= $locked || ($position === 1 && $ordered[0]['kind'] === 'fixed') ? ' disabled' : '' ?>><?= icon('arrowup', 16) ?></button>
            <button class="btn-icon" name="do" value="down" aria-label="<?= e($title) ?>: bir sıra geriye taşı"<?= $locked || $position === count($ordered) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button>
          </form>
<?php endif ?>
        </div>
<?php if ($canEdit && $kind !== 'fixed'): ?>
        <form method="post" action="<?= e(url($kind === 'activity' ? 'admin.banners.activity' : 'admin.banners.news')) ?>" class="bw-remove"><?= csrf_field() ?><input type="hidden" name="<?= $kind === 'activity' ? 'activity_id' : 'news_id' ?>" value="<?= (int) $r['id'] ?>"><button class="btn btn-ghost" name="do" value="remove" data-confirm="Anasayfa listesinden çıkarılsın mı? İçeriğin kendisi silinmez.">Listeden çıkar</button></form>
<?php elseif ($canEdit && count($fixed) > 1): ?>
        <details class="bw-priority"><summary>Tanıtım önceliği</summary><p class="f-hint">Tanıtımlar arasında ilk sıradaki, açılış görseli olur.</p><form method="post" action="<?= e(url('admin.banners.move', ['id' => $r['id']])) ?>" class="bw-arrows"><?= csrf_field() ?><button class="btn btn-ghost" name="do" value="up">Öne al</button><button class="btn btn-ghost" name="do" value="down">Geri al</button></form></details>
<?php endif ?>
      </div>
    </li>
<?php endforeach ?>
  </ol>
<?php if (!$items): ?><div class="empty"><?= icon('banner', 40) ?><h4>Henüz görsel yok</h4><p>Yeni tanıtım oluştur veya mevcut bir içerik ekle.</p></div><?php endif ?>
  <p class="muted" data-no-results hidden>Filtreye uygun görsel bulunamadı.</p>
</div>
<script src="<?= e(asset('admin/banner-workspace.js')) ?>" defer></script>
