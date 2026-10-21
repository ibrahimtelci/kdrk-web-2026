<?php // One icon registry is used by PHP controls and client-created tabs. ?>
<script type="application/json" id="kdrk-ui-icons"><?= json_encode(require KDRK_ROOT . '/app/admin-icons.php', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script src="<?= e(asset('admin/ui.js')) ?>" defer></script>
