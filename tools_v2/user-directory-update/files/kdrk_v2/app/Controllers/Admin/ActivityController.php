<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Auth;
use App\Core\Image;
use App\Core\Session;
use App\Models\Activity;

final class ActivityController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer', 'guide'];

    private const FILTERS = ['yaklasan' => 'Yaklaşan', 'bekleyen' => 'Bekleyen başvurusu olan', 'gecmis' => 'Geçmiş', 'tumu' => 'Tümü'];

    public function index(): void
    {
        $filter = array_key_exists($_GET['durum'] ?? '', self::FILTERS) ? $_GET['durum'] : 'yaklasan';
        $q = trim((string) ($_GET['q'] ?? ''));
        $where = ['1 = 1'];
        $params = [];
        if (Auth::is('guide')) {
            $where[] = '(a.guide_primary_user_id = ? OR a.guide_secondary_user_id = ?)';
            $params = [(int) Auth::user()['id'], (int) Auth::user()['id']];
        }
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
        $old = Session::pull('admin_old', []);
        if (!$old && isset($_GET['kopya'])) {
            $source = $this->find((int) $_GET['kopya']);
            $keys = ['title', 'category', 'location', 'summary', 'body', 'difficulty_score', 'distance_km', 'quota', 'fee', 'meeting_text', 'lat', 'lng', 'gear_extra', 'guide_primary_user_id', 'guide_secondary_user_id'];
            $old = array_intersect_key($source, array_flip($keys));
            $old['title'] = $source['title'] . ' — yeni';
            $old['no_difficulty'] = $source['score'] === null ? '1' : '';
            $old['difficulty_score'] = $source['score'] ?? 3;
            $old['is_published'] = 0;
        }
        $this->render('activities/form', 'Yeni Faaliyet', 'activities', ['a' => null, 'old' => $old]);
    }

    public function edit(string $id): void
    {
        $a = $this->find((int) $id);
        if (Auth::is('guide')) {
            $this->render('activities/assigned', $a['title'], 'activities', ['a' => $a]);
            return;
        }
        $albumId = App::db()->value('SELECT id FROM gallery_albums WHERE activity_id = ? ORDER BY id LIMIT 1', [$a['id']]);
        $this->render('activities/form', $a['title'], 'activities', ['a' => $a, 'albumId' => $albumId !== null ? (int) $albumId : null]);
    }

    public function store(): void
    {
        $this->requireRole('admin', 'editor');
        [$data, $errors] = $this->validate(null);
        if ($errors) {
            $this->back(url('admin.activities.new'), $errors, $_POST);
        }
        $this->handleCover($data, null);
        $id = App::db()->insert('activities', $data);
        Audit::log('create', 'activity', $id, ['title' => $data['title']]);
        $this->flash($data['is_published'] ? 'Faaliyet eklendi. Sitede yayında.' : 'Faaliyet eklendi; yayında değil.');
        $this->done(url('admin.activities.edit', ['id' => $id]));
    }

    public function update(string $id): void
    {
        $this->requireRole('admin', 'editor');
        $a = $this->find((int) $id);
        [$data, $errors] = $this->validate($a);
        if ($errors) {
            $this->back(url('admin.activities.edit', ['id' => $a['id']]), $errors, $_POST);
        }
        if ($data['slug'] !== $a['slug']) {
            $this->rememberAddress('activity', (int) $a['id'], $a['slug']);
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
        $this->requireRole('admin', 'editor');
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
        \App\Core\ActivityAccess::requireAssigned($row);
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

        // Sade form: program, malzeme listesi, sorumlu ve "bilmen gerekenler" alanları artık formda yok;
        // eski değerleri veritabanında kalır ama sitede gösterilmez. Malzeme notu kategoriden, ödeme notu genel ayardan gelir.
        $noScore = !empty($_POST['no_difficulty']);
        $score = $noScore ? null : $int('difficulty_score', 1, 10);
        $category = (string) ($_POST['category'] ?? '');
        $d = [
            'title'               => self::str('title', 160),
            'category'            => $category,
            'starts_on'           => $date('starts_on', true),
            'ends_on'             => $date('ends_on', false),
            'location'            => self::str('location', 160),
            'lat'                 => null,
            'lng'                 => null,
            'summary'             => self::str('summary', 300),
            'body'                => self::nullable('body', 20000),
            'difficulty_score'    => $score,
            'difficulty'          => $score !== null ? Activity::bandOf($score) : null,
            'is_training'         => $category === 'egitim' ? 1 : 0,
            'distance_km'         => null,
            'quota'               => $int('quota', 0, 5000) ?? 0,
            'fee'                 => null,
            'meeting_text'        => self::nullable('meeting_text'),
            'gear_extra'          => self::nullable('gear_extra', 3000),
            'manual_status'       => in_array($_POST['manual_status'] ?? '', ['closed', 'postponed', 'cancelled'], true) ? $_POST['manual_status'] : null,
            'completed_on'        => !empty($_POST['is_completed']) ? ($current['completed_on'] ?? date('Y-m-d')) : null,
            'report_summary'      => self::nullable('report_summary', 5000),
            'report_participants' => $int('report_participants', 0, 5000),
            'is_published'        => empty($_POST['is_published']) ? 0 : 1,
            'in_slider'           => empty($_POST['in_slider']) ? 0 : 1,
        ];
        if ($current !== null && !array_key_exists('manual_status', $_POST)) {
            // Sihirbazda durum/rapor alanları yok; olanlar korunur
            unset($d['manual_status'], $d['report_summary'], $d['report_participants'], $d['completed_on']);
        }

        foreach (['guide_primary_user_id', 'guide_secondary_user_id'] as $key) {
            // Eski istemciler alanları göndermiyorsa bağlantıyı koru.
            $d[$key] = array_key_exists($key, $_POST) ? $int($key, 1, PHP_INT_MAX) : ($current[$key] ?? null);
            if ($d[$key]) {
                $person = App::db()->one("SELECT id, is_active, phone, role FROM users WHERE id = ?", [$d[$key]]);
                $unchanged = $current && in_array((int) $d[$key], [(int) ($current['guide_primary_user_id'] ?? 0), (int) ($current['guide_secondary_user_id'] ?? 0)], true);
                if (!$person || (!$unchanged && (!$person['is_active'] || !$person['phone'] || !in_array($person['role'], ['admin', 'editor', 'guide'], true)))) $e[$key] = 'Telefonu tanımlı, aktif bir görevli seç.';
            }
            if ($d['is_published'] && array_key_exists($key, $_POST) && !$d[$key]) { $e[$key] = 'Yayınlamak için iki rehber seç.'; }
        }
        if ($d['guide_primary_user_id'] && $d['guide_primary_user_id'] === $d['guide_secondary_user_id']) { $e['guide_secondary_user_id'] = 'İkinci rehber farklı bir kişi olmalı.'; }

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
        if (!empty($d['completed_on']) && (!$d['starts_on'] || ($d['ends_on'] ?: $d['starts_on']) >= date('Y-m-d'))) {
            $e['is_completed'] = 'Tamamlandı olarak işaretlemek için faaliyetin tarihi geçmiş olmalı.';
        }
        if ($d['ends_on'] === $d['starts_on']) {
            $d['ends_on'] = null;
        }
        if (!$noScore && $score === null && !isset($e['difficulty_score'])) {
            $e['difficulty_score'] = 'Zorluk derecesini seç ya da "Zorluk derecesi yok" kutusunu işaretle.';
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
        // Haritadan konum: ikisi birlikte gelir ya da hiç gelmez
        $lat = trim((string) ($_POST['lat'] ?? ''));
        $lng = trim((string) ($_POST['lng'] ?? ''));
        if ($lat !== '' || $lng !== '') {
            if (!is_numeric($lat) || !is_numeric($lng) || abs((float) $lat) > 90 || abs((float) $lng) > 180) {
                $e['lat'] = 'Haritadaki konum okunamadı; yeniden seç ya da kaldır.';
            } else {
                $d['lat'] = round((float) $lat, 6);
                $d['lng'] = round((float) $lng, 6);
            }
        }

        // Adres: yeni faaliyette başlıktan + yıl; düzenlemede elle değiştirilebilir
        $slug = slugify(array_key_exists('slug', $_POST) ? self::str('slug', 160) : (string) ($current['slug'] ?? ''));
        $auto = $slug === '';
        if ($auto) {
            $year = $d['starts_on'] ? substr($d['starts_on'], 0, 4) : date('Y');
            $slug = slugify(str_contains($d['title'], $year) ? $d['title'] : $d['title'] . ' ' . $year);
        }
        $taken = static fn (string $s): bool => (bool) App::db()->value('SELECT COUNT(*) FROM activities WHERE slug = ? AND id <> ?', [$s, $current['id'] ?? 0]);
        if ($auto && $slug !== '') {
            // Kendiliğinden üretilen adres çakışırsa sona -2, -3… eklenir (sihirbazda adres alanı yok)
            for ($n = 2, $base = $slug; $taken($slug); $n++) {
                $slug = mb_substr($base, 0, 150) . '-' . $n;
            }
        } elseif ($taken($slug)) {
            $e['slug'] = 'Bu adres başka bir faaliyette kullanılıyor; farklı bir adres yaz.';
        }
        if (App::db()->value('SELECT COUNT(*) FROM content_redirects WHERE kind = ? AND slug = ? AND content_id <> ?', ['activity', $slug, $current['id'] ?? 0])) {
            $e['slug'] = 'Bu adres daha önce başka bir kayda aitti; farklı bir adres yaz.';
        }
        $d['slug'] = $slug;

        if (!empty($_FILES['cover']['name']) && ($_FILES['cover']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            if (($_FILES['cover']['size'] ?? 0) > Image::MAX_BYTES || ($_FILES['cover']['error'] ?? 0) === UPLOAD_ERR_INI_SIZE) {
                $e['cover'] = 'Kapak görseli en fazla 15 MB olabilir.';
            }
        }
        // Slayt görseli kapaktan gelir
        $hasCover = (!empty($_FILES['cover']['name']) && ($_FILES['cover']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE)
            || (!empty($current['cover_image']) && empty($_POST['remove_cover']));
        if ($d['in_slider'] && !$hasCover) {
            $e['in_slider'] = 'Slaytta göstermek için kapak görseli yükle.';
        }
        return [$d, $e];
    }

    private function handleCover(array &$d, ?array $current): void
    {
        $old = $current['cover_image'] ?? null;
        if (!empty($_FILES['cover']['name']) && ($_FILES['cover']['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            try {
                $d['cover_image'] = Image::store($_FILES['cover'], 'activities', Image::parseCrop($_POST['cover_crop'] ?? null));
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
