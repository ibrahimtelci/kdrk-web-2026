<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\App;
use App\Core\Crypto;

/** Faaliyet başvuruları. TC kimlik ve sağlık notu şifreli saklanır (CLAUDE.md §15). */
final class Application
{
    /**
     * Form girdisini doğrular ve temizler. Kurallar activity-detail.js ile aynıdır.
     * @return array{0: array<string, string>, 1: array<string, string>} [temiz veri, alan => hata]
     */
    public static function validate(array $in): array
    {
        $str = static fn (string $k, int $max): string => mb_substr(trim((string) ($in[$k] ?? '')), 0, $max);
        $digits = static fn (string $k): string => preg_replace('/\D/', '', (string) ($in[$k] ?? ''));
        $phone = static fn (string $k): string => preg_replace('/^0/', '', $digits($k));

        $d = [
            'name'       => $str('name', 120),
            'tc'         => $digits('tc'),
            'phone'      => $phone('phone'),
            'email'      => mb_strtolower($str('email', 190)),
            'emer_name'  => $str('emer_name', 120),
            'emer_phone' => $phone('emer_phone'),
            'health'     => $str('health', 1000),
        ];

        $e = [];
        if (mb_strlen($d['name']) < 3) {
            $e['name'] = 'Ad ve soyadını yaz.';
        }
        if (!self::validTc($d['tc'])) {
            $e['tc'] = 'Geçerli bir TC Kimlik No yaz (11 hane).';
        }
        if (!preg_match('/^5\d{9}$/', $d['phone'])) {
            $e['phone'] = '5 ile başlayan 10 haneli telefon numarası yaz.';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            $e['email'] = 'Geçerli bir e-posta adresi yaz (ör. ad@ornek.com).';
        }
        if (mb_strlen($d['emer_name']) < 3) {
            $e['emer_name'] = 'Acil durumda aranacak kişinin adını yaz.';
        }
        if (!preg_match('/^5\d{9}$/', $d['emer_phone'])) {
            $e['emer_phone'] = 'Acil durum için 5 ile başlayan 10 haneli numara yaz.';
        }
        if (empty($in['kvkk'])) {
            $e['kvkk'] = 'Devam etmek için KVKK onayını işaretle.';
        }
        return [$d, $e];
    }

    /** TC Kimlik No sağlama algoritması. */
    public static function validTc(string $tc): bool
    {
        if (!preg_match('/^[1-9]\d{10}$/', $tc)) {
            return false;
        }
        $d = array_map('intval', str_split($tc));
        $d10 = (($d[0] + $d[2] + $d[4] + $d[6] + $d[8]) * 7 - ($d[1] + $d[3] + $d[5] + $d[7])) % 10;
        $d10 = ($d10 + 10) % 10;
        $d11 = array_sum(array_slice($d, 0, 10)) % 10;
        return $d10 === $d[9] && $d11 === $d[10];
    }

    /**
     * Başvuruyu kaydeder. Kontenjan kontrolü satır kilidiyle yapılır; doluysa yedek listeye alınır.
     * @return array{id:int, status:string}|array{error:string}
     */
    public static function create(int $activityId, array $d): array
    {
        return App::db()->transaction(static function ($db) use ($activityId, $d): array {
            $act = $db->one('SELECT id, quota FROM activities WHERE id = ? FOR UPDATE', [$activityId]);
            $dup = $db->value(
                "SELECT COUNT(*) FROM applications WHERE activity_id = ? AND email = ? AND status IN ('pending','approved','waitlist')",
                [$activityId, $d['email']]
            );
            if ($dup > 0) {
                return ['error' => 'Bu e-posta adresiyle bu faaliyete zaten başvuru yapılmış. Değişiklik için kulüple iletişime geç.'];
            }
            $taken = (int) $db->value(
                "SELECT COUNT(*) FROM applications WHERE activity_id = ? AND status IN ('pending','approved')",
                [$activityId]
            );
            $status = ((int) $act['quota'] > 0 && $taken >= (int) $act['quota']) ? 'waitlist' : 'pending';

            $id = $db->insert('applications', [
                'activity_id'     => $activityId,
                'name'            => $d['name'],
                'phone'           => $d['phone'],
                'email'           => $d['email'],
                'tc_enc'          => Crypto::encrypt($d['tc']),
                'emer_name'       => $d['emer_name'],
                'emer_phone'      => $d['emer_phone'],
                'health_enc'      => Crypto::encrypt($d['health']),
                'status'          => $status,
                'kvkk_consent_at' => date('Y-m-d H:i:s'),
            ]);
            return ['id' => $id, 'status' => $status];
        });
    }
}
