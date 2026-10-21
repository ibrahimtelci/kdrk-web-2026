<?php
use App\Core\Auth;
use App\Core\Settings;
use App\Models\Banner;
use App\Models\News;

$canEdit = Auth::canEdit();
$ordered = Banner::orderedCandidates();
$manual = Settings::get('slider_order', '[]') !== '[]';
$types = ['fixed' => 'Tanıtım', 'activity' => 'Faaliyet', 'news' => 'Duyuru & Haber'];
$editUrl = static fn ($kind, $id) => url(match ($kind) {
    'fixed' => 'admin.banners.edit', 'activity' => 'admin.activities.edit', default => 'admin.news.edit',
}, ['id' => $id]);
$tabs = ['tanitim' => ['Tanıtım', count($fixed)], 'faaliyet' => ['Faaliyet', count($activities)], 'haber' => ['Duyuru & Haber', count($news)]];
$rows = match ($tab) { 'faaliyet' => $activities, 'haber' => $news, default => $fixed };
?>
<link rel="stylesheet" href="<?= e(asset('admin/banner-workspace.css')) ?>">
<div class="banner-workspace" data-banner-workspace>
  <div class="bw-heading">
    <div><h2>Anasayfanın ilk izlenimini düzenle</h2><p class="muted">Görselleri seç, yayın sırasını belirle. İlk <?= Banner::MAX ?> görsel anasayfada gösterilir.</p></div>
    <a class="btn btn-ghost" href="<?= e(url('home')) ?>" target="_blank" rel="noopener"><?= icon('ext', 16) ?>Anasayfayı aç</a>
  </div>

  <section class="panel" aria-labelledby="bw-lineup-title">
    <div class="panel-head bw-section-head">
      <div><h2 id="bw-lineup-title">Yayın sırası</h2><p class="f-hint">Yukarı ve aşağı oklarla sırayı değiştir. Değişiklikler hemen siteye yansır.</p></div>
      <span class="chip green dot"><?= min(count($ordered), Banner::MAX) ?> / <?= Banner::MAX ?> yayında</span>
    </div>
    <div class="panel-body">
      <div class="bw-order-mode"><span class="chip <?= $manual ? 'orange' : 'gray' ?>"><?= $manual ? 'Elle sıralama' : 'Otomatik sıralama' ?></span>
        <span class="f-hint">İlk tanıtım görseli başta kalır. Otomatik düzende yakın faaliyetler ve yeni duyurular öne çıkar.</span>
<?php if ($canEdit && $manual): ?>
        <form method="post" action="<?= e(url('admin.banners.order')) ?>"><?= csrf_field() ?><button class="btn btn-ghost" name="do" value="reset" data-confirm="Yayın sırası otomatik düzene dönsün mü?">Otomatik sıraya dön</button></form>
<?php endif ?>
      </div>
<?php if (!$ordered): ?>
      <div class="empty"><?= icon('banner', 40) ?><h4>Anasayfa için henüz görsel seçilmedi</h4><p>Aşağıdaki kütüphaneden bir tanıtım, faaliyet veya duyuru ekle.</p><a class="btn btn-primary" href="#gorsel-kutuphanesi">Görsel kütüphanesine git</a></div>
<?php else: ?>
      <ol class="bw-lineup">
<?php foreach ($ordered as $i => $s):
    $locked = $i === 0 && $s['kind'] === 'fixed';
    $waiting = $i >= Banner::MAX;
?>
        <li class="bw-slide<?= $waiting ? ' bw-waiting' : '' ?>">
          <a class="bw-image" href="<?= e($editUrl($s['kind'], $s['source_id'])) ?>">
            <img src="<?= e(img($s['image'], 900)) ?>" alt="" loading="lazy" decoding="async">
            <span class="bw-position"><?= $i + 1 ?></span>
          </a>
          <div class="bw-card-body">
            <div class="bw-tags"><span class="chip <?= $waiting ? 'gray' : 'green' ?> dot"><?= $waiting ? 'Sırada bekliyor' : 'Sitede gösteriliyor' ?></span><span class="f-hint"><?= e($types[$s['kind']]) ?></span></div>
            <h3><a href="<?= e($editUrl($s['kind'], $s['source_id'])) ?>"><?= e(fill_stats($s['title'])) ?></a></h3>
            <p class="f-hint"><?= $locked ? 'Kulüp tanıtımı · İlk sırada sabit' : ($waiting ? 'İlk ' . Banner::MAX . ' içine taşıyarak yayına al.' : e(fill_stats($s['eyebrow'] ?? ''))) ?></p>
            <div class="bw-card-actions">
              <a class="btn btn-ghost" href="<?= e($editUrl($s['kind'], $s['source_id'])) ?>"><?= $canEdit ? 'Düzenle' : 'İncele' ?></a>
<?php if ($canEdit): ?>
              <form method="post" action="<?= e(url('admin.banners.order')) ?>" class="bw-arrows">
                <?= csrf_field() ?><input type="hidden" name="key" value="<?= e($s['kind'] . ':' . $s['source_id']) ?>">
                <button class="btn-icon" name="do" value="up" aria-label="<?= e(fill_stats($s['title'])) ?>: bir sıra öne taşı"<?= $locked || ($i === 1 && $ordered[0]['kind'] === 'fixed') ? ' disabled' : '' ?>><?= icon('arrowup', 18) ?></button>
                <button class="btn-icon" name="do" value="down" aria-label="<?= e(fill_stats($s['title'])) ?>: bir sıra geriye taşı"<?= $locked || $i === count($ordered) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 18) ?></button>
              </form>
<?php endif ?>
            </div>
          </div>
        </li>
<?php endforeach ?>
      </ol>
<?php endif ?>
<?php if (!$ordered || $ordered[0]['kind'] !== 'fixed'): ?>
      <p class="bw-notice">Kulübü tanıtan bir açılış görseli ekleyerek ilk sırayı tamamlayabilirsin.</p>
<?php endif ?>
    </div>
  </section>

  <section class="panel" id="gorsel-kutuphanesi" aria-labelledby="bw-library-title">
    <div class="panel-head bw-section-head"><div><h2 id="bw-library-title">Görsel kütüphanesi</h2><p class="f-hint">Tanıtım görsellerini yönet veya mevcut içeriklerden anasayfaya görsel ekle.</p></div>
<?php if ($canEdit): ?><a class="btn btn-primary" href="<?= e(url('admin.banners.new')) ?>" data-modal-link><?= icon('plus', 18) ?>Yeni tanıtım görseli</a><?php endif ?>
    </div>
    <div class="panel-body">
      <nav class="bw-source-tabs" aria-label="Görsel kaynağı">
<?php foreach ($tabs as $key => [$label, $number]): ?>
        <a class="btn <?= $tab === $key ? 'btn-primary' : 'btn-ghost' ?>" href="<?= e(url('admin.banners', $key === 'tanitim' ? [] : ['sekme' => $key])) ?>#gorsel-kutuphanesi"<?= $tab === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?><span class="bw-count"><?= $number ?></span></a>
<?php endforeach ?>
      </nav>
<?php if ($canEdit && $tab !== 'tanitim'): $isAct = $tab === 'faaliyet'; ?>
      <form method="post" action="<?= e(url($isAct ? 'admin.banners.activity' : 'admin.banners.news')) ?>" class="bw-add">
        <?= csrf_field() ?><input type="hidden" name="do" value="add">
<?php if ($candidates): ?>
        <div class="field"><label for="bw-source"><?= $isAct ? 'Anasayfada gösterilecek faaliyet' : 'Anasayfada gösterilecek duyuru veya haber' ?></label>
          <select id="bw-source" name="<?= $isAct ? 'activity_id' : 'news_id' ?>" required><option value="">İçerik seç…</option>
<?php foreach ($candidates as $c): ?>
            <option value="<?= (int) $c['id'] ?>"<?= $c['cover_image'] ? '' : ' disabled' ?>><?= e(tr_date($isAct ? $c['starts_on'] : $c['published_on']) . ' · ' . $c['title'] . ($c['cover_image'] ? '' : ' (önce kapak görseli ekle)')) ?></option>
<?php endforeach ?>
          </select><span class="f-hint">Başlık ve görsel içerikten alınır. İlk <?= Banner::MAX ?> doluysa yeni eklenen görsel sırada bekleyebilir.<?= $isAct ? ' Faaliyetin tarihi geçince gösterim biter.' : ' Tekrar eklenen duyurunun eski gösterim bitiş tarihi temizlenir.' ?></span>
        </div><button class="btn btn-primary" type="submit"><?= icon('plus', 16) ?>Yayın sırasına ekle</button>
<?php else: ?>
        <p class="muted">Eklenebilecek <?= $isAct ? 'yaklaşan ve yayındaki faaliyet' : 'yayındaki duyuru veya haber' ?> yok.</p>
<?php endif ?>
      </form>
<?php endif ?>
      <div class="bw-library-toolbar" data-library-tools hidden><label class="field" for="bw-search">Bu listedeki görsellerde ara<input id="bw-search" type="search" placeholder="Başlığa göre ara" data-banner-search></label><span class="f-hint" data-result-count role="status" aria-live="polite"></span></div>
      <div class="bw-library-grid">
<?php foreach ($rows as $i => $r):
    $kind = match ($tab) { 'faaliyet' => 'activity', 'haber' => 'news', default => 'fixed' };
    $state = $kind === 'fixed' ? $r['state'] : $r['slide_state'];
    $image = $kind === 'fixed' ? $r['image'] : $r['cover_image'];
    $title = fill_stats($r['title']);
?>
        <article class="bw-library-card" data-banner-card data-title="<?= e($title) ?>" id="<?= $kind === 'fixed' ? 'slayt' : ($kind === 'activity' ? 'faaliyet' : 'haber') ?>-<?= (int) $r['id'] ?>">
          <a class="bw-library-image" href="<?= e($editUrl($kind, $r['id'])) ?>"><?php if ($image): ?><img src="<?= e(img($image, 400)) ?>" alt="" loading="lazy" decoding="async"><?php else: ?><span>Kapak görseli yok</span><?php endif ?></a>
          <div class="bw-card-body"><span class="chip <?= $r['on_site'] ? 'green' : 'gray' ?> dot"><?= e($state) ?></span><h3><a href="<?= e($editUrl($kind, $r['id'])) ?>"><?= e($title) ?></a></h3>
<?php if ($kind === 'fixed'): ?>
            <p class="f-hint"><?= e(fill_stats($r['eyebrow'] ?: 'Kulüp tanıtım görseli')) ?></p>
<?php else: ?>
            <p class="f-hint"><?= e($kind === 'activity' ? tr_range($r['starts_on'], $r['ends_on']) : ($r['slider_until'] ? tr_date($r['slider_until']) . ' gününe kadar gösterilir' : 'Gösterim bitiş tarihi yok')) ?></p>
<?php endif ?>
            <div class="bw-card-actions"><a class="btn btn-ghost" href="<?= e($editUrl($kind, $r['id'])) ?>"><?= $canEdit ? 'Düzenle' : 'İncele' ?></a>
<?php if ($canEdit && $kind !== 'fixed'): ?>
              <form method="post" action="<?= e(url($kind === 'activity' ? 'admin.banners.activity' : 'admin.banners.news')) ?>"><?= csrf_field() ?><input type="hidden" name="<?= $kind === 'activity' ? 'activity_id' : 'news_id' ?>" value="<?= (int) $r['id'] ?>"><button class="btn btn-ghost" name="do" value="remove" data-confirm="Bu içerik anasayfadan kaldırılsın mı? İçeriğin kendisi silinmez.">Listeden çıkar</button></form>
<?php elseif ($canEdit): ?>
              <form method="post" action="<?= e(url('admin.banners.move', ['id' => $r['id']])) ?>" class="bw-arrows"><?= csrf_field() ?><button class="btn-icon" name="do" value="up" aria-label="<?= e($title) ?>: tanıtım önceliğini yükselt"<?= $i === 0 ? ' disabled' : '' ?>><?= icon('arrowup', 16) ?></button><button class="btn-icon" name="do" value="down" aria-label="<?= e($title) ?>: tanıtım önceliğini düşür"<?= $i === count($rows) - 1 ? ' disabled' : '' ?>><?= icon('arrowdown', 16) ?></button></form>
<?php endif ?>
            </div>
          </div>
        </article>
<?php endforeach ?>
      </div>
<?php if (!$rows): ?><div class="empty"><?= icon('banner', 40) ?><h4>Bu bölümde henüz görsel yok</h4><p><?= $tab === 'tanitim' ? 'Yeni tanıtım görseli ekleyerek başla.' : 'Yukarıdaki alandan bir içerik seçerek yayın sırasına ekle.' ?></p></div><?php endif ?>
      <p class="bw-no-results muted" data-no-results hidden>Aramana uygun görsel bulunamadı.</p>
      <details class="bw-help"><summary>Gösterim ve sıralama nasıl çalışır?</summary><p>Tanıtım görselleri burada hazırlanır. Faaliyet ve duyuruların görseli, başlığı ve bağlantıları kendi kayıtlarından gelir. Yayına uygun görseller üstteki yayın sırasına girer; yalnızca ilk <?= Banner::MAX ?> tanesi sitede görünür. Kapalı, taslak, süresi dolmuş veya kapaksız içerikler yayın sırasına girmez.</p><p>Tanıtım önceliği okları açılış görselini belirler. Yayın sırasındaki oklar ise farklı kaynakların gösterim sırasını değiştirir.</p></details>
    </div>
  </section>
</div>
<script src="<?= e(asset('admin/banner-workspace.js')) ?>" defer></script>
