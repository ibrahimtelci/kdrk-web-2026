<?php
/**
 * Albüm ekle / düzenle + fotoğraf yönetimi.
 * @var array|null $g          albüm ya da null
 * @var array      $photos     albümün fotoğrafları (sıralı)
 * @var array      $activities bağlanabilecek faaliyetler
 * @var array      $old
 * @var array      $errors
 */
use App\Core\Auth;
use App\Models\Activity;

$isNew = $g === null;
$ro = !Auth::canEdit();
$v = static function (string $k, mixed $default = '') use ($old, $g): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($g !== null && array_key_exists($k, $g) && $g[$k] !== null) {
        return (string) $g[$k];
    }
    return (string) $default;
};
$err = static fn (string $k): string => isset($errors[$k]) ? '<span class="f-err" id="err-' . $k . '">' . e($errors[$k]) . '</span>' : '';
$inv = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$input = static function (string $k, string $label, string $type = 'text', array $opt = []) use ($v, $err, $inv): string {
    $attrs = '';
    foreach ($opt['attrs'] ?? [] as $n => $val) {
        $attrs .= ' ' . $n . '="' . e($val) . '"';
    }
    return '<div class="field' . (isset($opt['span']) ? ' col-span' : '') . '"><label for="f-' . $k . '">' . e($label) . (!empty($opt['req']) ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>'
        . '<input id="f-' . $k . '" name="' . $k . '" type="' . $type . '" value="' . e($v($k)) . '"' . (!empty($opt['req']) ? ' required' : '') . $attrs . $inv($k) . '>'
        . (isset($opt['hint']) ? '<span class="f-hint">' . e($opt['hint']) . '</span>' : '') . $err($k) . '</div>';
};
$published = array_key_exists('title', $old) ? !empty($old['is_published']) : (bool) ($g['is_published'] ?? 1);
$cover = $g['cover_photo_id'] ?? null;
if ($photos && !$cover) {
    $cover = $photos[0]['id']; // kapak seçilmemişse ilk fotoğraf kullanılır
}
$postMax = ini_get('post_max_size');
?>
<div class="page-head">
  <a class="back-link" href="<?= e(url('admin.gallery')) ?>"><?= icon('back', 16) ?>Fotoğraf Galerisi</a>
<?php if (!$isNew): ?>
  <div class="page-head-actions">
    <span class="chip <?= $g['is_published'] ? 'green' : 'gray' ?> dot"><?= $g['is_published'] ? 'Yayında' : 'Yayında değil' ?></span>
<?php if ($g['is_published'] && $photos): ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('galeri.album', ['slug' => $g['slug']])) ?>" target="_blank" rel="noopener"><?= icon('ext', 14) ?>Sitede gör</a>
<?php endif ?>
  </div>
<?php endif ?>
</div>

<form method="post" class="act-form" novalidate action="<?= e($isNew ? url('admin.gallery.store') : url('admin.gallery.update', ['id' => $g['id']])) ?>">
  <?= csrf_field() ?>
  <fieldset<?= $ro ? ' disabled' : '' ?>>
  <section class="panel">
    <div class="panel-head"><h2>Albüm bilgileri</h2></div>
    <div class="panel-body form-grid">
      <?= $input('title', 'Albüm adı', 'text', ['req' => true, 'span' => true, 'attrs' => ['maxlength' => 160]]) ?>
      <div class="field col-span">
        <label for="f-activity_id">Faaliyet</label>
        <select id="f-activity_id" name="activity_id"<?= $inv('activity_id') ?>>
          <option value="">Faaliyete bağlı değil (eski albüm)</option>
<?php foreach ($activities as $a): ?>
          <option value="<?= (int) $a['id'] ?>"<?= $v('activity_id') === (string) $a['id'] ? ' selected' : '' ?>><?= e(tr_date($a['starts_on']) . ' · ' . $a['title']) ?></option>
<?php endforeach ?>
        </select>
        <span class="f-hint">Bağlarsan tarih, kategori ve yer faaliyetten gelir; fotoğraflar faaliyet sayfasında da görünür.</span><?= $err('activity_id') ?>
      </div>
      <?= $input('taken_on', 'Tarih', 'date', ['hint' => 'Faaliyete bağlıysa boş bırakabilirsin.']) ?>
      <div class="field">
        <label for="f-category">Kategori</label>
        <select id="f-category" name="category"<?= $inv('category') ?>>
          <option value="">Faaliyetten al</option>
<?php foreach (Activity::CATEGORIES as $k => $label): ?>
          <option value="<?= e($k) ?>"<?= $v('category') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
        </select><?= $err('category') ?>
      </div>
      <?= $input('location', 'Yer', 'text', ['span' => true, 'hint' => 'Faaliyete bağlıysa boş bırakabilirsin. Ör. Rize / İkizdere · 3.711 m', 'attrs' => ['maxlength' => 160]]) ?>
      <div class="field col-span">
        <label for="f-description">Kısa açıklama</label>
        <textarea id="f-description" name="description" rows="3" maxlength="2000"><?= e($v('description')) ?></textarea>
        <span class="f-hint">Albüm sayfasının başında görünür. İsteğe bağlı.</span>
      </div>
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_published" value="1"<?= $published ? ' checked' : '' ?>><span>Sitede yayında</span></label><span class="f-hint">Fotoğrafı olmayan albüm sitede görünmez.</span></div>
      <?= $input('slug', 'Sayfa adresi', 'text', ['span' => true, 'hint' => $isNew ? 'Boş bırakırsan addan üretilir.' : 'Değiştirirsen eski link çalışmaz; paylaşılmışsa dokunma.', 'attrs' => ['pattern' => '[a-z0-9-]*', 'autocapitalize' => 'off']]) ?>
    </div>
  </section>
  </fieldset>
<?php if (!$ro): ?>
  <div class="save-bar">
<?php if (!$isNew): ?>
    <button type="submit" class="btn btn-danger btn-sm" form="delete-form" data-confirm="Albüm ve içindeki <?= (int) $g['photo_count'] ?> fotoğraf silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 14) ?>Albümü sil</button>
<?php endif ?>
    <div class="tb-spacer"></div>
    <a class="btn btn-ghost" href="<?= e(url('admin.gallery')) ?>">Vazgeç</a>
    <button type="submit" class="btn btn-primary" data-busy="Kaydediliyor…"><?= icon('check') ?><?= $isNew ? 'Albümü Oluştur' : 'Kaydet' ?></button>
  </div>
<?php endif ?>
</form>

<?php if (!$isNew): ?>
<section class="panel" id="fotograflar">
  <div class="panel-head"><h2>Fotoğraflar</h2><span class="ph-sub"><?= count($photos) ?> fotoğraf</span></div>
  <div class="panel-body">
<?php if (!$ro): ?>
    <form method="post" enctype="multipart/form-data" action="<?= e(url('admin.gallery.upload', ['id' => $g['id']])) ?>" class="up-form" data-photo-upload>
      <?= csrf_field() ?>
      <label class="up-dropzone" data-dropzone>
        <?= icon('upload') ?>
        <span class="up-dz-text">Fotoğrafları seç veya buraya sürükle</span>
        <small>JPG, PNG veya WebP · tanesi en fazla 15 MB · otomatik küçültülür, konum bilgisi silinir</small>
        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="up-input" data-photo-input>
      </label>
      <div class="up-progress" data-up-progress hidden role="status" aria-live="polite"></div>
      <div class="up-submit" data-up-submit>
        <button type="submit" class="btn btn-primary" data-busy="Yükleniyor…"><?= icon('upload') ?>Seçilenleri Yükle</button>
        <span class="f-hint">Tek seferde toplam en fazla <?= e($postMax) ?> gönderilebilir; fazlası için birkaç kez yükle.</span>
      </div>
    </form>
<?php endif ?>

<?php if ($photos): ?>
    <ol class="ph-list">
<?php foreach ($photos as $i => $p): $isCover = (int) $p['id'] === (int) $cover; ?>
      <li class="ph-item" id="foto-<?= (int) $p['id'] ?>">
        <form method="post" action="<?= e(url('admin.gallery.photo', ['id' => $p['id']])) ?>">
          <?= csrf_field() ?>
          <div class="ph-thumb">
            <img src="<?= e(img($p['path'], 400)) ?>" alt="<?= e($p['caption'] ?? '') ?>" loading="lazy">
            <span class="ph-no mono"><?= $i + 1 ?></span>
<?php if ($isCover): ?>
            <span class="chip orange ph-cover">Kapak</span>
<?php endif ?>
          </div>
          <fieldset class="ph-fields"<?= $ro ? ' disabled' : '' ?>>
            <div class="field"><label for="cap-<?= (int) $p['id'] ?>">Açıklama</label><input id="cap-<?= (int) $p['id'] ?>" name="caption" type="text" maxlength="255" value="<?= e($p['caption'] ?? '') ?>"></div>
            <div class="field"><label for="cr-<?= (int) $p['id'] ?>">Çeken</label><input id="cr-<?= (int) $p['id'] ?>" name="credit" type="text" maxlength="120" value="<?= e($p['credit'] ?? '') ?>" autocomplete="off"></div>
          </fieldset>
<?php if (!$ro): ?>
          <div class="ph-actions">
            <button type="submit" name="do" value="save" class="btn btn-ghost btn-sm"><?= icon('check', 14) ?>Kaydet</button>
<?php if (!$isCover): ?>
            <button type="submit" name="do" value="cover" class="btn btn-ghost btn-sm">Kapak yap</button>
<?php endif ?>
            <button type="submit" name="do" value="up" class="btn-icon" aria-label="Yukarı taşı"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrowup', 16) ?></button>
            <button type="submit" name="do" value="down" class="btn-icon" aria-label="Aşağı taşı"<?= $i === count($photos) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button>
            <button type="submit" name="do" value="delete" class="btn-icon del" aria-label="Fotoğrafı sil" data-confirm="Bu fotoğraf silinsin mi?"><?= icon('trash', 16) ?></button>
          </div>
<?php endif ?>
        </form>
      </li>
<?php endforeach ?>
    </ol>
<?php else: ?>
    <div class="empty"><?= icon('gallery', 40) ?><h4>Henüz fotoğraf yok</h4><p>Yukarıdan fotoğraf yükle. Fotoğrafı olmayan albüm sitede görünmez.</p></div>
<?php endif ?>
  </div>
</section>
<?php endif ?>

<?php if (!$isNew && !$ro): ?>
<form id="delete-form" method="post" action="<?= e(url('admin.gallery.delete', ['id' => $g['id']])) ?>" hidden><?= csrf_field() ?></form>
<?php endif ?>
