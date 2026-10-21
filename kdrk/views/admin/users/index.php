<?php
/** @var array $rows */
use App\Core\Auth;

$roleColor = ['admin' => 'orange', 'editor' => 'blue', 'viewer' => 'gray'];
?>
<div class="toolbar">
  <p class="muted toolbar-note"><b>Admin</b> her şeyi yapar, TC ve sağlık bilgisini görür. <b>Editör</b> faaliyet, başvuru ve mesajları yönetir. <b>İzleme</b> yalnızca okur.</p>
  <div class="tb-spacer"></div>
  <a class="btn btn-primary" href="<?= e(url('admin.users.new')) ?>" data-modal-link><?= icon('plus') ?>Yeni Kullanıcı</a>
</div>
<div class="panel">
  <ul class="row-list">
<?php foreach ($rows as $u): ?>
    <li class="<?= $u['is_active'] ? '' : 'is-muted' ?>"><a href="<?= e(url('admin.users.edit', ['id' => $u['id']])) ?>">
      <span class="mi-ava" aria-hidden="true"><?= e(mb_substr($u['name'], 0, 1)) ?></span>
      <span class="rl-main"><span class="rl-title"><?= e($u['name']) ?><?= (int) $u['id'] === (int) Auth::user()['id'] ? ' <span class="muted">(sen)</span>' : '' ?></span><span class="rl-sub muted"><?= e($u['email']) ?> · son giriş: <?= $u['last_login_at'] ? e(tr_date(substr($u['last_login_at'], 0, 10))) : 'hiç' ?></span></span>
      <span class="chip <?= $roleColor[$u['role']] ?>"><?= e(Auth::ROLES[$u['role']]) ?></span>
      <?php if (!$u['is_active']): ?><span class="chip gray">Pasif</span><?php endif ?>
    </a></li>
<?php endforeach ?>
  </ul>
</div>
