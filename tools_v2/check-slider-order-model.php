<?php
declare(strict_types=1);
require __DIR__ . '/../kdrk_v2/bootstrap.php';
use App\Core\Settings;
use App\Models\Banner;
$cache = new ReflectionProperty(Settings::class, 'cache');
$original = $cache->getValue();
$checks = 0;
function checkOrder(bool $ok, string $label): void {
    global $checks;
    if (!$ok) throw new RuntimeException($label);
    $checks++; echo "OK: $label\n";
}
try {
    $cache->setValue(null, ['slider_order' => '["news:10","fixed:5","activity:9","fixed:1"]', 'slider_disabled' => '["fixed:3"]']);
    $fixed = array_map(static fn ($id) => ['id' => $id], range(1, 6));
    $lineup = new ReflectionMethod(Banner::class, 'lineup');
    $slides = $lineup->invoke(null, $fixed, [], []);
    checkOrder(count($slides) === 5, 'more than three slides remain visible');
    checkOrder($slides[0]['source_id'] === 5, 'first promotion is freely movable');
    checkOrder(!in_array(3, array_column($slides, 'source_id')), 'passive key excluded from public slides');
    checkOrder(count($lineup->invoke(null, $fixed, [], [], true)) === 6, 'eligibility lookup includes disabled slides');
    $keys = Banner::orderedKeys(['fixed' => $fixed, 'activities' => [['id' => 9]], 'news' => [['id' => 10]]]);
    checkOrder(array_slice($keys, 0, 3) === ['news:10', 'fixed:5', 'activity:9'], 'all source types follow saved manual order');
    checkOrder(in_array('fixed:3', $keys, true), 'passive slide keeps its place in editor order');
    checkOrder(count($keys) === 8 && count(array_unique($keys)) === 8, 'editor has every configured slide exactly once');
    $cache->setValue(null, ['slider_order' => '[]', 'slider_disabled' => '[]']);
    checkOrder(count($lineup->invoke(null, $fixed, [], [])) === 6, 'all six active slides visible');
    echo "$checks order model checks passed.\n";
} finally { $cache->setValue(null, $original); }
