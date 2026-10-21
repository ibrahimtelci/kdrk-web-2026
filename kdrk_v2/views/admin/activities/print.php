<?php
/**
 * Yazdırılabilir Katılım / Taşımacılık listesi — yalnızca onaylı katılımcılar.
 * @var array  $a
 * @var string $kind       katilim | tasima
 * @var array  $rows
 * @var bool   $sensitive  TC ve sağlık notu sütunları yalnızca admin'e
 * @var bool   $autoPrint  ?bicim=pdf: sayfa açılınca yazdırma/PDF penceresi gelir
 */
use App\Controllers\Admin\ApplicationController as AC;
use App\Models\Activity;

$title = $kind === 'katilim' ? 'Katılım Listesi' : 'Taşımacılık Listesi';
$autoPrint ??= false;
$paidCol = $a['fee'] === null || (float) $a['fee'] > 0;
// Yoklamadan gelen işaret; işaretlenmemişse elle doldurulacak boş kutu.
$mark = static fn ($v): string => $v === null ? '<span class="box"></span>' : ((int) $v ? '&#10003;' : '&#10007;');
$ph = static fn (?string $p): string => $p ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '0$1 $2 $3 $4', $p) : '';
$riders = array_filter($rows, static fn ($p) => $p['transport'] !== 'own');
$own = array_filter($rows, static fn ($p) => $p['transport'] === 'own');
$groups = [];
foreach ($riders as $p) {
    $groups[$p['pickup'] ?: 'Biniş noktası belirtilmedi'][] = $p;
}
?>
<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title . ' · ' . $a['title']) ?></title>
<style>
@page{size:A4 <?= $kind === 'katilim' ? 'landscape' : 'portrait' ?>;margin:14mm}
*{box-sizing:border-box}body{font-family:Inter,Arial,sans-serif;font-size:10.5pt;color:#0B1220;margin:0;padding:16px}
header{display:flex;align-items:center;gap:14px;border-bottom:2px solid #0B1220;padding-bottom:10px;margin-bottom:12px}header img{width:54px;height:54px}header .t{flex:1}
h1{font-family:Manrope,Arial,sans-serif;font-weight:800;font-size:18pt;letter-spacing:-0.02em;margin:0}
.club{font-family:monospace;font-size:8pt;letter-spacing:.14em;text-transform:uppercase;color:#475569}
.act{display:grid;grid-template-columns:2fr 1fr 1.4fr 1fr;border:1px solid #CBD5E1;border-radius:6px;margin-bottom:10px}.act div{padding:7px 10px;border-right:1px solid #CBD5E1}.act div:last-child{border-right:0}
.act span,.fill span,th{font-family:monospace;font-size:7.5pt;letter-spacing:.12em;text-transform:uppercase;color:#475569}.act span{display:block}.act b{font-size:11pt}
.sum{display:flex;gap:20px;font-size:9.5pt;margin-bottom:10px}.fill{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px}.fill i{display:block;border-bottom:1px solid #0B1220;height:22px}
table{width:100%;border-collapse:collapse}th{text-align:left;border-bottom:1.5px solid #0B1220;padding:6px}td{padding:7px 6px;border-bottom:1px solid #E2E8F0;vertical-align:top}tr{page-break-inside:avoid}
.n{width:26px;color:#475569}.m{font-family:monospace;font-size:9pt}.sig{width:110px}.chk{width:52px;text-align:center}.amt{width:78px;white-space:nowrap;font-family:monospace;font-size:9pt}.box{display:inline-block;width:14px;height:14px;border:1.5px solid #0B1220;border-radius:3px}
.grp td{background:#ECEFF5;font-weight:600;font-size:9.5pt;border-bottom:1px solid #CBD5E1}.note{font-size:9.5pt;margin-top:10px}
footer{margin-top:18px;display:flex;justify-content:space-between;gap:20px;font-size:9pt}footer .s{flex:1;max-width:220px}footer .s i{display:block;border-bottom:1px solid #0B1220;height:30px;margin-bottom:4px}footer .s span{font-family:monospace;font-size:7.5pt;letter-spacing:.12em;text-transform:uppercase;color:#475569}
.gen{font-family:monospace;font-size:7.5pt;color:#475569;margin-top:12px}.kvkk{font-size:8pt;color:#475569;margin-top:6px}
.toolbar{margin-bottom:16px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}.toolbar .hint{font-size:10pt;color:#475569}.toolbar button{font:inherit;padding:10px 16px;border-radius:8px;border:1px solid #0B1220;background:#0B1220;color:#fff;cursor:pointer}
@media print{.toolbar{display:none}body{padding:0}}
</style>
</head>
<body>
<div class="toolbar"><button type="button" onclick="window.print()"><?= $autoPrint ? 'PDF olarak kaydet' : 'Yazdır' ?></button><?php if ($autoPrint): ?><span class="hint">Açılan pencerede hedef olarak <b>PDF olarak kaydet</b>i seç.</span><?php endif ?></div>
<header>
  <img src="<?= e(asset('kdrk-logo-2026-sm.png')) ?>" alt="">
  <div class="t"><div class="club">Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü</div><h1><?= e($title) ?></h1></div>
</header>
<div class="act">
  <div><span>Faaliyet</span><b><?= e($a['title']) ?></b></div>
  <div><span>Tarih</span><b><?= e(tr_range($a['starts_on'], $a['ends_on'])) ?></b></div>
  <div><span>Yer</span><b><?= e($a['location']) ?></b></div>
  <div><span>Kategori</span><b><?= e(Activity::CATEGORIES[$a['category']]) ?></b></div>
</div>

<?php if (!$rows): ?>
<p>Onaylı katılımcı yok.</p>
<?php elseif ($kind === 'katilim'): ?>
<div class="sum"><span>Onaylı katılımcı: <b><?= count($rows) ?></b></span><?php $came = count(array_filter($rows, static fn ($p) => $p['attended'] !== null && (int) $p['attended'] === 1)); if ($came): ?><span>Gelen: <b><?= $came ?></b></span><?php endif ?><?php $sumPaid = array_sum(array_map(static fn ($p) => (int) $p['paid'] ? (float) $p['paid_amount'] : 0.0, $rows)); if ($paidCol && $sumPaid > 0): ?><span>Tahsil edilen: <b><?= e(tr_money($sumPaid)) ?></b></span><?php endif ?><?php if ($sensitive): ?><span>Sağlık notu olan: <b><?= count(array_filter($rows, static fn ($p) => $p['health'])) ?></b></span><?php endif ?></div>
<table>
  <thead><tr><th class="n">#</th><th>Ad Soyad</th><?php if ($sensitive): ?><th>TC Kimlik No</th><?php endif ?><th>Telefon</th><th>Acil Durum Kişisi</th><?php if ($sensitive): ?><th>Sağlık Notu</th><?php endif ?><th class="chk">Geldi</th><?php if ($paidCol): ?><th class="chk amt">Ücret</th><?php endif ?><th class="sig">İmza</th></tr></thead>
  <tbody>
<?php foreach (array_values($rows) as $i => $p): ?>
    <tr><td class="n"><?= $i + 1 ?></td><td><b><?= e($p['name']) ?></b></td><?php if ($sensitive): ?><td class="m"><?= e($p['tc']) ?></td><?php endif ?><td class="m"><?= e($ph($p['phone'])) ?></td><td><?= e($p['emer_name']) ?><?php if ($p['emer_phone']): ?><br><span class="m"><?= e($ph($p['emer_phone'])) ?></span><?php endif ?></td><?php if ($sensitive): ?><td><?= e($p['health']) ?></td><?php endif ?><td class="chk"><?= $mark($p['attended']) ?></td><?php if ($paidCol): ?><td class="chk amt"><?= (int) $p['paid'] ? ($p['paid_amount'] !== null ? e(tr_money((float) $p['paid_amount'])) : '&#10003;') : '<span class="box"></span>' ?></td><?php endif ?><td class="sig"></td></tr>
<?php endforeach ?>
  </tbody>
</table>
<?php else: $n = 0; ?>
<div class="sum"><span>Kulüp aracı: <b><?= count($riders) ?></b> yolcu</span><span>Kendi aracı: <b><?= count($own) ?></b></span><span>Biniş noktası: <b><?= count($groups) ?></b></span></div>
<div class="fill"><div><span>Araç plakası</span><i></i></div><div><span>Sürücü</span><i></i></div><div><span>Sürücü telefonu</span><i></i></div><div><span>Kalkış saati</span><i></i></div></div>
<table>
  <thead><tr><th class="n">#</th><th>Ad Soyad</th><?php if ($sensitive): ?><th>TC Kimlik No</th><?php endif ?><th>Telefon</th><th>Acil Durum Telefonu</th><th class="chk">Bindi</th><th class="chk">Döndü</th></tr></thead>
  <tbody>
<?php foreach ($groups as $stop => $list): ?>
    <tr class="grp"><td colspan="<?= $sensitive ? 7 : 6 ?>"><?= e($stop) ?> · <?= count($list) ?> kişi</td></tr>
<?php foreach ($list as $p): ?>
    <tr><td class="n"><?= ++$n ?></td><td><b><?= e($p['name']) ?></b></td><?php if ($sensitive): ?><td class="m"><?= e($p['tc']) ?></td><?php endif ?><td class="m"><?= e($ph($p['phone'])) ?></td><td class="m"><?= e($ph($p['emer_phone'])) ?></td><td class="chk"><span class="box"></span></td><td class="chk"><span class="box"></span></td></tr>
<?php endforeach ?>
<?php endforeach ?>
  </tbody>
</table>
<?php if ($own): ?><p class="note">Kendi aracıyla katılan (<?= count($own) ?>): <?= e(implode(', ', array_column($own, 'name'))) ?></p><?php endif ?>
<?php endif ?>

<footer><div class="s"><i></i><span>Faaliyet Lideri</span></div><div class="s"><i></i><span>Kulüp Yetkilisi</span></div></footer>
<div class="gen">KDRK Yönetim Paneli · <?= e(date('d.m.Y H:i')) ?></div>
<?php if ($sensitive): ?><div class="kvkk">Bu liste kişisel veri içerir. Faaliyet bitince imha edilmelidir.</div><?php endif ?>
<?php if ($autoPrint): ?><script>window.addEventListener('load', () => setTimeout(() => window.print(), 250));</script><?php endif ?>
</body>
</html>
