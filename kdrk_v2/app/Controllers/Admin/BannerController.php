<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Image;
use App\Models\Banner;

/**
 * Anasayfa slaytları (CLAUDE.md §4): aktif slaytların tamamı gösterilir.
 * Üç sekme: tanıtım slaytları (elle yazılır, sırası panelden), faaliyet slaytları ve duyuru & haber slaytları.
 * Duyuru için ayrıca slayt yazılmaz; Duyuru & Haber'deki kayıt slayta eklenir. Sıra kuralı: Banner sınıfı.
 */
final class BannerController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];

    public function index(): void
    {
        $this->render('banners/index', 'Anasayfa Görselleri', 'banner', Banner::forPanel() + [
            'activityCandidates' => Banner::activityCandidates(),
            'newsCandidates' => Banner::newsCandidates(),
        ]);
    }

    /** Faaliyeti slayta ekle / slayttan kaldır (do = add|remove). Faaliyet formundaki onay kutusuyla aynı alanı yazar. */
    public function activity(): void
    {
        $back = $this->listUrl();
        $a = App::db()->one('SELECT id, title, cover_image, is_published, starts_on, ends_on, manual_status FROM activities WHERE id = ?', [(int) ($_POST['activity_id'] ?? 0)]);
        if ($a === null) {
            $this->flash('Bir faaliyet seç.', 'error');
            redirect($back, 303);
        }
        $add = ($_POST['do'] ?? '') === 'add';
        if ($add && (!$a['is_published'] || in_array($a['manual_status'], ['cancelled', 'postponed'], true) || (Banner::removeEndedActivities() && ($a['ends_on'] ?: $a['starts_on']) < date('Y-m-d')))) {
            $this->flash('Bu faaliyet artık slayta eklenmeye uygun değil. Listeyi yenileyip tekrar seç.', 'error');
            redirect($back, 303);
        }
        if ($add && !$a['cover_image']) {
            $this->flash('“' . $a['title'] . '” için önce kapak görseli yükle; slayt görseli kapaktan gelir.', 'error');
            redirect($back, 303);
        }
        $previousOrder = Banner::orderedKeys();
        App::db()->run('UPDATE activities SET in_slider = ? WHERE id = ?', [$add ? 1 : 0, $a['id']]);
        if ($add) { Banner::setEnabled('activity:' . $a['id'], true); Banner::appendToOrder($previousOrder, 'activity:' . $a['id']); }
        Audit::log('update', 'activity', (int) $a['id'], ['in_slider' => $add ? 1 : 0]);
        $this->flash($add ? 'Faaliyet slayta eklendi.' : 'Faaliyet slayttan kaldırıldı.');
        redirect($back, 303);
    }

    /** Duyuru & haberi slayta ekle / slayttan kaldır (do = add|remove). Haber formundaki onay kutusuyla aynı alanı yazar. */
    public function news(): void
    {
        $back = $this->listUrl();
        $n = App::db()->one('SELECT id, title, cover_image, status, published_on FROM news WHERE id = ?', [(int) ($_POST['news_id'] ?? 0)]);
        if ($n === null) {
            $this->flash('Bir duyuru veya haber seç.', 'error');
            redirect($back, 303);
        }
        $add = ($_POST['do'] ?? '') === 'add';
        if ($add && ($n['status'] !== 'published' || !$n['published_on'] || $n['published_on'] > date('Y-m-d'))) {
            $this->flash('Yalnızca yayındaki haber ve duyurular eklenebilir.', 'error');
            redirect($back, 303);
        }
        if ($add && !$n['cover_image']) {
            $this->flash('“' . $n['title'] . '” için önce kapak görseli yükle; slayt görseli kapaktan gelir.', 'error');
            redirect($back, 303);
        }
        // Eklerken eski (geçmiş) slayt bitişi temizlenir; bitiş gerekiyorsa haber formundan verilir.
        $previousOrder = Banner::orderedKeys();
        App::db()->run('UPDATE news SET in_slider = ?, slider_until = IF(?, NULL, slider_until) WHERE id = ?', [$add ? 1 : 0, $add ? 1 : 0, $n['id']]);
        if ($add) { Banner::setEnabled('news:' . $n['id'], true); Banner::appendToOrder($previousOrder, 'news:' . $n['id']); }
        Audit::log('update', 'news', (int) $n['id'], ['in_slider' => $add ? 1 : 0]);
        $this->flash($add ? 'Slayta eklendi.' : 'Slayttan kaldırıldı.');
        redirect($back, 303);
    }

    public function reorder(): void
    {
        $this->requireRole('admin', 'editor');
        $direction = $_POST['do'] ?? '';
        if ($direction === 'set') {
            $keys = Banner::orderedKeys();
            $requested = $_POST['keys'] ?? null;
            $status = 200;
            $message = 'Sıralama kaydedildi.';
            if (!hash_equals(hash('sha256', json_encode($keys)), (string) ($_POST['revision'] ?? ''))) {
                $status = 409; $message = 'Liste değişti. Sayfayı yenileyip tekrar sırala.';
            } elseif (!is_array($requested) || count($requested) !== count($keys) || count(array_filter($requested, 'is_string')) !== count($keys)
                || count(array_unique($requested)) !== count($keys) || array_diff($keys, $requested)) {
                $status = 422; $message = 'Geçersiz sıra. Listeyi yenileyip tekrar dene.';
            } else {
                \App\Core\Settings::set('slider_order', json_encode(array_values($requested), JSON_THROW_ON_ERROR));
                Audit::log('update', 'banner', null, ['order' => 'drag']);
            }
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => $status === 200, 'message' => $message], JSON_UNESCAPED_UNICODE);
            return;
        }
        $keys = Banner::orderedKeys();
        $index = array_search((string) ($_POST['key'] ?? ''), $keys, true);
        $target = $index === false ? -1 : $index + ($direction === 'up' ? -1 : 1);
        if (!in_array($direction, ['up', 'down'], true) || $index === false || $target < 0 || $target >= count($keys)) abort(422);
        [$keys[$index], $keys[$target]] = [$keys[$target], $keys[$index]];
        \App\Core\Settings::set('slider_order', json_encode($keys, JSON_THROW_ON_ERROR));
        Audit::log('update', 'banner', null, ['order' => $direction]);
        $this->flash('Slaytların sırası kaydedildi.');
        redirect($this->listUrl(), 303);
    }

    public function toggle(): void
    {
        $this->requireRole('admin', 'editor');
        $key = (string) ($_POST['key'] ?? '');
        if (!in_array($key, Banner::orderedKeys(), true) || !in_array($_POST['active'] ?? '', ['0', '1'], true)) abort(422);
        $active = $_POST['active'] === '1';
        if ($active && !str_starts_with($key, 'fixed:')) {
            $eligible = array_map(static fn ($r) => $r['kind'] . ':' . $r['source_id'], Banner::orderedCandidates(true));
            if (!in_array($key, $eligible, true)) {
                $this->flash('Bu içerik henüz gösterime uygun değil. Yayın durumunu, tarihini ve kapak görselini düzenle.', 'error');
                redirect($this->listUrl(), 303);
            }
        }
        $keys = Banner::orderedKeys();
        Banner::setEnabled($key, $active);
        \App\Core\Settings::set('slider_order', json_encode($keys, JSON_THROW_ON_ERROR));
        Audit::log('update', 'banner', null, ['slide' => $key, 'active' => $active]);
        $this->flash($active ? 'Slayt yayına alındı.' : 'Slayt pasife alındı.');
        redirect($this->listUrl(), 303);
    }

    /** Seçili slaytları topluca yayına al / pasife al (do = activate|deactivate). Uygun olmayanlar atlanır. */
    public function bulk(): void
    {
        $this->requireRole('admin', 'editor');
        $do = (string) ($_POST['do'] ?? '');
        if (!in_array($do, ['activate', 'deactivate'], true)) abort(422);
        $active = $do === 'activate';
        $keys = Banner::orderedKeys();
        $requested = is_array($_POST['keys'] ?? null) ? array_filter($_POST['keys'], 'is_string') : [];
        $selected = array_values(array_intersect($keys, $requested));
        if (!$selected) {
            $this->flash('Önce en az bir slayt seç.', 'error');
            redirect($this->listUrl(), 303);
        }
        $eligible = $active ? array_map(static fn ($r) => $r['kind'] . ':' . $r['source_id'], Banner::orderedCandidates(true)) : [];
        $changed = 0;
        foreach ($selected as $key) {
            if ($active && !str_starts_with($key, 'fixed:') && !in_array($key, $eligible, true)) continue;
            Banner::setEnabled($key, $active);
            $changed++;
        }
        \App\Core\Settings::set('slider_order', json_encode($keys, JSON_THROW_ON_ERROR));
        Audit::log('update', 'banner', null, ['slides' => $selected, 'active' => $active]);
        $skipped = count($selected) - $changed;
        $message = $changed . ' slayt ' . ($active ? 'yayına alındı.' : 'pasife alındı.');
        if ($skipped) $message .= ' ' . $skipped . ' slayt henüz gösterime uygun olmadığı için atlandı; yayın durumunu, tarihini ve kapak görselini kontrol et.';
        $this->flash($message, $changed ? 'ok' : 'error');
        redirect($this->listUrl(), 303);
    }

    /** Liste işleminden sonra aynı filtre, arama, sıralama ve görünüme dönülür. Yalnızca bu listenin adresi kabul edilir. */
    private function listUrl(): string
    {
        $base = url('admin.banners');
        $return = (string) ($_POST['return'] ?? '');
        $path = parse_url($return, PHP_URL_PATH);
        if ($return === '' || $path !== parse_url($base, PHP_URL_PATH) || str_contains($return, '//')) return $base;
        parse_str((string) parse_url($return, PHP_URL_QUERY), $query);
        $allowed = array_intersect_key($query, array_flip(['tur', 'q', 'sirala', 'yon']));
        $allowed = array_filter($allowed, static fn ($v) => is_string($v) && $v !== '');
        return $base . ($allowed ? '?' . http_build_query($allowed) : '');
    }

    public function create(): void
    {
        $this->requireRole('admin', 'editor');
        $this->render('banners/form', 'Yeni Tanıtım Slaytı', 'banner', ['b' => null]);
    }

    public function edit(string $id): void
    {
        $b = $this->find((int) $id);
        $this->render('banners/form', $b['title'], 'banner', ['b' => $b]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(null);
        if ($errors) {
            $this->back(url('admin.banners.new'), $errors, $_POST);
        }
        $this->replaceImage($data, 'image', 'image', null, 'banners', url('admin.banners.new'));
        $data['sort'] = 1 + (int) App::db()->value('SELECT COALESCE(MAX(sort), 0) FROM banners');
        $previousOrder = Banner::orderedKeys();
        $id = App::db()->insert('banners', $data);
        Banner::appendToOrder($previousOrder, 'fixed:' . $id);
        Audit::log('create', 'banner', $id, ['title' => $data['title']]);
        $this->flash('Tanıtım slaytı eklendi. Sırası listenin sonunda; gerekirse yukarı taşı.');
        $this->done(url('admin.banners'));
    }

    public function update(string $id): void
    {
        $b = $this->find((int) $id);
        $back = url('admin.banners.edit', ['id' => $b['id']]);
        [$data, $errors] = $this->validate($b);
        if ($errors) {
            $this->back($back, $errors, $_POST);
        }
        $this->replaceImage($data, 'image', 'image', $b['image'], 'banners', $back);
        $sets = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($data)));
        App::db()->run("UPDATE banners SET $sets WHERE id = :id", $data + ['id' => $b['id']]);
        Audit::log('update', 'banner', (int) $b['id']);
        $this->flash('Değişiklikler kaydedildi.');
        redirect($back, 303);
    }

    public function delete(string $id): void
    {
        $b = $this->find((int) $id);
        App::db()->run('DELETE FROM banners WHERE id = ?', [$b['id']]);
        Image::delete($b['image']);
        Audit::log('delete', 'banner', (int) $b['id'], ['title' => $b['title']]);
        $this->flash('Slayt silindi.');
        redirect($this->listUrl(), 303);
    }

    /** Sırayı değiştir (do = up|down). */
    public function move(string $id): void
    {
        $b = $this->find((int) $id);
        $dir = ($_POST['do'] ?? '') === 'up' ? -1 : 1;
        App::db()->transaction(static function () use ($b, $dir): void {
            $db = App::db();
            $ids = array_map('intval', array_column($db->all('SELECT id FROM banners ORDER BY sort, id'), 'id'));
            $i = array_search((int) $b['id'], $ids, true);
            $j = $i + $dir;
            if ($i === false || $j < 0 || $j >= count($ids)) {
                return;
            }
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            foreach ($ids as $n => $bid) {
                $db->run('UPDATE banners SET sort = ? WHERE id = ?', [$n + 1, $bid]);
            }
        });
        Audit::log('update', 'banner', (int) $b['id'], ['move' => $dir]);
        redirect(url('admin.banners') . '#slayt-' . $b['id'], 303);
    }

    private function find(int $id): array
    {
        return App::db()->one('SELECT * FROM banners WHERE id = ?', [$id]) ?? abort(404);
    }

    /** @return array{0: array, 1: array} */
    private function validate(?array $current): array
    {
        $e = [];
        $d = [
            'eyebrow'    => self::nullable('eyebrow', 80),
            'title'      => self::str('title', 160),
            'text'       => self::nullable('text', 400),
            'cta_label'  => self::nullable('cta_label', 60),
            'cta_url'    => null,
            'cta2_label' => self::nullable('cta2_label', 60),
            'cta2_url'   => null,
            'stamp_1'    => self::nullable('stamp_1', 60),
            'stamp_2'    => self::nullable('stamp_2', 60),
            'is_active'  => empty($_POST['is_active']) ? 0 : 1,
        ];
        if (mb_strlen($d['title']) < 3) {
            $e['title'] = 'Slayt başlığını yaz.';
        }
        foreach (['cta' => 'cta_url', 'cta2' => 'cta2_url'] as $prefix => $column) {
            $choice = (string) ($_POST[$prefix . '_choice'] ?? Banner::introChoice($current, $prefix));
            $d[$prefix . '_label'] = null;
            if ($choice === '') {
                $d[$column] = null;
            } elseif (isset(Banner::CTA_PRESETS[$choice])) {
                $d[$prefix . '_label'] = Banner::CTA_PRESETS[$choice];
                $d[$column] = $choice === 'calendar' ? url('faaliyetler') : url('kulup-uyeligi');
            } else {
                $e[$prefix . '_choice'] = 'Hazır buton seçeneklerinden birini seç.';
            }
        }
        // Tanıtım slaytında tarih yok; eski kayıttaki show_from/show_until değerine dokunulmaz.
        $hasFile = !empty($_FILES['image']['name']) && ($_FILES['image']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE;
        if (!$hasFile && ($current === null || !$current['image'] || !empty($_POST['remove_image']))) {
            $e['image'] = 'Slayt için bir görsel seç (yatay, en az 1600 px genişlik önerilir).';
        } elseif ($hasFile && ($_FILES['image']['size'] ?? 0) > Image::MAX_BYTES) {
            $e['image'] = 'Görsel en fazla 15 MB olabilir.';
        }
        return [$d, $e];
    }
}
