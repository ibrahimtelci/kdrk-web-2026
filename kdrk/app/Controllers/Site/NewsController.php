<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\View;
use App\Models\News;

final class NewsController
{
    public function show(string $slug): void
    {
        $n = News::findBySlug($slug) ?? abort(404);
        if (trim((string) $n['body']) === '') {
            redirect($n['url'], 302); // metni olmayan haber gittiği yere yönlenir
        }
        echo View::render('pages/news/show', [
            'n'      => $n,
            'others' => News::others((int) $n['id']),
            'page'   => [
                'title'         => $n['title'],
                'description'   => $n['excerpt'] ?: mb_substr(trim(preg_replace('/\s+/u', ' ', (string) $n['body'])), 0, 160),
                'css'           => ['home.css', 'article.css'],
                'navLight'      => true,
                'nav'           => 'home',
                'current'       => 'duyuru',
                'currentParams' => ['slug' => $n['slug']],
                'image'         => $n['cover_image'],
            ],
        ]);
    }
}
