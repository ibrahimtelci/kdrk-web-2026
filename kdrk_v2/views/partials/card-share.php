<?php
/**
 * Kart görselinin sağ üstündeki genel paylaş simgesi (albüm, duyuru). Faaliyet kartı kendi gönderi
 * hazırlama butonunu (partials/social-share-button) kullanır; ikisi aynı yerde, aynı görünümde durur.
 * Tıklanınca cihazın paylaşım menüsü açılır; açılamazsa bağlantı kopyalanır (assets/site.js).
 * @var string $url   paylaşılacak adres
 * @var string $title paylaşım başlığı
 * @var string $label erişilebilir ad (ör. "Albümü paylaş")
 */
?>
<button type="button" class="card-share" data-share-url="<?= e($url) ?>" data-share-title="<?= e($title) ?>" aria-label="<?= e($label) ?>" title="Paylaş"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg><span class="card-share-tip" role="status"></span></button>
