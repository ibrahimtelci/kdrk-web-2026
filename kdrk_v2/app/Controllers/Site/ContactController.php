<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\App;
use App\Core\Csrf;
use App\Core\Session;

/** İletişim formu gönderimi. Sayfanın kendisi PageController ile gösterilir. */
final class ContactController
{
    public const TOPICS = [
        'genel'     => 'Genel Bilgi',
        'uyelik'    => 'Üyelik',
        'faaliyet'  => 'Faaliyet Katılımı',
        'egitim'    => 'Eğitim Programları',
        'isbirligi' => 'İş Birliği / Sponsorluk',
        'basin'     => 'Basın',
    ];

    public function send(): void
    {
        Csrf::verify();
        $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $back = url('iletisim') . '#iletisim-formu';

        $str = static fn (string $k, int $max): string => mb_substr(trim((string) ($_POST[$k] ?? '')), 0, $max);
        $d = [
            'name'    => $str('name', 120),
            'phone'   => preg_replace('/^0/', '', preg_replace('/\D/', '', (string) ($_POST['phone'] ?? ''))),
            'email'   => mb_strtolower($str('email', 190)),
            'topic'   => array_key_exists($_POST['topic'] ?? '', self::TOPICS) ? $_POST['topic'] : 'genel',
            'message' => $str('message', 5000),
        ];

        if (empty($_POST['website'])) { // bot tuzağı doluysa sessizce başarılı say
            $e = [];
            if (mb_strlen($d['name']) < 3) {
                $e['name'] = 'Ad ve soyadını yaz.';
            }
            if ($d['phone'] !== '' && !preg_match('/^5\d{9}$/', $d['phone'])) {
                $e['phone'] = '5 ile başlayan 10 haneli numara yaz ya da boş bırak.';
            }
            if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
                $e['email'] = 'Geçerli bir e-posta adresi yaz (ör. ad@ornek.com).';
            }
            if (mb_strlen($d['message']) < 10) {
                $e['message'] = 'Mesajın çok kısa; en az 10 karakter yaz.';
            }
            $recent = array_filter((array) Session::get('contact_times', []), static fn ($t) => $t > time() - 600);
            if (!$e && count($recent) >= 3) {
                $e['message'] = 'Kısa sürede çok fazla mesaj gönderildi. Birkaç dakika sonra tekrar dene.';
            }
            if ($e) {
                if ($json) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['ok' => false, 'errors' => $e], JSON_UNESCAPED_UNICODE);
                    return;
                }
                Session::flash('form_old', $d);
                Session::flash('form_errors', $e);
                redirect($back, 303);
            }
            App::db()->insert('messages', [
                'name'    => $d['name'],
                'email'   => $d['email'],
                'phone'   => $d['phone'] ?: null,
                'topic'   => $d['topic'],
                'subject' => self::TOPICS[$d['topic']],
                'body'    => $d['message'],
            ]);
            $recent[] = time();
            Session::set('contact_times', array_values($recent));
            // E-posta bildirimi: Mailer aşamasında eklenecek (messages.mail_status = 'pending')
        }

        if ($json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
            return;
        }
        Session::flash('form_ok', true);
        redirect($back, 303);
    }
}
