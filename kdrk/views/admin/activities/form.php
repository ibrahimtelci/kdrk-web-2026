<?php
/**
 * Faaliyet ekle / düzenle.
 * @var array|null $a       düzenlenen faaliyet (Activity::decorate) ya da null
 * @var array      $old     hata sonrası geri dönen form değerleri
 * @var array      $errors  alan => mesaj
 */
use App\Controllers\Admin\ApplicationController;
use App\Core\Auth;
use App\Models\Activity;

$S = require dirname(__DIR__) . '/_status.php';
$isNew = $a === null;
$ro = !Auth::canEdit();
$v = static function (string $k, mixed $default = '') use ($old, $a): string {
    if (array_key_exists($k, $old)) {
        return (string) $old[$k];
    }
    if ($a !== null && array_key_exists($k, $a) && $a[$k] !== null && !is_array($a[$k])) {
        return (string) $a[$k];
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
        . '<input id="f-' . $k . '" name="' . $k . '" type="' . $type . '" value="' . e($v($k, $opt['default'] ?? '')) . '"' . (!empty($opt['req']) ? ' required' : '') . $attrs . $inv($k) . '>'
        . (isset($opt['hint']) ? '<span class="f-hint">' . e($opt['hint']) . '</span>' : '') . $err($k) . '</div>';
};
$textarea = static function (string $k, string $label, array $opt = []) use ($v, $err, $inv): string {
    return '<div class="field col-span"><label for="f-' . $k . '">' . e($label) . (!empty($opt['req']) ? ' <span class="req" aria-hidden="true">*</span>' : '') . '</label>'
        . '<textarea id="f-' . $k . '" name="' . $k . '" rows="' . ($opt['rows'] ?? 4) . '"' . (isset($opt['max']) ? ' maxlength="' . $opt['max'] . '"' : '') . (!empty($opt['req']) ? ' required' : '') . $inv($k) . '>' . e($v($k, $opt['default'] ?? '')) . '</textarea>'
        . (isset($opt['hint']) ? '<span class="f-hint">' . e($opt['hint']) . '</span>' : '') . $err($k) . '</div>';
};
$program = array_key_exists('program', $old) ? (json_decode((string) $old['program'], true) ?: []) : ($a['program'] ?? []);
$equipment = array_key_exists('equipment', $old) ? (string) $old['equipment'] : implode("\n", $a['equipment'] ?? []);
$published = array_key_exists('title', $old) ? !empty($old['is_published']) : ($a['is_published'] ?? 1);
$training = array_key_exists('title', $old) ? !empty($old['is_training']) : (bool) ($a['is_training'] ?? false);
?>
<div class="page-head">
  <a class="back-link" href="<?= e(url('admin.activities')) ?>"><?= icon('back', 16) ?>Faaliyetler</a>
<?php if (!$isNew): ?>
  <div class="page-head-actions">
    <?= '<span class="chip ' . $S['activity'][$a['status']][1] . ' dot">' . e($S['activity'][$a['status']][0]) . '</span>' ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>"><?= icon('users', 14) ?>Başvurular (<?= (int) $a['taken'] ?>)</a>
    <a class="btn btn-ghost btn-sm" href="<?= e($a['url']) ?>" target="_blank" rel="noopener"><?= icon('ext', 14) ?>Sitede gör</a>
  </div>
<?php endif ?>
</div>

<form method="post" enctype="multipart/form-data" class="act-form" data-act-form novalidate
      action="<?= e($isNew ? url('admin.activities.store') : url('admin.activities.update', ['id' => $a['id']])) ?>">
  <?= csrf_field() ?>
  <fieldset<?= $ro ? ' disabled' : '' ?>>

  <section class="panel">
    <div class="panel-head"><h2>Temel bilgiler</h2></div>
    <div class="panel-body form-grid">
      <?= $input('title', 'Faaliyet adı', 'text', ['req' => true, 'span' => true, 'attrs' => ['maxlength' => 160]]) ?>
      <div class="field">
        <label for="f-category">Kategori <span class="req" aria-hidden="true">*</span></label>
        <select id="f-category" name="category" required<?= $inv('category') ?>>
          <option value="">Seç…</option>
<?php foreach (Activity::CATEGORIES as $k => $label): ?>
          <option value="<?= e($k) ?>"<?= $v('category') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
        </select><?= $err('category') ?>
      </div>
      <?= $input('location', 'Yer', 'text', ['req' => true, 'hint' => 'Ör. Rize / Çamlıhemşin · 3.937 m', 'attrs' => ['maxlength' => 160]]) ?>
      <?= $textarea('summary', 'Kısa açıklama (kartta görünür)', ['req' => true, 'rows' => 2, 'max' => 300, 'hint' => 'En fazla 300 karakter. Anasayfa ve listedeki kartta görünür.']) ?>
      <?= $textarea('body', 'Faaliyet hakkında', ['rows' => 7, 'hint' => 'Paragrafları boş satırla ayır. Detay sayfasında görünür.']) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Tarih ve seviye</h2></div>
    <div class="panel-body form-grid">
      <?= $input('starts_on', 'Başlangıç tarihi', 'date', ['req' => true]) ?>
      <?= $input('ends_on', 'Bitiş tarihi', 'date', ['hint' => 'Tek günlükse boş bırak.']) ?>
      <div class="field">
        <label for="f-difficulty">Zorluk</label>
        <select id="f-difficulty" name="difficulty"<?= $inv('difficulty') ?>>
          <option value="">Seviye yok (eğitim)</option>
<?php foreach ([1 => 'Kolay · 1/4', 2 => 'Orta · 2/4', 3 => 'Zor · 3/4', 4 => 'Çok zor · 4/4'] as $k => $label): ?>
          <option value="<?= $k ?>"<?= $v('difficulty') === (string) $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
        </select><?= $err('difficulty') ?>
      </div>
      <div class="field"><span class="field-label">Tür</span><label class="check"><input type="checkbox" name="is_training" value="1"<?= $training ? ' checked' : '' ?>><span>Bu bir eğitim</span></label></div>
      <?= $input('duration_text', 'Süre', 'text', ['hint' => 'Ör. 3 gece · 4 gün', 'attrs' => ['maxlength' => 60]]) ?>
      <?= $input('summit_m', 'Zirve (m)', 'number', ['attrs' => ['inputmode' => 'numeric', 'min' => 1, 'max' => 9000]]) ?>
      <?= $input('distance_km', 'Mesafe (km)', 'text', ['attrs' => ['inputmode' => 'decimal']]) ?>
      <?= $input('elevation_gain_m', 'İrtifa farkı (m)', 'number', ['attrs' => ['inputmode' => 'numeric', 'min' => 1, 'max' => 9000]]) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Başvuru ve ücret</h2></div>
    <div class="panel-body form-grid">
      <?= $input('quota', 'Kontenjan', 'number', ['default' => 15, 'hint' => '0 = sınır yok. Dolunca başvurular yedek listeye alınır.', 'attrs' => ['inputmode' => 'numeric', 'min' => 0]]) ?>
      <?= $input('fee', 'Ücret (₺)', 'text', ['hint' => 'Boş = ücret bilgisi gösterilmez, 0 = Ücretsiz', 'attrs' => ['inputmode' => 'decimal']]) ?>
      <?= $input('fee_note', 'Ücret notu', 'text', ['span' => true, 'hint' => 'Ör. Ulaşım ve kamp dahil; bireysel ekipman katılımcıya ait.']) ?>
      <div class="field col-span">
        <label for="f-manual_status">Başvuru durumu</label>
        <select id="f-manual_status" name="manual_status">
          <option value="">Otomatik (kontenjan ve tarihe göre)</option>
<?php foreach (['closed' => 'Başvuruları kapat', 'postponed' => 'Ertelendi', 'cancelled' => 'İptal edildi'] as $k => $label): ?>
          <option value="<?= $k ?>"<?= $v('manual_status') === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
        </select>
        <span class="f-hint">"Tamamlandı" elle seçilmez; bitiş tarihi geçince kendiliğinden olur.</span>
      </div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Bilmen gerekenler</h2><span class="ph-sub">Boş bırakılan satır sitede gösterilmez</span></div>
    <div class="panel-body form-grid">
      <?= $input('meeting_text', 'Buluşma', 'text', ['hint' => 'Ör. Pazar otogarı · 06:30']) ?>
      <?= $input('return_text', 'Dönüş', 'text') ?>
      <?= $input('included_text', 'Ücrete dahil', 'text') ?>
      <?= $input('personal_text', 'Katılımcıya ait', 'text') ?>
      <?= $textarea('requirement_text', 'Katılım şartı', ['rows' => 3]) ?>
      <?= $input('weather_text', 'Hava durumu notu', 'text', ['span' => true]) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Program</h2><span class="ph-sub">Gün gün akış</span></div>
    <div class="panel-body">
      <input type="hidden" name="program" data-program-json value="<?= e(json_encode($program, JSON_UNESCAPED_UNICODE)) ?>">
      <div class="program-editor" data-program-editor></div>
<?php if (!$ro): ?>
      <button type="button" class="btn btn-ghost btn-sm" data-add-day><?= icon('plus', 14) ?>Gün ekle</button>
<?php endif ?>
      <noscript><p class="f-hint">Program düzenleyici için JavaScript gerekli.</p></noscript>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Malzemeler</h2></div>
    <div class="panel-body">
      <div class="field"><label for="f-equipment">Gerekli malzemeler</label><textarea id="f-equipment" name="equipment" rows="6"><?= e($equipment) ?></textarea><span class="f-hint">Her satıra bir malzeme yaz.</span></div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Kapak görseli ve sorumlu</h2></div>
    <div class="panel-body form-grid">
      <div class="field col-span">
        <span class="field-label">Kapak görseli</span>
        <div class="cover-ctrl">
          <div class="cover-prev" data-cover-prev<?= !$isNew && $a['cover_image'] ? ' style="background-image:url(\'' . e(img($a['cover_image'], 900)) . '\')"' : '' ?>></div>
          <div class="cover-btns">
            <label class="btn btn-ghost btn-sm file-btn"><?= icon('upload', 14) ?>Görsel seç<input type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-cover-input<?= $inv('cover') ?>></label>
<?php if (!$isNew && $a['cover_image']): ?>
            <label class="check"><input type="checkbox" name="remove_cover" value="1"><span>Kapağı kaldır</span></label>
<?php endif ?>
          </div>
        </div>
        <span class="f-hint">JPG, PNG veya WebP · en fazla 15 MB. Otomatik küçültülür; yatay (16:10) fotoğraf en iyi sonucu verir.</span><?= $err('cover') ?>
      </div>
      <?= $input('leader_name', 'Faaliyet sorumlusu', 'text', ['attrs' => ['maxlength' => 120]]) ?>
      <?= $input('leader_title', 'Sorumlunun unvanı', 'text', ['hint' => 'Ör. TDF sertifikalı yaz dağcılık eğitmeni']) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Yayın ve sonuç</h2></div>
    <div class="panel-body form-grid">
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_published" value="1"<?= $published ? ' checked' : '' ?>><span>Sitede yayında</span></label><span class="f-hint">Kapatırsan faaliyet sitede görünmez; başvurular panelde kalır.</span></div>
      <?= $input('slug', 'Sayfa adresi', 'text', ['span' => true, 'hint' => $isNew ? 'Boş bırakırsan addan üretilir.' : 'Değiştirirsen eski link çalışmaz; paylaşılmışsa dokunma.', 'attrs' => ['pattern' => '[a-z0-9-]*', 'autocapitalize' => 'off']]) ?>
      <?= $textarea('report_summary', 'Faaliyet raporu (tamamlandıktan sonra)', ['rows' => 3, 'hint' => 'Faaliyet bitince detay sayfasında ve kartta görünür.']) ?>
      <?= $input('report_participants', 'Katılan kişi sayısı', 'number', ['attrs' => ['inputmode' => 'numeric', 'min' => 0]]) ?>
    </div>
  </section>

  </fieldset>

<?php if (!$ro): ?>
  <div class="save-bar">
<?php if (!$isNew): ?>
    <button type="submit" class="btn btn-danger btn-sm" form="delete-form" data-confirm="Bu faaliyet silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 14) ?>Sil</button>
<?php endif ?>
    <div class="tb-spacer"></div>
    <a class="btn btn-ghost" href="<?= e(url('admin.activities')) ?>">Vazgeç</a>
    <button type="submit" class="btn btn-primary" data-busy="Kaydediliyor…"><?= icon('check') ?><?= $isNew ? 'Faaliyeti Ekle' : 'Kaydet' ?></button>
  </div>
<?php endif ?>
</form>
<?php if (!$isNew && !$ro): ?>
<form id="delete-form" method="post" action="<?= e(url('admin.activities.delete', ['id' => $a['id']])) ?>" hidden><?= csrf_field() ?></form>
<?php endif ?>
