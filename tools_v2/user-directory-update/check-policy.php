<?php
declare(strict_types=1);
require __DIR__ . '/../../kdrk_v2/bootstrap.php';
require __DIR__ . '/files/kdrk_v2/app/Core/Auth.php';
require __DIR__ . '/files/kdrk_v2/app/Core/ActivityAccess.php';
require __DIR__ . '/files/kdrk_v2/app/Models/GuideContact.php';
use App\Core\Auth;
use App\Core\ActivityAccess;
use App\Models\GuideContact;
$n = 0;
function check(bool $ok, string $name): void { global $n; if (!$ok) throw new RuntimeException($name); $n++; }
$reflect = new ReflectionClass(Auth::class);
$reflect->getProperty('loaded')->setValue(null, true);
foreach (['guide', 'viewer', 'editor', 'admin'] as $role) {
    $reflect->getProperty('user')->setValue(null, ['id' => 12, 'role' => $role]);
    check(Auth::canEdit() === in_array($role, ['admin','editor']), $role . ' content editing');
    check(ActivityAccess::canApprove() === ($role !== 'viewer'), $role . ' approval');
    check(Auth::canSeeSensitive() === ($role === 'admin'), $role . ' sensitive fields');
    try { ActivityAccess::requireAssigned(['guide_primary_user_id' => 99]); $allowed = true; }
    catch (App\Core\HttpException $e) { $allowed = false; check($e->status === 403, 'scoped denial HTTP status'); }
    check($allowed === ($role !== 'guide'), $role . ' cross activity access');
}
check(ActivityAccess::assigned(['guide_primary_user_id' => 12],12), 'primary assignment');
check(ActivityAccess::assigned(['guide_secondary_user_id' => '12'],12), 'secondary assignment');
check(!ActivityAccess::assigned([],0), 'empty assignment does not grant access');
check(!ActivityAccess::assigned(['guide_primary_user_id' => 12],13), 'different user denied');
foreach (['0532 123 45 67','5321234567','+90 (532) 123-45-67'] as $raw) check(GuideContact::normalizePhone($raw) === '+905321234567', 'phone normalization');
foreach (['','abc','123','0532abc1234567','+903121234567'] as $raw) check(GuideContact::normalizePhone($raw) === null, 'invalid mobile rejected');
echo $n . " policy and phone checks passed.\n";
