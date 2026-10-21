<?php
/** Shared list heading: contextual help at left; create/preview actions at right.
 * @var string $description
 * @var string|null $createUrl
 * @var string $createLabel
 * @var string $modalSize
 */
?>
<div class="ui-list-heading">
  <p class="muted"><?= e($description) ?></p>
<?php if (!empty($createUrl)): ?>
  <div class="ui-heading-actions"><a class="btn btn-primary" href="<?= e($createUrl) ?>" data-modal-link data-modal-title="<?= e($createLabel) ?>"<?= !empty($modalSize) ? ' data-modal-size="' . e($modalSize) . '"' : '' ?>><?= icon('plus', 16) ?><?= e($createLabel) ?></a></div>
<?php endif ?>
</div>
