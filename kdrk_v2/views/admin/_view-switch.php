<?php
/** Ortak Kart/Tablo seçici. Bağlantıdır; JS sayfayı yenilemeden değiştirir ve tercihi çereze yazar.
 * @var string $key   liste anahtarı (çerez: kdrk_view_<key>), aynı sayfadaki [data-view-key] ile eşleşir
 * @var string $current  tablo | kart ($view adı şablon motoruna ayrılmıştır)
 */
$href = static function (string $v): string {
    $query = $_GET;
    $query['gorunum'] = $v;
    return strtok((string) ($_SERVER['REQUEST_URI'] ?? ''), '?') . '?' . http_build_query($query);
};
?>
<div class="ui-view-switch" role="group" aria-label="Görünüm" data-view-switch="<?= e($key) ?>">
  <a class="btn btn-ghost" href="<?= e($href('tablo')) ?>" data-view="tablo"<?= $current === 'tablo' ? ' aria-current="true"' : '' ?>><?= icon('view-table', 16) ?><span>Tablo</span></a>
  <a class="btn btn-ghost" href="<?= e($href('kart')) ?>" data-view="kart"<?= $current === 'kart' ? ' aria-current="true"' : '' ?>><?= icon('view-grid', 16) ?><span>Kart</span></a>
</div>
