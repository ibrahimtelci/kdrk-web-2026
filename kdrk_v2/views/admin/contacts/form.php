<?php
use App\Core\Auth;
$ro = !Auth::canEdit();
$value = static fn (string $key): string => (string) ($old[$key] ?? $person[$key] ?? '');
?>
<form method="post" action="<?= e($person ? url('admin.contacts.update', ['id' => $person['id']]) : url('admin.contacts.store')) ?>" class="panel">
<?= csrf_field() ?><?php if ($modal): ?><input type="hidden" name="_pencere" value="1"><?php endif ?>
<div class="panel-body form-grid"><fieldset class="col-span"<?= $ro ? ' disabled' : '' ?> style="border:0;padding:0;margin:0;min-width:0">
<div class="field"><label for="person-name">Ad soyad *</label><input id="person-name" name="name" required minlength="3" maxlength="120" autocomplete="name" value="<?= e($value('name')) ?>"><?php if (isset($errors['name'])): ?><p class="f-error"><?= e($errors['name']) ?></p><?php endif ?></div>
<div class="field"><label for="person-phone">Telefon *</label><input id="person-phone" name="phone" required type="tel" maxlength="40" autocomplete="tel" placeholder="05XX XXX XX XX" value="<?= e($value('phone')) ?>"><?php if (isset($errors['phone'])): ?><p class="f-error"><?= e($errors['phone']) ?></p><?php endif ?></div>
</fieldset><p class="f-hint col-span">Faaliyete rehber olarak seçildiğinde adı ve telefonu faaliyet detayında ve paylaşım görselinde görünür.</p>
<div class="col-span"><?php if (!$ro): ?><button class="btn btn-primary" type="submit" data-busy="Kaydediliyor…">Kaydet</button><?php endif ?> <a class="btn btn-ghost" href="<?= e(url('admin.contacts')) ?>">Listeye dön</a></div></div></form>
