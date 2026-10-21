<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\App;
use App\Core\Audit;
use App\Core\Auth;

/** Panel kullanıcıları — yalnızca admin. */
final class UserController extends BaseController
{
    protected array $roles = ['admin'];
    public const MIN_PASSWORD = 10;

    public function index(): void
    {
        $rows = App::db()->all('SELECT id, name, email, phone, role, is_active, last_login_at FROM users ORDER BY is_active DESC, name');
        $this->render('users/index', 'Kullanıcılar', 'users', ['rows' => $rows]);
    }

    public function create(): void
    {
        $this->render('users/form', 'Yeni Kullanıcı', 'users', ['u' => null]);
    }

    public function edit(string $id): void
    {
        $u = App::db()->one('SELECT id, name, email, phone, role, is_active FROM users WHERE id = ?', [(int) $id]) ?? abort(404);
        $this->render('users/form', $u['name'], 'users', ['u' => $u]);
    }

    public function store(): void
    {
        [$d, $e] = $this->validate(null);
        if ($e) {
            $this->back(url('admin.users.new'), $e, array_diff_key($_POST, ['password' => 1]));
        }
        $id = App::db()->insert('users', $d);
        Audit::log('create', 'user', $id, ['role' => $d['role']]);
        $this->flash('Kullanıcı eklendi. Parolayı kişiye güvenli bir yoldan ilet; ilk girişte değiştirmesini iste.');
        $this->done(url('admin.users'));
    }

    public function update(string $id): void
    {
        $u = App::db()->one('SELECT id, role, password_hash FROM users WHERE id = ?', [(int) $id]) ?? abort(404);
        [$d, $e] = $this->validate($u);
        if ($e) {
            $this->back(url('admin.users.edit', ['id' => $u['id']]), $e, array_diff_key($_POST, ['password' => 1]));
        }
        $sets = implode(', ', array_map(static fn ($c) => "`$c` = :$c", array_keys($d)));
        App::db()->run("UPDATE users SET $sets WHERE id = :id", $d + ['id' => $u['id']]);
        Audit::log('update', 'user', (int) $u['id'], ['role' => $d['role'], 'password_changed' => isset($d['password_hash'])]);
        $this->flash('Kullanıcı güncellendi.');
        redirect(url('admin.users'), 303);
    }

    private function validate(?array $current): array
    {
        $e = [];
        $self = $current !== null && (int) $current['id'] === (int) Auth::user()['id'];
        $d = [
            'name'      => self::str('name', 120),
            'email'     => mb_strtolower(self::str('email', 190)) ?: null,
            'phone'     => \App\Models\GuideContact::normalizePhone(self::str('phone', 40)),
            'role'      => array_key_exists($_POST['role'] ?? '', Auth::ROLES) ? $_POST['role'] : '',
            'is_active' => empty($_POST['is_active']) ? 0 : 1,
        ];
        if (mb_strlen($d['name']) < 3) {
            $e['name'] = 'Ad ve soyadı yaz.';
        }
        if (($d['is_active'] || $d['email'] !== null) && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $e['email'] = 'Geçerli bir e-posta adresi yaz.';
        } elseif (App::db()->value('SELECT COUNT(*) FROM users WHERE email = ? AND id <> ?', [$d['email'], $current['id'] ?? 0])) {
            $e['email'] = 'Bu e-posta ile başka bir kullanıcı var.';
        }
        if (self::str('phone', 40) !== '' && $d['phone'] === null) $e['phone'] = 'Geçerli cep telefonu yaz. Ör. 0532 123 45 67';
        if ($current && $d['phone'] === null && App::db()->value('SELECT id FROM activities WHERE guide_primary_user_id = ? OR guide_secondary_user_id = ? LIMIT 1', [$current['id'], $current['id']])) $e['phone'] = 'Faaliyete atanmış kullanıcının telefonu boş bırakılamaz.';
        if ($d['role'] === 'guide' && $d['phone'] === null) $e['phone'] = 'Görevli için cep telefonu gerekli.';
        if ($d['role'] === '') {
            $e['role'] = 'Rol seç.';
        }
        // Kendini kilitleme: kendi rolünü düşüremez, kendini pasif yapamaz
        if ($self && ($d['role'] !== 'admin' || !$d['is_active'])) {
            $e['role'] = 'Kendi hesabının admin yetkisini kaldıramaz veya kendini pasif yapamazsın.';
        }
        // Son aktif admin korunur
        if ($current && $current['role'] === 'admin' && ($d['role'] !== 'admin' || !$d['is_active'])
            && (int) App::db()->value("SELECT COUNT(*) FROM users WHERE role = 'admin' AND is_active = 1 AND id <> ?", [$current['id']]) === 0) {
            $e['role'] = 'Sistemde en az bir aktif admin kalmalı.';
        }
        $pw = (string) ($_POST['password'] ?? '');
        if (($d['is_active'] && empty($current['password_hash'])) || $pw !== '') {
            if (mb_strlen($pw) < self::MIN_PASSWORD) {
                $e['password'] = 'Parola en az ' . self::MIN_PASSWORD . ' karakter olmalı.';
            } else {
                $d['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
            }
        }
        if ($current === null && !isset($d['password_hash'])) $d['password_hash'] = '';
        return [$d, $e];
    }
}
