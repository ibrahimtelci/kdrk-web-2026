<?php
/** Tek kayıt kaynağından sosyal gönderi bilgileri; hassas başvuru verisi içermez. */
$share = [
    'title' => $a['title'], 'summary' => $a['summary'] ?? '',
    'date' => tr_range($a['starts_on'], $a['ends_on']),
    'startDate' => $a['starts_on'], 'endDate' => $a['ends_on'] ?? null,
    'day' => substr($a['starts_on'], 8, 2),
    'month' => tr_month($a['starts_on']), 'year' => substr($a['starts_on'], 0, 4),
    'location' => $a['location'], 'category' => $a['category_text'],
    'difficulty' => trim(($a['difficulty_level'] ?? '') . ' · ' . $a['difficulty_text'], ' ·'),
    'status' => $a['status_text'], 'statusKey' => $a['status'], 'url' => $a['url'],
    'image' => img($a['cover_image'], 1600),
    'logo' => asset('kdrk-logo-social.png'),
    'topo' => asset('topo.svg'),
    'difficultyStep' => $a['difficulty_step'] ?? null,
    'score' => $a['score'] ?? null,
    'difficultyName' => $a['difficulty_text'], 'difficultyClass' => $a['difficulty_class'],
    'lat' => !empty($a['has_map']) ? (float) $a['lat'] : null, 'lng' => !empty($a['has_map']) ? (float) $a['lng'] : null,
    'distance' => (float) ($a['distance_km'] ?? 0) > 0 ? rtrim(rtrim(number_format((float) $a['distance_km'], 1, ',', ''), '0'), ',') . ' km' : 'Belirtilmedi',
    'guides' => array_map(static fn (array $p): array => ['name' => $p['name'], 'phone' => $p['phone']], $a['guides'] ?? []),
    'published' => (bool) ($a['is_published'] ?? true),
    'meeting' => $a['meeting_text'] ?? '',
];
$data = e(json_encode($share, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
?>
<?php if (!empty($icon)): ?>
<button type="button" class="social-share-trigger is-icon" data-social-share="<?= $data ?>" aria-label="Faaliyeti paylaş" title="Paylaş" hidden><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/></svg></button>
<?php else: ?>
<button type="button" class="social-share-trigger" data-social-share="<?= $data ?>" aria-label="<?= e($a['title']) ?> için gönderi hazırla" hidden><?= e($label ?? 'Paylaş') ?></button>
<?php endif ?>
