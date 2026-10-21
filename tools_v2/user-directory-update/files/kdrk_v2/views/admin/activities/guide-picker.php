<?php
$people = \App\Models\GuideContact::all();
?>
<div class="field col-span" data-guide-picker>
<span class="field-label">Faaliyet görevlileri / rehberleri</span><p class="f-hint">Kullanıcılar arasından iki görevli seç. Adları ve telefonları faaliyet detayında ve paylaşım görselinde görünür.</p>
<div class="form-grid">
<?php foreach (['guide_primary_user_id' => '1. rehber', 'guide_secondary_user_id' => '2. rehber'] as $key => $label): ?>
<div class="field"><label for="f-<?= e($key) ?>"><?= e($label) ?></label><select name="<?= e($key) ?>" id="f-<?= e($key) ?>" data-guide-select<?= $inv($key) ?>>
<option value="">Rehber seç…</option>
<?php foreach ($people as $person): $selected = (string) $v($key) === (string) $person['id']; if ((!$person['is_active'] || !in_array($person['role'], ['admin', 'editor', 'guide'], true)) && !$selected) continue; ?><option value="<?= (int) $person['id'] ?>"<?= (string) $v($key) === (string) $person['id'] ? ' selected' : '' ?>><?= e($person['name'] . ' · ' . $person['phone'] . (!$person['is_active'] ? ' (giriş kapalı)' : '')) ?></option><?php endforeach ?>

</select><?= $err($key) ?></div>
<?php endforeach ?>
</div>
<?php if (\App\Core\Auth::is('admin')): ?><p class="f-hint"><a href="<?= e(url('admin.users.new')) ?>" target="_blank" rel="noopener">Yeni görevli kullanıcı ekle</a> · Kaydettikten sonra bu listeyi yenile.</p><?php endif ?>
</div>
