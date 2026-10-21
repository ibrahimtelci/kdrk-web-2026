<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Models\Activity;

final class DashboardController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer', 'guide'];

    public function index(): void
    {
        if (\App\Core\Auth::is('guide')) { redirect(url('admin.activities')); }
        $db = App::db();
        $upcoming = Activity::upcoming();
        $this->render('dashboard', 'Ana ekran', 'dashboard', [
            'stats' => [
                'open'    => count(array_filter($upcoming, static fn ($a) => $a['can_apply'])),
                'pending' => (int) $db->value("SELECT COUNT(*) FROM applications WHERE status = 'pending'"),
                'done'    => (int) $db->value('SELECT COUNT(*) FROM activities WHERE COALESCE(ends_on, starts_on) < CURDATE() AND is_published = 1'),
                'unread'  => (int) $db->value('SELECT COUNT(*) FROM messages WHERE is_read = 0'),
            ],
            'needsReport' => $db->all("SELECT id, title FROM activities WHERE COALESCE(ends_on, starts_on) < CURDATE() AND manual_status IS NULL AND (completed_on IS NULL OR report_summary IS NULL OR report_summary = '') ORDER BY starts_on DESC LIMIT 5"),
            'next'     => array_slice($upcoming, 0, 5),
            'recent'   => $db->all(
                "SELECT p.id, p.name, p.status, p.created_at, a.id AS activity_id, a.title
                 FROM applications p JOIN activities a ON a.id = p.activity_id
                 
                 ORDER BY p.created_at DESC LIMIT 6"
            ),
            'messages' => $db->all('SELECT id, name, subject, created_at FROM messages WHERE is_read = 0 ORDER BY created_at DESC LIMIT 4'),
        ]);
    }
}
