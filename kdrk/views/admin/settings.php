<?php
/**
 * @var array $s       settings tablosu
 * @var array $old
 * @var array $errors
 */
$v = static fn (string $k): string => (string) ($old[$k] ?? $s[$k] ?? '');
$err = static fn (string $k): string => isset($errors[$k]) ? '<span class="f-err" id="err-' . $k . '">' . e($errors[$k]) . '</span>' : '';
$inv = static fn (string $k): string => isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '';
$field = static function (string $k, string $label, string $type = 'text', ?string $hint = null, array $attrs = []) use ($v, $err, $inv): string {
    $a = '';
    foreach ($attrs as $n => $val) {
        $a .= ' ' . $n . '="' . e($val) . '"';
    }
    return '<div class="field"><label for="s-' . $k . '">' . e($label) . '</label><input id="s-' . $k . '" name="' . $k . '" type="' . $type . '" value="' . e($v($k)) . '"' . $a . $inv($k) . '>'
        . ($hint ? '<span class="f-hint">' . e($hint) . '</span>' : '') . $err($k) . '</div>';
};
$maintenance = ($old['maintenance'] ?? $s['maintenance'] ?? '0') === '1';
$eta = $v('maintenance_eta');
$eta = $eta !== '' ? substr(str_replace(' ', 'T', $eta), 0, 16) : '';
?>
<form method="post" action="<?= e(url('admin.settings.update')) ?>" novalidate>
  <?= csrf_field() ?>
  <div data-steps="tabs">
  <div class="steps-nav" aria-label="Ayar bölümleri"></div>

  <div class="step" id="ay-genel" data-title="Genel">

  <section class="panel<?= $maintenance ? ' panel-alert' : '' ?>">
    <div class="panel-head"><h2>Bakım modu</h2><?php if ($maintenance): ?><span class="chip red dot">Açık</span><?php else: ?><span class="chip green dot">Kapalı · site yayında</span><?php endif ?></div>
    <div class="panel-body form-grid">
      <div class="field col-span">
        <input type="hidden" name="maintenance" value="0">
        <label class="check check-lg"><input type="checkbox" name="maintenance" value="1"<?= $maintenance ? ' checked' : '' ?>><span>Siteyi bakım moduna al</span></label>
        <span class="f-hint">Açıkken ziyaretçiler bakım sayfasını görür (503). Panele giriş yapmış olduğun için sen siteyi normal görmeye devam edersin.</span>
      </div>
      <?= $field('maintenance_title', 'Bakım başlığı') ?>
      <div class="field"><label for="s-maintenance_eta">Tahmini dönüş</label><input id="s-maintenance_eta" name="maintenance_eta" type="datetime-local" value="<?= e($eta) ?>"<?= $inv('maintenance_eta') ?>><span class="f-hint">Boş bırakılabilir.</span><?= $err('maintenance_eta') ?></div>
      <div class="field col-span"><label for="s-maintenance_message">Bakım mesajı</label><textarea id="s-maintenance_message" name="maintenance_message" rows="2" maxlength="400"><?= e($v('maintenance_message')) ?></textarea></div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Kulüp rakamları</h2><span class="ph-sub">Sitedeki tüm sayaçlar buradan beslenir</span></div>
    <div class="panel-body form-grid">
      <?= $field('stat_kurulus', 'Kuruluş yılı', 'number', 'Kulübün yaşı bundan otomatik hesaplanır.', ['inputmode' => 'numeric']) ?>
      <?= $field('stat_zirve', 'Zirve tırmanışı (+)', 'number', null, ['inputmode' => 'numeric']) ?>
      <?= $field('stat_yuruyus', 'Doğa yürüyüşü (+)', 'number', null, ['inputmode' => 'numeric']) ?>
      <?= $field('stat_arama_kurtarma', 'Arama-kurtarma (+)', 'number', null, ['inputmode' => 'numeric']) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>İletişim bilgileri</h2><span class="ph-sub">Footer, İletişim ve detay sayfalarında görünür</span></div>
    <div class="panel-body form-grid">
      <?= $field('contact_email', 'E-posta', 'email', null, ['inputmode' => 'email', 'autocapitalize' => 'off']) ?>
      <?= $field('contact_phone', 'Telefon', 'tel', 'Ör. +90 (464) 214 71 10') ?>
      <div class="field col-span"><label for="s-contact_address">Adres</label><input id="s-contact_address" name="contact_address" value="<?= e($v('contact_address')) ?>"></div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Sosyal hesaplar</h2><span class="ph-sub">Boş bırakılan hesabın ikonu sitede gösterilmez</span></div>
    <div class="panel-body form-grid">
      <?= $field('social_instagram', 'Instagram', 'url', 'https://instagram.com/…', ['inputmode' => 'url', 'autocapitalize' => 'off']) ?>
      <?= $field('social_whatsapp', 'WhatsApp topluluk linki', 'url', 'https://chat.whatsapp.com/…', ['inputmode' => 'url', 'autocapitalize' => 'off']) ?>
      <?= $field('social_youtube', 'YouTube', 'url', null, ['inputmode' => 'url', 'autocapitalize' => 'off']) ?>
      <?= $field('social_strava', 'Strava', 'url', null, ['inputmode' => 'url', 'autocapitalize' => 'off']) ?>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>KVKK</h2></div>
    <div class="panel-body form-grid">
      <?= $field('kvkk_retention_days', 'Başvuru verisi saklama süresi (gün)', 'number', 'Faaliyet bittikten bu kadar gün sonra TC kimlik ve sağlık notu silinir; ad ve katılım kaydı kalır. Kulüp kararıyla belirlenir.', ['inputmode' => 'numeric', 'min' => 30]) ?>
    </div>
  </section>
  </div>

  <div class="step" id="ay-faaliyet" data-title="Faaliyet notları">
  <section class="panel">
    <div class="panel-head"><h2>Ödeme açıklaması</h2><span class="ph-sub">Ücreti girilen her faaliyette, ücretin altında otomatik görünür</span></div>
    <div class="panel-body form-grid">
      <div class="field col-span"><label for="s-payment_note">Genel ödeme açıklaması</label><textarea id="s-payment_note" name="payment_note" rows="3" maxlength="800"><?= e($v('payment_note')) ?></textarea><span class="f-hint">Ör. Ücret ulaşım ve kamp giderini kapsar. Ödeme, başvurun onaylandıktan sonra kulüp hesabına yapılır.</span></div>
    </div>
  </section>

  <section class="panel">
    <div class="panel-head"><h2>Gerekli malzemeler ve kontroller</h2><span class="ph-sub">Faaliyetin kategorisine göre detay sayfasında otomatik görünür</span></div>
    <div class="panel-body form-grid">
<?php foreach (\App\Models\Activity::CATEGORIES as $k => $label): ?>
      <div class="field"><label for="s-gear_<?= e($k) ?>"><?= e($label) ?></label><textarea id="s-gear_<?= e($k) ?>" name="gear_<?= e($k) ?>" rows="5" maxlength="2000"><?= e($v('gear_' . $k)) ?></textarea></div>
<?php endforeach ?>
      <p class="f-hint col-span">Her satıra bir madde yaz. Boş bırakılan kategoride bu bölüm sitede görünmez.</p>
    </div>
  </section>
  </div>

  <div class="save-bar">
    <div class="tb-spacer"></div>
    <button type="submit" class="btn btn-primary" data-busy="Kaydediliyor…"><?= icon('check') ?>Ayarları Kaydet</button>
  </div>
  </div>
</form>
