<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root = $argv[1] ?? dirname(__DIR__, 2);
require $root . '/kdrk_v2/bootstrap.php';
use App\Core\App;
if (!App::isLocal() || App::config('db.name') !== 'kdrk_web_v2') throw new RuntimeException('Only local kdrk_web_v2 is allowed.');
$db = App::db();
$version = '009_user_directory';
if ($db->value('SELECT version FROM schema_migrations WHERE version = ?', [$version])) { echo "Migration already applied.\n"; exit; }
if (!$db->value("SELECT version FROM schema_migrations WHERE version = '008_activity_contacts'")) throw new RuntimeException('008_activity_contacts must be applied first.');
$sql = preg_replace('/^\s*--.*$/m', '', file_get_contents(__DIR__ . '/009_user_directory.sql'));
foreach (preg_split('/;\s*$/m', $sql) as $statement) {
    if (preg_match('/ADD CONSTRAINT (\w+)/', $statement, $match) && $db->value('SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?', ['activities', $match[1]])) continue;
    if (trim($statement) !== '') $db->pdo()->exec($statement);
}
$db->run('INSERT INTO schema_migrations (version) VALUES (?)', [$version]);
echo "User directory migration applied to local V2.\n";
