<?php
/**
 * Fotoğraf galerisi listesi. Filtreler GET formudur; JS yalnızca seçim değişince formu gönderir.
 * @var array $groups   yıl => albümler (Gallery::decorate)
 * @var int   $total
 * @var int   $pages
 * @var int   $pageNo   geçerli sayfa
 * @var array $filters  yil, kategori, q
 * @var array $overview years, categories, albums, photos
 */
$num = static fn (int $n): string => number_format($n, 0, ',', '.');
$firstYear = $overview['years'] ? min($overview['years']) : null;
$filtered = $filters['yil'] || $filters['kategori'] || $filters['q'] !== '';
$pageUrl = static fn (int $p): string => url('galeri', array_filter([
    'yil' => $filters['yil'], 'kategori' => $filters['kategori'], 'q' => $filters['q'], 'sayfa' => $p > 1 ? $p : null,
], static fn ($v) => $v !== null && $v !== ''));
$chev = '<svg class="select-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>';
?>
<section class="list-header" data-screen-label="01 Header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a>
      <span class="sep" aria-hidden="true">›</span>
      <span class="current" aria-current="page">Fotoğraf Galerisi</span>
    </nav>
    <span class="eyebrow">Sahadan</span>
    <h1>Fotoğraf Galerisi</h1>
    <p><span data-stat="yil"><?= club_stat('yil') ?></span> yılın anılarını faaliyet faaliyet keşfet.</p>
<?php if ($overview['photos']): ?>
    <div class="header-meta">
      <span class="mono corp-hero-stamp"><strong><?= $num($overview['photos']) ?> FOTOĞRAF</strong> · <?= $num($overview['albums']) ?> ALBÜM<?= $firstYear ? ' · ' . $firstYear . ' → ' . date('Y') : '' ?></span>
    </div>
<?php endif ?>
  </div>
</section>

<section class="filter-bar" data-screen-label="02 Filters">
  <form class="container filter-inner" method="get" action="<?= e(url('galeri')) ?>" role="search" data-gallery-filter>
    <div class="filter-selects">
      <label class="select-field">
        <span class="select-label">Yıl</span>
        <span class="select-wrap">
          <select name="yil">
            <option value="">Tüm yıllar</option>
<?php foreach ($overview['years'] as $y): ?>
            <option value="<?= $y ?>"<?= $filters['yil'] === $y ? ' selected' : '' ?>><?= $y ?></option>
<?php endforeach ?>
          </select>
          <?= $chev ?>
        </span>
      </label>
      <label class="select-field">
        <span class="select-label">Kategori</span>
        <span class="select-wrap">
          <select name="kategori">
            <option value="">Tüm kategoriler</option>
<?php foreach ($overview['categories'] as $k => $label): ?>
            <option value="<?= e($k) ?>"<?= $filters['kategori'] === $k ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach ?>
          </select>
          <?= $chev ?>
        </span>
      </label>
    </div>
    <div class="filter-tools">
      <div class="search-input">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" placeholder="Albüm veya yer ara…" aria-label="Albüm veya yer ara" maxlength="80" enterkeyhint="search">
      </div>
      <button type="submit" class="btn btn-ghost" data-filter-submit>Filtrele</button>
    </div>
  </form>
</section>

<section class="gallery-list" data-screen-label="03 Listing">
  <div class="container">
    <div class="listing-meta">
      <div class="count-line"><strong><?= $num($total) ?></strong> ALBÜM<?= $filtered ? ' BULUNDU' : '' ?><?= $pages > 1 ? ' · SAYFA ' . $pageNo . ' / ' . $pages : '' ?></div>
<?php if ($filtered): ?>
      <a class="btn-text" href="<?= e(url('galeri')) ?>">Filtreleri temizle</a>
<?php endif ?>
    </div>

<?php foreach ($groups as $year => $albums):
    $photos = array_sum(array_column($albums, 'photo_count')); ?>
    <div class="year-group">
      <div class="year-header">
        <h2 class="year-num"><em><?= $year ?: 'Tarihsiz' ?></em></h2>
        <span class="year-meta"><strong><?= count($albums) ?> ALBÜM</strong> · <?= $num((int) $photos) ?> FOTOĞRAF</span>
      </div>
      <div class="gallery-grid">
<?php foreach ($albums as $g): ?>
        <?= $this->partial('partials/gallery-card', ['g' => $g]) ?>
<?php endforeach ?>
      </div>
    </div>
<?php endforeach ?>

<?php if (!$total): ?>
    <div class="empty-state">
      <div class="topo-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg></div>
      <span class="stamp">0 SONUÇ</span>
<?php if ($filtered): ?>
      <h3>Aramanla eşleşen albüm yok.</h3>
      <p>Yıl veya kategori filtresini değiştir ya da farklı bir ad dene.</p>
      <a class="btn btn-primary" href="<?= e(url('galeri')) ?>">Tüm Albümlere Bak</a>
<?php else: ?>
      <h3>Henüz albüm yok.</h3>
      <p>Faaliyet fotoğrafları yüklendikçe burada görünecek.</p>
      <a class="btn btn-primary" href="<?= e(url('faaliyetler')) ?>">Faaliyet Takvimine Git</a>
<?php endif ?>
    </div>
<?php endif ?>

<?php if ($pages > 1): ?>
    <nav class="pagination" aria-label="Sayfalar">
<?php if ($pageNo > 1): ?>
      <a class="page-btn" href="<?= e($pageUrl($pageNo - 1)) ?>" aria-label="Önceki sayfa" rel="prev"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></a>
<?php else: ?>
      <span class="page-btn is-disabled" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></span>
<?php endif ?>
      <div class="pagination-pages">
<?php for ($p = 1; $p <= $pages; $p++): ?>
        <a class="page-btn<?= $p === $pageNo ? ' is-active' : '' ?>" href="<?= e($pageUrl($p)) ?>"<?= $p === $pageNo ? ' aria-current="page"' : '' ?>><?= $p ?></a>
<?php endfor ?>
      </div>
<?php if ($pageNo < $pages): ?>
      <a class="page-btn" href="<?= e($pageUrl($pageNo + 1)) ?>" aria-label="Sonraki sayfa" rel="next"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg></a>
<?php else: ?>
      <span class="page-btn is-disabled" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></span>
<?php endif ?>
      <span class="page-info">Sayfa <?= $pageNo ?> / <?= $pages ?></span>
    </nav>
<?php endif ?>
  </div>
</section>

<?php $this->section('scripts') ?>
<script src="<?= e(asset('gallery.js')) ?>" defer></script>
<?php $this->end() ?>
