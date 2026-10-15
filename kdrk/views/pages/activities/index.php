<?php
/**
 * Faaliyet takvimi — yaklaşan + geçmiş, kart ve takvim görünümü.
 * Filtre/sekme/takvim betikleri kartların data-* özniteliklerinden okur.
 * @var array $activities
 */
?>
<section class="list-header" data-screen-label="01 Header"><div class="container"><nav class="breadcrumb" aria-label="Breadcrumb"><a href="<?= e(url('home')) ?>">Anasayfa</a><span class="sep" aria-hidden="true">›</span><span class="current" aria-current="page">Faaliyetler</span></nav><span class="eyebrow">Faaliyet Takvimi</span><h1>Faaliyetler</h1><p>Yaklaşan faaliyetlere başvur; geçmiş faaliyetlerin raporlarına ve fotoğraflarına göz at.</p><div class="header-meta"><div class="list-tabs" role="group" aria-label="Zaman aralığı"><button class="list-tab is-active" data-tab="yaklasan" aria-pressed="true">Yaklaşan <span class="count" data-tab-count="yaklasan"></span></button><button class="list-tab" data-tab="gecmis" aria-pressed="false">Geçmiş <span class="count" data-tab-count="gecmis"></span></button></div><span class="mono" data-header-meta></span></div></div></section>
<section class="filter-bar" data-screen-label="02 Filters"><div class="container filter-inner"><div class="filter-pills" role="group" aria-label="Kategori"><button class="filter-pill is-active" data-filter="all" aria-pressed="true">Tümü <span class="count" data-count="all"></span></button><button class="filter-pill" data-filter="gunubirlik" aria-pressed="false">Günübirlik Doğa Yürüyüşü <span class="count" data-count="gunubirlik"></span></button><button class="filter-pill" data-filter="dagcilik" aria-pressed="false">Dağcılık <span class="count" data-count="dagcilik"></span></button><button class="filter-pill" data-filter="spor-tirmanis" aria-pressed="false">Spor Tırmanış <span class="count" data-count="spor-tirmanis"></span></button><button class="filter-pill" data-filter="dag-kayagi" aria-pressed="false">Dağ Kayağı <span class="count" data-count="dag-kayagi"></span></button><button class="filter-pill" data-filter="egitim" aria-pressed="false">Eğitim Programı <span class="count" data-count="egitim"></span></button><button class="filter-pill" data-filter="sosyal" aria-pressed="false">Sosyal-Kültürel <span class="count" data-count="sosyal"></span></button><button class="filter-pill" data-filter="arama-kurtarma" aria-pressed="false">Arama Kurtarma <span class="count" data-count="arama-kurtarma"></span></button></div><div class="filter-tools"><div class="search-input"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg><input type="search" placeholder="Faaliyet veya yer ara…" data-search aria-label="Faaliyet ara"></div><div class="sort-dropdown" data-sort><button class="sort-trigger" data-sort-trigger aria-haspopup="true" aria-expanded="false"><span class="label">Sırala:</span><span data-sort-label>Tarih (yakın önce)</span><svg class="chev" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button><div class="sort-menu"><button data-sort-by="date-asc" class="is-selected">Tarih (yakın önce)</button><button data-sort-by="date-desc">Tarih (uzak önce)</button><button data-sort-by="difficulty">Zorluk (kolaydan zora)</button><button data-sort-by="popular">Doluluk (dolu önce)</button></div></div></div></div></section>
<section class="listing activities" data-screen-label="03 Listing"><div class="container"><div class="listing-meta"><div class="count-line" aria-live="polite"><strong data-result-count></strong> <span data-result-text></span></div><div class="view-toggle" role="group" aria-label="Görünüm"><button class="view-btn is-active" data-view="kart" aria-pressed="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="3" width="7.5" height="7.5" rx="1.5"/><rect x="3" y="13.5" width="7.5" height="7.5" rx="1.5"/><rect x="13.5" y="13.5" width="7.5" height="7.5" rx="1.5"/></svg>Kart</button><button class="view-btn" data-view="takvim" aria-pressed="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="4.5" width="18" height="16.5" rx="2"/><path d="M3 9.5h18M8 2.5v4M16 2.5v4"/></svg>Takvim</button></div></div><div class="cal" data-cal hidden><div class="cal-head"><button class="cal-nav" data-cal-prev aria-label="Önceki ay"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button><h2 class="cal-title" data-cal-title aria-live="polite"></h2><button class="cal-nav" data-cal-next aria-label="Sonraki ay"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button><span class="cal-count mono" data-cal-count></span></div><div class="cal-grid" data-cal-grid></div><ol class="cal-agenda" data-cal-agenda></ol></div><div class="listing-grid activities-grid" data-grid>
<?php foreach ($activities as $a): ?>
  <?= $this->partial('partials/activity-card', ['a' => $a]) ?>
<?php endforeach ?>
</div><div class="empty-state" data-empty hidden><div class="topo-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M3 20l5-9 4 6 3-4 6 7"/><path d="M3 20h18"/></svg></div><span class="stamp">0 SONUÇ</span><h3 data-empty-title>Bu kategoride yaklaşan faaliyet yok.</h3><p>Filtreleri temizleyerek tüm faaliyetleri görebilir ya da geçmiş faaliyetlere bakabilirsin.</p><button class="btn btn-primary" data-clear-filters>Filtreleri temizle</button></div></div></section>

<?php $this->section('scripts') ?>
<script>
(function(){
const MO=['OCA','ŞUB','MAR','NİS','MAY','HAZ','TEM','AĞU','EYL','EKİ','KAS','ARA'];
const params=new URLSearchParams(location.search);
const grid=document.querySelector('[data-grid]'),cards=[...grid.querySelectorAll('.activity-card')],empty=document.querySelector('[data-empty]');
const pills=[...document.querySelectorAll('.filter-pill')],tabs=[...document.querySelectorAll('.list-tab')],search=document.querySelector('[data-search]');
let view=params.get('view')==='takvim'?'takvim':(localStorage.getItem('kdrk-faaliyet-view')||'kart'),calY=null,calM=null;
let tab=params.get('tab')==='gecmis'?'gecmis':'yaklasan',filter=params.get('cat')||'all',sort=tab==='gecmis'?'date-desc':'date-asc',term='';
const inTab=c=>(c.dataset.status==='done')===(tab==='gecmis');
const txt=c=>(c.querySelector('h3').textContent+' '+c.querySelector('p').textContent+' '+c.querySelector('.activity-loc').textContent).toLocaleLowerCase('tr');
const fmt=d=>MO[+d.slice(5,7)-1]+' '+d.slice(0,4);
const calEl=document.querySelector('[data-cal]'),viewBtns=[...document.querySelectorAll('[data-view]')];
const MONTHS=['Ocak','Şubat','Mart','Nisan','Mayıs','Haziran','Temmuz','Ağustos','Eylül','Ekim','Kasım','Aralık'],WD=['PZT','SAL','ÇAR','PER','CUM','CMT','PAZ'];
const esc=s=>s.replace(/[&<>"]/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[ch]));
const evOf=c=>{const d=c.querySelector('.activity-difficulty'),s=c.querySelector('.activity-status');return{date:c.dataset.date,href:c.getAttribute('href'),title:c.querySelector('h3').textContent,lv:d?getComputedStyle(d).getPropertyValue('--lv').trim():'',diff:d?d.textContent.replace(/\d\/\d/,'').trim():'',st:c.dataset.status,stTxt:s?s.textContent:''}};
const ymKey=(y,m)=>y+'-'+String(m+1).padStart(2,'0');
function renderCal(vis){
const evs=vis.map(evOf).sort((a,b)=>a.date.localeCompare(b.date));
const now=new Date(),todayKey=now.toISOString().slice(0,10);
if(calY===null||(!noJump&&!evs.some(e=>e.date.startsWith(ymKey(calY,calM))))){const pick=(tab==='gecmis'?[...evs].reverse():evs).find(e=>tab==='gecmis'||e.date>=todayKey.slice(0,7))||evs[0];if(pick){calY=+pick.date.slice(0,4);calM=+pick.date.slice(5,7)-1}else{calY=now.getFullYear();calM=now.getMonth()}}
const key=ymKey(calY,calM),monthEvs=evs.filter(e=>e.date.startsWith(key));
document.querySelector('[data-cal-title]').textContent=MONTHS[calM]+' '+calY;
document.querySelector('[data-cal-count]').textContent=monthEvs.length?monthEvs.length+' FAALİYET':'BU AY FAALİYET YOK';
const first=new Date(calY,calM,1),offset=(first.getDay()+6)%7,days=new Date(calY,calM+1,0).getDate(),cells=Math.ceil((offset+days)/7)*7;
const chip=e=>'<a class="cal-event st-'+e.st+'" href="'+esc(e.href)+'" style="--lv:'+(e.lv||'var(--accent-secondary)')+'" title="'+esc(e.title+' · '+e.diff+' · '+e.stTxt)+'"><span class="dot" aria-hidden="true"></span><span class="t">'+esc(e.title)+'</span>'+(['full','closed','few','done'].includes(e.st)?'<span class="s">'+esc(e.stTxt)+'</span>':'')+'</a>';
let g=WD.map(w=>'<div class="cal-wd" aria-hidden="true">'+w+'</div>').join('');
for(let i=0;i<cells;i++){const d=i-offset+1,inM=d>=1&&d<=days;if(!inM){g+='<div class="cal-cell is-out" aria-hidden="true"></div>';continue}
const dk=key+'-'+String(d).padStart(2,'0'),de=monthEvs.filter(e=>e.date===dk);
g+='<div class="cal-cell'+(dk===todayKey?' is-today':'')+(de.length?' has-ev':'')+'"><span class="cal-day">'+d+'</span>'+de.map(chip).join('')+'</div>'}
document.querySelector('[data-cal-grid]').innerHTML=g;
document.querySelector('[data-cal-agenda]').innerHTML=monthEvs.length?monthEvs.map(e=>{const dt=new Date(e.date+'T00:00');return '<li><span class="ag-date"><span class="d">'+dt.getDate()+'</span><span class="w">'+WD[(dt.getDay()+6)%7]+'</span></span>'+chip(e)+'</li>'}).join(''):'<li class="ag-empty">Bu ayda faaliyet yok. Diğer aylara geçmek için okları kullan.</li>';
}
document.querySelector('[data-cal-prev]').addEventListener('click',()=>{calM--;if(calM<0){calM=11;calY--}renderCalOnly()});
document.querySelector('[data-cal-next]').addEventListener('click',()=>{calM++;if(calM>11){calM=0;calY++}renderCalOnly()});
let noJump=false;
function renderCalOnly(){noJump=true;renderCal(cards.filter(c=>!c.hidden).sort((a,b)=>a.style.order-b.style.order));noJump=false}
viewBtns.forEach(b=>b.addEventListener('click',()=>{view=b.dataset.view;localStorage.setItem('kdrk-faaliyet-view',view);render()}));
function render(){
const q=term.toLocaleLowerCase('tr').trim();
const base=cards.filter(c=>inTab(c)&&(!q||txt(c).includes(q)));
pills.forEach(p=>{const k=p.dataset.filter;p.querySelector('.count').textContent=base.filter(c=>k==='all'||c.dataset.cat===k).length;const on=k===filter;p.classList.toggle('is-active',on);p.setAttribute('aria-pressed',on)});
tabs.forEach(t=>{const on=t.dataset.tab===tab;t.classList.toggle('is-active',on);t.setAttribute('aria-pressed',on);t.querySelector('.count').textContent=cards.filter(c=>(c.dataset.status==='done')===(t.dataset.tab==='gecmis')).length});
let vis=base.filter(c=>filter==='all'||c.dataset.cat===filter);
vis.sort((a,b)=>sort==='date-asc'?a.dataset.date.localeCompare(b.dataset.date):sort==='date-desc'?b.dataset.date.localeCompare(a.dataset.date):sort==='difficulty'?a.dataset.difficulty-b.dataset.difficulty:b.dataset.fill-a.dataset.fill);
cards.forEach(c=>c.hidden=true);vis.forEach((c,i)=>{c.hidden=false;c.style.order=i;c.classList.add('is-visible')});
empty.hidden=vis.length>0;grid.hidden=!vis.length||view==='takvim';calEl.hidden=!vis.length||view!=='takvim';viewBtns.forEach(b=>{const on=b.dataset.view===view;b.classList.toggle('is-active',on);b.setAttribute('aria-pressed',on)});if(view==='takvim')renderCal(vis);
document.querySelector('[data-empty-title]').textContent=tab==='gecmis'?'Bu seçimde geçmiş faaliyet kaydı yok.':'Bu seçimde yaklaşan faaliyet yok.';
document.querySelector('[data-result-count]').textContent=vis.length;
document.querySelector('[data-result-text]').textContent=tab==='gecmis'?'GEÇMİŞ FAALİYET':'YAKLAŞAN FAALİYET';
const all=cards.filter(inTab).map(c=>c.dataset.date).sort();
document.querySelector('[data-header-meta]').textContent=all.length?all.length+(tab==='gecmis'?' GEÇMİŞ':' YAKLAŞAN')+' · '+fmt(all[0])+' → '+fmt(all[all.length-1]):'';
document.querySelectorAll('[data-sort-by]').forEach(b=>b.classList.toggle('is-selected',b.dataset.sortBy===sort));
document.querySelector('[data-sort-label]').textContent=document.querySelector('[data-sort-by="'+sort+'"]').textContent;
const u=new URLSearchParams();if(tab==='gecmis')u.set('tab','gecmis');if(filter!=='all')u.set('cat',filter);if(view==='takvim')u.set('view','takvim');history.replaceState(null,'',location.pathname+(u.toString()?'?'+u:''));
}
pills.forEach(p=>p.addEventListener('click',()=>{filter=p.dataset.filter;render()}));
tabs.forEach(t=>t.addEventListener('click',()=>{tab=t.dataset.tab;sort=tab==='gecmis'?'date-desc':'date-asc';render()}));
let st;search.addEventListener('input',e=>{clearTimeout(st);st=setTimeout(()=>{term=e.target.value;render()},150)});
const dd=document.querySelector('[data-sort]'),trig=dd.querySelector('[data-sort-trigger]');
const setDD=v=>{dd.classList.toggle('is-open',v);trig.setAttribute('aria-expanded',v)};
trig.addEventListener('click',e=>{e.stopPropagation();setDD(!dd.classList.contains('is-open'))});
document.addEventListener('click',()=>setDD(false));
dd.addEventListener('keydown',e=>{if(e.key==='Escape'){setDD(false);trig.focus()}});
dd.querySelectorAll('[data-sort-by]').forEach(b=>b.addEventListener('click',()=>{sort=b.dataset.sortBy;setDD(false);trig.focus();render()}));
document.querySelector('[data-clear-filters]').addEventListener('click',()=>{filter='all';term='';search.value='';render()});
render();
})();
</script>
<?php $this->end() ?>
