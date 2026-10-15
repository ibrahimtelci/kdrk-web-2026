<?php
declare(strict_types=1);

/*
 * KDRK yerel test takımı.
 *   1) tools/dev.bat ile sunucuyu başlat   2) php -d extension=sodium tools/test.php [http://localhost:8000]
 * Yerel veritabanına test başvuruları yazar ve sonunda siler. Canlıda ÇALIŞTIRILMAZ.
 */

require __DIR__ . '/../kdrk/bootstrap.php';

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
check(Activity::status(['starts_on' => '2026-10-01'] + $base, $today) === 'done', 'tarihi geçince: done');
check(Activity::status(['starts_on' => '2026-10-14', 'ends_on' => '2026-10-16'] + $base, $today) === 'open', 'devam eden çok günlü faaliyet: done değil');
check(Activity::status(['manual_status' => 'closed'] + $base, $today) === 'closed', 'elle kapatılan: closed');
check(Activity::status(['manual_status' => 'closed', 'starts_on' => '2026-01-01'] + $base, $today) === 'done', 'kapalı + tarihi geçmiş: done');
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
    foreach ($x->query('//a[contains(@class,"activity-card")]') as $card) {
        if ($card->getAttribute('data-date') < date('Y-m-d') && in_array($card->getAttribute('data-status'), ['open', 'few', 'full'], true)) {
            $issues[] = 'tarihi geçmiş açık kart: ' . $card->getAttribute('href');
        }
    }
    $issues = array_unique($issues);
    check($issues === [], $path . ($issues ? '  → ' . implode(' | ', array_slice($issues, 0, 6)) : ''));
}

/* ------------------------------------------------------------------ */
echo "\n3) Anasayfa içeriği\n";
$home = dom(http('GET', "$BASE/")['body']);
$slides = $home->query('//*[@data-slide]')->length;
check($slides === 2, "süresi geçen slayt gizli (görünen slayt: $slides)");
check($home->query('//*[@data-slide][1]//h1')->length === 1 && $home->query('//*[@data-slide]//h1')->length === 1, 'yalnız ilk slaytta h1');
check(!str_contains($home->document->saveHTML(), '{yil}') && !str_contains($home->document->saveHTML(), '{zirve}'), 'rakam belirteçleri dolduruldu');
$homeCards = $home->query('//section[@id="takvim"]//a[contains(@class,"activity-card")]');
$future = true;
foreach ($homeCards as $c) {
    $future = $future && $c->getAttribute('data-status') !== 'done';
}
check($homeCards->length === 3 && $future, 'yaklaşan bölümünde 3 gelecek tarihli faaliyet');
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
foreach (['admin', 'editor', 'viewer'] as $role) {
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
check($login('editor')['code'] === 303 && $login('viewer')['code'] === 303, 'editör ve izleme girişi');

// Faaliyet ekleme: kapak görseli + program + malzeme
$jpg = tempnam(sys_get_temp_dir(), 'kdrkimg') . '.jpg';
$im = imagecreatetruecolor(1800, 1100);
imagefill($im, 0, 0, imagecolorallocate($im, 40, 90, 140));
imagejpeg($im, $jpg, 85);
$t = $token('admin', '/yonetim/faaliyetler/yeni');
$r = http('POST', "$BASE/yonetim/faaliyetler", ['jar' => 'admin', 'multipart' => [
    '_csrf' => $t, 'title' => 'Test Zirvesi Tırmanışı', 'category' => 'dagcilik', 'location' => 'Rize / Test',
    'summary' => 'Panel testinden eklenen faaliyet.', 'body' => "Birinci paragraf.\n\nİkinci <b>paragraf</b>.",
    'starts_on' => date('Y-m-d', strtotime('+40 days')), 'ends_on' => date('Y-m-d', strtotime('+41 days')),
    'difficulty' => '3', 'quota' => '2', 'fee' => '1250', 'is_published' => '1',
    'program' => json_encode([['title' => 'Yaklaşma', 'date' => null, 'items' => [['time' => '07.30', 'text' => 'Toplanma'], ['time' => 'x', 'text' => '']]]]),
    'equipment' => "Kask\n\nKrampon\n", 'cover' => new CURLFile($jpg, 'image/jpeg', 'kapak.jpg'),
]]);
$act = $db->one("SELECT * FROM activities WHERE title = 'Test Zirvesi Tırmanışı'");
check($r['code'] === 303 && $act !== null && $act['slug'] === 'test-zirvesi-tirmanisi-' . substr($act['starts_on'], 0, 4), 'faaliyet eklendi, adres üretildi');
$prog = json_decode((string) $act['program'], true);
check(($prog[0]['items'] ?? null) === [['time' => '07:30', 'text' => 'Toplanma']] && json_decode((string) $act['equipment'], true) === ['Kask', 'Krampon'], 'program ve malzeme temizlenerek kaydedildi');
$coverOk = $act['cover_image'] && is_file(App::config('public_path') . "/{$act['cover_image']}-900.webp") && is_file(App::config('public_path') . "/{$act['cover_image']}-1600.webp") && !is_file(App::config('public_path') . "/{$act['cover_image']}-2400.webp");
check($coverOk, 'kapak WebP türevleri üretildi (özgünden büyük türev yok)');
$site = http('GET', "$BASE/faaliyetler/{$act['slug']}")['body'];
check(str_contains($site, 'Toplanma') && str_contains($site, 'Krampon') && str_contains($site, '-1600.webp') && str_contains($site, '₺1.250'), 'yeni faaliyet sitede: program, malzeme, kapak, ücret');
check(str_contains($site, '&lt;b&gt;paragraf&lt;/b&gt;') && !str_contains($site, '<b>paragraf</b>'), 'açıklamadaki HTML kaçışlı (XSS yok)');

$r = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => ['_csrf' => $t, 'title' => '', 'category' => 'dagcilik', 'location' => 'x', 'summary' => 'kısa',
    'starts_on' => '2027-05-10', 'ends_on' => '2027-05-01']]);
$form = http('GET', $r['location'], ['jar' => 'admin'])['body'];
check(str_contains($form, 'Faaliyet adını yaz') && str_contains($form, 'Bitiş tarihi başlangıçtan önce olamaz') && str_contains($form, 'value="x"'), 'hatalı form: alan hataları + girilen değer korunur');
check(http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => ['title' => 'x']])['code'] === 419, 'CSRF olmadan panel POST → 419');

// Başvuru → onay → listeler
$pub = http('GET', "$BASE/faaliyetler/{$act['slug']}", ['jar' => 'site']);
preg_match('/name="_csrf" value="([^"]+)"/', $pub['body'], $m);
http('POST', "$BASE/faaliyetler/{$act['slug']}/basvuru", ['jar' => 'site', 'headers' => $json, 'post' => ['_csrf' => $m[1], 'name' => 'Panel Testi', 'tc' => '10000000146',
    'phone' => '5321112233', 'email' => 'panel-test@example.invalid', 'emer_name' => 'Acil Kişi', 'emer_phone' => '5332223344', 'health' => 'Gizli sağlık notu', 'kvkk' => '1']]);
$app = $db->one("SELECT id, status FROM applications WHERE email = 'panel-test@example.invalid'");
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
    'ends_on' => $act['ends_on'], 'difficulty' => '3', 'quota' => '2', 'slug' => $act['slug'], 'is_published' => '1', 'manual_status' => 'closed'];
http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}", ['jar' => 'admin', 'post' => $fields]);
check(str_contains(http('GET', "$BASE/faaliyetler/{$act['slug']}")['body'], 'başvuruları kapandı'), 'elle kapatılan faaliyet sitede "kapandı"');
$r = http('POST', "$BASE/yonetim/faaliyetler/{$act['id']}/sil", ['jar' => 'admin', 'post' => ['_csrf' => $t]]);
check($db->value('SELECT COUNT(*) FROM activities WHERE id = ?', [$act['id']]) == 1, 'başvurusu olan faaliyet silinemez');

// Ayarlar siteye yansır; bakım modu
$t = $token('admin', '/yonetim/ayarlar');
$settings = ['_csrf' => $t, 'stat_kurulus' => '1998', 'stat_zirve' => '512', 'stat_yuruyus' => '1000', 'stat_arama_kurtarma' => '25',
    'contact_email' => 'info@kdrk.org.tr', 'contact_phone' => '+90 (464) 651 00 00', 'contact_address' => setting('contact_address'),
    'social_instagram' => 'https://instagram.com/kdrk_test', 'social_whatsapp' => '', 'social_youtube' => '', 'social_strava' => '',
    'maintenance' => '1', 'maintenance_title' => 'Test bakımı', 'maintenance_message' => 'Kısa süre.', 'maintenance_eta' => '', 'kvkk_retention_days' => '365'];
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

// Temizlik
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
echo "\n" . str_repeat('─', 50) . "\n";
printf("%d geçti, %d kaldı\n", $pass, count($fail));
foreach ($fail as $f) {
    echo "  ✗ $f\n";
}
exit($fail ? 1 : 0);
