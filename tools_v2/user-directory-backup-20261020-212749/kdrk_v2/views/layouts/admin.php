<?php
/**
 * Panel iskeleti. Panel kendi token setini kullanır (assets/admin/admin.css) — site CSS'i bağlanmaz (CLAUDE.md §11).
 * @var string $content
 * @var string $title
 * @var string $active
 * @var array|null $flash ['type' => ok|error, 'message' => …]
 */
use App\Core\App;
use App\Core\Auth;

$u = Auth::user();
$db = App::db();
$pending = (int) $db->value("SELECT COUNT(*) FROM applications WHERE status = 'pending'");
$unread = (int) $db->value('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$modules = [
    'Genel'    => [['dashboard', 'Ana ekran', 'admin.dashboard', ['admin', 'editor', 'viewer'], 0]],
    'İçerik'   => [
        ['activities', 'Faaliyetler', 'admin.activities', ['admin', 'editor', 'viewer'], $pending],
        ['gallery', 'Galeri', 'admin.gallery', ['admin', 'editor', 'viewer'], 0],
        ['news', 'Duyurular', 'admin.news', ['admin', 'editor', 'viewer'], 0],
        ['banner', 'Ana sayfa görselleri', 'admin.banners', ['admin', 'editor', 'viewer'], 0],
    ],
    'İletişim' => [['contacts', 'İletişim Bilgileri', 'admin.contacts', ['admin', 'editor', 'viewer'], 0], ['messages', 'Mesajlar', 'admin.messages', ['admin', 'editor'], $unread]],
    'Sistem'   => [
        ['users', 'Kullanıcılar', 'admin.users', ['admin'], 0],
        ['settings', 'Ayarlar', 'admin.settings', ['admin'], 0],
        ['account', 'Hesabım', 'admin.account', ['admin', 'editor', 'viewer'], 0],
    ],
];
?>
<!doctype html>
<html lang="tr" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · KDRK Yönetim</title>
  <link rel="icon" type="image/png" href="<?= e(asset('kdrk-logo-2026-sm.png')) ?>">
  <script>try{var t=localStorage.getItem('kdrk-admin-theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('admin/panel.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('admin/quick.css')) ?>">
<?= $this->yield('head') ?>
  <link rel="stylesheet" href="<?= e(asset('social-share.css')) ?>">
</head>
<body>
<div class="app is-auth">
  <aside class="sidebar" id="sidebar" data-sidebar>
    <div class="sb-brand"><span class="sb-mark"><img src="<?= e(asset('kdrk-logo-2026-sm.png')) ?>" alt=""></span><div><div class="sb-name">KDRK</div><div class="sb-tag">Yönetim · V2</div></div></div>
    <nav class="sb-nav" aria-label="Panel menüsü">
<?php foreach ($modules as $group => $items):
    $items = array_filter($items, static fn ($m) => in_array($u['role'], $m[3], true));
    if (!$items) {
        continue;
    } ?>
      <div class="sb-section-label"><?= e($group) ?></div>
<?php foreach ($items as [$id, $label, $route, , $badge]): ?>
      <a class="sb-link<?= $active === $id ? ' is-active' : '' ?>" href="<?= e(url($route)) ?>"<?= $active === $id ? ' aria-current="page"' : '' ?>><?= icon($id) ?><span><?= e($label) ?></span><?php if ($badge): ?><span class="sb-badge" aria-label="<?= $badge ?> yeni"><?= $badge ?></span><?php endif ?></a>
<?php endforeach ?>
<?php endforeach ?>
    </nav>
    <div class="sb-foot">
      <span class="sb-avatar" aria-hidden="true"><?= e(mb_substr($u['name'], 0, 1)) ?></span>
      <div class="sb-user"><div class="su-name"><?= e($u['name']) ?></div><div class="su-role"><?= e(Auth::ROLES[$u['role']]) ?></div></div>
      <form method="post" action="<?= e(url('admin.logout')) ?>"><?= csrf_field() ?><button class="sb-logout" type="submit" aria-label="Çıkış yap" title="Çıkış yap"><?= icon('logout') ?></button></form>
    </div>
  </aside>
  <div class="sb-scrim" data-sidebar-scrim hidden></div>

  <div class="main">
    <header class="topbar">
      <button class="tb-btn menu-toggle" data-menu aria-label="Menüyü aç" aria-controls="sidebar" aria-expanded="false"><?= icon('menu') ?></button>
      <h1 class="tb-title"><?= e($title) ?></h1>
      <div class="tb-spacer"></div>
      <a class="tb-view-site" href="<?= e(url('home')) ?>" target="_blank" rel="noopener"><?= icon('ext') ?><span>Siteyi Gör</span></a>
      <button class="tb-btn" data-theme-btn aria-label="Temayı değiştir"><?= icon('sun') ?></button>
    </header>
    <main class="content">
<?php if ($flash): ?>
      <div class="flash flash-<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>"><?= icon($flash['type'] === 'error' ? 'x' : 'check') ?><span><?= e($flash['message']) ?></span></div>
<?php endif ?>
<?php if ($u['role'] === 'viewer'): ?>
      <div class="flash flash-info"><?= icon('eye') ?><span>İzleme yetkisiyle giriş yaptın; değişiklik yapamazsın.</span></div>
<?php endif ?>
<?= $content ?>
    </main>
  </div>
</div>
<script src="<?= e(asset('admin/panel.js')) ?>" defer></script>
<script src="<?= e(asset('admin/quick.js')) ?>" defer></script>
<script src="<?= e(asset('social-share.js')) ?>" defer></script>
<script src="<?= e(asset('admin/guide-contacts.js')) ?>" defer></script>
</body>
</html>
