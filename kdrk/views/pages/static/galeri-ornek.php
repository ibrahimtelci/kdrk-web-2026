<!-- HERO -->
<section class="gd-hero" data-screen-label="01 Hero">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:16px">
      <a href="<?= e(url('home')) ?>">Anasayfa</a>
      <span class="sep">›</span>
      <a href="<?= e(url('galeri')) ?>">Galeri</a>
      <span class="sep">›</span>
      <a href="<?= e(url('galeri')) ?>">2026</a>
      <span class="sep">›</span>
      <span class="current" aria-current="page">Kaçkar Zirve Tırmanışı</span>
    </nav>
    <div class="gd-hero-grid">
      <div>
        <span class="eyebrow">FAALİYET GALERİSİ</span>
        <h1>Kaçkar Zirve Tırmanışı</h1>
        <div class="gd-meta-line">
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> <strong>12 Temmuz 2026</strong></span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Çamlıhemşin / Rize · 3.937 m</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> <strong data-photo-total>47</strong> fotoğraf</span>
        </div>
      </div>
      <a href="<?= e(url('faaliyetler')) ?>" class="gd-cta">Faaliyet Detayını Gör <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
    </div>
  </div>
</section>

<!-- MASONRY GALLERY -->
<section class="gd-gallery" data-screen-label="02 Masonry">
  <div class="container">
    <div class="masonry" data-masonry></div>
  </div>
</section>

<!-- RELATED -->
<section class="gd-related" data-screen-label="03 Related">
  <div class="container">
    <h3>2026'dan diğer faaliyetler</h3>
    <div class="gd-related-grid">

      <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal">
        <div class="gallery-card-img">
          <div style="background-image:url('https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=900&q=80&auto=format&fit=crop')"></div>
          <span class="gallery-card-cover-tag">Kapak</span>
          <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 78 FOTOĞRAF</span>
        </div>
        <div class="gallery-card-body">
          <div>
            <h3>Avusor Yaylası Kamp</h3>
            <div class="gallery-card-meta">
              <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 25 May 2026</span>
            </div>
          </div>
          <div class="gallery-card-tags">
            <span class="activity-difficulty medium pill">Orta</span>
            <span class="pill cat">Günübirlik</span>
          </div>
        </div>
      </a>

      <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal">
        <div class="gallery-card-img">
          <div style="background-image:url('https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=900&q=80&auto=format&fit=crop')"></div>
          <span class="gallery-card-cover-tag">Kapak</span>
          <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 32 FOTOĞRAF</span>
        </div>
        <div class="gallery-card-body">
          <div>
            <h3>Pokut Yaylası Doğa Yürüyüşü</h3>
            <div class="gallery-card-meta">
              <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 18 May 2026</span>
            </div>
          </div>
          <div class="gallery-card-tags">
            <span class="activity-difficulty easy pill">Kolay</span>
            <span class="pill cat">Günübirlik</span>
          </div>
        </div>
      </a>

      <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal">
        <div class="gallery-card-img">
          <div style="background-image:url('https://images.unsplash.com/photo-1432405972618-c60b0225b8f9?w=900&q=80&auto=format&fit=crop')"></div>
          <span class="gallery-card-cover-tag">Kapak</span>
          <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 54 FOTOĞRAF</span>
        </div>
        <div class="gallery-card-body">
          <div>
            <h3>Hazindak Yaylası Botanik Gezisi</h3>
            <div class="gallery-card-meta">
              <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 03 Ağu 2026</span>
            </div>
          </div>
          <div class="gallery-card-tags">
            <span class="activity-difficulty easy pill">Kolay</span>
            <span class="pill cat">Günübirlik</span>
          </div>
        </div>
      </a>

    </div>
  </div>
</section>

<!-- LIGHTBOX -->
<div class="lightbox" data-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Fotoğraf görüntüleyici">
  <div class="lb-topbar">
    <div class="lb-title-block">
      <span class="eyebrow">2026 · DAĞCILIK · 12 TEMMUZ 2026</span>
      <h3>Kaçkar Zirve Tırmanışı</h3>
    </div>
    <div class="lb-counter"><strong data-lb-current>01</strong> <span style="opacity:0.5">/</span> <span data-lb-total>47</span></div>
    <div class="lb-actions">
      <button class="lb-action" data-lb-download aria-label="İndir" title="İndir">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
      </button>
      <button class="lb-action" data-lb-fullscreen aria-label="Tam ekran" title="Tam ekran">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 00-2 2v3M21 8V5a2 2 0 00-2-2h-3M3 16v3a2 2 0 002 2h3M16 21h3a2 2 0 002-2v-3"/></svg>
      </button>
      <button class="lb-action" data-lb-close aria-label="Kapat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
  </div>
  <div class="lb-stage">
    <button class="lb-arrow prev" data-lb-prev aria-label="Önceki"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
    <div class="lb-img-wrap">
      <img class="lb-img" data-lb-img alt=""/>
      <div class="lb-caption" data-lb-caption></div>
    </div>
    <button class="lb-arrow next" data-lb-next aria-label="Sonraki"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg></button>
  </div>
</div>

<!-- FOOTER -->

<?php $this->section('scripts') ?>
<script>
  // Generate masonry tiles + wire up lightbox
  (function(){
    const PHOTOS = [
      { id:'photo-1486870591958-9b9d0d1dda99', h:1200, credit:'M. Yılmaz', cap:'Zirve sırtında ilk ışık' },
      { id:'photo-1551632436-cbf8dd35adfa',    h:900,  credit:'A. Demir',  cap:'Buz kazma molası' },
      { id:'photo-1454496522488-7a8e488e8606', h:1400, credit:'E. Kaya',   cap:'Avusor kuzey duvarı' },
      { id:'photo-1500382017468-9049fed747ef', h:800,  credit:'M. Yılmaz', cap:'Sis altında yürüyüş' },
      { id:'photo-1519681393784-d120267933ba', h:1100, credit:'B. Çelik',  cap:'Gün doğumu, 3.500 m' },
      { id:'photo-1483728642387-6c3bdd6c93e5', h:1300, credit:'A. Demir',  cap:'Granit kayalıklar' },
      { id:'photo-1464822759023-fed622ff2c3b', h:850,  credit:'E. Kaya',   cap:'Çadır kuruluyor' },
      { id:'photo-1506905925346-21bda4d32df4', h:1000, credit:'M. Yılmaz', cap:'Yıldızlı gece' },
      { id:'photo-1469474968028-56623f02e42e', h:1500, credit:'B. Çelik',  cap:'Vadi panoraması' },
      { id:'photo-1426604966848-d7adac402bff', h:900,  credit:'A. Demir',  cap:'Buzul gölü' },
      { id:'photo-1418065460487-3e41a6c84dc5', h:1200, credit:'E. Kaya',   cap:'Kar köprüsü' },
      { id:'photo-1447752875215-b2761acb3c5d', h:800,  credit:'M. Yılmaz', cap:'Karaltıdan zirveye' },
      { id:'photo-1501785888041-af3ef285b470', h:1100, credit:'B. Çelik',  cap:'Yamaçtan bakış' },
      { id:'photo-1490598000245-075175152d25', h:1350, credit:'A. Demir',  cap:'Donmuş şelale' },
      { id:'photo-1505765050516-f72dcac9c60e', h:950,  credit:'E. Kaya',   cap:'Sırt çantaları sıralı' },
      { id:'photo-1502082553048-f009c37129b9', h:1250, credit:'M. Yılmaz', cap:'Saatler 04:30' },
      { id:'photo-1500534314209-a25ddb2bd429', h:1000, credit:'B. Çelik',  cap:'Çadırdan ilk göz' },
      { id:'photo-1551632811-561732d1e306',    h:1450, credit:'A. Demir',  cap:'Verçenik silueti' },
      { id:'photo-1432405972618-c60b0225b8f9', h:900,  credit:'E. Kaya',   cap:'Çiy damlaları' },
      { id:'photo-1486870591958-9b9d0d1dda99', h:1100, credit:'M. Yılmaz', cap:'Zirve dönüşü' },
      { id:'photo-1454496522488-7a8e488e8606', h:1300, credit:'B. Çelik',  cap:'Bulut denizi' },
      { id:'photo-1519681393784-d120267933ba', h:850,  credit:'A. Demir',  cap:'Aşağıdaki yayla' },
      { id:'photo-1500382017468-9049fed747ef', h:1200, credit:'E. Kaya',   cap:'Mola, kahve molası' },
      { id:'photo-1483728642387-6c3bdd6c93e5', h:1000, credit:'M. Yılmaz', cap:'İp boylarında' }
    ];
    const TOTAL = PHOTOS.length;
    function pad(n){ return String(n).padStart(2,'0'); }
    function url(id, w){ return `https://images.unsplash.com/${id}?w=${w}&q=80&auto=format&fit=crop`; }

    document.querySelector('[data-photo-total]').textContent = TOTAL;
    document.querySelector('[data-lb-total]').textContent = pad(TOTAL);

    const masonry = document.querySelector('[data-masonry]');
    const dates = ['12 Tem 04:30','12 Tem 06:14','12 Tem 07:48','12 Tem 09:22','12 Tem 10:51','12 Tem 12:30','12 Tem 13:45','12 Tem 14:18','12 Tem 15:02','12 Tem 16:30','12 Tem 17:55','12 Tem 19:10'];

    const frag = document.createDocumentFragment();
    PHOTOS.forEach((p, i) => {
      const tile = document.createElement('button');
      tile.className = 'masonry-tile';
      tile.dataset.idx = i;
      tile.style.aspectRatio = `800 / ${p.h}`;
      tile.innerHTML = `
        <img loading="lazy" decoding="async" src="${url(p.id, 600)}" alt="${p.cap}"/>
        <span class="masonry-tile-stamp">${dates[i % dates.length]}</span>
        <span class="masonry-tile-credit">© ${p.credit}</span>
        <div class="masonry-tile-overlay">
          <span class="masonry-tile-expand"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg></span>
        </div>`;
      tile.addEventListener('click', () => openLB(i));
      frag.appendChild(tile);
    });
    masonry.appendChild(frag);

    // Lightbox
    const lb = document.querySelector('[data-lightbox]');
    const lbImg = lb.querySelector('[data-lb-img]');
    const lbCurrent = lb.querySelector('[data-lb-current]');
    const lbCaption = lb.querySelector('[data-lb-caption]');
    const lbPrev = lb.querySelector('[data-lb-prev]');
    const lbNext = lb.querySelector('[data-lb-next]');
    const lbClose = lb.querySelector('[data-lb-close]');
    const lbDownload = lb.querySelector('[data-lb-download]');
    const lbFullscreen = lb.querySelector('[data-lb-fullscreen]');
    let idx = 0;

    function show(i){
      idx = (i + TOTAL) % TOTAL;
      const p = PHOTOS[idx];
      lbImg.classList.remove('is-loaded');
      const img = new Image();
      img.onload = () => { lbImg.src = img.src; lbImg.classList.add('is-loaded'); };
      img.src = url(p.id, 1800);
      lbCurrent.textContent = pad(idx+1);
      lbCaption.textContent = `${p.cap} · ${dates[idx % dates.length]} · © ${p.credit}`;
    }
    function openLB(i){
      lb.classList.add('is-open');
      lb.setAttribute('aria-hidden','false');
      document.body.style.overflow = 'hidden';
      show(i);
    }
    function closeLB(){
      lb.classList.remove('is-open');
      lb.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
    }
    lbPrev.addEventListener('click', () => show(idx-1));
    lbNext.addEventListener('click', () => show(idx+1));
    lbClose.addEventListener('click', closeLB);
    lb.addEventListener('click', e => { if (e.target === lb) closeLB(); });
    lbDownload.addEventListener('click', () => {
      const a = document.createElement('a');
      a.href = url(PHOTOS[idx].id, 2400);
      a.download = `kackar-${pad(idx+1)}.jpg`;
      a.target = '_blank';
      a.click();
    });
    lbFullscreen.addEventListener('click', () => {
      if (document.fullscreenElement) document.exitFullscreen();
      else lb.requestFullscreen?.();
    });
    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLB();
      else if (e.key === 'ArrowLeft') show(idx-1);
      else if (e.key === 'ArrowRight') show(idx+1);
    });
    let tx = 0;
    const stage = lb.querySelector('.lb-stage');
    stage.addEventListener('touchstart', e => { tx = e.touches[0].clientX; }, {passive:true});
    stage.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].clientX - tx;
      if (Math.abs(dx) > 50) show(dx < 0 ? idx+1 : idx-1);
    }, {passive:true});
  })();
</script>
<?php $this->end() ?>
