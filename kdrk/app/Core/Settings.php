<?php
declare(strict_types=1);

namespace App\Core;

/** settings tablosu: kulüp rakamları, bakım modu, KVKK saklama süresi, iletişim bilgileri. */
final class Settings
{
    private static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            foreach (App::db()->all('SELECT `key`, `value` FROM settings') as $row) {
                self::$cache[$row['key']] = $row['value'];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return self::all()[$key] ?? $default;
    }

    public static function int(string $key, int $default = 0): int
    {
        $v = self::get($key);
        return $v === null ? $default : (int) $v;
    }

    public static function bool(string $key): bool
    {
        return in_array(self::get($key), ['1', 'true'], true);
    }

    public static function set(string $key, string $value): void
    {
        App::db()->run(
            'INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)',
            [$key, $value]
        );
        self::$cache = null;
    }

    /**
     * Kulüp rakamları — tek kaynak (CLAUDE.md §9). Yıl, kuruluştan hesaplanır.
     * @return array{kurulus:int, zirve:int, yuruyus:int, aramaKurtarma:int, yil:int}
     */
    public static function stats(): array
    {
        $kurulus = self::int('stat_kurulus', 1998);
        return [
            'kurulus'       => $kurulus,
            'zirve'         => self::int('stat_zirve'),
            'yuruyus'       => self::int('stat_yuruyus'),
            'aramaKurtarma' => self::int('stat_arama_kurtarma'),
            'yil'           => (int) date('Y') - $kurulus,
        ];
    }
}
