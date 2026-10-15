<?php
/*
 * Panelde kullanılan etiket eşlemeleri. include ile alınır: $S = require __DIR__ . '/_status.php';
 * Renk adları admin.css .chip sınıflarıdır (green, orange, red, blue, gray).
 */
return [
    'activity' => [
        'open'      => ['Başvuru açık', 'green'],
        'few'       => ['Son yerler', 'orange'],
        'full'      => ['Dolu', 'gray'],
        'closed'    => ['Başvuru kapandı', 'gray'],
        'postponed' => ['Ertelendi', 'orange'],
        'cancelled' => ['İptal', 'red'],
        'done'      => ['Tamamlandı', 'blue'],
    ],
    'application' => [
        'pending'  => ['Bekliyor', 'orange'],
        'approved' => ['Onaylı', 'green'],
        'waitlist' => ['Yedek', 'blue'],
        'rejected' => ['Reddedildi', 'red'],
    ],
];
