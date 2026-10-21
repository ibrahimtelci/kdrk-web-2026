<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Image;
use App\Core\Session;
use App\Models\Activity;
use App\Models\Gallery;

/**
 * Fotoğraf galerisi: albümler ve fotoğraflar.
 * Fotoğraf yükleme JS açıkken tek tek (fetch), kapalıyken formla toplu gider.
 */
final class GalleryController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];

    private const FILTERS = ['tumu' => 'Tümü', 'yayinda' => 'Yayında', 'taslak' => 'Yayında değil'];

    public function index(): void
    {
        $filter = array_key_exists($_GET['durum'] ?? '', self::FILTERS) ? $_GET['durum'] : 'tumu';
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = ['1 = 1'];
        $params = [];
        if ($filter !== 'tumu') {
            $where[] = 'g.is_published = ' . ($filter === 'yayinda' ? 1 : 0);
        }
        if ($q !== '') {
            $where[] = Gallery::SEARCH; // albüm adı, etiket, yer, faaliyet adı
            $like = '%' . addcslashes($q, '%_\\') . '%';
            array_push($params, $like, $like, $like, $like);
        }
        $rows = App::db()->all(
            'SELECT g.*, COALESCE(g.taken_on, ac.starts_on) AS shot_on, ac.title AS activity_title,
                    (SELECT COUNT(*) FROM gallery_photos p WHERE p.album_id = g.id) AS photo_count,
                    COALESCE(
                      (SELECT c.path FROM gallery_photos c WHERE c.id = g.cover_photo_id AND c.album_id = g.id),
                      (SELECT f.path FROM gallery_photos f WHERE f.album_id = g.id ORDER BY f.sort, f.id LIMIT 1)
                    ) AS cover
               FROM gallery_albums g LEFT JOIN activities ac ON ac.id = g.activity_id
              WHERE ' . implode(' AND ', $where) . '
              ORDER BY shot_on IS NULL, shot_on DESC, g.id DESC',
            $params
        );
        $years = [];
        foreach ($rows as $r) {
            $years[$r['shot_on'] ? substr($r['shot_on'], 0, 4) : 'Tarihsiz'][] = $r;
        }
        $this->render('gallery/index', 'Fotoğraf Galerisi', 'gallery', [
            'years' => $years, 'count' => count($rows), 'filter' => $filter, 'filters' => self::FILTERS, 'q' => $q,
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin', 'editor');
        // Faaliyet sayfasındaki "Albüm oluştur" kısayolu: ?faaliyet=ID → ad, tarih, kategori, yer, açıklama ve etiketler faaliyetten
        $prefill = null;
        if (isset($_GET['faaliyet'])) {
            $prefill = App::db()->one('SELECT id, title, starts_on, category, location, report_summary FROM activities WHERE id = ?', [(int) $_GET['faaliyet']]);
        }
        $old = Session::pull('admin_old', []);
        if (!$old && $prefill) {
            $old = [
                'activity_id'  => $prefill['id'],
                'title'        => $prefill['title'],
                'taken_on'     => $prefill['starts_on'],
                'category'     => $prefill['category'],
                'location'     => $prefill['location'],
                'description'  => (string) $prefill['report_summary'],
                'tags'         => (string) Gallery::tagsFor($prefill),
                'is_published' => 1,
            ];
        }
        $this->render('gallery/form', 'Yeni Albüm', 'gallery', ['g' => null, 'photos' => [], 'activities' => $this->activityOptions(), 'old' => $old]);
    }

    public function edit(string $id): void
    {
        $g = $this->find((int) $id);
        $photos = App::db()->all('SELECT * FROM gallery_photos WHERE album_id = ? ORDER BY sort, id', [$g['id']]);
        $this->render('gallery/form', $g['title'], 'gallery', ['g' => $g, 'photos' => $photos, 'activities' => $this->activityOptions()]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(null);
        if ($errors) {
            $this->back(url('admin.gallery.new'), $errors, $_POST);
        }
        $id = App::db()->insert('gallery_albums', $data);
        Audit::log('create', 'gallery_album', $id, ['title' => $data['title']]);
        $this->flash('Albüm oluşturuldu. Şimdi fotoğrafları yükle.');
        $this->done(url('admin.gallery.edit', ['id' => $id]) . '#fotograflar');
    }

    public function update(string $id): void
    {
        $g = $this->find((int) $id);
        [$data, $errors] = $this->validate($g);
        if ($errors) {
            $this->back(url('admin.gallery.edit', ['id' => $g['id']]), $errors, $_POST);
        }
        $sets = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($data)));
        App::db()->run("UPDATE gallery_albums SET $sets WHERE id = :id", $data + ['id' => $g['id']]);
        Audit::log('update', 'gallery_album', (int) $g['id']);
        $this->flash('Değişiklikler kaydedildi.');
        redirect(url('admin.gallery.edit', ['id' => $g['id']]), 303);
    }

    public function delete(string $id): void
    {
        $g = $this->find((int) $id);
        $paths = App::db()->all('SELECT path FROM gallery_photos WHERE album_id = ?', [$g['id']]);
        App::db()->run('DELETE FROM gallery_albums WHERE id = ?', [$g['id']]); // fotoğraf satırları CASCADE ile gider
        foreach ($paths as $p) {
            Image::delete($p['path']);
        }
        Audit::log('delete', 'gallery_album', (int) $g['id'], ['title' => $g['title'], 'photos' => count($paths)]);
        $this->flash('Albüm ve ' . count($paths) . ' fotoğrafı silindi.');
        redirect(url('admin.gallery'), 303);
    }

    /** Fotoğraf yükleme. JS her dosyayı ayrı istekte gönderir ve JSON bekler. */
    public function upload(string $id): void
    {
        $g = $this->find((int) $id);
        $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $added = [];
        $errors = [];
        $sort = (int) App::db()->value('SELECT COALESCE(MAX(sort), 0) FROM gallery_photos WHERE album_id = ?', [$g['id']]);
        foreach (self::files('photos') as $f) {
            try {
                $path = Image::store($f, 'gallery');
            } catch (\RuntimeException $ex) {
                $errors[] = $f['name'] . ': ' . $ex->getMessage();
                continue;
            }
            [$w, $h] = Image::size($path);
            $pid = App::db()->insert('gallery_photos', ['album_id' => $g['id'], 'path' => $path, 'width' => $w, 'height' => $h, 'sort' => ++$sort]);
            $added[] = ['id' => $pid, 'thumb' => img($path, 400)];
        }
        if (!$added && !$errors) {
            $errors[] = 'Dosya seçilmedi ya da toplam boyut sunucu sınırını aştı (' . ini_get('post_max_size') . ').';
        }
        if ($added) {
            Audit::log('upload', 'gallery_album', (int) $g['id'], ['photos' => count($added)]);
        }
        if ($json) {
            http_response_code($added ? 200 : 422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => (bool) $added, 'added' => $added, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($added) {
            $this->flash(count($added) . ' fotoğraf yüklendi.' . ($errors ? ' Yüklenemeyenler: ' . implode(' · ', $errors) : ''), $errors ? 'error' : 'ok');
        } else {
            $this->flash(implode(' · ', $errors), 'error');
        }
        redirect(url('admin.gallery.edit', ['id' => $g['id']]) . '#fotograflar', 303);
    }

    /** Tek fotoğraf: açıklama kaydet, kapak yap, sırala, sil (do = save|cover|up|down|delete). */
    public function photo(string $id): void
    {
        $db = App::db();
        $p = $db->one('SELECT * FROM gallery_photos WHERE id = ?', [(int) $id]) ?? abort(404);
        $back = url('admin.gallery.edit', ['id' => $p['album_id']]) . '#foto-' . $p['id'];
        switch ($_POST['do'] ?? 'save') {
            case 'cover':
                $db->run('UPDATE gallery_albums SET cover_photo_id = ? WHERE id = ?', [$p['id'], $p['album_id']]);
                $this->flash('Kapak fotoğrafı değişti.');
                break;
            case 'up':
            case 'down':
                $this->move($p, $_POST['do'] === 'up' ? -1 : 1);
                break;
            case 'delete':
                $db->transaction(static function () use ($db, $p): void {
                    $db->run('UPDATE gallery_albums SET cover_photo_id = NULL WHERE id = ? AND cover_photo_id = ?', [$p['album_id'], $p['id']]);
                    $db->run('DELETE FROM gallery_photos WHERE id = ?', [$p['id']]);
                });
                Image::delete($p['path']);
                Audit::log('delete', 'gallery_photo', (int) $p['id'], ['album' => (int) $p['album_id']]);
                $this->flash('Fotoğraf silindi.');
                $back = url('admin.gallery.edit', ['id' => $p['album_id']]) . '#fotograflar';
                break;
            default:
                $db->run('UPDATE gallery_photos SET caption = ?, credit = ? WHERE id = ?', [self::nullable('caption'), self::nullable('credit', 120), $p['id']]);
                $this->flash('Fotoğraf bilgisi kaydedildi.');
        }
        redirect($back, 303);
    }

    private function move(array $p, int $dir): void
    {
        App::db()->transaction(static function () use ($p, $dir): void {
            $db = App::db();
            $ids = array_map('intval', array_column($db->all('SELECT id FROM gallery_photos WHERE album_id = ? ORDER BY sort, id', [$p['album_id']]), 'id'));
            $i = array_search((int) $p['id'], $ids, true);
            $j = $i + $dir;
            if ($i === false || $j < 0 || $j >= count($ids)) {
                return;
            }
            [$ids[$i], $ids[$j]] = [$ids[$j], $ids[$i]];
            foreach ($ids as $n => $pid) {
                $db->run('UPDATE gallery_photos SET sort = ? WHERE id = ?', [$n + 1, $pid]);
            }
        });
    }

    private function find(int $id): array
    {
        return App::db()->one(
            'SELECT g.*, (SELECT COUNT(*) FROM gallery_photos p WHERE p.album_id = g.id) AS photo_count FROM gallery_albums g WHERE g.id = ?',
            [$id]
        ) ?? abort(404);
    }

    /** Albüme bağlanabilecek faaliyetler: yeniden eskiye. */
    private function activityOptions(): array
    {
        return App::db()->all('SELECT id, title, starts_on FROM activities ORDER BY starts_on DESC, id DESC');
    }

    /** @return array{0: array, 1: array} */
    private function validate(?array $current): array
    {
        $e = [];
        $activityId = (int) ($_POST['activity_id'] ?? 0) ?: null;
        $activity = $activityId ? App::db()->one('SELECT id, title, starts_on, category FROM activities WHERE id = ?', [$activityId]) : null;
        if ($activityId && !$activity) {
            $e['activity_id'] = 'Seçilen faaliyet bulunamadı.';
        }
        $d = [
            'title'        => self::str('title', 160),
            'activity_id'  => $activity['id'] ?? null,
            'taken_on'     => null,
            'category'     => isset(Activity::CATEGORIES[$_POST['category'] ?? '']) ? $_POST['category'] : null,
            'location'     => self::nullable('location', 160),
            'tags'         => Gallery::cleanTags((string) ($_POST['tags'] ?? '')),
            'description'  => self::nullable('description', 2000),
            'is_published' => empty($_POST['is_published']) ? 0 : 1,
        ];
        $date = trim((string) ($_POST['taken_on'] ?? ''));
        if ($date !== '') {
            $dt = \DateTime::createFromFormat('!Y-m-d', $date);
            if (!$dt || $dt->format('Y-m-d') !== $date) {
                $e['taken_on'] = 'Geçerli bir tarih seç.';
            } else {
                $d['taken_on'] = $date;
            }
        }
        if (mb_strlen($d['title']) < 3) {
            $e['title'] = 'Albüm adını yaz.';
        }
        if (!$activity && $d['taken_on'] === null && !isset($e['taken_on'])) {
            $e['taken_on'] = 'Faaliyete bağlı olmayan albümde tarih gerekli.';
        }
        if (!$activity && $d['category'] === null) {
            $e['category'] = 'Faaliyete bağlı olmayan albümde kategori seç.';
        }

        $slug = slugify(self::str('slug', 160));
        if ($slug === '') {
            $year = substr($d['taken_on'] ?? $activity['starts_on'] ?? date('Y'), 0, 4);
            $slug = slugify(str_contains($d['title'], $year) ? $d['title'] : $d['title'] . ' ' . $year);
        }
        if (App::db()->value('SELECT COUNT(*) FROM gallery_albums WHERE slug = ? AND id <> ?', [$slug, $current['id'] ?? 0])) {
            $e['slug'] = 'Bu adres başka bir albümde kullanılıyor; farklı bir adres yaz.';
        }
        $d['slug'] = $slug;
        return [$d, $e];
    }

    /** $_FILES['photos'] çoklu yüklemesini dosya listesine çevirir. */
    private static function files(string $key): array
    {
        $f = $_FILES[$key] ?? null;
        if (!$f || !is_array($f['name'])) {
            return $f && ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE ? [$f] : [];
        }
        $out = [];
        foreach (array_keys($f['name']) as $i) {
            if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            $out[] = ['name' => (string) $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
        }
        return $out;
    }
}
