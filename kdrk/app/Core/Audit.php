<?php
declare(strict_types=1);

namespace App\Core;

/** Panelde yapılan değişikliklerin kaydı (audit_log). Kişisel veri yazılmaz. */
final class Audit
{
    public static function log(string $action, string $entity, ?int $entityId = null, array $details = []): void
    {
        try {
            $uid = Session::get('user_id');
            App::db()->insert('audit_log', [
                'user_id'   => is_int($uid) ? $uid : null,
                'action'    => $action,
                'entity'    => $entity,
                'entity_id' => $entityId,
                'details'   => $details ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (\Throwable $e) {
            error_log('audit: ' . $e->getMessage()); // kayıt hatası asıl işlemi durdurmaz
        }
    }
}
