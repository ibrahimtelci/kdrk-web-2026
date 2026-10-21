<?php
$people = \App\Models\GuideContact::all();
?>
<div class="field col-span" data-guide-picker>
<span class="field-label">Faaliyet rehberleri</span><p class="f-hint">İki rehber seç. Adları ve telefonları faaliyet detayında ve paylaşım görselinde görünür.</p>
<div class="form-grid">
<?php foreach (['guide_primary_id' => '1. rehber', 'guide_secondary_id' => '2. rehber'] as $key => $label): ?>
<div class="field"><label for="f-<?= e($key) ?>"><?= e($label) ?></label><select name="<?= e($key) ?>" id="f-<?= e($key) ?>" data-guide-select<?= $inv($key) ?>>
<option value="">Rehber seç…</option>
<?php foreach ($people as $person): ?><option value="<?= (int) $person['id'] ?>"<?= (string) $v($key) === (string) $person['id'] ? ' selected' : '' ?>><?= e($person['name'] . ' · ' . $person['phone']) ?></option><?php endforeach ?>
<?php if (!$ro): ?><option value="__new">+ Yeni kişi ekle…</option><?php endif ?>
</select><?= $err($key) ?></div>
<?php endforeach ?>
</div></div>
