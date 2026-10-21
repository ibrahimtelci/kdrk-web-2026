<?php
declare(strict_types=1);
namespace App\Core;

/** Görevli erişimi hem listede hem her kayıt isteğinde denetlenir. */
final class ActivityAccess
{
    public static function assigned(array $activity, int $userId): bool
    {
        return $userId > 0 && in_array($userId, [(int) ($activity['guide_primary_user_id'] ?? 0), (int) ($activity['guide_secondary_user_id'] ?? 0)], true);
    }
    public static function requireAssigned(array $activity): void
    {
        if (Auth::is('guide') && !self::assigned($activity, (int) Auth::user()['id'])) abort(403);
    }
    public static function canApprove(): bool { return Auth::canEdit() || Auth::is('guide'); }
}
