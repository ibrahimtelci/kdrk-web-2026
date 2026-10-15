<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Panel oturumu (CLAUDE.md §15).
 * - Parola: password_hash / password_verify
 * - Girişte session_regenerate_id
 * - 15 dakikada 5 hatalı denemeden sonra geçici kilit (e-posta ve IP bazında)
 * - 2 saat hareketsizlikte oturum kapanır
 */
final class Auth
{
    public const ROLES = ['admin' => 'Admin', 'editor' => 'Editör', 'viewer' => 'İzleme'];
    private const MAX_ATTEMPTS = 5;
    private const LOCK_MINUTES = 15;
    private const IDLE_SECONDS = 7200;

    private static ?array $user = null;
    private static bool $loaded = false;

    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }
        self::$loaded = true;
        $id = Session::get('user_id');
        if (!is_int($id)) {
            return null;
        }
        if (time() - (int) Session::get('last_seen', 0) > self::IDLE_SECONDS) {
            self::logout();
            return null;
        }
        Session::set('last_seen', time());
        self::$user = App::db()->one('SELECT id, name, email, role FROM users WHERE id = ? AND is_active = 1', [$id]);
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function is(string ...$roles): bool
    {
        $u = self::user();
        return $u !== null && in_array($u['role'], $roles, true);
    }

    /** İzleme rolü hiçbir şeyi değiştiremez. */
    public static function canEdit(): bool
    {
        return self::is('admin', 'editor');
    }

    /** TC kimlik ve sağlık notu yalnızca admin'e açılır. */
    public static function canSeeSensitive(): bool
    {
        return self::is('admin');
    }

    public static function ip(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
    }

    public static function isLocked(string $email): bool
    {
        $n = (int) App::db()->value(
            'SELECT COUNT(*) FROM login_attempts WHERE (email = ? OR ip = ?) AND attempted_at > NOW() - INTERVAL ? MINUTE',
            [mb_strtolower($email), self::ip(), self::LOCK_MINUTES]
        );
        return $n >= self::MAX_ATTEMPTS;
    }

    /** @return 'ok'|'invalid'|'locked' */
    public static function attempt(string $email, string $password): string
    {
        $email = mb_strtolower(trim($email));
        if (self::isLocked($email)) {
            return 'locked';
        }
        $u = App::db()->one('SELECT id, password_hash FROM users WHERE email = ? AND is_active = 1', [$email]);
        // Kullanıcı yokken de aynı sürede yanıt verilsin diye sahte hash ile doğrulama yapılır
        $hash = $u['password_hash'] ?? '$2y$12$' . str_repeat('a', 53);
        if (!password_verify($password, $hash) || $u === null) {
            App::db()->insert('login_attempts', ['email' => $email, 'ip' => self::ip()]);
            Audit::log('login_failed', 'user', null, ['email' => $email]);
            return 'invalid';
        }
        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
            App::db()->run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
        }
        Session::regenerate();
        Session::set('user_id', (int) $u['id']);
        Session::set('last_seen', time());
        self::$loaded = false;
        App::db()->run('UPDATE users SET last_login_at = NOW() WHERE id = ?', [$u['id']]);
        App::db()->run('DELETE FROM login_attempts WHERE email = ?', [$email]);
        Audit::log('login', 'user', (int) $u['id']);
        return 'ok';
    }

    public static function logout(): void
    {
        Session::forget('user_id');
        Session::forget('last_seen');
        Session::regenerate();
        self::$user = null;
    }
}
