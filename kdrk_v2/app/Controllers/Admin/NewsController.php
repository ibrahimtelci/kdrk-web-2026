<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Image;
use App\Core\Session;
use App\Models\News;

/** Duyuru & Haber (Anasayfa bölümü, /duyurular listesi, /duyurular/{slug}, isteğe bağlı Anasayfa slaytı). */
final class NewsController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];

    private const FILTERS = ['tumu' => 'Tümü', 'yayinda' => 'Yayında', 'taslak' => 'Taslak'];

    public function index(): void
    {
        $filter = array_key_exists($_GET['durum'] ?? '', self::FILTERS) ? $_GET['durum'] : 'tumu';
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = ['1 = 1'];
        $params = [];
        if ($filter === 'yayinda') {
            $where[] = "n.status = 'published'";
        } elseif ($filter === 'taslak') {
            $where[] = "n.status = 'draft'";
        }
        if ($q !== '') {
            $where[] = 'n.title LIKE ?';
            $params[] = '%' . addcslashes($q, '%_\\') . '%';
        }
        $rows = App::db()->all(
            'SELECT n.*, ac.title AS activity_title FROM news n LEFT JOIN activities ac ON ac.id = n.activity_id
              WHERE ' . implode(' AND ', $where) . ' ORDER BY n.published_on IS NULL DESC, n.published_on DESC, n.id DESC',
            $params
        );
        // Anasayfada şu an görünen 5 haber (öne çıkan dahil)
        $onHome = array_map('intval', array_column(App::db()->all(
            "SELECT id FROM news WHERE status = 'published' AND published_on <= CURDATE() ORDER BY is_featured DESC, published_on DESC, id DESC LIMIT 5"
        ), 'id'));
        $this->render('news/index', 'Duyuru & Haber', 'news', ['rows' => $rows, 'onHome' => $onHome, 'filter' => $filter, 'filters' => self::FILTERS, 'q' => $q]);
    }

    public function create(): void
    {
        $this->requireRole('admin', 'editor');
        $old = Session::pull('admin_old', []);
        if (!$old && !empty($_GET['rapor'])) {
            $activity = App::db()->one('SELECT id, title, report_summary FROM activities WHERE id = ?', [(int) $_GET['rapor']]) ?? abort(404);
            $old = ['title' => $activity['title'] . ' — faaliyet raporu', 'category' => 'rapor', 'excerpt' => mb_substr((string) $activity['report_summary'], 0, 400), 'activity_id' => $activity['id'], 'content_mode' => 'activity', 'status' => 'draft'];
        }
        $this->render('news/form', 'Yeni Duyuru / Haber', 'news', ['n' => null, 'activities' => $this->activityOptions(), 'old' => $old]);
    }

    public function edit(string $id): void
    {
        $n = $this->find((int) $id);
        $live = News::findBySlug($n['slug']); // yalnızca yayındaysa döner
        $this->render('news/form', $n['title'], 'news', ['n' => $n, 'activities' => $this->activityOptions(), 'siteUrl' => $live['url'] ?? null]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(null);
        if ($errors) {
            $this->back(url('admin.news.new'), $errors, $_POST);
        }
        $this->replaceImage($data, 'cover', 'cover_image', null, 'news', url('admin.news.new'));
        $id = App::db()->insert('news', $data);
        Audit::log('create', 'news', $id, ['title' => $data['title']]);
        $this->flash($data['status'] === 'published' ? 'Yayınlandı.' : 'Taslak olarak kaydedildi.');
        $this->done(url('admin.news.edit', ['id' => $id]));
    }

    public function update(string $id): void
    {
        $n = $this->find((int) $id);
        [$data, $errors] = $this->validate($n);
        $back = url('admin.news.edit', ['id' => $n['id']]);
        if ($errors) {
            $this->back($back, $errors, $_POST);
        }
        $this->replaceImage($data, 'cover', 'cover_image', $n['cover_image'], 'news', $back);
        if ($data['slug'] !== $n['slug']) $this->rememberAddress('news', (int) $n['id'], $n['slug']);
        $sets = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($data)));
        App::db()->run("UPDATE news SET $sets WHERE id = :id", $data + ['id' => $n['id']]);
        Audit::log('update', 'news', (int) $n['id']);
        $this->flash('Değişiklikler kaydedildi.');
        redirect($back, 303);
    }

    public function delete(string $id): void
    {
        $n = $this->find((int) $id);
        App::db()->run('DELETE FROM news WHERE id = ?', [$n['id']]);
        Image::delete($n['cover_image']);
        Audit::log('delete', 'news', (int) $n['id'], ['title' => $n['title']]);
        $this->flash('Silindi.');
        redirect(url('admin.news'), 303);
    }

    private function find(int $id): array
    {
        return App::db()->one('SELECT * FROM news WHERE id = ?', [$id]) ?? abort(404);
    }

    private function activityOptions(): array
    {
        return App::db()->all('SELECT id, title, starts_on FROM activities ORDER BY starts_on DESC, id DESC');
    }

    /** @return array{0: array, 1: array} */
    private function validate(?array $current): array
    {
        $e = [];
        $activityId = (int) ($_POST['activity_id'] ?? 0) ?: null;
        if ($activityId && !App::db()->value('SELECT COUNT(*) FROM activities WHERE id = ?', [$activityId])) {
            $e['activity_id'] = 'Seçilen faaliyet bulunamadı.';
            $activityId = null;
        }
        [$link, $linkErr] = self::link('link_url');
        if ($linkErr) {
            $e['link_url'] = $linkErr;
        }
        $d = [
            'title'        => self::str('title', 200),
            'category'     => isset(News::CATEGORIES[$_POST['category'] ?? '']) ? $_POST['category'] : 'haber',
            'excerpt'      => self::nullable('excerpt', 400),
            'body'         => self::nullable('body', 20000),
            'activity_id'  => $activityId,
            'link_url'     => $link,
            'read_minutes' => null,
            'is_featured'  => empty($_POST['is_featured']) ? 0 : 1,
            'in_slider'    => empty($_POST['in_slider']) ? 0 : 1,
            'slider_until' => null,
            'status'       => ($_POST['status'] ?? '') === 'published' ? 'published' : 'draft',
            'published_on' => null,
        ];
        if (mb_strlen($d['title']) < 5) {
            $e['title'] = 'Başlığı yaz (en az 5 karakter).';
        }
        $mode = $_POST['content_mode'] ?? null;
        if ($mode === 'activity') {
            $d['body'] = null;
            $d['link_url'] = null;
            if (!$d['activity_id']) $e['activity_id'] = 'Yönlendirilecek faaliyeti seç.';
        } elseif ($mode === 'link') {
            $d['body'] = null;
            $d['activity_id'] = null;
            if (!$d['link_url']) $e['link_url'] = 'Bağlantı adresini yaz.';
        } elseif ($mode === 'article' && !$d['body']) {
            $e['body'] = 'Yayınlanacak metni yaz.';
        }
        $date = trim((string) ($_POST['published_on'] ?? ''));
        if ($date !== '') {
            $dt = \DateTime::createFromFormat('!Y-m-d', $date);
            if (!$dt || $dt->format('Y-m-d') !== $date) {
                $e['published_on'] = 'Geçerli bir tarih seç.';
            } else {
                $d['published_on'] = $date;
            }
        }
        $until = trim((string) ($_POST['slider_until'] ?? ''));
        if ($until !== '') {
            $dt = \DateTime::createFromFormat('!Y-m-d', $until);
            if (!$dt || $dt->format('Y-m-d') !== $until) {
                $e['slider_until'] = 'Geçerli bir tarih seç.';
            } else {
                $d['slider_until'] = $until;
            }
        }
        // Slayt görseli kapaktan gelir
        $hasCover = (!empty($_FILES['cover']['name']) && ($_FILES['cover']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE)
            || (!empty($current['cover_image']) && empty($_POST['remove_cover']));
        if ($d['in_slider'] && !$hasCover) {
            $e['in_slider'] = 'Slaytta göstermek için kapak görseli yükle.';
        }
        if ($d['status'] === 'published' && $d['published_on'] === null && !isset($e['published_on'])) {
            $d['published_on'] = date('Y-m-d'); // yayınlanırken tarih boşsa bugün
        }
        if ($d['body'] === null && $d['activity_id'] === null && $d['link_url'] === null && !isset($e['link_url'])) {
            $e['body'] = 'Haber bir yere götürmeli: metin yaz, bir faaliyet seç ya da bağlantı ver.';
        }
        $minutes = trim((string) ($_POST['read_minutes'] ?? ''));
        if ($minutes !== '') {
            if (!preg_match('/^\d{1,2}$/', $minutes) || (int) $minutes < 1) {
                $e['read_minutes'] = '1 ile 99 arasında bir sayı yaz ya da boş bırak.';
            } else {
                $d['read_minutes'] = (int) $minutes;
            }
        }

        $slug = slugify(array_key_exists('slug', $_POST) ? self::str('slug', 160) : (string) ($current['slug'] ?? '')) ?: slugify($d['title']);
        if ($slug === '') {
            $e['title'] ??= 'Başlığı yaz.';
        } elseif (App::db()->value('SELECT COUNT(*) FROM news WHERE slug = ? AND id <> ?', [$slug, $current['id'] ?? 0])) {
            $e['slug'] = 'Bu adres başka bir duyuruda kullanılıyor; farklı bir adres yaz.';
        }
        if (App::db()->value('SELECT COUNT(*) FROM content_redirects WHERE kind = ? AND slug = ? AND content_id <> ?', ['news', $slug, $current['id'] ?? 0])) {
            $e['slug'] = 'Bu adres daha önce başka bir kayda aitti; farklı bir adres yaz.';
        }
        $d['slug'] = $slug;

        if (!empty($_FILES['cover']['name']) && ($_FILES['cover']['size'] ?? 0) > Image::MAX_BYTES) {
            $e['cover'] = 'Kapak görseli en fazla 15 MB olabilir.';
        }
        return [$d, $e];
    }
}
