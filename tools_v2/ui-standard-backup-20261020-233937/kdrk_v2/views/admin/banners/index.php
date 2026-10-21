<?php
use App\Core\Auth;
use App\Core\Settings;
use App\Models\Banner;
use App\Models\Activity;
use App\Models\News;
$canEdit = Auth::canEdit();
$ordered = Banner::orderedCandidates();
$items = []; $positions = [];
foreach (Banner::orderedKeys(['fixed' => $fixed, 'activities' => $activities, 'news' => $news]) as $i => $key) $positions[$key] = $i;
foreach (['fixed' => $fixed, 'activity' => $activities, 'news' => $news] as $kind => $rows) {
    foreach ($rows as $r) {
        $key = $kind . ':' . $r['id'];
        $type = $kind === 'fixed' ? 'tanitim' : ($kind === 'activity' ? 'faaliyet' : ($r['category'] === 'haber' ? 'haber' : 'duyuru'));
        $items[$key] = ['row' => $r, 'kind' => $kind, 'type' => $type, 'position' => $positions[$key] ?? null];
    }
}
uasort($items, static fn ($a, $b) => ($a['position'] ?? PHP_INT_MAX) <=> ($b['position'] ?? PHP_INT_MAX));
$types = ['' => 'Tümü', 'faaliyet' => 'Faaliyet', 'news' => 'Haber & Duyuru', 'tanitim' => 'Tanıtım'];
$filter = array_key_exists($_GET['tur'] ?? '', $types) ? ($_GET['tur'] ?? '') : '';
$query = trim((string) ($_GET['q'] ?? ''));
$stateFilter = in_array($_GET['durum'] ?? '', ['active', 'inactive'], true) ? $_GET['durum'] : '';
$editUrl = static fn ($kind, $id) => url(match ($kind) { 'fixed' => 'admin.banners.edit', 'activity' => 'admin.activities.edit', default => 'admin.news.edit' }, ['id' => $id]);
?>
<link rel="stylesheet" href="<?= e(asset('admin/banner-workspace.css')) ?>">
<div class="banner-workspace" data-banner-workspace>
  <div class="bw-heading">
    <div><h2>Anasayfa Görselleri</h2><p class="muted">Görselleri seç, sırala ve yayın durumunu yönet.</p></div>
    <div class="bw-top-actions">
      <a class="btn btn-ghost" href="<?= e(url('home')) ?>" target="_blank" rel="noopener"><?= icon('ext', 16) ?>Anasayfayı aç</a>
<?php if ($canEdit): ?>
      <details class="bw-create-menu" data-create-menu><summary class="btn btn-primary"><?= icon('plus', 16) ?>Yeni Slayt Ekle<?= icon('arrowdown', 14) ?></summary><div class="bw-menu-list">
        <a href="<?= e(url('admin.banners.new')) ?>" data-modal-link><?= icon('upload', 16) ?>Tanıtım Ekle</a>
        <a href="#slayt-sec-activity" data-picker-open="activity"><?= icon('activities', 16) ?>Faaliyet Ekle</a>
        <a href="#slayt-sec-news" data-picker-open="news"><?= icon('news', 16) ?>Haber &amp; Duyuru Ekle</a>
      </div></details>
<?php endif ?>
    </div>
  </div>
  <div class="bw-filter-bar">
    <form method="get" action="<?= e(url('admin.banners')) ?>" id="bw-filter-form" class="bw-filters" data-banner-filters role="search">
      <input type="hidden" name="tur" value="<?= e($filter) ?>" data-banner-type>
      <nav class="bw-type-tabs" aria-label="İçerik türü">
<?php foreach ($types as $value => $label): ?><button type="button" class="bw-filter-pill" data-type-choice="<?= e($value) ?>" aria-pressed="<?= $filter === $value ? 'true' : 'false' ?>"><?= icon(match ($value) { 'faaliyet' => 'star', 'news' => 'news', 'tanitim' => 'megaphone', default => 'view-grid' }, 15) ?><?= e($label) ?> <span>(<?= $value === '' ? count($items) : count(array_filter($items, static fn ($item) => ($value === 'news' ? in_array($item['type'], ['haber', 'duyuru'], true) : $item['type'] === $value))) ?>)</span></button><?php endforeach ?>
      </nav>
      <noscript><label>İçerik türü<select name="tur"><?php foreach ($types as $value => $label): ?><option value="<?= e($value) ?>"<?= $filter === $value ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach ?></select></label></noscript>
      <div class="field bw-search"><?= icon('search', 16) ?><label for="bw-search">Görsel ara</label><input id="bw-search" name="q" type="search" placeholder="Başlığa göre ara" value="<?= e($query) ?>" data-banner-search></div>
      <div class="bw-view-switch" role="group" aria-label="Görünüm"><button type="button" class="btn btn-ghost" data-view-choice="cards" aria-pressed="true" title="Kart görünümü"><?= icon('view-grid', 16) ?>Kart</button><button type="button" class="btn btn-ghost" data-view-choice="list" aria-pressed="false" title="Resimli tablo görünümü"><?= icon('view-table', 16) ?>Tablo</button></div>
      <button type="submit" class="btn btn-ghost" data-filter-submit>Filtrele</button>
    </form>
  </div>
  <div class="bw-view-bar"><div class="bw-summary"><span class="bw-count-active"><?= count($ordered) ?> aktif</span><span class="bw-count-inactive"><?= count($items) - count($ordered) ?> pasif</span><div class="field bw-state-field"><label class="sr-only" for="bw-state">Slayt durumu</label><select form="bw-filter-form" id="bw-state" name="durum" data-banner-state><option value="">Aktif ve pasif</option><?php foreach (['active' => 'Aktif', 'inactive' => 'Pasif'] as $key => $label): ?><option value="<?= e($key) ?>"<?= $stateFilter === $key ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach ?></select></div><span class="f-hint" data-result-count role="status" aria-live="polite"><?= count($items) ?> slayt</span><span class="f-hint" data-order-status role="status" aria-live="polite">Sürükleyerek sırala.</span></div></div>
<?php if ($canEdit): ?><form data-order-form method="post" action="<?= e(url('admin.banners.order')) ?>" hidden><?= csrf_field() ?><input type="hidden" name="revision" value="<?= e(hash('sha256', json_encode(array_keys($positions)))) ?>"><input type="hidden" name="do" value="set"></form><?php endif ?>
  <ol class="bw-grid">
<?php foreach ($items as $key => $item):
    ['row' => $r, 'kind' => $kind, 'type' => $type, 'position' => $position] = $item;
    $title = fill_stats($r['title']);
    $image = $kind === 'fixed' ? $r['image'] : $r['cover_image'];
    $live = !empty($r['on_site']);
    $locked = false;
    $reason = $kind === 'fixed' ? $r['state'] : $r['slide_state'];
    $state = $live ? 'Aktif' : 'Pasif';
    $stateKey = $live ? 'active' : 'inactive';
    $hidden = ($stateFilter !== '' && $stateFilter !== $stateKey) || ($filter !== '' && ($filter === 'news' ? !in_array($type, ['haber', 'duyuru'], true) : $filter !== $type)) || ($query !== '' && mb_stripos($title, $query) === false);
?>
    <li class="bw-card<?= $live ? ' is-live' : ' is-inactive' ?>" data-banner-card data-key="<?= e($key) ?>" data-eligible="<?= $position !== null ? '1' : '0' ?>" data-locked="<?= $locked ? '1' : '0' ?>" data-type="<?= e($type) ?>" data-title="<?= e($title) ?>" data-state="<?= e($stateKey) ?>" id="<?= $kind === 'fixed' ? 'slayt' : ($kind === 'activity' ? 'faaliyet' : 'haber') ?>-<?= (int) $r['id'] ?>"<?= $hidden ? ' hidden' : '' ?>>
<?php if ($canEdit && $position !== null): ?><button type="button" class="bw-grip" data-drag-handle aria-label="<?= e($title) ?>: sürükleyerek sırala"<?= $locked ? ' disabled' : '' ?> title="Sürükleyerek sırala; oklarla da taşıyabilirsin"><svg viewBox="0 0 16 20" width="14" height="18" fill="currentColor" aria-hidden="true"><circle cx="5" cy="4" r="1.5"/><circle cx="11" cy="4" r="1.5"/><circle cx="5" cy="10" r="1.5"/><circle cx="11" cy="10" r="1.5"/><circle cx="5" cy="16" r="1.5"/><circle cx="11" cy="16" r="1.5"/></svg></button><?php endif ?>
      <a class="bw-image" href="<?= e($editUrl($kind, $r['id'])) ?>" aria-label="<?= e($title) ?>: <?= $canEdit ? 'düzenle' : 'incele' ?>">
<?php if ($image): ?><img src="<?= e(img($image, 400)) ?>" alt="" loading="lazy" decoding="async"><?php else: ?><span class="bw-placeholder">Görsel yok</span><?php endif ?>
        <span class="bw-type" data-category="<?= e($type) ?>"><?= e(['tanitim' => 'Tanıtım', 'faaliyet' => 'Faaliyet', 'haber' => 'Haber', 'duyuru' => 'Duyuru'][$type]) ?></span>
<?php if ($position !== null): ?><span class="bw-position"><?= $position + 1 ?></span><?php endif ?>
      </a>
      <div class="bw-card-body">
        <div class="bw-status-line"><span class="bw-status <?= $live ? 'is-active' : 'is-inactive' ?>"><?= icon($live ? 'check' : 'x', 14) ?><?= e($state) ?></span>
<?php if ($canEdit): ?><form method="post" action="<?= e(url('admin.banners.toggle')) ?>"><?= csrf_field() ?><input type="hidden" name="key" value="<?= e($key) ?>"><input type="hidden" name="active" value="<?= $live ? '0' : '1' ?>"><button type="submit" class="bw-toggle" aria-label="<?= e($title . ': ' . ($live ? 'pasif yap' : 'aktif yap')) ?>"><?= $live ? 'Pasif Yap' : 'Aktif Yap' ?></button></form><?php endif ?>
        </div>
<?php if (!$live && !in_array($reason, ['Pasif', 'Kapalı'], true)): ?><span class="bw-passive-reason"><?= e($reason) ?></span><?php endif ?>
        <h2><a href="<?= e($editUrl($kind, $r['id'])) ?>"><?= e($title) ?></a></h2>
        <div class="bw-card-actions">
          <a class="btn bw-edit" href="<?= e($editUrl($kind, $r['id'])) ?>"><?= icon($canEdit ? 'edit' : 'eye', 16) ?><?= $canEdit ? 'Düzenle' : 'İncele' ?></a>
<?php if ($canEdit): ?>
          <form method="post" action="<?= e($kind === 'fixed' ? url('admin.banners.delete', ['id' => $r['id']]) : url($kind === 'activity' ? 'admin.banners.activity' : 'admin.banners.news')) ?>" data-slide-remove data-remove-title="<?= e($title) ?>" data-remove-message="<?= $kind === 'fixed' ? 'Bu tanıtım slaytı ve görseli kalıcı olarak silinecek. Bu işlem geri alınamaz.' : 'Bu içerik yalnızca anasayfa slaytlarından kaldırılacak. Asıl içerik silinmez; daha sonra yeniden ekleyebilirsin.' ?>">
            <?= csrf_field() ?>
<?php if ($kind !== 'fixed'): ?><input type="hidden" name="<?= $kind === 'activity' ? 'activity_id' : 'news_id' ?>" value="<?= (int) $r['id'] ?>"><input type="hidden" name="do" value="remove"><?php endif ?>
            <button type="submit" class="btn-icon bw-delete" aria-label="<?= e($title) ?>: <?= $kind === 'fixed' ? 'sil' : 'slayttan kaldır' ?>" data-confirm="<?= $kind === 'fixed' ? 'Tanıtım slaytı kalıcı olarak silinsin mi?' : 'İçerik slayttan kaldırılsın mı? Asıl içerik silinmez.' ?>"><?= icon('trash', 16) ?></button>
          </form>
<?php endif ?>
<?php if ($canEdit && $position !== null && !$locked): ?>
          <form method="post" action="<?= e(url('admin.banners.order')) ?>" class="bw-arrows"><?= csrf_field() ?><input type="hidden" name="key" value="<?= e($key) ?>">
            <button class="btn-icon" name="do" value="up" aria-label="<?= e($title) ?>: bir sıra öne taşı"<?= $position === 0 ? ' disabled' : '' ?>><?= icon('arrowup', 16) ?></button>
            <button class="btn-icon" name="do" value="down" aria-label="<?= e($title) ?>: bir sıra geriye taşı"<?= $position === count($items) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button>
          </form>
<?php endif ?>
        </div>

      </div>
    </li>
<?php endforeach ?>
  </ol>
<?php if (!$items): ?><div class="empty"><?= icon('banner', 40) ?><h4>Henüz görsel yok</h4><p>Yeni tanıtım oluştur veya mevcut bir içerik ekle.</p></div><?php endif ?>
  <p class="muted" data-no-results hidden>Filtreye uygun görsel bulunamadı.</p>
<?php if ($canEdit): ?>
<?php foreach (['activity' => $activityCandidates, 'news' => $newsCandidates] as $source => $choices): $isAct = $source === 'activity'; ?>
  <details class="bw-picker-fallback" id="slayt-sec-<?= e($source) ?>" data-source-picker="<?= e($source) ?>"><summary><?= $isAct ? 'Faaliyet seç' : 'Haber & Duyuru seç' ?></summary>
    <div class="bw-picker-body" data-picker-body>
      <header class="bw-picker-head"><div><h2><?= $isAct ? 'Faaliyet Ekle' : 'Haber & Duyuru Ekle' ?></h2><p class="f-hint">Yayındaki içeriklerden birini seç. Görsel ve başlık otomatik alınır.</p></div><button type="button" class="btn-icon" data-picker-close aria-label="Pencereyi kapat" hidden><?= icon('x', 18) ?></button></header>
      <div class="bw-picker-filters" data-picker-tools hidden><div class="field"><label for="pick-category-<?= e($source) ?>">Kategori</label><select id="pick-category-<?= e($source) ?>" data-picker-category><option value="">Tüm kategoriler</option><?php foreach ($isAct ? Activity::CATEGORIES : News::CATEGORIES as $category => $label): ?><option value="<?= e($category) ?>"><?= e($label) ?></option><?php endforeach ?></select></div><div class="field"><label for="pick-search-<?= e($source) ?>">İçerik ara</label><input id="pick-search-<?= e($source) ?>" type="search" placeholder="Başlığa göre ara" data-picker-search></div></div>
      <form method="post" action="<?= e(url($isAct ? 'admin.banners.activity' : 'admin.banners.news')) ?>" data-picker-form>
        <?= csrf_field() ?><input type="hidden" name="do" value="add">
        <div class="bw-picker-list">
<?php foreach ($choices as $c): ?>
          <label class="bw-picker-option" data-picker-option data-category="<?= e($c['category']) ?>" data-title="<?= e($c['title']) ?>">
            <input type="radio" name="<?= $isAct ? 'activity_id' : 'news_id' ?>" value="<?= (int) $c['id'] ?>" required<?= !$c['cover_image'] ? ' disabled' : '' ?>>
<?php if ($c['cover_image']): ?><img src="<?= e(img($c['cover_image'], 400)) ?>" alt="" loading="lazy"><?php endif ?>
            <span><strong><?= e($c['title']) ?></strong><small><?= e(($isAct ? Activity::CATEGORIES[$c['category']] : (News::CATEGORIES[$c['category']] ?? $c['category'])) . ' · ' . tr_date($isAct ? $c['starts_on'] : $c['published_on'])) ?><?= !$c['cover_image'] ? ' · Önce kapak görseli ekle' : '' ?></small></span>
          </label>
<?php endforeach ?>
        </div>
        <p class="muted" data-picker-empty<?= $choices ? ' hidden' : '' ?>>Seçilebilecek içerik bulunamadı.</p>
        <footer class="bw-picker-footer"><span class="f-hint"><?= $isAct ? 'Faaliyete Başvur ve Faaliyet Ayrıntıları butonları otomatik eklenir. Başvuru kapalıysa başvuru butonu gösterilmez.' : 'Devamını Oku butonu otomatik eklenir.' ?> Aktif slaytların tamamı anasayfada gösterilir.<?= !$isAct ? ' Yeniden eklemede eski gösterim bitişi temizlenir.' : '' ?></span><button class="btn btn-primary" type="submit"<?= !$choices ? ' disabled' : '' ?>><?= icon('plus', 16) ?>Seçileni Ekle</button></footer>
      </form>
    </div>
  </details>
<?php endforeach ?>
<?php endif ?>

</div>
<script src="<?= e(asset('admin/banner-workspace.js')) ?>" defer></script>
