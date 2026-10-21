<?php
/*
 * Bu dosyayı config.php adıyla kopyalayıp doldurun.
 * config.php GİT'E GİRMEZ ve sunucuda FTP ile ÜZERİNE YAZILMAZ (CLAUDE.md §15, §18).
 */
return [
    // 'local' → hatalar ekranda; 'production' → hatalar yalnızca storage/logs altında
    'env' => 'local',

    // Sitenin kök adresi; sonunda / olmadan. Alt klasörde çalışıyorsa yolu ekleyin.
    'base_url' => 'http://localhost:8000',

    // true → tüm sayfalar "X-Robots-Tag: noindex" ile gelir. Deneme adresinde (yeni.kdrk.org.tr) true,
    // asıl alan adına (kdrk.org.tr) geçince false yapılır.
    'noindex' => false,

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'kdrk_web',
        'user' => 'kdrk_web',
        'pass' => '',
    ],

    // KVKK alanlarını şifreleyen anahtar (base64, 32 bayt).
    // Üretmek için: php -r "echo base64_encode(random_bytes(32));"
    // DEĞİŞTİRİLİRSE eski şifreli veriler okunamaz.
    'app_key' => '',

    'mail' => [
        'host'       => 'mail.kdrk.org.tr',
        'port'       => 465,
        'encryption' => 'ssl',            // 'ssl' (465) veya 'tls' (587)
        'user'       => 'bildirim@kdrk.org.tr',
        'pass'       => '',
        'from'       => 'bildirim@kdrk.org.tr',
        'from_name'  => 'KDRK',
        'notify'     => 'info@kdrk.org.tr', // başvuru ve iletişim bildirimlerinin gideceği adres
    ],

    // Web kökünün (public_html) bu dosyaya göre konumu
    'public_path' => dirname(__DIR__, 2) . '/public_html',
];
