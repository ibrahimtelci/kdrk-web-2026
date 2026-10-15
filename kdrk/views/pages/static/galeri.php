<!-- PAGE HEADER -->
<section class="list-header" data-screen-label="01 Header">
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a>
      <span class="sep">›</span>
      <span class="current">Fotoğraf Galerisi</span>
    </nav>
    <h1>Fotoğraf Galerisi</h1>
    <p><span data-stat="yil"><?= club_stat('yil') ?></span> yılın anılarını faaliyet faaliyet keşfet.</p>
    <div class="header-meta">
      <span class="mono"><strong>2.847 FOTOĞRAF</strong> · 168 FAALİYET · 1998 → 2026</span>
    </div>
  </div>
</section>

<!-- FILTER BAR -->
<section class="filter-bar" data-screen-label="02 Filters">
  <div class="container filter-inner">
    <div class="filter-selects">
      <label class="select-field">
        <span class="select-label">Yıl</span>
        <div class="select-wrap">
          <select data-year-select aria-label="Yıl filtresi">
            <option value="all" selected>Tüm yıllar</option>
            <option value="2026">2026</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
            <option value="2022">2022</option>
            <option value="2021">2021</option>
            <option value="2020">2020</option>
          </select>
          <svg class="select-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </label>
      <label class="select-field">
        <span class="select-label">Kategori</span>
        <div class="select-wrap">
          <select data-cat-select aria-label="Kategori filtresi">
            <option value="all">Tüm kategoriler</option>
          </select>
          <svg class="select-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
        </div>
      </label>
    </div>
    <div class="filter-tools">
      <div class="search-input">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        <input type="text" placeholder="Faaliyet adı ara…" data-search aria-label="Faaliyet ara"/>
      </div>
    </div>
  </div>
</section>

<!-- LISTING -->
<section class="gallery-list" data-screen-label="03 Listing">
  <div class="container">
    <div class="listing-meta">
      <div class="count-line"><strong data-result-count>10</strong> FAALİYET GÖSTERİLİYOR</div>
    </div>

    <!-- 2026 GROUP -->
    <div class="year-group" data-year="2026">
      <div class="year-header">
        <h2 class="year-num"><em>2026</em></h2>
        <span class="year-meta"><strong>3 FAALİYET</strong> · 157 FOTOĞRAF · DEVAM EDİYOR</span>
      </div>
      <div class="gallery-grid" data-grid>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2026" data-name="kackar zirve tirmanisi">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 47 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Kaçkar Zirve Tırmanışı</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 12 Tem 2026</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Çamlıhemşin / Rize · 3.937 m</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty very-hard pill">Çok Zor</span>
              <span class="pill cat">Dağcılık</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2026" data-name="avusor yaylasi kamp">
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
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Rize / Çamlıhemşin</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty medium pill">Orta</span>
              <span class="pill cat">Günübirlik</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2026" data-name="pokut yaylasi doga yuruyusu">
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
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Rize / Çamlıhemşin</span>
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

    <!-- 2025 GROUP -->
    <div class="year-group" data-year="2025">
      <div class="year-header">
        <h2 class="year-num"><em>2025</em></h2>
        <span class="year-meta"><strong>4 FAALİYET</strong> · 312 FOTOĞRAF</span>
      </div>
      <div class="gallery-grid" data-grid>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2025" data-name="kis dagciligi egitimi">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1551632436-cbf8dd35adfa?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 124 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Kış Dağcılığı Eğitimi 2025</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 18 Ara 2025</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Erzurum / Palandöken</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty education pill">Eğitim</span>
              <span class="pill cat">Eğitim Programı</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2025" data-name="kackar zirve tirmanisi">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 92 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Kaçkar Zirve Tırmanışı</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 14 Tem 2025</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Çamlıhemşin · 3.937 m</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty very-hard pill">Çok Zor</span>
              <span class="pill cat">Dağcılık</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2025" data-name="hazindak yaylasi">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 54 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Hazindak Yaylası Botanik Gezisi</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 03 Ağu 2025</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Rize / Hemşin</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty easy pill">Kolay</span>
              <span class="pill cat">Günübirlik</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2025" data-name="kavron gecidi">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 42 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Kavron Geçidi Trekking</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 22 Haz 2025</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Rize / Çamlıhemşin</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty medium-hard pill">Orta-Zor</span>
              <span class="pill cat">Dağcılık</span>
            </div>
          </div>
        </a>
      </div>
    </div>

    <!-- 2024 GROUP -->
    <div class="year-group" data-year="2024">
      <div class="year-header">
        <h2 class="year-num"><em>2024</em></h2>
        <span class="year-meta"><strong>3 FAALİYET</strong> · 198 FOTOĞRAF</span>
      </div>
      <div class="gallery-grid" data-grid>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2024" data-name="vercenik tepesi">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1551632811-561732d1e306?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 67 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Verçenik Tepesi Tırmanışı</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 08 Eyl 2024</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Rize / İkizdere · 3.711 m</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty hard pill">Zor</span>
              <span class="pill cat">Dağcılık</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2024" data-name="ayder selale yuruyusu">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1432405972618-c60b0225b8f9?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 81 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Ayder Şelale Yürüyüşü (Aile)</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 16 Haz 2024</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Rize / Ayder</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty easy pill">Kolay</span>
              <span class="pill cat">Günübirlik</span>
            </div>
          </div>
        </a>

        <a href="<?= e(url('galeri-ornek')) ?>" class="gallery-card reveal" data-year="2024" data-name="karagol sahara">
          <div class="gallery-card-img">
            <div style="background-image:url('https://images.unsplash.com/photo-1486870591958-9b9d0d1dda99?w=900&q=80&auto=format&fit=crop')"></div>
            <span class="gallery-card-cover-tag">Kapak</span>
            <span class="gallery-card-photos-tag"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg> 50 FOTOĞRAF</span>
          </div>
          <div class="gallery-card-body">
            <div>
              <h3>Karagöl–Sahara Trekking</h3>
              <div class="gallery-card-meta">
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg> 04 Ağu 2024</span>
                <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg> Artvin / Borçka</span>
              </div>
            </div>
            <div class="gallery-card-tags">
              <span class="activity-difficulty medium pill">Orta</span>
              <span class="pill cat">Günübirlik</span>
            </div>
          </div>
        </a>
      </div>
    </div>

    <!-- Empty state -->
    <div class="empty-state" data-empty hidden>
      <div class="topo-mark">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>
      </div>
      <span class="stamp">0 SONUÇ</span>
      <h3>Aramanla eşleşen galeri yok.</h3>
      <p>Yıl filtresini sıfırla ya da farklı bir faaliyet adı dene.</p>
      <button class="btn btn-primary" data-clear-filters>
        Tüm Galerilere Bak
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </button>
    </div>

    <!-- Pagination -->
    <div class="pagination">
      <button class="page-btn is-disabled" aria-label="Önceki"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
      <div class="pagination-pages">
        <button class="page-btn is-active">1</button>
        <button class="page-btn">2</button>
        <button class="page-btn">3</button>
        <button class="page-btn">4</button>
      </div>
      <button class="page-btn" aria-label="Sonraki"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
      <span class="page-info">Sayfa 1 / 4</span>
    </div>
  </div>
</section>

<!-- FOOTER -->


<!-- LIGHTBOX -->
<div class="lightbox" data-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Fotoğraf galerisi">
  <div class="lb-topbar">
    <div class="lb-title-block">
      <span class="eyebrow" data-lb-eyebrow>2026 · DAĞCILIK</span>
      <h3 data-lb-title>Faaliyet Adı</h3>
    </div>
    <div class="lb-counter"><strong data-lb-current>01</strong> <span style="opacity:0.5">/</span> <span data-lb-total>47</span></div>
    <div class="lb-actions">
      <a href="Faaliyet Detay.html" class="lb-action detail-link" data-lb-detail>
        Faaliyeti Gör
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
      </a>
      <button class="lb-action" data-lb-close aria-label="Kapat">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
      </button>
    </div>
  </div>
  <div class="lb-stage">
    <button class="lb-arrow prev" data-lb-prev aria-label="Önceki">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <div class="lb-img-wrap">
      <img class="lb-img" data-lb-img alt=""/>
      <div class="lb-caption" data-lb-caption></div>
    </div>
    <button class="lb-arrow next" data-lb-next aria-label="Sonraki">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 6l6 6-6 6"/></svg>
    </button>
  </div>
  <div class="lb-strip">
    <div class="lb-strip-track" data-lb-strip></div>
  </div>
</div>

<?php $this->section('scripts') ?>
<script>
  // ============================================================
  // Lightbox: convert <a class="gallery-card"> into buttons,
  // build deterministic photo sets, wire up navigation.
  // ============================================================
  (function setupLightbox(){
    const POOL = [
      'photo-1486870591958-9b9d0d1dda99','photo-1464822759023-fed622ff2c3b','photo-1500534314209-a25ddb2bd429',
      'photo-1551632436-cbf8dd35adfa','photo-1454496522488-7a8e488e8606','photo-1500382017468-9049fed747ef',
      'photo-1519681393784-d120267933ba','photo-1483728642387-6c3bdd6c93e5','photo-1464822759023-fed622ff2c3b',
      'photo-1506905925346-21bda4d32df4','photo-1469474968028-56623f02e42e','photo-1426604966848-d7adac402bff',
      'photo-1418065460487-3e41a6c84dc5','photo-1447752875215-b2761acb3c5d','photo-1501785888041-af3ef285b470',
      'photo-1464822759023-fed622ff2c3b','photo-1490598000245-075175152d25','photo-1505765050516-f72dcac9c60e',
      'photo-1502082553048-f009c37129b9','photo-1486870591958-9b9d0d1dda99','photo-1464822759023-fed622ff2c3b',
      'photo-1454496522488-7a8e488e8606','photo-1500382017468-9049fed747ef','photo-1519681393784-d120267933ba'
    ];
    function hash(str){ let h=0; for(let i=0;i<str.length;i++){ h=((h<<5)-h)+str.charCodeAt(i); h|=0; } return Math.abs(h); }
    function buildPhotoSet(seedKey, count, coverId){
      const photos = [];
      if (coverId) photos.push(coverId);
      const seed = hash(seedKey);
      const target = Math.min(count, POOL.length);
      let i = 0;
      let safety = 0;
      while (photos.length < target && safety < 200){
        const id = POOL[(seed + i*7) % POOL.length];
        if (!photos.includes(id)) photos.push(id);
        i++;
        safety++;
      }
      return photos;
    }
    function unsplashUrl(id, w){
      return `https://images.unsplash.com/${id}?w=${w}&q=80&auto=format&fit=crop`;
    }

    // Convert each card link → button, attach photo set
    const cards = Array.from(document.querySelectorAll('a.gallery-card'));
    cards.forEach(a => {
      const detailHref = a.getAttribute('href') || '<?= e(url('faaliyetler')) ?>';
      const name = a.dataset.name || '';
      const year = a.dataset.year || '';
      const title = a.querySelector('h3')?.textContent.trim() || '';
      const cat = a.querySelector('.pill.cat')?.textContent.trim() || '';
      const date = a.querySelector('.gallery-card-meta span')?.textContent.trim() || '';
      const photoTag = a.querySelector('.gallery-card-photos-tag')?.textContent || '';
      const m = photoTag.match(/(\d+)/);
      const count = m ? Math.min(parseInt(m[1],10), 12) : 12;
      const coverDiv = a.querySelector('.gallery-card-img > div[style*="background-image"]');
      let coverId = null;
      if (coverDiv){
        const url = coverDiv.getAttribute('style') || '';
        const idm = url.match(/photo-[\w-]+/);
        if (idm) coverId = idm[0];
      }
      const photos = buildPhotoSet(name + year, count, coverId);

      // Replace <a> with <div role="button">
      const btn = document.createElement('div');
      btn.className = a.className;
      btn.dataset.year = year;
      btn.dataset.name = name;
      btn.setAttribute('role','button');
      btn.setAttribute('tabindex','0');
      btn.innerHTML = a.innerHTML;
      btn._meta = { detailHref, title, year, cat, date, photos };
      a.parentNode.replaceChild(btn, a);

      const open = () => openLightbox(btn._meta);
      btn.addEventListener('click', open);
      btn.addEventListener('keydown', e => {
        if (e.key === 'Enter' || e.key === ' '){ e.preventDefault(); open(); }
      });
    });

    // Lightbox elements
    const lb = document.querySelector('[data-lightbox]');
    const lbImg = lb.querySelector('[data-lb-img]');
    const lbTitle = lb.querySelector('[data-lb-title]');
    const lbEyebrow = lb.querySelector('[data-lb-eyebrow]');
    const lbCurrent = lb.querySelector('[data-lb-current]');
    const lbTotal = lb.querySelector('[data-lb-total]');
    const lbCaption = lb.querySelector('[data-lb-caption]');
    const lbStrip = lb.querySelector('[data-lb-strip]');
    const lbDetail = lb.querySelector('[data-lb-detail]');
    const lbPrev = lb.querySelector('[data-lb-prev]');
    const lbNext = lb.querySelector('[data-lb-next]');
    const lbClose = lb.querySelector('[data-lb-close]');

    let currentMeta = null;
    let idx = 0;

    function pad(n){ return String(n).padStart(2,'0'); }

    function showPhoto(i){
      if (!currentMeta) return;
      idx = (i + currentMeta.photos.length) % currentMeta.photos.length;
      const id = currentMeta.photos[idx];
      lbImg.classList.remove('is-loaded');
      const img = new Image();
      img.onload = () => {
        lbImg.src = img.src;
        lbImg.classList.add('is-loaded');
      };
      img.src = unsplashUrl(id, 1800);
      lbCurrent.textContent = pad(idx+1);
      lbCaption.textContent = `${currentMeta.title} · ${pad(idx+1)}/${pad(currentMeta.photos.length)}`;
      // strip active
      lbStrip.querySelectorAll('.lb-thumb').forEach((t,j) => {
        t.classList.toggle('is-active', j === idx);
      });
      const activeThumb = lbStrip.querySelector('.lb-thumb.is-active');
      if (activeThumb){
        const stripRect = lbStrip.getBoundingClientRect();
        const thumbRect = activeThumb.getBoundingClientRect();
        const offset = (thumbRect.left - stripRect.left) - (stripRect.width/2 - thumbRect.width/2);
        lbStrip.scrollBy({ left: offset, behavior: 'smooth' });
      }
    }

    function openLightbox(meta){
      currentMeta = meta;
      idx = 0;
      lbTitle.textContent = meta.title;
      lbEyebrow.textContent = `${meta.year} · ${meta.cat.toUpperCase()} · ${meta.date}`;
      lbTotal.textContent = pad(meta.photos.length);
      lbDetail.href = meta.detailHref;
      // build strip
      lbStrip.innerHTML = '';
      meta.photos.forEach((id, i) => {
        const t = document.createElement('button');
        t.className = 'lb-thumb';
        t.style.backgroundImage = `url('${unsplashUrl(id, 200)}')`;
        t.innerHTML = `<span class="lb-thumb-num">${pad(i+1)}</span>`;
        t.addEventListener('click', () => showPhoto(i));
        lbStrip.appendChild(t);
      });
      lb.classList.add('is-open');
      lb.setAttribute('aria-hidden','false');
      document.body.style.overflow = 'hidden';
      showPhoto(0);
    }

    function closeLightbox(){
      lb.classList.remove('is-open');
      lb.setAttribute('aria-hidden','true');
      document.body.style.overflow = '';
      currentMeta = null;
    }

    lbPrev.addEventListener('click', () => showPhoto(idx-1));
    lbNext.addEventListener('click', () => showPhoto(idx+1));
    lbClose.addEventListener('click', closeLightbox);
    lb.addEventListener('click', e => { if (e.target === lb) closeLightbox(); });

    document.addEventListener('keydown', e => {
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') closeLightbox();
      else if (e.key === 'ArrowLeft') showPhoto(idx-1);
      else if (e.key === 'ArrowRight') showPhoto(idx+1);
    });

    // Touch swipe on stage
    const stage = lb.querySelector('.lb-stage');
    let touchStartX = 0;
    stage.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
    stage.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 50) showPhoto(dx < 0 ? idx+1 : idx-1);
    }, {passive:true});
  })();
</script>
<script>
  const cards = Array.from(document.querySelectorAll('.gallery-card'));
  const groups = Array.from(document.querySelectorAll('.year-group'));
  const empty = document.querySelector('[data-empty]');
  const resultCount = document.querySelector('[data-result-count]');
  const search = document.querySelector('[data-search]');
  const yearSel = document.querySelector('[data-year-select]');
  const catSel = document.querySelector('[data-cat-select]');

  // Build category list from cards
  const cats = new Map();
  cards.forEach(c => {
    const t = c.querySelector('.pill.cat')?.textContent.trim();
    if (t){
      const slug = t.toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9-]/g,'');
      if (!cats.has(slug)) cats.set(slug, t);
      c.dataset.cat = slug;
    }
  });
  Array.from(cats.entries()).sort((a,b)=>a[1].localeCompare(b[1],'tr')).forEach(([slug,label])=>{
    const o = document.createElement('option');
    o.value = slug; o.textContent = label;
    catSel.appendChild(o);
  });

  let activeYear = yearSel.value;   // '2026' default
  let activeCat = 'all';
  let searchTerm = '';

  function applyFilters() {
    const term = searchTerm.toLowerCase().trim();
    let visibleCount = 0;
    groups.forEach(g => {
      const yearMatch = activeYear === 'all' || g.dataset.year === activeYear;
      let groupVisible = 0;
      g.querySelectorAll('.gallery-card').forEach(c => {
        const nameMatch = !term || c.dataset.name.includes(term) || c.querySelector('h3').textContent.toLowerCase().includes(term);
        const catMatch = activeCat === 'all' || c.dataset.cat === activeCat;
        const show = yearMatch && nameMatch && catMatch;
        c.style.display = show ? '' : 'none';
        if (show) groupVisible++;
      });
      g.style.display = groupVisible > 0 ? '' : 'none';
      visibleCount += groupVisible;
    });
    empty.hidden = visibleCount > 0;
    resultCount.textContent = visibleCount;
  }

  yearSel.addEventListener('change', e => { activeYear = e.target.value; applyFilters(); });
  catSel.addEventListener('change', e => { activeCat = e.target.value; applyFilters(); });

  let st;
  search.addEventListener('input', e => {
    clearTimeout(st);
    st = setTimeout(() => { searchTerm = e.target.value; applyFilters(); }, 180);
  });

  document.querySelector('[data-clear-filters]').addEventListener('click', () => {
    yearSel.value = 'all';
    catSel.value = 'all';
    search.value = '';
    activeYear = 'all';
    activeCat = 'all';
    searchTerm = '';
    applyFilters();
  });

  applyFilters();
</script>
<?php $this->end() ?>
