<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\App;

/**
 * Faaliyetler — tek veri kaynağı (CLAUDE.md §9).
 * Durum elle tutulmaz, burada hesaplanır (CLAUDE.md §14).
 */
final class Activity
{
    public const CATEGORIES = [
        'gunubirlik'     => 'Günübirlik',
        'dagcilik'       => 'Dağcılık',
        'spor-tirmanis'  => 'Spor Tırmanış',
        'dag-kayagi'     => 'Dağ Kayağı',
        'egitim'         => 'Eğitim',
        'sosyal'         => 'Sosyal-Kültürel',
        'arama-kurtarma' => 'Arama Kurtarma',
    ];

    /** Kalan yer bu sayıya inince "Son X yer" gösterilir. */
    public const FEW_SEATS = 3;

    private const DIFFICULTY = [
        1 => ['easy', 'Kolay'],
        2 => ['medium', 'Orta'],
        3 => ['hard', 'Zor'],
        4 => ['very-hard', 'Çok zor'],
    ];

    private const STATUS_TEXT = [
        'open'      => 'Başvuru açık',
        'full'      => 'Dolu',
        'closed'    => 'Başvuru kapandı',
        'postponed' => 'Ertelendi',
        'cancelled' => 'İptal',
        'done'      => 'Tamamlandı',
    ];

    // Kontenjanı bekleyen ve onaylanan başvurular doldurur; reddedilen ve yedek listedekiler doldurmaz.
    private const SELECT = "SELECT a.*,
        (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status IN ('pending','approved')) AS taken
        FROM activities a WHERE a.is_published = 1";

    /** Bugün ve sonrası (bitiş tarihi geçmemiş), tarihe göre artan. */
    public static function upcoming(?int $limit = null, ?string $category = null): array
    {
        $sql = self::SELECT . ' AND COALESCE(a.ends_on, a.starts_on) >= CURDATE()';
        $params = [];
        if ($category !== null) {
            $sql .= ' AND a.category = ?';
            $params[] = $category;
        }
        $sql .= ' ORDER BY a.starts_on, a.id' . ($limit ? ' LIMIT ' . (int) $limit : '');
        return array_map([self::class, 'decorate'], App::db()->all($sql, $params));
    }

    /** Liste sayfası: yaklaşan + geçmiş birlikte; sekme ayrımı data-status ile yapılır. */
    public static function all(): array
    {
        return array_map([self::class, 'decorate'], App::db()->all(self::SELECT . ' ORDER BY a.starts_on, a.id'));
    }

    public static function findBySlug(string $slug): ?array
    {
        $row = App::db()->one(self::SELECT . ' AND a.slug = ?', [$slug]);
        return $row ? self::decorate($row) : null;
    }

    public static function status(array $a, ?string $today = null): string
    {
        $today ??= date('Y-m-d');
        $end = $a['ends_on'] ?: $a['starts_on'];
        $manual = $a['manual_status'] ?? null;
        $quota = (int) $a['quota'];
        $taken = (int) ($a['taken'] ?? 0);

        if ($manual === 'cancelled' || $manual === 'postponed') {
            return $manual;
        }
        if ($end < $today) {
            return 'done';
        }
        if ($manual === 'closed') {
            return 'closed';
        }
        if ($quota > 0 && $taken >= $quota) {
            return 'full';
        }
        if ($quota > 0 && $quota - $taken <= self::FEW_SEATS) {
            return 'few';
        }
        return 'open';
    }

    /** @return array{0: string, 1: string} [rozet sınıfı, metin] — faaliyet ve galeri kartı aynı rozeti kullanır */
    public static function difficultyInfo(?int $difficulty, bool $isTraining): array
    {
        if ($difficulty !== null && isset(self::DIFFICULTY[$difficulty])) {
            return self::DIFFICULTY[$difficulty];
        }
        return $isTraining ? ['education', 'Eğitim'] : ['social', 'Herkese açık'];
    }

    /** Görünümlerin ihtiyaç duyduğu türetilmiş alanları ekler. */
    public static function decorate(array $a): array
    {
        $status = self::status($a);
        $quota = (int) $a['quota'];
        $taken = (int) $a['taken'];
        $left = max(0, $quota - $taken);

        $a['status'] = $status;
        $a['status_text'] = $status === 'few' ? "Son $left yer" : self::STATUS_TEXT[$status];
        $a['seats_left'] = $left;
        $a['can_apply'] = in_array($status, ['open', 'few'], true);
        $a['fill'] = $quota > 0 ? min(1, $taken / $quota) : 0.0;
        $a['url'] = url('faaliyet', ['slug' => $a['slug']]);

        [$a['difficulty_class'], $a['difficulty_text']] = self::difficultyInfo($a['difficulty'] !== null ? (int) $a['difficulty'] : null, (bool) $a['is_training']);
        $a['difficulty_level'] = $a['difficulty'] !== null ? $a['difficulty'] . '/4' : null;
        $a['category_text'] = self::CATEGORIES[$a['category']] ?? $a['category'];
        $a['program'] = $a['program'] ? json_decode($a['program'], true) : [];
        $a['equipment'] = $a['equipment'] ? json_decode($a['equipment'], true) : [];
        return $a;
    }
}
