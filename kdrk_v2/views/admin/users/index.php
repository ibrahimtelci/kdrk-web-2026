<?php
/** @var array $rows */
use App\Core\Auth;

$listView = admin_list_view('kullanicilar');
$roleColor = ['admin' => 'orange', 'editor' => 'blue', 'viewer' => 'gray', 'guide' => 'green'];
?>
<?= $this->partial('admin/_list-head', ['description' => 'Kullanıcı hesaplarını, görevleri ve iletişim bilgilerini yönet.', 'createUrl' => url('admin.users.new'), 'createLabel' => 'Yeni Kullanıcı', 'modalSize' => 'compact']) ?>
<details class="ui-help"><summary><?= icon('info', 16) ?>Roller ve yetkiler</summary><p><b>Admin</b> her şeyi yapar, TC ve sağlık bilgisini görür. <b>Editör</b> faaliyet, başvuru ve mesajları yönetir. <b>Görevli</b> yalnızca atandığı faaliyetlerin katılımcılarını görür ve onaylar. <b>İzleme</b> yalnızca okur.</p></details>
<?= $this->partial('admin/_list-toolbar', ['filterLabel' => 'Kullanıcılar', 'viewSwitch' => ['key' => 'kullanicilar', 'current' => $listView]]) ?>
<div class="panel ui-list" data-view-key="kullanicilar" data-list-view="<?= e($listView) ?>">
  <ul class="row-list">
<?php foreach ($rows as $u): ?>
    <li class="<?= $u['is_active'] ? '' : 'is-muted' ?>"><a href="<?= e(url('admin.users.edit', ['id' => $u['id']])) ?>" data-modal-link data-modal-size="compact" data-modal-title="Kullanıcıyı Düzenle">
      <span class="mi-ava" aria-hidden="true"><?= e(mb_substr($u['name'], 0, 1)) ?></span>
      <span class="rl-main"><span class="rl-title"><?= e($u['name']) ?><?= (int) $u['id'] === (int) Auth::user()['id'] ? ' <span class="muted">(sen)</span>' : '' ?></span><span class="rl-sub muted"><?= e($u['email'] ?: 'Giriş e-postası tanımlanmadı') ?> · <?= e($u['phone'] ?: 'Telefon tanımlanmadı') ?> · son giriş: <?= $u['last_login_at'] ? e(tr_date(substr($u['last_login_at'], 0, 10))) : 'hiç' ?></span></span>
      <span class="chip <?= $roleColor[$u['role']] ?>"><?= e(Auth::ROLES[$u['role']]) ?></span>
      <?php if (!$u['is_active']): ?><span class="chip gray">Pasif</span><?php endif ?>
    </a></li>
<?php endforeach ?>
  </ul>
</div>
