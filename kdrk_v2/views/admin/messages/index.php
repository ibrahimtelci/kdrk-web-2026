<?php
/**
 * @var array $rows
 * @var bool  $unreadOnly
 */
?>
<?php $listView = admin_list_view('mesajlar'); ?>
<?= $this->partial('admin/_list-head', ['description' => 'İletişim formundan gelen mesajları incele.', 'createUrl' => null]) ?>
<?= $this->partial('admin/_list-toolbar', ['filterLabel' => 'Mesaj filtresi', 'tabs' => [
    ['href' => url('admin.messages'), 'label' => 'Tümü', 'active' => !$unreadOnly, 'icon' => 'view-grid'],
    ['href' => url('admin.messages', ['filtre' => 'okunmamis']), 'label' => 'Okunmamış', 'active' => $unreadOnly, 'icon' => 'mail'],
], 'search' => null, 'viewSwitch' => ['key' => 'mesajlar', 'current' => $listView]]) ?>
<div class="panel ui-list" data-view-key="mesajlar" data-list-view="<?= e($listView) ?>">
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
