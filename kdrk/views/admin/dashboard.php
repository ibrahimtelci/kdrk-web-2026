<?php
/**
 * @var array $stats
 * @var array $next
 * @var array $recent
 * @var array $messages
 */
use App\Core\Auth;

$S = require __DIR__ . '/_status.php';
$chip = static fn (array $c): string => '<span class="chip ' . $c[1] . ' dot">' . e($c[0]) . '</span>';
$cards = [
    ['activities', 'a', $stats['open'], 'Başvurusu açık faaliyet', url('admin.activities')],
    ['users', 'd', $stats['pending'], 'Bekleyen başvuru', url('admin.activities', ['durum' => 'bekleyen'])],
    ['check', 'b', $stats['done'], 'Tamamlanan faaliyet', url('admin.activities', ['durum' => 'gecmis'])],
    ['messages', 'c', $stats['unread'], 'Okunmamış mesaj', Auth::is('admin', 'editor') ? url('admin.messages') : null],
];
?>
<div class="stat-cards">
<?php foreach ($cards as [$ic, $cls, $num, $label, $href]): ?>
  <<?= $href ? 'a href="' . e($href) . '"' : 'div' ?> class="stat-card"><div class="sc-top"><span class="sc-icon <?= $cls ?>"><?= icon($ic) ?></span></div><div class="sc-num"><?= (int) $num ?></div><div class="sc-label"><?= e($label) ?></div></<?= $href ? 'a' : 'div' ?>>
<?php endforeach ?>
</div>

<?php if (Auth::canEdit()): ?>
<div class="panel callout">
  <div class="panel-body callout-body">
    <div><div class="callout-title">Yeni faaliyet</div><div class="muted">Faaliyeti ekle, sitede kartı ve başvuru formu kendiliğinden açılır. Tarihi geçince "Tamamlandı" olur.</div></div>
    <a class="btn btn-primary" href="<?= e(url('admin.activities.new')) ?>" data-modal-link><?= icon('plus') ?>Yeni Faaliyet</a>
  </div>
</div>
<?php endif ?>

<div class="dash-grid">
  <section class="panel">
    <div class="panel-head"><h2>Yaklaşan faaliyetler</h2><a class="btn btn-ghost btn-sm" href="<?= e(url('admin.activities')) ?>">Tümü</a></div>
<?php if ($next): ?>
    <ul class="row-list">
<?php foreach ($next as $a): ?>
      <li><a href="<?= e(url('admin.activities.edit', ['id' => $a['id']])) ?>">
        <span class="rl-date mono"><?= e(tr_date($a['starts_on'])) ?></span>
        <span class="rl-main"><span class="rl-title"><?= e($a['title']) ?></span><span class="rl-sub muted"><?= (int) $a['taken'] ?><?= $a['quota'] ? ' / ' . (int) $a['quota'] : '' ?> kişi</span></span>
        <?= $chip($S['activity'][$a['status']]) ?>
      </a></li>
<?php endforeach ?>
    </ul>
<?php else: ?>
    <div class="empty"><?= icon('activities', 40) ?><h4>Yaklaşan faaliyet yok</h4><p>Takvime yeni faaliyet ekleyebilirsin.</p></div>
<?php endif ?>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Son başvurular</h2></div>
<?php if ($recent): ?>
    <ul class="row-list">
<?php foreach ($recent as $p): ?>
      <li><a href="<?= e(url('admin.applications', ['id' => $p['activity_id']])) ?>">
        <span class="rl-main"><span class="rl-title"><?= e($p['name']) ?></span><span class="rl-sub muted"><?= e($p['title']) ?> · <?= e(tr_date(substr($p['created_at'], 0, 10))) ?></span></span>
        <?= $chip($S['application'][$p['status']]) ?>
      </a></li>
<?php endforeach ?>
    </ul>
<?php else: ?>
    <div class="empty"><?= icon('users', 40) ?><h4>Henüz başvuru yok</h4><p>Sitedeki formdan gelen başvurular burada görünür.</p></div>
<?php endif ?>
<?php if ($messages && Auth::is('admin', 'editor')): ?>
    <div class="panel-head panel-head-sub"><h2>Okunmamış mesajlar</h2><a class="btn btn-ghost btn-sm" href="<?= e(url('admin.messages')) ?>">Tümü</a></div>
    <ul class="row-list">
<?php foreach ($messages as $m): ?>
      <li><a href="<?= e(url('admin.messages.show', ['id' => $m['id']])) ?>"><span class="rl-main"><span class="rl-title"><?= e($m['name']) ?></span><span class="rl-sub muted"><?= e($m['subject']) ?></span></span><span class="mono muted"><?= e(tr_date(substr($m['created_at'], 0, 10), false)) ?></span></a></li>
<?php endforeach ?>
    </ul>
<?php endif ?>
  </section>
</div>
