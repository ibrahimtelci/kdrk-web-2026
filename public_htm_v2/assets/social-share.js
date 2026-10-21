/* Faaliyet verisinden 1080 × 1350 Instagram kartı (tasarım: Instagram Kart.html). Paket ve dış servis gerektirmez. */
(() => {
  'use strict';
  if (typeof HTMLDialogElement !== 'function') return;
  const buttons = document.querySelectorAll('[data-social-share]');
  if (!buttons.length) return;
  // Gönderi her zaman markanın koyu paletini kullanır; arayüz temasından bağımsızdır.
  const color = { bg: '#0B1220', bg2: '#111B2C', surface: '#1A2638', border: '#1F2A3D', text: '#F4F6FA', muted: '#94A3B8', accent: '#F97316', onAccent: '#0B1220', success: '#10B981', warning: '#F59E0B', danger: '#EF4444' };
  const dlg = document.createElement('dialog');
  dlg.className = 'social-dialog';
  dlg.setAttribute('aria-labelledby', 'social-title');
  dlg.innerHTML = '<header><h2 id="social-title">Instagram gönderisi hazırla</h2><button class="social-close" type="button" aria-label="Kapat">×</button></header><div class="social-layout"><canvas class="social-preview" width="1080" height="1350" role="img" aria-label="Faaliyet gönderisi önizlemesi"></canvas><img class="social-preview" data-social-image width="1080" height="1350" alt="Hazırlanan faaliyet gönderisi" hidden><div class="social-tools"><p>1080 × 1350 piksel · Dikey gönderi</p><label for="social-caption">Gönderi açıklaması</label><textarea id="social-caption"></textarea><div class="social-actions"><button type="button" data-social-send hidden>Görseli paylaş</button><button type="button" data-social-download disabled>Görseli indir</button><button type="button" data-social-copy>Açıklamayı kopyala</button><button type="button" data-social-retry hidden>Yeniden hazırla</button></div><p>Önce açıklamayı kopyala; Instagram’da gönderiyi oluştururken yapıştır. Paylaşım menüsünde Instagram görünmüyorsa görseli indirip uygulamadan seç.</p><p class="social-feedback" role="status" aria-live="polite"></p></div></div>';
  document.body.append(dlg);
  const $ = s => dlg.querySelector(s), canvas = $('canvas'), ctx = canvas.getContext('2d');
  const previewImage = $('[data-social-image]');
  const caption = $('textarea'), feedback = $('.social-feedback'), send = $('[data-social-send]'), download = $('[data-social-download]'), retry = $('[data-social-retry]');
  let current, file, objectUrl, focus, version = 0;
  function clean() { if (objectUrl) URL.revokeObjectURL(objectUrl); objectUrl = null; file = null; }
  function loadImage(src) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      const timer = setTimeout(() => { img.src = ''; reject(Error('timeout')); }, 15000);
      img.crossOrigin = 'anonymous';
      img.onload = () => { clearTimeout(timer); resolve(img); };
      img.onerror = () => { clearTimeout(timer); reject(Error('image')); };
      img.src = src;
    });
  }
  // Fotoğraf hedefi yüksekliğin %55'i; uzun başlık ve iki rehberde alttaki bilgiler sığsın diye kısalır.
  const W = 1080, H = 1350, PAD = 56, PHOTO_MAX = 743, PHOTO_MIN = 608, FOOT_LINE = H - 71;
  const MONTHS = ['OCA', 'ŞUB', 'MAR', 'NİS', 'MAY', 'HAZ', 'TEM', 'AĞU', 'EYL', 'EKİ', 'KAS', 'ARA'];
  const DAYS = ['PAZAR', 'PAZARTESİ', 'SALI', 'ÇARŞAMBA', 'PERŞEMBE', 'CUMA', 'CUMARTESİ'];
  // Zorluk kademesi renkleri (CLAUDE.md §1); Kolay-Orta, Kolay ile Orta'nın oklch karışımıdır.
  const LEVEL_COLOR = { easy: '#34D399', 'easy-medium': '#B9B84A', medium: '#FB923C', 'medium-hard': '#F87171', 'very-hard': '#DC2626', education: '#38BDF8', social: '#CBD5E1' };
  const STATUS_COLOR = { open: color.success, few: color.warning, full: color.danger, closed: color.muted, postponed: color.warning, cancelled: color.danger, done: color.muted, past: color.muted };
  const ICONS = {
    pin: 'M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z',
    clock: 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm1 10.4 3.6 2.1-1 1.7L11 13V6.5h2z',
    route: 'M5.5 16a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM18.5 3a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM8 17.5h7.5a3 3 0 0 0 0-6h-7a3 3 0 0 1 0-6H16V7.5H8.5a1 1 0 0 0 0 2h7a5 5 0 0 1 0 10H8z',
    boot: 'M6 3h6v6.5l5.2 2.6c2.2 1.1 3.8 2.6 3.8 4.9V18H3V5.5A2.5 2.5 0 0 1 5.5 3zM2 19.5h20V21H2z'
  };
  // Mevsim rozeti başlangıç tarihinden seçilir (tasarım: Mevsim kutuları).
  function seasonBadge(month, x, y) {
    const s = month === 11 || month < 2 ? 'kis' : month < 5 ? 'ilkbahar' : month < 8 ? 'yaz' : 'sonbahar';
    ctx.save(); ctx.shadowColor = 'rgba(11,18,32,.35)'; ctx.shadowBlur = 18; ctx.shadowOffsetY = 6;
    ctx.fillStyle = color.bg; ctx.beginPath(); ctx.arc(x, y, 32, 0, Math.PI * 2); ctx.fill(); ctx.restore();
    ctx.strokeStyle = color.accent; ctx.lineWidth = 3; ctx.beginPath(); ctx.arc(x, y, 30.5, 0, Math.PI * 2); ctx.stroke();
    ctx.save(); ctx.translate(x - 17, y - 17); ctx.scale(34 / 24, 34 / 24);
    ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.fillStyle = '#fff'; ctx.strokeStyle = '#fff';
    if (s === 'kis') {
      ctx.lineWidth = 1.8;
      ctx.stroke(new Path2D('M12 2v20M3.3 7l17.4 10M3.3 17l17.4-10M9 3.5l3 2.5 3-2.5M9 20.5l3-2.5 3 2.5M3.6 10.6l3.4-.6-1.2-3.2M20.4 13.4l-3.4.6 1.2 3.2M3.6 13.4l3.4.6-1.2 3.2M20.4 10.6l-3.4-.6 1.2-3.2'));
    } else if (s === 'ilkbahar') {
      for (const [cx, cy] of [[12, 6.5], [17.2, 10.3], [15.2, 16.4], [8.8, 16.4], [6.8, 10.3]]) { ctx.beginPath(); ctx.arc(cx, cy, 3.6, 0, Math.PI * 2); ctx.fill(); }
      ctx.fillStyle = color.accent; ctx.beginPath(); ctx.arc(12, 12, 3, 0, Math.PI * 2); ctx.fill();
    } else if (s === 'yaz') {
      ctx.beginPath(); ctx.arc(12, 12, 4.6, 0, Math.PI * 2); ctx.fill();
      ctx.lineWidth = 2; ctx.stroke(new Path2D('M12 1.8v2.6M12 19.6v2.6M1.8 12h2.6M19.6 12h2.6M4.8 4.8l1.8 1.8M17.4 17.4l1.8 1.8M4.8 19.2l1.8-1.8M17.4 6.6l1.8-1.8'));
    } else {
      ctx.fill(new Path2D('M20.5 3.5C11 3.5 4 8 4 15c0 1.8.5 3.3 1.3 4.5C7 20.5 8.6 21 10.5 21c6.5 0 10-7.5 10-17.5z'));
      ctx.strokeStyle = color.bg; ctx.lineWidth = 1.8; ctx.stroke(new Path2D('M3 22c3-5 7-9.5 13-13'));
    }
    ctx.restore();
  }
  function setFont(size, weight, font, spacing = 0) {
    ctx.font = `${weight} ${size}px ${font}, sans-serif`;
    if ('letterSpacing' in ctx) ctx.letterSpacing = spacing ? (size * spacing).toFixed(2) + 'px' : '0px';
  }
  function text(value, x, y, size, weight = 600, fill = color.text, font = 'Inter', spacing = 0, align = 'left') {
    setFont(size, weight, font, spacing); ctx.fillStyle = fill; ctx.textAlign = align; ctx.fillText(value, x, y); ctx.textAlign = 'left';
    return ctx.measureText(value).width;
  }
  function measure(value, size, weight, font, spacing = 0) { setFont(size, weight, font, spacing); return ctx.measureText(value).width; }
  function fit(value, width, size, weight, font, spacing = 0) {
    let v = String(value || ''); setFont(size, weight, font, spacing);
    if (ctx.measureText(v).width <= width) return v;
    while (v && ctx.measureText(v + '…').width > width) v = v.slice(0, -1);
    return v.trimEnd() + '…';
  }
  function lines(value, width, size, weight = 600, font = 'Inter') {
    setFont(size, weight, font);
    const out = []; let line = '';
    for (const word of String(value || '').trim().split(/\s+/)) {
      if (line && ctx.measureText(line + ' ' + word).width > width) { out.push(line); line = ''; }
      if (ctx.measureText(word).width <= width) { line += (line ? ' ' : '') + word; continue; }
      for (const char of word) {
        if (line && ctx.measureText(line + char).width > width) { out.push(line); line = ''; }
        line += char;
      }
    }
    if (line.trim()) out.push(line.trim()); return out;
  }
  function block(value, x, y, width, size, count, lineHeight, weight = 600, fill = color.text, font = 'Inter') {
    const all = lines(value, width, size, weight, font), shown = all.slice(0, count);
    if (all.length > count) shown[count - 1] = fit(shown[count - 1] + ' …', width, size, weight, font).replace(/\s*…+$/, '…');
    shown.forEach((s, i) => text(s, x, y + i * lineHeight, size, weight, fill, font));
    return shown.length;
  }
  function round(x, y, w, h, r) {
    ctx.beginPath();
    if (ctx.roundRect) { ctx.roundRect(x, y, w, h, r); return; }
    ctx.moveTo(x + r, y); ctx.arcTo(x + w, y, x + w, y + h, r); ctx.arcTo(x + w, y + h, x, y + h, r); ctx.arcTo(x, y + h, x, y, r); ctx.arcTo(x, y, x + w, y, r); ctx.closePath();
  }
  function icon(kind, x, y, size, fill) {
    ctx.save(); ctx.translate(x, y); ctx.scale(size / 24, size / 24); ctx.fillStyle = fill; ctx.fill(new Path2D(ICONS[kind])); ctx.restore();
  }
  function dms(value, pos, neg) {
    const a = Math.abs(value), deg = Math.floor(a), min = Math.floor((a - deg) * 60);
    return `${deg}°${String(min).padStart(2, '0')}'${value >= 0 ? pos : neg}`;
  }
  // "Rize / Çamlıhemşin · Kaçkar Dağı" → ["Kaçkar Dağı", "Rize / Çamlıhemşin"]
  function splitPlace(value) {
    const v = String(value || '').trim();
    if (v.includes(' · ')) { const p = v.split(' · '); return [p.slice(1).join(' · '), p[0]]; }
    if (v.includes(' / ')) { const p = v.split(' / '); return [p.pop(), p.join(' / ')]; }
    return [v || 'Belirtilmedi', ''];
  }
  // "Rize otogarı · 06:30" → ["06:30", "Rize otogarı"]
  function splitMeeting(value) {
    const v = String(value || '').trim(), m = v.match(/\b([01]?\d|2[0-3])[:.]([0-5]\d)\b/);
    if (!v) return ['Belirtilmedi', ''];
    if (!m) return [v, ''];
    return [m[1].padStart(2, '0') + ':' + m[2], v.replace(m[0], '').replace(/^[\s·,–-]+|[\s·,–-]+$/g, '')];
  }
  function captionFor(d) {
    return [d.title, d.summary, `Tarih: ${d.date}`, `Konum: ${d.location}`, `Kategori: ${d.category}`, `Mesafe: ${d.distance}`, `Zorluk: ${d.difficulty}`, `Durum: ${d.status}`, d.meeting ? `Buluşma: ${d.meeting}` : '', d.published ? `Detaylar: ${new URL(d.url, location.href).href}` : 'Bu faaliyet henüz yayında değil.', d.guides && d.guides.length ? 'Rehberler:\n' + d.guides.map(p => p.name + ' · ' + p.phone).join('\n') : '', '#KDRK #DoğaSporları'].filter(Boolean).join('\n\n');
  }
  async function render(d, token) {
    retry.hidden = true; download.disabled = true; send.hidden = true; clean();
    feedback.textContent = 'Görsel hazırlanıyor…';
    canvas.hidden = false; previewImage.hidden = true;
    ctx.fillStyle = color.bg; ctx.fillRect(0, 0, W, H);
    const fontLoad = document.fonts ? Promise.all([document.fonts.load('800 76px Manrope'), document.fonts.load('700 28px Manrope'), document.fonts.load('400 26px Inter'), document.fonts.load('500 20px Inter'), document.fonts.load('500 16px "JetBrains Mono"'), document.fonts.load('600 18px "JetBrains Mono"')]) : Promise.resolve();
    let photo = null, logo = null, topo = null, photoFailed = false;
    await Promise.all([fontLoad.catch(() => {}), d.image ? loadImage(d.image).then(i => photo = i).catch(() => { photoFailed = true; }) : Promise.resolve(), loadImage(d.logo).then(i => logo = i).catch(() => {}), d.topo ? loadImage(d.topo).then(i => topo = i).catch(() => {}) : Promise.resolve()]);
    if (token !== version || !dlg.open) return;
    const mono = '"JetBrains Mono"', right = W - PAD, inner = W - PAD * 2;
    ctx.fillStyle = color.bg; ctx.fillRect(0, 0, W, H);

    // Önce alt bölüm ölçülür: başlık en fazla 2, açıklama 1 satır; kalan yükseklik fotoğrafındır.
    let size = 76;
    while (size > 52 && lines(d.title, inner, size, 800, 'Manrope').length > 2) size -= 2;
    const titleLines = lines(d.title, inner, size, 800, 'Manrope');
    if (titleLines.length > 2) { titleLines.length = 2; titleLines[1] = fit(titleLines[1] + ' …', inner, size, 800, 'Manrope').replace(/\s*…+$/, '…'); }
    const lead = size * 0.98, guides = (d.guides || []).slice(0, 2);
    const below = 4 + 22 + 44 + 10 + titleLines.length * lead + (d.summary ? 10 + 37.7 : 0) + 20 + 173 + 14 + (guides.length > 1 ? 80 : 50) + 14;
    const PHOTO_H = Math.round(Math.max(PHOTO_MIN, Math.min(PHOTO_MAX, FOOT_LINE - below)));

    // Fotoğraf: odak biraz yukarıda; üstte marka ve tarihi okutan koyu geçiş.
    ctx.fillStyle = color.bg2; ctx.fillRect(0, 0, W, PHOTO_H);
    if (photo) {
      const s = Math.max(W / photo.width, PHOTO_H / photo.height), pw = photo.width * s, ph = photo.height * s;
      ctx.drawImage(photo, (W - pw) / 2, (PHOTO_H - ph) * 0.35, pw, ph);
    }
    const shade = ctx.createLinearGradient(0, 0, 0, PHOTO_H);
    shade.addColorStop(0, 'rgba(11,18,32,.88)'); shade.addColorStop(.28, 'rgba(11,18,32,.6)'); shade.addColorStop(.52, 'rgba(11,18,32,0)');
    ctx.fillStyle = shade; ctx.fillRect(0, 0, W, PHOTO_H);

    // Marka: beyaz daire içinde amblem, yanında kısa ad ve tam ad.
    ctx.save(); ctx.shadowColor = 'rgba(11,18,32,.35)'; ctx.shadowBlur = 28; ctx.shadowOffsetY = 8;
    ctx.fillStyle = '#fff'; ctx.beginPath(); ctx.arc(PAD + 86, PAD + 86, 86, 0, Math.PI * 2); ctx.fill(); ctx.restore();
    if (logo) { const s = 158 / Math.max(logo.width, logo.height); ctx.drawImage(logo, PAD + 86 - logo.width * s / 2, PAD + 86 - logo.height * s / 2, logo.width * s, logo.height * s); }
    else text('KDRK', PAD + 86, PAD + 100, 40, 800, color.bg, 'Manrope', -0.02, 'center');
    const bx = PAD + 172 + 22;
    text('KDRK', bx, 119, 44, 800, color.text, 'Manrope', -0.02);
    ['KAÇKAR DAĞCILIK', 'RAFTİNG KAYAK İHTİSAS', 'SPOR KULÜBÜ'].forEach((s, i) => text(s, bx, 150 + i * 22.5, 15, 500, color.text, mono, 0.16));

    // Tarih kutusu: gün, ay-yıl, gün adı (çok günlükte süre); köşede mevsim rozeti.
    const day = v => { const [yy, mm, dd] = String(v || '').split('-').map(Number); return new Date(yy, (mm || 1) - 1, dd || 1); };
    const start = day(d.startDate);
    const days = d.endDate && d.endDate > d.startDate ? Math.round((day(d.endDate) - start) / 864e5) + 1 : 1;
    const dx = right - 244, dcx = right - 122;
    ctx.fillStyle = color.accent; round(dx, PAD, 244, 280, 16); ctx.fill();
    text(String(start.getDate()), dcx, 195, 124, 800, color.onAccent, 'Manrope', -0.04, 'center');
    text(`${MONTHS[start.getMonth()]} ${start.getFullYear()}`, dcx, 242, 30, 600, color.onAccent, mono, 0.12, 'center');
    const dayLabel = days > 1 ? `${days} GÜN` : DAYS[start.getDay()];
    const dlw = measure(dayLabel, 24, 600, mono, 0.18);
    ctx.fillStyle = color.onAccent; ctx.fillRect(dcx - dlw / 2, 256, dlw, 3);
    text(dayLabel, dcx, 291, 24, 600, color.onAccent, mono, 0.18, 'center');
    seasonBadge(start.getMonth(), right + 18 - 32, PAD - 18 + 32);

    // Başlık bölümü: turuncu üst çizgi, kategori hapı, başlık (son kelime vurgulu), kısa açıklama.
    let y = PHOTO_H;
    ctx.fillStyle = color.accent; ctx.fillRect(0, y, W, 4); y += 4 + 22;
    const cat = fit(d.category.toLocaleUpperCase('tr-TR'), inner - 62, 18, 600, mono, 0.18);
    const cw = measure(cat, 18, 600, mono, 0.18) + 62;
    ctx.fillStyle = color.bg2; round(PAD, y, cw, 44, 22); ctx.fill();
    ctx.strokeStyle = color.border; ctx.lineWidth = 1; round(PAD + .5, y + .5, cw - 1, 43, 21.5); ctx.stroke();
    ctx.fillStyle = LEVEL_COLOR[d.difficultyClass] || LEVEL_COLOR.social; ctx.beginPath(); ctx.arc(PAD + 25, y + 22, 5, 0, Math.PI * 2); ctx.fill();
    text(cat, PAD + 42, y + 29, 18, 600, color.text, mono, 0.18);
    y += 44 + 10;
    titleLines.forEach((line, i) => {
      const base = y + size * 0.8 + i * lead, words = line.split(' ');
      // Son satırın son kelimesi turuncu; tek kelimelik başlık beyaz kalır.
      if (i === titleLines.length - 1 && words.length > 1 && !line.endsWith('…')) {
        const last = words.pop(), w = text(words.join(' ') + ' ', PAD, base, size, 800, color.text, 'Manrope', -0.035);
        text(last, PAD + w, base, size, 800, color.accent, 'Manrope', -0.035);
      } else text(line, PAD, base, size, 800, color.text, 'Manrope', -0.035);
    });
    y += titleLines.length * lead;
    if (d.summary) { y += 10; y += block(d.summary, PAD, y + 27, 860, 26, 1, 37.7, 400, color.muted) * 37.7; }
    y += 20;

    // Gövde: topografya filigranı + 2×2 bilgi kutusu.
    if (topo) { ctx.save(); ctx.globalAlpha = .05; const s = Math.max(W / topo.width, (H - y) / topo.height); ctx.drawImage(topo, (W - topo.width * s) / 2, y + (H - y - topo.height * s) / 2, topo.width * s, topo.height * s); ctx.restore(); }
    const cellH = 86, cellW = inner / 2;
    ctx.fillStyle = color.bg2; round(PAD, y, inner, cellH * 2 + 1, 16); ctx.fill();
    ctx.strokeStyle = color.border; ctx.lineWidth = 1; round(PAD + .5, y + .5, inner - 1, cellH * 2, 15.5); ctx.stroke();
    ctx.fillStyle = color.border; ctx.fillRect(PAD + cellW, y, 1, cellH * 2 + 1); ctx.fillRect(PAD, y + cellH, inner, 1);
    const [place, region] = splitPlace(d.location), [time, meetPlace] = splitMeeting(d.meeting);
    const missingDistance = !d.distance || d.distance === 'Belirtilmedi';
    const facts = [['pin', 'YER', place, region], ['clock', 'HAREKET', time, meetPlace], ['route', 'MESAFE', missingDistance ? 'Belirtilmedi' : d.distance, ''], ['boot', 'ZORLUK', d.difficultyName || 'Belirtilmedi', '']];
    facts.forEach(([kind, label, main, small], i) => {
      const cx = PAD + (i % 2) * (cellW + 1) + 24, cy = y + Math.floor(i / 2) * (cellH + 1), tx = cx + 46 + 16, tw = cellW - 48 - 62;
      ctx.fillStyle = color.surface; round(cx, cy + 20, 46, 46, 12); ctx.fill();
      icon(kind, cx + 9, cy + 29, 28, color.accent);
      text(label, tx, cy + 37, 16, 500, color.muted, mono, 0.18);
      if (kind === 'boot') {
        // Zorluk: kademe adı solda, 5 bot sağda; dolu oran puan / 2 (5/10 → 2,5 bot).
        const step = d.score != null ? d.score / 2 : d.difficultyStep;
        const scoreText = d.score != null ? `${d.score}/10` : '', sw = scoreText ? measure(scoreText, 18, 500, mono, 0.08) + 8 : 0;
        // Kademe adı sığmazsa ("Orta-Zor") botlar 34 px yerine 26 px çizilir.
        const nameW = measure(main, 28, 700, 'Manrope', -0.02);
        const bs = step != null && tw - nameW - 12 < 5 * 34 + 24 + sw ? 26 : 34, gap = bs === 34 ? 6 : 4;
        const bootsW = step != null ? 5 * bs + 4 * gap + sw : 0, endX = PAD + (i % 2) * (cellW + 1) + cellW - 24;
        text(fit(main, tw - bootsW - 12, 28, 700, 'Manrope', -0.02), tx, cy + 70, 28, 700, color.text, 'Manrope', -0.02);
        if (step != null) {
          const fill = LEVEL_COLOR[d.difficultyClass] || color.accent, bx0 = endX - bootsW, by = cy + 59 - bs / 2;
          for (let k = 0; k < 5; k++) {
            const x = bx0 + k * (bs + gap), part = Math.max(0, Math.min(1, step - k));
            icon('boot', x, by, bs, '#4A5566');
            if (part > 0) { ctx.save(); ctx.beginPath(); ctx.rect(x, by, bs * part, bs); ctx.clip(); icon('boot', x, by, bs, fill); ctx.restore(); }
          }
          if (scoreText) text(scoreText, endX, cy + 66, 18, 500, color.muted, mono, 0.08, 'right');
        }
        return;
      }
      const m = fit(main, tw, 28, 700, 'Manrope', -0.02), mw = text(m, tx, cy + 70, 28, 700, color.text, 'Manrope', -0.02);
      const room = tw - mw - 10;
      if (small && room > 60) text(fit(small, room, 20, 500, 'Inter'), tx + mw + 10, cy + 70, 20, 500, color.muted);
    });
    y += cellH * 2 + 1 + 14;

    // Durum hapı solda, rehber iletişimi sağda.
    const status = fit(d.status.toLocaleUpperCase('tr-TR'), 420, 19, 600, mono, 0.14), stw = measure(status, 19, 600, mono, 0.14) + 68;
    ctx.fillStyle = color.surface; round(PAD, y, stw, 46, 23); ctx.fill();
    ctx.strokeStyle = color.border; round(PAD + .5, y + .5, stw - 1, 45, 22.5); ctx.stroke();
    ctx.fillStyle = STATUS_COLOR[d.statusKey] || color.muted; ctx.beginPath(); ctx.arc(PAD + 28, y + 23, 6, 0, Math.PI * 2); ctx.fill();
    text(status, PAD + 46, y + 30, 19, 600, color.text, mono, 0.14);
    const gw = inner - stw - 24;
    text(guides.length > 1 ? 'FAALİYET REHBERLERİ / İLETİŞİM' : 'FAALİYET REHBERİ / İLETİŞİM', right, y + 14, 15, 500, color.muted, mono, 0.18, 'right');
    if (guides.length) guides.forEach((p, i) => text(fit(p.name + ' · ' + p.phone, gw, 22, 500, 'Inter'), right, y + 44 + i * 30, 22, 500, color.text, 'Inter', 0, 'right'));
    else text('Rehber bilgisi henüz eklenmedi', right, y + 44, 22, 500, color.text, 'Inter', 0, 'right');

    // Alt damga: kuruluş ve koordinat (faaliyet konumu varsa onunki).
    const fy = H - 30 - 25;
    ctx.fillStyle = color.border; ctx.fillRect(PAD, fy - 16, inner, 1);
    const ew = text('EST. 1998 · ', PAD, fy + 18, 17, 500, color.muted, mono, 0.16);
    text('RİZE', PAD + ew, fy + 18, 17, 600, color.text, mono, 0.16);
    const coord = d.lat != null && d.lng != null ? `${dms(d.lat, 'N', 'S')} · ${dms(d.lng, 'E', 'W')}` : "41°01'N · 40°31'E";
    text(coord, right, fy + 18, 17, 500, color.muted, mono, 0.16, 'right');
    if ('letterSpacing' in ctx) ctx.letterSpacing = '0px';

    try {
      const blob = await new Promise((resolve, reject) => canvas.toBlob(b => b ? resolve(b) : reject(Error('blob')), 'image/png'));
      if (token !== version || !dlg.open) return;
      file = new File([blob], 'kdrk-' + String(d.title).toLocaleLowerCase('tr-TR').replace(/[^\p{L}\p{N}]+/gu, '-').slice(0, 65) + '.png', { type: 'image/png' });
      objectUrl = URL.createObjectURL(blob); previewImage.src = objectUrl; previewImage.hidden = false; canvas.hidden = true; download.disabled = false;
      send.hidden = !(navigator.share && navigator.canShare && navigator.canShare({ files: [file] }));
      feedback.textContent = (photoFailed ? 'Kapak yüklenemedi; fotoğrafsız tasarım hazırlandı. Yeniden deneyebilirsin. ' : !d.image ? 'Kapak eklenmemiş; fotoğrafsız tasarım hazırlandı. ' : 'Görsel hazır. ') + (!d.published ? 'Dikkat: faaliyet henüz yayında değil; açıklamaya bağlantı eklenmedi.' : '');
      retry.hidden = !photoFailed;
    } catch (_) { feedback.textContent = 'Görsel oluşturulamadı. Kapağı yeniden yükleyip tekrar dene.'; retry.hidden = false; }
  }
  // Tıklama belge düzeyinde dinlenir: sonradan kopyalanan kartlardaki (takvim paneli) butonlar da çalışır.
  buttons.forEach(b => { b.hidden = false; });
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-social-share]');
    if (!b || b.hidden) return;
    e.preventDefault();
    try { current = JSON.parse(b.dataset.socialShare); } catch (_) { return; }
    focus = b; caption.value = captionFor(current); dlg.showModal(); dlg.scrollTop = 0;
    render(current, ++version).catch(() => { feedback.textContent = 'Gönderi hazırlanamadı. Tekrar deneyebilirsin.'; retry.hidden = false; });
  });
  $('.social-close').addEventListener('click', () => dlg.close());
  dlg.addEventListener('click', e => { if (e.target === dlg) { const r = dlg.getBoundingClientRect(); if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) dlg.close(); } });
  dlg.addEventListener('close', () => { ++version; clean(); if (focus) focus.focus(); });
  retry.addEventListener('click', () => render(current, ++version).catch(() => { feedback.textContent = 'Görsel hazırlanamadı.'; }));
  download.addEventListener('click', () => { if (!file || !objectUrl) return; const a = document.createElement('a'); a.href = objectUrl; a.download = file.name; document.body.append(a); a.click(); a.remove(); feedback.textContent = 'İndirme başlatıldı. Açıklamayı da kopyalayabilirsin.'; });
  $('[data-social-copy]').addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(caption.value); feedback.textContent = 'Açıklama kopyalandı. Instagram gönderisine yapıştırabilirsin.'; }
    catch (_) { caption.focus(); caption.select(); feedback.textContent = 'Otomatik kopyalama engellendi. Seçili açıklamayı elle kopyala.'; }
  });
  send.addEventListener('click', async () => {
    if (!file) return;
    try { await navigator.share({ files: [file], title: current.title }); feedback.textContent = 'Paylaşım menüsüne gönderildi.'; }
    catch (e) { feedback.textContent = e.name === 'AbortError' ? 'Paylaşım iptal edildi; görsel hazır.' : 'Paylaşım açılamadı. Görseli indirip Instagram’dan seçebilirsin.'; }
  });
})();
