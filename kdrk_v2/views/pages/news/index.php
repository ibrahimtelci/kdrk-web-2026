<?php
/**
 * Duyuru & Haberler listesi. Tür sekmeleri ve sayfalama linktir; JS kapalıyken de çalışır.
 * @var array       $items  News::decorate()
 * @var array       $counts tür => yayındaki haber sayısı
 * @var int         $total
 * @var int         $pages
 * @var int         $pageNo
 * @var string|null $type   seçili sekme (News::FILTERS anahtarı: duyuru|rapor) ya da null
 */
use App\Models\News;

$pageUrl = static fn (?string $t, int $p = 1): string => url('duyurular', array_filter(['tur' => $t, 'sayfa' => $p > 1 ? $p : null]));
$tabs = [null => ['Tümü', array_sum($counts)]];
foreach (News::FILTERS as $k => [$label]) {
    if ($counts[$k] > 0) {
        $tabs[$k] = [$label, $counts[$k]];
    }
}
?>
<section class="list-header" data-screen-label="01 Header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a>
      <span class="sep" aria-hidden="true">›</span>
      <span class="current" aria-current="page">Duyuru &amp; Haberler</span>
    </nav>
    <span class="eyebrow">Gündem</span>
    <h1>Duyuru &amp; Haberler</h1>
    <p>Başvuru duyuruları, kulüp haberleri ve tamamlanan faaliyetlerin raporları.</p>
<?php if (count($tabs) > 2): ?>
    <div class="header-meta">
      <nav class="list-tabs news-tabs" aria-label="Tür">
<?php foreach ($tabs as $k => [$label, $n]): $on = ($k === '' ? null : $k) === $type; ?>
        <a class="list-tab<?= $on ? ' is-active' : '' ?>" href="<?= e($pageUrl($k === '' ? null : $k)) ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= e($label) ?> <span class="count"><?= $n ?></span></a>
<?php endforeach ?>
      </nav>
    </div>
<?php endif ?>
  </div>
</section>

<section class="news-listing" data-screen-label="02 Liste">
  <div class="container">
<?php if ($items): ?>
    <div class="listing-meta">
      <div class="count-line"><strong><?= $total ?></strong> <?= e(tr_upper($type ? News::FILTERS[$type][0] : 'Duyuru, haber ve rapor')) ?><?= $pages > 1 ? ' · SAYFA ' . $pageNo . ' / ' . $pages : '' ?></div>
    </div>
    <div class="activities-grid">
<?php foreach ($items as $n): ?>
      <?= $this->partial('partials/news-card', ['n' => $n]) ?>
<?php endforeach ?>
    </div>
<?php else: ?>
    <div class="empty-state">
      <span class="stamp">0 SONUÇ</span>
      <h2>Henüz duyuru veya haber yok.</h2>
      <p>Yeni duyurular yayınlandıkça burada görünecek.</p>
      <a class="btn btn-primary" href="<?= e(url('faaliyetler')) ?>">Faaliyet Takvimine Git</a>
    </div>
<?php endif ?>

<?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Sayfalar">
<?php if ($pageNo > 1): ?>
      <a class="page-btn" href="<?= e($pageUrl($type, $pageNo - 1)) ?>" aria-label="Önceki sayfa" rel="prev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></a>
<?php else: ?>
      <span class="page-btn is-disabled" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></span>
<?php endif ?>
      <div class="pagination-pages">
<?php for ($p = 1; $p <= $pages; $p++): ?>
        <a class="page-btn<?= $p === $pageNo ? ' is-active' : '' ?>" href="<?= e($pageUrl($type, $p)) ?>"<?= $p === $pageNo ? ' aria-current="page"' : '' ?>><?= $p ?></a>
<?php endfor ?>
      </div>
<?php if ($pageNo < $pages): ?>
      <a class="page-btn" href="<?= e($pageUrl($type, $pageNo + 1)) ?>" aria-label="Sonraki sayfa" rel="next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></a>
<?php else: ?>
      <span class="page-btn is-disabled" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></span>
<?php endif ?>
      <span class="page-info">Sayfa <?= $pageNo ?> / <?= $pages ?></span>
    </nav>
<?php endif ?>
  </div>
</section>
