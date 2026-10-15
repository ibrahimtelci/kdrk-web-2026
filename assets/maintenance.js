(function(){try{
var p=new URLSearchParams(location.search);
if(p.get('onizleme')==='0')sessionStorage.removeItem('kdrk-preview');else if(p.has('onizleme'))sessionStorage.setItem('kdrk-preview','1');
var s=JSON.parse(localStorage.getItem('kdrk-site-settings')||'{}'),prev=sessionStorage.getItem('kdrk-preview')==='1';
if(!s.maintenance)return;
if(!prev){location.replace('Bakim.html');return;}
document.addEventListener('DOMContentLoaded',function(){var b=document.createElement('div');b.className='preview-bar';b.setAttribute('role','status');b.innerHTML='<span><b>ÖNİZLEME</b> · Ziyaretçiler şu an bakım sayfasını görüyor</span><a href="admin/Panel.html">Panele dön</a><a href="?onizleme=0">Önizlemeden çık</a>';document.body.appendChild(b);});
}catch(e){}})();
