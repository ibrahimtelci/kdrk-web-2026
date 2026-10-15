<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Auth;
use App\Core\Image;
use App\Models\Activity;

final class ActivityController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];

    private const FILTERS = ['yaklasan' => 'Yaklaşan', 'bekleyen' => 'Bekleyen başvurusu olan', 'gecmis' => 'Geçmiş', 'tumu' => 'Tümü'];

    public function index(): void
    {
        $filter = array_key_exists($_GET['durum'] ?? '', self::FILTERS) ? $_GET['durum'] : 'yaklasan';
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = ['1 = 1'];
        $params = [];
        if ($filter === 'yaklasan') {
            $where[] = 'COALESCE(a.ends_on, a.starts_on) >= CURDATE()';
        } elseif ($filter === 'gecmis') {
            $where[] = 'COALESCE(a.ends_on, a.starts_on) < CURDATE()';
        } elseif ($filter === 'bekleyen') {
            $where[] = "EXISTS (SELECT 1 FROM applications x WHERE x.activity_id = a.id AND x.status = 'pending')";
        }
        if ($q !== '') {
            $where[] = '(a.title LIKE ? OR a.location LIKE ?)';
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        $rows = App::db()->all(
            "SELECT a.*,
               (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status IN ('pending','approved')) AS taken,
               (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status = 'pending') AS pending_count,
               (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id) AS app_count
             FROM activities a WHERE " . implode(' AND ', $where) . '
             ORDER BY ' . ($filter === 'gecmis' ? 'a.starts_on DESC' : 'a.starts_on ASC') . ', a.id',
            $params
        );
        $this->render('activities/index', 'Faaliyetler', 'activities', [
            'rows'    => array_map([Activity::class, 'decorate'], $rows),
            'filter'  => $filter,
            'filters' => self::FILTERS,
            'q'       => $q,
        ]);
    }

    public function create(): void
    {
        $this->requireRole('admin', 'editor');
        $this->render('activities/form', 'Yeni Faaliyet', 'activities', ['a' => null]);
    }

    public function edit(string $id): void
    {
        $a = $this->find((int) $id);
        $albumId = App::db()->value('SELECT id FROM gallery_albums WHERE activity_id = ? ORDER BY id LIMIT 1', [$a['id']]);
        $this->render('activities/form', $a['title'], 'activities', ['a' => $a, 'albumId' => $albumId !== null ? (int) $albumId : null]);
    }

    public function store(): void
    {
        [$data, $errors] = $this->validate(null);
        if ($errors) {
            $this->back(url('admin.activities.new'), $errors, $_POST);
        }
        $this->handleCover($data, null);
        $id = App::db()->insert('activities', $data);
        Audit::log('create', 'activity', $id, ['title' => $data['title']]);
        $this->flash('Faaliyet eklendi. Sitede yayında.');
        redirect(url('admin.activities.edit', ['id' => $id]), 303);
    }

    public function update(string $id): void
    {
        $a = $this->find((int) $id);
        [$data, $errors] = $this->validate($a);
        if ($errors) {
            $this->back(url('admin.activities.edit', ['id' => $a['id']]), $errors, $_POST);
        }
        $this->handleCover($data, $a);
        $sets = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($data)));
        App::db()->run("UPDATE activities SET $sets WHERE id = :id", $data + ['id' => $a['id']]);
        Audit::log('update', 'activity', (int) $a['id']);
        $this->flash('Değişiklikler kaydedildi.');
        redirect(url('admin.activities.edit', ['id' => $a['id']]), 303);
    }

    public function delete(string $id): void
    {
        $a = $this->find((int) $id);
        $apps = (int) App::db()->value('SELECT COUNT(*) FROM applications WHERE activity_id = ?', [$a['id']]);
        if ($apps > 0) {
            $this->flash("Bu faaliyetin $apps başvurusu var; silinemez. Sitede görünmesin istiyorsan \"Sitede yayında\" seçeneğini kapat.", 'error');
            redirect(url('admin.activities.edit', ['id' => $a['id']]), 303);
        }
        App::db()->run('DELETE FROM activities WHERE id = ?', [$a['id']]);
        Image::delete($a['cover_image']);
        Audit::log('delete', 'activity', (int) $a['id'], ['title' => $a['title']]);
        $this->flash('Faaliyet silindi.');
        redirect(url('admin.activities'), 303);
    }

    /** Ham satır + türetilmiş alanlar (yayında olmayanlar dahil). */
    private function find(int $id): array
    {
        $row = App::db()->one(
            "SELECT a.*, (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status IN ('pending','approved')) AS taken
             FROM activities a WHERE a.id = ?",
            [$id]
        ) ?? abort(404);
        return Activity::decorate($row);
    }

    /** @return array{0: array, 1: array} [veritabanı satırı, alan => hata] */
    private function validate(?array $current): array
    {
        $e = [];
        $int = static function (string $k, int $min, int $max) use (&$e): ?int {
            $v = trim((string) ($_POST[$k] ?? ''));
            if ($v === '') {
                return null;
            }
            if (!preg_match('/^\d+$/', $v) || (int) $v < $min || (int) $v > $max) {
                $e[$k] = "$min ile $max arasında bir sayı yaz.";
                return null;
            }
            return (int) $v;
        };
        $date = static function (string $k, bool $required) use (&$e): ?string {
            $v = trim((string) ($_POST[$k] ?? ''));
            if ($v === '') {
                if ($required) {
                    $e[$k] = 'Tarih seç.';
                }
                return null;
            }
            $d = \DateTime::createFromFormat('!Y-m-d', $v);
            if (!$d || $d->format('Y-m-d') !== $v) {
                $e[$k] = 'Geçerli bir tarih seç.';
                return null;
            }
            return $v;
        };

        $d = [
            'title'            => self::str('title', 160),
            'category'         => (string) ($_POST['category'] ?? ''),
            'starts_on'        => $date('starts_on', true),
            'ends_on'          => $date('ends_on', false),
            'location'         => self::str('location', 160),
            'summary'          => self::str('summary', 300),
            'body'             => self::nullable('body', 20000),
            'difficulty'       => $int('difficulty', 1, 4),
            'is_training'      => empty($_POST['is_training']) ? 0 : 1,
            'duration_text'    => self::nullable('duration_text', 60),
            'summit_m'         => $int('summit_m', 1, 9000),
            'distance_km'      => null,
            'elevation_gain_m' => $int('elevation_gain_m', 1, 9000),
            'quota'            => $int('quota', 0, 5000) ?? 0,
            'fee'              => null,
            'fee_note'         => self::nullable('fee_note'),
            'meeting_text'     => self::nullable('meeting_text'),
            'return_text'      => self::nullable('return_text'),
            'included_text'    => self::nullable('included_text'),
            'personal_text'    => self::nullable('personal_text'),
            'requirement_text' => self::nullable('requirement_text', 2000),
            'weather_text'     => self::nullable('weather_text'),
            'leader_name'      => self::nullable('leader_name', 120),
            'leader_title'     => self::nullable('leader_title', 160),
            'manual_status'    => in_array($_POST['manual_status'] ?? '', ['closed', 'postponed', 'cancelled'], true) ? $_POST['manual_status'] : null,
            'report_summary'   => self::nullable('report_summary', 5000),
            'report_participants' => $int('report_participants', 0, 5000),
            'is_published'     => empty($_POST['is_published']) ? 0 : 1,
        ];

        if (mb_strlen($d['title']) < 3) {
            $e['title'] = 'Faaliyet adını yaz.';
        }
        if (!isset(Activity::CATEGORIES[$d['category']])) {
            $e['category'] = 'Kategori seç.';
        }
        if ($d['location'] === '') {
            $e['location'] = 'Yer bilgisini yaz (ör. Rize / Çamlıhemşin).';
        }
        if (mb_strlen($d['summary']) < 10) {
            $e['summary'] = 'Kartta görünecek kısa açıklamayı yaz (en az 10 karakter).';
        }
        if ($d['starts_on'] && $d['ends_on'] && $d['ends_on'] < $d['starts_on']) {
            $e['ends_on'] = 'Bitiş tarihi başlangıçtan önce olamaz.';
        }
        if ($d['ends_on'] === $d['starts_on']) {
            $d['ends_on'] = null;
        }
        if ($d['difficulty'] === null && !$d['is_training'] && !isset($e['difficulty'])) {
            $e['difficulty'] = 'Zorluk seç (eğitimlerde boş bırakılabilir).';
        }
        foreach (['distance_km' => [0.1, 999], 'fee' => [0, 1000000]] as $k => [$min, $max]) {
            $v = str_replace(',', '.', trim((string) ($_POST[$k] ?? '')));
            if ($v === '') {
                continue;
            }
            if (!is_numeric($v) || (float) $v < $min || (float) $v > $max) {
                $e[$k] = 'Geçerli bir sayı yaz.';
            } else {
                $d[$k] = round((float) $v, $k === 'fee' ? 2 : 1);
            }
        }

        // Program: panel.js gün/saat düzenleyicisinden JSON gelir
        $program = json_decode((string) ($_POST['program'] ?? '[]'), true);
        $clean = [];
        if (is_array($program)) {
            foreach ($program as $day) {
                $items = [];
                foreach ((array) ($day['items'] ?? []) as $it) {
                    $text = mb_substr(trim((string) ($it['text'] ?? '')), 0, 200);
                    $time = preg_match('/^\d{1,2}[:.]\d{2}$/', trim((string) ($it['time'] ?? ''))) ? str_replace('.', ':', trim($it['time'])) : '';
                    if ($text !== '') {
                        $items[] = ['time' => $time, 'text' => $text];
                    }
                }
                $title = mb_substr(trim((string) ($day['title'] ?? '')), 0, 120);
                $dayDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($day['date'] ?? '')) ? $day['date'] : null;
                if ($title !== '' || $items) {
                    $clean[] = ['title' => $title, 'date' => $dayDate, 'items' => $items];
                }
            }
        }
        $d['program'] = $clean ? json_encode($clean, JSON_UNESCAPED_UNICODE) : null;

        // Malzeme listesi: her satır bir madde
        $gear = array_values(array_filter(array_map(
            static fn ($l) => mb_substr(trim($l), 0, 120),
            preg_split('/\R/', (string) ($_POST['equipment'] ?? '')) ?: []
        )));
        $d['equipment'] = $gear ? json_encode($gear, JSON_UNESCAPED_UNICODE) : null;

        // Adres: yeni faaliyette başlıktan + yıl; düzenlemede elle değiştirilebilir
        $slug = slugify(self::str('slug', 160));
        if ($slug === '') {
            $year = $d['starts_on'] ? substr($d['starts_on'], 0, 4) : date('Y');
            $slug = slugify(str_contains($d['title'], $year) ? $d['title'] : $d['title'] . ' ' . $year);
        }
        $taken = App::db()->value('SELECT COUNT(*) FROM activities WHERE slug = ? AND id <> ?', [$slug, $current['id'] ?? 0]);
        if ($taken) {
            $e['slug'] = 'Bu adres başka bir faaliyette kullanılıyor; farklı bir adres yaz.';
        }
        $d['slug'] = $slug;

        if (!empty($_FILES['cover']['name']) && ($_FILES['cover']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            if (($_FILES['cover']['size'] ?? 0) > Image::MAX_BYTES || ($_FILES['cover']['error'] ?? 0) === UPLOAD_ERR_INI_SIZE) {
                $e['cover'] = 'Kapak görseli en fazla 15 MB olabilir.';
            }
        }
        return [$d, $e];
    }

    private function handleCover(array &$d, ?array $current): void
    {
        $old = $current['cover_image'] ?? null;
        if (!empty($_FILES['cover']['name']) && ($_FILES['cover']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            try {
                $d['cover_image'] = Image::store($_FILES['cover'], 'activities');
            } catch (\RuntimeException $ex) {
                $back = $current ? url('admin.activities.edit', ['id' => $current['id']]) : url('admin.activities.new');
                $this->back($back, ['cover' => $ex->getMessage()], $_POST);
            }
            Image::delete($old);
        } elseif (!empty($_POST['remove_cover'])) {
            $d['cover_image'] = null;
            Image::delete($old);
        }
    }
}
