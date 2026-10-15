<?php
/*
 * KDRK sunucu uyumluluk kontrolü — TEK SEFERLİK.
 * public_html/ içine yükle, tarayıcıda aç, sonucu kaydet ve DOSYAYI HEMEN SİL.
 * Yalnızca evet/hayır ve sürüm bilgisi gösterir; phpinfo() yayınlamaz.
 * Bilerek eski PHP sözdizimiyle yazıldı: sürüm düşükse de çalışıp bunu raporlamalı.
 */
header('Content-Type: text/plain; charset=utf-8');
header('X-Robots-Tag: noindex');

function kdrk_bytes($v) {
    $v = trim((string) $v);
    $n = (int) $v;
    $u = strtolower(substr($v, -1));
    if ($u === 'g') return $n * 1073741824;
    if ($u === 'm') return $n * 1048576;
    if ($u === 'k') return $n * 1024;
    return $n;
}

$gd = function_exists('gd_info') ? gd_info() : array();
$opcache = function_exists('opcache_get_status') ? @opcache_get_status(false) : false;
$docroot = isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : dirname(__FILE__);
$outside = dirname($docroot);
$mem = ini_get('memory_limit');

$checks = array(
    'PHP >= 8.2'                       => version_compare(PHP_VERSION, '8.2.0', '>='),
    'pdo_mysql'                        => extension_loaded('pdo_mysql'),
    'mbstring'                         => extension_loaded('mbstring'),
    'fileinfo (yükleme türü kontrolü)' => extension_loaded('fileinfo'),
    'sodium (KVKK şifreleme)'          => extension_loaded('sodium'),
    'openssl (SMTP TLS)'               => extension_loaded('openssl'),
    'gd'                               => extension_loaded('gd'),
    'gd: JPEG okuma'                   => !empty($gd['JPEG Support']),
    'gd: PNG okuma'                    => !empty($gd['PNG Support']),
    'gd: WebP üretme'                  => !empty($gd['WebP Support']),
    'OPcache açık'                     => is_array($opcache) && !empty($opcache['opcache_enabled']),
    'memory_limit >= 256M'             => $mem === '-1' || kdrk_bytes($mem) >= 268435456,
    'upload_max_filesize >= 15M'       => kdrk_bytes(ini_get('upload_max_filesize')) >= 15728640,
    'post_max_size >= 20M'             => kdrk_bytes(ini_get('post_max_size')) >= 20971520,
    'file_uploads açık'                => (bool) ini_get('file_uploads'),
    'public_html dışı yazılabilir'     => is_writable($outside),
);

echo "KDRK SUNUCU KONTROLÜ\n====================\n";
echo 'PHP sürümü          : ' . PHP_VERSION . ' (' . PHP_SAPI . ")\n";
echo 'Sunucu yazılımı     : ' . (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '?') . "\n";
echo 'memory_limit        : ' . $mem . "\n";
echo 'upload_max_filesize : ' . ini_get('upload_max_filesize') . "\n";
echo 'post_max_size       : ' . ini_get('post_max_size') . "\n";
echo 'max_execution_time  : ' . ini_get('max_execution_time') . "\n";
echo 'display_errors      : ' . (ini_get('display_errors') ? ini_get('display_errors') : '0') . "\n\n";

$fail = 0;
foreach ($checks as $name => $ok) {
    echo ($ok ? '[ OK ] ' : '[EKSİK] ') . $name . "\n";
    if (!$ok) $fail++;
}
echo "\n" . ($fail ? $fail . ' madde eksik. cPanel > Select PHP Version / MultiPHP INI Editor üzerinden açılabilir.' : 'Tüm gereksinimler karşılanıyor.') . "\n";
echo "\nBU DOSYAYI ŞİMDİ SİL.\n";
