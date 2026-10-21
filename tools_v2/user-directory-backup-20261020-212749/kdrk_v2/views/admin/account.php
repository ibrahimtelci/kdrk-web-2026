<?php
/**
 * @var array $u
 * @var array $errors
 */
use App\Controllers\Admin\UserController;
use App\Core\Auth;

$err = static fn (string $k): string => isset($errors[$k]) ? '<span class="f-err" id="err-' . $k . '">' . e($errors[$k]) . '</span>' : '';
$inv = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
?>
<section class="panel">
  <div class="panel-head"><h2><?= e($u['name']) ?></h2><span class="chip gray"><?= e(Auth::ROLES[$u['role']]) ?></span></div>
  <div class="panel-body"><p class="muted"><?= e($u['email']) ?></p></div>
</section>
<form method="post" action="<?= e(url('admin.account.update')) ?>" novalidate>
  <?= csrf_field() ?>
  <input type="email" name="username" value="<?= e($u['email']) ?>" autocomplete="username" hidden>
  <section class="panel">
    <div class="panel-head"><h2>Parola değiştir</h2></div>
    <div class="panel-body form-grid">
      <div class="field col-span"><label for="a-current">Mevcut parola</label><input id="a-current" name="current" type="password" autocomplete="current-password" required<?= $inv('current') ?>><?= $err('current') ?></div>
      <div class="field"><label for="a-password">Yeni parola</label><input id="a-password" name="password" type="password" autocomplete="new-password" minlength="<?= UserController::MIN_PASSWORD ?>" required<?= $inv('password') ?>><span class="f-hint">En az <?= UserController::MIN_PASSWORD ?> karakter. Uzun bir cümle kısa ve karmaşık paroladan daha güvenlidir.</span><?= $err('password') ?></div>
      <div class="field"><label for="a-confirm">Yeni parola (tekrar)</label><input id="a-confirm" name="password_confirm" type="password" autocomplete="new-password" required<?= $inv('password_confirm') ?>><?= $err('password_confirm') ?></div>
    </div>
  </section>
  <div class="save-bar">
    <div class="tb-spacer"></div>
    <button type="submit" class="btn btn-primary"><?= icon('check') ?>Parolayı Değiştir</button>
  </div>
</form>
