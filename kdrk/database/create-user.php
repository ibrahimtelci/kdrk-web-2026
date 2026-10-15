<?php
declare(strict_types=1);

/*
 * Panel kullanıcısı oluşturur / parolasını sıfırlar (ilk admin için).
 *   php kdrk/database/create-user.php <e-posta> "<Ad Soyad>" [admin|editor|viewer]
 * Parola rastgele üretilir ve YALNIZCA bir kez ekrana yazılır. İlk girişte "Hesabım"dan değiştirilmeli.
 * Aynı e-posta varsa parolası sıfırlanır ve hesap aktifleştirilir.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require dirname(__DIR__) . '/bootstrap.php';

use App\Core\App;
use App\Core\Auth;

[$self, $email, $name, $role] = array_pad($argv, 4, null);
$role ??= 'admin';
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$name || !isset(Auth::ROLES[$role])) {
    fwrite(STDERR, "Kullanım: php kdrk/database/create-user.php <e-posta> \"<Ad Soyad>\" [admin|editor|viewer]\n");
    exit(1);
}
$email = mb_strtolower($email);
// Karışması kolay karakterler (0/O, 1/l/I) olmadan okunabilir parola
$alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$password = '';
for ($i = 0; $i < 16; $i++) {
    $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}
$password = implode('-', str_split($password, 4));

$db = App::db();
$id = $db->value('SELECT id FROM users WHERE email = ?', [$email]);
if ($id) {
    $db->run('UPDATE users SET name = ?, role = ?, is_active = 1, password_hash = ? WHERE id = ?', [$name, $role, password_hash($password, PASSWORD_DEFAULT), $id]);
    echo "Var olan kullanıcının parolası sıfırlandı.\n";
} else {
    $db->insert('users', ['name' => $name, 'email' => $email, 'role' => $role, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    echo "Kullanıcı oluşturuldu.\n";
}
echo "  E-posta : $email\n  Rol     : " . Auth::ROLES[$role] . "\n  Parola  : $password\n";
echo "Bu parola bir daha gösterilmez. İlk girişten sonra Hesabım sayfasından değiştir.\n";
