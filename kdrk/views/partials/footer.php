<?php
/**
 * Footer — tek şablon (CLAUDE.md §3): 4 kolon + footer-fullname + TDF rozeti.
 * İletişim ve sosyal hesaplar settings tablosundan gelir; adresi girilmemiş hesap gösterilmez.
 */
$email = (string) setting('contact_email', '');
$phone = (string) setting('contact_phone', '');
$socials = array_filter([
    'Instagram' => [setting('social_instagram'), '<rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>'],
    'YouTube'   => [setting('social_youtube'), '<path d="M22.54 6.42a2.78 2.78 0 00-1.94-2C18.88 4 12 4 12 4s-6.88 0-8.6.46a2.78 2.78 0 00-1.94 2A29 29 0 001 11.75a29 29 0 00.46 5.33A2.78 2.78 0 003.4 19.08C5.12 19.54 12 19.54 12 19.54s6.88 0 8.6-.46a2.78 2.78 0 001.94-2 29 29 0 00.46-5.33 29 29 0 00-.46-5.33z"/><path d="M9.75 15.02V8.48l5.75 3.27z" fill="currentColor"/>'],
    'WhatsApp'  => [setting('social_whatsapp'), '<path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8z"/>'],
    'Strava'    => [setting('social_strava'), '<path d="M9 5l5 10h-3l-2-4-2 4H4z"/><path d="M14 15l3 6 3-6h-2.5l-.5 1-.5-1z"/>'],
], static fn ($s) => !empty($s[0]));
$menu = require KDRK_ROOT . '/app/menu.php';
$activityLinks = array_values(array_filter($menu[2]['items'], static fn ($i) => $i[0] !== '---'));
?>
<footer class="footer">
  <div class="topo-watermark"></div>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-col">
        <div class="footer-brand"><span class="kdrk-mark"><img src="<?= e(asset('kdrk-logo-2026-sm.png')) ?>" alt=""></span>KDRK</div>
        <p class="footer-fullname">Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü</p>
        <p class="footer-mission">Dağa, doğaya, ipe saygıyla.<br><?= club_stat('kurulus') ?>'den beri Kaçkar'da.</p>
<?php if ($socials): ?>
        <div class="footer-socials">
<?php foreach ($socials as $label => [$href, $icon]): ?>
          <a href="<?= e($href) ?>" aria-label="<?= e($label) ?>" target="_blank" rel="noopener"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" aria-hidden="true"><?= $icon ?></svg></a>
<?php endforeach ?>
        </div>
<?php endif ?>
      </div>
      <div class="footer-col">
        <h4>Kulüp</h4>
        <ul>
          <li><a href="<?= e(url('hakkimizda')) ?>">Hakkımızda</a></li>
          <li><a href="<?= e(url('yonetim-kurulu')) ?>">Yönetim Kurulu</a></li>
          <li><a href="<?= e(url('emegi-gecenler')) ?>">Emeği Geçenler</a></li>
          <li><a href="<?= e(url('kulup-uyeligi')) ?>">Kulüp Üyeliği</a></li>
          <li><a href="<?= e(url('sporcu-lisansi')) ?>">Sporcu Lisansı</a></li>
          <li><a href="<?= e(url('galeri')) ?>">Fotoğraf Galerisi</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Faaliyetler</h4>
        <ul>
<?php foreach ($activityLinks as $l): ?>
          <li><a href="<?= e(url($l[1], $l[2] ?? [])) ?>"><?= e($l[0]) ?></a></li>
<?php endforeach ?>
        </ul>
      </div>
      <div class="footer-col">
        <h4>İletişim</h4>
        <div class="footer-contact">
          <span><strong>Rize Merkez</strong></span>
          <span><?= e(setting('contact_address', '')) ?></span>
<?php if ($email !== ''): ?>
          <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
<?php endif ?>
<?php if ($phone !== ''): ?>
          <a href="tel:<?= e(preg_replace('/[^\d+]/', '', $phone)) ?>"><?= e($phone) ?></a>
<?php endif ?>
          <a href="<?= e(url('iletisim')) ?>">İletişim formu →</a>
          <span class="map-pin"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>41°01'N · 40°31'E</span>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> KDRK · Tüm Hakları Saklıdır</span>
      <div class="badges"><img src="<?= e(asset('tdf-logo-sm.png')) ?>" class="tdf-badge" alt="Türkiye Dağcılık Federasyonu" loading="lazy"><span class="badge">TDF ÜYESİ</span></div>
    </div>
  </div>
</footer>
