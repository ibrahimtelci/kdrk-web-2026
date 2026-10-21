<?php
/** @var array $m */
$phone = $m['phone'] ? preg_replace('/^(\d{3})(\d{3})(\d{2})(\d{2})$/', '$1 $2 $3 $4', $m['phone']) : null;
?>
<div class="page-head"><a class="back-link" href="<?= e(url('admin.messages')) ?>"><?= icon('back', 16) ?>Mesajlar</a></div>
<article class="panel">
  <div class="panel-head">
    <div><h2><?= e($m['name']) ?></h2><span class="ph-sub"><?= e(tr_date(substr($m['created_at'], 0, 10))) ?> · <?= e(substr($m['created_at'], 11, 5)) ?></span></div>
    <span class="chip gray"><?= e($m['subject']) ?></span>
  </div>
  <div class="panel-body">
    <dl class="ar-grid">
      <div><dt>E-posta</dt><dd><a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a></dd></div>
<?php if ($phone): ?>
      <div><dt>Telefon</dt><dd><a class="mono" href="tel:+90<?= e($m['phone']) ?>"><?= e($phone) ?></a></dd></div>
<?php endif ?>
    </dl>
    <div class="msg-body"><?= paragraphs($m['body']) ?></div>
  </div>
  <div class="save-bar save-bar-static">
    <form method="post" action="<?= e(url('admin.messages.delete', ['id' => $m['id']])) ?>"><?= csrf_field() ?><button class="btn btn-danger btn-sm" data-confirm="Bu mesaj silinsin mi?"><?= icon('trash', 14) ?>Sil</button></form>
    <form method="post" action="<?= e(url('admin.messages.unread', ['id' => $m['id']])) ?>"><?= csrf_field() ?><button class="btn btn-ghost btn-sm">Okunmadı yap</button></form>
    <div class="tb-spacer"></div>
    <a class="btn btn-primary" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: ' . $m['subject']) ?>"><?= icon('mail') ?>E-postayla yanıtla</a>
  </div>
</article>
