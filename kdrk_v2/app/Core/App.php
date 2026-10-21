<?php
declare(strict_types=1);

namespace App\Core;

use Throwable;

final class App
{
    private static array $config = [];
    private static ?DB $db = null;
    private static ?Router $router = null;

    public static function boot(array $config): void
    {
        self::$config = $config;
        date_default_timezone_set('Europe/Istanbul');
        mb_internal_encoding('UTF-8');

        $local = self::isLocal();
        ini_set('display_errors', $local ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', KDRK_ROOT . '/storage/logs/php-' . date('Y-m') . '.log');
        error_reporting(E_ALL);
        set_error_handler(static function (int $no, string $msg, string $file, int $line): bool {
            if (!(error_reporting() & $no)) {
                return false;
            }
            throw new \ErrorException($msg, 0, $no, $file, $line);
        });
    }

    public static function config(string $key, mixed $default = null): mixed
    {
        $value = self::$config;
        foreach (explode('.', $key) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return $default;
            }
            $value = $value[$part];
        }
        return $value;
    }

    public static function isLocal(): bool
    {
        return (self::$config['env'] ?? 'production') === 'local';
    }

    public static function db(): DB
    {
        return self::$db ??= new DB(self::$config['db']);
    }

    public static function router(): Router
    {
        if (self::$router === null) {
            self::$router = new Router();
            (require KDRK_ROOT . '/app/routes.php')(self::$router);
        }
        return self::$router;
    }

    /** Web isteğini işler (public_html/index.php çağırır). */
    public static function handle(): void
    {
        self::sendSecurityHeaders();
        try {
            Session::start();
            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            // Bakım modunda panel ve giriş yapmış yöneticiler siteyi görmeye devam eder
            if (Settings::bool('maintenance') && !Auth::check() && !str_contains($path, '/yonetim')) {
                http_response_code(503);
                header('Retry-After: 3600');
                echo View::render('pages/maintenance', [], null);
                return;
            }
            self::router()->dispatch($method, $path);
        } catch (HttpException $e) {
            self::renderError($e->status);
        } catch (Throwable $e) {
            error_log((string) $e);
            if (self::isLocal()) {
                http_response_code(500);
                header('Content-Type: text/plain; charset=utf-8');
                echo $e;
                return;
            }
            self::renderError(500);
        }
    }

    public static function renderError(int $status): void
    {
        http_response_code($status);
        $view = $status === 404 ? 'pages/404' : 'pages/error';
        try {
            echo View::render($view, ['status' => $status, 'page' => [
                'title'    => $status === 404 ? 'Sayfa bulunamadı' : 'Hata',
                'css'      => ['home.css', 'list.css'],
                'navLight' => true,
                'robots'   => 'noindex',
            ]]);
        } catch (Throwable $e) {
            error_log((string) $e);
            echo $status === 404 ? 'Sayfa bulunamadı.' : 'Beklenmeyen bir hata oluştu.';
        }
    }

    private static function sendSecurityHeaders(): void
    {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: SAMEORIGIN');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        // Deneme sunucusu (yeni.kdrk.org.tr) arama motorlarına kapalı: config 'noindex' => true
        if (self::config('noindex')) {
            header('X-Robots-Tag: noindex, nofollow');
        }
    }
}
