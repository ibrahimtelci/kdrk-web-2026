<?php
/**
 * Bakım sayfası — settings.maintenance = 1 iken sunucu 503 ile bunu döndürür (CLAUDE.md §14).
 * Kendi iskeleti vardır; site layout'u kullanmaz.
 */
$phone = (string) setting('contact_phone', '');
$email = (string) setting('contact_email', '');
$eta = setting('maintenance_eta');
$instagram = (string) setting('social_instagram', '');
$whatsapp = (string) setting('social_whatsapp', '');
?>
<!doctype html>
<html lang="tr" data-theme="dark">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Bakım Çalışması · KDRK</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= e(asset('system.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('maintenance.css')) ?>">
</head>
<body class="mt-body">
<main class="mt">
  <div class="mt-bg" aria-hidden="true"></div>
  <div class="mt-overlay" aria-hidden="true"></div>
  <div class="topo-watermark" aria-hidden="true"></div>
  <header class="mt-top">
    <span class="kdrk-mark mt-logo"><img src="<?= e(asset('kdrk-logo-2026-sm.png')) ?>" alt="KDRK"></span>
    <span class="mt-stamp">EST. <?= club_stat('kurulus') ?> · RİZE</span>
  </header>
  <div class="mt-main">
    <section class="mt-content">
      <span class="eyebrow">Bakım Çalışması</span>
      <h1><?= e(setting('maintenance_title', 'Sayfamız bakım aşamasında.')) ?></h1>
      <p class="mt-lede"><?= e(setting('maintenance_message', 'Çok yakında sizlerle olacağız.')) ?></p>
      <div class="mt-rope" aria-hidden="true"><span></span></div>
<?php if ($eta): ?>
      <p class="mt-eta">TAHMİNİ DÖNÜŞ · <?= e(tr_date($eta)) ?> · <?= e(date('H:i', strtotime($eta))) ?></p>
<?php endif ?>
<?php if ($instagram !== '' || $whatsapp !== ''): ?>
      <div class="mt-follow">
        <span class="mt-follow-label">Bu sürede bizi takip edin</span>
        <div class="mt-links">
<?php if ($instagram !== ''): ?>
          <a href="<?= e($instagram) ?>" target="_blank" rel="noopener" class="mt-link">Instagram</a>
<?php endif ?>
<?php if ($whatsapp !== ''): ?>
          <a href="<?= e($whatsapp) ?>" target="_blank" rel="noopener" class="mt-link">WhatsApp Topluluk</a>
<?php endif ?>
        </div>
      </div>
<?php endif ?>
    </section>
    <aside class="mt-info" aria-label="İletişim bilgileri">
      <dl class="mt-dl">
        <div><dt>Adres</dt><dd><?= e(setting('contact_address', '')) ?><span>Rize Merkez / Rize</span></dd></div>
<?php if ($phone !== ''): ?>
        <div><dt>Telefon</dt><dd><a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a></dd></div>
<?php endif ?>
<?php if ($email !== ''): ?>
        <div><dt>E-posta</dt><dd><a href="mailto:<?= e($email) ?>"><?= e($email) ?></a></dd></div>
<?php endif ?>
      </dl>
      <a class="mt-route" href="https://www.google.com/maps/search/?api=1&amp;query=41.0239479%2C40.5262278" target="_blank" rel="noopener">Haritada aç</a>
    </aside>
  </div>
  <footer class="mt-foot">
    <div class="mt-foot-right"><span class="mt-fullname">Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü</span><span class="mt-coord">41°01'N · 40°31'E</span></div>
  </footer>
</main>
</body>
</html>
