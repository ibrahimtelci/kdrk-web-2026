/* ============================================================
   KDRK Admin — Faaliyet-merkezli veri modeli (v2)
   Tek kaynak: activities. Galeri ve haberler tamamlanan
   faaliyetlerden OTOMATİK türetilir; haberler ayrıca
   düzenlenebilir (newsEdits) ve manuel eklenebilir (newsManual).
   ============================================================ */
window.KDRK = (function(){
  const KEY = 'kdrk-admin-db-v3';
  const U = (id,w)=>`https://images.unsplash.com/${id}?w=${w||600}&q=80&auto=format&fit=crop`;

  const SEED = {
    activities: [
      { id:'a1', title:'Ayder Şelale Yürüyüşü', cat:'gunubirlik', date:'2026-06-08', loc:'Rize / Ayder',
        diff:'easy', quota:20, img:U('photo-1432405972618-c60b0225b8f9'),
        desc:'Aile dostu, orman içi şelale rotası. Yeni başlayanlara açık.',
        status:'plani', applications:[], report:null },

      { id:'a2', title:'Pokut Yaylası Doğa Yürüyüşü', cat:'gunubirlik', date:'2026-05-18', loc:'Rize / Çamlıhemşin',
        diff:'easy', quota:15, img:U('photo-1500534314209-a25ddb2bd429'),
        desc:'Bahar çiçekleri ve sis altın saatte; günübirlik yürüyüş.',
        status:'basvuru', report:null,
        applications:[
          {name:'Ahmet Yıldız', tc:'12345678901', phone:'532 111 22 33', email:'ahmet@mail.com', date:'2026-04-20', status:'approved', emerName:'Ayşe Yıldız', emerPhone:'532 999 88 77', health:'Bilinen rahatsızlık yok.'},
          {name:'Selin Kaya', tc:'23456789012', phone:'533 222 33 44', email:'selin@mail.com', date:'2026-04-21', status:'approved', emerName:'Mert Kaya', emerPhone:'533 888 77 66', health:'Polen alerjisi.'},
          {name:'Murat Demir', tc:'34567890123', phone:'535 333 44 55', email:'murat@mail.com', date:'2026-04-22', status:'pending', emerName:'Elif Demir', emerPhone:'535 777 66 55', health:''},
          {name:'Ece Aksoy', tc:'45678901234', phone:'534 444 55 66', email:'ece@mail.com', date:'2026-04-23', status:'pending', emerName:'Can Aksoy', emerPhone:'534 666 55 44', health:'Astım — yanında ilacı var.'},
        ] },
      { id:'a3', title:'Kaçkar Zirve Tırmanışı', cat:'dagcilik', date:'2026-07-12', loc:'Çamlıhemşin · 3.937 m',
        diff:'very-hard', quota:10, img:U('photo-1486870591958-9b9d0d1dda99'),
        desc:'4 gün, buzul aşması, 35° kar şutları. Yüksek irtifa deneyimi gerekli.',
        status:'basvuru', report:null,
        applications:[
          {name:'Can Polat', tc:'56789012345', phone:'538 666 77 88', email:'can@mail.com', date:'2026-05-01', status:'approved', emerName:'Deniz Polat', emerPhone:'538 111 00 99', health:'Sağlık raporu teslim edildi.'},
          {name:'Deniz Aydın', tc:'67890123456', phone:'539 777 88 99', email:'deniz@mail.com', date:'2026-05-03', status:'pending', emerName:'Su Aydın', emerPhone:'539 222 11 00', health:''},
        ] },

      { id:'a4', title:'Verçenik Tepesi Tırmanışı', cat:'dagcilik', date:'2025-09-08', loc:'Rize / İkizdere · 3.711 m',
        diff:'hard', quota:12, img:U('photo-1551632811-561732d1e306'),
        desc:'Teknik kar ve kaya geçişleri; deneyimli üyelere yönelik klasik rota.',
        status:'tamamlandi',
        applications:[
          {name:'Emre Şahin', tc:'78901234567', phone:'536 444 55 66', email:'emre@mail.com', date:'2025-08-10', status:'approved', emerName:'Naz Şahin', emerPhone:'536 333 22 11', health:''},
          {name:'Zeynep Ak', tc:'89012345678', phone:'537 555 66 77', email:'zeynep@mail.com', date:'2025-08-12', status:'approved', emerName:'Ali Ak', emerPhone:'537 444 33 22', health:''},
        ],
        report:{ held:true, date:'2025-09-08', participants:11, summary:'11 katılımcıyla gerçekleşti. Hava şartları uygun gitti, tüm ekip zirveye ulaşıp güvenle indi. Buzul geçişinde ip ekibi düzeniyle ilerlendi.',
          photos:[U('photo-1551632811-561732d1e306'),U('photo-1454496522488-7a8e488e8606'),U('photo-1486870591958-9b9d0d1dda99'),U('photo-1551632436-cbf8dd35adfa'),U('photo-1519681393784-d120267933ba')],
          videos:['https://www.youtube.com/watch?v=demo1'] } },
      { id:'a5', title:'Ovit Yaylası Skitur Kampı', cat:'dag-kayagi', date:'2025-02-14', loc:'Rize / Ovit',
        diff:'medium', quota:12, img:U('photo-1551524559-8af4e6624178'),
        desc:'İki günlük tur kayağı kampı; bakir karda iniş.',
        status:'tamamlandi',
        applications:[ {name:'Okan Yılmaz', tc:'90123456789', phone:'531 222 11 00', email:'okan@mail.com', date:'2025-01-20', status:'approved', emerName:'Pelin Yılmaz', emerPhone:'531 333 44 55', health:''} ],
        report:{ held:true, date:'2025-02-14', participants:8, summary:'8 kişilik ekiple Ovit sırtlarında iki gün skitur yapıldı. Kar kalitesi mükemmeldi.',
          photos:[U('photo-1551524559-8af4e6624178'),U('photo-1605540436563-5bca919ae766'),U('photo-1502767089025-6572583495c9')],
          videos:[] } },
    ],
    // Manuel eklenen haber/duyurular (faaliyetten bağımsız)
    newsManual: [
      { id:'n-man-1', title:'2026 Üyelik Aidatları Güncellendi', cat:'Duyuru', date:'2026-04-12', status:'published',
        cover:U('photo-1454496522488-7a8e488e8606'), body:'Yeni dönem üyelik aidatları ve ödeme takvimi yayınlandı. Detaylar için kulüp lokaline uğrayabilirsiniz.' },
    ],
    // Otomatik haberlerin düzenlemeleri: { [actId]: {title,body,date,cover,hidden} }
    newsEdits: {},
    // Anasayfa banner / hero slaytları
    banners: [
      { id:'b1', title:"Pokut Yaylası'nda bahar.", text:'Bu cumartesi mor çiçeklerin arasından sisli vadiye kısa bir yürüyüş.', img:'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=1600&q=80&auto=format&fit=crop', link:'activity:a2', active:true, order:1 },
      { id:'b2', title:'Yaz 2026 programı yayında.', text:'12 günübirlik, 4 zirve tırmanışı, 2 eğitim — programın tamamını incele.', img:'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?w=1600&q=80&auto=format&fit=crop', link:'news:n-man-1', active:true, order:2 },
      { id:'b3', title:'Yirmi sekiz yıldır aynı ipe bağlıyız.', text:'1998’den beri Kaçkar’da; 500+ zirve, binlerce katılımcı.', img:'https://images.unsplash.com/photo-1551632811-561732d1e306?w=1600&q=80&auto=format&fit=crop', link:'url:Hakkimizda.html', active:true, order:3 },
    ],
    messages: [
      { id:'m1', name:'Burcu Eren', email:'burcu@mail.com', subject:'Üyelik hakkında', body:'Merhaba, kulübe üye olmak istiyorum. Süreç nasıl işliyor? Hiç deneyimim yok, başlangıç için uygun mu?', date:'2026-05-10 14:32', read:false, topic:'uyelik' },
      { id:'m2', name:'Kerem Acar', email:'kerem@mail.com', subject:'Kaçkar tırmanışı', body:'12 Temmuz Kaçkar zirve tırmanışına katılmak istiyorum ama daha önce sadece günübirlik yürüyüş yaptım. Uygun olur mu?', date:'2026-05-09 09:15', read:false, topic:'faaliyet' },
      { id:'m3', name:'Aslı Tunç', email:'asli@mail.com', subject:'Sponsorluk teklifi', body:'Outdoor markamız adına işbirliği görüşmek isteriz. Detayları konuşabilir miyiz?', date:'2026-05-07 16:40', read:true, topic:'isbirligi' },
    ],
    users: [
      { id:'u1', name:'Murat Kayhan', email:'murat@kdrk.org.tr', role:'admin', active:true,
        perms:{ dashboard:true, activities:true, gallery:true, news:true, banner:true, messages:true, users:true } },
      { id:'u2', name:'Elif Şahin', email:'elif@kdrk.org.tr', role:'editor', active:true,
        perms:{ dashboard:true, activities:true, gallery:true, news:true, banner:true, messages:true, users:false } },
      { id:'u3', name:'Nazlı Aydın', email:'nazli@kdrk.org.tr', role:'viewer', active:true,
        perms:{ dashboard:true, activities:true, gallery:true, news:true, banner:true, messages:false, users:false } },
    ],
  };

  let db;
  function load(){ try{ db=JSON.parse(localStorage.getItem(KEY)); }catch(e){ db=null; } if(!db){ db=JSON.parse(JSON.stringify(SEED)); save(); }
    if(!db.newsManual) db.newsManual=[]; if(!db.newsEdits) db.newsEdits={}; if(!db.banners) db.banners=[]; return db; }
  function save(){ try{ localStorage.setItem(KEY, JSON.stringify(db)); }catch(e){ console.warn('Kayıt limiti:', e); } }
  function reset(){ db=JSON.parse(JSON.stringify(SEED)); save(); return db; }
  function uid(){ return 'x'+Math.random().toString(36).slice(2,9); }

  function galleries(){
    return db.activities.filter(a=>a.status==='tamamlandi' && a.report && a.report.photos && a.report.photos.length)
      .map(a=>({ id:'g-'+a.id, actId:a.id, title:a.title, cat:a.cat, year:+String(a.report.date||a.date).slice(0,4),
        date:(a.report.date||a.date), cover:a.report.photos[0], photos:a.report.photos, videos:a.report.videos||[] }))
      .sort((x,y)=>(y.date||'').localeCompare(x.date||''));
  }
  // Otomatik + manuel + düzenlenmiş haberler birleşik
  function newsList(){
    const auto = db.activities.filter(a=>a.status==='tamamlandi' && a.report).map(a=>{
      const base={ id:'n-'+a.id, actId:a.id, source:'auto', cat:'Faaliyet Raporu',
        title:a.title+' tamamlandı', date:(a.report.date||a.date), body:a.report.summary,
        cover:(a.report.photos&&a.report.photos[0])||a.img, status:'published' };
      const e=db.newsEdits[a.id]; if(e){ if(e.hidden) return null; return Object.assign(base, e, {id:base.id, actId:a.id, source:'auto'}); }
      return base;
    }).filter(Boolean);
    const manual = (db.newsManual||[]).map(n=>Object.assign({source:'manual'}, n));
    return auto.concat(manual).sort((x,y)=>(y.date||'').localeCompare(x.date||''));
  }
  function related(act){
    return db.activities.filter(a=>a.id!==act.id && a.cat===act.cat && a.status==='tamamlandi' && a.report)
      .sort((x,y)=>(y.report.date||'').localeCompare(x.report.date||''));
  }

  load();
  return { get db(){return db;}, save, reset, uid, galleries, newsList, related };
})();
