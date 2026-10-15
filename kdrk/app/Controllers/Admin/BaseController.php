<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Session;
use App\Core\View;

/**
 * Tüm panel controller'larının tabanı.
 * - Oturum yoksa giriş sayfasına yönlendirir
 * - Her POST'ta CSRF doğrular; İzleme rolü hiçbir POST yapamaz
 * - Varsayılan erişim REDDEDİLİR: her controller $roles ile kimin girebileceğini açıkça söyler (CLAUDE.md §15)
 */
abstract class BaseController
{
    /** Bu controller'a erişebilen roller. */
    protected array $roles = ['admin'];

    /** İzleme rolünün POST yapabileceği tek yer kendi hesabıdır (parola değişikliği). */
    protected bool $viewerCanPost = false;

    public function __construct()
    {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store');
        if (!Auth::check()) {
            $next = $_SERVER['REQUEST_METHOD'] === 'GET' ? ($_SERVER['REQUEST_URI'] ?? '') : '';
            redirect(url('admin.login', $next !== '' ? ['next' => $next] : []));
        }
        if (!Auth::is(...$this->roles)) {
            throw new HttpException(403);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            if (!Auth::canEdit() && !$this->viewerCanPost) {
                throw new HttpException(403);
            }
        }
    }

    /** @param string $active kenar çubuğunda işaretlenecek modül */
    protected function render(string $view, string $title, string $active, array $data = []): void
    {
        echo View::render('admin/' . $view, $data + [
            'title'  => $title,
            'active' => $active,
            'flash'  => Session::pull('admin_flash'),
            'errors' => Session::pull('admin_errors', []),
            'old'    => Session::pull('admin_old', []),
        ], 'layouts/admin');
    }

    protected function flash(string $message, string $type = 'ok'): void
    {
        Session::flash('admin_flash', ['type' => $type, 'message' => $message]);
    }

    /** Form hatasıyla geri dön: girilen değerler korunur. */
    protected function back(string $to, array $errors, array $old): never
    {
        Session::flash('admin_errors', $errors);
        Session::flash('admin_old', $old);
        $this->flash('Formda düzeltilmesi gereken alanlar var.', 'error');
        redirect($to, 303);
    }

    protected function requireRole(string ...$roles): void
    {
        if (!Auth::is(...$roles)) {
            throw new HttpException(403);
        }
    }

    protected static function str(string $key, int $max = 255): string
    {
        return mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
    }

    protected static function nullable(string $key, int $max = 255): ?string
    {
        $v = self::str($key, $max);
        return $v === '' ? null : $v;
    }
}
