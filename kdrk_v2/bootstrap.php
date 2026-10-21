<?php
declare(strict_types=1);

/*
 * Uygulama girişi. public_html/index.php ve CLI betikleri bu dosyayı yükler.
 * Bu klasör (kdrk/) web kökünün DIŞINDA durur.
 */

define('KDRK_ROOT', __DIR__);

spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $file = __DIR__ . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$configFile = __DIR__ . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Yapılandırma eksik: kdrk/config/config.php oluşturulmalı (config.example.php dosyasından kopyalayın).');
}

require __DIR__ . '/app/helpers.php';

App\Core\App::boot(require $configFile);
