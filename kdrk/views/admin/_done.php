<?php
/**
 * Açılır pencerede kayıt bitti: ana sayfa hedef adrese gider (BaseController::done).
 * @var string $to
 */
?>
<!doctype html>
<html lang="tr">
<head><meta charset="utf-8"><meta name="robots" content="noindex, nofollow"><title>Kaydedildi</title></head>
<body>
<p>Kaydedildi. <a href="<?= e($to) ?>" target="_top">Devam et</a></p>
<script>
  (function () {
    var to = <?= json_encode($to, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    if (window.parent !== window) window.parent.postMessage({ kdrk: 'go', url: to }, location.origin);
    else location.replace(to);
  })();
</script>
</body>
</html>
