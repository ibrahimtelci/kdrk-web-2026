<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;

/** Giriş / çıkış. BaseController'dan türemez (oturum gerektirmez). */
final class AuthController
{
    public function form(): void
    {
        if (Auth::check()) {
            redirect(url('admin.dashboard'));
        }
        header('X-Robots-Tag: noindex, nofollow');
        echo View::render('admin/login', [
            'error' => Session::pull('login_error'),
            'email' => Session::pull('login_email', ''),
            'next'  => self::safeNext($_GET['next'] ?? ''),
        ], null);
    }

    public function login(): void
    {
        Csrf::verify();
        $email = trim((string) ($_POST['email'] ?? ''));
        $next = self::safeNext($_POST['next'] ?? '');
        $result = Auth::attempt($email, (string) ($_POST['password'] ?? ''));
        if ($result === 'ok') {
            redirect($next !== '' ? $next : url('admin.dashboard'), 303);
        }
        Session::flash('login_email', $email);
        Session::flash('login_error', $result === 'locked'
            ? 'Çok fazla hatalı deneme yapıldı. 15 dakika sonra tekrar dene.'
            : 'E-posta veya parola hatalı.');
        redirect(url('admin.login', $next !== '' ? ['next' => $next] : []), 303);
    }

    public function logout(): void
    {
        Csrf::verify();
        Auth::logout();
        redirect(url('admin.login'), 303);
    }

    /** Açık yönlendirme açığını önler: yalnızca panel içi yollar. */
    private static function safeNext(mixed $next): string
    {
        $next = (string) $next;
        return preg_match('#^/[^/\\\\]#', $next) && str_contains($next, '/yonetim') ? $next : '';
    }
}
