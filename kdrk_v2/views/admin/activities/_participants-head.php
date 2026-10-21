<?php
/**
 * Başvurular ve Yoklama ekranlarının ortak şeridi: faaliyet bağlamı, iki ekran arası geçiş, dışa aktarma.
 * Sayfa adı üst çubukta olduğu için burada tekrar edilmez (UI_STANDARDS_V2 §Yerleşim 1).
 * @var array  $a       faaliyet (Activity::decorate)
 * @var array  $head    ApplicationController::headCounts()
 * @var string $current basvurular | yoklama
 */
$S = require dirname(__DIR__) . '/_status.php';
[$stLabel, $stColor] = $S['activity'][$a['status']];
$fee = $a['fee'] === null ? null : ((float) $a['fee'] > 0 ? tr_money((float) $a['fee']) : 'Ücretsiz');
$quota = (int) $a['quota'];
$taken = (int) $a['taken'];
$approved = $head['approved'];
$nav = [
    'basvurular' => ['Başvurular', 'users', url('admin.applications', ['id' => $a['id']]), (string) $head['total'] . ($head['pending'] ? ' · ' . $head['pending'] . ' yeni' : '')],
    'yoklama'    => ['Yoklama', 'checklist', url('admin.applications.checklist', ['id' => $a['id']]), $head['came'] . ' / ' . $approved],
];
?>
<section class="pa-head" aria-label="Faaliyet özeti">
  <div class="pa-head-top">
    <a class="back-link" href="<?= e(url('admin.activities.edit', ['id' => $a['id']])) ?>"><?= icon('back', 16) ?>Faaliyete dön</a>
    <span class="chip <?= $stColor ?> dot"><?= e($stLabel) ?></span>
  </div>
  <dl class="pa-meta">
    <div><dt>Tarih</dt><dd class="mono"><?= e(tr_range($a['starts_on'], $a['ends_on'])) ?></dd></div>
<?php if (trim((string) $a['location']) !== ''): ?>
    <div><dt>Yer</dt><dd><?= e($a['location']) ?></dd></div>
<?php endif ?>
<?php if ($fee !== null): ?>
    <div><dt>Ücret</dt><dd><?= e($fee) ?></dd></div>
<?php endif ?>
<?php if ($quota > 0): ?>
    <div class="pa-quota"><dt>Kontenjan</dt><dd><b><?= $taken ?></b> / <?= $quota ?><meter min="0" max="<?= $quota ?>" value="<?= min($taken, $quota) ?>" aria-label="Kontenjan doluluğu: <?= $quota ?> kişiden <?= $taken ?>"></meter></dd></div>
<?php endif ?>
  </dl>
  <div class="pa-switch">
    <nav class="seg pa-nav" aria-label="Katılımcı ekranları">
<?php foreach ($nav as $key => [$label, $ic, $href, $count]): $on = $key === $current; ?>
      <a href="<?= e($href) ?>" class="<?= $on ? 'is-active' : '' ?>"<?= $on ? ' aria-current="page"' : '' ?>><?= icon($ic, 16) ?><span><?= e($label) ?></span><span class="pa-count mono"><?= e($count) ?></span></a>
<?php endforeach ?>
    </nav>
<?php if ($approved): ?>
    <details class="export-menu" data-export-menu>
      <summary class="btn btn-ghost btn-sm"><?= icon('download', 14) ?>Dışa aktar<span class="export-chev" aria-hidden="true"><?= icon('arrowdown', 14) ?></span></summary>
      <div class="export-panel">
<?php foreach (['katilim' => ['Katılım listesi', 'print'], 'tasima' => ['Taşımacılık listesi', 'bus']] as $kind => [$label, $ic]): ?>
        <div class="export-group" role="group" aria-label="<?= e($label) ?>">
          <span class="export-label"><?= icon($ic, 14) ?><?= e($label) ?></span>
          <a class="export-item" href="<?= e(url('admin.applications.print', ['id' => $a['id'], 'kind' => $kind, 'bicim' => 'pdf'])) ?>" target="_blank" rel="noopener"><span class="export-ico is-pdf"><?= icon('file-pdf', 18) ?></span><span><b>PDF</b><small>Yazdır ya da PDF olarak kaydet</small></span></a>
          <a class="export-item" href="<?= e(url('admin.applications.print', ['id' => $a['id'], 'kind' => $kind, 'bicim' => 'excel'])) ?>"><span class="export-ico is-xls"><?= icon('file-sheet', 18) ?></span><span><b>Excel</b><small>Tablo olarak indir (.csv)</small></span></a>
        </div>
<?php endforeach ?>
      </div>
    </details>
<?php endif ?>
  </div>
</section>
