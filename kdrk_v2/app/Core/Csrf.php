<?php
declare(strict_types=1);

namespace App\Core;

/** Her POST formunda zorunlu (CLAUDE.md §15). */
final class Csrf
{
    public static function token(): string
    {
        $t = Session::get('_csrf');
        if (!is_string($t)) {
            $t = bin2hex(random_bytes(32));
            Session::set('_csrf', $t);
        }
        return $t;
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . e(self::token()) . '">';
    }

    public static function verify(): void
    {
        $sent = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $known = Session::get('_csrf');
        if (!is_string($sent) || !is_string($known) || !hash_equals($known, $sent)) {
            throw new HttpException(419);
        }
    }
}
