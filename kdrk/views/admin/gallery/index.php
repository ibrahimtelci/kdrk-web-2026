<?php
/**
 * Albüm listesi: yıla göre kartlar.
 * @var array  $years   yıl => albüm satırları
 * @var int    $count
 * @var string $filter
 * @var array  $filters
 * @var string $q
 */
use App\Core\Auth;
?>
<div class="toolbar">
  <form class="search-box" method="get" action="<?= e(url('admin.gallery')) ?>" role="search">
    <?= icon('search') ?>
    <input type="hidden" name="durum" value="<?= e($filter) ?>">
    <input type="search" name="q" value="<?= e($q) ?>" placeholder="Albüm ara…" aria-label="Albüm ara">
  </form>
  <nav class="seg" aria-label="Albüm filtresi">
<?php foreach ($filters as $k => $label): ?>
    <a href="<?= e(url('admin.gallery', array_filter(['durum' => $k === 'tumu' ? null : $k, 'q' => $q]))) ?>" class="<?= $filter === $k ? 'is-active' : '' ?>"<?= $filter === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
<?php endforeach ?>
  </nav>
  <div class="tb-spacer"></div>
<?php if (Auth::canEdit()): ?>
  <a class="btn btn-primary" href="<?= e(url('admin.gallery.new')) ?>"><?= icon('plus') ?>Yeni Albüm</a>
<?php endif ?>
</div>

<?php if ($count): ?>
<?php foreach ($years as $year => $albums): ?>
<section class="year-block">
  <div class="year-head"><h2 class="year-num"><?= e((string) $year) ?></h2><span class="muted mono"><?= count($albums) ?> ALBÜM · <?= array_sum(array_column($albums, 'photo_count')) ?> FOTOĞRAF</span></div>
  <div class="card-grid">
<?php foreach ($albums as $g): ?>
    <a class="g-card" href="<?= e(url('admin.gallery.edit', ['id' => $g['id']])) ?>">
      <div class="g-cover">
        <?php if ($g['cover']): ?><img src="<?= e(img($g['cover'], 400)) ?>" alt="" loading="lazy"><?php endif ?>
        <span class="g-count"><?= icon('gallery', 13) ?> <?= (int) $g['photo_count'] ?></span>
<?php if (!$g['is_published']): ?>
        <span class="g-src manual">Yayında değil</span>
<?php elseif (!$g['photo_count']): ?>
        <span class="g-src manual">Fotoğraf yok</span>
<?php endif ?>
      </div>
      <div class="g-body">
        <div class="g-title"><?= e($g['title']) ?></div>
        <div class="muted g-sub"><?= $g['activity_title'] ? 'Faaliyet: ' . e($g['activity_title']) : 'Faaliyete bağlı değil' ?></div>
        <div class="g-meta"><span class="muted mono"><?= $g['shot_on'] ? e(tr_date($g['shot_on'])) : '—' ?></span></div>
      </div>
    </a>
<?php endforeach ?>
  </div>
</section>
<?php endforeach ?>
<?php else: ?>
<div class="panel">
  <div class="empty"><?= icon('gallery', 40) ?><h4><?= $q !== '' || $filter !== 'tumu' ? 'Albüm bulunamadı' : 'Henüz albüm yok' ?></h4><p><?= $q !== '' ? 'Aramanı değiştirip tekrar dene.' : 'Faaliyet fotoğraflarını yüklemek için yeni bir albüm oluştur.' ?></p></div>
</div>
<?php endif ?>
