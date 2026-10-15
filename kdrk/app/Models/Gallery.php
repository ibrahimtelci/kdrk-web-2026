<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\App;

/**
 * Fotoğraf galerisi (gallery_albums + gallery_photos).
 * Albüm bir faaliyete bağlıysa boş bırakılan tarih, kategori ve yer faaliyetten gelir.
 * Sitede yalnızca yayında olan ve en az bir fotoğrafı olan albümler görünür.
 */
final class Gallery
{
    public const PER_PAGE = 12;

    private const SELECT = "SELECT g.*,
          COALESCE(g.taken_on, ac.starts_on) AS shot_on,
          COALESCE(g.category, ac.category) AS cat,
          COALESCE(g.location, ac.location) AS loc,
          ac.difficulty, ac.is_training, ac.slug AS activity_slug, ac.is_published AS activity_published,
          (SELECT COUNT(*) FROM gallery_photos p WHERE p.album_id = g.id) AS photo_count,
          COALESCE(
            (SELECT c.path FROM gallery_photos c WHERE c.id = g.cover_photo_id AND c.album_id = g.id),
            (SELECT f.path FROM gallery_photos f WHERE f.album_id = g.id ORDER BY f.sort, f.id LIMIT 1)
          ) AS cover
        FROM gallery_albums g
        LEFT JOIN activities ac ON ac.id = g.activity_id";

    private const PUBLIC = 'g.is_published = 1 AND EXISTS (SELECT 1 FROM gallery_photos x WHERE x.album_id = g.id)';

    /**
     * Liste sayfası: filtre + sayfalama.
     * @param array{yil?: ?int, kategori?: ?string, q?: string} $f
     * @return array{albums: array, total: int, pages: int, pageNo: int}
     */
    public static function search(array $f, int $page): array
    {
        [$where, $params] = self::filters($f);
        $db = App::db();
        $total = (int) $db->value("SELECT COUNT(*) FROM gallery_albums g LEFT JOIN activities ac ON ac.id = g.activity_id WHERE $where", $params);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);
        $rows = $db->all(
            self::SELECT . " WHERE $where ORDER BY shot_on DESC, g.id DESC LIMIT " . self::PER_PAGE . ' OFFSET ' . (($page - 1) * self::PER_PAGE),
            $params
        );
        return ['albums' => array_map([self::class, 'decorate'], $rows), 'total' => $total, 'pages' => $pages, 'pageNo' => $page];
    }

    /** Filtre seçenekleri ve başlıktaki sayılar (tek sorgu). */
    public static function overview(): array
    {
        $rows = App::db()->all(
            'SELECT YEAR(COALESCE(g.taken_on, ac.starts_on)) AS y, COALESCE(g.category, ac.category) AS cat,
                    COUNT(DISTINCT g.id) AS albums, COUNT(p.id) AS photos
               FROM gallery_albums g
               LEFT JOIN activities ac ON ac.id = g.activity_id
               JOIN gallery_photos p ON p.album_id = g.id
              WHERE g.is_published = 1
              GROUP BY y, cat'
        );
        $years = $cats = [];
        $albums = $photos = 0;
        foreach ($rows as $r) {
            if ($r['y'] !== null) {
                $years[(int) $r['y']] = true;
            }
            if ($r['cat'] !== null && isset(Activity::CATEGORIES[$r['cat']])) {
                $cats[$r['cat']] = Activity::CATEGORIES[$r['cat']];
            }
            $albums += (int) $r['albums'];
            $photos += (int) $r['photos'];
        }
        krsort($years);
        asort($cats);
        return ['years' => array_keys($years), 'categories' => $cats, 'albums' => $albums, 'photos' => $photos];
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = App::db()->one(self::SELECT . ' WHERE g.slug = ? AND ' . self::PUBLIC, [$slug]);
        return $row ? self::decorate($row) : null;
    }

    /** @return list<array> albümün fotoğrafları, sıralı */
    public static function photos(int $albumId, ?int $limit = null): array
    {
        return App::db()->all(
            'SELECT * FROM gallery_photos WHERE album_id = ? ORDER BY sort, id' . ($limit ? ' LIMIT ' . (int) $limit : ''),
            [$albumId]
        );
    }

    /** Aynı yıldan diğer albümler (detay sayfasının altı). */
    public static function related(array $album, int $limit = 3): array
    {
        $rows = App::db()->all(
            self::SELECT . ' WHERE ' . self::PUBLIC . ' AND g.id <> ? AND YEAR(COALESCE(g.taken_on, ac.starts_on)) = ?
             ORDER BY shot_on DESC, g.id DESC LIMIT ' . (int) $limit,
            [$album['id'], $album['year']]
        );
        return array_map([self::class, 'decorate'], $rows);
    }

    /** Faaliyet detay sayfası: faaliyete bağlı ilk yayındaki albüm. */
    public static function forActivity(int $activityId): ?array
    {
        $row = App::db()->one(self::SELECT . ' WHERE g.activity_id = ? AND ' . self::PUBLIC . ' ORDER BY g.id LIMIT 1', [$activityId]);
        return $row ? self::decorate($row) : null;
    }

    /**
     * Disiplin sayfasındaki şerit: o kategoriden en yeni albümlerin kapakları.
     * @return list<array{src: string, url: string, title: string}>
     */
    public static function strip(string $category, int $limit = 4): array
    {
        $rows = App::db()->all(
            self::SELECT . ' WHERE ' . self::PUBLIC . ' AND COALESCE(g.category, ac.category) = ?
             ORDER BY shot_on DESC, g.id DESC LIMIT ' . (int) $limit,
            [$category]
        );
        return array_map(static fn (array $r) => ['src' => $r['cover'], 'url' => url('galeri.album', ['slug' => $r['slug']]), 'title' => $r['title']], $rows);
    }

    /** Kart ve sayfa için türetilmiş alanlar. */
    public static function decorate(array $g): array
    {
        $g['url'] = url('galeri.album', ['slug' => $g['slug']]);
        $g['year'] = $g['shot_on'] ? (int) substr($g['shot_on'], 0, 4) : null;
        $g['category_text'] = Activity::CATEGORIES[$g['cat'] ?? ''] ?? null;
        $g['activity_url'] = $g['activity_slug'] && $g['activity_published'] ? url('faaliyet', ['slug' => $g['activity_slug']]) : null;
        $g['difficulty_class'] = $g['difficulty_text'] = null;
        if ($g['activity_slug'] !== null) {
            $d = Activity::difficultyInfo($g['difficulty'] !== null ? (int) $g['difficulty'] : null, (bool) $g['is_training']);
            [$g['difficulty_class'], $g['difficulty_text']] = $d;
        }
        return $g;
    }

    /** @return array{0: string, 1: array} */
    private static function filters(array $f): array
    {
        $where = [self::PUBLIC];
        $params = [];
        if (!empty($f['yil'])) {
            $where[] = 'YEAR(COALESCE(g.taken_on, ac.starts_on)) = ?';
            $params[] = (int) $f['yil'];
        }
        if (!empty($f['kategori'])) {
            $where[] = 'COALESCE(g.category, ac.category) = ?';
            $params[] = $f['kategori'];
        }
        if (($f['q'] ?? '') !== '') {
            $where[] = '(g.title LIKE ? OR COALESCE(g.location, ac.location) LIKE ?)';
            $like = '%' . addcslashes($f['q'], '%_\\') . '%';
            $params[] = $like;
            $params[] = $like;
        }
        return [implode(' AND ', $where), $params];
    }
}
