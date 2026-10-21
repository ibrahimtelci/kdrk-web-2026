<?php
declare(strict_types=1);
namespace App\Models;
use App\Core\App;

final class GuideContact
{
    private static ?array $cache = null;
    public static function all(): array
    {
        // Liste sayfasındaki her kart için ayrı sorgu yapılmaz.
        return self::$cache ??= array_column(App::db()->all('SELECT id, name, phone FROM guide_contacts ORDER BY name, id'), null, 'id');
    }
    public static function selected(?int $first, ?int $second): array
    {
        if (!$first && !$second) { return []; }
        $all = self::all(); $out = [];
        foreach (array_unique([$first, $second]) as $id) {
            if ($id && isset($all[$id])) { $out[] = $all[$id]; }
        }
        return $out;
    }
}
