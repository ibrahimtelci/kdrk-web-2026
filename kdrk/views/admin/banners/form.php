<?php
/**
 * Slayt ekle / düzenle.
 * @var array|null $b
 * @var array      $old
 * @var array      $errors
 */
use App\Core\Auth;

['err' => $err, 'inv' => $inv, 'input' => $input, 'textarea' => $textarea] = (require dirname(__DIR__) . '/_form.php')($old, $b, $errors);
$isNew = $b === null;
$ro = !Auth::canEdit();
$active = array_key_exists('title', $old) ? !empty($old['is_active']) : (bool) ($b['is_active'] ?? 1);
$tokens = 'Rakamlar için {yil}, {zirve}, {yuruyus}, {aramaKurtarma}, {kurulus} yaz; Genel Ayarlar\'daki kulüp rakamlarıyla dolar.';
?>
<div class="page-head">
  <a class="back-link" href="<?= e(url('admin.banners')) ?>"><?= icon('back', 16) ?>Anasayfa Slaytları</a>
</div>

<form method="post" enctype="multipart/form-data" class="act-form" novalidate action="<?= e($isNew ? url('admin.banners.store') : url('admin.banners.update', ['id' => $b['id']])) ?>">
  <?= csrf_field() ?>
  <fieldset<?= $ro ? ' disabled' : '' ?>>
  <section class="panel">
    <div class="panel-head"><h2>Metin</h2></div>
    <div class="panel-body form-grid">
      <?= $input('eyebrow', 'Üst etiket', 'text', ['span' => true, 'hint' => 'Kısa, büyük harfle görünür. Ör. EST. 1998 · RİZE', 'attrs' => ['maxlength' => 80]]) ?>
      <?= $input('title', 'Başlık', 'text', ['req' => true, 'span' => true, 'attrs' => ['maxlength' => 160]]) ?>
      <?= $textarea('text', 'Açıklama', ['rows' => 3, 'attrs' => ['maxlength' => 400], 'hint' => $tokens]) ?>
      <?= $input('stamp_1', 'Damga 1', 'text', ['hint' => 'Sağ altta mono yazı. Ör. {zirve}+ ZİRVE', 'attrs' => ['maxlength' => 60]]) ?>
      <?= $input('stamp_2', 'Damga 2', 'text', ['hint' => 'Ör. 41°01\'N · 40°31\'E', 'attrs' => ['maxlength' => 60]]) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Butonlar</h2><span class="ph-sub">İsteğe bağlı</span></div>
    <div class="panel-body form-grid">
      <?= $input('cta_label', 'Ana buton yazısı', 'text', ['attrs' => ['maxlength' => 60]]) ?>
      <?= $input('cta_url', 'Ana buton bağlantısı', 'text', ['hint' => 'Ör. /faaliyetler', 'attrs' => ['maxlength' => 255, 'autocapitalize' => 'off', 'inputmode' => 'url']]) ?>
      <?= $input('cta2_label', 'İkinci buton yazısı', 'text', ['attrs' => ['maxlength' => 60]]) ?>
      <?= $input('cta2_url', 'İkinci buton bağlantısı', 'text', ['hint' => 'Ör. /bilgi-bankasi/kulup-uyeligi', 'attrs' => ['maxlength' => 255, 'autocapitalize' => 'off', 'inputmode' => 'url']]) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Görsel</h2></div>
    <div class="panel-body">
      <div class="field">
        <span class="field-label">Arka plan görseli <span class="req" aria-hidden="true">*</span></span>
        <div class="cover-ctrl">
          <div class="cover-prev" data-cover-prev<?= !$isNew ? ' style="background-image:url(\'' . e(img($b['image'], 900)) . '\')"' : '' ?>></div>
          <div class="cover-btns">
            <label class="btn btn-ghost btn-sm file-btn"><?= icon('upload', 14) ?><?= $isNew ? 'Görsel seç' : 'Görseli değiştir' ?><input type="file" name="image" accept="image/jpeg,image/png,image/webp" data-cover-input<?= $inv('image') ?>></label>
          </div>
        </div>
        <span class="f-hint">Yatay fotoğraf, en az 1600 px genişlik. Üstüne koyu katman eklenir; metin her zaman okunur.</span><?= $err('image') ?>
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Yayın</h2></div>
    <div class="panel-body form-grid">
      <?= $input('show_from', 'Gösterim başlangıcı', 'date', ['hint' => 'Boşsa hemen.']) ?>
      <?= $input('show_until', 'Gösterim bitişi', 'date', ['hint' => 'Duyuru slaytında doldur; tarih geçince kendiliğinden kalkar. Bitişi olan slayt ilk sıraya konmaz.']) ?>
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_active" value="1"<?= $active ? ' checked' : '' ?>><span>Etkin</span></label></div>
    </div>
  </section>
  </fieldset>

<?php if (!$ro): ?>
  <div class="save-bar">
<?php if (!$isNew): ?>
    <button type="submit" class="btn btn-danger btn-sm" form="delete-form" data-confirm="Bu slayt silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 14) ?>Sil</button>
<?php endif ?>
    <div class="tb-spacer"></div>
    <a class="btn btn-ghost" href="<?= e(url('admin.banners')) ?>">Vazgeç</a>
    <button type="submit" class="btn btn-primary" data-busy="Kaydediliyor…"><?= icon('check') ?><?= $isNew ? 'Slaytı Ekle' : 'Kaydet' ?></button>
  </div>
<?php endif ?>
</form>
<?php if (!$isNew && !$ro): ?>
<form id="delete-form" method="post" action="<?= e(url('admin.banners.delete', ['id' => $b['id']])) ?>" hidden><?= csrf_field() ?></form>
<?php endif ?>
