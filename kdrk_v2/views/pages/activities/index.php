<?php
/**
 * Faaliyet takvimi — yaklaşan + geçmiş, kart ve takvim görünümü.
 * Filtre/sekme/takvim betiği (assets/activities.js) kartların data-* özniteliklerinden okur;
 * takvimin sağ panelindeki kartlar aynı .activity-card'ların kompakt kopyasıdır.
 * @var array $activities
 */
$navIcons = require KDRK_ROOT . '/app/nav-icons.php';
// Kategori → menüdeki disiplin simgesi (app/menu.php ile aynı eşleşme)
$calIcons = array_map(static fn (string $k): string => $navIcons[$k], [
    'gunubirlik' => 'trail', 'dagcilik' => 'mountain', 'spor-tirmanis' => 'carabiner', 'dag-kayagi' => 'ski',
    'egitim' => 'book', 'sosyal' => 'chat', 'arama-kurtarma' => 'lifebuoy',
]);
$chevL = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>';
$chevR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>';
?>
<section class="list-header" data-screen-label="01 Header"><div class="container"><nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep" aria-hidden="true">›</span><span class="current" aria-current="page">Faaliyetler</span></nav><span class="eyebrow">Faaliyet Takvimi</span><h1>Faaliyetler</h1><p>Yaklaşan faaliyetlere başvur; geçmiş faaliyetlerin raporlarına ve fotoğraflarına göz at.</p><div class="header-meta"><div class="list-tabs" role="group" aria-label="Zaman aralığı"><button class="list-tab is-active" data-tab="yaklasan" aria-pressed="true">Yaklaşan <span class="count" data-tab-count="yaklasan"></span></button><button class="list-tab" data-tab="gecmis" aria-pressed="false">Geçmiş <span class="count" data-tab-count="gecmis"></span></button></div><span class="mono" data-header-meta></span></div></div></section>
<section class="filter-bar" data-screen-label="02 Filters"><div class="container filter-inner"><div class="filter-pills" role="group" aria-label="Kategori"><button class="filter-pill is-active" data-filter="all" aria-pressed="true">Tümü <span class="count" data-count="all"></span></button><button class="filter-pill" data-filter="gunubirlik" aria-pressed="false">Günübirlik Doğa Yürüyüşü <span class="count" data-count="gunubirlik"></span></button><button class="filter-pill" data-filter="dagcilik" aria-pressed="false">Dağcılık <span class="count" data-count="dagcilik"></span></button><button class="filter-pill" data-filter="spor-tirmanis" aria-pressed="false">Spor Tırmanış <span class="count" data-count="spor-tirmanis"></span></button><button class="filter-pill" data-filter="dag-kayagi" aria-pressed="false">Dağ Kayağı <span class="count" data-count="dag-kayagi"></span></button><button class="filter-pill" data-filter="egitim" aria-pressed="false">Eğitim Programı <span class="count" data-count="egitim"></span></button><button class="filter-pill" data-filter="sosyal" aria-pressed="false">Sosyal-Kültürel <span class="count" data-count="sosyal"></span></button><button class="filter-pill" data-filter="arama-kurtarma" aria-pressed="false">Arama Kurtarma <span class="count" data-count="arama-kurtarma"></span></button></div><div class="filter-tools"><div class="search-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input type="search" placeholder="Faaliyet veya yer ara…" data-search aria-label="Faaliyet ara"></div><div class="sort-dropdown" data-sort><button class="sort-trigger" data-sort-trigger aria-haspopup="true" aria-expanded="false"><span class="label">Sırala:</span><span data-sort-label>Tarih (yakın önce)</span><svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button><div class="sort-menu"><button data-sort-by="date-asc" class="is-selected">Tarih (yakın önce)</button><button data-sort-by="date-desc">Tarih (uzak önce)</button><button data-sort-by="difficulty">Zorluk (kolaydan zora)</button><button data-sort-by="popular">Doluluk (dolu önce)</button></div></div></div></div></section>
<section class="listing activities" data-screen-label="03 Listing"><div class="container"><div class="listing-meta"><div class="count-line" aria-live="polite"><strong data-result-count></strong> <span data-result-text></span></div><div class="view-toggle" role="group" aria-label="Görünüm"><button class="view-btn is-active" data-view="kart" aria-pressed="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/></svg>Kart</button><button class="view-btn" data-view="takvim" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/></svg>Takvim</button></div></div>

<div class="cal" data-cal data-today="<?= e(date('Y-m-d')) ?>" data-icons="<?= e(json_encode($calIcons, JSON_UNESCAPED_UNICODE)) ?>" hidden>
  <p class="cal-summary" data-cal-summary aria-live="polite"></p>
  <div class="cal-layout">
    <div class="cal-main">
      <div class="cal-toolbar">
        <div class="cal-month-nav">
          <button type="button" class="cal-nav" data-cal-prev aria-label="Önceki ay"><?= $chevL ?></button>
          <h2 class="cal-title" data-cal-title aria-live="polite"></h2>
          <button type="button" class="cal-nav" data-cal-next aria-label="Sonraki ay"><?= $chevR ?></button>
        </div>
        <div class="cal-jumps">
          <button type="button" class="cal-jump" data-cal-today>Bugün</button>
          <button type="button" class="cal-jump" data-cal-nextev>Sonraki faaliyet<?= $chevR ?></button>
        </div>
      </div>
      <p class="cal-note" data-cal-note role="status" hidden></p>
      <div class="cal-weekdays" aria-hidden="true"><span>PZT</span><span>SAL</span><span>ÇAR</span><span>PER</span><span>CUM</span><span class="is-weekend">CMT</span><span class="is-weekend">PAZ</span></div>
      <div class="cal-days" data-cal-grid role="group"></div>
    </div>
    <section class="cal-panel" aria-labelledby="cal-panel-title">
      <div class="cal-panel-head">
        <h2 class="cal-panel-title" id="cal-panel-title" data-panel-title tabindex="-1"></h2>
        <button type="button" class="cal-link" data-panel-all hidden>Ayın tümünü göster</button>
      </div>
      <div class="cal-panel-list" data-panel-list></div>
      <div class="cal-panel-empty" data-panel-empty hidden>
        <p data-panel-empty-text></p>
        <button type="button" class="cal-link" data-panel-empty-action></button>
      </div>
    </section>
  </div>
</div>

<div class="listing-grid activities-grid" data-grid>
<?php foreach ($activities as $a): ?>
  <?= $this->partial('partials/activity-card', ['a' => $a]) ?>
<?php endforeach ?>
</div><div class="empty-state" data-empty hidden><div class="topo-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 20l5-9 4 6 3-4 6 7"/><path d="M3 20h18"/></svg></div><span class="stamp">0 SONUÇ</span><h3 data-empty-title>Bu kategoride yaklaşan faaliyet yok.</h3><p>Filtreleri temizleyerek tüm faaliyetleri görebilir ya da geçmiş faaliyetlere bakabilirsin.</p><button class="btn btn-primary" data-clear-filters>Filtreleri temizle</button></div></div></section>

<?php $this->section('scripts') ?>
<script src="<?= e(asset('activities.js')) ?>" defer></script>
<?php $this->end() ?>
