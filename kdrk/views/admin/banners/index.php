<?php
/**
 * Anasayfa slaytları — üç sekme: tanıtım, faaliyet, duyuru & haber.
 * @var array  $fixed      Banner::forPanel()['fixed']      (tanıtım slaytları)
 * @var array  $activities Banner::forPanel()['activities']
 * @var array  $news       Banner::forPanel()['news']
 * @var array  $candidates slayta eklenebilecek kayıtlar (faaliyet / haber sekmesinde)
 * @var string $tab        tanitim|faaliyet|haber
 */
use App\Core\Auth;
use App\Models\Banner;
use App\Models\News;

$canEdit = Auth::canEdit();
$live = array_values(array_filter($fixed, static fn ($r) => $r['on_site']));
$onSite = static fn (array $rows): int => count(array_filter($rows, static fn ($r) => $r['on_site']));
$queued = static fn (array $rows, string $key): int => count(array_filter($rows, static fn ($r) => $r[$key] === 'Sırada (ilk 3 dışında)'));
$liveCount = count($live) + $onSite($activities) + $onSite($news);
$extra = $queued($fixed, 'state') + $queued($activities, 'slide_state') + $queued($news, 'slide_state');
$colors = ['Sitede' => 'green', 'Zamanlandı' => 'blue', 'Süresi doldu' => 'gray', 'Kapalı' => 'gray', 'Sırada (ilk 3 dışında)' => 'orange',
    'Yayında değil' => 'gray', 'Taslak' => 'gray', 'İptal edildi' => 'gray', 'Ertelendi' => 'gray', 'Kapak görseli yok' => 'orange'];
$tabs = [
    'tanitim'  => ['Tanıtım', count($fixed)],
    'faaliyet' => ['Faaliyet', count($activities)],
    'haber'    => ['Duyuru & Haber', count($news)],
];
?>
<div class="toolbar">
  <nav class="seg" aria-label="Slayt türü">
<?php foreach ($tabs as $k => [$label, $n]): ?>
    <a href="<?= e(url('admin.banners', $k === 'tanitim' ? [] : ['sekme' => $k])) ?>" class="<?= $tab === $k ? 'is-active' : '' ?>"<?= $tab === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> (<?= $n ?>)</a>
<?php endforeach ?>
  </nav>
  <div class="tb-spacer"></div>
<?php if ($canEdit && $tab === 'tanitim'): ?>
  <a class="btn btn-primary" href="<?= e(url('admin.banners.new')) ?>" data-modal-link><?= icon('plus') ?>Yeni Tanıtım Slaytı</a>
<?php endif ?>
</div>

<p class="toolbar-note bn-note muted">Anasayfada en fazla <?= Banner::MAX ?> slayt görünür. Sıra: <strong>1.</strong> ilk tanıtım slaytı (kulübü anlatır) → <strong>2.</strong> slayta eklenen faaliyetler, en yakın tarihli önce → <strong>3.</strong> slayta eklenen duyuru &amp; haberler, en yeni önce → boş yer kalırsa diğer tanıtım slaytları. Faaliyet tarihi geçince, duyuru da "slaytta gösterim bitişi" gelince kendiliğinden kalkar. Şu an sitede <?= $liveCount ?> slayt var.</p>

<?php if ($live && $live[0]['show_until']): ?>
<div class="flash flash-error" role="alert"><?= icon('x') ?><span>Sitedeki ilk slaytın bitiş tarihi var (<?= e(tr_date($live[0]['show_until'])) ?>). İlk sıraya kulübü tanıtan, tarihsiz bir slayt taşı.</span></div>
<?php endif ?>
<?php if (!$live): ?>
<div class="flash flash-error" role="alert"><?= icon('x') ?><span>Sitede etkin tanıtım slaytı yok. İlk slayt kulübü tanıtmalı; en az bir tanıtım slaytı ekle.</span></div>
<?php endif ?>
<?php if ($extra): ?>
<div class="flash flash-info"><?= icon('eye') ?><span><?= $extra ?> slayt ilk <?= Banner::MAX ?> dışında kaldığı için sitede görünmüyor.</span></div>
<?php endif ?>

<?php if ($tab === 'tanitim'): ?>
<div class="panel">
<?php if ($fixed): ?>
  <ol class="bn-list">
<?php foreach ($fixed as $i => $b): ?>
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
        <button type="submit" name="do" value="down" class="btn-icon" aria-label="<?= e(fill_stats($b['title'])) ?> aşağı taşı"<?= $i === count($fixed) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button>
      </form>
<?php endif ?>
    </li>
<?php endforeach ?>
  </ol>
<?php else: ?>
  <div class="empty"><?= icon('banner', 40) ?><h4>Henüz tanıtım slaytı yok</h4><p>Anasayfanın üst bölümünde kulübü tanıtan ilk slaytı ekle.</p></div>
<?php endif ?>
</div>

<?php else:
    $isAct = $tab === 'faaliyet';
    $rows = $isAct ? $activities : $news;
    $idField = $isAct ? 'activity_id' : 'news_id';
    $action = url($isAct ? 'admin.banners.activity' : 'admin.banners.news');
?>
<?php if ($canEdit): ?>
<form method="post" action="<?= e($action) ?>" class="panel bn-add">
  <?= csrf_field() ?>
  <input type="hidden" name="do" value="add">
  <div class="panel-body">
<?php if ($candidates): ?>
    <div class="field">
      <label for="f-source"><?= $isAct ? 'Slayta faaliyet ekle' : 'Slayta duyuru veya haber ekle' ?></label>
      <select id="f-source" name="<?= $idField ?>" required>
        <option value=""><?= $isAct ? 'Faaliyet seç…' : 'Duyuru veya haber seç…' ?></option>
<?php foreach ($candidates as $c):
    $label = $isAct ? tr_date($c['starts_on']) . ' · ' . $c['title'] : tr_date($c['published_on']) . ' · ' . (News::CATEGORIES[$c['category']] ?? '') . ' · ' . $c['title']; ?>
        <option value="<?= (int) $c['id'] ?>"<?= $c['cover_image'] ? '' : ' disabled' ?>><?= e($label . ($c['cover_image'] ? '' : ' (kapak görseli yok)')) ?></option>
<?php endforeach ?>
      </select>
<?php if ($isAct): ?>
      <span class="f-hint">Başlık, açıklama, görsel, tarih ve zorluk faaliyetten gelir. "Faaliyete Başvur" ve "Faaliyet Ayrıntıları" butonları kendiliğinden eklenir. Faaliyet formundaki "Anasayfa slaytında göster" kutusu da aynı işi yapar.</span>
<?php else: ?>
      <span class="f-hint">Başlık, özet ve kapak görseli kayıttan gelir. "Devamını Oku" ve "Tüm Duyuru &amp; Haberler" butonları kendiliğinden eklenir. Slayttan kalkış tarihini kaydın formundaki "Slaytta gösterim bitişi" alanından verebilirsin.</span>
<?php endif ?>
    </div>
    <button type="submit" class="btn btn-primary"><?= icon('plus') ?>Slayta Ekle</button>
<?php else: ?>
    <p class="muted"><?= $isAct ? 'Eklenebilecek yaklaşan faaliyet yok. Slayta yalnız tarihi geçmemiş, yayındaki faaliyetler eklenir.' : 'Eklenebilecek yayında duyuru veya haber yok.' ?></p>
<?php endif ?>
  </div>
</form>
<?php endif ?>

<div class="panel">
<?php if ($rows): ?>
  <ol class="bn-list">
<?php foreach ($rows as $r):
    $edit = url($isAct ? 'admin.activities.edit' : 'admin.news.edit', ['id' => $r['id']]);
    $sub = $isAct
        ? tr_range($r['starts_on'], $r['ends_on']) . ' · ' . $r['status_text']
        : $r['category_text'] . ($r['published_on'] ? ' · ' . tr_date($r['published_on']) : '') . ($r['slider_until'] ? ' · slaytta ' . tr_date($r['slider_until']) . ' gününe kadar' : ''); ?>
    <li class="bn-item bn-act<?= $r['on_site'] ? '' : ' is-off' ?>" id="<?= $isAct ? 'faaliyet' : 'haber' ?>-<?= (int) $r['id'] ?>">
      <a class="bn-main" href="<?= e($edit) ?>">
<?php if ($r['cover_image']): ?>
        <img class="bn-thumb" src="<?= e(img($r['cover_image'], 400)) ?>" alt="" loading="lazy">
<?php else: ?>
        <span class="bn-thumb"></span>
<?php endif ?>
        <span class="bn-text">
          <span class="t-title"><?= e($r['title']) ?></span>
          <span class="t-sub muted"><?= e($sub) ?></span>
        </span>
      </a>
      <span class="chip <?= $colors[$r['slide_state']] ?> dot bn-state"><?= e($r['slide_state']) ?></span>
<?php if ($canEdit): ?>
      <form method="post" action="<?= e($action) ?>" class="bn-move">
        <?= csrf_field() ?>
        <input type="hidden" name="<?= $idField ?>" value="<?= (int) $r['id'] ?>">
        <button type="submit" name="do" value="remove" class="btn btn-ghost btn-sm" aria-label="<?= e($r['title']) ?> slayttan kaldır"><?= icon('x', 14) ?>Kaldır</button>
      </form>
<?php endif ?>
    </li>
<?php endforeach ?>
  </ol>
<?php else: ?>
  <div class="empty"><?= icon('banner', 40) ?><h4><?= $isAct ? 'Slaytta faaliyet yok' : 'Slaytta duyuru veya haber yok' ?></h4><p><?= $isAct ? 'Öne çıkarmak istediğin yaklaşan faaliyeti yukarıdan ekle. Tarihi geçince kendiliğinden kalkar.' : 'Öne çıkarmak istediğin duyuru veya haberi yukarıdan ekle.' ?></p></div>
<?php endif ?>
</div>
<?php endif ?>
