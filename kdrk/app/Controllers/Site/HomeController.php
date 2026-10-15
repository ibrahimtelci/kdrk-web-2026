<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\View;
use App\Models\Activity;
use App\Models\Banner;
use App\Models\News;

final class HomeController
{
    public function index(): void
    {
        echo View::render('pages/home', [
            // En fazla 3 slayt; tarihi geçen duyuru slaytı kendiliğinden kalkar (CLAUDE.md §4)
            'banners'  => Banner::visible(),
            'upcoming' => Activity::upcoming(3),
            'news'     => News::latest(5),
            'page'     => [
                'description' => "1998'den beri Kaçkar'da. Doğa yürüyüşlerinden zirve tırmanışlarına, eğitim programlarından arama-kurtarma çalışmalarına KDRK.",
                'css'         => ['home.css'],
                'nav'         => 'home',
                'current'     => 'home',
            ],
        ]);
    }
}
