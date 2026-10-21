<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Auth;
use App\Core\Crypto;
use App\Core\View;
use App\Models\Activity;

/**
 * Bir faaliyetin başvuruları: durum, ulaşım, biniş noktası; yazdırılabilir listeler.
 * TC kimlik ve sağlık notu yalnızca admin'e çözülür (CLAUDE.md §15).
 */
final class ApplicationController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];

    public const PICKUPS = ['Rize Otogar', 'Pazar Merkez', 'Ardeşen Merkez', 'Çamlıhemşin Merkez', 'Ayder'];
    public const TRANSPORT = ['club' => 'Kulüp aracı', 'own' => 'Kendi aracı'];

    public function index(string $id): void
    {
        $a = $this->activity((int) $id);
        $status = in_array($_GET['durum'] ?? '', ['pending', 'approved', 'waitlist', 'rejected'], true) ? $_GET['durum'] : null;
        $rows = $this->applications((int) $a['id'], $status);
        $counts = [];
        foreach (App::db()->all('SELECT status, COUNT(*) n FROM applications WHERE activity_id = ? GROUP BY status', [$a['id']]) as $r) {
            $counts[$r['status']] = (int) $r['n'];
        }
        $this->render('activities/applications', $a['title'], 'activities', [
            'a'      => $a,
            'rows'   => $rows,
            'status' => $status,
            'counts' => $counts,
        ]);
    }

    public function update(string $id): void
    {
        $p = App::db()->one('SELECT id, activity_id, status FROM applications WHERE id = ?', [(int) $id]) ?? abort(404);
        $sets = [];
        $params = [];
        if (in_array($_POST['status'] ?? '', ['pending', 'approved', 'waitlist', 'rejected'], true)) {
            $sets[] = 'status = ?';
            $params[] = $_POST['status'];
        }
        if (array_key_exists('transport', $_POST)) {
            $sets[] = 'transport = ?';
            $params[] = array_key_exists($_POST['transport'], self::TRANSPORT) ? $_POST['transport'] : null;
            $sets[] = 'pickup = ?';
            $params[] = ($_POST['transport'] ?? '') === 'club' ? (self::nullable('pickup', 120)) : null;
        }
        if ($sets) {
            $params[] = $p['id'];
            App::db()->run('UPDATE applications SET ' . implode(', ', $sets) . ' WHERE id = ?', $params);
            Audit::log('update', 'application', (int) $p['id'], array_intersect_key($_POST, ['status' => 1, 'transport' => 1]));
        }
        $labels = ['approved' => 'Başvuru onaylandı.', 'rejected' => 'Başvuru reddedildi.', 'waitlist' => 'Yedek listeye alındı.', 'pending' => 'Başvuru beklemeye alındı.'];
        $this->flash($labels[$_POST['status'] ?? ''] ?? 'Kaydedildi.');
        redirect(url('admin.applications', ['id' => $p['activity_id']]) . '#p' . $p['id'], 303);
    }

    /** KVKK silme talebi gibi durumlar için; yalnızca admin. */
    public function delete(string $id): void
    {
        $this->requireRole('admin');
        $p = App::db()->one('SELECT id, activity_id FROM applications WHERE id = ?', [(int) $id]) ?? abort(404);
        App::db()->run('DELETE FROM applications WHERE id = ?', [$p['id']]);
        Audit::log('delete', 'application', (int) $p['id']);
        $this->flash('Başvuru ve kişisel verileri silindi.');
        redirect(url('admin.applications', ['id' => $p['activity_id']]), 303);
    }

    /** Yazdırılabilir Katılım / Taşımacılık listesi (yalnızca onaylılar). */
    public function printList(string $id, string $kind): void
    {
        $a = $this->activity((int) $id);
        if (!in_array($kind, ['katilim', 'tasima'], true)) {
            abort(404);
        }
        Audit::log('print', 'activity', (int) $a['id'], ['list' => $kind]);
        echo View::render('admin/activities/print', [
            'a'         => $a,
            'kind'      => $kind,
            'rows'      => $this->applications((int) $a['id'], 'approved'),
            'sensitive' => Auth::canSeeSensitive(),
        ], null);
    }

    private function activity(int $id): array
    {
        $row = App::db()->one(
            "SELECT a.*, (SELECT COUNT(*) FROM applications p WHERE p.activity_id = a.id AND p.status IN ('pending','approved')) AS taken
             FROM activities a WHERE a.id = ?",
            [$id]
        ) ?? abort(404);
        return Activity::decorate($row);
    }

    private function applications(int $activityId, ?string $status): array
    {
        $sql = 'SELECT * FROM applications WHERE activity_id = ?';
        $params = [$activityId];
        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        $rows = App::db()->all($sql . " ORDER BY FIELD(status, 'pending', 'approved', 'waitlist', 'rejected'), created_at", $params);
        $sensitive = Auth::canSeeSensitive();
        foreach ($rows as &$r) {
            $r['tc'] = $sensitive ? Crypto::decrypt($r['tc_enc']) : null;
            $r['health'] = $sensitive ? Crypto::decrypt($r['health_enc']) : null;
            $r['has_health'] = $r['health_enc'] !== null;
            unset($r['tc_enc'], $r['health_enc']);
        }
        return $rows;
    }
}
