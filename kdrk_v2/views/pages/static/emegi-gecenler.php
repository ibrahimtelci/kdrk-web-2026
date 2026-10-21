<!-- HERO -->
<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep" aria-hidden="true">›</span><a href="<?= e(url('hakkimizda')) ?>">Kurumsal</a><span class="sep" aria-hidden="true">›</span><span class="current">Emeği Geçenler</span>
    </nav>
    <span class="eyebrow" style="color:#fff">KURUMSAL</span>
    <h1>Emeği geçen herkese.</h1>
    <p class="lede">KDRK bir gönüllülük hareketidir. <span data-stat="yil"><?= club_stat('yil') ?></span> yıldır rotaları açan liderlerden, fotoğrafları çeken arkadaşlara; herkesin emeği bu dağda iz bıraktı.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> 2010 → 2026 · TEŞEKKÜRLER</div>
  </div>
</section>

<!-- MEMORIAM -->
<section class="corp-section" data-screen-label="02 Anma">
  <div class="container">
    <div class="honor-band reveal">
      <div class="topo-watermark"></div>
      <div class="honor-avatar" style="background-image:url('https://images.unsplash.com/photo-1552058544-f2b08422138a?w=400&q=80&auto=format&fit=crop')"></div>
      <div class="honor-text">
        <span class="h-eyebrow">Saygıyla Anıyoruz</span>
        <h3>Kemal Aksoy (1958–2021)</h3>
        <p>Kurucu üyemiz ve ilk eğitmenimiz. Kaçkar'ın her patikasını avucunun içi gibi bilirdi. Öğrettiği her düğüm, bağladığı her ip hâlâ bizimle. Rotalarımızda yaşıyor.</p>
      </div>
    </div>
  </div>
</section>

<!-- CONTRIBUTORS GROUPS -->
<section class="contrib-section corp-section alt" data-screen-label="03 Katkı">
  <div class="container">

    <!-- Geçmiş Yönetim Kurulları -->
    <div class="contrib-group reveal">
      <div class="contrib-group-head">
        <h3>Geçmiş Yönetim Kurulları</h3>
        <span class="cg-count">1998 → 2024</span>
        <span class="cg-desc">İki yılda bir genel kurulda seçilen kurullar. Dönem fotoğrafını görmek için tıkla.</span>
      </div>
      <div class="terms-list" data-terms></div>
    </div>

    <!-- Sporcular & Antrenörler -->
    <div class="contrib-group reveal">
      <div class="contrib-group-head">
        <h3>Sporcular &amp; Antrenörler</h3>
        <span class="cg-count">8 KİŞİ</span>
        <span class="cg-desc">Kulübü yarışmalarda temsil eden ve yetiştiren ekip.</span>
      </div>
      <div class="athlete-grid">
        <article class="athlete-card">
          <div class="athlete-photo">
            <div style="background-image:url('https://images.unsplash.com/photo-1522163182402-834f871fd851?w=600&q=80&auto=format&fit=crop')"></div>
            <span class="athlete-disc">Spor Tırmanış</span>
            <div class="athlete-overlay">
              <div class="athlete-name">Elif Şahin</div>
              <div class="athlete-role">Baş Antrenör</div>
            </div>
          </div>
          <div class="athlete-body">
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg> TDF Lider seviye antrenör</div>
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9V2h12v7a6 6 0 01-12 0zM6 5H3v2a3 3 0 003 3M18 5h3v2a3 3 0 01-3 3"/></svg> 2023 Bölge Şampiyonası 1.</div>
          </div>
        </article>

        <article class="athlete-card">
          <div class="athlete-photo">
            <div style="background-image:url('https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=600&q=80&auto=format&fit=crop')"></div>
            <span class="athlete-disc">Dağcılık</span>
            <div class="athlete-overlay">
              <div class="athlete-name">Emre Kaya</div>
              <div class="athlete-role">Dağcılık Antrenörü</div>
            </div>
          </div>
          <div class="athlete-body">
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 20l5-9 4 6 3-4 6 7"/></svg> 22 zirve tırmanışı</div>
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg> Buzul &amp; kaya uzmanı</div>
          </div>
        </article>

        <article class="athlete-card">
          <div class="athlete-photo">
            <div style="background-image:url('https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&q=80&auto=format&fit=crop')"></div>
            <span class="athlete-disc">Spor Tırmanış</span>
            <div class="athlete-overlay">
              <div class="athlete-name">Zeynep Ak</div>
              <div class="athlete-role">Sporcu</div>
            </div>
          </div>
          <div class="athlete-body">
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 9V2h12v7a6 6 0 01-12 0zM6 5H3v2a3 3 0 003 3M18 5h3v2a3 3 0 01-3 3"/></svg> Genç Milli Takım kampı</div>
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 20l5-9 4 6 3-4 6 7"/></svg> Lead &amp; Boulder</div>
          </div>
        </article>

        <article class="athlete-card">
          <div class="athlete-photo">
            <div style="background-image:url('https://images.unsplash.com/photo-1502767089025-6572583495c9?w=600&q=80&auto=format&fit=crop')"></div>
            <span class="athlete-disc">Dağ Kayağı</span>
            <div class="athlete-overlay">
              <div class="athlete-name">Deniz Akın</div>
              <div class="athlete-role">Skitur Antrenörü</div>
            </div>
          </div>
          <div class="athlete-body">
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 20l5-9 4 6 3-4 6 7"/></svg> 9 sezon skitur deneyimi</div>
            <div class="athlete-achievement"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3 7h7l-5.5 4 2 7L12 16l-6.5 4 2-7L2 9h7z"/></svg> Çığ güvenliği eğitmeni</div>
          </div>
        </article>
      </div>
    </div>

    <!-- Gönüllüler -->
    <div class="contrib-group reveal">
      <div class="contrib-group-head">
        <h3>Gönüllüler &amp; Destekçiler</h3>
        <span class="cg-count">20+ KİŞİ</span>
        <span class="cg-desc">Fotoğraf, lojistik, web, organizasyon — perde arkasındaki herkes.</span>
      </div>
      <div class="contrib-grid">
        <div class="contrib-card"><div class="contrib-avatar initials">BÇ</div><div class="contrib-info"><div class="contrib-name">Berk Çetin</div><div class="contrib-role">Fotoğraf</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">NA</div><div class="contrib-info"><div class="contrib-name">Nazlı Aydın</div><div class="contrib-role">Sosyal Medya</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">ST</div><div class="contrib-info"><div class="contrib-name">Selim Tunç</div><div class="contrib-role">Lojistik</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">CÖ</div><div class="contrib-info"><div class="contrib-name">Cem Öztürk</div><div class="contrib-role">Web &amp; Teknik</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">HU</div><div class="contrib-info"><div class="contrib-name">Hande Ulu</div><div class="contrib-role">Etkinlik Org.</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">EÖ</div><div class="contrib-info"><div class="contrib-name">Ediz Önal</div><div class="contrib-role">Video</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">TG</div><div class="contrib-info"><div class="contrib-name">Tuğçe Güneş</div><div class="contrib-role">İletişim</div></div></div>
        <div class="contrib-card"><div class="contrib-avatar initials">AB</div><div class="contrib-info"><div class="contrib-name">Ali Barış</div><div class="contrib-role">Malzeme Sorumlusu</div></div></div>
      </div>
    </div>

  </div>
</section>

<!-- THANK YOU CTA -->
<section class="corp-section" data-screen-label="04 CTA">
  <div class="container">
    <div class="join-band reveal">
      <div class="topo-watermark"></div>
      <span class="eyebrow center" style="color:#fff;justify-content:center">Sen de Katıl</span>
      <h2>Bu listede senin de adın olabilir.</h2>
      <p>KDRK gönüllülerle büyür. Fotoğraf çek, rota planla, lider ol ya da sadece gel — her emek değerli.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('kulup-uyeligi')) ?>" class="btn btn-primary">Gönüllü Ol <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('hakkimizda')) ?>" class="btn btn-ghost">Hakkımızda</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->

<?php $this->section('scripts') ?>
<script>
  // Geçmiş Yönetim Kurulları — 1998'den beri 2 yıllık dönemler
  (function(){
    const TERMS = [
      { p:'2022–2024', baskan:'Murat Kayhan',
        roster:[['Bşk.Yrd','Reşat Yalçın'],['Sayman','Hakan Demir'],['Sekreter','Burak Çelik'],['Üye','Sevgi Doğan'],['Üye','Pınar Öz']] },
      { p:'2020–2022', baskan:'Reşat Yalçın',
        roster:[['Bşk.Yrd','Murat Kayhan'],['Sayman','Filiz Korkmaz'],['Sekreter','Sevgi Doğan'],['Üye','Osman Çakır'],['Üye','Yusuf Kara']] },
      { p:'2018–2020', baskan:'Reşat Yalçın',
        roster:[['Bşk.Yrd','Sevgi Doğan'],['Sayman','Filiz Korkmaz'],['Sekreter','İlhan Tan'],['Üye','Murat Kayhan'],['Üye','Gül Arslan']] },
      { p:'2016–2018', baskan:'Sevgi Doğan',
        roster:[['Bşk.Yrd','Osman Çakır'],['Sayman','Filiz Korkmaz'],['Sekreter','İlhan Tan'],['Üye','Kemal Aksoy'],['Üye','Gül Arslan']] },
      { p:'2014–2016', baskan:'Kemal Aksoy',
        roster:[['Bşk.Yrd','Sevgi Doğan'],['Sayman','Osman Çakır'],['Sekreter','İlhan Tan'],['Üye','Reşat Yalçın'],['Üye','Filiz Korkmaz']] },
      { p:'2012–2014', baskan:'Kemal Aksoy',
        roster:[['Bşk.Yrd','İlhan Tan'],['Sayman','Osman Çakır'],['Sekreter','Gül Arslan'],['Üye','Sevgi Doğan'],['Üye','Reşat Yalçın']] },
      { p:'2010–2012', baskan:'İlhan Tan',
        roster:[['Bşk.Yrd','Kemal Aksoy'],['Sayman','Osman Çakır'],['Sekreter','Gül Arslan'],['Üye','Filiz Korkmaz'],['Üye','Reşat Yalçın']] },
      { p:'2008–2010', baskan:'İlhan Tan',
        roster:[['Bşk.Yrd','Hüseyin Aktaş'],['Sayman','Necla Bozkurt'],['Sekreter','Gül Arslan'],['Üye','Kemal Aksoy'],['Üye','Selma Yıldırım']] },
      { p:'2006–2008', baskan:'Hüseyin Aktaş',
        roster:[['Bşk.Yrd','İlhan Tan'],['Sayman','Necla Bozkurt'],['Sekreter','Tamer Koç'],['Üye','Selma Yıldırım'],['Üye','Kemal Aksoy']] },
      { p:'2004–2006', baskan:'Hüseyin Aktaş',
        roster:[['Bşk.Yrd','Tamer Koç'],['Sayman','Necla Bozkurt'],['Sekreter','Aydın Şen'],['Üye','İlhan Tan'],['Üye','Selma Yıldırım']] },
      { p:'2002–2004', baskan:'Aydın Şen',
        roster:[['Bşk.Yrd','Hüseyin Aktaş'],['Sayman','Tamer Koç'],['Sekreter','Necla Bozkurt'],['Üye','Erol Güneş'],['Üye','Selma Yıldırım']] },
      { p:'2000–2002', baskan:'Aydın Şen',
        roster:[['Bşk.Yrd','Erol Güneş'],['Sayman','Tamer Koç'],['Sekreter','Hüseyin Aktaş'],['Üye','Necla Bozkurt'],['Üye','Mehmet Polat']] },
      { p:'1998–2000', baskan:'Erol Güneş', founding:true,
        roster:[['Bşk.Yrd','Aydın Şen'],['Sayman','Mehmet Polat'],['Sekreter','Hüseyin Aktaş'],['Üye','Tamer Koç'],['Üye','Necla Bozkurt']] }
    ];
    const wrap = document.querySelector('[data-terms]');
    if (!wrap) return;

    function slug(p){ return 'board-' + p.replace(/[^0-9]/g, '-'); }

    TERMS.forEach((t, i) => {
      const item = document.createElement('div');
      item.className = 'term-item' + (i === 0 ? ' is-open' : '');
      const rosterHtml = t.roster.map(r =>
        `<div class="roster-cell"><span class="roster-text"><span class="roster-role">${r[0]}</span><span class="roster-name">${r[1]}</span></span></div>`
      ).join('');
      const slotId = slug(t.p);
      item.innerHTML = `
        <button class="term-head" aria-expanded="${i===0}">
          <span class="term-period">${t.p}</span>
          <span class="term-pres">Başkan: <strong>${t.baskan}</strong></span>
          <span class="term-badges">
            ${t.founding ? '<span class="term-current" style="background:#1A2638;color:#94A3B8;border:1px solid #1F2A3D">İlk Dönem</span>' : ''}
            <span class="term-chevron"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></span>
          </span>
        </button>
        <div class="term-body">
          <div class="term-body-inner">
            <image-slot class="term-board-photo" id="${slotId}" shape="rounded" radius="10"
              placeholder="${t.p} Yönetim Kurulu fotoğrafını sürükle"></image-slot>
            <div class="term-photo-caption"><span>${t.p}</span><span class="dot"></span><span>Başkan: ${t.baskan}</span></div>
            <div class="term-roster">${rosterHtml}</div>
          </div>
        </div>`;
      wrap.appendChild(item);

      const head = item.querySelector('.term-head');
      const body = item.querySelector('.term-body');
      function measure(){ body.style.maxHeight = item.classList.contains('is-open') ? body.scrollHeight + 'px' : '0px'; }
      if (i === 0) requestAnimationFrame(measure);
      head.addEventListener('click', () => {
        item.classList.toggle('is-open');
        head.setAttribute('aria-expanded', item.classList.contains('is-open'));
        measure();
      });
      // Foto yüklenince yükseklik güncellensin
      const slotEl = item.querySelector('image-slot');
      if (slotEl) slotEl.addEventListener('load', () => { if (item.classList.contains('is-open')) measure(); });
    });
    // Recalculate open heights on resize
    window.addEventListener('resize', () => {
      wrap.querySelectorAll('.term-item.is-open .term-body').forEach(b => {
        b.style.maxHeight = b.scrollHeight + 'px';
      });
    });
  })();
</script>
<?php $this->end() ?>
