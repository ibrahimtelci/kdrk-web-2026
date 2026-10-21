<?php
/**
 * Panel açılır pencere iskeleti (?pencere=1): kenar çubuğu ve üst çubuk yok, yalnız form.
 * Ana sayfadaki <dialog> içindeki iframe'de açılır (panel.js). Kayıt bitince admin/_done ana sayfayı yönlendirir.
 * @var string $content
 * @var string $title
 * @var array|null $flash
 */
?>
<!doctype html>
<html lang="tr" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · KDRK Yönetim</title>
  <script>try{var t=localStorage.getItem('kdrk-admin-theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('admin/panel.css')) ?>">
<?= $this->yield('head') ?>
</head>
<body class="is-modal" data-modal-page>
<main class="content modal-content">
<?php if ($flash): ?>
  <div class="flash flash-<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>"><?= icon($flash['type'] === 'error' ? 'x' : 'check') ?><span><?= e($flash['message']) ?></span></div>
<?php endif ?>
<?= $content ?>
</main>
<script src="<?= e(asset('admin/panel.js')) ?>" defer></script>
</body>
</html>
