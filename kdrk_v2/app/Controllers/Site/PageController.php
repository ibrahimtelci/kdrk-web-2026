<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Session;
use App\Core\View;

/** İçeriği şablonda duran sayfalar (app/pages.php). */
final class PageController
{
    public function show(string $name): void
    {
        [, $view, $title, $description, $css, $navLight, $group] = (require KDRK_ROOT . '/app/pages.php')[$name];
        echo View::render($view, [
            // Form içeren sayfalar (İletişim) JS kapalıyken dönen hata/değerleri buradan okur
            'old'    => Session::pull('form_old', []),
            'errors' => Session::pull('form_errors', []),
            'sent'   => (bool) Session::pull('form_ok', false),
            'page'   => [
            'title'       => $title,
            'description' => $description,
            'css'         => $css,
            'navLight'    => $navLight,
            'nav'         => $group,
            'current'     => $name,
        ]]);
    }
}
