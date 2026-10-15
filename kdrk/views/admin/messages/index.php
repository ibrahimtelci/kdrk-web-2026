<?php
/**
 * @var array $rows
 * @var bool  $unreadOnly
 */
?>
<div class="toolbar">
  <nav class="seg" aria-label="Mesaj filtresi">
    <a href="<?= e(url('admin.messages')) ?>" class="<?= $unreadOnly ? '' : 'is-active' ?>">Tümü</a>
    <a href="<?= e(url('admin.messages', ['filtre' => 'okunmamis'])) ?>" class="<?= $unreadOnly ? 'is-active' : '' ?>">Okunmamış</a>
  </nav>
</div>
<div class="panel">
<?php if ($rows): ?>
  <ul class="row-list msg-list">
<?php foreach ($rows as $m): ?>
    <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>"><a href="<?= e(url('admin.messages.show', ['id' => $m['id']])) ?>">
      <span class="mi-ava" aria-hidden="true"><?= e(mb_substr($m['name'], 0, 1)) ?></span>
      <span class="rl-main"><span class="rl-title"><?= e($m['name']) ?><?= $m['is_read'] ? '' : ' <span class="chip orange">yeni</span>' ?></span><span class="rl-sub"><b><?= e($m['subject']) ?></b> · <span class="muted"><?= e($m['preview']) ?></span></span></span>
      <span class="mono muted"><?= e(tr_date(substr($m['created_at'], 0, 10), false)) ?></span>
    </a></li>
<?php endforeach ?>
  </ul>
<?php else: ?>
  <div class="empty"><?= icon('messages', 40) ?><h4><?= $unreadOnly ? 'Okunmamış mesaj yok' : 'Henüz mesaj yok' ?></h4><p>Sitedeki iletişim formundan gelen mesajlar burada görünür.</p></div>
<?php endif ?>
</div>
