<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\App;

/**
 * Duyuru & Haber. Bir haber üç yoldan birine götürür:
 * kendi metni varsa /duyurular/{slug}, yoksa bağlı faaliyet, yoksa link_url.
 * Tümü /duyurular sayfasında listelenir; işaretlenen haber Anasayfa slaytında da görünür.
 */
final class News
{
    public const CATEGORIES = ['duyuru' => 'Duyuru', 'haber' => 'Haber', 'rapor' => 'Faaliyet Raporu'];

    /** Liste sayfasında sayfa başına haber. */
    public const PER_PAGE = 12;

    private const SELECT = 'SELECT n.*, ac.slug AS activity_slug, ac.is_published AS activity_published
        FROM news n LEFT JOIN activities ac ON ac.id = n.activity_id';

    private const PUBLIC = "n.status = 'published' AND n.published_on <= CURDATE()";

    public const IN_SLIDER = 'n.in_slider = 1 AND n.cover_image IS NOT NULL AND (n.slider_until IS NULL OR n.slider_until >= CURDATE())';

    /** Anasayfa: öne çıkan önce, sonra en yeniler. */
    public static function latest(int $limit = 5): array
    {
        $rows = App::db()->all(self::SELECT . ' WHERE ' . self::PUBLIC . ' ORDER BY n.is_featured DESC, n.published_on DESC, n.id DESC LIMIT ' . (int) $limit);
        return array_map([self::class, 'decorate'], $rows);
    }

    /** /duyurular: türe göre süzülmüş, en yeni önce, sayfalı. Sayılar sekmelerde görünür (tek sorgu). */
    public static function search(?string $category, int $page): array
    {
        $db = App::db();
        $counts = array_fill_keys(array_keys(self::CATEGORIES), 0);
        foreach ($db->all('SELECT n.category, COUNT(*) AS c FROM news n WHERE ' . self::PUBLIC . ' GROUP BY n.category') as $r) {
            $counts[$r['category']] = (int) $r['c'];
        }
        $total = $category !== null ? $counts[$category] : array_sum($counts);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);
        $rows = $db->all(
            self::SELECT . ' WHERE ' . self::PUBLIC . ($category !== null ? ' AND n.category = ?' : '')
            . ' ORDER BY n.published_on DESC, n.id DESC LIMIT ' . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE),
            $category !== null ? [$category] : []
        );
        return ['items' => array_map([self::class, 'decorate'], $rows), 'counts' => $counts, 'total' => $total, 'pages' => $pages, 'pageNo' => $page];
    }

    /** Anasayfa slaytına eklenen yayındaki haberler: kapaklı, slayt bitişi geçmemiş; en yeni önce. */
    public static function forSlider(int $limit): array
    {
        $rows = App::db()->all(self::SELECT . ' WHERE ' . self::PUBLIC . ' AND ' . self::IN_SLIDER
            . ' ORDER BY n.published_on DESC, n.id DESC LIMIT ' . $limit);
        return array_map([self::class, 'decorate'], $rows);
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = App::db()->one(self::SELECT . ' WHERE n.slug = ? AND ' . self::PUBLIC, [$slug]);
        return $row ? self::decorate($row) : null;
    }

    /** Haber sayfasının altı: en yeni diğer haberler. */
    public static function others(int $id, int $limit = 3): array
    {
        $rows = App::db()->all(self::SELECT . ' WHERE ' . self::PUBLIC . ' AND n.id <> ? ORDER BY n.published_on DESC, n.id DESC LIMIT ' . (int) $limit, [$id]);
        return array_map([self::class, 'decorate'], $rows);
    }

    public static function decorate(array $n): array
    {
        $n['category_text'] = self::CATEGORIES[$n['category']] ?? $n['category'];
        $n['url'] = self::href($n);
        $n['minutes'] = $n['read_minutes'] ?: self::readMinutes($n['body']);
        return $n;
    }

    /** Okuma süresi: dakikada ~200 kelime; kısa metinde null. */
    public static function readMinutes(?string $body): ?int
    {
        $words = $body ? count(preg_split('/\s+/u', trim($body)) ?: []) : 0;
        return $words >= 120 ? max(1, (int) round($words / 200)) : null;
    }

    private static function href(array $n): string
    {
        if (trim((string) $n['body']) !== '') {
            return url('duyuru', ['slug' => $n['slug']]);
        }
        if ($n['activity_slug'] && $n['activity_published']) {
            return url('faaliyet', ['slug' => $n['activity_slug']]);
        }
        if ($n['link_url']) {
            return site_url($n['link_url']);
        }
        return url('faaliyetler');
    }
}
