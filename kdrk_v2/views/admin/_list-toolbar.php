<?php
/** Shared list toolbar. Filters first, search second, Kart/Tablo last. No page-specific spacing.
 * @var array $tabs [{href,label,active,icon}]
 * @var string $filterLabel
 * @var array|null $search {action,value,label,hidden?}
 * @var array|null $viewSwitch {key,current} — Kart/Tablo seçici (_view-switch)
 */
?>
<div class="toolbar ui-toolbar">
<?php if (!empty($tabs)): ?>
  <nav class="seg ui-tabs" aria-label="<?= e($filterLabel) ?>">
<?php foreach ($tabs as $tab): ?>
    <a href="<?= e($tab['href']) ?>" class="<?= $tab['active'] ? 'is-active' : '' ?>"<?= $tab['active'] ? ' aria-current="page"' : '' ?>><?= icon($tab['icon'] ?? 'filter', 16) ?><span><?= e($tab['label']) ?></span></a>
<?php endforeach ?>
  </nav>
<?php endif ?>
<?php if (!empty($search)): ?>
  <form class="search-box" method="get" action="<?= e($search['action']) ?>" role="search">
<?php foreach ($search['hidden'] ?? [] as $name => $value): ?><input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>"><?php endforeach ?>
    <input type="search" name="q" value="<?= e($search['value']) ?>" placeholder="<?= e($search['label']) ?>" aria-label="<?= e($search['label']) ?>">
  </form>
<?php endif ?>
<?php if (!empty($viewSwitch)): ?>
  <?= $this->partial('admin/_view-switch', ['key' => $viewSwitch['key'], 'current' => $viewSwitch['current']]) ?>
<?php endif ?>
</div>
