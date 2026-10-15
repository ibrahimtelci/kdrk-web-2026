<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Audit;
use App\Core\Settings;

/** Genel ayarlar — yalnızca admin. Kulüp rakamları, iletişim, sosyal hesaplar, bakım modu, KVKK. */
final class SettingsController extends BaseController
{
    protected array $roles = ['admin'];

    public const FIELDS = [
        'stat_kurulus'        => ['int', 1900, 2100],
        'stat_zirve'          => ['int', 0, 100000],
        'stat_yuruyus'        => ['int', 0, 100000],
        'stat_arama_kurtarma' => ['int', 0, 100000],
        'contact_email'       => ['email'],
        'contact_phone'       => ['text', 40],
        'contact_address'     => ['text', 255],
        'social_instagram'    => ['url'],
        'social_whatsapp'     => ['url'],
        'social_youtube'      => ['url'],
        'social_strava'       => ['url'],
        'maintenance'         => ['bool'],
        'maintenance_title'   => ['text', 120],
        'maintenance_message' => ['text', 400],
        'maintenance_eta'     => ['datetime'],
        'kvkk_retention_days' => ['int', 30, 3650],
    ];

    public function index(): void
    {
        $this->render('settings', 'Genel Ayarlar', 'settings', ['s' => Settings::all()]);
    }

    public function update(): void
    {
        $e = [];
        $values = [];
        foreach (self::FIELDS as $k => $rule) {
            $raw = trim((string) ($_POST[$k] ?? ''));
            switch ($rule[0]) {
                case 'int':
                    if (!preg_match('/^\d+$/', $raw) || (int) $raw < $rule[1] || (int) $raw > $rule[2]) {
                        $e[$k] = "{$rule[1]} ile {$rule[2]} arasında bir sayı yaz.";
                    }
                    break;
                case 'email':
                    if ($raw !== '' && !filter_var($raw, FILTER_VALIDATE_EMAIL)) {
                        $e[$k] = 'Geçerli bir e-posta adresi yaz.';
                    }
                    break;
                case 'url':
                    if ($raw !== '' && (!filter_var($raw, FILTER_VALIDATE_URL) || !str_starts_with($raw, 'https://'))) {
                        $e[$k] = 'https:// ile başlayan tam adres yaz ya da boş bırak.';
                    }
                    break;
                case 'bool':
                    $raw = $raw === '1' ? '1' : '0';
                    break;
                case 'datetime':
                    if ($raw !== '' && !\DateTime::createFromFormat('Y-m-d\TH:i', $raw)) {
                        $e[$k] = 'Geçerli bir tarih ve saat seç.';
                    }
                    $raw = $raw === '' ? '' : str_replace('T', ' ', $raw) . ':00';
                    break;
                default:
                    $raw = mb_substr($raw, 0, $rule[1]);
            }
            $values[$k] = $raw;
        }
        if ($e) {
            $this->back(url('admin.settings'), $e, $_POST);
        }
        $before = Settings::all();
        foreach ($values as $k => $v) {
            Settings::set($k, $v);
        }
        Audit::log('update', 'settings', null, ['changed' => array_keys(array_diff_assoc($values, array_intersect_key($before, $values)))]);
        $this->flash($values['maintenance'] === '1'
            ? 'Ayarlar kaydedildi. Bakım modu AÇIK: ziyaretçiler bakım sayfasını görüyor.'
            : 'Ayarlar kaydedildi.');
        redirect(url('admin.settings'), 303);
    }
}
