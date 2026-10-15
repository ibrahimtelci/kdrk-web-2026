<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\App;

/** Anasayfa slaytları (CLAUDE.md §4): en fazla 3, tarihi geçen kendiliğinden kalkar. */
final class Banner
{
    public const MAX = 3;

    private const VISIBLE = 'is_active = 1 AND (show_from IS NULL OR show_from <= CURDATE()) AND (show_until IS NULL OR show_until >= CURDATE())';

    public static function visible(): array
    {
        return App::db()->all('SELECT * FROM banners WHERE ' . self::VISIBLE . ' ORDER BY sort, id LIMIT ' . self::MAX);
    }

    /** Panel: hepsi, sitedeki sıraya göre; her satıra "şu an sitede mi" bilgisi eklenir. */
    public static function forPanel(): array
    {
        $rows = App::db()->all('SELECT b.*, (' . self::VISIBLE . ') AS live FROM banners b ORDER BY sort, id');
        $shown = 0;
        foreach ($rows as &$r) {
            $r['on_site'] = $r['live'] && $shown < self::MAX;
            $shown += (int) $r['on_site'];
            $r['state'] = match (true) {
                !$r['is_active'] => 'Kapalı',
                $r['show_from'] && $r['show_from'] > date('Y-m-d') => 'Zamanlandı',
                $r['show_until'] && $r['show_until'] < date('Y-m-d') => 'Süresi doldu',
                !$r['on_site'] => 'Sırada (ilk 3 dışında)',
                default => 'Sitede',
            };
        }
        return $rows;
    }
}
