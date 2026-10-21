<?php
/**
 * Duyuru / haber ekle / düzenle.
 * @var array|null $n
 * @var array      $activities
 * @var string|null $siteUrl yayındaysa sitedeki adresi
 * @var array      $old
 * @var array      $errors
 */
use App\Core\Auth;
use App\Models\News;

['v' => $v, 'err' => $err, 'inv' => $inv, 'input' => $input, 'textarea' => $textarea, 'select' => $select] = (require dirname(__DIR__) . '/_form.php')($old, $n, $errors);
$isNew = $n === null;
$ro = !Auth::canEdit();
$posted = array_key_exists('title', $old);
$featured = $posted ? !empty($old['is_featured']) : (bool) ($n['is_featured'] ?? 0);
$inSlider = $posted ? !empty($old['in_slider']) : (bool) ($n['in_slider'] ?? 0);
$actChoices = ['' => 'Faaliyete bağlı değil'];
foreach ($activities as $a) {
    $actChoices[$a['id']] = tr_date($a['starts_on']) . ' · ' . $a['title'];
}
?>
<div class="page-head">
  <a class="back-link" href="<?= e(url('admin.news')) ?>"><?= icon('back', 16) ?>Duyuru &amp; Haber</a>
<?php if (!empty($siteUrl)): ?>
  <div class="page-head-actions">
    <a class="btn btn-ghost btn-sm" href="<?= e($siteUrl) ?>" target="_blank" rel="noopener"><?= icon('ext', 14) ?>Sitede gör</a>
  </div>
<?php endif ?>
</div>

<form method="post" enctype="multipart/form-data" class="act-form" data-news-form data-dirty-guard novalidate action="<?= e($isNew ? url('admin.news.store') : url('admin.news.update', ['id' => $n['id']])) ?>">
  <?= csrf_field() ?>
  <fieldset<?= $ro ? ' disabled' : '' ?>>
  <section class="panel">
    <div class="panel-head"><h2>İçerik</h2></div>
    <div class="panel-body form-grid">
      <div class="field col-span"><label for="content-mode">Ne yayınlamak istiyorsun?</label><select id="content-mode" name="content_mode" data-content-mode>
<option value="article"<?= $v('content_mode', $v('body') !== '' ? 'article' : ($v('activity_id') !== '' ? 'activity' : ($v('link_url') !== '' ? 'link' : 'article'))) === 'article' ? ' selected' : '' ?>>Yazı yayınla</option>
<option value="activity"<?= $v('content_mode', $v('body') !== '' ? 'article' : ($v('activity_id') !== '' ? 'activity' : 'link')) === 'activity' ? ' selected' : '' ?>>Faaliyete yönlendir</option>
<option value="link"<?= $v('content_mode', $v('body') !== '' ? 'article' : ($v('activity_id') !== '' ? 'activity' : 'link')) === 'link' && $v('link_url') !== '' ? ' selected' : '' ?>>Bağlantı paylaş</option>
</select><span class="f-hint">Seçimine göre gerekli alanlar gösterilir.</span></div>
      <?= $input('title', 'Başlık', 'text', ['req' => true, 'span' => true, 'attrs' => ['maxlength' => 200]]) ?>
      <?= $select('category', 'Tür', News::CATEGORIES, ['default' => 'haber']) ?>
      <details class="form-extra col-span"><summary>Gelişmiş: okuma süresi</summary>
      <?= $input('read_minutes', 'Okuma süresi (dk)', 'number', ['hint' => 'Boş bırakırsan metinden hesaplanır.', 'attrs' => ['inputmode' => 'numeric', 'min' => 1, 'max' => 99]]) ?>
      </details>
      <?= $textarea('excerpt', 'Kısa özet', ['rows' => 2, 'attrs' => ['maxlength' => 400], 'hint' => 'Anasayfadaki öne çıkan kartta ve haber sayfasının başında görünür.']) ?>
      <?= $textarea('body', 'Metin', ['rows' => 10, 'hint' => 'Paragrafları boş satırla ayır. Metin yazarsan haberin kendi sayfası olur (/duyurular/…).']) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Bağlantı</h2><span class="ph-sub">Metin yoksa haber buraya götürür</span></div>
    <div class="panel-body form-grid">
      <?= $select('activity_id', 'İlgili faaliyet', $actChoices, ['span' => true, 'hint' => 'Metin yoksa haber faaliyet sayfasına gider; metin varsa haber sayfasında "Faaliyete Git" çıkar.']) ?>
      <?= $input('link_url', 'Bağlantı', 'text', ['span' => true, 'hint' => 'Metin ve faaliyet yoksa kullanılır. Ör. /galeri ya da https://tdf.gov.tr/…', 'attrs' => ['maxlength' => 255, 'autocapitalize' => 'off', 'inputmode' => 'url']]) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Kapak görseli</h2></div>
    <div class="panel-body">
      <div class="field">
        <div class="cover-ctrl">
          <div class="cover-prev" data-cover-prev<?= !$isNew && $n['cover_image'] ? ' style="background-image:url(\'' . e(img($n['cover_image'], 900)) . '\')"' : '' ?>></div>
          <div class="cover-btns">
            <label class="btn btn-ghost btn-sm file-btn"><?= icon('upload', 14) ?>Görsel seç<input type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-cover-input data-crop-ratio="16/10" data-crop-label="Kart ve slayt kapağı (16:10)"<?= $inv('cover') ?>></label><input type="hidden" name="cover_crop" value="" data-crop-value>
<?php if (!$isNew && $n['cover_image']): ?>
            <label class="check"><input type="checkbox" name="remove_cover" value="1"><span>Kapağı kaldır</span></label>
<?php endif ?>
          </div>
        </div>
        <span class="f-hint">JPG, PNG veya WebP · en fazla 15 MB. Seçince kırpma penceresi açılır; kapakta görünecek alanı ayarlarsın.</span><?= $err('cover') ?>
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Yayın</h2></div>
    <div class="panel-body form-grid">
      <?= $select('status', 'Durum', ['draft' => 'Taslak (sitede görünmez)', 'published' => 'Yayında'], ['default' => 'draft']) ?>
      <?= $input('published_on', 'Yayın tarihi', 'date', ['hint' => 'İleri bir tarih seçersen o gün yayına girer. Boşsa bugün.']) ?>
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_featured" value="1"<?= $featured ? ' checked' : '' ?>><span>Öne çıkan (Anasayfada büyük kart)</span></label></div>
      <div class="field col-span"><label class="check"><input type="checkbox" name="in_slider" value="1"<?= $inSlider ? ' checked' : '' ?><?= $inv('in_slider') ?>><span>Anasayfa slaytında göster</span></label><span class="f-hint">Kapak görseli, başlık ve özetle slayt olur; "Devamını Oku" butonu kendiliğinden eklenir. Kapak görseli gerekli. Sırayı "Anasayfa Slaytları" sayfasında görürsün.</span><?= $err('in_slider') ?></div>
      <?= $input('slider_until', 'Slaytta gösterim bitişi', 'date', ['hint' => 'Tarihi geçebilecek duyuruda doldur; o günden sonra slayttan kalkar, haber sitede kalır. Boşsa sen kaldırana kadar slaytta.']) ?>
      <details class="form-extra col-span"><summary>Gelişmiş: sayfa adresi</summary>
      <?= $input('slug', 'Sayfa adresi', 'text', ['span' => true, 'hint' => $isNew ? 'Boş bırakırsan başlıktan üretilir.' : 'Eski adres yeni adrese otomatik yönlenir.', 'attrs' => ['pattern' => '[a-z0-9-]*', 'autocapitalize' => 'off']]) ?>
      </details>
    </div>
  </section>
  </fieldset>

<?php if (!$ro): ?>
  <div class="save-bar">
<?php if (!$isNew): ?>
    <button type="submit" class="btn btn-danger btn-sm" form="delete-form" data-confirm="Bu kayıt silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 14) ?>Sil</button>
<?php endif ?>
    <div class="tb-spacer"></div>
    <a class="btn btn-ghost" href="<?= e(url('admin.news')) ?>">Vazgeç</a>
    <button type="submit" class="btn btn-primary" data-busy="Kaydediliyor…"><?= icon('check') ?>Kaydet</button>
  </div>
<?php endif ?>
</form>
<?php if (!$isNew && !$ro): ?>
<form id="delete-form" method="post" action="<?= e(url('admin.news.delete', ['id' => $n['id']])) ?>" hidden><?= csrf_field() ?></form>
<?php endif ?>
