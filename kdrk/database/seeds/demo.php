<?php
declare(strict_types=1);

/*
 * Yerel örnek veri — prototip sayfalarındaki içerikten (demo-data.json) üretilir.
 * migrate.php --seed ile çalışır; yalnızca env=local. Var olan içerik tablolarını BOŞALTIR.
 */

use App\Core\App;

$db = App::db();
$data = json_decode((string) file_get_contents(__DIR__ . '/demo-data.json'), true, 512, JSON_THROW_ON_ERROR);

$db->pdo()->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['applications', 'gallery_photos', 'gallery_albums', 'news', 'banners', 'activities'] as $t) {
    $db->pdo()->exec("TRUNCATE TABLE `$t`");
}
$db->pdo()->exec('SET FOREIGN_KEY_CHECKS = 1');

// Kaçkar Zirve Tırmanışı için prototipteki tam detay sayfası içeriği
$kackar = [
    'ends_on'          => '2026-07-15',
    'duration_text'    => '3 gece · 4 gün',
    'summit_m'         => 3937,
    'distance_km'      => 32,
    'elevation_gain_m' => 1840,
    'fee'              => 2450,
    'fee_note'         => 'Ulaşım, kamp ve grup malzemesi dahil. Bireysel ekipman katılımcıya aittir.',
    'meeting_text'     => 'Pazar otogarı · 06:30',
    'return_text'      => 'Pazar · 15 Temmuz, 22:00 (planlanan)',
    'included_text'    => 'Ulaşım, kamp ve grup malzemesi',
    'personal_text'    => 'Bireysel ekipman (aşağıdaki liste)',
    'requirement_text' => 'En az iki yüksek irtifa deneyimi; krampon, buz baltası ve ip teknikleri bilgisi. Başvurular KDRK eğitmenlerince değerlendirilir; uygun bulunmayanlar farklı seviyedeki faaliyetlere yönlendirilir.',
    'weather_text'     => 'Hava uygun değilse zirve denemesi ertelenir.',
    'leader_name'      => 'Murat Kayhan',
    'leader_title'     => 'TDF sertifikalı yaz dağcılık eğitmeni',
    'body'             => "Türkiye'nin doğusundaki en yüksek zirve. Kaçkar Dağı (3.937 m), Doğu Karadeniz silsilesinin tacı. Tırmanışımız Çamlıhemşin'den başlar; Olgunlar köyünden yürüyüşe çıkıp Dilberdüzü kampında konaklayarak zirveye ulaşır. Buzul aşması, 35° eğimli kar şutları ve son metrelerde basit kaya geçişleri içerir.\n\nBu rota teknik bilgi ve fiziksel kondisyon gerektirir. En az iki yüksek irtifa deneyimi, krampon-balta kullanımı ve ip teknikleriyle aşinalık şart. Katılım, KDRK eğitmenleri tarafından önceden değerlendirilir; uygun bulunmayan başvurular farklı seviyedeki faaliyetlere yönlendirilir.\n\nYıllardır aynı rotayı yürürüz; bu yıl da aynı disiplinle çıkıyoruz. Hava şartları uygun değilse zirve denemesi ertelenir — döndüğümüz an, başardığımız andır.",
    'program' => json_encode([
        ['title' => 'Yola çıkış & Olgunlar', 'date' => '2026-07-12', 'items' => [
            ['time' => '06:30', 'text' => 'Pazar otogarı toplanma noktası, çantaların kontrolü'],
            ['time' => '08:00', 'text' => "Çamlıhemşin'e doğru hareket, kahvaltı molası"],
            ['time' => '14:00', 'text' => 'Olgunlar köyünden yürüyüşe başlangıç'],
            ['time' => '18:00', 'text' => 'Dilberdüzü kampı (2.580 m), çadırların kurulumu'],
        ]],
        ['title' => 'Aklimatizasyon & teknik tekrar', 'date' => '2026-07-13', 'items' => [
            ['time' => '07:00', 'text' => 'Kahvaltı, hava raporu değerlendirmesi'],
            ['time' => '09:30', 'text' => 'Deniz Gölü çevresinde aklimatizasyon yürüyüşü'],
            ['time' => '14:00', 'text' => 'Krampon, balta ve ip teknikleri tekrarı'],
            ['time' => '19:00', 'text' => 'Erken yatış — zirve sabahı için hazırlık'],
        ]],
        ['title' => 'Zirve tırmanışı', 'date' => '2026-07-14', 'items' => [
            ['time' => '02:30', 'text' => 'Alpine start — sıcak kahvaltı, çıkış hazırlığı'],
            ['time' => '03:30', 'text' => 'Buzul geçişi başlangıcı, ip ekibi düzeni'],
            ['time' => '08:30', 'text' => 'Zirve hedeflenen saat (3.937 m)'],
            ['time' => '14:00', 'text' => 'Kampa dönüş, dinlenme'],
        ]],
        ['title' => 'İniş & dönüş', 'date' => '2026-07-15', 'items' => [
            ['time' => '07:00', 'text' => 'Kamp toplanır, çantalar bağlanır'],
            ['time' => '09:00', 'text' => "Olgunlar'a iniş"],
            ['time' => '13:00', 'text' => "Çamlıhemşin'de öğle yemeği — kapanış sohbeti"],
            ['time' => '22:00', 'text' => "Pazar'a varış"],
        ]],
    ], JSON_UNESCAPED_UNICODE),
    'equipment' => json_encode([
        'Yüksek montaj çantası (60–70 L)', 'Gore-Tex ceket & pantolon', 'Polar & termal iç çamaşır (2 set)',
        'Plastik kabuk dağcı botu', 'Krampon (12 dişli)', 'Buz baltası', 'Tırmanış emniyet kemeri', 'Kask',
        'Eldiven (ince + kalın)', 'Buzul gözlüğü (Cat. 4)', 'Kafa lambası + yedek pil', 'Uyku tulumu (-10 °C)',
        'Mat & kişisel termos', 'Kişisel ilk yardım çantası',
    ], JSON_UNESCAPED_UNICODE),
];

$ids = [];
foreach ($data['activities'] as $a) {
    $row = [
        'slug'          => slugify(str_contains($a['title'], substr($a['starts_on'], 0, 4)) ? $a['title'] : $a['title'] . ' ' . substr($a['starts_on'], 0, 4)),
        'title'         => $a['title'],
        'category'      => $a['category'],
        'starts_on'     => $a['starts_on'],
        'location'      => $a['location'],
        'summary'       => $a['summary'],
        'difficulty'    => $a['difficulty'],
        'is_training'   => (int) ($a['category'] === 'egitim'),
        'quota'         => $a['quota'],
        'cover_image'   => $a['cover_image'],
        'manual_status' => $a['manual_status'],
    ];
    if ($a['done']) {
        $row['report_summary'] = $a['summary'];
    }
    if ($a['title'] === 'Kaçkar Zirve Tırmanışı') {
        $row = array_merge($row, $kackar);
    }
    $id = $db->insert('activities', $row);
    $ids[$a['title']] = $id;

    // Doluluk göstergesi prototiple aynı çıksın diye örnek (kişisel verisiz) onaylı başvurular
    for ($i = 1; $i <= $a['taken']; $i++) {
        $db->insert('applications', [
            'activity_id'     => $id,
            'name'            => "Örnek Katılımcı $i",
            'phone'           => '0000000000',
            'email'           => 'ornek@example.invalid',
            'status'          => 'approved',
            'kvkk_consent_at' => date('Y-m-d H:i:s'),
            'mail_status'     => 'sent',
        ]);
    }
}

// Slider: rakamlar {yil}, {zirve} gibi belirteçlerle yazılır, ekranda KDRK_STATS ile doldurulur.
$routeOf = [
    'Faaliyetler.html' => '/faaliyetler', 'Faaliyetler.html?cat=egitim' => '/faaliyetler?cat=egitim',
    'Kulup Uyeligi.html' => '/bilgi-bankasi/kulup-uyeligi', 'Egitim.html' => '/disiplin/egitim',
    'Iletisim.html' => '/iletisim', 'Arama Kurtarma.html' => '/disiplin/arama-kurtarma',
    'Dag Kayagi.html' => '/disiplin/dag-kayagi', 'Galeri Detay.html' => '/galeri',
];
foreach ($data['banners'] as $i => $b) {
    $text = str_replace('28 yıldır', '{yil} yıldır', $b['text']);
    $stamps = array_map(static fn ($s) => strtr($s, ['500+' => '{zirve}+', '28 YIL' => '{yil} YIL']), $b['stamps']);
    $db->insert('banners', [
        'eyebrow'    => $b['eyebrow'],
        'title'      => $b['title'],
        'text'       => $text,
        'image'      => $b['image'],
        'cta_label'  => $b['ctas'][0][1] ?? null,
        'cta_url'    => $routeOf[$b['ctas'][0][0] ?? ''] ?? null,
        'cta2_label' => $b['ctas'][1][1] ?? null,
        'cta2_url'   => $routeOf[$b['ctas'][1][0] ?? ''] ?? null,
        'stamp_1'    => $stamps[0] ?? null,
        'stamp_2'    => $stamps[1] ?? null,
        'sort'       => $i + 1,
        // 2. slayt Temmuz 2026 eğitimini duyuruyor: tarihi geçince kendiliğinden kalkar
        'show_until' => str_contains($b['title'], '2026') ? '2026-07-20' : null,
    ]);
}

$catOf = ['Duyuru' => 'duyuru', 'Haber' => 'haber', 'Faaliyet Raporu' => 'rapor'];
$monthOf = array_flip(TR_MONTHS);
foreach ($data['news'] as $n) {
    preg_match('/(\d{2}) (\S+) (\d{4})(?: · (\d+) DK)?/u', $n['meta'], $m);
    $date = sprintf('%s-%02d-%s', $m[3], $monthOf[$m[2]] + 1, $m[1]);
    $db->insert('news', [
        'slug'         => slugify($n['title']),
        'title'        => $n['title'],
        'category'     => $catOf[$n['category']],
        'excerpt'      => $n['excerpt'],
        'cover_image'  => $n['cover_image'],
        'link_url'     => $routeOf[$n['href']] ?? null,
        'read_minutes' => isset($m[4]) ? (int) $m[4] : null,
        'is_featured'  => (int) $n['featured'],
        'status'       => 'published',
        'published_on' => $date,
    ]);
}

// Galeri: tamamlanan her faaliyete bir albüm + faaliyete bağlı olmayan iki eski albüm (Unsplash yer tutucu)
$pool = [
    ['photo-1486870591958-9b9d0d1dda99', 1200, 'Zirve sırtında ilk ışık'], ['photo-1551632436-cbf8dd35adfa', 900, 'Buz kazma molası'],
    ['photo-1454496522488-7a8e488e8606', 1400, 'Kuzey duvarı'], ['photo-1500382017468-9049fed747ef', 800, 'Sis altında yürüyüş'],
    ['photo-1519681393784-d120267933ba', 1100, 'Gün doğumu, 3.500 m'], ['photo-1483728642387-6c3bdd6c93e5', 1300, 'Granit kayalıklar'],
    ['photo-1464822759023-fed622ff2c3b', 850, 'Çadır kuruluyor'], ['photo-1506905925346-21bda4d32df4', 1000, 'Yıldızlı gece'],
    ['photo-1469474968028-56623f02e42e', 1500, 'Vadi panoraması'], ['photo-1426604966848-d7adac402bff', 900, 'Buzul gölü'],
    ['photo-1418065460487-3e41a6c84dc5', 1200, 'Kar köprüsü'], ['photo-1501785888041-af3ef285b470', 1100, 'Yamaçtan bakış'],
    ['photo-1505765050516-f72dcac9c60e', 950, 'Sırt çantaları sıralı'], ['photo-1500534314209-a25ddb2bd429', 1000, 'Çadırdan ilk bakış'],
    ['photo-1551632811-561732d1e306', 1450, 'Verçenik silueti'], ['photo-1432405972618-c60b0225b8f9', 900, 'Çiy damlaları'],
];
$credits = ['M. Yılmaz', 'A. Demir', 'E. Kaya', 'B. Çelik'];
$albums = [];
foreach ($db->all('SELECT id, title, starts_on, cover_image FROM activities WHERE COALESCE(ends_on, starts_on) < CURDATE() ORDER BY starts_on') as $a) {
    $year = substr($a['starts_on'], 0, 4);
    $albums[] = ['title' => $a['title'], 'slug' => slugify(str_contains($a['title'], $year) ? $a['title'] : $a['title'] . ' ' . $year), 'activity_id' => $a['id'], 'cover' => $a['cover_image']];
}
$albums[] = ['title' => 'Verçenik Tepesi Tırmanışı', 'slug' => 'vercenik-tepesi-tirmanisi-2024', 'taken_on' => '2024-09-08', 'category' => 'dagcilik', 'location' => 'Rize / İkizdere · 3.711 m'];
$albums[] = ['title' => 'Ayder Şelale Yürüyüşü', 'slug' => 'ayder-selale-yuruyusu-2024', 'taken_on' => '2024-06-16', 'category' => 'gunubirlik', 'location' => 'Rize / Ayder',
    'description' => 'Ailelerle birlikte Gelin Tülü şelalesine kısa ve kolay bir yürüyüş.'];
$photoCount = 0;
foreach ($albums as $k => $al) {
    $cover = $al['cover'] ?? null;
    unset($al['cover']);
    $albumId = $db->insert('gallery_albums', $al);
    $n = 6 + ($k * 5) % 9;
    for ($i = 0; $i < $n; $i++) {
        [$pid, $h, $cap] = $pool[($k * 7 + $i * 3) % count($pool)];
        $db->insert('gallery_photos', [
            'album_id' => $albumId,
            'path'     => $i === 0 && $cover ? $cover : "https://images.unsplash.com/$pid",
            'width'    => 800,
            'height'   => $h,
            'caption'  => $i % 3 === 2 ? null : $cap,
            'credit'   => $credits[($k + $i) % 4],
            'sort'     => $i + 1,
        ]);
        $photoCount++;
    }
}

printf("Örnek veri yüklendi: %d faaliyet, %d slayt, %d duyuru, %d albüm (%d fotoğraf).\n", count($ids), count($data['banners']), count($data['news']), count($albums), $photoCount);
