<?php
/**
 * Bir faaliyetin başvuruları.
 * @var array       $a
 * @var array       $rows
 * @var string|null $status
 * @var array       $counts
 */
use App\Controllers\Admin\ApplicationController as AC;
use App\Core\Auth;

$S = require dirname(__DIR__) . '/_status.php';
$canEdit = Auth::canEdit();
$sensitive = Auth::canSeeSensitive();
$approved = $counts['approved'] ?? 0;
$phoneFmt = static fn (?string $p): string => $p ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '$1 $2 $3 $4', $p) : '—';
?>
<div class="page-head">
  <a class="back-link" href="<?= e(url('admin.activities.edit', ['id' => $a['id']])) ?>"><?= icon('back', 16) ?>Faaliyete dön</a>
  <div class="page-head-actions">
    <span class="mono muted"><?= e(tr_range($a['starts_on'], $a['ends_on'])) ?></span>
    <span class="chip <?= $S['activity'][$a['status']][1] ?> dot"><?= e($S['activity'][$a['status']][0]) ?></span>
  </div>
</div>

<div class="stat-cards stat-cards-sm">
  <div class="stat-card"><div class="sc-num"><?= (int) $a['taken'] ?><?= $a['quota'] ? '<small> / ' . (int) $a['quota'] . '</small>' : '' ?></div><div class="sc-label">Kontenjanı dolduran (bekleyen + onaylı)</div></div>
  <div class="stat-card"><div class="sc-num"><?= (int) ($counts['pending'] ?? 0) ?></div><div class="sc-label">Karar bekleyen</div></div>
  <div class="stat-card"><div class="sc-num"><?= (int) $approved ?></div><div class="sc-label">Onaylı</div></div>
  <div class="stat-card"><div class="sc-num"><?= (int) ($counts['waitlist'] ?? 0) ?></div><div class="sc-label">Yedek listede</div></div>
</div>

<div class="toolbar">
  <nav class="seg" aria-label="Başvuru filtresi">
    <a href="<?= e(url('admin.applications', ['id' => $a['id']])) ?>" class="<?= $status === null ? 'is-active' : '' ?>">Tümü</a>
<?php foreach ($S['application'] as $k => [$label]): ?>
    <a href="<?= e(url('admin.applications', ['id' => $a['id'], 'durum' => $k])) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= e($label) ?> <span class="mono">(<?= (int) ($counts[$k] ?? 0) ?>)</span></a>
<?php endforeach ?>
  </nav>
  <div class="tb-spacer"></div>
<?php if ($approved): ?>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications.print', ['id' => $a['id'], 'kind' => 'katilim'])) ?>" target="_blank" rel="noopener"><?= icon('print', 14) ?>Katılım listesi</a>
  <a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications.print', ['id' => $a['id'], 'kind' => 'tasima'])) ?>" target="_blank" rel="noopener"><?= icon('bus', 14) ?>Taşımacılık listesi</a>
<?php endif ?>
</div>

<?php if (!$sensitive): ?>
<p class="note-line"><?= icon('lock', 14) ?>TC kimlik ve sağlık notları yalnızca Admin rolüne görünür.</p>
<?php endif ?>

<div class="panel">
<?php if ($rows): ?>
  <ul class="app-list">
<?php foreach ($rows as $p): [$stLabel, $stColor] = $S['application'][$p['status']]; ?>
    <li class="app-row st-<?= e($p['status']) ?>" id="p<?= (int) $p['id'] ?>">
      <details>
        <summary>
          <span class="ar-who"><span class="ar-name"><?= e($p['name']) ?></span><span class="ar-sub mono muted"><?= e(tr_date(substr($p['created_at'], 0, 10))) ?><?= $p['has_health'] ? ' · sağlık notu var' : '' ?></span></span>
          <span class="chip <?= $stColor ?> dot"><?= e($stLabel) ?></span>
          <span class="ar-more" aria-hidden="true"><?= icon('arrowdown', 16) ?></span>
        </summary>
        <div class="ar-detail">
          <dl class="ar-grid">
            <div><dt>Telefon</dt><dd><a href="tel:+90<?= e($p['phone']) ?>" class="mono"><?= e($phoneFmt($p['phone'])) ?></a></dd></div>
            <div><dt>E-posta</dt><dd><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></dd></div>
            <div><dt>Acil durum</dt><dd><?= e($p['emer_name'] ?? '—') ?><?php if ($p['emer_phone']): ?> · <a href="tel:+90<?= e($p['emer_phone']) ?>" class="mono"><?= e($phoneFmt($p['emer_phone'])) ?></a><?php endif ?></dd></div>
<?php if ($sensitive): ?>
            <div><dt>TC Kimlik No</dt><dd class="mono"><?= e($p['tc'] ?? ($p['purged_at'] ? 'Silindi (KVKK)' : '—')) ?></dd></div>
            <div class="ar-full"><dt>Sağlık notu</dt><dd class="<?= $p['health'] ? 'warn' : '' ?>"><?= e($p['health'] ?? ($p['purged_at'] ? 'Silindi (KVKK)' : 'Belirtilmedi')) ?></dd></div>
<?php endif ?>
          </dl>
<?php if ($canEdit): ?>
          <form method="post" action="<?= e(url('admin.applications.update', ['id' => $p['id']])) ?>" class="ar-transport">
            <?= csrf_field() ?>
            <div class="field"><label for="tr-<?= (int) $p['id'] ?>">Ulaşım</label>
              <select id="tr-<?= (int) $p['id'] ?>" name="transport" data-transport>
                <option value="">Belirtilmedi</option>
<?php foreach (AC::TRANSPORT as $k => $label): ?>
                <option value="<?= $k ?>"<?= $p['transport'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
              </select></div>
            <div class="field" data-pickup<?= $p['transport'] === 'club' ? '' : ' hidden' ?>><label for="pk-<?= (int) $p['id'] ?>">Biniş noktası</label><input id="pk-<?= (int) $p['id'] ?>" name="pickup" list="pickup-list" value="<?= e($p['pickup']) ?>" placeholder="Ör. Rize Otogar"></div>
            <button class="btn btn-ghost btn-sm" type="submit">Ulaşımı kaydet</button>
          </form>
<?php else: ?>
          <p class="muted">Ulaşım: <?= e(AC::TRANSPORT[$p['transport']] ?? 'Belirtilmedi') ?><?= $p['pickup'] ? ' · ' . e($p['pickup']) : '' ?></p>
<?php endif ?>
        </div>
      </details>
<?php if ($canEdit): ?>
      <form method="post" action="<?= e(url('admin.applications.update', ['id' => $p['id']])) ?>" class="ar-actions">
        <?= csrf_field() ?>
<?php if ($p['status'] !== 'approved'): ?><button class="al-btn ok" name="status" value="approved"><?= icon('check', 14) ?>Onayla</button><?php endif ?>
<details class="action-menu"><summary class="btn btn-ghost btn-sm">Diğer işlemler</summary><div class="action-options">
<?php if ($p['status'] !== 'waitlist'): ?><button class="al-btn ghost" name="status" value="waitlist">Yedek</button><?php endif ?>
<?php if ($p['status'] !== 'rejected'): ?><button class="al-btn no" name="status" value="rejected" data-confirm="<?= e($p['name']) ?> adlı kişinin başvurusu reddedilsin mi?"><?= icon('x', 14) ?>Reddet</button><?php endif ?>
<?php if ($p['status'] !== 'pending'): ?><button class="al-btn ghost" name="status" value="pending">Beklemeye al</button><?php endif ?>
<?php if ($sensitive): ?><button class="al-btn ghost danger" formaction="<?= e(url('admin.applications.delete', ['id' => $p['id']])) ?>" data-confirm="Bu başvuru ve tüm kişisel verileri kalıcı olarak silinsin mi? (KVKK silme talebi)"><?= icon('trash', 14) ?><span class="sr-only">Sil</span></button><?php endif ?>
      </div></details>
      </form>
<?php endif ?>
    </li>
<?php endforeach ?>
  </ul>
  <datalist id="pickup-list"><?php foreach (AC::PICKUPS as $pk): ?><option value="<?= e($pk) ?>"><?php endforeach ?></datalist>
<?php else: ?>
  <div class="empty"><?= icon('users', 40) ?><h4>Başvuru yok</h4><p><?= $status ? 'Bu durumda başvuru bulunmuyor.' : 'Sitedeki formdan gelen başvurular burada listelenir.' ?></p></div>
<?php endif ?>
</div>
