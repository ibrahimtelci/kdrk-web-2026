<?php if (($a['difficulty_step'] ?? null) !== null): ?>
<span class="difficulty-meter" role="img" aria-label="<?= e($a['difficulty_text']) ?> · <?= (int) $a['difficulty_step'] ?> / 5 basamak">
<?php for ($step = 1; $step <= 5; $step++): ?><svg viewBox="0 0 24 20" aria-hidden="true"<?= $step <= $a['difficulty_step'] ? ' class="is-filled"' : '' ?>><path class="peak" d="M12 1.5 22.5 18.5H1.5Z"/><path class="snow" d="M12 1.5l3.7 6-1.8-.9L12 8.4l-1.9-1.8-1.8.9Z"/></svg><?php endfor ?>
</span>
<?php endif ?>
