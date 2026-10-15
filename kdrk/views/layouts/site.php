<?php
/**
 * Site iskeleti (CLAUDE.md §3): system.css → nav-footer.css → sayfa CSS'i; nav, drawer, içerik, footer, site.js.
 * @var string $content
 * @var array  $page  title, description, css[], navLight, nav, current, currentParams, robots
 */
$page = ($page ?? []) + ['title' => null, 'description' => null, 'css' => [], 'navLight' => false, 'nav' => null, 'current' => null, 'currentParams' => [], 'robots' => null];
$fullName = 'Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü';
$title = $page['title'] ? $page['title'] . ' · KDRK' : 'KDRK · ' . $fullName;
$canonical = $page['current'] ? url($page['current'], $page['currentParams']) : null;
?>
<!doctype html>
<html lang="tr" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title) ?></title>
<?php if ($page['description']): ?>
  <meta name="description" content="<?= e($page['description']) ?>">
<?php endif ?>
<?php if ($page['robots']): ?>
  <meta name="robots" content="<?= e($page['robots']) ?>">
<?php endif ?>
<?php if ($canonical): ?>
  <link rel="canonical" href="<?= e($canonical) ?>">
<?php endif ?>
  <meta property="og:site_name" content="KDRK">
  <meta property="og:title" content="<?= e($page['title'] ?? 'KDRK') ?>">
<?php if ($page['description']): ?>
  <meta property="og:description" content="<?= e($page['description']) ?>">
<?php endif ?>
  <meta property="og:locale" content="tr_TR">
  <link rel="icon" type="image/png" href="<?= e(asset('kdrk-logo-2026-sm.png')) ?>">
  <script>try{var t=localStorage.getItem('kdrk-theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= e(asset('system.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('nav-footer.css')) ?>">
<?php foreach ($page['css'] as $css): ?>
  <link rel="stylesheet" href="<?= e(asset($css)) ?>">
<?php endforeach ?>
</head>
<body>

<?= $this->partial('partials/nav') ?>

<?= $content ?>

<?= $this->partial('partials/footer') ?>

<script>window.KDRK_STATS = <?= json_encode(\App\Core\Settings::stats(), JSON_HEX_TAG) ?>;</script>
<script src="<?= e(asset('site.js')) ?>"></script>
<?= $this->yield('scripts') ?>
</body>
</html>
