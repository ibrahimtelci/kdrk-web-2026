<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\App;
final class GuideContact
{
    private static ?array $cache = null;
    public static function all(): array
    {
        return self::$cache ??= array_column(App::db()->all('SELECT id, name, phone, role, is_active FROM users WHERE phone IS NOT NULL ORDER BY name, id'), null, 'id');
    }
    public static function selected(?int $first, ?int $second): array
    {
        if (!$first && !$second) return [];
        $all = self::all(); $out = [];
        foreach (array_unique([$first, $second]) as $id) if ($id && isset($all[$id])) $out[] = array_intersect_key($all[$id], array_flip(['id', 'name', 'phone']));
        return $out;
    }
    public static function normalizePhone(string $raw): ?string
    {
        if (preg_match('/[^\d\s+().-]/u', $raw)) return null;
        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 10 && str_starts_with($digits, '5')) $digits = '90' . $digits;
        elseif (strlen($digits) === 11 && str_starts_with($digits, '05')) $digits = '90' . substr($digits, 1);
        return preg_match('/^905\d{9}$/', $digits) ? '+' . $digits : null;
    }
}
