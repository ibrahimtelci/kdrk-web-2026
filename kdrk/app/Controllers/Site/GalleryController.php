<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\View;
use App\Models\Activity;
use App\Models\Gallery;

final class GalleryController
{
    /** /galeri?yil=2025&kategori=dagcilik&q=…&sayfa=2 — filtreler JS kapalıyken de çalışır */
    public function index(): void
    {
        $f = [
            'yil'      => preg_match('/^(19|20)\d{2}$/', (string) ($_GET['yil'] ?? '')) ? (int) $_GET['yil'] : null,
            'kategori' => isset(Activity::CATEGORIES[$_GET['kategori'] ?? '']) ? $_GET['kategori'] : null,
            'q'        => mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 80),
        ];
        $result = Gallery::search($f, (int) ($_GET['sayfa'] ?? 1));

        // Kartlar yıla göre gruplanır (sıra: yeniden eskiye)
        $groups = [];
        foreach ($result['albums'] as $g) {
            $groups[$g['year'] ?? 0][] = $g;
        }

        echo View::render('pages/gallery/index', $result + [
            'groups'   => $groups,
            'filters'  => $f,
            'overview' => Gallery::overview(),
            'page'     => [
                'title'       => 'Fotoğraf Galerisi',
                'description' => 'KDRK faaliyetlerinden fotoğraflar: zirveler, yürüyüşler, eğitimler ve kamplar.',
                'css'         => ['home.css', 'list.css', 'gallery.css'],
                'navLight'    => true,
                'nav'         => 'galeri',
                'current'     => 'galeri',
            ],
        ]);
    }

    public function show(string $slug): void
    {
        $album = Gallery::findBySlug($slug) ?? abort(404);
        echo View::render('pages/gallery/show', [
            'album'   => $album,
            'photos'  => Gallery::photos((int) $album['id']),
            'related' => $album['year'] ? Gallery::related($album) : [],
            'page'    => [
                'title'         => $album['title'] . ' · Fotoğraf Galerisi',
                'description'   => $album['description'] ?: $album['title'] . ' faaliyetinden ' . $album['photo_count'] . ' fotoğraf.',
                'css'           => ['home.css', 'list.css', 'gallery.css', 'gallery-detail.css'],
                'navLight'      => true,
                'nav'           => 'galeri',
                'current'       => 'galeri.album',
                'currentParams' => ['slug' => $album['slug']],
                'image'         => $album['cover'],
            ],
        ]);
    }
}
