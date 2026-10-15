<?php
/**
 * Genel hata sayfası (500, 405, 419 …). Teknik ayrıntı gösterilmez (CLAUDE.md §15).
 * @var int $status
 */
$messages = [
    419 => ['Oturum süresi doldu.', 'Sayfa uzun süre açık kaldığı için güvenlik kodu geçersiz oldu. Sayfayı yenileyip tekrar dene; girdiğin bilgileri yeniden yazman gerekebilir.'],
    405 => ['Bu işlem desteklenmiyor.', 'İstek bu adreste kullanılamıyor.'],
    403 => ['Bu sayfaya erişim yetkin yok.', 'Hesabının rolü bu işlem için yeterli değil. Gerekirse bir yöneticiyle görüş.'],
];
[$heading, $text] = $messages[$status] ?? ['Beklenmeyen bir hata oluştu.', 'Sorun kaydedildi. Birazdan tekrar dene; sürerse kulüple iletişime geç.'];
?>
<main class="list-header notfound">
  <div class="container">
    <span class="eyebrow">Hata <?= (int) $status ?></span>
    <h1><?= e($heading) ?></h1>
    <p><?= e($text) ?></p>
    <div class="notfound-actions">
      <a href="<?= e(url('home')) ?>" class="btn btn-primary">Anasayfa</a>
      <a href="<?= e(url('iletisim')) ?>" class="btn btn-ghost">İletişim</a>
    </div>
  </div>
</main>
