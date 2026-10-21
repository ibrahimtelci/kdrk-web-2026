/*
 * Mobil uyum denetimi (CLAUDE.md §6a) — ek paket gerektirmez (Node 22+ ve Chrome).
 *   node tools/mobile-check.mjs [http://localhost:8000] [--shots]
 * Her sayfayı 390×844 (iPhone) ve 360×740 genişlikte açar; yatay taşma, 44px altı dokunma alanı,
 * 16px altı form yazısı ve 12px altı metin arar. --shots ile tools/screens/ altına ekran görüntüsü alır.
 */
import { spawn } from 'node:child_process';
import { mkdirSync, writeFileSync, mkdtempSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';

const BASE = (process.argv.find(a => a.startsWith('http')) || 'http://localhost:8000').replace(/\/$/, '');
const SHOTS = process.argv.includes('--shots');
const CHROME = process.env.CHROME || 'C:/Program Files/Google/Chrome/Application/chrome.exe';
const PAGES = ['/', '/faaliyetler', '/faaliyetler/ovit-yaylasi-skitur-kampi-2027', '/faaliyetler/kackar-zirve-tirmanisi-2026',
  '/disiplin/dagcilik', '/disiplin/egitim', '/kurumsal/hakkimizda', '/kurumsal/yonetim-kurulu', '/kurumsal/emegi-gecenler',
  '/galeri', '/duyurular', '/galeri?yil=2024', '/galeri/kackar-zirve-tirmanisi-2026', '/duyurular/vercenik-tepesi-bahar-tirmanisi-tamamlandi', '/bilgi-bankasi/kulup-uyeligi', '/bilgi-bankasi/yaz-dagcilik', '/iletisim', '/olmayan-sayfa',
  // '|' sonrası: ölçmeden önce tıklanacak öğe (açık modal denetimi)
  '/faaliyetler/ovit-yaylasi-skitur-kampi-2027|[data-open-apply]'];
const SIZES = [[390, 844], [360, 740]];

const port = 9333;
const profile = mkdtempSync(join(tmpdir(), 'kdrk-chrome-'));
const chrome = spawn(CHROME, ['--headless=new', `--remote-debugging-port=${port}`, `--user-data-dir=${profile}`, '--no-first-run', '--hide-scrollbars', 'about:blank'], { stdio: 'ignore' });
const sleep = ms => new Promise(r => setTimeout(r, ms));

let wsUrl;
for (let i = 0; i < 50 && !wsUrl; i++) {
  try { wsUrl = (await (await fetch(`http://127.0.0.1:${port}/json/list`)).json()).find(t => t.type === 'page').webSocketDebuggerUrl; }
  catch { await sleep(200); }
}
const ws = new WebSocket(wsUrl);
await new Promise(r => ws.addEventListener('open', r));
let id = 0; const pending = new Map(); const waiters = [];
ws.addEventListener('message', ev => {
  const m = JSON.parse(ev.data);
  if (m.id && pending.has(m.id)) { pending.get(m.id)(m); pending.delete(m.id); }
  if (m.method) waiters.filter(w => w.method === m.method).forEach(w => { w.resolve(m.params); waiters.splice(waiters.indexOf(w), 1); });
});
const send = (method, params = {}) => new Promise(r => { const i = ++id; pending.set(i, r); ws.send(JSON.stringify({ id: i, method, params })); });
const once = (method, ms = 15000) => Promise.race([new Promise(resolve => waiters.push({ method, resolve })), sleep(ms).then(() => { console.log('  ! ' + method + ' beklenirken zaman aşımı'); })]);
const evaluate = async expr => (await send('Runtime.evaluate', { expression: expr, returnByValue: true, awaitPromise: true })).result?.result?.value;

await send('Page.enable');

// Sayfada çalışan denetim — sonuçları dizi olarak döndürür
const AUDIT = `(() => {
  const vw = document.documentElement.clientWidth, out = [];
  if (document.documentElement.scrollWidth > vw + 1) {
    const wide = [...document.querySelectorAll('body *')].filter(el => {
      const r = el.getBoundingClientRect(); const cs = getComputedStyle(el);
      return r.width > 0 && (r.right > vw + 1 || r.left < -1) && cs.position !== 'fixed' && !el.closest('[aria-hidden="true"],.drawer,.modal-backdrop,.dlb,.lightbox');
    }).slice(0, 4).map(el => el.tagName.toLowerCase() + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\\s+/).join('.') : '') + ' (' + Math.round(el.getBoundingClientRect().right) + 'px)');
    out.push('YATAY TAŞMA ' + document.documentElement.scrollWidth + 'px > ' + vw + 'px: ' + wide.join(', '));
  }
  const vis = el => { const r = el.getBoundingClientRect(), cs = getComputedStyle(el); return r.width > 0 && r.height > 0 && cs.visibility !== 'hidden' && !el.closest('[hidden],[aria-hidden="true"],.drawer,.modal-backdrop:not(.is-open),.dlb,.lightbox,.hp-field'); };
  const small = [...document.querySelectorAll('a[href],button,input:not([type=hidden]),select,textarea,summary')].filter(vis).filter(el => {
    const r = el.getBoundingClientRect();
    if (el.matches('input[type=checkbox],input[type=radio]')) return false;
    if (el.closest('p,li,dd,.breadcrumb,.footer-contact,.footer-col ul,.form-checkbox .text') && el.tagName === 'A' && getComputedStyle(el).display === 'inline') return false; // metin içi link
    return r.height < 44 && r.width < 44 || r.height < 32;
  }).map(el => (el.getAttribute('aria-label') || el.textContent.trim().slice(0, 24) || el.tagName) + ' ' + Math.round(el.getBoundingClientRect().width) + '×' + Math.round(el.getBoundingClientRect().height));
  if (small.length) out.push('KÜÇÜK DOKUNMA ALANI (' + small.length + '): ' + [...new Set(small)].slice(0, 6).join(' | '));
  const inputs = [...document.querySelectorAll('input:not([type=hidden]):not([type=checkbox]),select,textarea')].filter(el => vis(el) && !el.closest('.hp-field') && parseFloat(getComputedStyle(el).fontSize) < 16);
  if (inputs.length) out.push('FORM YAZISI < 16px: ' + inputs.map(el => (el.name || el.id) + ' ' + getComputedStyle(el).fontSize).slice(0, 5).join(', '));
  const tiny = [...document.querySelectorAll('body *')].filter(el => el.childNodes.length && [...el.childNodes].some(n => n.nodeType === 3 && n.textContent.trim()) && vis(el) && parseFloat(getComputedStyle(el).fontSize) < 12);
  if (tiny.length) out.push('METİN < 12px: ' + tiny.slice(0, 4).map(el => el.textContent.trim().slice(0, 20) + ' ' + getComputedStyle(el).fontSize).join(', '));
  return out;
})()`;

if (SHOTS) mkdirSync('tools/screens', { recursive: true });
let problems = 0;
for (const [w, h] of SIZES) {
  await send('Emulation.setDeviceMetricsOverride', { width: w, height: h, deviceScaleFactor: 2, mobile: true });
  await send('Emulation.setTouchEmulationEnabled', { enabled: true });
  console.log(`\n── ${w}×${h} ──`);
  for (const entry of PAGES) {
    const [path, click] = entry.split('|');
    const loaded = once('Page.loadEventFired');
    await send('Page.navigate', { url: BASE + path });
    await loaded;
    await evaluate(`document.querySelectorAll('.reveal').forEach(e => e.classList.add('is-visible')); new Promise(r => setTimeout(r, 400))`);
    if (click) await evaluate(`document.querySelector(${JSON.stringify(click)}).click(); new Promise(r => setTimeout(r, 500))`);
    const issues = await evaluate(AUDIT) || ['denetim çalışmadı'];
    problems += issues.length;
    console.log((issues.length ? '✗ ' : '✓ ') + entry + (issues.length ? '\n    ' + issues.join('\n    ') : ''));
    if (SHOTS && w === 390) {
      // Okunabilir olsun diye sayfa 1200px'lik dilimler hâlinde kaydedilir: ad-1.jpg, ad-2.jpg …
      const full = await send('Page.getLayoutMetrics');
      const total = click ? h : Math.min(Math.ceil(full.result.cssContentSize.height), 9600);
      const name = (path.replace(/^\//, '').replace(/[/?&=]/g, '_') || 'home') + (click ? '_modal' : '');
      for (let y = 0, n = 1; y < total; y += 1200, n++) {
        const shot = await send('Page.captureScreenshot', { format: 'jpeg', quality: 70, captureBeyondViewport: true, clip: { x: 0, y, width: w, height: Math.min(1200, total - y), scale: 0.5 } });
        writeFileSync(`tools/screens/${name}-${n}.jpg`, Buffer.from(shot.result.data, 'base64'));
      }
    }
  }
}
// Panel: KDRK_ADMIN="eposta:parola" verilirse giriş yapıp panel sayfalarını da denetler
if (process.env.KDRK_ADMIN) {
  const [email, pass] = process.env.KDRK_ADMIN.split(':');
  const ADMIN = ['/yonetim', '/yonetim/faaliyetler', '/yonetim/faaliyetler/yeni', '/yonetim/faaliyetler/yeni?pencere=1', '/yonetim/galeri/yeni?pencere=1', process.env.KDRK_ADMIN_ACT || '/yonetim/faaliyetler/1',
    (process.env.KDRK_ADMIN_ACT || '/yonetim/faaliyetler/1') + '/basvurular', '/yonetim/galeri', '/yonetim/galeri/yeni', '/yonetim/duyurular', '/yonetim/duyurular/yeni', '/yonetim/duyurular/1', '/yonetim/slaytlar', '/yonetim/slaytlar?sekme=faaliyet', '/yonetim/slaytlar?sekme=haber', '/yonetim/slaytlar/yeni', '/yonetim/slaytlar/1', process.env.KDRK_ADMIN_ALBUM || '/yonetim/galeri/1',
    '/yonetim/mesajlar', '/yonetim/ayarlar', '/yonetim/kullanicilar', '/yonetim/hesap',
    '/yonetim|[data-menu]'];
  await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 2, mobile: true });
  let loaded = once('Page.loadEventFired');
  await send('Page.navigate', { url: BASE + '/yonetim/giris' });
  await loaded;
  loaded = once('Page.loadEventFired');
  await evaluate(`document.querySelector('#lg-email').value=${JSON.stringify(email)};document.querySelector('#lg-pass').value=${JSON.stringify(pass)};document.querySelector('form').submit()`);
  await loaded;
  console.log('\n── Panel 390×844 ──');
  for (const entry of ADMIN) {
    const [path, click] = entry.split('|');
    const l = once('Page.loadEventFired');
    await send('Page.navigate', { url: BASE + path });
    await l;
    await evaluate('new Promise(r => setTimeout(r, 300))');
    if (click) await evaluate(`document.querySelector(${JSON.stringify(click)}).click(); new Promise(r => setTimeout(r, 400))`);
    const issues = await evaluate(AUDIT) || ['denetim çalışmadı'];
    problems += issues.length;
    console.log((issues.length ? '✗ ' : '✓ ') + entry + (issues.length ? '\n    ' + issues.join('\n    ') : ''));
    if (SHOTS) {
      const full = await send('Page.getLayoutMetrics');
      const total = click ? 844 : Math.min(Math.ceil(full.result.cssContentSize.height), 4800);
      const name = 'panel' + (path.replace(/\/yonetim/, '').replace(/\//g, '_') || '_home') + (click ? '_menu' : '');
      for (let y = 0, n = 1; y < total; y += 1200, n++) {
        const shot = await send('Page.captureScreenshot', { format: 'jpeg', quality: 70, captureBeyondViewport: true, clip: { x: 0, y, width: 390, height: Math.min(1200, total - y), scale: 0.5 } });
        writeFileSync(`tools/screens/${name}-${n}.jpg`, Buffer.from(shot.result.data, 'base64'));
      }
    }
  }
}

console.log(`\n${problems ? problems + ' sorun bulundu' : 'Sorun yok'}`);
ws.close(); chrome.kill();
process.exit(problems ? 1 : 0);
