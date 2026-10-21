<?php
declare(strict_types=1);

/*
 * KVKK saklama süresi (CLAUDE.md §15). cPanel cron görevi olarak günde bir çalışır:
 *   php /home/rizekdrk/kdrk-yeni/kdrk/cron/kvkk-purge.php
 * Faaliyetin bitişinden settings.kvkk_retention_days gün geçen başvurularda TC kimlik no
 * ve sağlık notu silinir; ad, iletişim ve katılım kaydı kalır.
 * --dry-run: hiçbir şey silmeden kaç kaydın etkileneceğini yazar.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\App;
use App\Core\Audit;
use App\Core\Settings;

$dryRun = in_array('--dry-run', $argv, true);
$days = max(30, Settings::int('kvkk_retention_days', 365));

$where = 'ap.purged_at IS NULL
    AND (ap.tc_enc IS NOT NULL OR ap.health_enc IS NOT NULL)
    AND COALESCE(ac.ends_on, ac.starts_on) < CURDATE() - INTERVAL ? DAY';

$db = App::db();
$count = (int) $db->value("SELECT COUNT(*) FROM applications ap JOIN activities ac ON ac.id = ap.activity_id WHERE $where", [$days]);

if ($dryRun || $count === 0) {
    echo ($dryRun ? '[deneme] ' : '') . "Saklama süresi $days gün; silinecek başvuru: $count\n";
    exit(0);
}

$purged = $db->run(
    "UPDATE applications ap JOIN activities ac ON ac.id = ap.activity_id
        SET ap.tc_enc = NULL, ap.health_enc = NULL, ap.purged_at = NOW()
      WHERE $where",
    [$days]
)->rowCount();

Audit::log('purge', 'applications', null, ['count' => $purged, 'retention_days' => $days]);
echo "Saklama süresi $days gün; hassas alanları silinen başvuru: $purged\n";
