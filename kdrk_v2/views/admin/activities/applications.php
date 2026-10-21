<?php
/**
 * Bir faaliyetin başvuruları. Üstte ortak şerit (bağlam, Başvurular ↔ Yoklama, dışa aktar), altında
 * ortak filtre çubuğu (durum + arama). Satırda telefon görünür; bekleyen başvurunun kararı satırdan verilir,
 * ayrıntılar (acil durum, TC, sağlık, ulaşım) katlanır alanda.
 * @var array       $a
 * @var array       $rows
 * @var string|null $status
 * @var string      $q
 * @var array       $head  ApplicationController::headCounts()
 */
use App\Controllers\Admin\ApplicationController as AC;
use App\Core\Auth;

$S = require dirname(__DIR__) . '/_status.php';
$canEdit = \App\Core\ActivityAccess::canApprove();
$sensitive = Auth::canSeeSensitive();
$phoneFmt = static fn (?string $p): string => $p ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '0$1 $2 $3 $4', $p) : '—';
$listView = admin_list_view('basvurular');
$initial = static fn (string $n): string => mb_strtoupper(mb_substr(trim($n), 0, 1, 'UTF-8'), 'UTF-8');
// Durum sekmeleri: karar bekleyen önce (iş sırası), sayılar sekmede (ayrı sayı kartı yok)
$tabs = [['href' => url('admin.applications', array_filter(['id' => $a['id'], 'q' => $q ?: null])), 'label' => 'Tümü · ' . $head['total'], 'active' => $status === null, 'icon' => 'view-grid']];
foreach (['pending', 'approved', 'waitlist', 'rejected'] as $k) {
    $tabs[] = ['href' => url('admin.applications', array_filter(['id' => $a['id'], 'durum' => $k, 'q' => $q ?: null])), 'label' => $S['application'][$k][0] . ' · ' . $head[$k], 'active' => $status === $k, 'icon' => admin_filter_icon($k)];
}
?>
<?php $this->section('head') ?>
  <link rel="stylesheet" href="<?= e(asset('admin/participants.css')) ?>">
<?php $this->end() ?>

<?= $this->partial('admin/activities/_participants-head', ['a' => $a, 'head' => $head, 'current' => 'basvurular']) ?>

<?= $this->partial('admin/_list-toolbar', [
    'tabs' => $tabs,
    'filterLabel' => 'Başvuru durumu',
    'search' => ['action' => url('admin.applications', ['id' => $a['id']]), 'value' => $q, 'label' => 'İsim, telefon veya e-posta ara', 'hidden' => array_filter(['durum' => $status])],
    'viewSwitch' => ['key' => 'basvurular', 'current' => $listView],
]) ?>

<?php if (!$sensitive): ?>
<p class="note-line"><?= icon('lock', 14) ?>TC kimlik ve sağlık notları yalnızca Admin rolüne görünür.</p>
<?php endif ?>

<div class="panel pa-panel ui-list" data-view-key="basvurular" data-list-view="<?= e($listView) ?>">
<?php if ($rows): ?>
  <ul class="pa-list">
<?php foreach ($rows as $p): [$stLabel, $stColor] = $S['application'][$p['status']]; $pid = (int) $p['id']; ?>
    <li class="pa-row st-<?= e($p['status']) ?>" id="p<?= $pid ?>">
      <div class="pa-main">
        <span class="pa-avatar" aria-hidden="true"><?= e($initial($p['name'])) ?></span>
        <div class="pa-who">
          <span class="pa-name"><?= e($p['name']) ?></span>
          <span class="pa-line">
            <a class="pa-phone mono" href="tel:+90<?= e($p['phone']) ?>" aria-label="<?= e($p['name']) ?> adlı kişiyi ara, <?= e($phoneFmt($p['phone'])) ?>"><?= icon('phone', 14) ?><?= e($phoneFmt($p['phone'])) ?></a>
            <span class="pa-date mono"><?= e(tr_date(substr($p['created_at'], 0, 10))) ?></span>
<?php if ($p['has_health']): ?><span class="chip orange">Sağlık notu</span><?php endif ?>
<?php if ($p['status'] === 'approved' && $p['transport']): ?><span class="pa-tag"><?= e($p['transport'] === 'club' ? ($p['pickup'] ?: 'Kulüp aracı') : 'Kendi aracı') ?></span><?php endif ?>
          </span>
        </div>
        <span class="chip <?= $stColor ?> dot pa-status"><?= e($stLabel) ?></span>
      </div>
<?php if ($canEdit): ?>
      <form method="post" action="<?= e(url('admin.applications.update', ['id' => $pid])) ?>" class="pa-actions">
        <?= csrf_field() ?>
<?php if ($p['status'] !== 'approved'): ?>
        <button class="btn btn-sm pa-approve" name="status" value="approved"><?= icon('check', 14) ?>Onayla</button>
<?php endif ?>
<?php if ($p['status'] === 'pending'): ?>
        <button class="btn btn-ghost btn-sm" name="status" value="waitlist">Yedeğe al</button>
        <button class="btn-icon pa-reject" name="status" value="rejected" aria-label="<?= e($p['name']) ?> başvurusunu reddet" title="Reddet" data-confirm="<?= e($p['name']) ?> adlı kişinin başvurusu reddedilsin mi?"><?= icon('x', 16) ?></button>
<?php endif ?>
        <details class="pa-more">
          <summary class="btn-icon" aria-label="<?= e($p['name']) ?> için diğer işlemler" title="Diğer işlemler"><?= icon('more', 18) ?></summary>
          <div class="pa-more-panel">
<?php if ($p['status'] !== 'pending'): ?><button class="pa-more-item" name="status" value="pending"><?= icon('clock', 16) ?>Beklemeye al</button><?php endif ?>
<?php if ($p['status'] !== 'waitlist'): ?><button class="pa-more-item" name="status" value="waitlist"><?= icon(admin_filter_icon('waitlist'), 16) ?>Yedek listeye al</button><?php endif ?>
<?php if ($p['status'] !== 'rejected'): ?><button class="pa-more-item is-danger" name="status" value="rejected" data-confirm="<?= e($p['name']) ?> adlı kişinin başvurusu reddedilsin mi?"><?= icon('x', 16) ?>Reddet</button><?php endif ?>
<?php if ($sensitive): ?><button class="pa-more-item is-danger" formaction="<?= e(url('admin.applications.delete', ['id' => $pid])) ?>" data-confirm="Bu başvuru ve tüm kişisel verileri kalıcı olarak silinsin mi? (KVKK silme talebi)"><?= icon('trash', 16) ?>Kalıcı sil (KVKK)</button><?php endif ?>
          </div>
        </details>
      </form>
<?php endif ?>
      <details class="pa-detail">
        <summary><span>Ayrıntılar</span><?= icon('arrowdown', 16) ?></summary>
        <div class="pa-detail-body">
          <dl class="pa-grid">
            <div><dt>E-posta</dt><dd><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></dd></div>
            <div><dt>Acil durum</dt><dd><?= e($p['emer_name'] ?? '—') ?><?php if ($p['emer_phone']): ?> · <a href="tel:+90<?= e($p['emer_phone']) ?>" class="mono"><?= e($phoneFmt($p['emer_phone'])) ?></a><?php endif ?></dd></div>
<?php if ($sensitive): ?>
            <div><dt>TC Kimlik No</dt><dd class="mono"><?= e($p['tc'] ?? ($p['purged_at'] ? 'Silindi (KVKK)' : '—')) ?></dd></div>
            <div class="pa-full"><dt>Sağlık notu</dt><dd class="<?= $p['health'] ? 'warn' : '' ?>"><?= e($p['health'] ?? ($p['purged_at'] ? 'Silindi (KVKK)' : 'Belirtilmedi')) ?></dd></div>
<?php endif ?>
          </dl>
<?php if ($canEdit): ?>
          <form method="post" action="<?= e(url('admin.applications.update', ['id' => $pid])) ?>" class="pa-transport">
            <?= csrf_field() ?>
            <div class="field"><label for="tr-<?= $pid ?>">Ulaşım</label>
              <select id="tr-<?= $pid ?>" name="transport" data-transport>
                <option value="">Belirtilmedi</option>
<?php foreach (AC::TRANSPORT as $k => $label): ?>
                <option value="<?= $k ?>"<?= $p['transport'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
              </select></div>
            <div class="field" data-pickup<?= $p['transport'] === 'club' ? '' : ' hidden' ?>><label for="pk-<?= $pid ?>">Biniş noktası</label><input id="pk-<?= $pid ?>" name="pickup" list="pickup-list" value="<?= e($p['pickup']) ?>" placeholder="Ör. Rize Otogar"></div>
            <button class="btn btn-ghost btn-sm" type="submit"><?= icon('check', 14) ?>Ulaşımı kaydet</button>
          </form>
<?php else: ?>
          <p class="muted">Ulaşım: <?= e(AC::TRANSPORT[$p['transport']] ?? 'Belirtilmedi') ?><?= $p['pickup'] ? ' · ' . e($p['pickup']) : '' ?></p>
<?php endif ?>
        </div>
      </details>
    </li>
<?php endforeach ?>
  </ul>
  <datalist id="pickup-list"><?php foreach (AC::PICKUPS as $pk): ?><option value="<?= e($pk) ?>"><?php endforeach ?></datalist>
<?php else: ?>
  <div class="empty"><?= icon('users', 40) ?><h4><?= $q !== '' ? 'Eşleşen başvuru yok' : 'Başvuru yok' ?></h4><p><?= $q !== '' ? 'Aramayı değiştirip tekrar dene.' : ($status ? 'Bu durumda başvuru bulunmuyor.' : 'Sitedeki formdan gelen başvurular burada listelenir.') ?></p></div>
<?php endif ?>
</div>
