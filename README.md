# KDRK Web — Çalışma Prensibi

> **Durum (Ekim 2026):** Site PHP + MariaDB'ye taşınıyor. Canlı kod `kdrk/` (uygulama, web kökü dışında) ve `public_html/` (web kökü) klasörlerindedir. Aşağıdaki bölümler statik prototipi anlatır; prototip dosyaları referans olarak duruyor. Kurallar: **CLAUDE.md §12–18**.
>
> **Yerelde hızlı başlangıç:**
> 1. XAMPP Control Panel'den MySQL'i başlat.
> 2. `kdrk/config/config.php` yoksa `config.example.php` dosyasından kopyala (veritabanı bilgileri, `app_key`).
> 3. `C:\xampp\php\php.exe -d extension=sodium kdrk\database\migrate.php` (örnek veri için sonuna `--seed`).
> 4. `tools\dev.bat` → http://localhost:8000
> 5. Testler: `php -d extension=sodium tools/test.php` ve `node tools/mobile-check.mjs`
> 6. Panel: http://localhost:8000/yonetim. İlk admin hesabını aç: `php -d extension=sodium kdrk/database/create-user.php <e-posta> "<Ad Soyad>" admin`
>
> **Sunucuda cron (KVKK):** cPanel → Cron İşleri → günde bir (ör. `0 3 * * *`):
> `/usr/local/bin/php /home/rizekdrk/kdrk-yeni/kdrk/cron/kvkk-purge.php > /dev/null 2>&1`
> PHP yolu MultiPHP'de farklıysa (`/opt/cpanel/ea-php84/root/usr/bin/php`) onu yaz. Önce elle `--dry-run` ile dene.
>
> Git Bash'te `mobile-check.mjs`'e panel yolu verirken başına `MSYS_NO_PATHCONV=1` ekle; yoksa `/yonetim/...` yolu Windows yoluna çevrilir.

Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü web sitesi ve yönetim paneli. Bu belge, projeyi yerelde kodlamaya devam edecek geliştirici için yazıldı.

> Tasarım kuralları (renk, tipografi, sayfa iskeleti, bileşenler) **CLAUDE.md** dosyasındadır ve bağlayıcıdır. Bu README sistemin *nasıl çalıştığını* anlatır.

---

## 1. Genel mimari

- **Saf statik site:** HTML + CSS + vanilla JS. Derleme adımı, framework, npm yok.
- Her sayfa kendi `.html` dosyasıdır. Nav ve footer her sayfada **kopya olarak** durur (ortak şablon motoru yok). Nav/footer değişikliği tüm sayfalara uygulanmalıdır.
- **Yönetim paneli** (`admin/Panel.html`) şu an **tarayıcı içi prototip**: veriler `localStorage`'da tutulur, sunucu yoktur. Site sayfaları panel verisini henüz **okumaz** — sayfalardaki faaliyet kartları HTML içine elle yazılmıştır.
- Çalıştırmak için herhangi bir statik sunucu yeterli:
  ```bash
  npx serve .        # veya
  python -m http.server 8000   # veya
  php -S localhost:8000
  ```
  VSCode'da *Live Server* de olur. (Dosyaya çift tıklayarak açmak da çalışır; ancak bazı tarayıcılar `file://` altında `localStorage`'ı sayfalar arasında paylaşmaz.)

---

## 2. Klasör yapısı

```
/
├── Anasayfa.html            Giriş sayfası (slider hero)
├── Hakkimizda.html          Kurumsal
├── Yonetim Kurulu.html      Kurumsal — 2025–2026 kurulu
├── Emegi Gecenler.html      Kurumsal — eski kurullar, katkı verenler
├── Faaliyetler.html         Faaliyet takvimi (kart + takvim görünümü)
├── Faaliyet Detay.html      Tek faaliyet + başvuru formu
├── Dagcilik.html, Gunubirlik.html, Spor Tirmanis.html,
│   Dag Kayagi.html, Egitim.html, Sosyal Kulturel.html,
│   Arama Kurtarma.html      7 disiplin sayfası
├── Galeri.html, Galeri Detay.html     Fotoğraf galerisi
├── Kulup Uyeligi.html, Sporcu Lisansi.html,
│   Yaz Dagcilik.html, Kis Dagcilik.html,
│   Tek Ip Boylu.html, Cok Ip Boylu.html  Bilgi Bankası makaleleri
├── Iletisim.html            İletişim + harita + form
├── Bakim.html               Bakım modu sayfası
├── 404.html
│
├── assets/
│   ├── system.css           Tasarım token'ları (renk, tipografi, temalar) — HER SAYFADA İLK
│   ├── nav-footer.css       Nav, drawer, footer — HER SAYFADA İKİNCİ
│   ├── home.css             Anasayfa + ortak kartlar (.activity-card, .bento, .pill)
│   ├── list.css             Liste başlığı, filtre çubuğu, takvim görünümü
│   ├── corporate.css        Kurumsal sayfalar, .stat-band, .corp-timeline, .yk-card
│   ├── discipline.css       Disiplin sayfaları, .level-card
│   ├── detail.css           Faaliyet detay + başvuru formu
│   ├── gallery.css, gallery-detail.css
│   ├── article.css          Bilgi Bankası makale + içindekiler
│   ├── contact.css          İletişim
│   ├── maintenance.css      Bakım sayfası
│   ├── site.js              Ortak davranışlar (tüm sayfalarda en sonda)
│   ├── maintenance.js       Bakım modu yönlendirmesi (head'de, en başta)
│   ├── board/               Yönetim kurulu portreleri (*-orj.jpg = orijinal foto)
│   └── kdrk-logo-*.png, tdf-logo.png, topo.svg, mountains.svg
│
├── admin/
│   ├── Panel.html           Panel kabuğu
│   ├── admin-data.js        Veri katmanı (seed + localStorage)
│   ├── admin.js             Panel arayüzü (tüm modüller)
│   └── admin.css            Panel stilleri (siteden bağımsız token seti)
│
├── uploads/                 Kullanıcının yüklediği ham görseller (sitede doğrudan kullanılmaz)
├── CLAUDE.md                Bağlayıcı tasarım kuralları
├── SETUP.md                 Git / paralel çalışma kurulumu
└── README.md                Bu dosya
```

Prototip/yardımcı dosyalar (canlıya gitmez): `Anasayfa-print.html` (eski baskı kopyası), `KDRK UX Denetimi.html` (denetim raporu), `tweaks-panel.jsx`, `image-slot.js`, `screenshots/`.

---

## 3. Sayfa iskeleti

Her sayfa aynı sırayı izler (ayrıntı: CLAUDE.md §3):

```html
<head>
  <script src="assets/maintenance.js"></script>   <!-- bakım kontrolü, en önce -->
  <link rel="stylesheet" href="assets/system.css">
  <link rel="stylesheet" href="assets/nav-footer.css">
  <link rel="stylesheet" href="assets/[sayfa].css">
</head>
<body>
  <header class="nav" data-nav>…</header>          <!-- açık zeminli hero'da: class="nav nav-on-light" -->
  <aside class="drawer" data-drawer>…</aside>       <!-- mobil menü -->
  <section class="corp-hero | list-header | hero">… <!-- 3 hero tipinden biri -->
  <section class="corp-section" data-screen-label="02 …">…</section>
  <footer class="footer">…</footer>
  <script src="assets/site.js"></script>
</body>
```

---

## 4. `assets/site.js` — ortak davranışlar

`DOMContentLoaded` sırasında çalışır:

| Fonksiyon | Ne yapar |
|---|---|
| `initTheme` | Koyu/açık tema. `[data-theme-toggle]` butonu, tercih `localStorage`'da. `<html data-theme="dark|light">` |
| `initNav` | Scroll'da nav arka planı, burger → drawer aç/kapa, odak tuzağı, ESC |
| `initDropdowns` | Masaüstü menüdeki ▾ alt menüler (≥1180px) |
| `initReveal` | `.reveal` öğeleri görünür olunca `.is-visible` ekler (IntersectionObserver) |
| `applyStats` | `data-stat` öğelerine `KDRK_STATS` değerlerini yazar (aşağıya bakın) |
| `initOdometer` | `data-odometer="500"` sayaç animasyonu |
| `initHeroSlider` | Anasayfa slider'ı: otomatik geçiş, duraklat butonu, oklar, azaltılmış harekette kapalı |

`prefers-reduced-motion` açıksa animasyonlar `system.css` içinde kapatılır.

### Kulüp rakamları — tek kaynak

```js
// assets/site.js en üstü
window.KDRK_STATS = { kurulus: 1998, zirve: 500, yuruyus: 1000, aramaKurtarma: 25 };
window.KDRK_STATS.yil = new Date().getFullYear() - window.KDRK_STATS.kurulus;
```

Sayfada elle rakam yazılmaz:

```html
<span data-stat="yil">28</span> yıl                        <!-- düz metin -->
<span data-stat="zirve" data-odometer="0">0</span>+        <!-- sayaçlı -->
```

İçerideki değer yalnızca JS kapalıyken görünen yedektir. Rakam değişince **sadece `KDRK_STATS`** güncellenir.

---

## 5. Faaliyetler sayfası

`Faaliyetler.html` içindeki satır içi script:

- Kartlar HTML'de `.activity-card` olarak durur; veri öznitelikleri:
  `data-cat` (gunubirlik, dagcilik, spor-tirmanis, dag-kayagi, egitim, sosyal, arama-kurtarma) · `data-date="2026-07-12"` · `data-difficulty="1–4"` · `data-fill="0.55"` (doluluk) · `data-status` (open, few, full, closed, postponed, cancelled, done)
- **Yaklaşan / Geçmiş** sekmesi: `data-status="done"` geçmiştir.
- **Kart / Takvim** görünümü; tercih `localStorage['kdrk-faaliyet-view']`.
- URL parametreleri: `?cat=egitim`, `?tab=gecmis`, `?view=takvim`.
- Zorluk ≠ durum: zorluk `.activity-difficulty` rozeti, durum `.activity-status` rozeti (her zaman metinle).

> Aynı faaliyet Anasayfa, disiplin sayfası ve Faaliyetler'de **ayrı ayrı elle** yazılı. Backend'e geçince tek veri kaynağından üretilmeli (bkz. §8).

---

## 6. Bakım modu

```
admin → Genel Ayarlar → Bakım Modu  ──►  localStorage['kdrk-site-settings'] = { maintenance, title, message, eta, since }
                                                         │
Her site sayfasının <head>'inde assets/maintenance.js ───┘
   maintenance === true  ve önizleme değilse  →  location.replace('Bakim.html')
   ?onizleme=1  →  sessionStorage['kdrk-preview']='1'  →  site gezilebilir, üstte "ÖNİZLEME" çubuğu
   ?onizleme=0  →  önizlemeden çık
```

`Bakim.html` aynı ayarlardan başlık, mesaj ve tahmini dönüş saatini okur. Bakım kapalıyken açılırsa üstte "önizleme" notu gösterir.

**Önemli:** Bu yalnızca **aynı tarayıcıda** çalışır. Gerçek ziyaretçileri engellemek için sunucu tarafı bayrak gerekir (ör. `.htaccess` / PHP'de `settings.maintenance` kontrolü → 503 + Bakim.html).

---

## 7. Yönetim paneli (`admin/`)

- `admin-data.js` → `window.KDRK`: `SEED` örnek veriyi ilk açılışta `localStorage['kdrk-admin-db-v3']`'e yazar; `load()`, `save()`, `reset()`, `uid()` sağlar.
- `admin.js` → tek dosyada tüm modüller, her biri `RENDER.<modül>` fonksiyonu:
  `dashboard` · `activities` (Faaliyetler + Başvurular, Katılım ve Taşımacılık listesi çıktıları) · `gallery` · `news` · `banner` · `messages` · `settings` (Genel Ayarlar / Bakım Modu) · `users`
- Giriş prototiptir: kullanıcı seçilir, `localStorage['kdrk-admin-session']`. **Gerçek kimlik doğrulama yok.**
- Panel CSS'i siteye, site CSS'i panele bağlanmaz (CLAUDE.md §11).

### localStorage anahtarları

| Anahtar | Sahibi | İçerik |
|---|---|---|
| `kdrk-admin-db-v3` | panel | Tüm panel verisi (faaliyetler, başvurular, haberler, banner, mesajlar, kullanıcılar) |
| `kdrk-admin-session` | panel | Oturumdaki kullanıcı id |
| `kdrk-admin-theme`, `kdrk-admin-actview` | panel | Panel tercihleri |
| `kdrk-site-settings` | panel → site | Bakım modu ayarları |
| `kdrk-faaliyet-view` | site | Faaliyetler kart/takvim tercihi |
| `kdrk-preview` (sessionStorage) | site | Bakım önizleme oturumu |

---

## 8. Backend'e geçiş — önerilen yol

> Kararlar kesinleşti: **PHP 8.2+ (framework yok) + MySQL/MariaDB, sunucu tarafı şablonlar, temiz URL, SFTP ile yayın.** Bağlayıcı kurallar: **CLAUDE.md §12–18**. Aşağıdaki adımlar bu kurallarla birlikte okunur.

1. **Veri modeli:** `admin-data.js` içindeki `SEED` nesnesi şemanın kendisidir (activities, applications, news, banners, messages, users). Tabloları buradan türet.
2. **API:** `KDRK.load/save` çağrılarını `fetch('/api/...')` ile değiştir; `admin.js` arayüzü büyük ölçüde aynı kalır.
3. **Site sayfaları:** elle yazılmış `.activity-card`, galeri kartı ve duyuruları API'den üret (sunucu tarafı şablon veya JS ile). Faaliyet verisi tek kaynaktan gelmeli (CLAUDE.md §9).
4. **Bakım modu:** `kdrk-site-settings` → veritabanı/dosya; kontrolü sunucuya taşı, `maintenance.js`'i kaldır ya da yedek olarak bırak.
5. **Başvuru ve iletişim formları:** şu an gönderim yok; POST uç noktası + e-posta bildirimi ekle. TC kimlik, sağlık notu gibi alanlar KVKK kapsamındadır — şifreli saklama ve aydınlatma metni gerekir.
6. **Kimlik doğrulama:** panel için gerçek oturum + rol yetkisi (`users` modülündeki rol/erişim yapısı hazır).

---

## 9. Kulüpten bekleyen bilgiler

- Instagram ve WhatsApp gerçek bağlantıları (şu an `instagram.com` / `wa.me/` örnek)
- Telefon numarası (sitede `+90 (464) 651 00 00` örnek; kayıtlarda 0464 214 71 10 ve 0544 358 99 36 geçiyor)
- Adres teyidi: Yeniköy Mah. Cumhuriyet Cad. Rize İş Merkezi No:184/410, Rize Merkez
- Taşımacılık biniş noktalarının gerçek listesi
- Denetim Kurulu isimleri (şu an örnek)
- Yönetim kurulu: Ali Yılmaz, Süreyya Bayraktar, Muhammet Koto, Sinan Çelik orijinal fotoğrafları
- Eğitim kartlarındaki ön koşul / süre / kontenjan bilgileri, Arama Kurtarma "ayda 1 tatbikat"
- Galeri ve faaliyet görselleri (şu an Unsplash yer tutucu)
