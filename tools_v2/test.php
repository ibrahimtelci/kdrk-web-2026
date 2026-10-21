<?php
declare(strict_types=1);

/*
 * KDRK yerel test takımı.
 *   1) tools/dev.bat ile sunucuyu başlat   2) php -d extension=sodium tools/test.php [http://localhost:8000]
 * Yerel veritabanına test başvuruları yazar ve sonunda siler. Canlıda ÇALIŞTIRILMAZ.
 */

require __DIR__ . '/../kdrk_v2/bootstrap.php';

use App\Core\App;
use App\Core\Crypto;
use App\Models\Activity;
use App\Models\Application;

if (!App::isLocal()) {
    exit("Test takımı yalnızca env=local ortamında çalışır.\n");
}

$BASE = rtrim($argv[1] ?? (string) App::config('base_url'), '/');
$pass = 0;
$fail = [];
function check(bool $ok, string $name): void
{
    global $pass, $fail;
    $ok ? $pass++ : $fail[] = $name;
    echo ($ok ? "  ✓ " : "  ✗ ") . $name . PHP_EOL;
}

function http(string $method, string $url, array $opt = []): array
{
    // Her rol ayrı oturum (çerez kavanozu) kullanır: $opt['jar'] = 'admin' | 'editor' | …
    static $jars = [];
    $jar = $jars[$opt['jar'] ?? 'default'] ??= tempnam(sys_get_temp_dir(), 'kdrkjar');
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_COOKIEJAR      => $jar,
        CURLOPT_COOKIEFILE     => $jar,
        CURLOPT_HTTPHEADER     => $opt['headers'] ?? [],
    ]);
    if (isset($opt['multipart'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $opt['multipart']); // CURLFile içerebilir
    } elseif (isset($opt['post'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($opt['post']));
    }
    $raw = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $hsize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $loc = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    return ['code' => $code, 'headers' => substr($raw, 0, $hsize), 'body' => substr($raw, $hsize), 'location' => $loc];
}

function dom(string $html): DOMXPath
{
    $d = new DOMDocument();
    libxml_use_internal_errors(true);
    $d->loadHTML('<?xml encoding="utf-8">' . $html);
    libxml_clear_errors();
    return new DOMXPath($d);
}

/* ------------------------------------------------------------------ */
echo "\n1) Birim testleri\n";
$base = ['starts_on' => '2026-11-10', 'ends_on' => null, 'manual_status' => null, 'quota' => 10, 'taken' => 2];
$today = '2026-10-15';
check(Activity::status($base, $today) === 'open', 'yer varsa: open');
check(Activity::status(['taken' => 7] + $base, $today) === 'few', '3 yer kalınca: few');
check(Activity::status(['taken' => 10] + $base, $today) === 'full', 'kontenjan dolunca: full');
check(Activity::status(['starts_on' => '2026-10-01'] + $base, $today) === 'past', 'tarihi geçince: past');
check(Activity::status(['starts_on' => '2026-10-14', 'ends_on' => '2026-10-16'] + $base, $today) === 'open', 'devam eden çok günlü faaliyet: done değil');
check(Activity::status(['manual_status' => 'closed'] + $base, $today) === 'closed', 'elle kapatılan: closed');
check(Activity::status(['manual_status' => 'closed', 'starts_on' => '2026-01-01'] + $base, $today) === 'past', 'kapalı + tarihi geçmiş: past');
check(Activity::status(['manual_status' => 'cancelled', 'starts_on' => '2026-01-01'] + $base, $today) === 'cancelled', 'iptal tarihi geçse de: cancelled');
check(Activity::status(['quota' => 0, 'taken' => 50] + $base, $today) === 'open', 'kontenjansız: open');
check(Application::validTc('10000000146'), 'geçerli TC kabul');
check(!Application::validTc('12345678901'), 'sağlaması tutmayan TC red');
check(!Application::validTc('01234567890'), '0 ile başlayan TC red');
[$d, $e] = Application::validate(['name' => ' Ali Veli ', 'tc' => '100 000 001 46', 'phone' => '0532 111 22 33', 'email' => 'ALI@Ornek.com',
    'emer_name' => 'Ayşe Veli', 'emer_phone' => '532 999 88 77', 'kvkk' => '1']);
check($e === [], 'geçerli form hatasız');
check($d['phone'] === '5321112233' && $d['email'] === 'ali@ornek.com' && $d['name'] === 'Ali Veli', 'telefon/e-posta/ad normalize');
[, $e] = Application::validate([]);
check(count($e) === 7, 'boş formda 7 alan hatası');
check(Crypto::decrypt(Crypto::encrypt('10000000146')) === '10000000146', 'şifreleme geri çözülüyor');
check(slugify('Kaçkar Zirve Tırmanışı 2027') === 'kackar-zirve-tirmanisi-2027', 'slugify Türkçe');
check(tr_range('2026-07-12', '2026-07-15') === '12–15 TEM 2026', 'tarih aralığı biçimi');
$ss = img_srcset('https://images.unsplash.com/photo-1');
check(substr_count($ss, 'w, ') === 3 && str_contains($ss, '?w=2400&') && str_ends_with($ss, ' 2400w'), 'srcset: 4 genişlik (§16)');
check(img_srcset('uploads/activities/olmayan') === '', 'srcset: diskte olmayan türev yazılmaz');

// §16: tek bir statik dosya 300 KB'ı geçemez
$heavy = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(dirname(__DIR__) . '/public_htm_v2/assets', FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
    if ($f->getSize() > 300 * 1024) {
        $heavy[] = $f->getFilename() . ' (' . round($f->getSize() / 1024) . ' KB)';
    }
}
check($heavy === [], 'assets içinde 300 KB üstü dosya yok' . ($heavy ? ': ' . implode(', ', $heavy) : ''));

/* ------------------------------------------------------------------ */
echo "\n2) Sayfalar, linkler ve iskelet kontrol listesi (CLAUDE.md §3, §10)\n";
$pages = ['/', '/faaliyetler'];
foreach (require KDRK_ROOT . '/app/pages.php' as $p) {
    $pages[] = $p[0];
}
foreach (array_keys(require KDRK_ROOT . '/app/disciplines.php') as $slug) {
    $pages[] = "/disiplin/$slug";
}
foreach (App::db()->all('SELECT slug FROM activities') as $r) {
    $pages[] = '/faaliyetler/' . $r['slug'];
}
foreach (App::db()->all("SELECT slug FROM news WHERE status = 'published' AND published_on <= CURDATE() AND body IS NOT NULL AND body <> ''") as $r) {
    $pages[] = '/duyurular/' . $r['slug'];
}
$pages[] = '/galeri';
$pages[] = '/duyurular';
$pages[] = '/duyurular?tur=duyuru';
$pages[] = '/galeri?yil=2024&kategori=dagcilik';
foreach (App::db()->all('SELECT slug FROM gallery_albums g WHERE is_published = 1 AND EXISTS (SELECT 1 FROM gallery_photos p WHERE p.album_id = g.id)') as $r) {
    $pages[] = '/galeri/' . $r['slug'];
}

$linkCache = [];
$problems = [];
foreach ($pages as $path) {
    $r = http('GET', $BASE . $path);
    $x = dom($r['body']);
    $issues = [];
    if ($r['code'] !== 200) {
        $issues[] = "HTTP {$r['code']}";
    }
    if (preg_match('/(Warning|Notice|Deprecated|Fatal error|Uncaught)\b.*?(on line|in \/)/i', $r['body'])) {
        $issues[] = 'PHP uyarısı çıktıda';
    }
    foreach (['//*[@data-nav]' => 'data-nav', '//*[@data-drawer]' => 'data-drawer', '//*[@data-theme-toggle]' => 'tema butonu',
        '//*[contains(@class,"nav-tdf")]' => 'nav-tdf', '//*[contains(@class,"footer-fullname")]' => 'footer-fullname',
        '//meta[@name="description"]' => 'meta description', '//meta[@name="viewport"]' => 'viewport',
        '//script[contains(@src,"assets/site.js")]' => 'site.js', '//*[@aria-current="page"]' => 'aria-current'] as $q => $label) {
        if ($x->query($q)->length === 0) {
            $issues[] = "eksik: $label";
        }
    }
    if (($n = $x->query('//*[contains(concat(" ",@class," ")," kdrk-mark ")]')->length) < 3) {
        $issues[] = "kdrk-mark x$n";
    }
    if (($n = $x->query('//h1')->length) !== 1) {
        $issues[] = "h1 x$n";
    }
    $lightHero = $x->query('//*[contains(@class,"list-header") or contains(@class,"kb-hero") or contains(@class,"gd-hero")]')->length > 0;
    if ($lightHero && $x->query('//header[contains(@class,"nav-on-light")]')->length === 0) {
        $issues[] = 'açık üst ama nav-on-light yok';
    }
    $ids = [];
    foreach ($x->query('//*[@id]') as $el) {
        $ids[$el->getAttribute('id')] = true;
    }
    foreach ($x->query('//a[@href] | //link[@href] | //script[@src] | //img[@src]') as $el) {
        $u = $el->getAttribute('href') ?: $el->getAttribute('src');
        if ($u === '#' || $u === '') {
            $issues[] = 'boş link';
            continue;
        }
        if (str_starts_with($u, '#')) {
            if (!isset($ids[substr($u, 1)])) {
                $issues[] = "anchor yok: $u";
            }
            continue;
        }
        if (!str_starts_with($u, $BASE)) {
            if (preg_match('#^https?://(instagram\.com|wa\.me|youtube\.com|strava\.com)/?$#', $u)) {
                $issues[] = "yer tutucu sosyal link: $u";
            }
            continue;
        }
        if (str_contains($u, '.html')) {
            $issues[] = "eski .html linki: $u";
        }
        $target = strtok($u, '#');
        if (!isset($linkCache[$target])) {
            $linkCache[$target] = http('GET', $target)['code'];
        }
        if (!in_array($linkCache[$target], [200], true)) {
            $issues[] = "kırık link ({$linkCache[$target]}): $u";
        }
    }
    // Tarihi geçmiş ama "başvuru açık" görünen kart olmamalı
    foreach ($x->query('//article[contains(concat(" ", @class, " ")," activity-card ")][@data-date]') as $card) {
        if ($card->getAttribute('data-date') < date('Y-m-d') && in_array($card->getAttribute('data-status'), ['open', 'few', 'full'], true)) {
            $issues[] = 'tarihi geçmiş açık kart: ' . $card->getAttribute('data-date');
        }
    }
    $issues = array_unique($issues);
    check($issues === [], $path . ($issues ? '  → ' . implode(' | ', array_slice($issues, 0, 6)) : ''));
}

/* ------------------------------------------------------------------ */
echo "\n3) Anasayfa içeriği\n";
$home = dom(http('GET', "$BASE/")['body']);
$slides = $home->query('//*[@data-slide]')->length;
$expired = array_column(App::db()->all('SELECT title FROM banners WHERE show_until < CURDATE()'), 'title');
$shownTitles = array_map(static fn ($n) => trim($n->textContent), iterator_to_array($home->query('//*[@data-slide]//*[contains(@class,"hero-title")]')));
check($slides >= 1 && $slides <= 3 && !array_intersect($expired, $shownTitles), "süresi geçen slayt gizli, en fazla 3 slayt (görünen slayt: $slides)");
check($home->query('//*[@data-slide][1]//h1')->length === 1 && $home->query('//*[@data-slide]//h1')->length === 1, 'yalnız ilk slaytta h1');
check(!str_contains($home->document->saveHTML(), '{yil}') && !str_contains($home->document->saveHTML(), '{zirve}'), 'rakam belirteçleri dolduruldu');
$homeCards = $home->query('//section[@id="takvim"]//article[contains(concat(" ", @class, " ")," activity-card ")]');
$bookable = $homeCards->length > 0;
foreach ($homeCards as $c) {
    // Yalnız başvurusu açık ya da kontenjanı dolu (yedek liste); kapalı, ertelenen, iptal ve geçmiş görünmez.
    $bookable = $bookable && in_array($c->getAttribute('data-status'), ['open', 'few', 'full'], true) && $c->getAttribute('data-end') >= date('Y-m-d');
}
$expected = min(3, count(Activity::openForApplication()));
check($homeCards->length === $expected && $bookable, "yaklaşan bölümünde yalnız başvuruya açık/dolu faaliyetler ({$homeCards->length}/{$expected})");
check($home->query('//section[@id="sosyal-takip"]')->length === 0, 'sosyal hesap girilmemişken takip bölümü gizli');
check(str_contains(http('GET', "$BASE/")['body'], 'Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü'), 'Türkçe karakterler doğru (UTF-8)');

/* ------------------------------------------------------------------ */
echo "\n4) Başvuru akışı\n";
$db = App::db();
$open = $db->one("SELECT id, slug, quota FROM activities WHERE starts_on > CURDATE() AND manual_status IS NULL AND quota > 0 ORDER BY starts_on LIMIT 1");
$closed = $db->one("SELECT slug FROM activities WHERE starts_on > CURDATE() AND manual_status = 'closed' LIMIT 1");
$detail = http('GET', "$BASE/faaliyetler/{$open['slug']}");
preg_match('/name="_csrf" value="([^"]+)"/', $detail['body'], $m);
$csrf = $m[1] ?? '';
check($csrf !== '', 'formda CSRF alanı var');
$action = "$BASE/faaliyetler/{$open['slug']}/basvuru";
$json = ['Accept: application/json'];
$valid = ['_csrf' => $csrf, 'name' => 'Test Katılımcı', 'tc' => '10000000146', 'phone' => '532 111 22 33',
    'email' => 'test-' . bin2hex(random_bytes(3)) . '@example.invalid', 'emer_name' => 'Acil Kişi', 'emer_phone' => '533 222 33 44',
    'health' => 'Polen alerjisi', 'kvkk' => '1'];

check(http('POST', $action, ['post' => ['_csrf' => 'yanlis'] + $valid, 'headers' => $json])['code'] === 419, 'yanlış CSRF → 419');
$r = http('POST', $action, ['post' => ['_csrf' => $csrf, 'tc' => '12345678901'], 'headers' => $json]);
$body = json_decode($r['body'], true);
check($r['code'] === 422 && isset($body['errors']['tc'], $body['errors']['kvkk']), 'eksik/hatalı alanlar → 422 + alan hataları');
$r = http('POST', $action, ['post' => $valid, 'headers' => $json]);
$body = json_decode($r['body'], true);
check($r['code'] === 200 && ($body['ok'] ?? false), 'geçerli başvuru kaydedildi');
$row = $db->one('SELECT * FROM applications WHERE email = ?', [$valid['email']]);
check($row !== null && $row['tc_enc'] !== '10000000146' && Crypto::decrypt($row['tc_enc']) === '10000000146', 'TC şifreli saklandı, çözülebiliyor');
check($row !== null && Crypto::decrypt($row['health_enc']) === 'Polen alerjisi' && $row['phone'] === '5321112233', 'sağlık notu şifreli, telefon normalize');
$r = http('POST', $action, ['post' => $valid, 'headers' => $json]);
check($r['code'] === 422, 'aynı e-postayla ikinci başvuru reddedildi');
$r = http('POST', $action, ['post' => ['website' => 'http://spam'] + $valid, 'headers' => $json]);
check($r['code'] === 200 && (int) $db->value('SELECT COUNT(*) FROM applications WHERE email = ?', [$valid['email']]) === 1, 'bot tuzağı: kayıt oluşmadı');
if ($closed) {
    $r = http('POST', "$BASE/faaliyetler/{$closed['slug']}/basvuru", ['post' => $valid, 'headers' => $json]);
    check($r['code'] === 409, 'başvurusu kapalı faaliyete gönderim → 409');
}
// JS kapalı akış: normal form POST → 303 yönlendirme, hatada değerler korunur
$r = http('POST', $action, ['post' => ['_csrf' => $csrf, 'name' => 'Formu Dolduran', 'email' => 'x']]);
check($r['code'] === 303 && str_contains($r['location'], '#basvuru'), 'JS kapalı: hatalı form → 303 geri');
$back = http('GET', $r['location']);
check(str_contains($back['body'], 'value="Formu Dolduran"') && str_contains($back['body'], 'is-open'), 'JS kapalı: girilen ad korunur, modal açık gelir');
check(!str_contains($back['body'], '12345678901'), 'TC oturuma/sayfaya geri yazılmaz');

// Kontenjan: dolu faaliyette yedek liste
$db->run('UPDATE activities SET quota = (SELECT COUNT(*) FROM applications WHERE activity_id = ? AND status IN ("pending","approved")) WHERE id = ?', [$open['id'], $open['id']]);
$wait = ['email' => 'wait-' . bin2hex(random_bytes(3)) . '@example.invalid'] + $valid;
$r = http('POST', $action, ['post' => $wait, 'headers' => $json]);
$body = json_decode($r['body'], true);
check($r['code'] === 200 && ($body['status'] ?? '') === 'waitlist', 'kontenjan doluyken → yedek liste');
$db->run('UPDATE activities SET quota = ? WHERE id = ?', [$open['quota'], $open['id']]);
$db->run("DELETE FROM applications WHERE email LIKE '%@example.invalid' AND name NOT LIKE 'Örnek Katılımcı%'");

/* ------------------------------------------------------------------ */
echo "\n4b) İletişim formu\n";
$page = http('GET', "$BASE/iletisim");
preg_match('/name="_csrf" value="([^"]+)"/', $page['body'], $m);
$csrf = $m[1] ?? '';
check($csrf !== '' && str_contains($page['body'], 'action="' . $BASE . '/iletisim"'), 'iletişim formu gerçek adrese POST ediyor');
$msgEmail = 'mesaj-' . bin2hex(random_bytes(3)) . '@example.invalid';
$r = http('POST', "$BASE/iletisim", ['post' => ['_csrf' => $csrf, 'name' => 'A', 'email' => 'yok', 'message' => 'kısa'], 'headers' => $json]);
$body = json_decode($r['body'], true);
check($r['code'] === 422 && isset($body['errors']['name'], $body['errors']['email'], $body['errors']['message']), 'hatalı mesaj → 422 + alan hataları');
$r = http('POST', "$BASE/iletisim", ['post' => ['_csrf' => $csrf, 'name' => 'Mesaj Testi', 'email' => $msgEmail, 'phone' => '0532 111 22 33',
    'topic' => 'uyelik', 'message' => 'Üyelik süreci hakkında bilgi almak istiyorum.'], 'headers' => $json]);
$saved = $db->one('SELECT * FROM messages WHERE email = ?', [$msgEmail]);
check($r['code'] === 200 && $saved && $saved['subject'] === 'Üyelik' && $saved['phone'] === '5321112233', 'geçerli mesaj kaydedildi (konu, telefon normalize)');
$r = http('POST', "$BASE/iletisim", ['post' => ['_csrf' => $csrf, 'name' => 'Formsuz Kişi', 'email' => 'x', 'message' => 'Bu mesaj JS kapalıyken gönderildi.']]);
$back = http('GET', strtok($r['location'], '#'));
check($r['code'] === 303 && str_contains($back['body'], 'value="Formsuz Kişi"') && str_contains($back['body'], 'Bu mesaj JS kapalıyken gönderildi.'), 'JS kapalı: hata sonrası yazılanlar korunur');
$db->run("DELETE FROM messages WHERE email LIKE '%@example.invalid'");

/* ------------------------------------------------------------------ */
echo "\n6) Yönetim paneli\n";
// Test kullanıcıları (sonda silinir)
$pw = 'test-parola-12345';
$tu = [];
foreach (['admin', 'editor', 'viewer', 'guide'] as $role) {
    $em = "test-$role@example.invalid";
    $db->run('DELETE FROM users WHERE email = ?', [$em]);
    $tu[$role] = $db->insert('users', ['name' => "Test " . ucfirst($role), 'email' => $em, 'role' => $role, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT)]);
}
$db->run("DELETE FROM login_attempts WHERE email LIKE '%@example.invalid'");
$token = static function (string $jar, string $path = '/yonetim/giris') use ($BASE): string {
    preg_match('/name="_csrf" value="([^"]+)"/', http('GET', $BASE . $path, ['jar' => $jar])['body'], $m);
    return $m[1] ?? '';
};
$login = static function (string $role) use ($BASE, $pw, $token): array {
    return http('POST', "$BASE/yonetim/giris", ['jar' => $role, 'post' => ['_csrf' => $token($role), 'email' => "test-$role@example.invalid", 'password' => $pw]]);
};

$r = http('GET', "$BASE/yonetim/faaliyetler", ['jar' => 'anon']);
check($r['code'] === 302 && str_contains($r['location'], '/yonetim/giris?next='), 'oturumsuz panel → giriş sayfası');
$r = http('POST', "$BASE/yonetim/giris", ['jar' => 'anon', 'post' => ['_csrf' => $token('anon'), 'email' => 'test-admin@example.invalid', 'password' => 'yanlis']]);
$page = http('GET', $r['location'], ['jar' => 'anon'])['body'];
check(str_contains($page, 'E-posta veya parola hatalı'), 'hatalı parola reddedilir');
for ($i = 0; $i < 5; $i++) {
    http('POST', "$BASE/yonetim/giris", ['jar' => 'brute', 'post' => ['_csrf' => $token('brute'), 'email' => 'kilit@example.invalid', 'password' => 'x']]);
}
$r = http('POST', "$BASE/yonetim/giris", ['jar' => 'brute', 'post' => ['_csrf' => $token('brute'), 'email' => 'kilit@example.invalid', 'password' => 'x']]);
check(str_contains(http('GET', $r['location'], ['jar' => 'brute'])['body'], '15 dakika'), '5 hatalı denemeden sonra kilit');
$db->run("DELETE FROM login_attempts WHERE email LIKE '%@example.invalid' OR ip IN ('127.0.0.1', '::1')");
$r = http('POST', "$BASE/yonetim/giris", ['jar' => 'admin', 'post' => ['_csrf' => $token('admin'), 'email' => 'test-admin@example.invalid', 'password' => $pw, 'next' => 'https://kotu-site.example/']]);
check($r['code'] === 303 && $r['location'] === "$BASE/yonetim", 'giriş başarılı; dış adrese yönlendirme engellendi');
check($login('editor')['code'] === 303 && $login('viewer')['code'] === 303 && $login('guide')['code'] === 303, 'editör, izleme ve rehber girişi');

// Faaliyet ekleme (sade form, açılır pencere): kapak, zorluk puanı, harita, plan; genel ödeme ve malzeme notu
$jpg = tempnam(sys_get_temp_dir(), 'kdrkimg') . '.jpg';
$im = imagecreatetruecolor(1800, 1100);
imagefill($im, 0, 0, imagecolorallocate($im, 40, 90, 140));
imagejpeg($im, $jpg, 85);

// Kapak kırpma: panel "x,y,w,h" oranları gönderir; bozuk değerde görsel kırpılmadan saklanır
$pc = static fn ($v) => \App\Core\Image::parseCrop($v);
check($pc('0.1,0.2,0.5,0.5') === [0.1, 0.2, 0.5, 0.5] && $pc('0,0,1,1') === [0.0, 0.0, 1.0, 1.0] && $pc('0.0000,0.2188,1.0000,0.5625') === [0.0, 0.2188, 1.0, 0.5625]
    && $pc('') === null && $pc('0.9,0,0.5,0.5') === null && $pc('-1,0,1,1') === null && $pc('0,0,0.001,1') === null && $pc('1e3,0,1,1') === null && $pc(['x']) === null,
    'kırpma: geçerli değer okunur, taşan/bozuk değer yok sayılır');
$cropped = \App\Core\Image::store(['error' => UPLOAD_ERR_OK, 'size' => filesize($jpg), 'tmp_name' => $jpg], 'test', $pc('0.25,0.1,0.5,0.8'));
check(\App\Core\Image::size($cropped) === [900, 880], 'kırpma: görsel seçilen alana kırpılıp küçültüldü');
\App\Core\Image::delete($cropped);
@rmdir(App::config('public_path') . '/uploads/test');
$keepNotes = ['payment_note' => setting('payment_note'), 'gear_dagcilik' => setting('gear_dagcilik')];
\App\Core\Settings::set('payment_note', 'Test ödeme notu: ücret ulaşımı kapsar.');
\App\Core\Settings::set('gear_dagcilik', "Kask\n\nKrampon\n");
$newForm = http('GET', "$BASE/yonetim/faaliyetler/yeni?pencere=1", ['jar' => 'admin'])['body'];
check(str_contains($newForm, 'data-steps="tabs"') && str_contains($newForm, 'data-modal-page') && !str_contains($newForm, 'class="sidebar"')
    && str_contains($newForm, 'data-preview') && str_contains($newForm, 'vendor/leaflet/leaflet.js'), 'yeni faaliyet: açılır pencerede hızlı form (kenar çubuğu yok, önizleme adımı, harita)');
$t = $token('admin', '/yonetim/faaliyetler/yeni');
$r = http('POST', "$BASE/yonetim/faaliyetler", ['jar' => 'admin', 'post' => ['_csrf' => $t, '_pencere' => '1', 'title' => 'x']]);
check($r['code'] === 303 && str_contains($r['location'], 'pencere=1'), 'pencerede hatalı kayıt: form pencere kipinde geri döner');
$r = http('POST', "$BASE/yonetim/faaliyetler", ['jar' => 'admin', 'multipart' => [
    '_csrf' => $t, '_pencere' => '1', 'title' => 'Test Zirvesi Tırmanışı', 'category' => 'dagcilik', 'location' => 'Rize / Test',
    'summary' => 'Panel testinden eklenen faaliyet.', 'body' => "Birinci paragraf.\n\nİkinci <b>paragraf</b>.",
    'starts_on' => date('Y-m-d', strtotime('+40 days')), 'ends_on' => date('Y-m-d', strtotime('+41 days')),
    'difficulty_score' => '7', 'distance_km' => '12,5', 'lat' => '40.832112', 'lng' => '41.110355', 'meeting_text' => 'Rize otogarı · 06:30',
    'quota' => '2', 'fee' => '1250', 'is_published' => '1', 'cover' => new CURLFile($jpg, 'image/jpeg', 'kapak.jpg'),
]]);
$act = $db->one("SELECT * FROM activities WHERE title = 'Test Zirvesi Tırmanışı'");
check($r['code'] === 200 && str_contains($r['body'], "kdrk: 'go'") && $act !== null && str_contains($r['body'], '/yonetim/faaliyetler/' . $act['id'])
    && $act['slug'] === 'test-zirvesi-tirmanisi-' . substr($act['starts_on'], 0, 4), 'faaliyet pencereden eklendi; pencere ana sayfayı düzenleme sayfasına götürür');
$r2 = http('POST', "$BASE/yonetim/faaliyetler", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Zirvesi Tırmanışı', 'category' => 'sosyal', 'location' => 'Rize',
    'summary' => 'Aynı adlı ikinci faaliyet.', 'starts_on' => $act['starts_on'], 'no_difficulty' => '1', 'quota' => '0']]);
$dup = $db->one("SELECT * FROM activities WHERE slug = ?", [$act['slug'] . '-2']);
check($r2['code'] === 303 && $dup && $dup['difficulty'] === null && $dup['difficulty_score'] === null, 'aynı adlı faaliyete adres -2 ile verilir; "zorluk yok" seçilebilir');
if ($dup) {
    $db->run('DELETE FROM activities WHERE id = ?', [$dup['id']]);
}
check((int) $act['difficulty_score'] === 7 && (int) $act['difficulty'] === 4 && (float) $act['distance_km'] === 12.5 && (float) $act['lat'] === 40.832112 && (int) $act['is_training'] === 0,
    'zorluk puanı 7 → 4. kademe "Orta-Zor"; mesafe ve harita konumu kaydedildi');
// Zorluk: 1–10 puan → 5 kademe, her yerde aynı ad ve 5 ikonluk gösterge (CLAUDE.md §1)
$levelsOk = true;
foreach ([1 => [1, 'Kolay'], 2 => [1, 'Kolay'], 3 => [2, 'Kolay-Orta'], 4 => [2, 'Kolay-Orta'], 5 => [3, 'Orta'], 6 => [3, 'Orta'], 7 => [4, 'Orta-Zor'], 8 => [4, 'Orta-Zor'], 9 => [5, 'Zor'], 10 => [5, 'Zor']] as $sc => [$lv, $name]) {
    $levelsOk = $levelsOk && \App\Models\Activity::levelOf($sc) === $lv && \App\Models\Activity::LEVELS[$lv][1] === $name;
}
$actPage = http('GET', "$BASE/faaliyetler/{$act['slug']}")['body'];
check($levelsOk && str_contains($actPage, 'Orta-Zor · 7/10') && substr_count($actPage, 'class="is-filled"') >= 4 && !str_contains($actPage, 'Çok zor'), 'zorluk: puan → 5 kademe; detayda ad, puan ve dolu dağ ikonları aynı kademeden');
$coverOk = $act['cover_image'] && is_file(App::config('public_path') . "/{$act['cover_image']}-900.webp") && is_file(App::config('public_path') . "/{$act['cover_image']}-1600.webp") && !is_file(App::config('public_path') . "/{$act['cover_image']}-2400.webp");
check($coverOk, 'kapak WebP türevleri üretildi (özgünden büyük türev yok)');
$site = http('GET', "$BASE/faaliyetler/{$act['slug']}")['body'];
check(str_contains($site, 'Krampon') && str_contains($site, 'Test ödeme notu') && str_contains($site, '-1600.webp') && str_contains($site, '₺1.250')
    && str_contains($site, '7/10') && str_contains($site, 'Rize otogarı') && str_contains($site, 'data-detail-map') && str_contains($site, 'openstreetmap.org/?mlat=40.832112'),
    'yeni faaliyet sitede: kategori malzeme notu, genel ödeme notu, kapak, ücret, puan, buluşma, harita');
check(str_contains($site, '&lt;b&gt;paragraf&lt;/b&gt;') && !str_contains($site, '<b>paragraf</b>'), 'açıklamadaki HTML kaçışlı (XSS yok)');

$r = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => '', 'category' => 'dagcilik', 'location' => 'x', 'summary' => 'kısa',
    'starts_on' => '2027-05-10', 'ends_on' => '2027-05-01']]);
$form = http('GET', $r['location'], ['jar' => 'admin'])['body'];
check(str_contains($form, 'Faaliyet adını yaz') && str_contains($form, 'Bitiş tarihi başlangıçtan önce olamaz') && str_contains($form, 'value="x"') && str_contains($form, 'data-steps="tabs"'), 'hatalı form: alan hataları + girilen değer korunur; düzenleme sekmeli');
$r = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => $act['title'], 'category' => 'dagcilik', 'location' => 'x', 'summary' => $act['summary'],
    'starts_on' => $act['starts_on'], 'difficulty_score' => '7', 'lat' => '999', 'lng' => '41']]);
check(str_contains(http('GET', $r['location'], ['jar' => 'admin'])['body'], 'konum okunamadı'), 'geçersiz harita konumu reddedilir');
check(!str_contains(http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin'])['body'], 'Albüm oluştur'), 'albüm oluştur düğmesi faaliyet tamamlanmadan görünmez');
check(http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => ['title' => 'x']])['code'] === 419, 'CSRF olmadan panel POST → 419');

// Başvuru → onay → listeler
$pub = http('GET', "$BASE/faaliyetler/{$act['slug']}", ['jar' => 'site']);
preg_match('/name="_csrf" value="([^"]+)"/', $pub['body'], $m);
http('POST', "$BASE/faaliyetler/{$act['slug']}/basvuru", ['jar' => 'site', 'headers' => $json, 'post' => ['_csrf' => $m[1], 'name' => 'Panel Testi', 'tc' => '10000000146',
    'phone' => '5321112233', 'email' => 'panel-test@example.invalid', 'emer_name' => 'Acil Kişi', 'emer_phone' => '5332223344', 'health' => 'Gizli sağlık notu', 'kvkk' => '1']]);
$app = $db->one("SELECT id, status FROM applications WHERE email = 'panel-test@example.invalid'");
check(str_contains(http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/basvurular?durum=pending", ['jar' => 'admin'])['body'], 'class="btn btn-sm pa-approve" name="status" value="approved"'), 'başvurular: bekleyen başvuruda Onayla satırda (menüye gerek yok)');
$t = $token('admin', "/yonetim/faaliyetler/{$act['id']}/basvurular");
$r = http('POST', "$BASE/yonetim/basvurular/{$app['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'status' => 'approved']]);
check($r['code'] === 303 && $db->value('SELECT status FROM applications WHERE id = ?', [$app['id']]) === 'approved', 'admin başvuruyu onayladı');
http('POST', "$BASE/yonetim/basvurular/{$app['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'transport' => 'club', 'pickup' => 'Rize Otogar']]);
check($db->one('SELECT transport, pickup FROM applications WHERE id = ?', [$app['id']]) === ['transport' => 'club', 'pickup' => 'Rize Otogar'], 'ulaşım ve biniş noktası kaydedildi');
$adminList = http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/basvurular", ['jar' => 'admin'])['body'];
$editorList = http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/basvurular", ['jar' => 'editor'])['body'];
check(str_contains($adminList, '10000000146') && str_contains($adminList, 'Gizli sağlık notu'), 'admin TC ve sağlık notunu görür');
check(str_contains($editorList, 'Panel Testi') && !str_contains($editorList, '10000000146') && !str_contains($editorList, 'Gizli sağlık notu'), 'editör TC ve sağlık notunu GÖRMEZ');
$print = http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/liste/tasima", ['jar' => 'admin'])['body'];
check(str_contains($print, 'Rize Otogar · 1 kişi') && str_contains($print, '10000000146'), 'taşımacılık listesi biniş noktasına göre gruplu');
check(!str_contains(http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/liste/katilim", ['jar' => 'editor'])['body'], '10000000146'), 'editörün katılım listesinde TC yok');

// Dışa aktar menüsü (PDF / Excel) ve faaliyet günü yoklaması
$L = "$BASE/yonetim/faaliyetler/{$act['id']}/liste";
$apL = "$BASE/yonetim/faaliyetler/{$act['id']}/basvurular";
check(str_contains($adminList, 'class="pa-head"') && str_contains($adminList, "/yonetim/faaliyetler/{$act['id']}/yoklama") && str_contains($adminList, 'class="pa-phone mono" href="tel:+905321112233"'), 'başvurular: ortak şerit (Başvurular ↔ Yoklama) ve satırda aranabilir telefon');
check(str_contains(http('GET', "$apL?q=" . rawurlencode('Panel Tes'), ['jar' => 'admin'])['body'], 'Panel Testi') && str_contains(http('GET', "$apL?q=" . rawurlencode('0532 111'), ['jar' => 'admin'])['body'], 'Panel Testi') && !str_contains(http('GET', "$apL?q=zzzyok", ['jar' => 'admin'])['body'], 'Panel Testi'), 'başvurular: isim ve telefonla (0532 111) arama, eşleşmeyen gizli');
check(str_contains($adminList, 'data-export-menu') && str_contains($adminList, 'bicim=pdf') && str_contains($adminList, 'bicim=excel') && str_contains($adminList, '/yoklama'), 'başvurular: dışa aktar menüsü (PDF, Excel) ve yoklama bağlantısı');
$csv = http('GET', "$L/katilim?bicim=excel", ['jar' => 'admin']);
check(str_starts_with($csv['body'], "\xEF\xBB\xBF") && str_contains($csv['body'], '"Ad Soyad"') && str_contains($csv['body'], '"Panel Testi"') && str_contains($csv['body'], '10000000146'), 'Excel: katılım listesi CSV (BOM, başlık, katılımcı)');
check(!str_contains(http('GET', "$L/katilim?bicim=excel", ['jar' => 'editor'])['body'], '10000000146'), 'Excel: editörün listesinde TC yok');
check(str_contains(http('GET', "$L/tasima?bicim=excel", ['jar' => 'admin'])['body'], '"Rize Otogar"'), 'Excel: taşımacılık listesi biniş noktalı');
check(str_contains(http('GET', "$L/katilim?bicim=pdf", ['jar' => 'admin'])['body'], 'window.print()'), 'PDF: yazdırma/PDF penceresi otomatik açılır');
$ck = http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/yoklama", ['jar' => 'admin']);
check($ck['code'] === 200 && str_contains($ck['body'], 'Panel Testi') && str_contains($ck['body'], 'tel:+905321112233') && str_contains($ck['body'], 'class="ck-phone"') && str_contains($ck['body'], '0532 111 22 33'), 'yoklama: kayıtlı numara görünür ve dokununca arar');
$tk = $token('admin', "/yonetim/faaliyetler/{$act['id']}/yoklama");
$r = http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'admin', 'headers' => $json, 'post' => ['_csrf' => $tk, 'attended' => '1']]);
$j = json_decode($r['body'], true) ?: [];
check(($j['ok'] ?? false) && $j['attended'] === 1 && ($j['summary']['came'] ?? 0) === 1 && (int) $db->value('SELECT attended FROM applications WHERE id = ?', [$app['id']]) === 1, 'yoklama: geldi işaretlendi (JSON + özet)');
$feeBefore = $db->value('SELECT fee FROM activities WHERE id = ?', [$act['id']]);
$db->run('UPDATE activities SET fee = 1500 WHERE id = ?', [$act['id']]);
$r = http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'admin', 'post' => ['_csrf' => $tk, 'paid' => '1']]);
$pa = $db->one('SELECT paid, paid_at, paid_amount FROM applications WHERE id = ?', [$app['id']]);
check($r['code'] === 303 && (int) $pa['paid'] === 1 && $pa['paid_at'] !== null && (float) $pa['paid_amount'] === 1500.0, 'yoklama: ödedi (JS kapalı form), tutar varsayılan faaliyet ücreti');
$j = json_decode(http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'admin', 'headers' => $json, 'post' => ['_csrf' => $tk, 'paid' => '1', 'amount' => '1.250']])['body'], true) ?: [];
check((float) ($j['amount'] ?? 0) === 1250.0 && ($j['amountText'] ?? '') === '1.250' && ($j['summary']['amountText'] ?? '') === '₺1.250' && $db->value('SELECT paid_at FROM applications WHERE id = ?', [$app['id']]) === $pa['paid_at'], 'yoklama: tutar değiştirilebilir (1.250), ödeme zamanı korunur');
$r = http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'admin', 'headers' => $json, 'post' => ['_csrf' => $tk, 'amount' => 'abc']]);
check($r['code'] === 422 && str_contains($r['body'], 'Geçerli bir tutar') && (float) $db->value('SELECT paid_amount FROM applications WHERE id = ?', [$app['id']]) === 1250.0, 'yoklama: geçersiz tutar reddedilir, kayıtlı tutar korunur');
check(str_contains(http('GET', "$L/katilim?bicim=excel", ['jar' => 'admin'])['body'], '"1250,00"') && str_contains(http('GET', "$L/katilim?bicim=pdf", ['jar' => 'admin'])['body'], '₺1.250'), 'Excel ve PDF: ödenen tutar listede');
http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'admin', 'headers' => $json, 'post' => ['_csrf' => $tk, 'attended' => '']]);
check($db->value('SELECT attended FROM applications WHERE id = ?', [$app['id']]) === null, 'yoklama: ikinci dokunuş işareti kaldırır');
check(str_contains(http('GET', "$L/katilim?bicim=excel", ['jar' => 'admin'])['body'], '"Ödedi"'), 'Excel: ücret durumu listede');
http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'admin', 'headers' => $json, 'post' => ['_csrf' => $tk, 'paid' => '0']]);
check($db->value('SELECT paid_amount FROM applications WHERE id = ?', [$app['id']]) === null, 'yoklama: ödemedi seçilince tutar silinir');
$db->run('UPDATE activities SET fee = ? WHERE id = ?', [$feeBefore, $act['id']]);
$tvk = $token('viewer', "/yonetim/faaliyetler/{$act['id']}/yoklama");
check(http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'viewer', 'post' => ['_csrf' => $tvk, 'attended' => '1']])['code'] === 403, 'yoklama: izleme rolü işaretleyemez');
check(http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}/yoklama", ['jar' => 'guide'])['code'] === 403, 'yoklama: atanmamış rehber göremez');
$db->run('UPDATE activities SET guide_primary_user_id = ? WHERE id = ?', [$tu['guide'], $act['id']]);
$tg = $token('guide', "/yonetim/faaliyetler/{$act['id']}/yoklama");
$r = http('POST', "$BASE/yonetim/basvurular/{$app['id']}/yoklama", ['jar' => 'guide', 'headers' => $json, 'post' => ['_csrf' => $tg, 'attended' => '0']]);
check((json_decode($r['body'], true)['attended'] ?? null) === 0, 'yoklama: atanmış rehber gelmedi işaretler');
$db->run('UPDATE activities SET guide_primary_user_id = NULL WHERE id = ?', [$act['id']]);

// Yetkiler
$te = $token('editor', '/yonetim');
check(http('GET', "$BASE/yonetim/ayarlar", ['jar' => 'editor'])['code'] === 403 && http('GET', "$BASE/yonetim/kullanicilar", ['jar' => 'editor'])['code'] === 403, 'editör ayarlara ve kullanıcılara giremez');
check(http('POST', "$BASE/yonetim/basvurular/{$app['id']}/sil", ['jar' => 'editor', 'post' => ['_csrf' => $te]])['code'] === 403, 'editör başvuru silemez (yalnızca admin)');
$tv = $token('viewer', '/yonetim');
check(http('GET', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'viewer'])['code'] === 200, 'izleme rolü faaliyeti görür');
check(http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'viewer', 'post' => ['_csrf' => $tv, 'title' => 'Değişti']])['code'] === 403, 'izleme rolü değişiklik yapamaz');
check(http('GET', "$BASE/yonetim/mesajlar", ['jar' => 'viewer'])['code'] === 403, 'izleme rolü mesajları göremez');

// Durum: elle kapatma sitede görünür
$t = $token('admin', "/yonetim/faaliyetler/{$act['id']}");
$fields = ['_csrf' => $t, 'title' => $act['title'], 'category' => 'dagcilik', 'location' => 'Rize / Test', 'summary' => $act['summary'], 'starts_on' => $act['starts_on'],
    'ends_on' => $act['ends_on'], 'difficulty_score' => '7', 'quota' => '2', 'slug' => $act['slug'], 'is_published' => '1', 'manual_status' => 'closed'];
http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $fields]);
check(str_contains(http('GET', "$BASE/faaliyetler/{$act['slug']}")['body'], 'başvuruları kapandı'), 'elle kapatılan faaliyet sitede "kapandı"');
$r = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}/sil", ['jar' => 'admin', 'post' => ['_csrf' => $t]]);
check($db->value('SELECT COUNT(*) FROM activities WHERE id = ?', [$act['id']]) == 1, 'başvurusu olan faaliyet silinemez');

// Ayarlar siteye yansır; bakım modu
$t = $token('admin', '/yonetim/ayarlar');
$settings = ['_csrf' => $t, 'stat_kurulus' => '1998', 'stat_zirve' => '512', 'stat_yuruyus' => '1000', 'stat_arama_kurtarma' => '25',
    'contact_email' => 'info@kdrk.org.tr', 'contact_phone' => '+90 (464) 651 00 00', 'contact_address' => setting('contact_address'),
    'social_instagram' => 'https://instagram.com/kdrk_test', 'social_whatsapp' => '', 'social_youtube' => '', 'social_strava' => '',
    'maintenance' => '1', 'maintenance_title' => 'Test bakımı', 'maintenance_message' => 'Kısa süre.', 'maintenance_eta' => '', 'kvkk_retention_days' => '365',
    'payment_note' => 'Test ödeme notu: ücret ulaşımı kapsar.', 'gear_dagcilik' => "Kask\nKrampon"];
check(str_contains(http('GET', "$BASE/yonetim/ayarlar", ['jar' => 'admin'])['body'], 'name="gear_egitim"'), 'ayarlar: her kategori için malzeme notu alanı var');
$r = http('POST', "$BASE/yonetim/ayarlar", ['jar' => 'admin', 'post' => ['social_youtube' => 'javascript:alert(1)'] + $settings]);
check(str_contains(http('GET', $r['location'], ['jar' => 'admin'])['body'], 'https:// ile başlayan'), 'sosyal linkte yalnızca https kabul edilir');
http('POST', "$BASE/yonetim/ayarlar", ['jar' => 'admin', 'post' => $settings]);
$anon = http('GET', "$BASE/", ['jar' => 'anon2']);
check($anon['code'] === 503 && str_contains($anon['body'], 'Test bakımı'), 'bakım modu: ziyaretçi 503 görür');
$adm = http('GET', "$BASE/", ['jar' => 'admin']);
check($adm['code'] === 200 && str_contains($adm['body'], 'https://instagram.com/kdrk_test') && str_contains($adm['body'], '"zirve":512'), 'admin siteyi görür; yeni rakam ve Instagram linki yayında');
http('POST', "$BASE/yonetim/ayarlar", ['jar' => 'admin', 'post' => ['maintenance' => '0', 'social_instagram' => '', 'stat_zirve' => '500'] + $settings]);
check(http('GET', "$BASE/", ['jar' => 'anon2'])['code'] === 200, 'bakım modu kapatıldı');

// Kullanıcılar
$t = $token('admin', '/yonetim/kullanicilar/yeni');
$r = http('POST', "$BASE/yonetim/kullanicilar/{$tu['admin']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'name' => 'Test Admin', 'email' => 'test-admin@example.invalid', 'role' => 'viewer', 'is_active' => '1']]);
check($db->value('SELECT role FROM users WHERE id = ?', [$tu['admin']]) === 'admin', 'admin kendi yetkisini düşüremez');
$r = http('POST', "$BASE/yonetim/kullanicilar", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'name' => 'Kısa Parola', 'email' => 'kisa@example.invalid', 'role' => 'editor', 'password' => '123', 'is_active' => '1']]);
check(!$db->value("SELECT COUNT(*) FROM users WHERE email = 'kisa@example.invalid'"), 'kısa parolalı kullanıcı oluşturulmaz');

// Galeri: albüm, fotoğraf yükleme, sıralama, kapak, silme (CLAUDE.md §15 dosya yükleme)
$t = $token('admin', '/yonetim/galeri/yeni');
$r = http('POST', "$BASE/yonetim/galeri", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Albüm']]);
$form = http('GET', $r['location'], ['jar' => 'admin'])['body'];
check($r['code'] === 303 && str_contains($form, 'tarih gerekli') && str_contains($form, 'kategori seç') && str_contains($form, 'value="Test Albüm"'), 'galeri: bağsız albümde tarih/kategori zorunlu, girilen ad korunur');
$r = http('POST', "$BASE/yonetim/galeri", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Albüm', 'activity_id' => $act['id'], 'is_published' => '1', 'description' => '<b>açıklama</b>']]);
$alb = $db->one("SELECT * FROM gallery_albums WHERE title = 'Test Albüm'");
check($r['code'] === 303 && $alb && $alb['activity_id'] == $act['id'] && $alb['slug'] === 'test-album-' . substr($act['starts_on'], 0, 4), 'galeri: faaliyete bağlı albüm oluşturuldu, adres üretildi');
check(http('GET', "$BASE/galeri/{$alb['slug']}")['code'] === 404, 'galeri: fotoğrafı olmayan albüm sitede görünmez');
$pre = http('GET', "$BASE/yonetim/galeri/yeni?faaliyet={$act['id']}&pencere=1", ['jar' => 'admin'])['body'];
check(str_contains($pre, 'value="Test Zirvesi Tırmanışı, Rize / Test, Dağcılık, ' . substr($act['starts_on'], 0, 4) . '"') && str_contains($pre, 'name="taken_on" type="date" value=""'),
    'galeri: albüm adı ve etiketler hazır gelir; tarih faaliyetten miras alınır');
$db->run("UPDATE gallery_albums SET tags = 'Testetiketi Vadisi, 2026' WHERE id = ?", [$alb['id']]);

$png = tempnam(sys_get_temp_dir(), 'kdrkimg') . '.png';
$im2 = imagecreatetruecolor(1000, 1400);
imagefill($im2, 0, 0, imagecolorallocate($im2, 200, 120, 40));
imagepng($im2, $png);
$txt = tempnam(sys_get_temp_dir(), 'kdrktxt') . '.jpg';
file_put_contents($txt, '<?php echo "x"; ?>');
$t = $token('admin', "/yonetim/galeri/{$alb['id']}");
$r = http('POST', "$BASE/yonetim/galeri/{$alb['id']}/fotograf", ['jar' => 'admin', 'headers' => ['Accept: application/json'], 'multipart' => [
    '_csrf' => $t, 'photos[0]' => new CURLFile($jpg, 'image/jpeg', 'zirve.jpg'), 'photos[1]' => new CURLFile($png, 'image/png', 'dikey.png'),
    'photos[2]' => new CURLFile($txt, 'image/jpeg', 'sahte.jpg'),
]]);
$j = json_decode($r['body'], true);
$ph = $db->all('SELECT * FROM gallery_photos WHERE album_id = ? ORDER BY sort', [$alb['id']]);
check(($j['ok'] ?? false) && count($j['added']) === 2 && count($j['errors']) === 1 && str_contains($j['errors'][0], 'sahte.jpg'), 'galeri: 2 görsel yüklendi, görsel olmayan dosya reddedildi');
$root = App::config('public_path');
check(count($ph) === 2 && is_file("$root/{$ph[0]['path']}-1600.webp") && !is_file("$root/{$ph[0]['path']}-2400.webp") && !str_contains($ph[0]['path'], 'zirve'), 'galeri: WebP türevleri üretildi, özgün ad diske yazılmadı');
check($ph[1]['width'] && abs($ph[1]['height'] / $ph[1]['width'] - 1.4) < 0.01, 'galeri: dikey fotoğrafın oranı kaydedildi');
check(glob("$root/uploads/gallery/*.jpg") === [] && glob("$root/uploads/gallery/*.php") === [], 'galeri: uploads içinde özgün/çalıştırılabilir dosya yok');

http('POST', "$BASE/yonetim/fotograflar/{$ph[1]['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'do' => 'save', 'caption' => 'Zirve <script>x</script>', 'credit' => 'T. Test']]);
http('POST', "$BASE/yonetim/fotograflar/{$ph[1]['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'do' => 'up']]);
http('POST', "$BASE/yonetim/fotograflar/{$ph[1]['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'do' => 'cover']]);
$order = array_map('intval', array_column($db->all('SELECT id FROM gallery_photos WHERE album_id = ? ORDER BY sort', [$alb['id']]), 'id'));
check($order === [(int) $ph[1]['id'], (int) $ph[0]['id']] && (int) $db->value('SELECT cover_photo_id FROM gallery_albums WHERE id = ?', [$alb['id']]) === (int) $ph[1]['id'], 'galeri: sıralama ve kapak seçimi');
$site = http('GET', "$BASE/galeri/{$alb['slug']}")['body'];
check(str_contains($site, 'masonry-tile') && str_contains($site, '© T. Test') && str_contains($site, 'Zirve &lt;script&gt;') && !str_contains($site, '<script>x</script>') && !str_contains($site, '<b>açıklama</b>'), 'galeri: albüm sitede, açıklamalar kaçışlı (XSS yok)');
check(str_contains(http('GET', "$BASE/galeri")['body'], 'Test Albüm'), 'galeri: albüm listede görünüyor');
check(str_contains(http('GET', "$BASE/galeri?q=" . rawurlencode('Testetiketi'))['body'], 'Test Albüm') && str_contains(http('GET', "$BASE/galeri?q=" . rawurlencode('Zirvesi Tırmanışı'))['body'], 'Test Albüm')
    && str_contains(http('GET', "$BASE/yonetim/galeri?q=Testetiketi", ['jar' => 'admin'])['body'], 'Test Albüm'), 'galeri: etiket ve faaliyet adıyla sitede ve panelde aranır');
check(str_contains($site, 'class="gd-tags"') && str_contains($site, 'Testetiketi Vadisi'), 'galeri: albüm sayfasında etiketler bağlantı olarak görünür');

$tv = $token('viewer', '/yonetim');
check(http('GET', "$BASE/yonetim/galeri/{$alb['id']}", ['jar' => 'viewer'])['code'] === 200, 'galeri: izleme rolü albümü görür');
check(http('POST', "$BASE/yonetim/fotograflar/{$ph[0]['id']}", ['jar' => 'viewer', 'post' => ['_csrf' => $tv, 'do' => 'delete']])['code'] === 403
    && http('POST', "$BASE/yonetim/galeri/{$alb['id']}/fotograf", ['jar' => 'viewer', 'multipart' => ['_csrf' => $tv, 'photos[0]' => new CURLFile($jpg, 'image/jpeg', 'a.jpg')]])['code'] === 403, 'galeri: izleme rolü yükleyemez ve silemez');

http('POST', "$BASE/yonetim/fotograflar/{$ph[1]['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'do' => 'delete']]);
check(!is_file("$root/{$ph[1]['path']}-900.webp") && $db->value('SELECT cover_photo_id FROM gallery_albums WHERE id = ?', [$alb['id']]) === null, 'galeri: fotoğraf silinince dosyaları ve kapak bağı kalktı');
http('POST', "$BASE/yonetim/galeri/{$alb['id']}/sil", ['jar' => 'admin', 'post' => ['_csrf' => $t]]);
check(!$db->value('SELECT COUNT(*) FROM gallery_albums WHERE id = ?', [$alb['id']]) && !is_file("$root/{$ph[0]['path']}-900.webp"), 'galeri: albüm silinince fotoğraf dosyaları da silindi');
@unlink($png);
@unlink($txt);

// Duyurular
$t = $token('admin', '/yonetim/duyurular/yeni');
$r = http('POST', "$BASE/yonetim/duyurular", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Duyurusu Başlık', 'status' => 'published']]);
$form = http('GET', $r['location'], ['jar' => 'admin'])['body'];
check(str_contains($form, 'Haber bir yere götürmeli') && str_contains($form, 'value="Test Duyurusu Başlık"'), 'duyuru: metni/bağlantısı olmayan haber reddedilir, değer korunur');
$r = http('POST', "$BASE/yonetim/duyurular", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Duyurusu Başlık', 'link_url' => 'javascript:alert(1)']]);
check(str_contains(http('GET', $r['location'], ['jar' => 'admin'])['body'], 'Site içi bir yol'), 'duyuru: javascript: bağlantısı reddedilir');
$r = http('POST', "$BASE/yonetim/duyurular", ['jar' => 'admin', 'multipart' => ['_csrf' => $t, 'title' => 'Test Duyurusu Başlık', 'category' => 'duyuru', 'status' => 'published', 'is_featured' => '1',
    'excerpt' => 'Kısa özet', 'body' => "Birinci paragraf <script>x</script>\n\nİkinci paragraf", 'cover' => new CURLFile($jpg, 'image/jpeg', 'kapak.jpg')]]);
$news = $db->one("SELECT * FROM news WHERE title = 'Test Duyurusu Başlık'");
check($r['code'] === 303 && $news && $news['slug'] === 'test-duyurusu-baslik' && $news['published_on'] === date('Y-m-d') && $news['cover_image'], 'duyuru: yayınlandı, tarih bugün, kapak yüklendi');
$page = http('GET', "$BASE/duyurular/{$news['slug']}");
check($page['code'] === 200 && substr_count($page['body'], '<p>') >= 2 && str_contains($page['body'], '&lt;script&gt;') && !str_contains($page['body'], '<script>x</script>'), 'duyuru: kendi sayfası var, paragraflar ve kaçış doğru');
$home = http('GET', "$BASE/")['body'];
check(preg_match('#href="[^"]*/duyurular/test-duyurusu-baslik" class="news-feature#', $home) === 1, 'duyuru: öne çıkan haber Anasayfada büyük kartta');
http('POST', "$BASE/yonetim/duyurular/{$news['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Duyurusu Başlık', 'body' => 'metin', 'status' => 'published', 'published_on' => date('Y-m-d', strtotime('+3 days'))]]);
check(http('GET', "$BASE/duyurular/{$news['slug']}")['code'] === 404 && !str_contains(http('GET', "$BASE/")['body'], 'Test Duyurusu'), 'duyuru: ileri tarihli haber o güne kadar görünmez');
$tv = $token('viewer', '/yonetim');
check(http('GET', "$BASE/yonetim/duyurular/{$news['id']}", ['jar' => 'viewer'])['code'] === 200
    && http('POST', "$BASE/yonetim/duyurular/{$news['id']}/sil", ['jar' => 'viewer', 'post' => ['_csrf' => $tv]])['code'] === 403, 'duyuru: izleme rolü görür, silemez');
http('POST', "$BASE/yonetim/duyurular/{$news['id']}/sil", ['jar' => 'admin', 'post' => ['_csrf' => $t]]);
check(!$db->value('SELECT COUNT(*) FROM news WHERE id = ?', [$news['id']]) && !is_file(App::config('public_path') . "/{$news['cover_image']}-900.webp"), 'duyuru: silindi, kapak dosyası da gitti');

// Anasayfa slaytları
$t = $token('admin', '/yonetim/slaytlar/yeni');
$r = http('POST', "$BASE/yonetim/slaytlar", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Slaytı', 'cta_label' => 'Git', 'is_active' => '1']]);
$form = http('GET', $r['location'], ['jar' => 'admin'])['body'];
check(str_contains($form, 'bir görsel seç') && str_contains($form, 'hem yazı hem bağlantı'), 'slayt: görselsiz ve bağlantısız buton reddedilir');
$r = http('POST', "$BASE/yonetim/slaytlar", ['jar' => 'admin', 'multipart' => ['_csrf' => $t, 'title' => 'Test Slaytı {yil} yıl', 'cta_label' => 'Takvim', 'cta_url' => '/faaliyetler',
    'is_active' => '1', 'image' => new CURLFile($jpg, 'image/jpeg', 'slayt.jpg'), 'image_crop' => '0.5,0,0.5,0.5']]);
$bn = $db->one("SELECT * FROM banners WHERE title LIKE 'Test Slaytı%'");
check(\App\Core\Image::size($bn['image']) === [900, 550], 'kırpma: slayt görseli seçilen alana kırpıldı (1800×1100 → 900×550)');
check($r['code'] === 303 && $bn && $bn['image'] && (int) $bn['sort'] === (int) $db->value('SELECT MAX(sort) FROM banners'), 'slayt: eklendi, sıranın sonunda');
$live = array_column(\App\Models\Banner::visible(), 'id');
check(count($live) <= 3, 'slayt: sitede en fazla 3 slayt');
for ($i = 0; $i < 6; $i++) {
    http('POST', "$BASE/yonetim/slaytlar/{$bn['id']}/tasi", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'do' => 'up']]);
}
$home = http('GET', "$BASE/")['body'];
check(\App\Models\Banner::visible()[0]['id'] == $bn['id'] && str_contains($home, 'Test Slaytı ' . club_stat('yil') . ' yıl</h1>'), 'slayt: en üste taşındı, h1 oldu, {yil} doldu');
// Tanıtım slaytında tarih alanı yok; eski kayıtlardaki tarih sınırı korunur
http('POST', "$BASE/yonetim/slaytlar/{$bn['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Slaytı', 'is_active' => '1', 'show_until' => date('Y-m-d', strtotime('+10 days'))]]);
check($db->value('SELECT show_until FROM banners WHERE id = ?', [$bn['id']]) === null && !str_contains(http('GET', "$BASE/yonetim/slaytlar/{$bn['id']}", ['jar' => 'admin'])['body'], 'name="show_until"'), 'slayt: tanıtım slaytında tarih alanı yok');
$db->run('UPDATE banners SET show_until = ? WHERE id = ?', [date('Y-m-d', strtotime('+10 days')), $bn['id']]);
check(str_contains(http('GET', "$BASE/yonetim/slaytlar", ['jar' => 'admin'])['body'], 'tarihsiz bir slayt taşı'), 'slayt: bitiş tarihli (eski) slayt ilk sıradaysa panel uyarır');
http('POST', "$BASE/yonetim/slaytlar/{$bn['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Slaytı', 'is_active' => '1']]);
check($db->value('SELECT show_until FROM banners WHERE id = ?', [$bn['id']]) !== null, 'slayt: kaydetmek eski tarih sınırını silmez');
$db->run('UPDATE banners SET show_until = ? WHERE id = ?', [date('Y-m-d', strtotime('-1 day')), $bn['id']]);
check(!str_contains(http('GET', "$BASE/")['body'], 'Test Slaytı'), 'slayt: süresi geçen slayt sitede görünmez');
$tv = $token('viewer', '/yonetim');
check(http('POST', "$BASE/yonetim/slaytlar/{$bn['id']}/tasi", ['jar' => 'viewer', 'post' => ['_csrf' => $tv, 'do' => 'down']])['code'] === 403, 'slayt: izleme rolü sırayı değiştiremez');
http('POST', "$BASE/yonetim/slaytlar/{$bn['id']}/sil", ['jar' => 'admin', 'post' => ['_csrf' => $t]]);
check(!$db->value('SELECT COUNT(*) FROM banners WHERE id = ?', [$bn['id']]) && !is_file(App::config('public_path') . "/{$bn['image']}-900.webp"), 'slayt: silindi, görsel dosyası da gitti');
// Silinen slayttan önceki sırayı geri getir (diğer testler ilk slayta bakar)
foreach ($db->all('SELECT id FROM banners ORDER BY sort, id') as $n => $row) {
    $db->run('UPDATE banners SET sort = ? WHERE id = ?', [$n + 1, $row['id']]);
}

// Faaliyet slaytı
$t = $token('admin', '/yonetim/slaytlar?sekme=faaliyet');
$tv = $token('viewer', '/yonetim');
check(http('POST', "$BASE/yonetim/slaytlar/faaliyet", ['jar' => 'viewer', 'post' => ['_csrf' => $tv, 'activity_id' => $act['id'], 'do' => 'add']])['code'] === 403, 'faaliyet slaytı: izleme rolü ekleyemez');
$db->run("INSERT INTO activities (slug, title, category, starts_on, location, summary, difficulty) VALUES ('test-kapaksiz-slayt', 'Test Kapaksız', 'gunubirlik', ?, 'Rize', 'Kapaksız test faaliyeti', 1)", [date('Y-m-d', strtotime('+20 days'))]);
$noCover = (int) $db->value("SELECT id FROM activities WHERE slug = 'test-kapaksiz-slayt'");
http('POST', "$BASE/yonetim/slaytlar/faaliyet", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'activity_id' => $noCover, 'do' => 'add']]);
check((int) $db->value('SELECT in_slider FROM activities WHERE id = ?', [$noCover]) === 0, 'faaliyet slaytı: kapak görseli olmayan faaliyet eklenemez');
$db->run('DELETE FROM activities WHERE id = ?', [$noCover]);
$r = http('POST', "$BASE/yonetim/slaytlar/faaliyet", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'activity_id' => $act['id'], 'do' => 'add']]);
$fa = \App\Models\Activity::findBySlug($act['slug']);
$home = dom(http('GET', "$BASE/")['body']);
$slideTitles = array_map(static fn ($n) => trim($n->textContent), iterator_to_array($home->query('//*[@data-slide]//*[contains(@class,"hero-title")]')));
$actSlide = $home->query('//*[@data-slide][.//*[contains(@class,"hero-title")][normalize-space()="' . $fa['title'] . '"]]')->item(0);
$links = $actSlide ? array_map(static fn ($n) => $n->getAttribute('href') . '|' . trim($n->textContent), iterator_to_array($home->query('.//a[contains(@class,"btn")]', $actSlide))) : [];
check($r['code'] === 303 && ($slideTitles[1] ?? '') === $fa['title'] && count($slideTitles) <= 3 && $home->query('//*[@data-slide]//h1')->length === 1,
    'faaliyet slaytı: 2. sırada (ilk sabit slayttan sonra), h1 ilk slaytta kaldı');
check(in_array($fa['url'] . '|Faaliyet Ayrıntıları', $links, true) && (!$fa['can_apply'] || in_array($fa['url'] . '#basvuru|Faaliyete Başvur', $links, true))
    && $home->query('.//*[contains(@class,"activity-status")]', $actSlide)->length === 1, 'faaliyet slaytı: başvuru ve ayrıntı butonları, durum rozeti');
check(str_contains(http('GET', "$BASE/yonetim/slaytlar?sekme=faaliyet", ['jar' => 'admin'])['body'], 'faaliyet-' . $act['id']), 'faaliyet slaytı: panel sekmesinde listeleniyor');
$db->run('UPDATE activities SET starts_on = ?, ends_on = NULL WHERE id = ?', [date('Y-m-d', strtotime('-2 days')), $act['id']]);
check(!in_array($fa['title'], array_column(\App\Models\Banner::visible(), 'title'), true), 'faaliyet slaytı: tarihi geçince kendiliğinden kalkar');
$db->run('UPDATE activities SET starts_on = ?, ends_on = ? WHERE id = ?', [$act['starts_on'], $act['ends_on'], $act['id']]);
http('POST', "$BASE/yonetim/slaytlar/faaliyet", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'activity_id' => $act['id'], 'do' => 'remove']]);
check((int) $db->value('SELECT in_slider FROM activities WHERE id = ?', [$act['id']]) === 0 && !in_array($fa['title'], array_column(\App\Models\Banner::visible(), 'title'), true), 'faaliyet slaytı: kaldırıldı');

// Duyuru & Haber slaytı ve /duyurular listesi
$keepActs = array_column($db->all('SELECT id FROM activities WHERE in_slider = 1'), 'id'); // yerel veriyi bozmadan 3. sıraya yer aç
$db->run('UPDATE activities SET in_slider = 0');
$keepNews = array_column($db->all('SELECT id FROM news WHERE in_slider = 1'), 'id');
$db->run('UPDATE news SET in_slider = 0');
$t = $token('admin', '/yonetim/duyurular/yeni');
$r = http('POST', "$BASE/yonetim/duyurular", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => 'Test Slayt Haberi', 'body' => 'metin', 'status' => 'published', 'in_slider' => '1']]);
check(str_contains(http('GET', $r['location'], ['jar' => 'admin'])['body'], 'kapak görseli yükle'), 'haber slaytı: kapaksız haber slayta eklenemez');
http('POST', "$BASE/yonetim/duyurular", ['jar' => 'admin', 'multipart' => ['_csrf' => $t, 'title' => 'Test Slayt Haberi', 'category' => 'haber', 'status' => 'published', 'excerpt' => 'Slayt özeti',
    'body' => 'Haber metni', 'in_slider' => '1', 'cover' => new CURLFile($jpg, 'image/jpeg', 'kapak.jpg'), 'cover_crop' => '0,0,1,0.5']]);
$sn = $db->one("SELECT * FROM news WHERE title = 'Test Slayt Haberi'");
check(\App\Core\Image::size($sn['cover_image']) === [1600, 489], 'kırpma: haber kapağı seçilen alana kırpıldı');
$snUrl = \App\Models\News::findBySlug($sn['slug'])['url'];
$home = dom(http('GET', "$BASE/")['body']);
$slide = $home->query('//*[@data-slide][.//*[contains(@class,"hero-title")][normalize-space()="Test Slayt Haberi"]]')->item(0);
$links = $slide ? array_map(static fn ($n) => $n->getAttribute('href') . '|' . trim($n->textContent), iterator_to_array($home->query('.//a[contains(@class,"btn")]', $slide))) : [];
check((int) $sn['in_slider'] === 1 && in_array($snUrl . '|Devamını Oku', $links, true) && in_array(url('duyurular') . '|Tüm Duyuru & Haberler', $links, true)
    && $home->query('//*[@data-slide]//h1')->length === 1, 'haber slaytı: Anasayfada, "Devamını Oku" ve "Tüm Duyuru & Haberler" butonlarıyla');
check(str_contains(http('GET', "$BASE/yonetim/slaytlar?sekme=haber", ['jar' => 'admin'])['body'], 'haber-' . $sn['id']), 'haber slaytı: panel sekmesinde listeleniyor');
$db->run('UPDATE news SET slider_until = ? WHERE id = ?', [date('Y-m-d', strtotime('-1 day')), $sn['id']]);
check(!in_array('Test Slayt Haberi', array_column(\App\Models\Banner::visible(), 'title'), true) && http('GET', "$BASE/duyurular/{$sn['slug']}")['code'] === 200, 'haber slaytı: bitiş tarihi geçince slayttan kalkar, haber sitede kalır');
$tv = $token('viewer', '/yonetim');
check(http('POST', "$BASE/yonetim/slaytlar/haber", ['jar' => 'viewer', 'post' => ['_csrf' => $tv, 'news_id' => $sn['id'], 'do' => 'add']])['code'] === 403, 'haber slaytı: izleme rolü ekleyemez');
http('POST', "$BASE/yonetim/slaytlar/haber", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'news_id' => $sn['id'], 'do' => 'add']]);
check(in_array('Test Slayt Haberi', array_column(\App\Models\Banner::visible(), 'title'), true), 'haber slaytı: panelden yeniden eklenince eski bitiş tarihi temizlenir');
http('POST', "$BASE/yonetim/slaytlar/haber", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'news_id' => $sn['id'], 'do' => 'remove']]);
check((int) $db->value('SELECT in_slider FROM news WHERE id = ?', [$sn['id']]) === 0, 'haber slaytı: kaldırıldı');

$list = http('GET', "$BASE/duyurular");
$ld = dom($list['body']);
check($list['code'] === 200 && $ld->query('//a[contains(@class,"news-card")][contains(@href,"/duyurular/' . $sn['slug'] . '")]')->length === 1
    && $ld->query('//a[@aria-current="page"][contains(@href,"/duyurular")]')->length >= 1, 'duyuru listesi: haber kartı var, menüde "Duyuru & Haber" etkin');
$rapor = dom(http('GET', "$BASE/duyurular?tur=rapor")['body']);
check($rapor->query('//a[contains(@class,"news-card")][contains(@href,"' . $sn['slug'] . '")]')->length === 0
    && $rapor->query('//link[@rel="canonical"][@href="' . url('duyurular') . '"]')->length === 1, 'duyuru listesi: türe göre süzülür, canonical yalın adres');
check(str_contains(http('GET', "$BASE/")['body'], 'Tüm Duyuru &amp; Haberler'), 'Anasayfa: "Tüm Duyuru & Haberler" bağlantısı');
http('POST', "$BASE/yonetim/duyurular/{$sn['id']}/sil", ['jar' => 'admin', 'post' => ['_csrf' => $t]]);
foreach ($keepActs as $id) {
    $db->run('UPDATE activities SET in_slider = 1 WHERE id = ?', [$id]);
}
foreach ($keepNews as $id) {
    $db->run('UPDATE news SET in_slider = 1 WHERE id = ?', [$id]);
}


// Ana test takımına temizlikten önce dahil edilir; yalnızca V2 test kayıtları üzerinde çalışır.
check(Activity::status(['starts_on' => '2026-01-01', 'completed_on' => '2026-02-01'] + $base, $today) === 'done', 'V2: gerçekleşmesi doğrulanan geçmiş faaliyet tamamlandı');
$copyPage = http('GET', "$BASE/yonetim/faaliyetler/yeni?kopya=" . $act['id'], ['jar' => 'admin'])['body'];
check(str_contains($copyPage, 'Test Zirvesi') && !str_contains($copyPage, 'value="' . $act['starts_on'] . '"'), 'V2: benzerinden form açılır; eski tarih kopyalanmaz');
check(str_contains($copyPage, 'data-steps="tabs"') && str_contains($copyPage, 'data-dirty-guard') && !str_contains($copyPage, 'data-step-next'), 'V2: yeni kayıt zorunlu sihirbaz olmadan kaydedilebilir');
check(!preg_match('/name="is_published"[^>]*checked/', $copyPage), 'V2: kopyadan oluşturulan faaliyet taslak başlar');
$dashboard = http('GET', "$BASE/yonetim", ['jar' => 'admin']);
check($dashboard['code'] === 200 && str_contains($dashboard['body'], 'Sonucunu tamamla') && str_contains($dashboard['body'], 'Son başvurular'), 'V2: ana ekran iş listesi ve gerçek son başvurular açılır');
$slides = http('GET', "$BASE/yonetim/slaytlar", ['jar' => 'admin']);
check($slides['code'] === 200 && substr_count($slides['body'], 'class="live-slide"') <= 3 && str_contains($slides['body'], 'Şu anda sitede görünenler'), 'V2: gerçek yayın sırasındaki slayt önizlemesi');
$t2 = $token('admin', '/yonetim/faaliyetler');
$row = $db->one('SELECT * FROM activities WHERE id = ?', [$act['id']]);
$payload = ['_csrf' => $t2, 'title' => $row['title'], 'category' => $row['category'], 'location' => $row['location'], 'summary' => $row['summary'], 'starts_on' => date('Y-m-d', strtotime('+20 days')), 'difficulty_score' => 5, 'quota' => 0, 'is_published' => 1, 'manual_status' => '', 'slug' => 'v2-adres-test', 'is_completed' => 1];
$res = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $payload]);
check(!$db->value('SELECT completed_on FROM activities WHERE id = ?', [$act['id']]), 'V2: gelecekteki faaliyet tamamlandı olarak kaydedilemez');
http('GET', $res['location'], ['jar' => 'admin']); // Hata sonrası form değerlerini tüket.
unset($payload['is_completed']);
$res = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $payload]);
$oldUrl = "$BASE/faaliyetler/{$row['slug']}";
$redir = http('GET', $oldUrl);
check($res['code'] === 303 && $redir['code'] === 301 && str_ends_with($redir['location'], '/v2-adres-test'), 'V2: eski faaliyet adresi yeni adrese 301 yönlenir');
$payload['slug'] = 'v2-adres-test-ikinci';
http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $payload]);
check(str_ends_with(http('GET', $oldUrl)['location'], '/v2-adres-test-ikinci'), 'V2: ikinci adres değişiminde ilk adres doğrudan son adrese yönlenir');
$payload['starts_on'] = date('Y-m-d', strtotime('-20 days'));
$payload['is_completed'] = 1;
$payload['report_summary'] = 'V2 doğrulama raporu';
http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $payload]);
check((bool) $db->value('SELECT completed_on FROM activities WHERE id = ?', [$act['id']]), 'V2: geçmiş faaliyet gerçekleşti olarak kaydedilir');
$draft = http('GET', "$BASE/yonetim/duyurular/yeni?rapor={$act['id']}", ['jar' => 'admin']);
check($draft['code'] === 200 && str_contains($draft['body'], 'V2 doğrulama raporu') && str_contains($draft['body'], 'faaliyet raporu'), 'V2: rapordan bağlantılı duyuru taslağı hazırlanır');
$newsData = ['_csrf' => $t2, 'title' => 'V2 Yönlendirme testi', 'content_mode' => 'activity', 'activity_id' => $act['id'], 'body' => 'Kip değişiminden kalan metin', 'status' => 'draft'];
$new = http('POST', "$BASE/yonetim/duyurular", ['jar' => 'admin', 'post' => $newsData]);
$testNews = $db->one("SELECT * FROM news WHERE title = 'V2 Yönlendirme testi' ORDER BY id DESC LIMIT 1");
check($new['code'] === 303 && $testNews && $testNews['body'] === null && (int) $testNews['activity_id'] === (int) $act['id'], 'V2: faaliyet yönlendirmesi gizli metin tarafından ezilmez');
if ($testNews) $db->run('DELETE FROM news WHERE id = ?', [$testNews['id']]);
$db->run("DELETE FROM content_redirects WHERE kind = 'activity' AND content_id = ?", [$act['id']]);


$orderBefore = \App\Core\Settings::get('slider_order', '[]');
$lineBefore = \App\Models\Banner::orderedCandidates();
if (count($lineBefore) > 2) {
    $last = $lineBefore[count($lineBefore) - 1];
    $key = $last['kind'] . ':' . $last['source_id'];
    $move = http('POST', "$BASE/yonetim/slaytlar/sira", ['jar' => 'admin', 'post' => ['_csrf' => $t2, 'do' => 'up', 'key' => $key]]);
    $saved = json_decode((string) $db->value("SELECT value FROM settings WHERE `key` = 'slider_order'"), true);
    check($move['code'] === 303 && $saved[count($saved) - 2] === $key, 'V2: farklı kaynaklardaki slaytlar tek sırada taşınır');
    check($saved[0] === $lineBefore[0]['kind'] . ':' . $lineBefore[0]['source_id'], 'V2: ilk kulüp tanıtımı sıralamada korunur');
    $reset = http('POST', "$BASE/yonetim/slaytlar/sira", ['jar' => 'admin', 'post' => ['_csrf' => $t2, 'do' => 'reset']]);
    check($reset['code'] === 303 && $db->value("SELECT value FROM settings WHERE `key` = 'slider_order'") === '[]', 'V2: otomatik slayt sıralamasına dönülür');
}
\App\Core\Settings::set('slider_order', $orderBefore);
check(http('POST', "$BASE/yonetim/slaytlar/sira", ['jar' => 'viewer', 'post' => ['_csrf' => $token('viewer', '/yonetim'), 'do' => 'reset']])['code'] === 403, 'V2: izleme rolü ortak yayın sırasını değiştiremez');
// Anasayfa görselleri: toplu yayın/pasif, listeye aynı filtreyle dönüş, tablo + kart aynı veriden
$bulkKey = \App\Models\Banner::orderedKeys()[0] ?? null;
if ($bulkKey !== null) {
    $disabledBefore = \App\Core\Settings::get('slider_disabled', '[]');
    $activeBefore = $db->all('SELECT id, is_active, show_from, show_until FROM banners');
    $bulk = http('POST', "$BASE/yonetim/slaytlar/toplu", ['jar' => 'admin', 'post' => ['_csrf' => $t2, 'do' => 'deactivate', 'keys' => [$bulkKey, 'bilinmeyen:1'], 'return' => '/yonetim/slaytlar?tur=tanitim&durum=active&gorunum=kart&x=1']]);
    $off = !in_array($bulkKey, array_map(static fn ($s) => $s['kind'] . ':' . $s['source_id'], \App\Models\Banner::orderedCandidates()), true);
    check($bulk['code'] === 303 && $off && str_ends_with($bulk['location'], '/yonetim/slaytlar?tur=tanitim'), 'slaytlar: toplu pasife alma, bilinmeyen anahtar ve parametre yok sayılır');
    $bulk = http('POST', "$BASE/yonetim/slaytlar/toplu", ['jar' => 'admin', 'post' => ['_csrf' => $t2, 'do' => 'activate', 'keys' => [$bulkKey], 'return' => '//kotu-site.example/yonetim/slaytlar']]);
    check($bulk['code'] === 303 && str_ends_with($bulk['location'], '/yonetim/slaytlar'), 'slaytlar: toplu yayına alma, dış adrese dönüş reddedilir');
    check(http('POST', "$BASE/yonetim/slaytlar/toplu", ['jar' => 'viewer', 'post' => ['_csrf' => $token('viewer', '/yonetim'), 'do' => 'activate', 'keys' => [$bulkKey]]])['code'] === 403, 'slaytlar: izleme rolü toplu işlem yapamaz');
    \App\Core\Settings::set('slider_disabled', $disabledBefore);
    foreach ($activeBefore as $b) $db->run('UPDATE banners SET is_active = ?, show_from = ?, show_until = ? WHERE id = ?', [$b['is_active'], $b['show_from'], $b['show_until'], $b['id']]);
}
$list = http('GET', "$BASE/yonetim/slaytlar?gorunum=kart&sirala=baslik", ['jar' => 'admin'])['body'];
$tableRows = preg_match_all('/<tr role="row" id="[^"]+" class="bw-row/', $list);
$cardRows = preg_match_all('/<li class="bw-tile/', $list);
check($tableRows > 0 && $tableRows === $cardRows && str_contains($list, 'data-list="kart" aria-label') && str_contains($list, 'aria-sort="ascending"'), 'slaytlar: tablo ve kart aynı kayıtlar, görünüm ve sıralama sunucuda da uygulanır');
// Kart/Tablo: tüm panel listelerinde seçici var; adresle seçilen görünüm çereze yazılır ve hatırlanır
$missing = [];
foreach (['/yonetim/faaliyetler', '/yonetim/galeri', '/yonetim/duyurular', '/yonetim/mesajlar', '/yonetim/kullanicilar', '/yonetim/slaytlar', "/yonetim/faaliyetler/{$act['id']}/basvurular"] as $path) {
    $page = http('GET', $BASE . $path, ['jar' => 'admin']);
    if ($page['code'] !== 200 || !str_contains($page['body'], 'aria-label="Görünüm"')) $missing[] = $path . ' (' . $page['code'] . ')';
}
check(!$missing, 'liste görünümü: Kart/Tablo seçici tüm listelerde' . ($missing ? ' (eksik: ' . implode(', ', $missing) . ')' : ''));
http('GET', "$BASE/yonetim/duyurular?gorunum=kart", ['jar' => 'admin']);
$remembered = http('GET', "$BASE/yonetim/duyurular", ['jar' => 'admin'])['body'];
check(str_contains($remembered, 'data-view-key="duyurular" data-list-view="kart"'), 'liste görünümü: seçilen görünüm sonraki açılışta hatırlanır');
http('GET', "$BASE/yonetim/duyurular?gorunum=tablo", ['jar' => 'admin']);
$viewerList = http('GET', "$BASE/yonetim/slaytlar", ['jar' => 'viewer'])['body'];
check(!str_contains($viewerList, 'data-select') && !str_contains($viewerList, 'data-drag-handle') && !str_contains($viewerList, 'admin.banners.toggle') && !str_contains($viewerList, '/yonetim/slaytlar/durum'), 'slaytlar: izleme rolü seçim, sıralama ve yayın işlemlerini görmez');
$payload['gear_extra'] = "V2 özel malzeme kontrolü\nYedek pil";
http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $payload]);
check($db->value('SELECT gear_extra FROM activities WHERE id = ?', [$act['id']]) === $payload['gear_extra'] && str_contains(http('GET', "$BASE/faaliyetler/{$payload['slug']}")['body'], 'V2 özel malzeme kontrolü'), 'V2: faaliyete özel ek malzeme notu kaydedilir ve sitede görünür');

// Temizlik
foreach ($keepNotes as $k => $v) {
    \App\Core\Settings::set($k, (string) $v);
}
$db->run("DELETE FROM applications WHERE email = 'panel-test@example.invalid'");
$db->run('DELETE FROM activities WHERE id = ?', [$act['id']]);
\App\Core\Image::delete($act['cover_image']);
$db->run("DELETE FROM audit_log WHERE user_id IN (SELECT id FROM users WHERE email LIKE 'test-%@example.invalid')");
$db->run("DELETE FROM users WHERE email LIKE '%@example.invalid'");
$db->run("DELETE FROM login_attempts WHERE email LIKE '%@example.invalid'");
@unlink($jpg);

/* ------------------------------------------------------------------ */
echo "\n5) Mobil uyum (statik kontroller, CLAUDE.md §6a)\n";
$css = '';
foreach (glob(App::config('public_path') . '/assets/*.css') as $f) {
    $css .= file_get_contents($f);
}
check((bool) preg_match('/input[^{]*\{[^}]*font-size:\s*(1[6-9]|[2-9]\d)px/s', $css) || str_contains($css, '--input-font: 16px'), 'form alanlarında font-size ≥ 16px kuralı var');
check(!preg_match('/font-size:\s*(\d|1[01])(\.\d+)?px/', $css), 'CSS’te 12px altı yazı yok');

/* ------------------------------------------------------------------ */
echo "\n7) KVKK saklama süresi (kdrk/cron/kvkk-purge.php, CLAUDE.md §15)\n";
$days = max(30, \App\Core\Settings::int('kvkk_retention_days', 365));
$mk = static fn (string $slug, string $date) => $db->insert('activities', ['slug' => $slug, 'title' => 'KVKK Test', 'category' => 'gunubirlik', 'starts_on' => $date]);
$oldAct = $mk('kvkk-test-eski', date('Y-m-d', strtotime('-' . ($days + 5) . ' days')));
$newAct = $mk('kvkk-test-yeni', date('Y-m-d', strtotime('-' . ($days - 5) . ' days')));
$app = static fn (int $act) => $db->insert('applications', ['activity_id' => $act, 'name' => 'KVKK Test', 'phone' => '5320000000', 'email' => 'kvkk-test@example.invalid',
    'tc_enc' => Crypto::encrypt('10000000146'), 'health_enc' => Crypto::encrypt('Astım'), 'kvkk_consent_at' => date('Y-m-d H:i:s')]);
$oldApp = $app($oldAct);
$newApp = $app($newAct);
$cmd = escapeshellarg(PHP_BINARY) . ' -d extension=sodium ' . escapeshellarg(dirname(__DIR__) . '/kdrk_v2/cron/kvkk-purge.php');
exec("$cmd --dry-run", $out1, $rc1);
check($rc1 === 0 && $db->value('SELECT tc_enc IS NOT NULL FROM applications WHERE id = ?', [$oldApp]) == 1, 'deneme modunda hiçbir şey silinmez');
exec($cmd, $out2, $rc2);
$o = $db->one('SELECT tc_enc, health_enc, purged_at, name, phone FROM applications WHERE id = ?', [$oldApp]);
check($rc2 === 0 && $o['tc_enc'] === null && $o['health_enc'] === null && $o['purged_at'] !== null, 'süresi dolan başvuruda TC ve sağlık notu silindi');
check($o['name'] === 'KVKK Test' && $o['phone'] === '5320000000', 'ad ve katılım kaydı kaldı');
$n = $db->one('SELECT tc_enc, purged_at FROM applications WHERE id = ?', [$newApp]);
check($n['tc_enc'] !== null && $n['purged_at'] === null && Crypto::decrypt($n['tc_enc']) === '10000000146', 'süresi dolmayan başvuruya dokunulmadı');
$db->run("DELETE FROM applications WHERE email = 'kvkk-test@example.invalid'");
$db->run("DELETE FROM activities WHERE slug IN ('kvkk-test-eski', 'kvkk-test-yeni')");
$db->run("DELETE FROM audit_log WHERE action = 'purge' AND user_id IS NULL AND created_at > NOW() - INTERVAL 5 MINUTE");

/* ------------------------------------------------------------------ */
echo "\n" . str_repeat('─', 50) . "\n";
printf("%d geçti, %d kaldı\n", $pass, count($fail));
foreach ($fail as $f) {
    echo "  ✗ $f\n";
}
exit($fail ? 1 : 0);
