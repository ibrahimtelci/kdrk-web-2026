<?php
/**
 * Anasayfa görselleri. Tablo ve kart görünümü aynı $rows dizisinden üretilir ve aynı işlem formlarını kullanır.
 * JS kapalıyken filtre, arama, sıralama ve görünüm GET parametreleriyle sunucuda çalışır.
 * @var array $fixed
 * @var array $activities
 * @var array $news
 * @var array $activityCandidates
 * @var array $newsCandidates
 */
use App\Core\Auth;
use App\Models\Banner;
use App\Models\Activity;
use App\Models\News;

$canEdit = Auth::canEdit();
$order = Banner::orderedKeys(['fixed' => $fixed, 'activities' => $activities, 'news' => $news]);
$positions = array_flip($order);
$today = date('Y-m-d');

$rows = [];
foreach (['fixed' => $fixed, 'activity' => $activities, 'news' => $news] as $kind => $list) {
    foreach ($list as $r) {
        $key = $kind . ':' . $r['id'];
        $live = !empty($r['on_site']);
        $reason = $kind === 'fixed' ? $r['state'] : $r['slide_state'];
        $rows[$key] = [
            'key'      => $key,
            'kind'     => $kind,
            'id'       => (int) $r['id'],
            'group'    => $kind === 'fixed' ? 'tanitim' : ($kind === 'activity' ? 'faaliyet' : 'news'),
            'label'    => match ($kind) { 'fixed' => 'Tanıtım', 'activity' => 'Faaliyet', default => $r['category_text'] },
            'title'    => fill_stats($r['title']),
            'image'    => $kind === 'fixed' ? $r['image'] : $r['cover_image'],
            'live'     => $live,
            'reason'   => !$live && !in_array($reason, ['Pasif', 'Kapalı'], true) ? $reason : null,
            'updated'  => substr((string) $r['updated_at'], 0, 10),
            'position' => $positions[$key] ?? count($order),
            'anchor'   => ($kind === 'fixed' ? 'slayt' : ($kind === 'activity' ? 'faaliyet' : 'haber')) . '-' . $r['id'],
            'edit'     => url(match ($kind) { 'fixed' => 'admin.banners.edit', 'activity' => 'admin.activities.edit', default => 'admin.news.edit' }, ['id' => $r['id']]),
            // Sitede açılabilen sayfa: tanıtım anasayfada, faaliyet ve metinli duyuru kendi sayfasında.
            'public'   => match ($kind) {
                'fixed'    => $live ? url('home') : null,
                'activity' => $r['is_published'] ? $r['url'] : null,
                default    => $r['status'] === 'published' && $r['published_on'] && $r['published_on'] <= $today && trim((string) $r['body']) !== '' ? url('duyuru', ['slug' => $r['slug']]) : null,
            },
        ];
    }
}

// Durum: GET parametreleri. Değerler beyaz listeden geçer.
$groups = ['' => 'Tümü', 'faaliyet' => 'Faaliyet', 'news' => 'Haber & Duyuru', 'tanitim' => 'Tanıtım'];
$groupIcons = ['' => 'view-grid', 'faaliyet' => 'activities', 'news' => 'news', 'tanitim' => 'megaphone'];
$sorts = ['sira' => 'Sıra', 'baslik' => 'Başlık', 'kategori' => 'Kategori', 'durum' => 'Yayın durumu', 'guncelleme' => 'Son güncelleme'];
$group = array_key_exists($_GET['tur'] ?? '', $groups) ? (string) ($_GET['tur'] ?? '') : '';
$query = trim((string) ($_GET['q'] ?? ''));
$sort = array_key_exists($_GET['sirala'] ?? '', $sorts) ? $_GET['sirala'] : 'sira';
$dir = ($_GET['yon'] ?? '') === 'desc' ? 'desc' : 'asc';
$view = admin_list_view('slaytlar');
$params = array_filter(['tur' => $group, 'q' => $query, 'sirala' => $sort === 'sira' ? '' : $sort, 'yon' => $dir === 'asc' ? '' : $dir, ]);
$listHref = static fn (array $over = []) => url('admin.banners', array_filter(array_merge($params, $over), static fn ($v) => $v !== null && $v !== ''));
$returnTo = $listHref();

$matches = static fn (array $row) => ($group === '' || $row['group'] === $group)
    && ($query === '' || mb_stripos($row['title'], $query) !== false);
$compare = static function (array $a, array $b) use ($sort): int {
    return match ($sort) {
        'baslik'     => strcmp(mb_strtolower($a['title']), mb_strtolower($b['title'])),
        'kategori'   => strcmp($a['label'], $b['label']),
        'durum'      => (int) $b['live'] <=> (int) $a['live'],
        'guncelleme' => strcmp($a['updated'], $b['updated']),
        default      => 0,
    } ?: $a['position'] <=> $b['position'];
};
uasort($rows, $compare);
if ($dir === 'desc') $rows = array_reverse($rows, true);

$total = count($rows);
$liveCount = count(array_filter($rows, static fn ($r) => $r['live']));
$visible = count(array_filter($rows, $matches));
$groupCount = static fn (string $g) => $g === '' ? $total : count(array_filter($rows, static fn ($r) => $r['group'] === $g));
$filtered = $group !== '' || $query !== '';
$dragReady = $canEdit && !$filtered && $sort === 'sira' && $dir === 'asc';
$last = count($order) - 1;

/** Satır ve kart işlemleri: tek menü, iki görünümde aynı içerik. */
$menu = static function (array $row) use ($canEdit, $returnTo, $last): string {
    $t = e($row['title']);
    $return = '<input type="hidden" name="return" value="' . e($returnTo) . '" data-return>';
    $html = '<details class="bw-menu" data-row-menu><summary class="bw-menu-trigger" aria-label="Diğer işlemler: ' . $t . '" title="Diğer işlemler">' . icon('more', 18) . '</summary><div class="bw-menu-list">';
    $html .= '<a href="' . e($row['edit']) . '">' . icon($canEdit ? 'edit' : 'eye', 16) . ($canEdit ? 'Düzenle' : 'İncele') . '</a>';
    if ($row['public']) {
        $html .= '<a href="' . e($row['public']) . '" target="_blank" rel="noopener">' . icon('ext', 16) . 'Sitede aç<span class="sr-only"> (yeni sekmede)</span></a>';
    }
    if ($canEdit) {
        $html .= '<form method="post" action="' . e(url('admin.banners.toggle')) . '">' . csrf_field() . $return
            . '<input type="hidden" name="key" value="' . e($row['key']) . '"><input type="hidden" name="active" value="' . ($row['live'] ? '0' : '1') . '">'
            . '<button type="submit">' . icon($row['live'] ? 'pause' : 'publish', 16) . ($row['live'] ? 'Pasife al' : 'Yayına al') . '</button></form>';
        $html .= '<form method="post" action="' . e(url('admin.banners.order')) . '">' . csrf_field() . $return
            . '<input type="hidden" name="key" value="' . e($row['key']) . '">'
            . '<button type="submit" name="do" value="up" data-move="up"' . ($row['position'] === 0 ? ' disabled' : '') . '>' . icon('arrowup', 16) . 'Bir sıra yukarı</button>'
            . '<button type="submit" name="do" value="down" data-move="down"' . ($row['position'] >= $last ? ' disabled' : '') . '>' . icon('arrowdown', 16) . 'Bir sıra aşağı</button></form>';
        $fixed = $row['kind'] === 'fixed';
        $action = $fixed ? url('admin.banners.delete', ['id' => $row['id']]) : url($row['kind'] === 'activity' ? 'admin.banners.activity' : 'admin.banners.news');
        $html .= '<form method="post" action="' . e($action) . '" class="bw-menu-danger" data-slide-remove data-remove-title="' . $t . '" data-remove-message="'
            . ($fixed ? 'Bu tanıtım slaytı ve görseli kalıcı olarak silinecek. Bu işlem geri alınamaz.' : 'Bu içerik yalnızca anasayfa slaytlarından kaldırılacak. Asıl içerik silinmez; daha sonra yeniden ekleyebilirsin.') . '">'
            . csrf_field() . $return
            . ($fixed ? '' : '<input type="hidden" name="' . ($row['kind'] === 'activity' ? 'activity_id' : 'news_id') . '" value="' . $row['id'] . '"><input type="hidden" name="do" value="remove">')
            . '<button type="submit" data-confirm="' . ($fixed ? 'Tanıtım slaytı kalıcı olarak silinsin mi?' : 'İçerik slayttan kaldırılsın mı? Asıl içerik silinmez.') . '">' . icon('trash', 16) . ($fixed ? 'Sil' : 'Slayttan kaldır') . '</button></form>';
    }
    return $html . '</div></details>';
};
$status = static fn (array $row): string => '<span class="bw-status ' . ($row['live'] ? 'is-live' : 'is-off') . '">' . icon($row['live'] ? 'check' : 'pause', 14) . ($row['live'] ? 'Yayında' : 'Pasif') . '</span>'
    . ($row['reason'] ? '<span class="bw-reason">' . e($row['reason']) . '</span>' : '');
$category = static fn (array $row): string => '<span class="bw-cat" data-group="' . e($row['group']) . '">' . e($row['label']) . '</span>';
$thumb = static fn (array $row, int $w): string => $row['image']
    ? '<img src="' . e(img($row['image'], $w)) . '" alt="" loading="lazy" decoding="async">'
    : '<span class="bw-noimg">' . icon('banner', 20) . '<span>Görsel yok</span></span>';
$select = static fn (array $row): string => '<label class="bw-check"><input type="checkbox" form="bw-bulk-form" name="keys[]" value="' . e($row['key']) . '" aria-label="Seç: ' . e($row['title']) . '" data-select></label>';
$grip = static fn (array $row): string => '<button type="button" class="bw-grip" data-drag-handle aria-label="Sırayı değiştir: ' . e($row['title']) . '" aria-describedby="bw-drag-help"'
    . ($dragReady ? '' : ' disabled') . '>' . icon('grip', 18) . '</button>';
$attrs = static fn (array $row): string => ' data-item data-key="' . e($row['key']) . '" data-group="' . e($row['group']) . '" data-title="' . e($row['title']) . '" data-label="' . e($row['label'])
    . '" data-state="' . ($row['live'] ? 'active' : 'inactive') . '" data-position="' . $row['position'] . '" data-updated="' . e($row['updated']) . '"' . ($matches($row) ? '' : ' hidden');
$sortHead = static function (string $key, string $label) use ($sort, $dir, $listHref): string {
    $active = $sort === $key;
    $next = $active && $dir === 'asc' ? 'desc' : ($active ? 'asc' : ($key === 'guncelleme' ? 'desc' : 'asc'));
    $aria = $active ? ' aria-sort="' . ($dir === 'asc' ? 'ascending' : 'descending') . '"' : '';
    return '<th scope="col" class="bw-col-' . $key . '"' . $aria . '><a class="bw-sort" href="' . e($listHref(['sirala' => $key === 'sira' && $next === 'asc' ? null : $key, 'yon' => $next === 'asc' ? null : 'desc'])) . '" data-sort="' . $key . '">'
        . e($label) . '<span class="bw-sort-icon" aria-hidden="true">' . icon($active ? ($dir === 'asc' ? 'arrowup' : 'arrowdown') : 'sort', 14) . '</span></a></th>';
};
?>
<link rel="stylesheet" href="<?= e(asset('admin/banner-workspace.css')) ?>">
<div class="banner-workspace" data-banner-workspace data-view="<?= e($view) ?>">
  <div class="ui-list-heading">
    <p class="muted">Görselleri seç, sırala ve yayın durumunu yönet.</p>
    <div class="ui-heading-actions bw-heading-actions">
      <a class="btn btn-ghost" href="<?= e(url('home')) ?>" target="_blank" rel="noopener"><?= icon('ext', 16) ?>Anasayfayı aç<span class="sr-only"> (yeni sekmede)</span></a>
<?php if ($canEdit): ?>
      <details class="bw-create" data-create-menu><summary class="btn btn-primary"><?= icon('plus', 16) ?>Yeni Slayt Ekle<?= icon('arrowdown', 14) ?></summary><div class="bw-menu-list bw-create-list">
        <a href="<?= e(url('admin.banners.new')) ?>" data-modal-link><?= icon('upload', 16) ?>Tanıtım slaytı</a>
        <a href="#slayt-sec-activity" data-picker-open="activity"><?= icon('activities', 16) ?>Faaliyetten ekle</a>
        <a href="#slayt-sec-news" data-picker-open="news"><?= icon('news', 16) ?>Haber &amp; duyurudan ekle</a>
      </div></details>
<?php endif ?>
    </div>
  </div>

  <form method="get" action="<?= e(url('admin.banners')) ?>" class="toolbar bw-toolbar" role="search" aria-label="Slaytları filtrele" data-banner-filters>
    <input type="hidden" name="tur" value="<?= e($group) ?>" data-filter-group>
    <input type="hidden" name="gorunum" value="<?= e($view) ?>" data-filter-view>
    <div class="seg bw-groups" role="group" aria-label="Kategori">
<?php foreach ($groups as $value => $label): ?>
      <button type="submit" name="tur" value="<?= e($value) ?>" class="<?= $group === $value ? 'is-active' : '' ?>" aria-pressed="<?= $group === $value ? 'true' : 'false' ?>" data-group-choice="<?= e($value) ?>"><?= icon($groupIcons[$value], 16) ?><?= e($label) ?><span class="bw-count"><?= $groupCount($value) ?></span></button>
<?php endforeach ?>
    </div>
    <div class="search-box bw-search">
      <label for="bw-search" class="sr-only">Başlığa göre ara</label>
      <input id="bw-search" type="search" name="q" value="<?= e($query) ?>" placeholder="Başlığa göre ara" autocomplete="off" enterkeyhint="search" data-filter-search>
      <button type="button" class="bw-search-clear" aria-label="Aramayı temizle" data-search-clear<?= $query === '' ? ' hidden' : '' ?>><?= icon('x', 16) ?></button>
    </div>
    <input type="hidden" name="sirala" value="<?= e($sort === 'sira' ? '' : $sort) ?>" data-filter-sort>
    <input type="hidden" name="yon" value="<?= e($dir === 'asc' ? '' : $dir) ?>" data-filter-dir>
    <div class="ui-view-switch" role="group" aria-label="Görünüm">
      <button type="submit" name="gorunum" value="tablo" class="btn btn-ghost" aria-pressed="<?= $view === 'tablo' ? 'true' : 'false' ?>" data-view-choice="tablo"><?= icon('view-table', 16) ?><span>Tablo</span></button>
      <button type="submit" name="gorunum" value="kart" class="btn btn-ghost" aria-pressed="<?= $view === 'kart' ? 'true' : 'false' ?>" data-view-choice="kart"><?= icon('view-grid', 16) ?><span>Kart</span></button>
    </div>
  </form>

<?php if ($total): ?>
  <div class="bw-listbar">
<?php if ($canEdit): ?>
    <label class="bw-check bw-select-all"><input type="checkbox" data-select-all><span>Tümünü seç</span></label>
<?php endif ?>
    <p class="bw-summary" data-summary><span data-result-count><?= $filtered ? $visible . ' / ' . $total : $total ?> slayt</span><span aria-hidden="true">·</span><span><?= $liveCount ?> yayında</span></p>
    <p class="bw-live" role="status" aria-live="polite" data-order-status></p>
<?php if ($canEdit): ?>
    <form method="post" action="<?= e(url('admin.banners.bulk')) ?>" id="bw-bulk-form" class="bw-bulk" data-bulk>
      <?= csrf_field() ?><input type="hidden" name="return" value="<?= e($returnTo) ?>" data-return>
      <span class="bw-bulk-count" data-bulk-count>Seçim yok</span>
      <button type="submit" name="do" value="activate" class="btn btn-ghost btn-sm"><?= icon('publish', 16) ?>Yayına al</button>
      <button type="submit" name="do" value="deactivate" class="btn btn-ghost btn-sm"><?= icon('pause', 16) ?>Pasife al</button>
      <button type="button" class="btn btn-ghost btn-sm" data-bulk-clear hidden>Seçimi kaldır</button>
    </form>
    <form data-order-form method="post" action="<?= e(url('admin.banners.order')) ?>" hidden><?= csrf_field() ?><input type="hidden" name="revision" value="<?= e(hash('sha256', json_encode($order))) ?>"><input type="hidden" name="do" value="set"></form>
    <p id="bw-drag-help" class="sr-only">Sürükleyerek ya da yukarı ve aşağı ok tuşlarıyla bir sıra taşı. Filtre veya farklı sıralama açıkken sıra değiştirilemez.</p>
<?php endif ?>
  </div>

  <div class="bw-table-wrap" data-view-pane="tablo"<?= $view === 'tablo' ? '' : ' hidden' ?>>
    <table class="bw-table" role="table">
      <caption class="sr-only">Anasayfa slaytları. Sütun başlıklarıyla sıralanabilir.</caption>
      <thead role="rowgroup">
        <tr role="row">
<?php if ($canEdit): ?>
          <th scope="col" class="bw-col-select"><span class="sr-only">Seç</span></th>
<?php endif ?>
          <?= $sortHead('sira', 'Sıra') ?>
          <?= $sortHead('baslik', 'Görsel ve başlık') ?>
          <?= $sortHead('kategori', 'Kategori') ?>
          <?= $sortHead('durum', 'Durum') ?>
          <?= $sortHead('guncelleme', 'Güncelleme') ?>
          <th scope="col" class="bw-col-actions"><span class="sr-only">İşlemler</span></th>
        </tr>
      </thead>
      <tbody role="rowgroup" data-list="tablo">
<?php foreach ($rows as $row): ?>
        <tr role="row" id="<?= e($row['anchor']) ?>" class="bw-row<?= $row['live'] ? '' : ' is-off' ?>"<?= $attrs($row) ?>>
<?php if ($canEdit): ?>
          <td role="cell" class="bw-col-select"><?= $select($row) ?></td>
<?php endif ?>
          <td role="cell" class="bw-col-sira"><div class="bw-order"><?= $canEdit ? $grip($row) : '' ?><span class="bw-pos" aria-label="Sıra <?= $row['position'] + 1 ?>"><?= $row['position'] + 1 ?></span></div></td>
          <td role="cell" class="bw-col-baslik">
            <div class="bw-main">
              <a class="bw-thumb" href="<?= e($row['edit']) ?>" tabindex="-1" aria-hidden="true"><?= $thumb($row, 400) ?><span class="bw-thumb-pos"><?= $row['position'] + 1 ?></span></a>
              <div class="bw-text">
                <a class="bw-title" href="<?= e($row['edit']) ?>" title="<?= e($row['title']) ?>"><?= e($row['title']) ?></a>
                <span class="bw-meta"><?= $category($row) ?><span class="bw-date mono"><?= e(tr_date($row['updated'])) ?></span></span>
              </div>
            </div>
          </td>
          <td role="cell" class="bw-col-kategori"><?= $category($row) ?></td>
          <td role="cell" class="bw-col-durum"><?= $status($row) ?></td>
          <td role="cell" class="bw-col-guncelleme"><time class="mono" datetime="<?= e($row['updated']) ?>"><?= e(tr_date($row['updated'])) ?></time></td>
          <td role="cell" class="bw-col-actions"><div class="bw-actions">
            <a class="btn btn-ghost btn-sm bw-edit-btn" href="<?= e($row['edit']) ?>" aria-label="<?= e(($canEdit ? 'Düzenle: ' : 'İncele: ') . $row['title']) ?>"><?= icon($canEdit ? 'edit' : 'eye', 16) ?><span aria-hidden="true"><?= $canEdit ? 'Düzenle' : 'İncele' ?></span></a>
            <?= $menu($row) ?>
          </div></td>
        </tr>
<?php endforeach ?>
      </tbody>
    </table>
  </div>

  <ul class="bw-tiles" data-view-pane="kart" data-list="kart"<?= $view === 'kart' ? '' : ' hidden' ?> aria-label="Anasayfa slaytları">
<?php foreach ($rows as $row): ?>
    <li class="bw-tile<?= $row['live'] ? '' : ' is-off' ?>"<?= $attrs($row) ?>>
      <div class="bw-tile-media">
        <a class="bw-thumb" href="<?= e($row['edit']) ?>" tabindex="-1" aria-hidden="true"><?= $thumb($row, 900) ?></a>
        <span class="bw-tile-pos" aria-label="Sıra <?= $row['position'] + 1 ?>"><?= $row['position'] + 1 ?></span>
<?php if ($canEdit): ?>
        <span class="bw-tile-check"><?= $select($row) ?></span>
        <?= $grip($row) ?>
<?php endif ?>
      </div>
      <div class="bw-tile-body">
        <h2 class="bw-tile-title"><a class="bw-title" href="<?= e($row['edit']) ?>" title="<?= e($row['title']) ?>"><?= e($row['title']) ?></a></h2>
        <div class="bw-tile-meta"><?= $category($row) ?><?= $status($row) ?></div>
      </div>
      <div class="bw-tile-actions">
        <a class="btn btn-ghost btn-sm bw-edit-btn" href="<?= e($row['edit']) ?>" aria-label="<?= e(($canEdit ? 'Düzenle: ' : 'İncele: ') . $row['title']) ?>"><?= icon($canEdit ? 'edit' : 'eye', 16) ?><span aria-hidden="true"><?= $canEdit ? 'Düzenle' : 'İncele' ?></span></a>
        <?= $menu($row) ?>
      </div>
    </li>
<?php endforeach ?>
  </ul>

  <div class="empty bw-empty" data-no-results<?= $visible ? ' hidden' : '' ?>>
    <?= icon('search', 32) ?>
    <h4>Filtreye uygun slayt yok</h4>
    <p>Aramayı ya da seçili kategoriyi değiştir.</p>
    <a class="btn btn-ghost" href="<?= e(url('admin.banners')) ?>" data-filter-reset><?= icon('x', 16) ?>Filtreleri temizle</a>
  </div>
<?php else: ?>
  <div class="empty bw-empty">
    <?= icon('banner', 40) ?>
    <h4>Henüz slayt yok</h4>
    <p><?= $canEdit ? 'Bir tanıtım slaytı oluştur ya da yayındaki bir faaliyet veya duyuruyu ekle.' : 'Slayt eklendiğinde burada listelenir.' ?></p>
  </div>
<?php endif ?>

<?php if ($canEdit): ?>
<?php foreach (['activity' => $activityCandidates, 'news' => $newsCandidates] as $source => $choices): $isAct = $source === 'activity'; ?>
  <details class="bw-picker-fallback" id="slayt-sec-<?= e($source) ?>" data-source-picker="<?= e($source) ?>"><summary><?= $isAct ? 'Faaliyet seç' : 'Haber & Duyuru seç' ?></summary>
    <div class="bw-picker-body" data-picker-body>
      <header class="bw-picker-head"><div><h2><?= $isAct ? 'Faaliyet Ekle' : 'Haber & Duyuru Ekle' ?></h2><p class="f-hint">Yayındaki içeriklerden birini seç. Görsel ve başlık otomatik alınır.</p></div><button type="button" class="btn-icon" data-picker-close aria-label="Pencereyi kapat" hidden><?= icon('x', 18) ?></button></header>
      <div class="bw-picker-filters" data-picker-tools hidden><div class="field"><label for="pick-category-<?= e($source) ?>">Kategori</label><select id="pick-category-<?= e($source) ?>" data-picker-category><option value="">Tüm kategoriler</option><?php foreach ($isAct ? Activity::CATEGORIES : News::CATEGORIES as $cat => $label): ?><option value="<?= e($cat) ?>"><?= e($label) ?></option><?php endforeach ?></select></div><div class="field"><label for="pick-search-<?= e($source) ?>">İçerik ara</label><input id="pick-search-<?= e($source) ?>" type="search" placeholder="Başlığa göre ara" data-picker-search></div></div>
      <form method="post" action="<?= e(url($isAct ? 'admin.banners.activity' : 'admin.banners.news')) ?>" data-picker-form>
        <?= csrf_field() ?><input type="hidden" name="do" value="add"><input type="hidden" name="return" value="<?= e($returnTo) ?>" data-return>
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
