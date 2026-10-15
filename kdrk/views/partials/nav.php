<?php
/**
 * Nav + mobil drawer — tek şablon (CLAUDE.md §3).
 * Aktif bölüm: üst öğeye .is-current, hedef linke aria-current="page".
 * @var array $page
 */
$menu = require KDRK_ROOT . '/app/menu.php';
$group = $page['nav'] ?? null;
$current = $page['current'] ?? null;
$currentParams = $page['currentParams'] ?? [];

$isCurrent = static fn (array $item): bool =>
    $current !== null && $item[1] === $current && ($item[2] ?? []) == $currentParams;
$aria = static fn (bool $on): string => $on ? ' aria-current="page"' : '';
$chev = '<svg class="chev" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
$themeToggle = '<button class="theme-toggle" data-theme-toggle aria-label="Tema değiştir"><svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg><svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="display:none" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg></button>';
$brand = '<a href="' . e(url('home')) . '" class="brand" aria-label="KDRK Anasayfa"><span class="kdrk-mark"><img src="' . e(asset('kdrk-logo-2026-sm.png')) . '" alt=""></span>KDRK</a>';

$subLinks = static function (array $items) use ($isCurrent, $aria): string {
    $html = '';
    foreach ($items as $it) {
        $html .= $it[0] === '---'
            ? '<div class="sub-eyebrow">' . e($it[1]) . '</div>'
            : '<a href="' . e(url($it[1], $it[2] ?? [])) . '"' . $aria($isCurrent($it)) . '>' . e($it[0]) . '</a>';
    }
    return $html;
};
?>
<header class="nav<?= !empty($page['navLight']) ? ' nav-on-light' : '' ?>" data-nav>
  <div class="nav-inner">
    <div class="brand-cluster">
      <?= $brand ?>
      <img src="<?= e(asset('tdf-logo-sm.png')) ?>" class="nav-tdf" alt="Türkiye Dağcılık Federasyonu üyesi" width="44" height="44">
    </div>
    <nav aria-label="Ana menü">
      <ul class="nav-menu">
<?php foreach ($menu as $m): $on = $m['group'] === $group; ?>
<?php if (isset($m['items'])): ?>
        <li data-dropdown><button class="nav-link<?= $on ? ' is-current' : '' ?>" data-dropdown-trigger aria-haspopup="true" aria-expanded="false"><?= e($m['label']) ?><?= $chev ?></button><div class="dropdown"><?= $subLinks($m['items']) ?></div></li>
<?php else: ?>
        <li><a href="<?= e(url($m['route'])) ?>" class="nav-link<?= $on ? ' is-current' : '' ?>"<?= $aria($current === $m['route']) ?>><?= e($m['label']) ?></a></li>
<?php endif ?>
<?php endforeach ?>
      </ul>
    </nav>
    <div class="nav-right">
      <?= $themeToggle ?>
      <a href="<?= e(url('faaliyetler')) ?>" class="nav-cta nav-cta-text">Takvimi Gör<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
      <button class="burger" data-burger aria-label="Menüyü aç" aria-expanded="false" aria-controls="drawer"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
    </div>
  </div>
</header>

<aside class="drawer" id="drawer" data-drawer aria-hidden="true" aria-label="Mobil menü">
  <div class="drawer-head">
    <?= $brand ?>
    <button class="theme-toggle" data-drawer-close aria-label="Menüyü kapat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
  </div>
  <div class="drawer-theme-row"><span class="drawer-label">Tema</span><?= $themeToggle ?></div>
  <nav aria-label="Mobil menü">
<?php foreach ($menu as $m): ?>
<?php if (isset($m['items'])): ?>
    <details<?= $m['group'] === $group ? ' open' : '' ?>><summary><?= e($m['label']) ?><?= $chev ?></summary><div class="drawer-sub"><?= $subLinks($m['items']) ?></div></details>
<?php else: ?>
    <a href="<?= e(url($m['route'])) ?>"<?= $aria($current === $m['route']) ?>><?= e($m['label']) ?></a>
<?php endif ?>
<?php endforeach ?>
  </nav>
  <a href="<?= e(url('faaliyetler')) ?>" class="drawer-cta">Faaliyet Takvimini Gör →</a>
</aside>
