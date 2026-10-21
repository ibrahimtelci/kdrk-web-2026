<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Auth;
use App\Core\Session;

/** Kendi parolasını değiştirme — tüm roller (İzleme dahil). */
final class AccountController extends BaseController
{
    protected array $roles = ['admin', 'editor', 'viewer'];
    protected bool $viewerCanPost = true;

    public function index(): void
    {
        $this->render('account', 'Hesabım', 'account', ['u' => Auth::user()]);
    }

    public function update(): void
    {
        $u = Auth::user();
        $row = App::db()->one('SELECT password_hash FROM users WHERE id = ?', [$u['id']]);
        $e = [];
        if (!password_verify((string) ($_POST['current'] ?? ''), $row['password_hash'])) {
            $e['current'] = 'Mevcut parola hatalı.';
        }
        $new = (string) ($_POST['password'] ?? '');
        if (mb_strlen($new) < UserController::MIN_PASSWORD) {
            $e['password'] = 'Yeni parola en az ' . UserController::MIN_PASSWORD . ' karakter olmalı.';
        } elseif ($new !== (string) ($_POST['password_confirm'] ?? '')) {
            $e['password_confirm'] = 'Parolalar aynı değil.';
        }
        if ($e) {
            $this->back(url('admin.account'), $e, []);
        }
        App::db()->run('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
        Session::regenerate();
        Audit::log('password_change', 'user', (int) $u['id']);
        $this->flash('Parolan değiştirildi.');
        redirect(url('admin.account'), 303);
    }
}
