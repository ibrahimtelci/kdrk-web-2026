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

<link rel="stylesheet" href="<?= e(asset('admin/album-workspace.css')) ?>">
<div class="album-workspace" data-album-workspace>
<?php if (!$isNew): ?>
  <nav class="steps-nav album-tabs" aria-label="Albüm bölümleri" data-album-tabs hidden>
    <button type="button" class="steps-tab" id="album-tab-photos" data-album-tab="fotograflar" aria-controls="fotograflar">Fotoğraflar <span class="chip gray"><?= count($photos) ?></span></button>
    <button type="button" class="steps-tab" id="album-tab-info" data-album-tab="album-bilgileri" aria-controls="album-bilgileri">Albüm Bilgileri</button>
  </nav>
<?php endif ?>

<?php if (!$isNew): ?>
<section class="panel" id="fotograflar" data-album-pane>
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
    <ol class="album-grid">
<?php foreach ($photos as $i => $p): $isCover = (int) $p['id'] === (int) $cover; ?>
      <li class="album-tile" id="foto-<?= (int) $p['id'] ?>">
        <details data-photo-detail>
          <summary class="album-thumb" aria-label="<?= e(($i + 1) . '. fotoğrafın ayrıntılarını aç') ?>">
            <img src="<?= e(img($p['path'], 400)) ?>" alt="<?= e($p['caption'] ?: ($g['title'] . ' — Fotoğraf ' . ($i + 1))) ?>" loading="lazy" decoding="async">
            <span class="album-number"><?= $i + 1 ?></span>
<?php if ($isCover): ?>
            <span class="chip orange album-cover">Kapak</span>
<?php endif ?>
            <span class="album-open">Ayrıntılar</span>
          </summary>
          <div class="album-photo-detail" data-photo-body>
            <img class="album-preview" src="<?= e(img($p['path'], 900)) ?>" alt="<?= e($p['caption'] ?: ($g['title'] . ' — Fotoğraf ' . ($i + 1))) ?>" loading="lazy">
            <form method="post" action="<?= e(url('admin.gallery.photo', ['id' => $p['id']])) ?>" data-photo-edit>
              <?= csrf_field() ?>
              <fieldset class="album-photo-fields"<?= $ro ? ' disabled' : '' ?>>
                <div class="field"><label for="cap-<?= (int) $p['id'] ?>">Fotoğraf açıklaması</label><textarea id="cap-<?= (int) $p['id'] ?>" name="caption" rows="3" maxlength="255"><?= e($p['caption'] ?? '') ?></textarea></div>
                <div class="field"><label for="cr-<?= (int) $p['id'] ?>">Fotoğrafı çeken</label><input id="cr-<?= (int) $p['id'] ?>" name="credit" type="text" maxlength="120" value="<?= e($p['credit'] ?? '') ?>" autocomplete="off"></div>
              </fieldset>
<?php if (!$ro): ?>
              <div class="album-detail-actions">
                <button type="submit" name="do" value="save" class="btn btn-primary"><?= icon('check', 14) ?>Kaydet</button>
<?php if (!$isCover): ?>
                <button type="submit" name="do" value="cover" class="btn btn-ghost">Kapak yap</button>
<?php endif ?>
                <button type="submit" name="do" value="up" class="btn-icon" aria-label="Önceki sıraya taşı"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrowup', 16) ?></button>
                <button type="submit" name="do" value="down" class="btn-icon" aria-label="Sonraki sıraya taşı"<?= $i === count($photos) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button>
                <button type="submit" name="do" value="delete" class="btn btn-danger" data-confirm="Bu fotoğraf silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 16) ?>Sil</button>
              </div>
<?php endif ?>
            </form>
          </div>
        </details>
        <div class="album-tile-footer">
          <span class="album-caption"><?= e($p['caption'] ?: ('Fotoğraf ' . ($i + 1))) ?></span>
<?php if (!$ro): ?>
          <form method="post" action="<?= e(url('admin.gallery.photo', ['id' => $p['id']])) ?>">
            <?= csrf_field() ?>
            <button type="submit" name="do" value="delete" class="btn-icon del" aria-label="<?= e(($i + 1) . '. fotoğrafı sil') ?>" data-confirm="Bu fotoğraf silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 16) ?></button>
          </form>
<?php endif ?>
        </div>
      </li>
<?php endforeach ?>
    </ol>
<?php else: ?>
    <div class="empty"><?= icon('gallery', 40) ?><h4>Henüz fotoğraf yok</h4><p>Yukarıdan fotoğraf yükle. Fotoğrafı olmayan albüm sitede görünmez.</p></div>
<?php endif ?>
  </div>
</section>
<?php endif ?>

<section id="album-bilgileri" data-album-pane>
<form method="post" class="act-form" data-gallery-form data-dirty-guard novalidate action="<?= e($isNew ? url('admin.gallery.store') : url('admin.gallery.update', ['id' => $g['id']])) ?>">
  <?= csrf_field() ?>
  <fieldset<?= $ro ? ' disabled' : '' ?>>
  <section class="panel">
    <div class="panel-head"><h2>Albüm bilgileri</h2></div>
    <div class="panel-body form-grid">
      <?= $input('title', 'Albüm adı', 'text', ['req' => true, 'span' => true, 'attrs' => ['maxlength' => 160]]) ?>
      <div class="field col-span">
        <label for="f-activity_id">Faaliyet</label>
        <select id="f-activity_id" name="activity_id"<?= $inv('activity_id') ?>>
          <option value="">Bağımsız albüm</option>
<?php foreach ($activities as $a): ?>
          <option value="<?= (int) $a['id'] ?>"<?= $v('activity_id') === (string) $a['id'] ? ' selected' : '' ?>><?= e(tr_date($a['starts_on']) . ' · ' . $a['title']) ?></option>
<?php endforeach ?>
        </select>
        <span class="f-hint">Bağlarsan tarih, kategori ve yer faaliyetten gelir; fotoğraflar faaliyet sayfasında da görünür.</span><?= $err('activity_id') ?>
      </div>
      <p class="f-hint col-span" data-inherited-note>Faaliyet seçildiğinde ad, tarih, kategori ve yer otomatik önerilir.</p>
      <details class="form-extra col-span" data-gallery-extra><summary>Tarih, kategori ve yer — farklı bilgi kullan</summary><div class="form-grid">
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
      </div></details>
      <?= $input('tags', 'Etiketler', 'text', ['span' => true, 'hint' => 'Virgülle ayır. Faaliyetten açılan albümde faaliyet adı, yer, kategori ve yıl hazır gelir. Panelde ve sitede aramada kullanılır.', 'attrs' => ['maxlength' => 500]]) ?>
      <div class="field col-span">
        <label for="f-description">Kısa açıklama</label>
        <textarea id="f-description" name="description" rows="3" maxlength="2000"><?= e($v('description')) ?></textarea>
        <span class="f-hint">Albüm sayfasının başında görünür. İsteğe bağlı.</span>
      </div>
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_published" value="1"<?= $published ? ' checked' : '' ?>><span>Sitede yayında</span></label><span class="f-hint">Fotoğrafı olmayan albüm sitede görünmez.</span></div>
      <details class="form-extra col-span"><summary>Gelişmiş: sayfa adresi</summary>
      <?= $input('slug', 'Sayfa adresi', 'text', ['span' => true, 'hint' => $isNew ? 'Boş bırakırsan addan üretilir.' : 'Eski adres yeni adrese otomatik yönlenir.', 'attrs' => ['pattern' => '[a-z0-9-]*', 'autocapitalize' => 'off']]) ?>
      </details>
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

</section>
</div>
<script src="<?= e(asset('admin/album-workspace.js')) ?>" defer></script>

<?php if (!$isNew && !$ro): ?>
<form id="delete-form" method="post" action="<?= e(url('admin.gallery.delete', ['id' => $g['id']])) ?>" hidden><?= csrf_field() ?></form>
<?php endif ?>
