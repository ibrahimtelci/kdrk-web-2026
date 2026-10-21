<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Image;
use App\Models\Banner;

/**
 * Anasayfa slaytları (CLAUDE.md §4): sitede en fazla 3 görünür.
 * Üç sekme: tanıtım slaytları (elle yazılır, sırası panelden), faaliyet slaytları ve duyuru & haber slaytları.
 * Duyuru için ayrıca slayt yazılmaz; Duyuru & Haber'deki kayıt slayta eklenir. Sıra kuralı: Banner sınıfı.
 */
final class BannerController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];

    public function index(): void
    {
        $tab = in_array($_GET['sekme'] ?? '', ['faaliyet', 'haber'], true) ? $_GET['sekme'] : 'tanitim';
        $this->render('banners/index', 'Anasayfa Slaytları', 'banner', Banner::forPanel() + [
            'tab'        => $tab,
            'candidates' => match ($tab) {
                'faaliyet' => Banner::activityCandidates(),
                'haber'    => Banner::newsCandidates(),
                default    => [],
            },
        ]);
    }

    /** Faaliyeti slayta ekle / slayttan kaldır (do = add|remove). Faaliyet formundaki onay kutusuyla aynı alanı yazar. */
    public function activity(): void
    {
        $back = url('admin.banners', ['sekme' => 'faaliyet']);
        $a = App::db()->one('SELECT id, title, cover_image FROM activities WHERE id = ?', [(int) ($_POST['activity_id'] ?? 0)]);
        if ($a === null) {
            $this->flash('Bir faaliyet seç.', 'error');
            redirect($back, 303);
        }
        $add = ($_POST['do'] ?? '') === 'add';
        if ($add && !$a['cover_image']) {
            $this->flash('“' . $a['title'] . '” için önce kapak görseli yükle; slayt görseli kapaktan gelir.', 'error');
            redirect($back, 303);
        }
        App::db()->run('UPDATE activities SET in_slider = ? WHERE id = ?', [$add ? 1 : 0, $a['id']]);
        Audit::log('update', 'activity', (int) $a['id'], ['in_slider' => $add ? 1 : 0]);
        $this->flash($add ? 'Faaliyet slayta eklendi.' : 'Faaliyet slayttan kaldırıldı.');
        redirect($back, 303);
    }

    /** Duyuru & haberi slayta ekle / slayttan kaldır (do = add|remove). Haber formundaki onay kutusuyla aynı alanı yazar. */
    public function news(): void
    {
        $back = url('admin.banners', ['sekme' => 'haber']);
        $n = App::db()->one('SELECT id, title, cover_image FROM news WHERE id = ?', [(int) ($_POST['news_id'] ?? 0)]);
        if ($n === null) {
            $this->flash('Bir duyuru veya haber seç.', 'error');
            redirect($back, 303);
        }
        $add = ($_POST['do'] ?? '') === 'add';
        if ($add && !$n['cover_image']) {
            $this->flash('“' . $n['title'] . '” için önce kapak görseli yükle; slayt görseli kapaktan gelir.', 'error');
            redirect($back, 303);
        }
        // Eklerken eski (geçmiş) slayt bitişi temizlenir; bitiş gerekiyorsa haber formundan verilir.
        App::db()->run('UPDATE news SET in_slider = ?, slider_until = IF(?, NULL, slider_until) WHERE id = ?', [$add ? 1 : 0, $add ? 1 : 0, $n['id']]);
        Audit::log('update', 'news', (int) $n['id'], ['in_slider' => $add ? 1 : 0]);
        $this->flash($add ? 'Slayta eklendi.' : 'Slayttan kaldırıldı.');
        redirect($back, 303);
    }

    public function reorder(): void
    {
        $this->requireRole('admin', 'editor');
        $direction = $_POST['do'] ?? '';
        if ($direction === 'reset') {
            \App\Core\Settings::set('slider_order', '[]');
        } else {
            $rows = Banner::orderedCandidates();
            $keys = array_map(static fn ($r) => $r['kind'] . ':' . $r['source_id'], $rows);
            $index = array_search((string) ($_POST['key'] ?? ''), $keys, true);
            $target = $index === false ? -1 : $index + ($direction === 'up' ? -1 : 1);
            $min = isset($rows[0]) && $rows[0]['kind'] === 'fixed' ? 1 : 0;
            if (in_array($direction, ['up', 'down'], true) && $index !== false && $index >= $min && $target >= $min && $target < count($keys)) {
                [$keys[$index], $keys[$target]] = [$keys[$target], $keys[$index]];
                \App\Core\Settings::set('slider_order', json_encode($keys, JSON_THROW_ON_ERROR));
            }
        }
        Audit::log('update', 'banner', null, ['order' => $direction]);
        $this->flash($direction === 'reset' ? 'Otomatik sıralama açıldı.' : 'Görsellerin yayın sırası kaydedildi.');
        redirect(url('admin.banners'), 303);
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
        $id = App::db()->insert('banners', $data);
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
        redirect(url('admin.banners'), 303);
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
        foreach (['cta' => 'cta_url', 'cta2' => 'cta2_url'] as $p => $k) {
            [$d[$k], $err] = self::link($k);
            if ($err) {
                $e[$k] = $err;
            } elseif (($d[$p . '_label'] === null) !== ($d[$k] === null)) {
                $e[$d[$k] === null ? $k : $p . '_label'] = 'Buton için hem yazı hem bağlantı gerekli (ya da ikisini de boş bırak).';
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
