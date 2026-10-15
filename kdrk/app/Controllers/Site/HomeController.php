<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\View;
use App\Models\Activity;

final class HomeController
{
    public function index(): void
    {
        $db = App::db();
        // En fazla 3 slayt; tarihi geçen duyuru slaytı kendiliğinden kalkar (CLAUDE.md §4)
        $banners = $db->all(
            'SELECT * FROM banners
             WHERE is_active = 1
               AND (show_from IS NULL OR show_from <= CURDATE())
               AND (show_until IS NULL OR show_until >= CURDATE())
             ORDER BY sort, id LIMIT 3'
        );
        $news = $db->all(
            "SELECT * FROM news WHERE status = 'published' AND published_on <= CURDATE()
             ORDER BY is_featured DESC, published_on DESC, id DESC LIMIT 5"
        );

        echo View::render('pages/home', [
            'banners'  => $banners,
            'upcoming' => Activity::upcoming(3),
            'news'     => $news,
            'page'     => [
                'description' => "1998'den beri Kaçkar'da. Doğa yürüyüşlerinden zirve tırmanışlarına, eğitim programlarından arama-kurtarma çalışmalarına KDRK.",
                'css'         => ['home.css'],
                'nav'         => 'home',
                'current'     => 'home',
            ],
        ]);
    }
}
