<?php
/**
 * Faaliyet günü yoklaması (rehber sahada telefondan kullanır).
 * Üstte ortak şerit; altında kaydırırken sabit kalan ilerleme + arama + süzgeç.
 * Satır: isim · numara (dokununca arar) · ara · Geldi ve Ödedi switch'leri (+ alınan tutar, varsayılanı ücret).
 * Geldi kapalı = gelmedi ya da henüz bakılmadı (attended NULL); açık = geldi (1).
 * JS varsa anında kaydeder; yoksa her buton normal form gönderimidir (CLAUDE.md §12).
 * @var array $a
 * @var array $rows    onaylı başvurular (attended: null|0|1, paid: 0|1, paid_amount)
 * @var bool  $canEdit işaretleyebilir mi (admin, editör, atanmış rehber)
 * @var array $head    ApplicationController::headCounts()
 */
use App\Controllers\Admin\ApplicationController as AC;

$ph = static fn (?string $p): string => $p ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '0$1 $2 $3 $4', $p) : '';
$paidCol = $a['fee'] === null || (float) $a['fee'] > 0;
$total = count($rows);
$att = static fn (array $p): ?int => $p['attended'] === null ? null : (int) $p['attended'];
$came = count(array_filter($rows, static fn ($p) => $att($p) === 1));
$paid = count(array_filter($rows, static fn ($p) => (int) $p['paid'] === 1));
$collected = array_sum(array_map(static fn ($p) => (int) $p['paid'] === 1 ? (float) $p['paid_amount'] : 0.0, $rows));
$missing = count(array_filter($rows, static fn ($p) => (int) $p['paid'] === 1 && $p['paid_amount'] === null));
$pct = static fn (int $n): string => $total ? round($n / $total * 100, 1) . '%' : '0%';
// Tutar alanında gösterim: 1500 → "1.500", 1500.5 → "1.500,50"
$amt = static fn ($v): string => $v === null ? '' : number_format((float) $v, fmod((float) $v, 1.0) === 0.0 ? 0 : 2, ',', '.');
?>
<?php $this->section('head') ?>
  <link rel="stylesheet" href="<?= e(asset('admin/participants.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('admin/checklist.css')) ?>">
  <script src="<?= e(asset('admin/checklist.js')) ?>" defer></script>
<?php $this->end() ?>

<?= $this->partial('admin/activities/_participants-head', ['a' => $a, 'head' => $head, 'current' => 'yoklama']) ?>

<?php if ($rows): ?>
<div class="ck-bar" data-ck-summary>
  <div class="ck-progress" aria-live="polite">
    <div class="ck-meter">
      <span class="ck-meter-label"><?= icon('checklist', 15) ?>Yoklama</span>
      <span class="ck-meter-num"><b data-sum="came"><?= $came ?></b> / <?= $total ?> geldi</span>
      <span class="ck-track" aria-hidden="true"><i class="is-yes" data-track="came" style="width:<?= $pct($came) ?>"></i></span>
      <span class="ck-meter-sub"><span data-sum="open"><?= $total - $came ?></span> kişi henüz gelmedi</span>
    </div>
<?php if ($paidCol): ?>
    <div class="ck-meter is-pay">
      <span class="ck-meter-label"><?= icon('wallet', 15) ?>Ödeme</span>
      <span class="ck-meter-num"><b data-sum="paid"><?= $paid ?></b> / <?= $total ?> ödedi</span>
      <span class="ck-track" aria-hidden="true"><i class="is-pay" data-track="paid" style="width:<?= $pct($paid) ?>"></i></span>
      <span class="ck-meter-sub" data-sum="paytext"><?= e(AC::payText($paid, $collected, $missing)) ?></span>
    </div>
<?php endif ?>
  </div>
  <div class="ck-tools" data-ck-tools hidden>
    <label class="search-box ck-search"><span class="sr-only">İsim ya da telefonla ara</span><input type="search" placeholder="İsim ya da telefon ara" data-ck-search autocomplete="off"></label>
    <div class="seg ck-filter" role="group" aria-label="Yoklama süzgeci">
      <button type="button" class="is-active" data-ck-filter="all" aria-pressed="true">Tümü</button>
      <button type="button" data-ck-filter="open" aria-pressed="false">Gelmeyen</button>
<?php if ($paidCol): ?>
      <button type="button" data-ck-filter="unpaid" aria-pressed="false">Ödemedi</button>
<?php endif ?>
    </div>
  </div>
</div>

<ul class="ck-list">
<?php foreach ($rows as $p): $at = $att($p); $pd = (int) $p['paid']; $pid = (int) $p['id']; ?>
  <li class="ck-row" id="p<?= $pid ?>" data-ck-row data-name="<?= e(mb_strtolower($p['name'], 'UTF-8') . ' ' . $p['phone']) ?>" data-attended="<?= $at === null ? '' : $at ?>" data-paid="<?= $pd ?>">
    <div class="ck-who">
      <span class="ck-name"><?= e($p['name']) ?></span>
      <a class="ck-phone" href="tel:+90<?= e($p['phone']) ?>"><?= e($ph($p['phone'])) ?></a>
      <span class="ck-sub"><?= e($p['transport'] === 'club' ? ($p['pickup'] ?: 'Kulüp aracı') : ($p['transport'] === 'own' ? 'Kendi aracı' : 'Ulaşım belirtilmedi')) ?><?php if ($p['emer_phone']): ?> · Acil: <a href="tel:+90<?= e($p['emer_phone']) ?>"><?= e($p['emer_name'] ?: $ph($p['emer_phone'])) ?></a><?php endif ?></span>
    </div>
    <a class="ck-call" href="tel:+90<?= e($p['phone']) ?>" aria-label="<?= e($p['name']) ?> adlı kişiyi ara, <?= e($ph($p['phone'])) ?>" title="Ara"><?= icon('phone', 18) ?></a>
<?php if ($canEdit): ?>
    <form class="ck-form" method="post" action="<?= e(url('admin.applications.checkin', ['id' => $pid])) ?>" data-ck-form>
      <?= csrf_field() ?>
      <button class="ck-switch is-came" name="attended" value="<?= $at === 1 ? '' : '1' ?>" role="switch" aria-checked="<?= $at === 1 ? 'true' : 'false' ?>" aria-label="<?= e($p['name']) ?> geldi"><span class="ck-switch-track" aria-hidden="true"><span class="ck-switch-knob"></span></span><span class="ck-switch-label" aria-hidden="true">Geldi</span></button>
<?php if ($paidCol): ?>
      <button class="ck-switch is-paid" name="paid" value="<?= $pd ? '0' : '1' ?>" role="switch" aria-checked="<?= $pd ? 'true' : 'false' ?>" aria-label="<?= e($p['name']) ?> ücreti ödedi"><span class="ck-switch-track" aria-hidden="true"><span class="ck-switch-knob"></span></span><span class="ck-switch-label" aria-hidden="true">Ödedi</span></button>
<?php endif ?>
    </form>
<?php if ($paidCol): ?>
    <form class="ck-amount" method="post" action="<?= e(url('admin.applications.checkin', ['id' => $pid])) ?>" data-ck-amount<?= $pd ? '' : ' hidden' ?>>
      <?= csrf_field() ?>
      <label class="sr-only" for="amt-<?= $pid ?>"><?= e($p['name']) ?> alınan tutar</label>
      <span class="ck-amount-field"><span aria-hidden="true">₺</span><input id="amt-<?= $pid ?>" name="amount" inputmode="decimal" autocomplete="off" value="<?= e($amt($p['paid_amount'])) ?>" placeholder="<?= $a['fee'] === null ? 'Tutar' : e($amt($a['fee'])) ?>" aria-describedby="amt-msg-<?= $pid ?>"></span>
      <button class="ck-amount-save" name="paid" value="1" aria-label="<?= e($p['name']) ?> tutarı kaydet" title="Tutarı kaydet"><?= icon('check', 16) ?></button>
      <span class="ck-amount-msg" id="amt-msg-<?= $pid ?>" role="status"></span>
    </form>
<?php endif ?>
<?php else: ?>
    <div class="ck-state">
      <span class="chip <?= $at === 1 ? 'green' : 'gray' ?> dot"><?= $at === 1 ? 'Geldi' : 'Gelmedi' ?></span>
<?php if ($paidCol): ?><span class="chip <?= $pd ? 'green' : 'gray' ?> dot"><?= $pd ? 'Ödedi' . ($p['paid_amount'] !== null ? ' · ' . e(tr_money((float) $p['paid_amount'])) : '') : 'Ödemedi' ?></span><?php endif ?>
    </div>
<?php endif ?>
  </li>
<?php endforeach ?>
</ul>
<p class="ck-empty-filter" data-ck-none hidden>Bu süzgeçte kimse yok.</p>
<?php else: ?>
<div class="panel"><div class="empty"><?= icon('users', 40) ?><h4>Onaylı katılımcı yok</h4><p>Başvurular onaylandıkça yoklama listesi burada oluşur.</p><a class="btn btn-ghost btn-sm" href="<?= e(url('admin.applications', ['id' => $a['id'], 'durum' => 'pending'])) ?>">Bekleyen başvurulara git</a></div></div>
<?php endif ?>
