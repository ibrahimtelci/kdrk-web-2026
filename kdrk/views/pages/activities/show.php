<?php
/**
 * Faaliyet detayı + başvuru formu.
 * @var array $a       Activity::decorate() çıktısı
 * @var array      $photos  faaliyete bağlı albümün ilk fotoğrafları (gallery_photos)
 * @var array|null $album   o albüm (Gallery::decorate) — "Tüm fotoğraflar" linki
 * @var array $old     sunucu doğrulamasından dönen form değerleri (JS kapalıyken)
 * @var array $errors  alan => hata mesajı
 * @var bool  $applied JS kapalıyken başarılı gönderim sonrası
 */
$num = static fn ($n): string => number_format((float) $n, 0, ',', '.');
$fee = $a['fee'] === null ? null : ((float) $a['fee'] > 0 ? '₺' . $num($a['fee']) : 'Ücretsiz');
$phone = (string) setting('contact_phone', '');
$tel = preg_replace('/[^\d+]/', '', $phone);
$email = (string) setting('contact_email', '');
$diffLabel = $a['difficulty_text'] . ($a['difficulty_level'] ? ' · ' . $a['difficulty_level'] : '');
$place = trim(explode('·', explode('/', $a['location'])[0])[0]);
$waitlist = $a['status'] === 'full';
$applyLabel = $waitlist ? 'Yedek Listeye Yazıl' : 'Başvuru Yap';
$old += ['name' => '', 'tc' => '', 'phone' => '', 'email' => '', 'emer_name' => '', 'emer_phone' => '', 'health' => ''];
$err = static fn (string $f): string => $errors[$f] ?? '';
$field = static function (string $f) use ($err): string {
    return ($err($f) !== '' ? ' class="is-error" aria-invalid="true"' : '') . ' aria-describedby="e-' . $f . '"';
};
$facts = array_filter([
    'Buluşma'          => $a['meeting_text'],
    'Dönüş'            => $a['return_text'],
    'Ücrete dahil'     => $a['included_text'],
    'Katılımcıya ait'  => $a['personal_text'],
    'Katılım şartı'    => $a['requirement_text'],
    'Hava durumu'      => $a['weather_text'],
]);
$summary = array_filter([
    'Tarih'        => tr_range($a['starts_on'], $a['ends_on']),
    'Süre'         => $a['duration_text'],
    'Zorluk'       => $diffLabel,
    'Zirve'        => $a['summit_m'] ? $num($a['summit_m']) . ' m' : null,
    'Mesafe'       => $a['distance_km'] ? rtrim(rtrim(number_format((float) $a['distance_km'], 1, ',', '.'), '0'), ',') . ' km' : null,
    'İrtifa farkı' => $a['elevation_gain_m'] ? $num($a['elevation_gain_m']) . ' m' : null,
]);
?>
<section class="detail-hero always-dark">
  <div class="detail-hero-img zoom"><?= img_tag($a['cover_image'], '100vw', 1600, '', false) ?></div>
  <div class="detail-hero-overlay"></div>

  <nav class="breadcrumb" aria-label="Breadcrumb">
    <a href="<?= e(url('home')) ?>">Anasayfa</a>
    <span class="sep" aria-hidden="true">›</span>
    <a href="<?= e(url('faaliyetler')) ?>">Faaliyetler</a>
    <span class="sep" aria-hidden="true">›</span>
    <span class="current" aria-current="page"><?= e($a['title']) ?></span>
  </nav>

  <div class="container detail-hero-content">
    <div class="detail-hero-top">
      <span class="hero-pill <?= e($a['difficulty_class']) ?>"><?= e($diffLabel) ?></span>
      <span class="hero-date-badge"><span class="day"><?= e(substr($a['starts_on'], 8, 2)) ?></span> · <?= e(tr_month($a['starts_on']) . ' ' . substr($a['starts_on'], 0, 4)) ?></span>
    </div>
    <div class="detail-hero-bottom">
      <span class="eyebrow"><?= e($a['category_text']) ?></span>
      <h1><?= e($a['title']) ?></h1>
      <div class="sub">
        <span><?= e($a['location']) ?></span>
<?php if ($a['duration_text']): ?>
        <span class="dot">•</span><span><?= e($a['duration_text']) ?></span>
<?php endif ?>
      </div>
    </div>
  </div>
</section>

<section class="detail-summary" aria-label="Faaliyet özeti">
  <div class="container">
    <dl class="summary-grid">
<?php foreach ($summary as $k => $v): ?>
      <div><dt><?= e($k) ?></dt><dd><?php if ($k === 'Zorluk'): ?><span class="lv-dot <?= e($a['difficulty_class']) ?>" aria-hidden="true"></span><?php endif ?><?= e($v) ?></dd></div>
<?php endforeach ?>
    </dl>
  </div>
</section>

<section class="detail-body">
  <div class="container">
    <div class="detail-grid">

      <div class="detail-main">

        <article class="detail-block reveal" data-screen-label="01 Hakkında">
          <span class="eyebrow">Faaliyet Hakkında</span>
          <h2><?= e($a['summary']) ?></h2>
<?php if ($a['body']): ?>
          <?= paragraphs($a['body']) /* düz metin; HTML kaçışlı */ ?>
<?php endif ?>
<?php if ($a['status'] === 'done' && $a['report_summary']): ?>
          <div class="kb-callout"><strong>Faaliyet raporu.</strong> <?= e($a['report_summary']) ?></div>
<?php endif ?>
        </article>

<?php if ($facts): ?>
        <article class="detail-block reveal" data-screen-label="02 Bilmen Gerekenler">
          <span class="eyebrow">Bilmen Gerekenler</span>
          <h2>Katılmadan önce.</h2>
          <dl class="facts">
<?php foreach ($facts as $k => $v): ?>
            <div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div>
<?php endforeach ?>
            <div><dt>İptal ve iade</dt><dd>Koşullar için faaliyet sorumlusuna <a href="#basvuru">buradan</a> ulaş.</dd></div>
          </dl>
        </article>
<?php endif ?>

<?php if ($a['program']): ?>
        <article class="detail-block reveal" data-screen-label="03 Program">
          <span class="eyebrow">Program</span>
          <h2><?= count($a['program']) > 1 ? count($a['program']) . ' günlük rota.' : 'Günün akışı.' ?></h2>
          <div class="timeline">
<?php foreach ($a['program'] as $i => $day): ?>
            <div class="timeline-day">
              <span class="day-label"><?= $i + 1 ?>. Gün<?= !empty($day['date']) ? ' — ' . e(mb_substr(tr_date_long($day['date']), 0, -5)) : '' ?></span>
              <h3><?= e($day['title']) ?></h3>
              <div class="timeline-events">
<?php foreach ($day['items'] as $ev): ?>
                <div class="timeline-event"><span class="time"><?= e($ev['time']) ?></span><span class="what"><?= e($ev['text']) ?></span></div>
<?php endforeach ?>
              </div>
            </div>
<?php endforeach ?>
          </div>
        </article>
<?php endif ?>

<?php if ($a['equipment']): ?>
        <article class="detail-block reveal" data-screen-label="04 Malzemeler">
          <span class="eyebrow">Gerekli Malzemeler</span>
          <h2>Çantanda olması gerekenler.</h2>
          <div class="gear-list">
<?php foreach ($a['equipment'] as $g): ?>
            <div class="gear-item"><span class="gear-dot" aria-hidden="true"></span><?= e($g) ?></div>
<?php endforeach ?>
          </div>
        </article>
<?php endif ?>

<?php if ($photos): $n = count($photos); ?>
        <article class="detail-block reveal" data-screen-label="05 Galeri">
          <div class="detail-block-head">
            <div style="display:flex;flex-direction:column;gap:16px">
              <span class="eyebrow">Faaliyet Fotoğrafları</span>
              <h2>Sahadan kareler.</h2>
            </div>
            <a href="<?= e($album['url']) ?>" class="btn-text">Tüm Fotoğraflar (<?= (int) $album['photo_count'] ?>) <span class="arrow">→</span></a>
          </div>
          <div class="photo-grid">
<?php foreach ($photos as $i => $p): ?>
            <button type="button" class="photo-tile" data-lightbox="<?= e(img($p['path'], 1600)) ?>" aria-label="Fotoğrafı büyüt (<?= $i + 1 ?> / <?= $n ?>)"><div><?= img_tag($p['path'], '(min-width: 1024px) 200px, 33vw', 400, $p['caption'] ?? '') ?></div></button>
<?php endforeach ?>
          </div>
        </article>
<?php endif ?>

      </div>

      <aside class="detail-sidebar" id="basvuru">
        <div class="apply-card">
          <div class="apply-head"><span class="eyebrow">Başvuru</span><span class="activity-status <?= e($a['status']) ?> is-inline"><?= e($a['status_text']) ?></span></div>
<?php if ((int) $a['quota'] > 0 && $a['status'] !== 'done'): ?>
          <div class="apply-attendees">
            <div class="apply-attendees-num"><?= (int) $a['taken'] ?> <span class="of">/ <?= (int) $a['quota'] ?> kişi</span></div>
            <div class="progress-bar" role="img" aria-label="<?= (int) $a['quota'] ?> kişilik kontenjanın <?= (int) $a['taken'] ?>'i dolu"><div class="progress-fill<?= $a['can_apply'] ? '' : ' is-muted' ?>" style="width:<?= (int) round($a['fill'] * 100) ?>%"></div></div>
          </div>
<?php endif ?>
<?php if ($fee !== null && $a['status'] !== 'done'): ?>
          <div class="apply-price">
            <span class="eyebrow no-bar">Faaliyet ücreti</span>
            <div class="num"><?= e($fee) ?></div>
<?php if ($a['fee_note']): ?>
            <div class="desc"><?= e($a['fee_note']) ?></div>
<?php endif ?>
          </div>
<?php endif ?>

<?php if ($applied): ?>
          <div class="form-success" role="status"><h3>Başvurun alındı.</h3><p>Faaliyet sorumlusu başvurunu değerlendirip seninle iletişime geçecek.</p></div>
<?php elseif ($a['can_apply'] || $waitlist): ?>
<?php if ($waitlist): ?>
          <p class="apply-note">Kontenjan doldu. Yedek listeye yazılırsan yer açıldığında sırayla aranırsın.</p>
<?php endif ?>
          <button class="btn-apply-big" data-open-apply><?= e($applyLabel) ?> <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></button>
<?php else: ?>
          <p class="apply-note"><?= e([
              'closed'    => 'Bu faaliyetin başvuruları kapandı.',
              'postponed' => 'Bu faaliyet ertelendi. Yeni tarih belli olunca duyurulacak.',
              'cancelled' => 'Bu faaliyet iptal edildi.',
              'done'      => 'Bu faaliyet tamamlandı.' . ($a['report_participants'] ? ' ' . (int) $a['report_participants'] . ' kişi katıldı.' : ''),
          ][$a['status']] ?? '') ?></p>
          <a class="btn btn-ghost" href="<?= e(url('faaliyetler')) ?>">Yaklaşan faaliyetlere bak</a>
<?php endif ?>

          <div class="apply-divider"></div>
<?php if ($a['leader_name']): ?>
          <div class="organizer">
            <span class="eyebrow no-bar">Faaliyet sorumlusu</span>
            <div class="organizer-row">
              <div class="organizer-avatar" aria-hidden="true"><?= e(mb_substr($a['leader_name'], 0, 1)) ?></div>
              <div><div class="organizer-name"><?= e($a['leader_name']) ?></div><?php if ($a['leader_title']): ?><div class="organizer-bio"><?= e($a['leader_title']) ?></div><?php endif ?></div>
            </div>
          </div>
<?php endif ?>
          <div class="organizer-actions">
<?php if ($tel): ?>
            <a class="btn btn-ghost btn-sm" href="tel:<?= e($tel) ?>">Kulübü ara</a>
<?php endif ?>
<?php if ($email): ?>
            <a class="btn btn-ghost btn-sm" href="mailto:<?= e($email) ?>?subject=<?= rawurlencode($a['title']) ?>">Soru sor</a>
<?php endif ?>
          </div>
          <div class="share-row">
            <span class="eyebrow no-bar">Paylaş</span>
            <div class="share-actions">
              <a class="share-btn" data-share-wa href="https://wa.me/?text=<?= rawurlencode($a['title'] . ' ' . $a['url']) ?>" target="_blank" rel="noopener"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z"/></svg>WhatsApp</a>
              <button type="button" class="share-btn" data-copy-link><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg><span data-copy-label>Linki kopyala</span></button>
              <button type="button" class="share-btn" data-native-share hidden>Paylaş…</button>
            </div>
          </div>
        </div>
      </aside>

    </div>
  </div>
</section>

<?php if ($a['can_apply'] || $waitlist): ?>
<div class="modal-backdrop<?= $errors ? ' is-open' : '' ?>" data-modal aria-hidden="<?= $errors ? 'false' : 'true' ?>">
  <div class="modal" role="dialog" aria-labelledby="modal-title" aria-modal="true">
    <button class="modal-close" data-modal-close aria-label="Başvuru formunu kapat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    <div data-modal-form>
      <h2 id="modal-title"><?= e($a['title']) ?></h2>
      <div class="modal-sub"><?= e(tr_upper(tr_range($a['starts_on'], $a['ends_on']) . ' · ' . $place . ($fee ? ' · ' . $fee : ''))) ?></div>
<?php if ($waitlist): ?>
      <p class="form-note">Kontenjan dolu; başvurun <strong>yedek listeye</strong> alınır.</p>
<?php endif ?>
      <p class="form-note">Yıldızlı (*) alanlar zorunludur.</p>
      <div class="form-alert" data-form-alert role="alert" tabindex="-1"<?= $errors ? '' : ' hidden' ?>><?php if ($errors): ?><strong><?= count($errors) ?> alanı düzeltmen gerekiyor.</strong><?php endif ?></div>
      <form id="apply-form" method="post" action="<?= e(url('faaliyet.basvuru', ['slug' => $a['slug']])) ?>" novalidate>
        <?= csrf_field() ?>
        <div class="hp-field" aria-hidden="true"><label for="f-website">Web sitesi</label><input type="text" id="f-website" name="website" tabindex="-1" autocomplete="off"></div>
        <div class="form-group"><label for="f-name">Ad Soyad <span class="req" aria-hidden="true">*</span></label><input type="text" id="f-name" name="name" value="<?= e($old['name']) ?>" autocomplete="name" autocapitalize="words" required aria-required="true"<?= $field('name') ?>><span class="err" id="e-name"<?= $err('name') ? '' : ' hidden' ?>><?= e($err('name')) ?></span></div>
        <div class="form-row">
          <div class="form-group"><label for="f-tc">TC Kimlik No <span class="req" aria-hidden="true">*</span></label><input type="text" id="f-tc" name="tc" value="<?= e($old['tc']) ?>" inputmode="numeric" pattern="[0-9]*" maxlength="11" autocomplete="off" required aria-required="true"<?= $field('tc') ?>><span class="hint" id="h-tc">11 haneli · şifreli saklanır</span><span class="err" id="e-tc"<?= $err('tc') ? '' : ' hidden' ?>><?= e($err('tc')) ?></span></div>
          <div class="form-group"><label for="f-phone">Telefon <span class="req" aria-hidden="true">*</span></label><input type="tel" id="f-phone" name="phone" value="<?= e($old['phone']) ?>" inputmode="tel" autocomplete="tel-national" placeholder="5XX XXX XX XX" required aria-required="true"<?= $field('phone') ?>><span class="err" id="e-phone"<?= $err('phone') ? '' : ' hidden' ?>><?= e($err('phone')) ?></span></div>
        </div>
        <div class="form-group"><label for="f-email">E-posta <span class="req" aria-hidden="true">*</span></label><input type="email" id="f-email" name="email" value="<?= e($old['email']) ?>" inputmode="email" autocomplete="email" autocapitalize="off" required aria-required="true"<?= $field('email') ?>><span class="err" id="e-email"<?= $err('email') ? '' : ' hidden' ?>><?= e($err('email')) ?></span></div>
        <div class="form-row">
          <div class="form-group"><label for="f-emer-name">Acil durumda aranacak kişi <span class="req" aria-hidden="true">*</span></label><input type="text" id="f-emer-name" name="emer_name" value="<?= e($old['emer_name']) ?>" autocapitalize="words" required aria-required="true"<?= $field('emer_name') ?>><span class="err" id="e-emer_name"<?= $err('emer_name') ? '' : ' hidden' ?>><?= e($err('emer_name')) ?></span></div>
          <div class="form-group"><label for="f-emer-phone">Acil durum telefonu <span class="req" aria-hidden="true">*</span></label><input type="tel" id="f-emer-phone" name="emer_phone" value="<?= e($old['emer_phone']) ?>" inputmode="tel" placeholder="5XX XXX XX XX" required aria-required="true"<?= $field('emer_phone') ?>><span class="err" id="e-emer_phone"<?= $err('emer_phone') ? '' : ' hidden' ?>><?= e($err('emer_phone')) ?></span></div>
        </div>
        <div class="form-group"><label for="f-health">Sağlık notları <span class="opt">(isteğe bağlı · şifreli saklanır)</span></label><textarea id="f-health" name="health" rows="3" maxlength="1000" placeholder="Alerji, kronik rahatsızlık, kullanılan ilaç…"><?= e($old['health']) ?></textarea></div>
        <label class="form-checkbox"><input type="checkbox" id="f-kvkk" name="kvkk" value="1" required aria-required="true"<?= $field('kvkk') ?>><span class="box" aria-hidden="true"></span><span class="text"><a href="<?= e(url('kulup-uyeligi')) ?>" target="_blank" rel="noopener">KVKK Aydınlatma Metni</a>'ni okudum, kişisel verilerimin işlenmesine onay veriyorum. <span class="req" aria-hidden="true">*</span></span></label><span class="err err-cb" id="e-kvkk"<?= $err('kvkk') ? '' : ' hidden' ?>><?= e($err('kvkk')) ?></span>
        <div class="form-actions"><button type="button" class="btn btn-ghost" data-modal-close>Vazgeç</button><button type="submit" class="btn btn-primary" data-submit><span data-submit-label><?= $waitlist ? 'Yedek Listeye Yazıl' : 'Başvuruyu Gönder' ?></span></button></div>
      </form>
    </div>
    <div data-modal-success hidden><div class="form-success"><span class="check-big"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M20 6L9 17l-5-5"/></svg></span><h3 data-success-title>Başvurun alındı.</h3><p>Faaliyet sorumlusu başvurunu değerlendirip seninle iletişime geçecek.<?php if ($tel): ?> Sorun olursa kulübü arayabilirsin: <a href="tel:<?= e($tel) ?>"><?= e($phone) ?></a><?php endif ?></p><button type="button" class="btn btn-primary" data-modal-close>Tamam</button></div></div>
    <div data-modal-error hidden><div class="form-success is-error"><span class="check-big"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 9v4M12 17h.01"/><circle cx="12" cy="12" r="10"/></svg></span><h3>Başvuru gönderilemedi.</h3><p data-error-text>Girdiğin bilgiler kaybolmadı. Bağlantını kontrol edip tekrar dene; sorun sürerse kulübü ara.</p><div class="form-actions center"><?php if ($tel): ?><a class="btn btn-ghost" href="tel:<?= e($tel) ?>">Kulübü ara</a><?php endif ?><button type="button" class="btn btn-primary" data-retry>Tekrar dene</button></div></div></div>
  </div>
</div>
<div class="apply-bar" data-apply-bar><div class="apply-bar-info"><strong><?= e($fee ?? $a['title']) ?></strong><span><?= e(tr_date($a['starts_on'], false) . ' · ' . tr_upper($a['status_text'])) ?></span></div><button class="btn btn-primary" data-open-apply><?= e($applyLabel) ?></button></div>
<?php endif ?>

<?php if ($photos): ?>
<div class="dlb" data-lightbox-modal role="dialog" aria-modal="true" aria-label="Fotoğraf görüntüleyici" aria-hidden="true"><button class="dlb-btn dlb-close" data-lb-close aria-label="Kapat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button><button class="dlb-btn dlb-prev" data-lb-prev aria-label="Önceki fotoğraf"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button><figure class="dlb-fig"><img data-lightbox-img alt=""><figcaption class="dlb-cap" data-lb-cap></figcaption></figure><button class="dlb-btn dlb-next" data-lb-next aria-label="Sonraki fotoğraf"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></button></div>
<?php endif ?>

<?php $this->section('scripts') ?>
<script src="<?= e(asset('activity-detail.js')) ?>" defer></script>
<?php $this->end() ?>
