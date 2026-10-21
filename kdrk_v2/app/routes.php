<?php
declare(strict_types=1);

use App\Controllers\Admin;
use App\Controllers\Site\ActivityController;
use App\Controllers\Site\ContactController;
use App\Controllers\Site\DisciplineController;
use App\Controllers\Site\GalleryController;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\NewsController;
use App\Controllers\Site\PageController;
use App\Core\Router;

/* URL yapısı: CLAUDE.md §13 */
return static function (Router $r): void {
    $r->get('/', [HomeController::class, 'index'], 'home');

    $r->get('/faaliyetler', [ActivityController::class, 'index'], 'faaliyetler');
    $r->get('/faaliyetler/{slug}', [ActivityController::class, 'show'], 'faaliyet');
    $r->post('/faaliyetler/{slug}/basvuru', [ActivityController::class, 'apply'], 'faaliyet.basvuru');

    $r->get('/disiplin/{slug}', [DisciplineController::class, 'show'], 'disiplin');

    $r->get('/duyurular', [NewsController::class, 'index'], 'duyurular');
    $r->get('/duyurular/{slug}', [NewsController::class, 'show'], 'duyuru');

    $r->get('/galeri', [GalleryController::class, 'index'], 'galeri');
    $r->get('/galeri/{slug}', [GalleryController::class, 'show'], 'galeri.album');
    $r->legacy('/Galeri.html', 'galeri');
    $r->legacy('/Galeri Detay.html', 'galeri');

    $r->post('/iletisim', [ContactController::class, 'send'], 'iletisim.gonder');

    foreach (require KDRK_ROOT . '/app/pages.php' as $name => $p) {
        $r->get($p[0], static fn () => (new PageController())->show($name), $name);
        $r->legacy('/' . $p[7], $name);
    }

    // Yönetim paneli (/yonetim) — sabit yollar {id}'den önce tanımlanır
    $r->get('/yonetim/giris', [Admin\AuthController::class, 'form'], 'admin.login');
    $r->post('/yonetim/giris', [Admin\AuthController::class, 'login'], 'admin.login.post');
    $r->post('/yonetim/cikis', [Admin\AuthController::class, 'logout'], 'admin.logout');
    $r->get('/yonetim', [Admin\DashboardController::class, 'index'], 'admin.dashboard');

    $r->get('/yonetim/faaliyetler', [Admin\ActivityController::class, 'index'], 'admin.activities');
    $r->get('/yonetim/faaliyetler/yeni', [Admin\ActivityController::class, 'create'], 'admin.activities.new');
    $r->post('/yonetim/faaliyetler', [Admin\ActivityController::class, 'store'], 'admin.activities.store');
    $r->get('/yonetim/faaliyetler/{id}', [Admin\ActivityController::class, 'edit'], 'admin.activities.edit');
    $r->post('/yonetim/faaliyetler/{id}', [Admin\ActivityController::class, 'update'], 'admin.activities.update');
    $r->post('/yonetim/faaliyetler/{id}/sil', [Admin\ActivityController::class, 'delete'], 'admin.activities.delete');
    $r->get('/yonetim/faaliyetler/{id}/basvurular', [Admin\ApplicationController::class, 'index'], 'admin.applications');
    $r->get('/yonetim/faaliyetler/{id}/liste/{kind}', [Admin\ApplicationController::class, 'printList'], 'admin.applications.print');
    $r->get('/yonetim/faaliyetler/{id}/yoklama', [Admin\ApplicationController::class, 'checklist'], 'admin.applications.checklist');
    $r->post('/yonetim/basvurular/{id}/yoklama', [Admin\ApplicationController::class, 'checkin'], 'admin.applications.checkin');
    $r->post('/yonetim/basvurular/{id}', [Admin\ApplicationController::class, 'update'], 'admin.applications.update');
    $r->post('/yonetim/basvurular/{id}/sil', [Admin\ApplicationController::class, 'delete'], 'admin.applications.delete');

    $r->get('/yonetim/galeri', [Admin\GalleryController::class, 'index'], 'admin.gallery');
    $r->get('/yonetim/galeri/yeni', [Admin\GalleryController::class, 'create'], 'admin.gallery.new');
    $r->post('/yonetim/galeri', [Admin\GalleryController::class, 'store'], 'admin.gallery.store');
    $r->get('/yonetim/galeri/{id}', [Admin\GalleryController::class, 'edit'], 'admin.gallery.edit');
    $r->post('/yonetim/galeri/{id}', [Admin\GalleryController::class, 'update'], 'admin.gallery.update');
    $r->post('/yonetim/galeri/{id}/sil', [Admin\GalleryController::class, 'delete'], 'admin.gallery.delete');
    $r->post('/yonetim/galeri/{id}/fotograf', [Admin\GalleryController::class, 'upload'], 'admin.gallery.upload');
    $r->post('/yonetim/fotograflar/{id}', [Admin\GalleryController::class, 'photo'], 'admin.gallery.photo');

    $r->get('/yonetim/duyurular', [Admin\NewsController::class, 'index'], 'admin.news');
    $r->get('/yonetim/duyurular/yeni', [Admin\NewsController::class, 'create'], 'admin.news.new');
    $r->post('/yonetim/duyurular', [Admin\NewsController::class, 'store'], 'admin.news.store');
    $r->get('/yonetim/duyurular/{id}', [Admin\NewsController::class, 'edit'], 'admin.news.edit');
    $r->post('/yonetim/duyurular/{id}', [Admin\NewsController::class, 'update'], 'admin.news.update');
    $r->post('/yonetim/duyurular/{id}/sil', [Admin\NewsController::class, 'delete'], 'admin.news.delete');

    $r->get('/yonetim/slaytlar', [Admin\BannerController::class, 'index'], 'admin.banners');
    $r->get('/yonetim/slaytlar/yeni', [Admin\BannerController::class, 'create'], 'admin.banners.new');
    $r->post('/yonetim/slaytlar/durum', [Admin\BannerController::class, 'toggle'], 'admin.banners.toggle');
    $r->post('/yonetim/slaytlar/sira', [Admin\BannerController::class, 'reorder'], 'admin.banners.order');
    $r->post('/yonetim/slaytlar/toplu', [Admin\BannerController::class, 'bulk'], 'admin.banners.bulk');
    $r->post('/yonetim/slaytlar', [Admin\BannerController::class, 'store'], 'admin.banners.store');
    $r->post('/yonetim/slaytlar/faaliyet', [Admin\BannerController::class, 'activity'], 'admin.banners.activity');
    $r->post('/yonetim/slaytlar/haber', [Admin\BannerController::class, 'news'], 'admin.banners.news');
    $r->get('/yonetim/slaytlar/{id}', [Admin\BannerController::class, 'edit'], 'admin.banners.edit');
    $r->post('/yonetim/slaytlar/{id}', [Admin\BannerController::class, 'update'], 'admin.banners.update');
    $r->post('/yonetim/slaytlar/{id}/sil', [Admin\BannerController::class, 'delete'], 'admin.banners.delete');
    $r->post('/yonetim/slaytlar/{id}/tasi', [Admin\BannerController::class, 'move'], 'admin.banners.move');

    $r->get('/yonetim/mesajlar',[Admin\MessageController::class, 'index'], 'admin.messages');
    $r->get('/yonetim/mesajlar/{id}', [Admin\MessageController::class, 'show'], 'admin.messages.show');
    $r->post('/yonetim/mesajlar/{id}/okunmadi', [Admin\MessageController::class, 'unread'], 'admin.messages.unread');
    $r->post('/yonetim/mesajlar/{id}/sil', [Admin\MessageController::class, 'delete'], 'admin.messages.delete');

    $r->get('/yonetim/iletisim-bilgileri', [Admin\GuideContactController::class, 'index'], 'admin.contacts');
    $r->get('/yonetim/iletisim-bilgileri/yeni', [Admin\GuideContactController::class, 'create'], 'admin.contacts.new');
    $r->post('/yonetim/iletisim-bilgileri', [Admin\GuideContactController::class, 'store'], 'admin.contacts.store');
    $r->get('/yonetim/iletisim-bilgileri/{id}', [Admin\GuideContactController::class, 'edit'], 'admin.contacts.edit');
    $r->post('/yonetim/iletisim-bilgileri/{id}', [Admin\GuideContactController::class, 'update'], 'admin.contacts.update');

    $r->get('/yonetim/ayarlar', [Admin\SettingsController::class, 'index'], 'admin.settings');
    $r->post('/yonetim/ayarlar', [Admin\SettingsController::class, 'update'], 'admin.settings.update');

    $r->get('/yonetim/kullanicilar', [Admin\UserController::class, 'index'], 'admin.users');
    $r->get('/yonetim/kullanicilar/yeni', [Admin\UserController::class, 'create'], 'admin.users.new');
    $r->post('/yonetim/kullanicilar', [Admin\UserController::class, 'store'], 'admin.users.store');
    $r->get('/yonetim/kullanicilar/{id}', [Admin\UserController::class, 'edit'], 'admin.users.edit');
    $r->post('/yonetim/kullanicilar/{id}', [Admin\UserController::class, 'update'], 'admin.users.update');

    $r->get('/yonetim/hesap', [Admin\AccountController::class, 'index'], 'admin.account');
    $r->post('/yonetim/hesap', [Admin\AccountController::class, 'update'], 'admin.account.update');

    // Eski prototip adresleri → 301 (CLAUDE.md §13)
    $r->legacy('/admin/Panel.html', 'admin.dashboard');
    $r->legacy('/Anasayfa.html', 'home');
    $r->legacy('/index.html', 'home');
    $r->legacy('/Faaliyetler.html', 'faaliyetler');
    $r->legacy('/Faaliyet Detay.html', 'faaliyetler');
    foreach (require KDRK_ROOT . '/app/disciplines.php' as $slug => $d) {
        $r->legacy('/' . $d[4], 'disiplin', ['slug' => $slug]);
    }
};
