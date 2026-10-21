<?php
/**
 * Faaliyet ekle / düzenle — sade alanlar.
 * Yeni kayıt: açılır pencerede 5 adımlı sihirbaz (son adım önizleme). Düzenleme: aynı bölümler sekme olarak.
 * Malzeme/kontrol notu kategoriye göre, ödeme açıklaması genelden gelir (Genel Ayarlar → Faaliyet notları).
 * @var array|null $a       düzenlenen faaliyet (Activity::decorate) ya da null
 * @var int|null   $albumId faaliyete bağlı galeri albümü
 * @var array      $old     hata sonrası geri dönen form değerleri
 * @var array      $errors  alan => mesaj
 */
use App\Core\Auth;
use App\Core\Settings;
use App\Models\Activity;

$S = require dirname(__DIR__) . '/_status.php';
['v' => $v, 'err' => $err, 'inv' => $inv, 'input' => $input, 'textarea' => $textarea, 'select' => $select] = (require dirname(__DIR__) . '/_form.php')($old, $a, $errors);
$isNew = $a === null;
$past = !$isNew && ($a['ends_on'] ?: $a['starts_on']) < date('Y-m-d');
$ro = !Auth::canEdit();
$posted = array_key_exists('title', $old);
$published = $posted ? !empty($old['is_published']) : (bool) ($a['is_published'] ?? 0);
$inSlider = $posted ? !empty($old['in_slider']) : (bool) ($a['in_slider'] ?? false);
$noScore = $posted ? !empty($old['no_difficulty']) : ($a !== null && $a['score'] === null);
$score = (int) ($posted ? ($old['difficulty_score'] ?? 3) : ($a['score'] ?? 3)) ?: 3;
$lat = $v('lat');
$lng = $v('lng');
// Önizleme ve plan adımındaki not için: kategori → malzeme maddeleri, genel ödeme notu
$gear = [];
foreach (array_keys(Activity::CATEGORIES) as $k) {
    $gear[$k] = Activity::gearNote($k);
}
$cats = ['' => 'Seç…'] + Activity::CATEGORIES;
?>
<?php if (!$isNew): ?><?= $this->partial('partials/social-share-button', ['a' => $a]) ?><p class="f-hint">Paylaşım kayıtlı bilgilerle hazırlanır; değişiklik yaptıysan önce kaydet.</p><?php endif ?>
<div class="page-head">
  <a class="back-link" href="<?= e(url('admin.activities')) ?>"><?= icon('back', 16) ?>Faaliyetler</a>
<?php if (!$isNew): ?>
  <div class="page-head-actions">
<?php if (Auth::canEdit()): ?><a class="btn btn-ghost btn-sm" href="<?= e(url('admin.activities.new', ['kopya' => $a['id']])) ?>">Benzerini oluştur</a><?php endif ?>
    <?= '<span class="chip ' . $S['activity'][$a['status']][1] . ' dot">' . e($S['activity'][$a['status']][0]) . '</span>' ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>"><?= icon('users', 14) ?>Başvurular (<?= (int) $a['taken'] ?>)</a>
<?php if (($albumId ?? null) !== null): ?>
    <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.gallery.edit', ['id' => $albumId])) ?>"><?= icon('gallery', 14) ?>Fotoğraflar</a>
<?php elseif (Auth::canEdit() && in_array($a['status'], ['done', 'past'], true)): ?>
    <a class="btn btn-primary btn-sm" href="<?= e(url('admin.gallery.new', ['faaliyet' => $a['id']])) ?>" data-modal-link data-modal-title="Yeni Albüm · <?= e($a['title']) ?>"><?= icon('gallery', 14) ?>Albüm oluştur</a>
<?php endif ?>
    <a class="btn btn-ghost btn-sm" href="<?= e($a['url']) ?>" target="_blank" rel="noopener"><?= icon('ext', 14) ?>Sitede gör</a>
  </div>
<?php endif ?>
</div>

<form method="post" enctype="multipart/form-data" class="act-form quick-activity" data-act-form data-dirty-guard novalidate
      action="<?= e($isNew ? url('admin.activities.store') : url('admin.activities.update', ['id' => $a['id']])) ?>"
      data-gear="<?= e(json_encode($gear, JSON_UNESCAPED_UNICODE)) ?>" data-pay-note="<?= e((string) Settings::get('payment_note', '')) ?>">
  <?= csrf_field() ?>
  <div data-steps="tabs" data-reset-step="<?= $isNew ? '1' : '0' ?>">
  <div class="steps-nav" aria-label="Form bölümleri"></div>
  <fieldset<?= $ro ? ' disabled' : '' ?>>

  <section class="step panel" id="st-temel" data-icon="edit" data-title="Hızlı tanımla">
    <div class="panel-head"><h2>Hızlı tanımla</h2><span class="ph-sub">Temel bilgileri gir, kaydet. Ayrıntıları sonra tamamlayabilirsin.</span></div>
    <div class="panel-body form-grid">
      <?= $input('title', 'Faaliyet adı', 'text', ['req' => true, 'span' => true, 'attrs' => ['maxlength' => 160, 'minlength' => 3]]) ?>
      <?= $select('category', 'Kategori', $cats, ['req' => true]) ?>
      <?= $input('starts_on', 'Faaliyet tarihi', 'date', ['req' => true]) ?>
      <?= $input('location', 'Yer', 'text', ['req' => true, 'hint' => 'Ör. Rize / Çamlıhemşin · Kaçkar Dağı', 'attrs' => ['maxlength' => 160]]) ?>
      <details class="form-extra col-span"<?= $v('ends_on') !== '' ? ' open' : '' ?>><summary>Çok günlük faaliyet</summary>
      <?= $input('ends_on', 'Bitiş tarihi', 'date', ['hint' => 'Son günü seç.']) ?>
      </details>
      <?= $textarea('summary', 'Kısa açıklama', ['req' => true, 'rows' => 2, 'attrs' => ['maxlength' => 300, 'minlength' => 10], 'hint' => 'Kartta görünür; 1–2 cümle.']) ?>
      <div class="field col-span">
        <span class="field-label">Kapak görseli</span>
        <div class="cover-ctrl">
          <div class="cover-prev cover-prev-portrait" data-cover-prev<?= !$isNew && $a['cover_image'] ? ' style="background-image:url(\'' . e(img($a['cover_image'], 900)) . '\')"' : '' ?>></div>
          <div class="cover-btns">
            <label class="btn btn-ghost btn-sm file-btn"><?= icon('upload', 14) ?>Görsel seç<input type="file" name="cover" accept="image/jpeg,image/png,image/webp" data-cover-input data-crop-ratio="4/5" data-crop-label="Instagram dikey gönderi (4:5)"<?= $inv('cover') ?>></label><input type="hidden" name="cover_crop" value="" data-crop-value>
<?php if (!$isNew && $a['cover_image']): ?>
            <label class="check"><input type="checkbox" name="remove_cover" value="1"><span>Kapağı kaldır</span></label>
<?php endif ?>
          </div>
        </div>
        <span class="f-hint">JPG, PNG veya WebP · en fazla 15 MB. Dikey fotoğraf önerilir; ideal en az 1080 × 1350 piksel. Seçince 4:5 kırpma açılır. Başlık için altta boşluk bırak; yatay site kartının alanını kılavuzdan kontrol et.</span><?= $err('cover') ?>
      </div>
    </div>
  </section>

  <section class="step panel" id="st-zorluk" data-icon="route" data-title="Rota">
    <div class="panel-head"><h2>Zorluk ve konum</h2></div>
    <div class="panel-body form-grid">
      <div class="field col-span">
        <div class="score" data-score>
          <div class="score-top">
            <label for="f-difficulty_score">Zorluk derecesi</label>
            <output class="score-out" data-score-out for="f-difficulty_score"></output>
          </div>
          <input type="range" id="f-difficulty_score" name="difficulty_score" min="1" max="10" step="1" value="<?= $score ?>"<?= $inv('difficulty_score') ?>>
          <div class="score-scale" aria-hidden="true"><span class="s1">Kolay 1–2</span><span class="s2">Kolay-Orta 3–4</span><span class="s3">Orta 5–6</span><span class="s4">Orta-Zor 7–8</span><span class="s5">Zor 9–10</span></div>
          <label class="check"><input type="checkbox" name="no_difficulty" value="1" data-score-none<?= $noScore ? ' checked' : '' ?>><span>Zorluk derecesi yok (eğitim, sosyal etkinlik)</span></label>
        </div>
        <?= $err('difficulty_score') ?>
      </div>
      <?= $input('distance_km', 'Tahmini mesafe (km)', 'text', ['hint' => 'Boş bırakılabilir.', 'attrs' => ['inputmode' => 'decimal', 'maxlength' => 6]]) ?>
      <div class="field col-span map-picker" data-map-picker>
        <span class="field-label">Haritada konum</span>
        <div class="map-canvas" role="application" aria-label="Konum seçme haritası"></div>
        <div class="map-foot"><span data-map-out aria-live="polite"><?= $lat !== '' ? 'Seçilen konum: ' . e($lat) . ', ' . e($lng) : 'Konum seçilmedi.' ?></span><button type="button" class="btn btn-ghost btn-sm" data-map-clear<?= $lat === '' ? ' hidden' : '' ?>>Konumu kaldır</button></div>
        <input type="hidden" name="lat" value="<?= e($lat) ?>" data-map-lat><input type="hidden" name="lng" value="<?= e($lng) ?>" data-map-lng>
        <span class="f-hint">İsteğe bağlı. Sitede faaliyet sayfasında harita ve "Haritada aç" bağlantısı olarak görünür.</span><?= $err('lat') ?>
      </div>
    </div>
  </section>

  <section class="step panel" id="st-plan" data-icon="plan" data-title="Plan">
    <div class="panel-head"><h2>Faaliyet planı</h2></div>
    <div class="panel-body form-grid">
      <?= $textarea('body', 'Faaliyet planı', ['rows' => 9, 'hint' => 'Hazırladığın metni yapıştır. Paragrafları boş satırla ayır.']) ?>
      <?= $this->partial('admin/activities/guide-picker', ['a' => $a, 'v' => $v, 'inv' => $inv, 'err' => $err, 'ro' => $ro]) ?>
      <?= $input('meeting_text', 'Buluşma yeri ve saati', 'text', ['span' => true, 'hint' => 'Ör. Rize otogarı · 06:30', 'attrs' => ['maxlength' => 255]]) ?>
      <div class="field col-span">
        <span class="field-label">Gerekli malzemeler ve kontroller</span>
        <p class="f-hint" data-gear-note>Kategoriye göre Genel Ayarlar'daki not otomatik eklenir.</p>
        <details class="form-extra"<?= $v('gear_extra') !== '' ? ' open' : '' ?>><summary>Yalnız bu faaliyete ek malzeme notu</summary>
        <?= $textarea('gear_extra', 'Ek malzemeler ve kontroller', ['rows' => 3, 'attrs' => ['maxlength' => 3000], 'hint' => 'Her satıra bir madde. Ortak kategori notunun altına eklenir.']) ?>
        </details>
      </div>
    </div>
  </section>

  <section class="step panel" id="st-kapak" data-icon="publish" data-title="Başvuru ve yayın">
    <div class="panel-head"><h2>Başvuru ve yayın</h2></div>
    <div class="panel-body form-grid">
      <div class="field"><label for="fee-mode">Ücret bilgisi</label><select id="fee-mode" data-fee-mode><option value="unknown"<?= $v('fee') === '' ? ' selected' : '' ?>>Henüz belli değil</option><option value="free"<?= $v('fee') !== '' && (float) $v('fee') === 0.0 ? ' selected' : '' ?>>Ücretsiz</option><option value="paid"<?= (float) $v('fee') > 0 ? ' selected' : '' ?>>Ücretli</option></select></div>
      <?= $input('fee', 'Tahmini ücret (₺)', 'text', ['hint' => 'Genel ödeme açıklaması otomatik eklenir.', 'attrs' => ['inputmode' => 'decimal', 'maxlength' => 10]]) ?>
      <div class="field"><label class="check"><input type="checkbox" data-quota-limited<?= (int) $v('quota', 15) > 0 ? ' checked' : '' ?>><span>Kontenjan sınırlı</span></label></div>
      <?= $input('quota', 'Kontenjan', 'number', ['default' => 15, 'hint' => 'Dolunca başvurular yedek listeye alınır.', 'attrs' => ['inputmode' => 'numeric', 'min' => 0, 'max' => 5000]]) ?>
      <div class="field col-span"><label class="check"><input type="checkbox" name="is_published" value="1"<?= $published ? ' checked' : '' ?>><span>Sitede yayında</span></label></div>
      <div class="field col-span"><label class="check"><input type="checkbox" name="in_slider" value="1"<?= $inSlider ? ' checked' : '' ?><?= $inv('in_slider') ?>><span>Anasayfa slaytında göster</span></label><span class="f-hint">Kapak görseli gerekir; tarihi geçince slayttan kendiliğinden kalkar.</span><?= $err('in_slider') ?></div>
    </div>
  </section>

<?php if (!$isNew): ?>
  <section class="step panel" id="st-durum" data-icon="report" data-title="Durum ve rapor">
    <div class="panel-head"><h2>Durum ve rapor</h2></div>
    <div class="panel-body form-grid">
      <?= $select('manual_status', 'Başvuru durumu', ['' => 'Otomatik (kontenjan ve tarihe göre)', 'closed' => 'Başvuruları kapat', 'postponed' => 'Ertelendi', 'cancelled' => 'İptal edildi'], ['span' => true, 'hint' => 'Tarih geçince başvurular kapanır; gerçekleştiğini aşağıdan doğrula.']) ?>
      <details class="form-extra col-span"<?= $past ? ' open' : '' ?>><summary>Faaliyet sonrası rapor</summary>
      <label class="check"><input type="checkbox" name="is_completed" value="1"<?= ($posted ? !empty($old['is_completed']) : !empty($a['completed_on'])) ? ' checked' : '' ?>><span>Faaliyet gerçekleşti, tamamlandı</span></label>
      <span class="f-hint">Yalnızca tarihi geçmiş faaliyetler tamamlandı olarak işaretlenebilir.</span><?= $err('is_completed') ?>
      <?= $textarea('report_summary', 'Faaliyet raporu', ['rows' => 3, 'attrs' => ['maxlength' => 5000], 'hint' => 'Faaliyet bitince detay sayfasında ve kartta görünür.']) ?>
      <?= $input('report_participants', 'Katılan kişi sayısı', 'number', ['attrs' => ['inputmode' => 'numeric', 'min' => 0, 'max' => 5000]]) ?>
<?php if ($past && !empty($a['report_summary'])): ?><a class="btn btn-ghost" href="<?= e(url('admin.news.new', ['rapor' => $a['id']])) ?>">Raporu duyuru olarak paylaş</a><?php endif ?>
      </details>
      <details class="form-extra col-span"><summary>Gelişmiş: sayfa adresi</summary>
      <?= $input('slug', 'Sayfa adresi', 'text', ['hint' => 'Eski adres yeni adrese otomatik yönlenir.', 'attrs' => ['pattern' => '[a-z0-9-]*', 'autocapitalize' => 'off', 'maxlength' => 160]]) ?>
      </details>
    </div>
  </section>
<?php endif ?>

  <section class="step panel" id="st-onizleme" data-icon="eye" data-title="Önizleme" data-preview>
    <div class="panel-head"><h2>Önizleme</h2><span class="ph-sub">Sitede böyle görünecek</span></div>
    <div class="panel-body">
      <div class="pv">
        <div class="pv-card">
          <div class="pv-img" data-pv-img><span class="pv-date" data-pv-date></span><span class="pv-diff" data-pv-diff></span></div>
          <div class="pv-body"><span class="pv-loc" data-pv-loc></span><span class="pv-title" data-pv-title></span><span class="pv-sum" data-pv-sum></span></div>
        </div>
        <dl class="pv-facts" data-pv-facts></dl>
      </div>
      <noscript><p class="f-hint">Önizleme için JavaScript gerekli.</p></noscript>
    </div>
  </section>

  </fieldset>

<?php if (!$ro): ?>
  <div class="save-bar">
<?php if (!$isNew): ?>
    <button type="submit" class="btn btn-danger btn-sm" form="delete-form" data-confirm="Bu faaliyet silinsin mi? Bu işlem geri alınamaz."><?= icon('trash', 14) ?>Sil</button>
<?php endif ?>
    <span class="save-state" data-save-state role="status">Değişiklik yok</span><div class="tb-spacer"></div>
    <a class="btn btn-ghost" href="<?= e(url('admin.activities')) ?>">Vazgeç</a>

    <button type="submit" class="btn btn-primary" data-busy="Kaydediliyor…"><?= icon('check') ?><?= $isNew ? 'Faaliyeti Kaydet' : 'Kaydet' ?></button>
  </div>
<?php endif ?>
  </div>
  <aside class="quick-preview" aria-label="Canlı kart önizlemesi"><div class="panel-head"><h2>Site kartı</h2></div><div class="panel-body"><div data-compact-preview></div><p class="f-hint">Yazdıkça güncellenir. Kaydetmeden sitede yayınlanmaz.</p></div></aside>
</form>
<?php if (!$isNew && !$ro): ?>
<form id="delete-form" method="post" action="<?= e(url('admin.activities.delete', ['id' => $a['id']])) ?>" hidden><?= csrf_field() ?></form>
<?php endif ?>


<?php $this->section('head') ?>
<link rel="stylesheet" href="<?= e(asset('vendor/leaflet/leaflet.css')) ?>">
<script src="<?= e(asset('vendor/leaflet/leaflet.js')) ?>" defer></script>
<?php $this->end() ?>
