<?php
/**
 * @var string|null $error
 * @var string $email
 * @var string $next
 */
?>
<!doctype html>
<html lang="tr" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>Giriş · KDRK Yönetim</title>
  <link rel="icon" type="image/png" href="<?= e(asset('kdrk-logo-2026-sm.png')) ?>">
  <script>try{var t=localStorage.getItem('kdrk-admin-theme');if(t)document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(asset('admin/admin.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('admin/panel.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('admin/components.css')) ?>">
</head>
<body>
<main class="login-wrap">
  <div class="login-topo" aria-hidden="true"></div>
  <div class="login-card">
    <div class="login-brand"><span class="login-mark"><img src="<?= e(asset('kdrk-logo-2026-sm.png')) ?>" alt=""></span><div><div class="lb-name">KDRK</div><div class="lb-sub">Yönetim Paneli</div></div></div>
    <h1>Giriş Yap</h1>
    <p class="login-hint">Kulüp tarafından verilen hesabınla giriş yap.</p>
<?php if ($error): ?>
    <div class="flash flash-error" role="alert"><?= icon('x') ?><span><?= e($error) ?></span></div>
<?php endif ?>
    <form method="post" action="<?= e(url('admin.login.post')) ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="next" value="<?= e($next) ?>">
      <div class="field"><label for="lg-email">E-posta</label><input id="lg-email" name="email" type="email" value="<?= e($email) ?>" autocomplete="username" inputmode="email" autocapitalize="off" required<?= $email === '' ? ' autofocus' : '' ?>></div>
      <div class="field"><label for="lg-pass">Parola</label><input id="lg-pass" name="password" type="password" autocomplete="current-password" required<?= $email !== '' ? ' autofocus' : '' ?>></div>
      <button class="btn btn-primary btn-block" type="submit">Giriş Yap</button>
    </form>
    <p class="login-foot muted">Parolanı unuttuysan kulüp yöneticisinden sıfırlamasını iste.</p>
  </div>
</main>
</body>
</html>
