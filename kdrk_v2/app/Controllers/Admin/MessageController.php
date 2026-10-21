<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;

final class MessageController extends BaseController
{
    protected array $roles = ['admin', 'editor'];

    public function index(): void
    {
        $unreadOnly = ($_GET['filtre'] ?? '') === 'okunmamis';
        $rows = App::db()->all(
            'SELECT id, name, email, subject, LEFT(body, 140) AS preview, is_read, created_at FROM messages'
            . ($unreadOnly ? ' WHERE is_read = 0' : '') . ' ORDER BY created_at DESC LIMIT 200'
        );
        $this->render('messages/index', 'İletişim Mesajları', 'messages', ['rows' => $rows, 'unreadOnly' => $unreadOnly]);
    }

    public function show(string $id): void
    {
        $m = App::db()->one('SELECT * FROM messages WHERE id = ?', [(int) $id]) ?? abort(404);
        if (!$m['is_read']) {
            App::db()->run('UPDATE messages SET is_read = 1 WHERE id = ?', [$m['id']]);
        }
        $this->render('messages/show', $m['subject'] ?: 'Mesaj', 'messages', ['m' => $m]);
    }

    public function unread(string $id): void
    {
        App::db()->run('UPDATE messages SET is_read = 0 WHERE id = ?', [(int) $id]);
        $this->flash('Okunmadı olarak işaretlendi.');
        redirect(url('admin.messages'), 303);
    }

    public function delete(string $id): void
    {
        App::db()->run('DELETE FROM messages WHERE id = ?', [(int) $id]);
        Audit::log('delete', 'message', (int) $id);
        $this->flash('Mesaj silindi.');
        redirect(url('admin.messages'), 303);
    }
}
