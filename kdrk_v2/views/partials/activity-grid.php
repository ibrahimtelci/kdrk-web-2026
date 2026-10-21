<?php
/**
 * Disiplin sayfalarındaki "yaklaşan faaliyetler" ızgarası.
 * @var array $activities
 * @var string|null $category
 */
?>
<?php if ($activities): ?>
<div class="activities-grid">
  <?php foreach ($activities as $a): ?>
    <?= $this->partial('partials/activity-card', ['a' => $a]) ?>
  <?php endforeach ?>
</div>
<?php else: ?>
<div class="activities-empty reveal">
  <p>Bu disiplinde şu an başvuruya açık faaliyet yok. Geçmiş faaliyetlere ve raporlara göz atabilirsin.</p>
  <a href="<?= e(url('faaliyetler', array_filter(['tab' => 'gecmis', 'cat' => $category ?? null]))) ?>" class="btn-text">Geçmiş faaliyetler <span class="arrow" aria-hidden="true">→</span></a>
</div>
<?php endif ?>
