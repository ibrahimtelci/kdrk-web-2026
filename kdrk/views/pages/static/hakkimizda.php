<!-- HERO -->
<section class="corp-hero" data-screen-label="01 Hero">
  <div class="corp-hero-bg" style="background-image:url('https://images.unsplash.com/photo-1454496522488-7a8e488e8606?w=2400&q=80&auto=format&fit=crop')"></div>
  <div class="corp-hero-overlay"></div>
  <div class="topo-watermark"></div>
  <div class="container">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep">›</span><span class="current">Hakkımızda</span>
    </nav>
    <span class="eyebrow" style="color:#fff">KURUMSAL</span>
    <h1>Dağa, doğaya, ipe saygıyla.</h1>
    <p class="lede">KDRK — Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü. 1998'den bu yana Doğu Karadeniz'in zirvelerinde yürüyen, tırmanan, eğiten ve gerektiğinde kurtaran bir topluluk.</p>
    <div class="corp-hero-stamp"><span class="bar"></span> EST. 1998 · RİZE</div>
  </div>
</section>

<!-- STORY -->
<section class="corp-section" data-screen-label="02 Hikaye">
  <div class="container">
    <div class="story-grid">
      <div class="story-text reveal">
        <span class="eyebrow">Hikayemiz</span>
        <h2 style="font-size:clamp(28px,3.6vw,44px)">Bir grup arkadaşın çayıyla başladı.</h2>
        <p class="drop">1998 yılının sonbaharında, Çamlıhemşin'de bir yayla evinde toplanan sekiz dağcı, bölgenin zirvelerini düzenli ve güvenli biçimde keşfetmek için bir kulüp kurmaya karar verdi. O gün ilk defteri açtık; bugün o defterde binlerce isim var.</p>
        <p>İlk yıllarımızda yalnızca günübirlik yürüyüşler düzenliyorduk. Zamanla dağcılık, spor tırmanış, dağ kayağı ve eğitim programları eklendi. Kuruluşumuzdan bu yana deprem, sel, çığ ve dağda kaybolma vakalarında arama-kurtarma çalışmalarına katılıyoruz; 2025’ten bu yana AFAD akreditasyonu için çalışıyoruz.</p>
        <p><span data-stat="yil"><?= club_stat('yil') ?></span> yıl sonra hâlâ aynı ilkeyle çıkıyoruz dağa: hazırlıklı, saygılı ve birlikte. Zirve hedeftir ama asıl olan, herkesin sağ salim eve dönmesidir.</p>
      </div>
      <div class="story-media reveal">
        <div style="background-image:url('https://images.unsplash.com/photo-1519681393784-d120267933ba?w=1200&q=80&auto=format&fit=crop')"></div>
        <span class="caption">İlk kuruluş kampı · Dilberdüzü · 1998</span>
      </div>
    </div>
  </div>
</section>

<!-- MISYON & VIZYON -->
<section class="corp-section" data-screen-label="03 Misyon Vizyon">
  <div class="container">
    <div class="mv-grid">
      <div class="mv-card reveal">
        <span class="mv-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="4"/><circle cx="12" cy="12" r="0.6" fill="currentColor"/></svg></span>
        <span class="eyebrow">Misyonumuz</span>
        <h2>Dağı güvenle, doğayı bozmadan paylaşmak.</h2>
        <p>Her yaştan ve seviyeden insanı doğayla buluşturmak; dağcılığı disiplinli, güvenli ve erişilebilir kılmak. Bilgiyi, deneyimi ve sorumluluğu paylaşarak Doğu Karadeniz'in dağ kültürünü yaşatmak ve gelecek kuşaklara aktarmak.</p>
      </div>
      <div class="mv-card reveal">
        <span class="mv-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg></span>
        <span class="eyebrow">Vizyonumuz</span>
        <h2>Türkiye'nin en köklü dağ topluluğu olmak.</h2>
        <p>Eğitimde, arama-kurtarmada ve doğa sporlarında ülkenin referans kulüplerinden biri olmak; Kaçkar'ı uluslararası bir dağcılık merkezi haline getirmeye katkı sunmak ve sürdürülebilir dağ turizminin öncüsü olmak.</p>
      </div>
    </div>
  </div>
</section>

<!-- STAT BAND -->
<section class="corp-section alt" data-screen-label="03 Rakamlar">
  <div class="container">
    <div class="corp-head center reveal">
      <span class="eyebrow center">Rakamlarla</span>
      <h2><span data-stat="yil"><?= club_stat('yil') ?></span> yılda biriken iz.</h2>
    </div>
    <div class="stat-band reveal">
      <div class="sb-cell"><div class="sb-num"><span data-stat="zirve" data-odometer="<?= club_stat('zirve') ?>">0</span>+</div><div class="sb-label">Zirve Tırmanışı</div></div>
      <div class="sb-cell"><div class="sb-num"><span data-stat="yuruyus" data-odometer="<?= club_stat('yuruyus') ?>">0</span>+</div><div class="sb-label">Doğa Yürüyüşü</div></div>
      <div class="sb-cell"><div class="sb-num"><span data-stat="aramaKurtarma" data-odometer="<?= club_stat('aramaKurtarma') ?>">0</span>+</div><div class="sb-label">Arama-Kurtarma</div></div>
      <div class="sb-cell"><div class="sb-num"><span data-stat="yil" data-odometer="<?= club_stat('yil') ?>">0</span></div><div class="sb-label">Yıl Tecrübe</div></div>
    </div>
  </div>
</section>

<!-- VALUES -->
<section class="corp-section" data-screen-label="04 Değerler">
  <div class="container">
    <div class="corp-head reveal">
      <span class="eyebrow">Değerlerimiz</span>
      <h2>Neye inanırız?</h2>
      <p>Dağ bize üç şey öğretti: hazırlık, saygı ve dayanışma. Her faaliyetimizin temelinde bu üçü var.</p>
    </div>
    <div class="value-grid">
      <div class="value-card reveal">
        <span class="vicon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>
        <span class="vnum">01</span>
        <h3>Güvenlik Önce</h3>
        <p>Hiçbir zirve, bir hayata değmez. Hava, ekipman ve katılımcı kondisyonu uygun değilse döneriz. Geri dönmek de bir başarıdır.</p>
      </div>
      <div class="value-card reveal">
        <span class="vicon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21l4-1 11-11a2.8 2.8 0 00-4-4L3 16l-1 4z"/><path d="M14 6l4 4"/></svg></span>
        <span class="vnum">02</span>
        <h3>Doğaya Saygı</h3>
        <p>İz bırakmadan yürürüz. Çöpümüzü taşır, patikadan çıkmaz, yaban hayatını rahatsız etmeyiz. Dağ bizim değil, biz dağın misafiriyiz.</p>
      </div>
      <div class="value-card reveal">
        <span class="vicon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg></span>
        <span class="vnum">03</span>
        <h3>Birlikte Güçlüyüz</h3>
        <p>Aynı ipe bağlıyız. Deneyimli, yeni başlayanı bekler; güçlü, yorgunun çantasını taşır. Kimseyi geride bırakmayız.</p>
      </div>
    </div>
  </div>
</section>

<!-- TIMELINE -->
<section class="corp-section alt" data-screen-label="05 Tarihçe">
  <div class="container">
    <div class="corp-head center reveal">
      <span class="eyebrow center">Yolculuk</span>
      <h2>15 yılda neler oldu?</h2>
    </div>
    <div class="corp-timeline">
      <div class="tl-item left reveal">
        <div class="tl-year">1998</div>
        <h4>Kuruluş</h4>
        <p>Sekiz dağcı, Çamlıhemşin'de KDRK'yı kurdu. İlk faaliyet: Pokut–Sal yayla yürüyüşü.</p>
      </div>
      <div class="tl-item right reveal">
        <div class="tl-year">2012</div>
        <h4>İlk Kaçkar Zirvesi</h4>
        <p>Kulüp olarak ilk resmi Kaçkar Dağı (3.937 m) zirve tırmanışımızı tamamladık.</p>
      </div>
      <div class="tl-item left reveal">
        <div class="tl-year">2014</div>
        <h4>Eğitim Programı</h4>
        <p>TDF iş birliğiyle ilk yaz dağcılık eğitim kampımızı düzenledik.</p>
      </div>
      <div class="tl-item right reveal">
        <div class="tl-year">2019</div>
        <h4>Dağ Kayağı &amp; Skitur</h4>
        <p>Kış faaliyetlerini genişlettik; düzenli skitur ve tur kayağı programları başladı.</p>
      </div>
      <div class="tl-item left reveal">
        <div class="tl-year">2025</div>
        <h4>AFAD Akreditasyon Süreci</h4>
        <p>Arama-kurtarma ekibimizin AFAD akreditasyonu için çalışmalara başladık.</p>
      </div>
      <div class="tl-item right reveal">
        <div class="tl-year">2026</div>
        <h4>Bugün</h4><p>Yürüyüş, tırmanış, dağ kayağı, eğitim ve arama-kurtarma faaliyetleri Çamlıhemşin'den sürüyor.</p>
      </div>
    </div>
  </div>
</section>

<!-- JOIN CTA -->
<section class="corp-section" data-screen-label="06 CTA">
  <div class="container">
    <div class="join-band reveal">
      <div class="topo-watermark"></div>
      <span class="eyebrow center" style="color:#fff;justify-content:center">Aramıza Katıl</span>
      <h2>Bir sonraki zirvede seni de görelim.</h2>
      <p>İster ilk yürüyüşün olsun, ister yıllardır dağdasın — KDRK kapısı herkese açık.</p>
      <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
        <a href="<?= e(url('faaliyetler')) ?>" class="btn btn-primary">Faaliyetlere Göz At <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 5l7 7-7 7"/></svg></a>
        <a href="<?= e(url('iletisim')) ?>" class="btn btn-ghost">Bize Ulaş</a>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
