<?php
/*
 * Yalnızca yerel geliştirme: PHP yerleşik sunucusu .htaccess okumaz; bu dosya onun yerini tutar.
 *   php -d extension=sodium -S localhost:8000 -t public_html tools/dev-router.php
 * (tools/dev.bat bunu çalıştırır)
 */
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$file = $_SERVER['DOCUMENT_ROOT'] . $path;
if ($path !== '/' && is_file($file)) {
    if (str_starts_with($path, '/uploads/') && preg_match('/\.(php\d?|phtml|phar)$/i', $path)) {
        http_response_code(403);
        return true;
    }
    return false; // statik dosyayı yerleşik sunucu verir
}
require $_SERVER['DOCUMENT_ROOT'] . '/index.php';
