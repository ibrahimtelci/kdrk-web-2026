<?php
/**
 * @var array|null $u
 * @var array $old
 * @var array $errors
 */
use App\Controllers\Admin\UserController;
use App\Core\Auth;

$isNew = $u === null;
$v = static fn (string $k, string $d = ''): string => (string) ($old[$k] ?? $u[$k] ?? $d);
$err = static fn (string $k): string => isset($errors[$k]) ? '<span class="f-err" id="err-' . $k . '">' . e($errors[$k]) . '</span>' : '';
$inv = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$active = array_key_exists('name', $old) ? !empty($old['is_active']) : (bool) ($u['is_active'] ?? true);
?>
<div class="page-head"><a class="back-link" href="<?= e(url('admin.users')) ?>"><?= icon('back', 16) ?>Kullanıcılar</a></div>
<form method="post" data-dirty-guard novalidate action="<?= e($isNew ? url('admin.users.store') : url('admin.users.update', ['id' => $u['id']])) ?>">
  <?= csrf_field() ?>
  <section class="panel">
    <div class="panel-body form-grid">
      <div class="field"><label for="u-name">Ad Soyad</label><input id="u-name" name="name" value="<?= e($v('name')) ?>" autocomplete="off" required<?= $inv('name') ?>><?= $err('name') ?></div>
      <div class="field"><label for="u-email">E-posta</label><input id="u-email" name="email" type="email" value="<?= e($v('email')) ?>" autocomplete="off" inputmode="email" autocapitalize="off"<?= $inv('email') ?>><?= $err('email') ?></div>
      <div class="field"><label for="u-phone">Cep telefonu</label><input id="u-phone" name="phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="40" value="<?= e($v('phone')) ?>" placeholder="0532 123 45 67"<?= $inv('phone') ?>><span class="f-hint">Faaliyete rehber olarak atanınca adı ve telefonu sitede ve paylaşım görselinde görünür.</span><?= $err('phone') ?></div>
      <div class="field"><label for="u-role">Rol</label>
        <select id="u-role" name="role"<?= $inv('role') ?>>
<?php foreach (Auth::ROLES as $k => $label): ?>
          <option value="<?= $k ?>"<?= $v('role', 'guide') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
        </select><?= $err('role') ?></div>
      <div class="field"><label for="u-password"><?= $isNew ? 'Parola' : 'Yeni parola' ?></label><input id="u-password" name="password" type="password" autocomplete="new-password" minlength="<?= UserController::MIN_PASSWORD ?>"<?= $inv('password') ?>><span class="f-hint">Aktif hesap için en az <?= UserController::MIN_PASSWORD ?> karakter. Mevcut parolayı korumak için boş bırak.</span><?= $err('password') ?></div>
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?>><span>Panele giriş yapabilir</span></label><span class="f-hint">Giriş için geçerli e-posta ve parola gerekir. Aktif görevli yalnızca atandığı faaliyetleri ve katılımcıları görür; başvuruları onaylayabilir. Pasif hesap giriş yapamaz. Ayrılan kişilerin hesabını silmek yerine pasif yap; işlem kayıtları korunur.</span></div>
    </div>
  </section>
  <div class="save-bar">
    <div class="tb-spacer"></div>
    <a class="btn btn-ghost" href="<?= e(url('admin.users')) ?>">Vazgeç</a>
    <button type="submit" class="btn btn-primary"><?= icon('check') ?><?= $isNew ? 'Kullanıcıyı Ekle' : 'Kaydet' ?></button>
  </div>
</form>
