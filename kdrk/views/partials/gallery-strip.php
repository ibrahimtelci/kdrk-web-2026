<?php
/**
 * Disiplin sayfasındaki fotoğraf şeridi: o kategorideki en yeni albümlerin kapakları (Gallery::strip).
 * @var array $strip [['src', 'url', 'title'], …]
 */
?>
<?php if ($strip): ?>
<div class="disc-strip reveal">
<?php foreach ($strip as $s): ?>
  <a href="<?= e($s['url']) ?>" aria-label="<?= e($s['title']) ?> albümü"><div><?= img_tag($s['src'], '(min-width: 1280px) 310px, (min-width: 701px) 25vw, 50vw', 400, '') ?></div></a>
<?php endforeach ?>
</div>
<?php else: ?>
<p class="disc-strip-empty reveal">Bu disiplinden fotoğraflar yüklendikçe burada görünecek.</p>
<?php endif ?>
