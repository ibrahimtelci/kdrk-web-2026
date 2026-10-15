<?php
declare(strict_types=1);

/*
 * Sunucuda Terminal/SSH yokken phpMyAdmin ile kurulum için SQL üretir. YEREL bilgisayarda çalışır.
 *
 *   php tools/phpmyadmin-sql.php kurulum [--from=002]
 *       → release/kurulum-NNN.sql: migration'lar + schema_migrations kayıtları.
 *         İlk kurulumda parametresiz; sonraki yayınlarda yalnızca yeni migration'lar için --from=003 gibi.
 *
 *   php tools/phpmyadmin-sql.php admin <e-posta> "<Ad Soyad>"
 *       → release/admin.sql: ilk admin hesabı (parola şifrelenmiş hâlde).
 *         Parola YALNIZCA bu terminalde bir kez görünür. SQL içe aktarıldıktan sonra release/admin.sql silinir.
 *
 * release/ klasörü depoya girmez.
 */

if (PHP_SAPI !== 'cli') {
    exit;
}
$root = dirname(__DIR__);
$out = "$root/release";
if (!is_dir($out)) {
    mkdir($out, 0755, true);
}
$cmd = $argv[1] ?? '';

if ($cmd === 'kurulum') {
    $from = 0;
    foreach ($argv as $a) {
        if (preg_match('/^--from=(\d+)$/', $a, $m)) {
            $from = (int) $m[1];
        }
    }
    $files = glob("$root/kdrk/database/migrations/*.sql") ?: [];
    sort($files);
    $sql = "-- KDRK veritabanı kurulumu (phpMyAdmin > veritabanını seç > İçe Aktar)\n"
        . "-- Üretildi: " . date('Y-m-d H:i') . "\n"
        . "SET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci;\n\n"
        . "CREATE TABLE IF NOT EXISTS schema_migrations (\n  version VARCHAR(120) NOT NULL PRIMARY KEY,\n  applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;\n\n";
    $names = [];
    foreach ($files as $f) {
        $v = basename($f, '.sql');
        if ((int) $v < $from) {
            continue;
        }
        $names[] = $v;
        $sql .= "-- ============ $v ============\n" . trim((string) file_get_contents($f)) . "\n\n"
            . "INSERT INTO schema_migrations (version) VALUES ('$v');\n\n";
    }
    if (!$names) {
        fwrite(STDERR, "Bu aralıkta migration yok.\n");
        exit(1);
    }
    $file = "$out/kurulum-" . ($from ? sprintf('%03d', $from) : 'ilk') . '.sql';
    file_put_contents($file, $sql);
    echo "Yazıldı: release/" . basename($file) . "\nİçindeki migration'lar: " . implode(', ', $names) . "\n";
    exit(0);
}

if ($cmd === 'admin') {
    [, , $email, $name] = array_pad($argv, 4, null);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$name) {
        fwrite(STDERR, "Kullanım: php tools/phpmyadmin-sql.php admin <e-posta> \"<Ad Soyad>\"\n");
        exit(1);
    }
    // create-user.php ile aynı: karışan karakterler (0/O, 1/l/I) yok
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $password = '';
    for ($i = 0; $i < 16; $i++) {
        $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    $password = implode('-', str_split($password, 4));
    $q = static fn (string $s): string => "'" . str_replace(["\\", "'"], ["\\\\", "''"], $s) . "'";
    $sql = "-- KDRK ilk admin hesabı. İçe aktardıktan sonra bu dosyayı SİL.\nSET NAMES utf8mb4 COLLATE utf8mb4_turkish_ci;\n"
        . 'INSERT INTO users (name, email, role, password_hash) VALUES ('
        . $q($name) . ', ' . $q(mb_strtolower($email)) . ", 'admin', " . $q(password_hash($password, PASSWORD_DEFAULT)) . ");\n";
    file_put_contents("$out/admin.sql", $sql);
    echo "Yazıldı: release/admin.sql\n\n  E-posta : " . mb_strtolower($email) . "\n  Parola  : $password\n\n"
        . "Parolayı şimdi bir parola yöneticisine kaydet; bir daha gösterilmez.\n"
        . "İlk girişten sonra panelde Hesabım sayfasından değiştir. SQL'i içe aktardıktan sonra release/admin.sql dosyasını sil.\n";
    exit(0);
}

fwrite(STDERR, "Kullanım:\n  php tools/phpmyadmin-sql.php kurulum [--from=NNN]\n  php tools/phpmyadmin-sql.php admin <e-posta> \"<Ad Soyad>\"\n");
exit(1);
