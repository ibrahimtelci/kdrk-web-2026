<?php
declare(strict_types=1);

/*
 * Migration çalıştırıcı (CLAUDE.md §14, §18).
 *   php kdrk/database/migrate.php            → bekleyen migration'ları uygular
 *   php kdrk/database/migrate.php --status   → durumu gösterir
 *   php kdrk/database/migrate.php --seed     → yerel örnek veriyi yükler (YALNIZCA env=local)
 * Canlıda çalıştırmadan önce veritabanı yedeği alınır.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/bootstrap.php';

use App\Core\App;

$db = App::db();
$pdo = $db->pdo();
$args = array_slice($argv, 1);

$pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (
  version VARCHAR(120) NOT NULL PRIMARY KEY,
  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci');

$applied = array_flip($pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));
$files = glob(__DIR__ . '/migrations/*.sql') ?: [];
sort($files);

if (in_array('--status', $args, true)) {
    foreach ($files as $f) {
        $v = basename($f, '.sql');
        echo (isset($applied[$v]) ? '[uygulandı] ' : '[bekliyor]  ') . $v . PHP_EOL;
    }
    exit(0);
}

if (in_array('--seed', $args, true)) {
    if (!App::isLocal()) {
        fwrite(STDERR, "Örnek veri yalnızca env=local ortamında yüklenir.\n");
        exit(1);
    }
    require __DIR__ . '/seeds/demo.php';
    exit(0);
}

$count = 0;
foreach ($files as $f) {
    $v = basename($f, '.sql');
    if (isset($applied[$v])) {
        continue;
    }
    echo "→ $v … ";
    // MariaDB'de DDL örtük commit yapar; dosyadaki ifadeler sırayla çalıştırılır.
    foreach (split_sql((string) file_get_contents($f)) as $stmt) {
        $pdo->exec($stmt);
    }
    $db->run('INSERT INTO schema_migrations (version) VALUES (?)', [$v]);
    echo "tamam\n";
    $count++;
}
echo $count ? "$count migration uygulandı.\n" : "Bekleyen migration yok.\n";

/** Yorumları atar, ifadeleri satır sonundaki ; ile böler (dosyalarda prosedür/trigger yok). */
function split_sql(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $parts = preg_split('/;\s*$/m', (string) $sql);
    return array_values(array_filter(array_map('trim', $parts), static fn ($s) => $s !== ''));
}
