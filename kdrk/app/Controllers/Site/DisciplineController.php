<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\View;
use App\Models\Activity;

final class DisciplineController
{
    public function show(string $slug): void
    {
        $all = require KDRK_ROOT . '/app/disciplines.php';
        if (!isset($all[$slug])) {
            abort(404);
        }
        [$category, $title, $description, $css] = $all[$slug];
        echo View::render('pages/discipline/' . $slug, [
            'activities' => Activity::upcoming(3, $category),
            'category'   => $category,
            'page'       => [
                'title'         => $title,
                'description'   => $description,
                'css'           => $css,
                'nav'           => 'faaliyetler',
                'current'       => 'disiplin',
                'currentParams' => ['slug' => $slug],
            ],
        ]);
    }
}
