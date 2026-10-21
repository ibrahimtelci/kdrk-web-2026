<?php
declare(strict_types=1);

namespace App\Controllers\Site;

use App\Core\Csrf;
use App\Core\Session;
use App\Core\View;
use App\Models\Activity;
use App\Models\Application;
use App\Models\Gallery;

final class ActivityController
{
    public function index(): void
    {
        echo View::render('pages/activities/index', [
            'activities' => Activity::all(),
            'page'       => [
                'title'       => 'Faaliyetler',
                'description' => "KDRK'nın yaklaşan ve geçmiş faaliyetleri: günübirlik yürüyüşler, dağcılık, eğitim ve daha fazlası.",
                'css'         => ['home.css', 'list.css'],
                'navLight'    => true,
                'nav'         => 'faaliyetler',
                'current'     => 'faaliyetler',
            ],
        ]);
    }

    public function show(string $slug): void
    {
        $a = Activity::findBySlug($slug);
        if (!$a) {
            $target = \App\Core\App::db()->value("SELECT a.slug FROM content_redirects r JOIN activities a ON a.id = r.content_id WHERE r.kind = 'activity' AND r.slug = ? AND a.is_published = 1", [$slug]);
            if ($target && $target !== $slug) {
                redirect(url('faaliyet', ['slug' => $target]), 301);
            }
            abort(404);
        }
        $this->render($a, Session::pull('apply_old', []), Session::pull('apply_errors', []), (bool) Session::pull('apply_ok', false));
    }

    public function apply(string $slug): void
    {
        $json = str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
        $a = Activity::findBySlug($slug) ?? abort(404);
        Csrf::verify();

        $back = $a['url'] . '#basvuru';
        $fail = function (int $status, array $errors, string $message, array $old = []) use ($json, $back): never {
            if ($json) {
                http_response_code($status);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'errors' => $errors ?: null, 'message' => $message], JSON_UNESCAPED_UNICODE);
                exit;
            }
            Session::flash('apply_old', $old);
            Session::flash('apply_errors', $errors ?: ['name' => $message]);
            redirect($back, 303);
        };

        // Bot tuzağı: görünmeyen alan doldurulmuşsa sessizce başarılı say
        if (!empty($_POST['website'])) {
            $this->done($json, $back, 'pending');
        }
        if (!$a['can_apply'] && $a['status'] !== 'full') {
            $fail(409, [], 'Bu faaliyete şu an başvuru alınmıyor.');
        }
        // Basit hız sınırı: aynı oturumdan 10 dakikada en fazla 5 başvuru
        $recent = array_filter((array) Session::get('apply_times', []), static fn ($t) => $t > time() - 600);
        if (count($recent) >= 5) {
            $fail(429, [], 'Kısa sürede çok fazla başvuru gönderildi. Birkaç dakika sonra tekrar dene.');
        }

        [$data, $errors] = Application::validate($_POST);
        $old = array_diff_key($data, ['tc' => 1, 'health' => 1]); // hassas alanlar oturuma yazılmaz
        if ($errors) {
            $fail(422, $errors, 'Formda düzeltilmesi gereken alanlar var.', $old);
        }

        $result = Application::create((int) $a['id'], $data);
        if (isset($result['error'])) {
            $fail(422, ['email' => $result['error']], $result['error'], $old);
        }
        $recent[] = time();
        Session::set('apply_times', array_values($recent));
        // E-posta bildirimi: Mailer aşamasında eklenecek (applications.mail_status = 'pending' bekliyor)
        $this->done($json, $back, $result['status']);
    }

    private function done(bool $json, string $back, string $status): never
    {
        $message = $status === 'waitlist' ? 'Yedek listeye alındın.' : 'Başvurun alındı.';
        if ($json) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => true, 'status' => $status, 'message' => $message], JSON_UNESCAPED_UNICODE);
            exit;
        }
        Session::flash('apply_ok', true);
        redirect($back, 303);
    }

    private function render(array $a, array $old, array $errors, bool $applied): void
    {
        $album = Gallery::forActivity((int) $a['id']);
        echo View::render('pages/activities/show', [
            'a'       => $a,
            'album'   => $album,
            'photos'  => $album ? Gallery::photos((int) $album['id'], 6) : [],
            'old'     => $old,
            'errors'  => $errors,
            'applied' => $applied,
            'page'    => [
                'title'         => $a['title'] . ' · ' . tr_date_long($a['starts_on']),
                'description'   => $a['summary'],
                'css'           => $a['has_map'] ? ['detail.css', 'vendor/leaflet/leaflet.css'] : ['detail.css'],
                'nav'           => 'faaliyetler',
                'current'       => 'faaliyet',
                'currentParams' => ['slug' => $a['slug']],
            ],
        ]);
    }
}
