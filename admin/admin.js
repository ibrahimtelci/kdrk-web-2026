/* ============================================================
   KDRK Admin — Faaliyet-merkezli uygulama (v2.1)
   ============================================================ */
(function(){
  const DB = window.KDRK;
  const $ = (s,r=document)=>r.querySelector(s);
  const $$ = (s,r=document)=>[...r.querySelectorAll(s)];
  const esc = s => String(s==null?'':s).replace(/[&<>"]/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));

  const ICON = {
    dashboard:'<rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/>',
    activities:'<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    gallery:'<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/>',
    news:'<path d="M4 22h16a2 2 0 002-2V4a2 2 0 00-2-2H8a2 2 0 00-2 2v16a2 2 0 01-2 2zm0 0a2 2 0 01-2-2v-9h4"/><path d="M18 14h-8M15 18h-5M10 6h8v4h-8z"/>',
    messages:'<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>',
    users:'<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 11l-3-3-3 3M19 8v8"/>',
    plus:'<path d="M12 5v14M5 12h14"/>', edit:'<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.1 2.1 0 013 3L12 15l-4 1 1-4z"/>',
    trash:'<path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>',
    eye:'<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>',
    search:'<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>', check:'<path d="M5 12l5 5L20 7"/>',
    x:'<path d="M18 6L6 18M6 6l12 12"/>', sun:'<circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>',
    moon:'<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>', logout:'<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>',
    ext:'<path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><path d="M15 3h6v6M10 14L21 3"/>',
    menu:'<path d="M3 6h18M3 12h18M3 18h18"/>', img:'<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/>',
    mail:'<rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 6L2 7"/>', reply:'<path d="M9 17l-5-5 5-5M4 12h11a4 4 0 014 4v2"/>',
    download:'<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>', video:'<path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2"/>',
    link:'<path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/>',
    spark:'<path d="M12 2l1.6 6.4L20 10l-6.4 1.6L12 18l-1.6-6.4L4 10l6.4-1.6z"/>',
    grid:'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
    list:'<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
    up:'<path d="M12 19V5M5 12l7-7 7 7"/>', upload:'<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
    banner:'<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 5l10 7L22 5"/><path d="M2 19l6-6M22 19l-6-6"/>',
    arrowup:'<path d="M18 15l-6-6-6 6"/>', arrowdown:'<path d="M6 9l6 6 6-6"/>',
    cal:'<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>', pin:'<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>'
  };
  const svg=(p,w=18)=>`<svg viewBox="0 0 24 24" width="${w}" height="${w}" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">${p}</svg>`;

  const MODULES=[
    {id:'dashboard',label:'Dashboard',group:'Genel'},
    {id:'activities',label:'Faaliyetler',group:'İçerik'},
    {id:'gallery',label:'Galeri',group:'Otomatik'},
    {id:'news',label:'Haberler & Duyurular',group:'Otomatik'},
    {id:'banner',label:'Anasayfa Banner',group:'İçerik'},
    {id:'messages',label:'İletişim Mesajları',group:'İletişim'},
    {id:'users',label:'Kullanıcı & Yetki',group:'Sistem'},
    {id:'settings',label:'Genel Ayarlar',group:'Sistem'},
  ];
  const ROLE_LABEL={admin:'Admin',editor:'Editör',viewer:'İzleme'};
  const CAT_LABEL={gunubirlik:'Günübirlik',dagcilik:'Dağcılık','spor-tirmanis':'Spor Tırmanış','dag-kayagi':'Dağ Kayağı',egitim:'Eğitim',sosyal:'Sosyal','arama-kurtarma':'Arama Kurtarma'};
  const DIFF={easy:['Kolay','green'],medium:['Orta','orange'],'medium-hard':['Orta-Zor','orange'],hard:['Zor','red'],'very-hard':['Çok Zor','red'],education:['Eğitim','blue']};
  const LIFE={plani:['Planlandı','gray'],basvuru:['Başvuru Açık','green'],tamamlandi:['Tamamlandı','blue']};
  const APPSTAT={approved:['Onaylı','green'],pending:['Bekliyor','orange'],waitlist:['Yedek','blue'],rejected:['Red','red']};
  const NEWSCAT=['Duyuru','Haber','Faaliyet Raporu'];
  const MAXW=1280; // foto stabil çözünürlük

  let session=null, current='dashboard';
  const chip=a=>a?`<span class="chip ${a[1]} dot">${a[0]}</span>`:'';
  const readonly=()=>session.user.role==='viewer';
  const can=m=>session&&(m==='settings'?session.user.role==='admin':session.user.perms[m]);
  const fmtDate=d=>d||'';

  /* theme */
  function initTheme(){ let t='dark'; try{t=localStorage.getItem('kdrk-admin-theme')||'dark';}catch(e){} document.documentElement.setAttribute('data-theme',t); }
  function toggleTheme(){ const t=document.documentElement.getAttribute('data-theme')==='dark'?'light':'dark'; document.documentElement.setAttribute('data-theme',t); try{localStorage.setItem('kdrk-admin-theme',t);}catch(e){} syncTheme(); }
  function syncTheme(){ const d=document.documentElement.getAttribute('data-theme')==='dark'; $$('[data-theme-btn]').forEach(b=>b.innerHTML=svg(d?ICON.sun:ICON.moon)); }

  /* toast */
  let tT; function toast(m){ let e=$('.toast'); if(!e){e=document.createElement('div');e.className='toast';document.body.appendChild(e);} e.innerHTML=svg(ICON.check)+'<span>'+esc(m)+'</span>'; e.classList.add('show'); clearTimeout(tT); tT=setTimeout(()=>e.classList.remove('show'),2200); }

  /* side drawer (users, messages) */
  function openDrawer(title,body,foot){
    let sc=$('.scrim'),dp=$('.drawer-panel');
    if(!sc){sc=document.createElement('div');sc.className='scrim';document.body.appendChild(sc);sc.addEventListener('click',closeDrawer);}
    if(!dp){dp=document.createElement('div');dp.className='drawer-panel';document.body.appendChild(dp);}
    dp.innerHTML=`<div class="dp-head"><h3>${esc(title)}</h3><button class="dp-close" data-close>${svg(ICON.x)}</button></div><div class="dp-body">${body}</div>${foot?`<div class="dp-foot">${foot}</div>`:''}`;
    $('[data-close]',dp).addEventListener('click',closeDrawer);
    requestAnimationFrame(()=>{sc.classList.add('open');dp.classList.add('open');});
    return dp;
  }
  function closeDrawer(){ $('.scrim')?.classList.remove('open'); $('.drawer-panel')?.classList.remove('open'); }

  /* centered wide modal (faaliyet detay) */
  function openModal(title,body,foot){
    let sc=$('.modal-scrim');
    if(!sc){sc=document.createElement('div');sc.className='modal-scrim';document.body.appendChild(sc);sc.addEventListener('click',e=>{if(e.target===sc)closeModal();});}
    sc.innerHTML=`<div class="modal-center"><div class="dp-head"><h3>${esc(title)}</h3><button class="dp-close" data-close>${svg(ICON.x)}</button></div><div class="dp-body">${body}</div>${foot?`<div class="dp-foot">${foot}</div>`:''}`;
    $('[data-close]',sc).addEventListener('click',closeModal);
    requestAnimationFrame(()=>sc.classList.add('open'));
    return sc.querySelector('.modal-center');
  }
  function closeModal(){ $('.modal-scrim')?.classList.remove('open'); }

  /* auth */
  function login(id){ session={user:DB.db.users.find(u=>u.id===id)||DB.db.users[0]}; try{localStorage.setItem('kdrk-admin-session',session.user.id);}catch(e){} renderShell(); }
  function logout(){ session=null; try{localStorage.removeItem('kdrk-admin-session');}catch(e){} renderLogin(); }
  function restore(){ let id; try{id=localStorage.getItem('kdrk-admin-session');}catch(e){} const u=id&&DB.db.users.find(x=>x.id===id); if(u){session={user:u};renderShell();}else renderLogin(); }

  Object.assign(ICON,{grid:'<rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/>',rows:'<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',print:'<path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',bus:'<rect x="3" y="4" width="18" height="13" rx="2"/><path d="M3 11h18M7 17v3M17 17v3"/><circle cx="7.5" cy="14" r=".5" fill="currentColor"/><circle cx="16.5" cy="14" r=".5" fill="currentColor"/>',clip:'<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 3v2h6V3M9 11h6M9 15h4"/>'});

  /* LOGIN */
  function renderLogin(){
    $('#app').classList.remove('is-auth'); const l=$('#login'); l.style.display='';
    l.innerHTML=`<div class="login-topo"></div><div class="login-card">
      <div class="login-brand"><span class="login-mark"><img src="../assets/kdrk-logo-2026.png" alt="KDRK"/></span><div><div class="lb-name">KDRK</div><div class="lb-sub">Yönetim Paneli</div></div></div>
      <h1>Giriş Yap</h1><p class="login-hint">Hesabınla giriş yap.</p>
      <div class="field"><label>E-posta</label><input id="lg-email" value="murat@kdrk.org.tr"/></div>
      <div class="field"><label>Parola</label><input id="lg-pass" type="password" value="parola"/></div>
      <button class="btn btn-primary" style="width:100%;height:46px" id="lg-go">Giriş Yap</button>
      <div class="login-demo"><b>Demo rolleri</b><div class="flex" style="margin-top:8px;flex-wrap:wrap;gap:6px"><button class="btn btn-ghost btn-sm" data-demo="u1">Admin</button><button class="btn btn-ghost btn-sm" data-demo="u2">Editör</button><button class="btn btn-ghost btn-sm" data-demo="u3">İzleme</button></div></div>
    </div>`;
    $('#lg-go').addEventListener('click',()=>login('u1'));
    $('#lg-pass').addEventListener('keydown',e=>{if(e.key==='Enter')login('u1');});
    $$('[data-demo]').forEach(b=>b.addEventListener('click',()=>login(b.dataset.demo)));
  }

  /* SHELL */
  function renderShell(){
    $('#login').style.display='none'; const app=$('#app'); app.classList.add('is-auth'); const u=session.user;
    const groups={}; MODULES.filter(m=>can(m.id)).forEach(m=>{(groups[m.group]=groups[m.group]||[]).push(m);});
    const nav=Object.entries(groups).map(([g,ms])=>`<div class="sb-section-label">${g}</div>${ms.map(m=>`<button class="sb-link" data-nav="${m.id}">${svg(ICON[m.id])}<span>${m.label}</span>${badge(m.id)}</button>`).join('')}`).join('');
    app.innerHTML=`<aside class="sidebar" data-sidebar>
        <div class="sb-brand"><span class="sb-mark"><img src="../assets/kdrk-logo-2026.png" alt="KDRK"/></span><div><div class="sb-name">KDRK</div><div class="sb-tag">Yönetim Paneli</div></div></div>
        <nav class="sb-nav">${nav}</nav>
        <div class="sb-foot"><span class="sb-avatar">${esc(u.name[0])}</span><div class="sb-user"><div class="su-name">${esc(u.name)}</div><div class="su-role">${ROLE_LABEL[u.role]}</div></div><button class="sb-logout" data-logout title="Çıkış">${svg(ICON.logout)}</button></div>
      </aside>
      <div class="main"><header class="topbar"><button class="tb-btn menu-toggle" data-menu>${svg(ICON.menu)}</button><div class="tb-title" data-title>Dashboard</div><div class="tb-spacer"></div><a class="tb-view-site" href="../Anasayfa.html?onizleme=1" target="_blank" title="Bakım modu açıkken de siteyi önizler">${svg(ICON.ext)}<span>Siteyi Gör</span></a><button class="tb-btn" data-theme-btn></button></header><div class="content"><div id="view"></div></div></div>`;
    $$('[data-nav]').forEach(b=>b.addEventListener('click',()=>{go(b.dataset.nav); if(innerWidth<=860)$('[data-sidebar]').classList.remove('open');}));
    $('[data-logout]').addEventListener('click',logout);
    $('[data-theme-btn]').addEventListener('click',toggleTheme);
    $('[data-menu]').addEventListener('click',()=>$('[data-sidebar]').classList.toggle('open'));
    syncTheme();
    const first=MODULES.find(m=>can(m.id));
    go(can(current)?current:(first?first.id:'dashboard'));
  }
  function badge(id){
    if(id==='messages'){const n=DB.db.messages.filter(m=>!m.read).length;return n?`<span class="sb-badge">${n}</span>`:'';}
    if(id==='activities'){let n=0;DB.db.activities.forEach(a=>n+=(a.applications||[]).filter(x=>x.status==='pending').length);return n?`<span class="sb-badge">${n}</span>`:'';}
    return '';
  }
  function refreshBadges(){ $$('[data-nav]').forEach(b=>{const ex=b.querySelector('.sb-badge');if(ex)ex.remove();const h=badge(b.dataset.nav);if(h)b.insertAdjacentHTML('beforeend',h);}); }
  function go(v){ if(!can(v))return; current=v; $$('[data-nav]').forEach(b=>b.classList.toggle('is-active',b.dataset.nav===v)); $('[data-title]').textContent=(MODULES.find(m=>m.id===v)||{}).label||''; $('#view').innerHTML=`<div class="view is-active">${RENDER[v]?RENDER[v]():''}</div>`; BIND[v]&&BIND[v](); }

  const RENDER={}, BIND={};

  /* ----- Dashboard ----- */
  RENDER.dashboard=function(){
    const db=DB.db;
    const open=db.activities.filter(a=>a.status==='basvuru').length;
    const pend=db.activities.reduce((n,a)=>n+(a.applications||[]).filter(x=>x.status==='pending').length,0);
    const done=db.activities.filter(a=>a.status==='tamamlandi').length;
    const unread=db.messages.filter(m=>!m.read).length;
    const card=(ic,cls,num,label,goto)=>`<div class="stat-card" ${goto?`data-goto="${goto}" style="cursor:pointer"`:''}><div class="sc-top"><span class="sc-icon ${cls}">${svg(ic)}</span></div><div class="sc-num">${num}</div><div class="sc-label">${label}</div></div>`;
    const recent=[]; db.activities.forEach(a=>(a.applications||[]).forEach(p=>recent.push({...p,act:a.title}))); recent.sort((a,b)=>(b.date||'').localeCompare(a.date||''));
    return `<div class="stat-cards">
        ${card(ICON.activities,'a',open,'Başvuru Açık Faaliyet','activities')}
        ${card(ICON.users,'c',pend,'Bekleyen Başvuru','activities')}
        ${card(ICON.gallery,'b',done,'Tamamlanan Faaliyet','gallery')}
        ${card(ICON.messages,'d',unread,'Okunmamış Mesaj','messages')}
      </div>
      <div class="panel" style="margin-bottom:16px"><div class="panel-body" style="display:flex;gap:14px;align-items:center"><span class="sc-icon a">${svg(ICON.spark)}</span><div><div style="font-weight:700">Faaliyet-merkezli akış</div><div class="muted" style="font-size:13px">Faaliyet oluştur → başvuru al → tamamlandı işaretle + foto/video gir. <b>Galeri ve haber otomatik oluşur.</b></div></div><div class="tb-spacer"></div>${readonly()?'':`<button class="btn btn-primary" data-new-act>${svg(ICON.plus)}Yeni Faaliyet</button>`}</div></div>
      <div class="dash-grid">
        <div class="panel"><div class="panel-head"><h3>Son Başvurular</h3><button class="btn btn-ghost btn-sm" data-goto="activities">Faaliyetler</button></div>
          <table class="tbl"><thead><tr><th>Ad Soyad</th><th>Faaliyet</th><th>Durum</th><th>Tarih</th></tr></thead><tbody>
          ${recent.slice(0,5).map(p=>`<tr><td class="t-title">${esc(p.name)}</td><td class="muted">${esc(p.act)}</td><td>${chip(APPSTAT[p.status])}</td><td class="mono muted">${esc(p.date)}</td></tr>`).join('')||'<tr><td colspan="4" class="muted">Kayıt yok</td></tr>'}</tbody></table></div>
        <div class="panel"><div class="panel-head"><h3>Okunmamış Mesajlar</h3><button class="btn btn-ghost btn-sm" data-goto="messages">Tümü</button></div>
          <div>${db.messages.filter(m=>!m.read).slice(0,4).map(m=>`<div class="msg-item" style="cursor:default"><span class="mi-ava">${esc(m.name[0])}</span><div><div class="mi-name">${esc(m.name)}</div><div class="mi-preview">${esc(m.subject)}</div></div><div class="mi-meta">${esc(m.date.split(' ')[0])}</div></div>`).join('')||'<div class="empty" style="padding:30px"><p>Tüm mesajlar okundu 🎉</p></div>'}</div></div>
      </div>`;
  };
  BIND.dashboard=()=>{ $$('[data-goto]').forEach(b=>b.addEventListener('click',()=>go(b.dataset.goto))); $('[data-new-act]')&&$('[data-new-act]').addEventListener('click',()=>{go('activities');setTimeout(()=>actModal(null),50);}); };

  /* ----- Faaliyetler ----- */
  let actFilter='all',actView=localStorage.getItem('kdrk-admin-actview')||'list';
  RENDER.activities=function(){
    return `<div class="toolbar">
        <div class="search-box">${svg(ICON.search)}<input data-search placeholder="Faaliyet ara..."/></div>
        <div class="seg" data-seg>${[['all','Tümü'],['plani','Planlandı'],['basvuru','Başvuru Açık'],['tamamlandi','Tamamlandı']].map(([f,l])=>`<button data-f="${f}" class="${actFilter===f?'is-active':''}">${l}</button>`).join('')}</div>
        <div class="tb-spacer"></div><div class="seg view-seg" data-vseg role="group" aria-label="Görünüm"><button data-v="list" class="${actView==='list'?'is-active':''}" aria-label="Liste" title="Liste">${svg(ICON.rows,16)}</button><button data-v="card" class="${actView==='card'?'is-active':''}" aria-label="Kart" title="Kart">${svg(ICON.grid,16)}</button></div>${readonly()?'':`<button class="btn btn-primary" data-add>${svg(ICON.plus)}Yeni Faaliyet</button>`}
      </div>
      <div class="panel" data-list-wrap><table class="tbl"><thead><tr><th>Faaliyet</th><th>Kategori</th><th>Tarih</th><th>Aşama</th><th>Başvuru</th><th>Çıktı</th><th></th></tr></thead><tbody data-rows></tbody></table></div><div class="card-grid act-cards" data-cards></div>`;
  };
  BIND.activities=function(){
    const draw=()=>{
      const term=($('[data-search]').value||'').toLowerCase();
      const rows=DB.db.activities.filter(a=>(actFilter==='all'||a.status===actFilter)&&a.title.toLowerCase().includes(term)).sort((x,y)=>(y.date||'').localeCompare(x.date||''));
      $('[data-rows]').innerHTML=rows.map(a=>{
        const pend=(a.applications||[]).filter(x=>x.status==='pending').length;
        const out=a.status==='tamamlandi'?`<span class="chip green dot">Galeri</span> <span class="chip blue dot">Haber</span>`:'<span class="muted">—</span>';
        return `<tr data-row="${a.id}" style="cursor:pointer"><td><div class="cell-main"><img class="t-thumb" src="${a.img}"/><div><div class="t-title">${esc(a.title)}</div><div class="t-sub">${esc(a.loc)} · ${chip(DIFF[a.diff])}</div></div></div></td>
        <td class="muted">${CAT_LABEL[a.cat]||a.cat}</td><td class="mono muted">${esc(a.date)}</td><td>${chip(LIFE[a.status])}</td>
        <td>${(a.applications||[]).length}${pend?` <span class="chip orange" style="margin-left:4px">${pend} yeni</span>`:''}</td><td>${out}</td>
        <td class="t-actions"><button class="btn-icon" data-view="${a.id}">${svg(ICON.eye)}</button>${readonly()?'':`<button class="btn-icon del" data-del="${a.id}">${svg(ICON.trash)}</button>`}</td></tr>`;
      }).join('')||`<tr><td colspan="7"><div class="empty">${svg(ICON.activities,40)}<h4>Faaliyet yok</h4><p>Bu aşamada faaliyet bulunamadı.</p></div></td></tr>`;
      $('[data-list-wrap]').style.display=actView==='list'?'':'none';
      const cw=$('[data-cards]');cw.style.display=actView==='card'?'':'none';
      cw.innerHTML=rows.map(a=>{const n=(a.applications||[]).length,ok=(a.applications||[]).filter(x=>x.status==='approved').length,pend=(a.applications||[]).filter(x=>x.status==='pending').length,pct=a.quota?Math.min(100,Math.round(ok/a.quota*100)):0;
        return `<div class="g-card act-card" data-row="${a.id}" tabindex="0"><div class="g-cover" style="background-image:url('${a.img}')"><span class="ac-date mono">${esc(a.date||'—')}</span>${pend?`<span class="ac-new">${pend} yeni</span>`:''}</div><div class="g-body"><div class="ac-chips">${chip(LIFE[a.status])}${chip(DIFF[a.diff])}</div><div class="g-title">${esc(a.title)}</div><div class="muted" style="font-size:13px">${esc(a.loc||'')} · ${CAT_LABEL[a.cat]||a.cat}</div><div class="ac-quota"><div class="ac-bar"><span style="width:${pct}%"></span></div><span class="mono">${ok}/${a.quota||'—'} onaylı · ${n} başvuru</span></div></div></div>`;}).join('')||`<div class="empty" style="grid-column:1/-1">${svg(ICON.activities,40)}<h4>Faaliyet yok</h4><p>Bu aşamada faaliyet bulunamadı.</p></div>`;
      $$('[data-row]').forEach(r=>{r.addEventListener('click',e=>{ if(e.target.closest('[data-del]'))return; actModal(r.dataset.row); });r.addEventListener('keydown',e=>{if(e.key==='Enter')actModal(r.dataset.row)});});
      $$('[data-del]').forEach(b=>b.addEventListener('click',e=>{e.stopPropagation(); if(confirm('Bu faaliyet silinsin mi? İlişkili otomatik galeri ve haber de kalkar.')){ DB.db.activities=DB.db.activities.filter(x=>x.id!==b.dataset.del); DB.save(); draw(); refreshBadges(); toast('Faaliyet silindi'); }}));
    };
    $('[data-search]').addEventListener('input',draw);
    $$('[data-seg] button').forEach(b=>b.addEventListener('click',()=>{actFilter=b.dataset.f;$$('[data-seg] button').forEach(x=>x.classList.toggle('is-active',x===b));draw();}));
    $$('[data-vseg] button').forEach(b=>b.addEventListener('click',()=>{actView=b.dataset.v;localStorage.setItem('kdrk-admin-actview',actView);$$('[data-vseg] button').forEach(x=>x.classList.toggle('is-active',x===b));draw();}));
    $('[data-add]')&&$('[data-add]').addEventListener('click',()=>actModal(null));
    draw();
  };

  /* image resize helper */
  function processImage(file){
    return new Promise(res=>{
      const r=new FileReader();
      r.onload=()=>{ const img=new Image(); img.onload=()=>{ const sc=Math.min(1,MAXW/img.width); const w=Math.round(img.width*sc),h=Math.round(img.height*sc); const c=document.createElement('canvas'); c.width=w;c.height=h; c.getContext('2d').drawImage(img,0,0,w,h); res(c.toDataURL('image/jpeg',0.82)); }; img.onerror=()=>res(null); img.src=r.result; };
      r.onerror=()=>res(null); r.readAsDataURL(file);
    });
  }
  function videoKind(u){ if(/youtu\.?be/i.test(u))return['yt','YouTube']; if(/drive\.google/i.test(u))return['drive','Drive']; if(/vimeo/i.test(u))return['drive','Vimeo']; return['drive','Video']; }

  const TRANS={kulup:'Kulüp aracı',kendi:'Kendi aracı'};
  const PICKUPS=['Rize Otogar','Pazar Merkez','Ardeşen Merkez','Çamlıhemşin Merkez','Ayder'];
  if(!document.getElementById('pickup-list')){const dl=document.createElement('datalist');dl.id='pickup-list';dl.innerHTML=PICKUPS.map(p=>'<option value="'+p+'">').join('');document.body.appendChild(dl);}
  function printList(kind,act,apps){
    const ok=apps.filter(x=>x.status==='approved');
    if(!ok.length){toast('Onaylı katılımcı yok');return;}
    const tr=d=>{if(!d)return'—';const [y,m,g]=d.split('-');return g+'.'+m+'.'+y;};
    const now=new Date().toLocaleString('tr-TR',{dateStyle:'short',timeStyle:'short'});
    let title,meta,body;
    if(kind==='katilim'){
      title='Katılım Listesi';
      body='<table><thead><tr><th class="n">#</th><th>Ad Soyad</th><th>TC Kimlik No</th><th>Telefon</th><th>Acil Durum Kişisi</th><th>Sağlık Notu</th><th class="sig">İmza</th></tr></thead><tbody>'+ok.map((p,i)=>'<tr><td class="n">'+(i+1)+'</td><td><b>'+esc(p.name)+'</b></td><td class="m">'+esc(p.tc||'')+'</td><td class="m">'+esc(p.phone||'')+'</td><td>'+esc(p.emerName||'')+(p.emerPhone?'<br><span class="m">'+esc(p.emerPhone)+'</span>':'')+'</td><td>'+esc(p.health||'')+'</td><td class="sig"></td></tr>').join('')+'</tbody></table>';
      meta='<div class="sum"><span>Onaylı katılımcı: <b>'+ok.length+'</b></span><span>Sağlık notu olan: <b>'+ok.filter(p=>p.health).length+'</b></span></div>';
    }else{
      title='Taşımacılık Listesi';
      const riders=ok.filter(p=>p.transport!=='kendi'),own=ok.filter(p=>p.transport==='kendi');
      const groups={};riders.forEach(p=>{const k=p.pickup||'Biniş noktası belirtilmedi';(groups[k]=groups[k]||[]).push(p)});
      let n=0;
      body='<div class="fill"><div><span>Araç plakası</span><i></i></div><div><span>Sürücü</span><i></i></div><div><span>Sürücü telefonu</span><i></i></div><div><span>Kalkış saati</span><i></i></div></div>'+
      '<table><thead><tr><th class="n">#</th><th>Ad Soyad</th><th>TC Kimlik No</th><th>Telefon</th><th>Acil Durum Telefonu</th><th class="chk">Bindi</th><th class="chk">Döndü</th></tr></thead><tbody>'+
      Object.keys(groups).map(k=>'<tr class="grp"><td colspan="7">'+esc(k)+' · '+groups[k].length+' kişi</td></tr>'+groups[k].map(p=>'<tr><td class="n">'+(++n)+'</td><td><b>'+esc(p.name)+'</b></td><td class="m">'+esc(p.tc||'')+'</td><td class="m">'+esc(p.phone||'')+'</td><td class="m">'+esc(p.emerPhone||'')+'</td><td class="chk"><span class="box"></span></td><td class="chk"><span class="box"></span></td></tr>').join('')).join('')+'</tbody></table>'+
      (own.length?'<p class="note">Kendi aracıyla katılan ('+own.length+'): '+own.map(p=>esc(p.name)).join(', ')+'</p>':'');
      meta='<div class="sum"><span>Kulüp aracı: <b>'+riders.length+'</b> yolcu</span><span>Kendi aracı: <b>'+own.length+'</b></span><span>Biniş noktası: <b>'+Object.keys(groups).length+'</b></span></div>';
    }
    const html='<!doctype html><html lang="tr"><head><meta charset="utf-8"><title>'+esc(title+' · '+act.title)+'</title><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;600&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"><style>@page{size:A4 '+(kind==='katilim'?'landscape':'portrait')+';margin:14mm}*{box-sizing:border-box}body{font-family:Inter,sans-serif;font-size:10.5pt;color:#0B1220;margin:0}header{display:flex;align-items:center;gap:14px;border-bottom:2px solid #0B1220;padding-bottom:10px;margin-bottom:12px}header img{width:54px;height:54px}header .t{flex:1}h1{font-family:Manrope,sans-serif;font-weight:800;font-size:18pt;letter-spacing:-0.02em;margin:0}.club{font-family:"JetBrains Mono",monospace;font-size:8pt;letter-spacing:.14em;text-transform:uppercase;color:#475569}.act{display:grid;grid-template-columns:2fr 1fr 1.4fr 1fr;gap:0;border:1px solid #CBD5E1;border-radius:6px;margin-bottom:10px}.act div{padding:7px 10px;border-right:1px solid #CBD5E1}.act div:last-child{border-right:0}.act span{display:block;font-family:"JetBrains Mono",monospace;font-size:7.5pt;letter-spacing:.12em;text-transform:uppercase;color:#475569}.act b{font-size:11pt}.sum{display:flex;gap:20px;font-size:9.5pt;margin-bottom:10px}.fill{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px}.fill span{font-family:"JetBrains Mono",monospace;font-size:7.5pt;letter-spacing:.12em;text-transform:uppercase;color:#475569}.fill i{display:block;border-bottom:1px solid #0B1220;height:22px}table{width:100%;border-collapse:collapse}th{font-family:"JetBrains Mono",monospace;font-size:7.5pt;letter-spacing:.1em;text-transform:uppercase;text-align:left;color:#475569;border-bottom:1.5px solid #0B1220;padding:6px}td{padding:7px 6px;border-bottom:1px solid #E2E8F0;vertical-align:top}tr{page-break-inside:avoid}.n{width:26px;color:#475569}.m{font-family:"JetBrains Mono",monospace;font-size:9pt}.sig{width:110px}.chk{width:52px;text-align:center}.box{display:inline-block;width:14px;height:14px;border:1.5px solid #0B1220;border-radius:3px}.grp td{background:#ECEFF5;font-weight:600;font-size:9.5pt;border-bottom:1px solid #CBD5E1}.note{font-size:9.5pt;margin-top:10px}footer{margin-top:18px;display:flex;justify-content:space-between;gap:20px;font-size:9pt}footer .s{flex:1;max-width:220px}footer .s i{display:block;border-bottom:1px solid #0B1220;height:30px;margin-bottom:4px}footer .s span{font-family:"JetBrains Mono",monospace;font-size:7.5pt;letter-spacing:.12em;text-transform:uppercase;color:#475569}.gen{font-family:"JetBrains Mono",monospace;font-size:7.5pt;color:#475569;margin-top:12px}</style></head><body>'+
      '<header><img src="'+new URL('../assets/kdrk-logo-2026.png',location.href).href+'" alt=""><div class="t"><div class="club">Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü</div><h1>'+title+'</h1></div></header>'+
      '<div class="act"><div><span>Faaliyet</span><b>'+esc(act.title)+'</b></div><div><span>Tarih</span><b>'+tr(act.date)+'</b></div><div><span>Yer</span><b>'+esc(act.loc||'—')+'</b></div><div><span>Kategori</span><b>'+esc(act.cat||'—')+'</b></div></div>'+meta+body+
      '<footer><div class="s"><i></i><span>Faaliyet Lideri</span></div><div class="s"><i></i><span>Kulüp Yetkilisi</span></div></footer><div class="gen">KDRK Yönetim Paneli · '+now+'</div></body></html>';
    let w=null;try{w=window.open('','_blank');}catch(e){}
    if(w){w.document.open();w.document.write(html);w.document.close();w.onload=()=>{w.focus();w.print();};setTimeout(()=>{try{w.print()}catch(e){}},900);return;}
    const f=document.createElement('iframe');f.style.cssText='position:fixed;right:0;bottom:0;width:0;height:0;border:0';document.body.appendChild(f);
    f.contentDocument.open();f.contentDocument.write(html);f.contentDocument.close();
    setTimeout(()=>{f.contentWindow.focus();f.contentWindow.print();setTimeout(()=>f.remove(),2000)},700);
  }

  function actModal(id){
    const isNew=!id;
    const a=id?DB.db.activities.find(x=>x.id===id):{title:'',cat:'gunubirlik',date:'',loc:'',diff:'easy',quota:15,img:'',desc:'',status:'plani',applications:[],report:null};
    const apps=(a.applications||[]).map(x=>Object.assign({},x));
    const rel=DB.related(a);
    const approvedCount=()=>apps.filter(x=>x.status==='approved').length;
    const r=a.report||{held:false,date:a.date,participants:'',summary:'',photos:[],videos:[]};
    let work={ held:r.held, date:r.date, participants:r.participants, summary:r.summary, photos:(r.photos||[]).slice(), videos:(r.videos||[]).slice() };
    let pending=[]; // staged photo dataURLs before upload

    const tabs=`<div class="tabs" data-tabs>
        <button class="is-active" data-tab="info">Bilgiler</button>
        <button data-tab="apps">Başvurular${apps.length?` <span class="chip gray" style="margin-left:4px">${apps.length}</span>`:''}</button>
        <button data-tab="report">Sonuç & Rapor</button>
        <button data-tab="rel">İlişkili${rel.length?` <span class="chip gray" style="margin-left:4px">${rel.length}</span>`:''}</button>
      </div>`;
    const infoPane=`<div data-pane="info"><div class="modal-2col">
        <div class="field col-span"><label>Başlık</label><input id="f-title" value="${esc(a.title)}"/></div>
        <div class="field col-span"><label>Açıklama</label><textarea id="f-desc" placeholder="Faaliyet detayları...">${esc(a.desc||'')}</textarea></div>
        <div class="field"><label>Kategori</label><select id="f-cat">${Object.entries(CAT_LABEL).map(([v,l])=>`<option value="${v}" ${a.cat===v?'selected':''}>${l}</option>`).join('')}</select></div>
        <div class="field"><label>Zorluk</label><select id="f-diff">${Object.entries(DIFF).map(([v,l])=>`<option value="${v}" ${a.diff===v?'selected':''}>${l[0]}</option>`).join('')}</select></div>
        <div class="field"><label>Tarih</label><input id="f-date" type="date" value="${esc(a.date)}"/></div>
        <div class="field"><label>Konum</label><input id="f-loc" value="${esc(a.loc)}"/></div>
        <div class="field"><label>Kontenjan</label><input id="f-quota" type="number" value="${a.quota}"/></div>
        <div class="field"><label>Aşama</label><select id="f-status">${Object.entries(LIFE).map(([v,l])=>`<option value="${v}" ${a.status===v?'selected':''}>${l[0]}</option>`).join('')}</select></div>
        <div class="field col-span"><label>Kapak Görseli</label>
          <div class="cover-ctrl">
            <div class="cover-prev" data-cover-prev></div>
            ${readonly()?'':`<div class="cover-btns">
              <button class="btn btn-ghost btn-sm" data-cover-upload>${svg(ICON.upload,14)}Yükle</button>
              <button class="btn btn-ghost btn-sm" data-cover-pick>${svg(ICON.gallery,14)}Galeriden Seç</button>
              <button class="btn btn-ghost btn-sm" data-cover-del>${svg(ICON.trash,14)}Sil</button>
            </div>`}
          </div>
          <input type="file" id="f-cover-file" accept="image/*" hidden/>
          <div class="cover-path muted mono" data-cover-path></div>
          <div class="cover-picker" data-cover-picker style="display:none"></div>
        </div>
      </div></div>`;
    const relPane=`<div data-pane="rel" style="display:none">
        <div class="muted" style="font-size:13px;margin-bottom:14px">Aynı kategoride (<b>${CAT_LABEL[a.cat]||a.cat}</b>) tamamlanmış geçmiş faaliyetlerin galeri ve haberleri. Bu faaliyet yayınlandığında site tarafında bunlara bağlantı verilebilir.</div>
        ${rel.length?`<div class="card-grid">${rel.map(x=>`<div class="g-card" data-rel-open="${x.id}"><div class="g-cover" style="background-image:url('${(x.report.photos&&x.report.photos[0])||x.img}')"><span class="g-count">${svg(ICON.img,13)} ${(x.report.photos||[]).length}</span></div><div class="g-body"><div class="g-title">${esc(x.title)}</div><div class="g-meta"><span class="muted mono" style="font-size:11px">${esc(x.report.date||x.date)}</span></div></div></div>`).join('')}</div>`:`<div class="empty">${svg(ICON.link,40)}<h4>İlişkili faaliyet yok</h4><p>Bu kategoride tamamlanmış başka faaliyet bulunmuyor.</p></div>`}
      </div>`;
    const appsPane=`<div data-pane="apps" style="display:none">
        <div class="row-between" style="margin-bottom:14px"><div class="muted" style="font-size:13px">${apps.length} başvuru · ${approvedCount()} onaylı</div>${apps.length?`<button class="btn btn-ghost btn-sm" data-export>${svg(ICON.download,14)}Dökümü İndir (CSV)</button>`:''}</div>
        ${apps.length?`<div class="report-out"><div class="ro-head"><span class="ro-label">Rapor Çıktıları</span><span class="muted" style="font-size:12px">Yalnız onaylı katılımcılar · yazdır veya PDF kaydet</span></div><div class="ro-btns"><button class="ro-btn" data-print="katilim">${svg(ICON.clip,20)}<span><b>Katılım Listesi</b><small>Kimlik, iletişim, acil durum, sağlık, imza</small></span></button><button class="ro-btn" data-print="tasima">${svg(ICON.bus,20)}<span><b>Taşımacılık Listesi</b><small>Yolcu listesi, biniş noktasına göre</small></span></button></div></div>`:''}
        <div data-apps></div>
        ${readonly()?'':`<button class="btn btn-ghost btn-sm" data-add-app style="margin-top:4px">${svg(ICON.plus,14)}Manuel Başvuru Ekle</button>`}
      </div>`;
    const reportPane=`<div data-pane="report" style="display:none">
        <div style="display:flex;gap:12px;background:var(--surface-2);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:18px"><span style="color:var(--accent)">${svg(ICON.spark)}</span><div style="font-size:13px;line-height:1.5">Aşamayı <b>Tamamlandı</b> yapıp fotoğraf eklediğinde, bu faaliyet için <b>galeri</b> ve <b>haber</b> otomatik oluşturulup yayınlanır.</div></div>
        ${readonly()?'':`<label class="switch" style="margin-bottom:16px"><input type="checkbox" id="r-held" ${work.held?'checked':''}/><span class="track"></span><span class="sw-label">Faaliyet gerçekleşti</span></label>`}
        <div class="field-row"><div class="field"><label>Gerçekleşme Tarihi</label><input id="r-date" type="date" value="${esc(work.date||'')}"/></div>
          <div class="field"><label>Katılımcı Sayısı <span class="muted" style="text-transform:none;letter-spacing:0">(onaylı: ${approvedCount()})</span></label><input id="r-part" type="number" value="${esc(work.participants||'')}"/></div></div>
        <div class="field"><label>Faaliyet Özeti (haber metni olur)</label><textarea id="r-summary" placeholder="Faaliyet nasıl geçti?">${esc(work.summary||'')}</textarea></div>
        <div class="field"><label>Fotoğraflar (galeri otomatik oluşur)</label>
          <div class="up-grid" data-photos></div>
          ${readonly()?'':`<input type="file" id="r-files" accept="image/*" multiple hidden/>
          <div data-staging style="display:none"></div>
          <div class="up-dropzone" data-dropzone>${svg(ICON.upload)}<div>Fotoğrafları seç veya buraya sürükle</div><small>Yüklenince stabil çözünürlüğe (maks ${MAXW}px) küçültülür</small></div>`}
        </div>
        <div class="field"><label>Videolar (YouTube / Drive)</label>
          <div data-videos></div>
          ${readonly()?'':`<div class="video-add"><input data-video-input placeholder="https://youtube.com/... veya Drive linki"/><button class="btn btn-ghost btn-sm" data-add-video>${svg(ICON.plus,14)}Ekle</button></div>`}
        </div>
      </div>`;
    const foot=readonly()?`<button class="btn btn-ghost" data-close-d>Kapat</button>`:`<button class="btn btn-ghost" data-close-d>İptal</button><button class="btn btn-primary" data-save>${svg(ICON.check)}Kaydet</button>`;
    const dp=openModal(isNew?'Yeni Faaliyet':a.title,tabs+infoPane+appsPane+reportPane+relPane,foot);

    $$('[data-tab]',dp).forEach(t=>t.addEventListener('click',()=>{$$('[data-tab]',dp).forEach(x=>x.classList.toggle('is-active',x===t));['info','apps','report','rel'].forEach(p=>$(`[data-pane="${p}"]`,dp).style.display=t.dataset.tab===p?'':'none');}));
    $('[data-close-d]',dp).addEventListener('click',closeModal);
    $$('[data-rel-open]',dp).forEach(b=>b.addEventListener('click',()=>actModal(b.dataset.relOpen)));

    /* --- kapak görseli kontrolü --- */
    function coverPath(){ const d=$('#f-date',dp).value||new Date().toISOString().slice(0,10); const ym=d.slice(0,7); return `uploads/kapak/${ym}/${id||'yeni'}.jpg`; }
    function drawCover(){
      const prev=$('[data-cover-prev]',dp);
      prev.innerHTML = a.img?`<img src="${a.img}" alt=""/>`:`${svg(ICON.img,24)}`;
      prev.classList.toggle('empty', !a.img);
      $('[data-cover-path]',dp).textContent = a.img?('Kayıt yolu: '+coverPath()):'';
    }
    drawCover();
    const coverFile=$('#f-cover-file',dp);
    $('[data-cover-upload]',dp)&&$('[data-cover-upload]',dp).addEventListener('click',()=>coverFile.click());
    coverFile&&coverFile.addEventListener('change',async e=>{ const f=e.target.files[0]; if(!f)return; toast('Kapak işleniyor...'); const d=await processImage(f); if(d){ a.img=d; drawCover(); toast('Kapak yüklendi'); } });
    $('[data-cover-del]',dp)&&$('[data-cover-del]',dp).addEventListener('click',()=>{ a.img=''; $('[data-cover-picker]',dp).style.display='none'; drawCover(); });
    $('[data-cover-pick]',dp)&&$('[data-cover-pick]',dp).addEventListener('click',()=>{
      const picker=$('[data-cover-picker]',dp);
      if(picker.style.display!=='none'){ picker.style.display='none'; return; }
      const pool=[]; DB.galleries().forEach(g=>g.photos.forEach(p=>pool.push({src:p,title:g.title})));
      if(!pool.length){ toast('Henüz geçmiş faaliyet fotoğrafı yok'); return; }
      picker.style.display='';
      picker.innerHTML=`<div class="muted" style="font-size:12px;margin:8px 0">Geçmiş faaliyetlerin fotoğraflarından seç:</div><div class="cover-pick-grid">${pool.map((p,i)=>`<button class="cover-pick-cell" data-pick="${i}" title="${esc(p.title)}"><img src="${p.src}"/></button>`).join('')}</div>`;
      $$('[data-pick]',picker).forEach(b=>b.addEventListener('click',()=>{ a.img=pool[+b.dataset.pick].src; picker.style.display='none'; drawCover(); toast('Kapak seçildi'); }));
    });

    /* --- applicants cards --- */
    const openApps=new Set();
    function updCount(){const m=$('[data-pane="apps"] .row-between .muted',dp);if(m)m.textContent=apps.length+' başvuru · '+approvedCount()+' onaylı';}
    function drawApps(){
      const ro=readonly();
      $('[data-apps]',dp).innerHTML = apps.length ? '<div class="app-list">'+apps.map((p,i)=>{const op=openApps.has(i);
        const acts=ro?'':(p.status==='approved'?'<span class="al-done">'+svg(ICON.check,14)+'Onaylandı</span><button class="al-btn ghost" data-act="pending" data-i="'+i+'">Geri al</button>'
          :p.status==='rejected'?'<span class="al-rej">İptal edildi</span><button class="al-btn ghost" data-act="pending" data-i="'+i+'">Geri al</button>'
          :'<button class="al-btn ok" data-act="approved" data-i="'+i+'">'+svg(ICON.check,14)+'Onayla</button><button class="al-btn no" data-act="rejected" data-i="'+i+'">İptal et</button>');
        return '<div class="al-row'+(op?' is-open':'')+' st-'+p.status+'"><div class="al-main">'+
          '<div class="al-who"><div class="al-name">'+esc(p.name)+'</div><div class="al-sub mono">'+esc(p.date||'—')+(p.status!=='approved'&&p.status!=='rejected'?' · '+APPSTAT[p.status][0]:'')+'</div></div>'+
          (p.phone?'<a class="al-phone mono" href="tel:'+esc(p.phone.replace(/\s/g,''))+'">'+esc(p.phone)+'</a>':'<span class="al-phone muted">—</span>')+
          '<div class="al-acts">'+acts+'</div>'+
          '<button class="al-more" data-more="'+i+'" aria-expanded="'+op+'">Ayrıntı<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button></div>'+
          (op?'<div class="al-detail"><div class="app-grid">'+
            '<div><div class="ag-label">E-posta</div><div class="ag-val">'+esc(p.email||'—')+'</div></div>'+
            '<div><div class="ag-label">TC Kimlik No</div><div class="ag-val mono">'+esc(p.tc||'—')+'</div></div>'+
            '<div><div class="ag-label">Acil Durum</div><div class="ag-val">'+esc(p.emerName||'—')+(p.emerPhone?' · <span class="mono">'+esc(p.emerPhone)+'</span>':'')+'</div></div>'+
            '<div><div class="ag-label">Durum</div>'+(ro?chip(APPSTAT[p.status]):'<select class="app-stat-sel ag-in" data-st="'+i+'">'+Object.entries(APPSTAT).map(([v,l])=>'<option value="'+v+'"'+(p.status===v?' selected':'')+'>'+l[0]+'</option>').join('')+'</select>')+'</div>'+
            '<div><div class="ag-label">Ulaşım</div>'+(ro?'<div class="ag-val">'+(TRANS[p.transport]||'Belirtilmedi')+'</div>':'<select class="app-stat-sel ag-in" data-tr="'+i+'"><option value="">Belirtilmedi</option>'+Object.entries(TRANS).map(([v,l])=>'<option value="'+v+'"'+(p.transport===v?' selected':'')+'>'+l+'</option>').join('')+'</select>')+'</div>'+
            '<div><div class="ag-label">Biniş Noktası</div>'+(ro?'<div class="ag-val">'+esc(p.pickup||'—')+'</div>':'<input class="app-stat-sel ag-in" data-pk="'+i+'" list="pickup-list" value="'+esc(p.pickup||'')+'" placeholder="Örn. Rize Otogar"/>')+'</div>'+
            '<div class="ag-full"><div class="ag-label">Sağlık Notu</div><div class="ag-val '+(p.health?'warn':'')+'">'+esc(p.health||'Belirtilmedi')+'</div></div>'+
          '</div>'+(ro?'':'<div class="flex" style="justify-content:flex-end;margin-top:10px"><button class="btn btn-ghost btn-sm del" data-del-app="'+i+'">'+svg(ICON.trash,14)+'Başvuruyu sil</button></div>')+'</div>':'')+
        '</div>';}).join('')+'</div>' : '<div class="empty">'+svg(ICON.users,40)+'<h4>Henüz başvuru yok</h4><p>Sitedeki başvuru formundan gelenler burada listelenir.</p></div>';
      $$('[data-act]',dp).forEach(b=>b.addEventListener('click',()=>{apps[+b.dataset.i].status=b.dataset.act;updCount();drawApps();}));
      $$('[data-more]',dp).forEach(b=>b.addEventListener('click',()=>{const i=+b.dataset.more;openApps.has(i)?openApps.delete(i):openApps.add(i);drawApps();}));
      $$('[data-st]',dp).forEach(s=>s.addEventListener('change',()=>{apps[+s.dataset.st].status=s.value;updCount();drawApps();}));
      $$('[data-tr]',dp).forEach(s=>s.addEventListener('change',()=>{apps[+s.dataset.tr].transport=s.value;}));
      $$('[data-pk]',dp).forEach(s=>s.addEventListener('input',()=>{apps[+s.dataset.pk].pickup=s.value;}));
      $$('[data-del-app]',dp).forEach(b=>b.addEventListener('click',()=>{ if(confirm('Başvuru silinsin mi?')){ apps.splice(+b.dataset.delApp,1); openApps.clear(); updCount(); drawApps(); } }));
    }
    drawApps();
    $$('[data-print]',dp).forEach(b=>b.addEventListener('click',()=>printList(b.dataset.print,{title:$('#f-title',dp).value||a.title,date:$('#f-date',dp).value||a.date,loc:$('#f-loc',dp).value||a.loc,cat:CAT_LABEL[$('#f-cat',dp).value]||''},apps)));
    $('[data-add-app]',dp)&&$('[data-add-app]',dp).addEventListener('click',()=>{
      const name=prompt('Ad Soyad:'); if(!name)return;
      apps.push({name,tc:'',phone:'',email:'',date:new Date().toISOString().slice(0,10),status:'pending',emerName:'',emerPhone:'',health:''});
      drawApps();
    });
    $('[data-export]',dp)&&$('[data-export]',dp).addEventListener('click',()=>exportApps(a.title,apps));

    /* --- auto participant count on "held" --- */
    const heldBox=$('#r-held',dp), partInput=$('#r-part',dp);
    if(heldBox) heldBox.addEventListener('change',()=>{ if(heldBox.checked){ partInput.value=approvedCount(); if(!$('#r-date',dp).value)$('#r-date',dp).value=$('#f-date',dp).value; } });

    /* --- photos --- */
    function drawPhotos(){
      const host=$('[data-photos]',dp);
      const up=work.photos.map((p,i)=>`<div class="up-cell"><img src="${p}"/>${readonly()?'':`<button class="up-rm" data-del-photo="${i}">${svg(ICON.x,13)}</button>`}</div>`).join('');
      const pend=pending.map((p,i)=>`<div class="up-cell pending"><img src="${p}"/><button class="up-rm" data-del-pending="${i}">${svg(ICON.x,13)}</button></div>`).join('');
      host.innerHTML=(up+pend)|| '<div class="muted" style="grid-column:1/-1;font-size:13px;padding:8px 0">Henüz fotoğraf yok</div>';
      $$('[data-del-photo]',dp).forEach(b=>b.addEventListener('click',()=>{work.photos.splice(+b.dataset.delPhoto,1);drawPhotos();}));
      $$('[data-del-pending]',dp).forEach(b=>b.addEventListener('click',()=>{pending.splice(+b.dataset.delPending,1);drawStaging();drawPhotos();}));
    }
    function drawStaging(){
      const st=$('[data-staging]',dp); if(!st)return;
      if(!pending.length){ st.style.display='none'; st.innerHTML=''; return; }
      st.style.display='';
      st.className='up-staging';
      st.innerHTML=`<div class="up-staging-head"><span><b>${pending.length}</b> fotoğraf yüklenmeye hazır</span><div class="flex" style="gap:6px"><button class="btn btn-primary btn-sm" data-do-upload>${svg(ICON.upload,14)}Yükle (${pending.length})</button><button class="btn btn-ghost btn-sm" data-clear-staging>Vazgeç</button></div></div><div class="muted" style="font-size:12px">Önizlemeler aşağıda "Bekliyor" etiketiyle görünür. Yükle'ye basınca galeriye eklenir.</div>`;
      $('[data-do-upload]',st).addEventListener('click',()=>{ work.photos=work.photos.concat(pending); pending=[]; drawStaging(); drawPhotos(); toast('Fotoğraflar yüklendi'); });
      $('[data-clear-staging]',st).addEventListener('click',()=>{ pending=[]; drawStaging(); drawPhotos(); });
    }
    async function handleFiles(files){
      const arr=[...files].filter(f=>f.type.startsWith('image/'));
      if(!arr.length)return;
      toast(arr.length+' fotoğraf işleniyor...');
      for(const f of arr){ const d=await processImage(f); if(d) pending.push(d); }
      drawStaging(); drawPhotos();
    }
    drawPhotos();
    const fileInput=$('#r-files',dp), dz=$('[data-dropzone]',dp);
    if(dz){
      dz.addEventListener('click',()=>fileInput.click());
      fileInput.addEventListener('change',e=>handleFiles(e.target.files));
      ['dragover','dragenter'].forEach(ev=>dz.addEventListener(ev,e=>{e.preventDefault();dz.classList.add('drag');}));
      ['dragleave','drop'].forEach(ev=>dz.addEventListener(ev,e=>{e.preventDefault();dz.classList.remove('drag');}));
      dz.addEventListener('drop',e=>handleFiles(e.dataTransfer.files));
    }

    /* --- videos --- */
    function drawVideos(){
      const host=$('[data-videos]',dp);
      host.innerHTML=work.videos.length?work.videos.map((v,i)=>{const [k,lab]=videoKind(v);return `<div class="video-row"><div class="vr-left"><span class="vr-ico ${k}">${svg(ICON.video,15)}</span><div style="min-width:0"><div class="vr-kind">${lab}</div><div class="vr-url">${esc(v)}</div></div></div>${readonly()?'':`<button class="btn-icon del" data-del-video="${i}">${svg(ICON.trash,13)}</button>`}</div>`;}).join(''):'<div class="muted" style="font-size:13px">Video yok</div>';
      $$('[data-del-video]',dp).forEach(b=>b.addEventListener('click',()=>{work.videos.splice(+b.dataset.delVideo,1);drawVideos();}));
    }
    drawVideos();
    const vinput=$('[data-video-input]',dp);
    function addVideo(){ const u=(vinput.value||'').trim(); if(!u)return; if(!/^https?:\/\//i.test(u)){alert('Geçerli bir bağlantı gir (https://...)');return;} work.videos.push(u); vinput.value=''; drawVideos(); }
    $('[data-add-video]',dp)&&$('[data-add-video]',dp).addEventListener('click',addVideo);
    vinput&&vinput.addEventListener('keydown',e=>{if(e.key==='Enter'){e.preventDefault();addVideo();}});

    /* --- save --- */
    $('[data-save]',dp)&&$('[data-save]',dp).addEventListener('click',()=>{
      const t=$('#f-title',dp).value.trim(); if(!t){alert('Başlık gerekli');return;}
      if(pending.length && !confirm('Yüklenmeyi bekleyen fotoğraflar var. Yüklemeden kaydedilsin mi?')) return;
      const status=$('#f-status',dp).value;
      work.held=heldBox?heldBox.checked:work.held; work.date=$('#r-date',dp).value; work.participants=$('#r-part',dp).value; work.summary=$('#r-summary',dp).value;
      const hasReport = status==='tamamlandi' && (work.summary || work.photos.length || work.videos.length);
      const obj={ title:t, desc:$('#f-desc',dp).value, cat:$('#f-cat',dp).value, diff:$('#f-diff',dp).value, date:$('#f-date',dp).value, loc:$('#f-loc',dp).value, quota:+$('#f-quota',dp).value||0, status, img:a.img||(work.photos[0])||'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=600', coverPath:(a.img?coverPath():''), applications:apps, report: status==='tamamlandi'?work:(a.report||null) };
      if(id) Object.assign(DB.db.activities.find(x=>x.id===id),obj); else { obj.id=DB.uid(); DB.db.activities.unshift(obj); }
      DB.save(); closeModal(); refreshBadges(); go('activities');
      if(status==='tamamlandi'&&hasReport) toast('Kaydedildi · galeri ve haber otomatik oluştu');
      else toast(id?'Faaliyet güncellendi':'Faaliyet eklendi');
    });
  }

  function exportApps(title,apps){
    const rows=[['Ad Soyad','TC Kimlik','Telefon','E-posta','Başvuru Tarihi','Durum','Acil Durum Kişi','Acil Durum Tel','Sağlık Notu']];
    (apps||[]).forEach(p=>rows.push([p.name,p.tc,p.phone,p.email,p.date,(APPSTAT[p.status]||['',''])[0],p.emerName,p.emerPhone,p.health]));
    const csv=rows.map(r=>r.map(c=>'"'+String(c==null?'':c).replace(/"/g,'""')+'"').join(',')).join('\r\n');
    const blob=new Blob(['\ufeff'+csv],{type:'text/csv;charset=utf-8'});
    const url=URL.createObjectURL(blob); const link=document.createElement('a');
    link.href=url; link.download=(title||'faaliyet').replace(/[^\wğüşıöçĞÜŞİÖÇ ]/g,'').replace(/\s+/g,'-')+'-basvurular.csv';
    document.body.appendChild(link); link.click(); link.remove(); setTimeout(()=>URL.revokeObjectURL(url),1000);
    toast('Başvuru dökümü indirildi');
  }

  /* ===== Filtre + görünüm yardımcıları (galeri & haber ortak) ===== */
  function filterBar(state, cats, years, onChange){
    // returns HTML; binding done by caller via data-attrs
    const catOpts=`<option value="all">Tüm Kategoriler</option>`+cats.map(c=>`<option value="${c}" ${state.cat===c?'selected':''}>${CAT_LABEL[c]||c}</option>`).join('');
    const yearOpts=`<option value="all">Tüm Yıllar</option>`+years.map(y=>`<option value="${y}" ${String(state.year)===String(y)?'selected':''}>${y}</option>`).join('');
    return `<div class="toolbar">
      <div class="search-box">${svg(ICON.search)}<input data-flt-search placeholder="Ara..." value="${esc(state.q||'')}"/></div>
      <div class="field" style="margin:0"><select data-flt-year style="height:40px">${yearOpts}</select></div>
      <div class="field" style="margin:0"><select data-flt-cat style="height:40px">${catOpts}</select></div>
      <div class="tb-spacer"></div>
      <div class="seg" data-view><button data-v="card" class="${state.view==='card'?'is-active':''}" title="Kart">${svg(ICON.grid,16)}</button><button data-v="list" class="${state.view==='list'?'is-active':''}" title="Liste">${svg(ICON.list,16)}</button></div>
    </div>`;
  }
  function bindFilter(root, state, onChange){
    $('[data-flt-search]',root).addEventListener('input',e=>{state.q=e.target.value;onChange();});
    $('[data-flt-year]',root).addEventListener('change',e=>{state.year=e.target.value;onChange();});
    $('[data-flt-cat]',root).addEventListener('change',e=>{state.cat=e.target.value;onChange();});
    $$('[data-v]',root).forEach(b=>b.addEventListener('click',()=>{state.view=b.dataset.v;$$('[data-v]',root).forEach(x=>x.classList.toggle('is-active',x===b));onChange();}));
  }

  /* ----- Galeri (otomatik) ----- */
  const galState={q:'',year:'all',cat:'all',view:'card'};
  RENDER.gallery=function(){
    return `<div class="panel" style="margin-bottom:16px"><div class="panel-body" style="display:flex;gap:12px;align-items:center"><span class="sc-icon b">${svg(ICON.spark)}</span><div style="font-size:13px;line-height:1.5"><b>Galeriler otomatik oluşur.</b> Tamamlanan ve fotoğrafı girilen her faaliyet için bir galeri burada görünür.</div></div></div>
      <div data-flt></div><div data-body></div>`;
  };
  BIND.gallery=function(){
    const all=DB.galleries();
    const years=[...new Set(all.map(g=>g.year))].sort((a,b)=>b-a);
    const cats=[...new Set(all.map(g=>g.cat))];
    $('[data-flt]').innerHTML=filterBar(galState,cats,years);
    bindFilter($('[data-flt]'),galState,draw);
    function list(){ const q=galState.q.toLowerCase().trim();
      return all.filter(g=>(galState.year==='all'||String(g.year)===String(galState.year))&&(galState.cat==='all'||g.cat===galState.cat)&&g.title.toLowerCase().includes(q)); }
    function draw(){
      const gs=list(); const host=$('[data-body]');
      if(!gs.length){ host.innerHTML=`<div class="panel"><div class="empty">${svg(ICON.gallery,40)}<h4>Galeri bulunamadı</h4><p>Filtreleri değiştir ya da bir faaliyeti tamamlayıp fotoğraf ekle.</p></div></div>`; return; }
      if(galState.view==='card'){
        // yıla göre grupla
        const byYear={}; gs.forEach(g=>{(byYear[g.year]=byYear[g.year]||[]).push(g);});
        host.innerHTML=Object.keys(byYear).sort((a,b)=>b-a).map(y=>`<div class="year-block"><div class="year-head"><span class="year-num">${y}</span><span class="muted mono" style="font-size:12px">${byYear[y].length} galeri</span></div>
          <div class="card-grid">${byYear[y].map(g=>cardHTML(g)).join('')}</div></div>`).join('');
      } else {
        host.innerHTML=`<div class="panel"><table class="tbl"><thead><tr><th>Galeri</th><th>Kategori</th><th>Yıl</th><th>Tarih</th><th>İçerik</th><th></th></tr></thead><tbody>
          ${gs.map(g=>`<tr><td><div class="cell-main"><img class="t-thumb" src="${g.cover}"/><div class="t-title">${esc(g.title)}</div></div></td><td class="muted">${CAT_LABEL[g.cat]||g.cat}</td><td class="mono">${g.year}</td><td class="mono muted">${esc(g.date)}</td><td><span class="chip blue">${g.photos.length} foto</span>${g.videos.length?` <span class="chip gray">${g.videos.length} video</span>`:''}</td><td class="t-actions"><button class="btn-icon" data-open="${g.actId}" title="Faaliyeti aç">${svg(ICON.link)}</button></td></tr>`).join('')}
        </tbody></table></div>`;
      }
      $$('[data-open]',host).forEach(b=>b.addEventListener('click',()=>{go('activities');setTimeout(()=>actModal(b.dataset.open),60);}));
    }
    function cardHTML(g){
      return `<div class="g-card" data-open="${g.actId}"><div class="g-cover" style="background-image:url('${g.cover}')"><span class="g-count">${svg(ICON.img,13)} ${g.photos.length}</span>${g.videos.length?`<span class="g-count v">${svg(ICON.video,13)} ${g.videos.length}</span>`:''}</div>
        <div class="g-body"><div class="g-title">${esc(g.title)}</div><div class="g-meta"><span class="chip gray">${CAT_LABEL[g.cat]||g.cat}</span><span class="muted mono" style="font-size:11px">${esc(g.date)}</span></div></div></div>`;
    }
    draw();
  };

  /* ----- Haberler & Duyurular ----- */
  const newsState={q:'',year:'all',cat:'all',view:'card'};
  RENDER.news=function(){
    return `<div class="panel" style="margin-bottom:16px"><div class="panel-body" style="display:flex;gap:12px;align-items:center"><span class="sc-icon b">${svg(ICON.spark)}</span><div style="font-size:13px;line-height:1.5;flex:1"><b>Tamamlanan faaliyetler otomatik haber olur.</b> Otomatik haberleri düzenleyebilir, ayrıca manuel duyuru ekleyebilirsin.</div>${readonly()?'':`<button class="btn btn-primary" data-add-news>${svg(ICON.plus)}Yeni Duyuru</button>`}</div></div>
      <div data-flt></div><div data-body></div>`;
  };
  BIND.news=function(){
    function all(){ return DB.newsList(); }
    const years=()=>[...new Set(all().map(n=>+String(n.date).slice(0,4)).filter(Boolean))].sort((a,b)=>b-a);
    const cats=()=>[...new Set(all().map(n=>n.cat))];
    function rebuildFlt(){ $('[data-flt]').innerHTML=newsFilterBar(); bindNewsFlt(); }
    function newsFilterBar(){
      const catOpts=`<option value="all">Tüm Kategoriler</option>`+cats().map(c=>`<option value="${c}" ${newsState.cat===c?'selected':''}>${c}</option>`).join('');
      const yearOpts=`<option value="all">Tüm Yıllar</option>`+years().map(y=>`<option value="${y}" ${String(newsState.year)===String(y)?'selected':''}>${y}</option>`).join('');
      return `<div class="toolbar"><div class="search-box">${svg(ICON.search)}<input data-flt-search placeholder="Haber ara..." value="${esc(newsState.q||'')}"/></div>
        <div class="field" style="margin:0"><select data-flt-year style="height:40px">${yearOpts}</select></div>
        <div class="field" style="margin:0"><select data-flt-cat style="height:40px">${catOpts}</select></div>
        <div class="tb-spacer"></div>
        <div class="seg" data-view><button data-v="card" class="${newsState.view==='card'?'is-active':''}">${svg(ICON.grid,16)}</button><button data-v="list" class="${newsState.view==='list'?'is-active':''}">${svg(ICON.list,16)}</button></div></div>`;
    }
    function bindNewsFlt(){
      $('[data-flt-search]').addEventListener('input',e=>{newsState.q=e.target.value;draw();});
      $('[data-flt-year]').addEventListener('change',e=>{newsState.year=e.target.value;draw();});
      $('[data-flt-cat]').addEventListener('change',e=>{newsState.cat=e.target.value;draw();});
      $$('[data-v]').forEach(b=>b.addEventListener('click',()=>{newsState.view=b.dataset.v;$$('[data-v]').forEach(x=>x.classList.toggle('is-active',x===b));draw();}));
    }
    function list(){ const q=newsState.q.toLowerCase().trim();
      return all().filter(n=>(newsState.year==='all'||String(n.date).slice(0,4)===String(newsState.year))&&(newsState.cat==='all'||n.cat===newsState.cat)&&(n.title||'').toLowerCase().includes(q)); }
    function draw(){
      const ns=list(); const host=$('[data-body]');
      if(!ns.length){ host.innerHTML=`<div class="panel"><div class="empty">${svg(ICON.news,40)}<h4>Haber bulunamadı</h4><p>Filtreleri değiştir ya da yeni duyuru ekle.</p></div></div>`; return; }
      if(newsState.view==='card'){
        host.innerHTML=`<div class="card-grid">${ns.map(n=>`<div class="g-card"><div class="g-cover" style="background-image:url('${n.cover}')"><span class="g-src ${n.source}">${n.source==='auto'?'Otomatik':'Manuel'}</span></div>
          <div class="g-body"><div class="flex" style="gap:6px;margin-bottom:6px"><span class="chip ${n.cat==='Duyuru'?'orange':n.cat==='Haber'?'blue':'green'}">${esc(n.cat)}</span><span class="chip green dot" style="margin-left:auto">${n.status==='published'?'Yayında':'Taslak'}</span></div>
          <div class="g-title">${esc(n.title)}</div><div class="muted" style="font-size:12px;margin:4px 0 8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">${esc(n.body||'')}</div>
          <div class="flex" style="justify-content:space-between"><span class="muted mono" style="font-size:11px">${esc(n.date)}</span>${readonly()?'':`<div class="flex" style="gap:4px"><button class="btn-icon" data-edit-news="${n.id}">${svg(ICON.edit,14)}</button>${n.source==='manual'?`<button class="btn-icon del" data-del-news="${n.id}">${svg(ICON.trash,14)}</button>`:''}</div>`}</div></div></div>`).join('')}</div>`;
      } else {
        host.innerHTML=`<div class="panel"><table class="tbl"><thead><tr><th>Haber</th><th>Kategori</th><th>Tarih</th><th>Kaynak</th><th>Durum</th><th></th></tr></thead><tbody>
          ${ns.map(n=>`<tr><td><div class="cell-main"><img class="t-thumb" src="${n.cover}"/><div><div class="t-title">${esc(n.title)}</div><div class="t-sub">${esc((n.body||'').slice(0,60))}${(n.body||'').length>60?'…':''}</div></div></div></td><td><span class="chip ${n.cat==='Duyuru'?'orange':n.cat==='Haber'?'blue':'green'}">${esc(n.cat)}</span></td><td class="mono muted">${esc(n.date)}</td><td><span class="chip ${n.source==='auto'?'green':'gray'} dot">${n.source==='auto'?'Otomatik':'Manuel'}</span></td><td>${n.status==='published'?'<span class="chip green dot">Yayında</span>':'<span class="chip gray dot">Taslak</span>'}</td><td class="t-actions">${readonly()?'':`<button class="btn-icon" data-edit-news="${n.id}">${svg(ICON.edit)}</button>${n.source==='manual'?`<button class="btn-icon del" data-del-news="${n.id}">${svg(ICON.trash)}</button>`:''}`}</td></tr>`).join('')}
        </tbody></table></div>`;
      }
      $$('[data-edit-news]',host).forEach(b=>b.addEventListener('click',()=>newsDrawer(b.dataset.editNews,refresh)));
      $$('[data-del-news]',host).forEach(b=>b.addEventListener('click',()=>{ if(confirm('Bu duyuru silinsin mi?')){ const realId=b.dataset.delNews.replace('n-man-',''); DB.db.newsManual=DB.db.newsManual.filter(x=>x.id!==b.dataset.delNews); DB.save(); refresh(); toast('Silindi'); } }));
    }
    function refresh(){ rebuildFlt(); draw(); }
    $('[data-add-news]')&&$('[data-add-news]').addEventListener('click',()=>newsDrawer(null,refresh));
    rebuildFlt(); draw();
  };
  function newsDrawer(id,refresh){
    const items=DB.newsList(); const n=id?items.find(x=>x.id===id):{id:null,source:'manual',cat:'Duyuru',title:'',body:'',date:new Date().toISOString().slice(0,10),cover:'',status:'published'};
    const isAuto=n.source==='auto';
    const body=`${isAuto?`<div style="display:flex;gap:10px;background:var(--surface-2);border:1px solid var(--border);border-radius:8px;padding:12px;margin-bottom:16px;font-size:13px"><span style="color:var(--accent)">${svg(ICON.spark,16)}</span><div>Bu haber bir faaliyetten <b>otomatik</b> oluştu. Burada yaptığın düzenleme otomatik metnin üzerine yazılır.</div></div>`:''}
      <div class="field"><label>Başlık</label><input id="nd-title" value="${esc(n.title)}"/></div>
      <div class="field-row"><div class="field"><label>Kategori</label><select id="nd-cat">${NEWSCAT.map(c=>`<option ${n.cat===c?'selected':''}>${c}</option>`).join('')}</select></div><div class="field"><label>Tarih</label><input id="nd-date" type="date" value="${esc(n.date)}"/></div></div>
      <div class="field"><label>Durum</label><select id="nd-status"><option value="published" ${n.status==='published'?'selected':''}>Yayında</option><option value="draft" ${n.status==='draft'?'selected':''}>Taslak</option></select></div>
      <div class="field"><label>İçerik</label><textarea id="nd-body" style="min-height:120px">${esc(n.body||'')}</textarea></div>
      <div class="field"><label>Kapak Görseli</label><div class="upslot" id="nd-up">${n.cover?`<img src="${n.cover}" style="border-radius:8px;max-height:140px"/>`:`${svg(ICON.img,28)}<small>Görsel ekle</small>`}</div></div>
      ${isAuto?`<button class="btn btn-ghost btn-sm" data-reset-news>Otomatik metne sıfırla</button>`:''}`;
    const dp=openDrawer(id?(isAuto?'Otomatik Haberi Düzenle':'Duyuru Düzenle'):'Yeni Duyuru',body,`<button class="btn btn-ghost" data-close-d>İptal</button><button class="btn btn-primary" data-save>${svg(ICON.check)}Kaydet</button>`);
    $('[data-close-d]',dp).addEventListener('click',closeDrawer);
    let cover=n.cover;
    $('#nd-up',dp).addEventListener('click',()=>{const url=prompt('Kapak görseli URL (demo):',cover||'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=600');if(url){cover=url;$('#nd-up',dp).innerHTML=`<img src="${url}" style="border-radius:8px;max-height:140px"/>`;}});
    $('[data-reset-news]',dp)&&$('[data-reset-news]',dp).addEventListener('click',()=>{ const actId=id.replace('n-',''); delete DB.db.newsEdits[actId]; DB.save(); closeDrawer(); refresh(); toast('Otomatik metne sıfırlandı'); });
    $('[data-save]',dp).addEventListener('click',()=>{
      const t=$('#nd-title',dp).value.trim(); if(!t){alert('Başlık gerekli');return;}
      const data={ title:t, cat:$('#nd-cat',dp).value, date:$('#nd-date',dp).value, status:$('#nd-status',dp).value, body:$('#nd-body',dp).value, cover:cover||'https://images.unsplash.com/photo-1519681393784-d120267933ba?w=600' };
      if(isAuto){ const actId=id.replace('n-',''); DB.db.newsEdits[actId]=Object.assign({},DB.db.newsEdits[actId],data); }
      else if(id){ Object.assign(DB.db.newsManual.find(x=>x.id===id),data); }
      else { data.id='n-man-'+DB.uid(); DB.db.newsManual.unshift(data); }
      DB.save(); closeDrawer(); refresh(); toast('Kaydedildi');
    });
  }

  /* ----- Anasayfa Banner ----- */
  RENDER.banner=function(){
    return `<div class="panel" style="margin-bottom:16px"><div class="panel-body" style="display:flex;gap:12px;align-items:center"><span class="sc-icon a">${svg(ICON.banner)}</span><div style="font-size:13px;line-height:1.5;flex:1"><b>Anasayfa hero/banner slaytları.</b> Görsel, metin ve bağlantı ekle; bir haber, duyuru ya da faaliyete bağla. Sıralamayı oklarla değiştir.</div>${readonly()?'':`<button class="btn btn-primary" data-add>${svg(ICON.plus)}Yeni Banner</button>`}</div></div>
      <div class="panel"><table class="tbl"><thead><tr><th>Sıra</th><th>Banner</th><th>Bağlantı</th><th>Yayın</th><th></th></tr></thead><tbody data-rows></tbody></table></div>`;
  };
  BIND.banner=function(){
    const draw=()=>{
      const list=DB.db.banners.slice().sort((a,b)=>a.order-b.order);
      $('[data-rows]').innerHTML=list.map((b,i)=>`<tr>
        <td class="mono">${readonly()?b.order:`<div class="flex" style="gap:2px"><button class="btn-icon" data-up="${b.id}" ${i===0?'disabled style=opacity:.3':''}>${svg(ICON.arrowup,14)}</button><button class="btn-icon" data-down="${b.id}" ${i===list.length-1?'disabled style=opacity:.3':''}>${svg(ICON.arrowdown,14)}</button></div>`}</td>
        <td><div class="cell-main"><img class="t-thumb" src="${b.img}"/><div><div class="t-title">${esc(b.title)}</div><div class="t-sub">${esc((b.text||'').slice(0,52))}${(b.text||'').length>52?'…':''}</div></div></div></td>
        <td>${linkChip(b.link)}</td>
        <td>${b.active?'<span class="chip green dot">Yayında</span>':'<span class="chip gray dot">Pasif</span>'}</td>
        <td class="t-actions">${readonly()?'':`<button class="btn-icon" data-edit="${b.id}">${svg(ICON.edit)}</button><button class="btn-icon del" data-del="${b.id}">${svg(ICON.trash)}</button>`}</td></tr>`).join('')||`<tr><td colspan="5"><div class="empty">${svg(ICON.banner,40)}<h4>Banner yok</h4><p>Anasayfa için ilk slaytı ekle.</p></div></td></tr>`;
      $$('[data-up]').forEach(x=>x.addEventListener('click',()=>reorderBanner(x.dataset.up,-1,draw)));
      $$('[data-down]').forEach(x=>x.addEventListener('click',()=>reorderBanner(x.dataset.down,1,draw)));
      $$('[data-edit]').forEach(x=>x.addEventListener('click',()=>bannerDrawer(x.dataset.edit,draw)));
      $$('[data-del]').forEach(x=>x.addEventListener('click',()=>{if(confirm('Banner silinsin mi?')){DB.db.banners=DB.db.banners.filter(z=>z.id!==x.dataset.del);DB.save();draw();toast('Silindi');}}));
    };
    $('[data-add]')&&$('[data-add]').addEventListener('click',()=>bannerDrawer(null,draw));
    draw();
  };
  function reorderBanner(id,dir,draw){ const list=DB.db.banners.slice().sort((a,b)=>a.order-b.order); const i=list.findIndex(x=>x.id===id),j=i+dir; if(j<0||j>=list.length)return; const o=list[i].order; list[i].order=list[j].order; list[j].order=o; DB.save(); draw(); }
  function linkLabel(link){
    if(!link) return ['gray','Bağlantı yok'];
    const [type,val]=link.split(':');
    if(type==='activity'){ const a=DB.db.activities.find(x=>x.id===val); return ['blue', a?('Faaliyet: '+a.title):'Faaliyet']; }
    if(type==='news'){ const n=DB.newsList().find(x=>x.id===val); return ['green', n?('Haber: '+n.title):'Haber/Duyuru']; }
    if(type==='url'){ return ['orange', val]; }
    return ['gray',link];
  }
  function linkChip(link){ const [c,l]=linkLabel(link); return `<span class="chip ${c}">${svg(ICON.link,12)} ${esc(l.length>34?l.slice(0,34)+'…':l)}</span>`; }
  function bannerDrawer(id,draw){
    const b=id?DB.db.banners.find(x=>x.id===id):{title:'',text:'',img:'',link:'',active:true,order:(DB.db.banners.length+1)};
    const parts=(b.link||'').split(':'); const lType=parts[0]||'none'; const lVal=parts.slice(1).join(':')||'';
    const actOpts=DB.db.activities.map(a=>`<option value="${a.id}" ${lType==='activity'&&lVal===a.id?'selected':''}>${esc(a.title)}</option>`).join('');
    const newsOpts=DB.newsList().map(n=>`<option value="${n.id}" ${lType==='news'&&lVal===n.id?'selected':''}>${esc(n.title)}</option>`).join('');
    const body=`<div class="field"><label>Banner Görseli</label><div class="upslot" id="b-up">${b.img?`<img src="${b.img}" style="border-radius:8px;max-height:150px"/>`:`${svg(ICON.img,28)}<small>Görsel ekle (geniş, yatay)</small>`}</div></div>
      <div class="field"><label>Başlık</label><input id="b-title" value="${esc(b.title)}"/></div>
      <div class="field"><label>Metin</label><textarea id="b-text" placeholder="Banner alt metni...">${esc(b.text||'')}</textarea></div>
      <div class="field"><label>Bağlantı Türü</label><select id="b-ltype">
        <option value="none" ${lType==='none'?'selected':''}>Bağlantı yok</option>
        <option value="activity" ${lType==='activity'?'selected':''}>Faaliyet</option>
        <option value="news" ${lType==='news'?'selected':''}>Haber / Duyuru</option>
        <option value="url" ${lType==='url'?'selected':''}>Özel URL</option>
      </select></div>
      <div class="field" data-lwrap="activity" style="${lType==='activity'?'':'display:none'}"><label>Faaliyet Seç</label><select id="b-lact">${actOpts||'<option>Faaliyet yok</option>'}</select></div>
      <div class="field" data-lwrap="news" style="${lType==='news'?'':'display:none'}"><label>Haber / Duyuru Seç</label><select id="b-lnews">${newsOpts||'<option>Haber yok</option>'}</select></div>
      <div class="field" data-lwrap="url" style="${lType==='url'?'':'display:none'}"><label>URL</label><input id="b-lurl" value="${lType==='url'?esc(lVal):''}" placeholder="Faaliyetler.html veya https://..."/></div>
      <label class="switch"><input type="checkbox" id="b-active" ${b.active?'checked':''}/><span class="track"></span><span class="sw-label">Anasayfada yayında</span></label>`;
    const dp=openDrawer(id?'Banner Düzenle':'Yeni Banner',body,`<button class="btn btn-ghost" data-close-d>İptal</button><button class="btn btn-primary" data-save>${svg(ICON.check)}Kaydet</button>`);
    $('[data-close-d]',dp).addEventListener('click',closeDrawer);
    let img=b.img;
    $('#b-up',dp).addEventListener('click',()=>{const url=prompt('Banner görseli URL (demo):',img||'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=1600');if(url){img=url;$('#b-up',dp).innerHTML=`<img src="${url}" style="border-radius:8px;max-height:150px"/>`;}});
    $('#b-ltype',dp).addEventListener('change',e=>{['activity','news','url'].forEach(k=>$(`[data-lwrap="${k}"]`,dp).style.display=e.target.value===k?'':'none');});
    $('[data-save]',dp).addEventListener('click',()=>{
      const t=$('#b-title',dp).value.trim(); if(!t){alert('Başlık gerekli');return;}
      const lt=$('#b-ltype',dp).value; let link='';
      if(lt==='activity') link='activity:'+$('#b-lact',dp).value;
      else if(lt==='news') link='news:'+$('#b-lnews',dp).value;
      else if(lt==='url') link='url:'+$('#b-lurl',dp).value.trim();
      const obj={title:t,text:$('#b-text',dp).value,img:img||'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?w=1600',link,active:$('#b-active',dp).checked,order:b.order};
      if(id) Object.assign(DB.db.banners.find(x=>x.id===id),obj); else { obj.id=DB.uid(); DB.db.banners.push(obj); }
      DB.save(); closeDrawer(); toast('Kaydedildi'); draw();
    });
  }

  /* ----- Messages ----- */
  let msgFilter='all';
  RENDER.messages=function(){
    return `<div class="toolbar"><div class="seg" data-seg><button data-f="all" class="${msgFilter==='all'?'is-active':''}">Tümü</button><button data-f="unread" class="${msgFilter==='unread'?'is-active':''}">Okunmamış</button></div><div class="search-box">${svg(ICON.search)}<input data-search placeholder="Mesaj ara..."/></div></div><div class="panel"><div data-rows></div></div>`;
  };
  BIND.messages=function(){
    const draw=()=>{
      const term=($('[data-search]').value||'').toLowerCase();
      const list=DB.db.messages.filter(m=>(msgFilter==='all'||!m.read)&&(m.name.toLowerCase().includes(term)||m.subject.toLowerCase().includes(term)));
      $('[data-rows]').innerHTML=list.map(m=>`<div class="msg-item ${m.read?'':'unread'}" data-msg="${m.id}"><span class="mi-ava">${esc(m.name[0])}</span><div><div class="mi-name">${esc(m.name)} ${m.read?'':'<span class="chip orange" style="margin-left:6px">Yeni</span>'}</div><div class="mi-subj">${esc(m.subject)}</div><div class="mi-preview">${esc(m.body)}</div></div><div class="mi-meta">${esc(m.date)}</div></div>`).join('')||`<div class="empty">${svg(ICON.messages,40)}<h4>Mesaj yok</h4></div>`;
      $$('[data-msg]').forEach(el=>el.addEventListener('click',()=>msgDrawer(el.dataset.msg,draw)));
    };
    $('[data-search]').addEventListener('input',draw);
    $$('[data-seg] button').forEach(b=>b.addEventListener('click',()=>{msgFilter=b.dataset.f;$$('[data-seg] button').forEach(x=>x.classList.toggle('is-active',x===b));draw();}));
    draw();
  };
  function msgDrawer(id,draw){
    const m=DB.db.messages.find(x=>x.id===id); if(!m)return; m.read=true; DB.save(); refreshBadges();
    const TOPIC={uyelik:'Üyelik',faaliyet:'Faaliyet',egitim:'Eğitim',isbirligi:'İşbirliği',genel:'Genel',basin:'Basın'};
    const body=`<div class="flex" style="margin-bottom:16px"><span class="mi-ava" style="width:48px;height:48px">${esc(m.name[0])}</span><div><div style="font-weight:700;font-size:16px">${esc(m.name)}</div><div class="muted mono" style="font-size:12px">${esc(m.email)}</div></div></div>
      <div class="flex" style="gap:8px;margin-bottom:16px"><span class="chip blue">${esc(TOPIC[m.topic]||m.topic)}</span><span class="muted mono" style="font-size:12px">${esc(m.date)}</span></div>
      <div class="field"><label>Konu</label><div style="font-weight:600;font-size:15px">${esc(m.subject)}</div></div>
      <div class="field"><label>Mesaj</label><div style="line-height:1.7">${esc(m.body)}</div></div>
      ${readonly()?'':`<div class="field" style="margin-top:8px"><label>Yanıtla</label><textarea placeholder="Yanıtınız... (demo)"></textarea></div>`}`;
    const foot=`<button class="btn btn-ghost" data-close-d>Kapat</button>${readonly()?'':`<a class="btn btn-ghost" href="mailto:${esc(m.email)}">${svg(ICON.mail)}E-posta</a><button class="btn btn-primary" data-reply>${svg(ICON.reply)}Gönder</button>`}`;
    const dp=openDrawer('Mesaj',body,foot);
    $('[data-close-d]',dp).addEventListener('click',()=>{closeDrawer();draw();});
    $('[data-reply]',dp)&&$('[data-reply]',dp).addEventListener('click',()=>{closeDrawer();draw();toast('Yanıt gönderildi (demo)');});
    draw();
  }

  /* ----- Genel Ayarlar ----- */
  const SKEY='kdrk-site-settings';
  const getS=()=>{try{return JSON.parse(localStorage.getItem(SKEY))||{}}catch(e){return{}}};
  const putS=s=>localStorage.setItem(SKEY,JSON.stringify(s));
  ICON.settings='<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>';
  RENDER.settings=function(){
    const s=getS(),on=!!s.maintenance;
    return `<div class="set-wrap">
      <div class="panel set-card mt-card ${on?'is-on':''}" data-mt-card>
        <div class="set-head"><div><h3 class="set-title">Bakım Modu</h3><p class="muted set-desc">Açıkken ziyaretçiler tüm sayfalarda bakım sayfasını görür. Siz panelden <b>Siteyi Gör</b> ile siteyi normal şekilde önizlemeye devam edersiniz.</p></div>
          <label class="switch set-switch"><input type="checkbox" data-mt-toggle ${on?'checked':''}/><span class="track"></span><span class="sw-label" data-mt-label>${on?'Açık':'Kapalı'}</span></label></div>
        <div class="mt-state" data-mt-state>${on?'<span class="chip red dot">Site bakımda</span><span class="muted">Ziyaretçiler bakım sayfasını görüyor'+(s.since?' · '+esc(s.since):'')+'</span>':'<span class="chip green dot">Site yayında</span><span class="muted">Ziyaretçiler siteyi normal görüyor</span>'}</div>
        <div class="set-actions"><a class="btn btn-ghost btn-sm" href="../Bakim.html?onizleme=1" target="_blank">${svg(ICON.eye,14)}Bakım sayfasını önizle</a><a class="btn btn-ghost btn-sm" href="../Anasayfa.html?onizleme=1" target="_blank">${svg(ICON.ext,14)}Siteyi önizle</a></div>
      </div>
      <div class="panel set-card">
        <h3 class="set-title">Bakım Sayfası Metni</h3>
        <div class="field"><label>Başlık</label><input data-mt-title value="${esc(s.title||'Sayfamız bakım aşamasında.')}"/></div>
        <div class="field"><label>Mesaj</label><textarea data-mt-msg rows="2">${esc(s.message||'Çok yakında sizlerle olacağız.')}</textarea></div>
        <div class="field"><label>Tahmini dönüş <span class="muted" style="text-transform:none;letter-spacing:0">(isteğe bağlı)</span></label><input type="datetime-local" data-mt-eta value="${esc(s.eta||'')}"/></div>
        ${readonly()?'':`<div class="set-actions"><button class="btn btn-primary btn-sm" data-mt-save>${svg(ICON.check,14)}Kaydet</button></div>`}
      </div>
      <p class="muted set-note">Not: Bu prototipte ayar tarayıcıda saklanır; canlı sitede sunucu tarafında tutulacak.</p>
    </div>`;
  };
  BIND.settings=function(){
    const t=$('[data-mt-toggle]');
    t.addEventListener('change',()=>{
      if(t.checked&&!confirm('Site bakıma alınsın mı? Ziyaretçiler yalnız bakım sayfasını görecek.')){t.checked=false;return;}
      const s=getS();s.maintenance=t.checked;s.since=t.checked?new Date().toLocaleString('tr-TR',{dateStyle:'short',timeStyle:'short'}):'';putS(s);
      go('settings');toast(t.checked?'Site bakıma alındı':'Site yeniden yayında');
    });
    $('[data-mt-save]')&&$('[data-mt-save]').addEventListener('click',()=>{const s=getS();s.title=$('[data-mt-title]').value.trim();s.message=$('[data-mt-msg]').value.trim();s.eta=$('[data-mt-eta]').value;putS(s);toast('Bakım sayfası metni kaydedildi');});
  };

  /* ----- Users ----- */
  RENDER.users=function(){
    return `<div class="toolbar"><div class="muted" style="font-size:13px">Panel kullanıcıları, rolleri ve modül erişim yetkileri.</div><div class="tb-spacer"></div><button class="btn btn-primary" data-add>${svg(ICON.plus)}Yeni Kullanıcı</button></div>
      <div class="panel"><table class="tbl"><thead><tr><th>Kullanıcı</th><th>Rol</th><th>Erişim</th><th>Durum</th><th></th></tr></thead><tbody data-rows></tbody></table></div>`;
  };
  BIND.users=function(){
    const draw=()=>{
      $('[data-rows]').innerHTML=DB.db.users.map(u=>{
        const cnt=Object.values(u.perms).filter(Boolean).length, rc={admin:'orange',editor:'blue',viewer:'gray'}[u.role];
        return `<tr><td><div class="cell-main"><span class="sb-avatar" style="width:38px;height:38px">${esc(u.name[0])}</span><div><div class="t-title">${esc(u.name)}</div><div class="t-sub mono">${esc(u.email)}</div></div></div></td><td><span class="chip ${rc}">${ROLE_LABEL[u.role]}</span></td><td class="mono muted">${cnt}/${MODULES.length} modül</td><td>${u.active?'<span class="chip green dot">Aktif</span>':'<span class="chip gray dot">Pasif</span>'}</td><td class="t-actions"><button class="btn-icon" data-edit="${u.id}">${svg(ICON.edit)}</button>${u.id==='u1'?'':`<button class="btn-icon del" data-del="${u.id}">${svg(ICON.trash)}</button>`}</td></tr>`;
      }).join('');
      $$('[data-edit]').forEach(b=>b.addEventListener('click',()=>userDrawer(b.dataset.edit,draw)));
      $$('[data-del]').forEach(b=>b.addEventListener('click',()=>{if(confirm('Kullanıcı silinsin mi?')){DB.db.users=DB.db.users.filter(x=>x.id!==b.dataset.del);DB.save();draw();toast('Silindi');}}));
    };
    $('[data-add]').addEventListener('click',()=>userDrawer(null,draw));
    draw();
  };
  function userDrawer(id,draw){
    const u=id?DB.db.users.find(x=>x.id===id):{name:'',email:'',role:'editor',active:true,perms:Object.fromEntries(MODULES.map(m=>[m.id,m.id==='dashboard']))};
    const rows=MODULES.map(m=>`<label class="switch" style="justify-content:space-between;width:100%;padding:8px 0;border-bottom:1px solid var(--border)"><span class="sw-label">${m.label}</span><span style="display:flex"><input type="checkbox" data-perm="${m.id}" ${u.perms[m.id]?'checked':''}/><span class="track"></span></span></label>`).join('');
    const body=`<div class="field"><label>Ad Soyad</label><input id="u-name" value="${esc(u.name)}"/></div>
      <div class="field"><label>E-posta</label><input id="u-email" value="${esc(u.email)}"/></div>
      <div class="field-row"><div class="field"><label>Rol</label><select id="u-role">${Object.entries(ROLE_LABEL).map(([v,l])=>`<option value="${v}" ${u.role===v?'selected':''}>${l}</option>`).join('')}</select></div><div class="field"><label>Durum</label><select id="u-active"><option value="1" ${u.active?'selected':''}>Aktif</option><option value="0" ${!u.active?'selected':''}>Pasif</option></select></div></div>
      <div class="field"><label>Modül Erişim Yetkileri</label><div class="muted" style="font-size:12px;margin-bottom:8px">Bu kullanıcının görebileceği bölümler.</div>${rows}</div>
      <div class="flex" style="gap:8px;margin-top:8px"><button class="btn btn-ghost btn-sm" data-preset="admin">Admin</button><button class="btn btn-ghost btn-sm" data-preset="editor">Editör</button><button class="btn btn-ghost btn-sm" data-preset="viewer">İzleme</button></div>`;
    const dp=openDrawer(id?'Kullanıcı Düzenle':'Yeni Kullanıcı',body,`<button class="btn btn-ghost" data-close-d>İptal</button><button class="btn btn-primary" data-save>${svg(ICON.check)}Kaydet</button>`);
    $('[data-close-d]',dp).addEventListener('click',closeDrawer);
    $$('[data-preset]',dp).forEach(b=>b.addEventListener('click',()=>{const r=b.dataset.preset;$('#u-role',dp).value=r;const map={admin:()=>true,editor:m=>m!=='users',viewer:()=>true};MODULES.forEach(m=>$(`[data-perm="${m.id}"]`,dp).checked=map[r](m.id));}));
    $('[data-save]',dp).addEventListener('click',()=>{
      const n=$('#u-name',dp).value.trim(); if(!n){alert('İsim gerekli');return;}
      const perms={}; MODULES.forEach(m=>perms[m.id]=$(`[data-perm="${m.id}"]`,dp).checked);
      const obj={name:n,email:$('#u-email',dp).value,role:$('#u-role',dp).value,active:$('#u-active',dp).value==='1',perms};
      if(id){Object.assign(DB.db.users.find(x=>x.id===id),obj);if(id===session.user.id)session.user=DB.db.users.find(x=>x.id===id);}else{obj.id=DB.uid();DB.db.users.push(obj);}
      DB.save(); closeDrawer(); toast('Kaydedildi'); draw(); if(id===session.user.id)renderShell();
    });
  }

  initTheme(); restore();
})();
