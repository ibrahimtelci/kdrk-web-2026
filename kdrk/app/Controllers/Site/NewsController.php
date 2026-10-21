<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\View;
use App\Models\News;

final class NewsController
{
    /** /duyurular?tur=duyuru&sayfa=2 — türe göre süzme ve sayfalama linkle çalışır (JS gerekmez). */
    public function index(): void
    {
        $type = isset(News::CATEGORIES[$_GET['tur'] ?? '']) ? $_GET['tur'] : null;
        echo View::render('pages/news/index', News::search($type, (int) ($_GET['sayfa'] ?? 1)) + [
            'type' => $type,
            'page' => [
                'title'       => 'Duyuru & Haberler',
                'description' => 'KDRK duyuruları, kulüp haberleri ve faaliyet raporları: başvuru tarihleri, eğitim programları ve sahadan son gelişmeler.',
                'css'         => ['home.css', 'list.css'],
                'navLight'    => true,
                'nav'         => 'duyuru',
                'current'     => 'duyurular',
            ],
        ]);
    }

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
                'nav'           => 'duyuru',
                'current'       => 'duyuru',
                'currentParams' => ['slug' => $n['slug']],
                'image'         => $n['cover_image'],
            ],
        ]);
    }
}
