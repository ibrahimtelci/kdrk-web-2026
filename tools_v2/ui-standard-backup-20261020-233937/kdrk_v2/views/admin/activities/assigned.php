<?php /** @var array $a */ ?>
<div class="page-head"><a class="back-link" href="<?= e(url('admin.activities')) ?>"><?= icon('back', 16) ?>Faaliyetlerim</a></div>
<section class="panel"><div class="panel-head"><h2><?= e($a['title']) ?></h2></div><div class="panel-body">
<p><?= e(tr_range($a['starts_on'], $a['ends_on'])) ?> · <?= e($a['location']) ?></p>
<p><?= e($a['summary']) ?></p>
<p class="f-hint">Bu faaliyette görevlisin. Katılımcıları inceleyebilir, başvuruları onaylayabilir veya yedek listeye alabilirsin.</p>
<div class="page-head-actions"><a class="btn btn-primary" href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>"><?= icon('users', 16) ?>Katılımcıları yönet</a><a class="btn btn-ghost" href="<?= e($a['url']) ?>" target="_blank" rel="noopener">Faaliyeti sitede gör</a></div>
</div></section>
