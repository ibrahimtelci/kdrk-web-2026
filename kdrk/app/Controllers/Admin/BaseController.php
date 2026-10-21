<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\HttpException;
use App\Core\Image;
use App\Core\Session;
use App\Core\View;

/**
 * Tüm panel controller'larının tabanı.
 * - Oturum yoksa giriş sayfasına yönlendirir
 * - Her POST'ta CSRF doğrular; İzleme rolü hiçbir POST yapamaz
 * - Varsayılan erişim REDDEDİLİR: her controller $roles ile kimin girebileceğini açıkça söyler (CLAUDE.md §15)
 */
abstract class BaseController
{
    /** Bu controller'a erişebilen roller. */
    protected array $roles = ['admin'];

    /** İzleme rolünün POST yapabileceği tek yer kendi hesabıdır (parola değişikliği). */
    protected bool $viewerCanPost = false;

    public function __construct()
    {
        header('X-Robots-Tag: noindex, nofollow');
        header('Cache-Control: no-store');
        if (!Auth::check()) {
            $next = $_SERVER['REQUEST_METHOD'] === 'GET' ? ($_SERVER['REQUEST_URI'] ?? '') : '';
            redirect(url('admin.login', $next !== '' ? ['next' => $next] : []));
        }
        if (!Auth::is(...$this->roles)) {
            throw new HttpException(403);
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::verify();
            if (!Auth::canEdit() && !$this->viewerCanPost) {
                throw new HttpException(403);
            }
        }
    }

    /**
     * Açılır pencere kipi: liste sayfasındaki "Yeni …" düğmesi formu ?pencere=1 ile bir pencerede açar.
     * Pencerede kenar çubuğu yoktur; kayıt bitince done() ana sayfayı hedef adrese götürür.
     * JS kapalıyken aynı formlar normal sayfa olarak çalışır.
     */
    protected static function inModal(): bool
    {
        return ($_GET['pencere'] ?? $_POST['_pencere'] ?? '') === '1';
    }

    /** Adrese pencere kipini ekler (hata sonrası forma dönüşte pencere içinde kalınsın). */
    protected static function keepModal(string $to): string
    {
        return self::inModal() ? $to . (str_contains($to, '?') ? '&' : '?') . 'pencere=1' : $to;
    }

    /** @param string $active kenar çubuğunda işaretlenecek modül */
    protected function render(string $view, string $title, string $active, array $data = []): void
    {
        echo View::render('admin/' . $view, $data + [
            'title'  => $title,
            'active' => $active,
            'modal'  => self::inModal(),
            'flash'  => Session::pull('admin_flash'),
            'errors' => Session::pull('admin_errors', []),
            'old'    => Session::pull('admin_old', []),
        ], self::inModal() ? 'layouts/admin-modal' : 'layouts/admin');
    }

    /** Kayıt bitti: pencere kipindeyse ana sayfayı $to adresine götürür, değilse yönlendirir. */
    protected function done(string $to): never
    {
        if (self::inModal()) {
            echo View::render('admin/_done', ['to' => $to], null);
            exit;
        }
        redirect($to, 303);
    }

    protected function flash(string $message, string $type = 'ok'): void
    {
        Session::flash('admin_flash', ['type' => $type, 'message' => $message]);
    }

    /** Form hatasıyla geri dön: girilen değerler korunur. */
    protected function back(string $to, array $errors, array $old): never
    {
        Session::flash('admin_errors', $errors);
        Session::flash('admin_old', $old);
        $this->flash('Formda düzeltilmesi gereken alanlar var.', 'error');
        redirect(self::keepModal($to), 303);
    }

    protected function requireRole(string ...$roles): void
    {
        if (!Auth::is(...$roles)) {
            throw new HttpException(403);
        }
    }

    /**
     * Formdaki görsel alanını işler: yeni dosya yüklendiyse kaydeder, "kaldır" işaretliyse siler.
     * Hata olursa forma döner. Eski dosya ancak yenisi başarıyla kaydedildikten sonra silinir.
     */
    protected function replaceImage(array &$d, string $field, string $column, ?string $old, string $folder, string $back): void
    {
        if (!empty($_FILES[$field]['name']) && ($_FILES[$field]['error'] ?? 0) !== UPLOAD_ERR_NO_FILE) {
            try {
                $d[$column] = Image::store($_FILES[$field], $folder, Image::parseCrop($_POST[$field . '_crop'] ?? null));
            } catch (\RuntimeException $ex) {
                $this->back($back, [$field => $ex->getMessage()], $_POST);
            }
            Image::delete($old);
        } elseif (!empty($_POST['remove_' . $field])) {
            $d[$column] = null;
            Image::delete($old);
        }
    }

    /**
     * Bağlantı alanı: site içi yol (/faaliyetler) ya da tam adres (https://…). Boşsa null.
     * @return array{0: ?string, 1: ?string} [değer, hata]
     */
    protected static function link(string $key): array
    {
        $v = self::str($key, 255);
        if ($v === '') {
            return [null, null];
        }
        if (preg_match('#^/(?!/)[^\s]*$#', $v) || (preg_match('#^https?://#i', $v) && filter_var($v, FILTER_VALIDATE_URL))) {
            return [$v, null];
        }
        return [null, 'Site içi bir yol (/faaliyetler gibi) ya da https:// ile başlayan bir adres yaz.'];
    }

    protected static function str(string $key, int $max = 255): string
    {
        return mb_substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
    }

    protected static function nullable(string $key, int $max = 255): ?string
    {
        $v = self::str($key, $max);
        return $v === '' ? null : $v;
    }
}
