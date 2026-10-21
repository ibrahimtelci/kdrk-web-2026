<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\App;

/**
 * Anasayfa slaytları (CLAUDE.md §4): en fazla 3.
 *
 * Üç kaynak var; duyuru için ayrıca slayt yazılmaz:
 *  - Tanıtım slaytı: kulübü tanıtır, panelde elle yazılır (banners tablosu).
 *  - Faaliyet slaytı: faaliyette "Anasayfa slaytında göster" işaretlenir; tarihi geçince kalkar.
 *  - Duyuru & Haber slaytı: haberde aynı kutu işaretlenir; slider_until geçince kalkar.
 *
 * Sitedeki sıra: ilk tanıtım slaytı → faaliyetler (en yakın tarih önce) → duyuru & haberler (en yeni önce) → kalan tanıtım slaytları.
 */
final class Banner
{
    public const MAX = 3;
    public const CTA_PRESETS = ['calendar' => 'Faaliyet Takvimi', 'apply' => 'Faaliyete Başvur', 'details' => 'Faaliyet Ayrıntıları', 'membership' => 'Kulübe Üye Ol'];

    public static function removeEndedActivities(): bool
    {
        return \App\Core\Settings::get('slider_remove_ended_activities', '1') === '1';
    }


    // Eski düzende tarih sınırıyla eklenmiş tanıtım slaytları için korunur; formda artık tarih yok.
    private const VISIBLE = 'is_active = 1 AND (show_from IS NULL OR show_from <= CURDATE()) AND (show_until IS NULL OR show_until >= CURDATE())';

    public static function visible(): array
    {
        return array_slice(self::orderedCandidates(), 0, self::MAX);
    }

    public static function orderedCandidates(): array
    {
        return self::lineup(
            App::db()->all('SELECT * FROM banners WHERE ' . self::VISIBLE . ' ORDER BY sort, id'),
            Activity::forSlider(10000),
            News::forSlider(10000),
            false
        );
    }

    /**
     * Panel: üç sekmenin satırları; her satıra "şu an sitede mi" bilgisi eklenir.
     * @return array{fixed: array, activities: array, news: array}
     */
    public static function forPanel(): array
    {
        $db = App::db();
        $fixed = $db->all('SELECT b.*, (' . self::VISIBLE . ') AS live FROM banners b ORDER BY sort, id');
        $onSite = self::lineup(
            array_values(array_filter($fixed, static fn ($r) => (bool) $r['live'])),
            Activity::forSlider(10000),
            News::forSlider(10000)
        );
        $ids = ['fixed' => [], 'activity' => [], 'news' => []];
        foreach ($onSite as $s) {
            $ids[$s['kind']][] = (int) $s['source_id'];
        }
        $today = date('Y-m-d');

        foreach ($fixed as &$r) {
            $r['on_site'] = in_array((int) $r['id'], $ids['fixed'], true);
            $r['state'] = match (true) {
                !$r['is_active'] => 'Kapalı',
                $r['show_from'] && $r['show_from'] > $today => 'Zamanlandı',
                $r['show_until'] && $r['show_until'] < $today => 'Süresi doldu',
                !$r['on_site'] => 'Sırada (ilk 3 dışında)',
                default => 'Sitede',
            };
        }
        unset($r);

        // Tarihi geçenler listede tutulmaz: slayttan kendiliğinden düşmüşlerdir.
        $acts = array_map([Activity::class, 'decorate'], $db->all(
            "SELECT a.*, (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status IN ('pending','approved')) AS taken
             FROM activities a WHERE a.in_slider = 1" . (self::removeEndedActivities() ? " AND COALESCE(a.ends_on, a.starts_on) >= CURDATE()" : "") . "
             ORDER BY a.starts_on, a.id"
        ));
        foreach ($acts as &$a) {
            $a['on_site'] = in_array((int) $a['id'], $ids['activity'], true);
            $a['slide_state'] = match (true) {
                !$a['is_published'] => 'Yayında değil',
                $a['status'] === 'cancelled' => 'İptal edildi',
                $a['status'] === 'postponed' => 'Ertelendi',
                !$a['cover_image'] => 'Kapak görseli yok',
                !$a['on_site'] => 'Sırada (ilk 3 dışında)',
                default => 'Sitede',
            };
        }
        unset($a);

        $news = $db->all(
            'SELECT * FROM news WHERE in_slider = 1 AND (slider_until IS NULL OR slider_until >= CURDATE())
             ORDER BY published_on IS NULL DESC, published_on DESC, id DESC'
        );
        foreach ($news as &$n) {
            $n['category_text'] = News::CATEGORIES[$n['category']] ?? $n['category'];
            $n['on_site'] = in_array((int) $n['id'], $ids['news'], true);
            $n['slide_state'] = match (true) {
                $n['status'] !== 'published' => 'Taslak',
                $n['published_on'] > $today => 'Zamanlandı',
                !$n['cover_image'] => 'Kapak görseli yok',
                !$n['on_site'] => 'Sırada (ilk 3 dışında)',
                default => 'Sitede',
            };
        }
        unset($n);

        return ['fixed' => $fixed, 'activities' => $acts, 'news' => $news];
    }

    /** Slayta eklenebilecek faaliyetler: yayında, tarihi geçmemiş, iptal/ertelenmemiş, henüz slaytta olmayan. */
    public static function activityCandidates(): array
    {
        return App::db()->all(
            "SELECT id, title, category, starts_on, ends_on, cover_image FROM activities
             WHERE in_slider = 0 AND is_published = 1" . (self::removeEndedActivities() ? " AND COALESCE(ends_on, starts_on) >= CURDATE()" : "") . "
               AND (manual_status IS NULL OR manual_status = 'closed')
             ORDER BY starts_on, id"
        );
    }

    /** Slayta eklenebilecek duyuru & haberler: yayında, henüz slaytta olmayan; en yeni 30. */
    public static function newsCandidates(): array
    {
        return App::db()->all(
            "SELECT id, title, category, published_on, cover_image FROM news
             WHERE (in_slider = 0 OR slider_until < CURDATE()) AND status = 'published' AND published_on <= CURDATE()
             ORDER BY published_on DESC, id DESC"
        );
    }

    private static function lineup(array $fixed, array $activities, array $news, bool $limit = true): array
    {
        $fixed = array_map(static fn ($b) => $b + ['kind' => 'fixed', 'source_id' => $b['id'], 'activity' => null], $fixed);
        $slides = array_merge(
            array_slice($fixed, 0, 1),
            array_map([self::class, 'fromActivity'], $activities),
            array_map([self::class, 'fromNews'], $news),
            array_slice($fixed, 1)
        );
        $order = json_decode((string) \App\Core\Settings::get('slider_order', '[]'), true);
        if (is_array($order) && $order && $slides) {
            $first = $slides[0]['kind'] === 'fixed' ? array_shift($slides) : null;
            $rank = array_flip(array_values(array_filter($order, 'is_string')));
            foreach ($slides as $i => &$slide) $slide['_default_rank'] = $i;
            unset($slide);
            usort($slides, static fn ($x, $y) =>
                ($rank[$x['kind'] . ':' . $x['source_id']] ?? 10000 + $x['_default_rank'])
                <=> ($rank[$y['kind'] . ':' . $y['source_id']] ?? 10000 + $y['_default_rank']));
            if ($first) array_unshift($slides, $first);
        }
        return $limit ? array_slice($slides, 0, self::MAX) : $slides;
    }

    /** Faaliyeti tanıtım slaytıyla aynı biçime çevirir; butonlar başvuru durumuna göre seçilir. */
    private static function fromActivity(array $a): array
    {
        $apply = match (true) {
            $a['can_apply'] => 'Faaliyete Başvur',
            $a['status'] === 'full' => 'Yedek Listeye Başvur',
            default => null,
        };
        return [
            'kind'       => 'activity',
            'source_id'  => $a['id'],
            'activity'   => $a,
            'eyebrow'    => $a['category_text'] . ' · ' . tr_range($a['starts_on'], $a['ends_on']),
            'title'      => $a['title'],
            'text'       => $a['summary'],
            'image'      => $a['cover_image'],
            'cta_label'  => $apply,
            'cta_url'    => $apply ? $a['url'] . '#basvuru' : null,
            'cta2_label' => 'Faaliyet Ayrıntıları',
            'cta2_url'   => $a['url'],
            'stamp_1'    => tr_upper($a['location']),
            'stamp_2'    => $a['summit_m'] ? number_format((int) $a['summit_m'], 0, ',', '.') . ' M' : ($a['duration_text'] ? tr_upper($a['duration_text']) : ''),
        ];
    }

    /** Duyuru & haberi slayta çevirir: kendi sayfası varsa "Devamını Oku", yoksa gittiği yere "Ayrıntılar". */
    private static function fromNews(array $n): array
    {
        return [
            'kind'       => 'news',
            'source_id'  => $n['id'],
            'activity'   => null,
            'eyebrow'    => $n['category_text'] . ' · ' . tr_date($n['published_on']),
            'title'      => $n['title'],
            'text'       => $n['excerpt'],
            'image'      => $n['cover_image'],
            'cta_label'  => trim((string) $n['body']) !== '' ? 'Devamını Oku' : 'Ayrıntılar',
            'cta_url'    => $n['url'],
            'cta2_label' => 'Tüm Duyuru & Haberler',
            'cta2_url'   => url('duyurular'),
            'stamp_1'    => 'DUYURU & HABER',
            'stamp_2'    => tr_upper(tr_date($n['published_on'])),
        ];
    }
}
