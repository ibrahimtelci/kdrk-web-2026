# KDRK Web — Tasarım Kuralları

Bu dosya projenin bağlayıcı tasarım sistemidir. **Yeni sayfa eklerken veya mevcut sayfayı değiştirirken bu kurallara uyulur.**

## 1. Renk paleti (assets/system.css — token dışına çıkma)

Renkler **yalnızca CSS değişkenleriyle** kullanılır; hex değerini doğrudan yazma.

| Token | Koyu tema | Açık tema | Kullanım |
|---|---|---|---|
| `--bg-primary` | `#0B1220` | `#F4F6FA` | Ana zemin |
| `--bg-secondary` | `#111B2C` | `#FFFFFF` | İkincil zemin, kart |
| `--surface` | `#1A2638` | `#ECEFF5` | Yüzey, input |
| `--border` | `#1F2A3D` | `#E2E8F0` | Kenarlık, ayırıcı |
| `--text-primary` | `#F4F6FA` | `#0B1220` | Başlık, ana metin |
| `--text-secondary` | `#94A3B8` | `#475569` | Yardımcı metin |
| `--accent-primary` | `#F97316` | `#C2410C` | Vurgu: buton, link, eyebrow çizgisi |
| `--on-accent` | `#0B1220` | `#FFFFFF` | Vurgu zeminli öğenin metni (buton, aktif hap). Turuncu üstüne `#fff` yazma. |
| `--accent-secondary` | `#38BDF8` | `#0284C7` | İkincil vurgu (seyrek) |
| `--success` / `--warning` / `--danger` | `#10B981` / `#F59E0B` / `#EF4444` | `#059669` / `#D97706` / `#DC2626` | Durum |

- Zorluk **1–10 puandır** (panelde kaydırma çubuğu) ve **5 kademeye** ayrılır; kademe = ⌈puan / 2⌉. Ad ve renk her yerde aynıdır (kart, detay, galeri, takvim, panel, paylaşım görseli): 1–2 **Kolay** `#34D399` · 3–4 **Kolay-Orta** (`color-mix(in oklch, #34D399, #FB923C)`) · 5–6 **Orta** `#FB923C` · 7–8 **Orta-Zor** `#F87171` · 9–10 **Zor** `#DC2626`; eğitim `#38BDF8`. Tek kaynak `Activity::LEVELS` / `Activity::levelOf()`. Rozet: koyu çip + renkli nokta (`--lv`) + kademe adı + puan (`7/10`). İkonlu gösterge 5 dağ simgesidir; dolu dağ sayısı kademeye eşittir (10 ikon kullanılmaz).
- **Durum rozeti zorluktan ayrıdır** (`.activity-status`): `open` Başvuru açık · `few` Son X yer · `full` Dolu · `closed` Başvuru kapandı · `postponed` Ertelendi · `cancelled` İptal · `done` Tamamlandı. Durum her zaman metinle yazılır, yalnız renkle verilmez.
- **Yeni renk uydurma.** Gerekirse `oklch()` ile mevcut paletten türet.
- Sayfa başına en fazla 2 zemin rengi (`--bg-primary` + `--bg-secondary`); bölümler `.corp-section` / `.corp-section.alt` ile alternatiflenir.

## 2. Tipografi

- **Manrope** 700/800 — başlıklar, marka, sayısal vurgular (`letter-spacing: -0.02em`)
- **Inter** 400/500/600 — gövde metni, buton, form
- **JetBrains Mono** 500/600 — etiket, koordinat, tarih, sayaç, eyebrow damgası (`letter-spacing: 0.1em–0.22em`, `text-transform: uppercase`)
- Gövde metni min 15px, ideal 16px; `line-height: 1.6–1.75`; `text-wrap: pretty`.
- Başlıklar `clamp()` ile responsive: h1 `clamp(32px,5.5vw,76px)`, h2 `clamp(26px,3.6vw,44px)`.
- Her bölüm bir `.eyebrow` ile açılır (turuncu kısa çizgi + mono büyük harf).

## 3. Zorunlu sayfa iskeleti

Her yeni sayfa **aynı sırayla** şunları içerir:

```
<head>  system.css → nav-footer.css → [sayfa CSS'i]
<body>
  <header class="nav" data-nav>        ← TDF logosu + KDRK amblemi + wordmark + menü + tema + CTA + burger
  <aside class="drawer" data-drawer>   ← mobil menü
  [HERO]                                ← aşağıdaki 3 tipten biri
  [BÖLÜMLER]                            ← .reveal sınıfı + data-screen-label
  <footer class="footer">               ← 4 kolon + footer-fullname + TDF rozeti
  <script src="assets/site.js">
```

**Kontrol listesi (her sayfada olmalı):**
- `data-nav`, `data-drawer`, `data-theme-toggle`
- `.kdrk-mark` (nav + footer + drawer), `.nav-tdf` (nav)
- Menü (tek şablon, tüm sayfalarda aynı): Anasayfa · Kurumsal ▾ · Faaliyetler ▾ (Faaliyet Takvimi, 7 disiplin; geçmiş faaliyetler takvim sayfasındaki sekmeden) · Fotoğraf Galerisi · Duyuru & Haber · Bilgi Bankası ▾ · İletişim. Menü öğeleri tek satırda kalır; 1180px'te sığmazsa yeni üst öğe eklenmez. Nav CTA her sayfada "Takvimi Gör" → `Faaliyetler.html`.
- Aktif bölüm: üst öğeye `.is-current`, hedef linke `aria-current="page"`.
- Masaüstü menü ≥1180px; altında burger + drawer (`<details>` grupları). CTA <600px'te gizlenir.
- Footer tek şablon: e-posta `mailto:`, telefon `tel:`.
- `.footer-fullname` → "Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü"
- Her bölümde `data-screen-label="NN Bölüm Adı"`
- Animasyonlu her blokta `class="reveal"`
- `assets/site.js` en sonda

## 4. Hero tipleri (yeni tip uydurma)

| Tip | Sınıf | Kullanım | Nav |
|---|---|---|---|
| Slider | `.hero` + `data-hero-slider` | **Sadece Anasayfa** | normal |
| Görselli başlık | `.corp-hero` | Kurumsal, disiplin, İletişim | normal |
| Sade başlık | `.list-header` / `.kb-hero` / `.gd-hero` | Liste, galeri, makale | **`nav-on-light` şart** |

**Kural:** Sayfanın üstü açık renkliyse `<header class="nav nav-on-light">` yazılır — yoksa açık temada menü beyaz zeminde kaybolur.

Her hero'da: breadcrumb → eyebrow → h1 → lede → mono damga (`.corp-hero-stamp`).

Slider: aktif slaytların tamamı kullanıcının belirlediği sırayla döner; sayı sınırı ve bekleme sırası yoktur. İlk slayt dahil tüm sıra panelden değiştirilebilir. Yalnız ilk slaytta `h1` (diğerleri `h2.hero-title`), görünür duraklat butonu, azaltılmış harekette otomatik geçiş kapalı.

Slayt kaynakları (ayrı "slayt duyurusu" yazılmaz): **tanıtım slaytı** (panelde elle, tarihsiz), **faaliyet** ve **duyuru & haber** (kayıtta "Anasayfa slaytında göster"; metin, görsel ve butonlar kayıttan gelir). Sıra panelde kullanıcı tarafından belirlenir; aktif/pasif durumu slayt bazındadır. Faaliyet tarihi geçince, haber `slider_until` gelince slayttan kendiliğinden kalkar.

Tweaks paneli (React/Babel) canlı sayfalara eklenmez.

## 5. Bileşen kütüphanesi (yeniden yazma, tekrar kullan)

| İhtiyaç | Bileşen | Dosya |
|---|---|---|
| Faaliyet kartı | `.activity-card` (img/date/difficulty/body/footer) | home.css |
| Bento ızgara | `.bento` + `.bento-tile` | home.css |
| İstatistik | `.stat-band` + `data-odometer` | corporate.css |
| Zaman çizelgesi | `.corp-timeline` + `.tl-item` | corporate.css |
| Seviye kartı | `.level-card` + `.lv-tag` | discipline.css |
| Hızlı bilgi | `.disc-quickfacts` + `.qf-cell` | discipline.css |
| Kişi kartı | `.board-card` / `.contrib-card` | corporate.css |
| Galeri kartı | `.gallery-card` | gallery.css |
| Makale + TOC | `.kb-layout` + `.kb-toc` + `.kb-article` | article.css |
| Uyarı kutusu | `.kb-callout` (`.warn`) | article.css |
| Filtre çubuğu | `.filter-bar` + `.select-field` | list.css, gallery.css |
| Form | `.cf-field` / `.form-group` | contact.css, detail.css |
| Lightbox | `.lightbox` + `.lb-*` | gallery.css |
| CTA bandı | `.join-band` | corporate.css |

## 6. Ölçü ve boşluk

- Konteyner: `.container` (max 1280px, `--gutter: clamp(20px,4vw,48px)`)
- Bölüm dolgusu: `.corp-section` → `clamp(72px,10vh,120px)`
- Izgara boşluğu: kart 24px, bento/şerit 16px, form 14px
- Köşe yarıçapı: kart/panel **12px**, küçük öğe **8px**, input **8px**, pill/chip **999px**, büyük bant **16px**
- Kart kenarlığı daima `1px solid var(--border)`; hover'da `--accent-primary`
- Dokunma alanı min 44×44px; metin min 12px (mono etiketler dahil)
- Odak: global `:focus-visible` halkası; `outline: none` yalnızca yerine görünür halka konuyorsa
- **Sıralı öğeler her zaman flex/grid + `gap`** ile dizilir; margin veya boşluk karakteriyle değil.

## 6a. Mobil öncelik (zorunlu)

Ziyaretçilerin çoğu telefondan gelir. Her sayfa **önce 360–430 px genişlikte** tasarlanır ve test edilir, masaüstü sonra gelir.

- Yatay kaydırma olmaz. Her sayfa 360 px'te kontrol edilir; tablo ve geniş içerik kendi kutusunda kayar.
- Gövde metni mobilde de min 16px. Satır uzunluğu kısa, paragraflar kısa.
- Dokunma alanı min 44×44px. Yan yana iki buton mobilde alt alta, tam genişlikte durur.
- **Formlar:**
  - Tek kolon. Etiket her zaman alanın üstünde görünür; placeholder etiket yerine kullanılmaz.
  - Input font-size **≥16px** (iOS'ta odakta yakınlaşmayı engeller), yükseklik ≥48px.
  - Doğru klavye açılır: `type="tel"`, `type="email"`, `inputmode="numeric"` (TC kimlik), uygun `autocomplete` değerleri (`name`, `tel`, `email`).
  - Zorunlu alan `*` ile ve metinle belirtilir. Hata mesajı alanın hemen altında, sade Türkçe ile yazılır ("11 haneli olmalı"). Gönderimde ilk hatalı alana odaklanılır.
  - Gönderme butonu tam genişlikte, gönderim sırasında devre dışı ve "Gönderiliyor…" yazar.
  - Gönderim sonrası girilen veri kaybolmaz; hata olursa form dolu kalır.
- Uzun sayfalarda başvuru gibi ana eylem mobilde ekranın altında sabit bir çubukla erişilebilir olur (detay sayfasındaki `.sticky-apply` gibi).
- Görseller `srcset` ile telefona küçük sürümde gelir (§16).

## 7. Hareket

- Hover geçişi 160–220ms `ease`; görsel yakınlaşma 500–600ms
- Scroll'da görünme: `.reveal` → `.is-visible` (site.js otomatik)
- Sayaç: `data-odometer="500"`
- Görsel hover: `transform: scale(1.04)`
- Kart hover: `translateY(-3px)` + turuncu kenarlık + turuncu gölge
- `prefers-reduced-motion` system.css'te kapatılıyor — ezme.

## 8. Görseller

- Placeholder: Unsplash `?w=900&q=80&auto=format&fit=crop` (kart), `?w=2400` (hero)
- Kart oranı 16/10, hero 21/9, portre 1/1 veya 4/5, A4 foto 1.414/1
- Koyu hero üstünde daima gradyan katman (`.corp-hero-overlay`)
- Topografya deseni: `.topo-watermark` (opacity 0.03–0.06)
- Kullanıcının yükleyeceği yerlerde `<image-slot>` kullan

## 9. İçerik dili

- Türkçe, sade ve doğrudan. Abartı ve pazarlama dili yok.
- Rakamlar `assets/site.js` içindeki `KDRK_STATS` tek kaynağından gelir; sayfada `data-stat="zirve|yuruyus|aramaKurtarma|yil|kurulus"` ile yazılır (sayaçta `data-odometer` ile birlikte). Elle rakam yazma. Yıl, kuruluştan otomatik hesaplanır.
- Rakamlar: 500+ zirve, 1000+ doğa yürüyüşü, 25+ arama-kurtarma, 28 yıl, kuruluş **1998**. Bunların dışında rakam/iddia (üye sayısı, tescil) kulüp teyidi olmadan yazılmaz.
- Kulüp teyitli ifade: KDRK **Türkiye'nin en köklü dağcılık kulüplerinden biridir** (2026-10-21). "En köklü" tek başına (birinci olduğu iddiası) değil, "en köklü … biri" kalıbıyla yazılır.
- Faaliyet verisi (tarih, zorluk, süre, kontenjan) tek kaynaktan gelir; Anasayfa, liste, disiplin ve detay sayfası aynı değeri gösterir.
- Mono etiketler büyük harf: `EST. 1998`, `41°01'N · 40°31'E`, `12 TEM 2026`
- Emoji kullanılmaz.
- Ton: %40 davetkar doğa / %60 teknik dağcılık dengesi.

## 10. Bağlantı kuralı

- Menü ve footer bağlantıları **daima gerçek `.html` dosyasına** işaret eder. `#anchor` placeholder bırakma.
- `#anchor` kullanılacaksa hedef `id` **aynı sayfada** bulunmalı.
- Çapraz sayfa anchor'ı (`Sayfa.html#bolum`) yazılacaksa o `id` hedef dosyada gerçekten olmalı.
- Yeni sayfa eklendikten sonra denetim: her `href` için hem dosya varlığı hem `#anchor` hedefi kontrol edilir (yalnızca `.html` uzantısını kontrol etmek yetmez).

## 11. Yönetim paneli (/yonetim)

**V2 ortak bileşen sözleşmesi:** [UI_STANDARDS_V2.md](UI_STANDARDS_V2.md) bağlayıcıdır. Liste başlığı, filtre/arama konumu, simgeli sekmeler, aktif renk, kart/tablo/form ölçüleri ve kaydet çubuğu bu kaynaktan uygulanır. Ortak stiller `assets/admin/components.css`, simgeler `app/admin-icons.php` üzerinden kullanılır; sayfa bazında yeniden tasarlanmaz. Panelin masaüstü yoğunluğu bu belgedeki 14px / 36–38px ölçüleridir.

Panel aynı paleti kullanır ama kendi token setiyle çalışır (`public_html/assets/admin/admin.css` + `panel.css`): `--surface`, `--surface-2`, `--accent`. Site CSS'lerini panele, panel CSS'ini siteye bağlama. Kökteki `admin/` klasörü eski prototiptir, kullanılmaz.

- **Kod yeri:** controller'lar `kdrk/app/Controllers/Admin/`, görünümler `kdrk/views/admin/`. Her controller `BaseController`'dan türer ve erişebilecek rolleri `$roles` ile **açıkça** yazar.
- **Roller:**

  | Rol | Yetki |
  |---|---|
  | Admin | Her şey; TC kimlik ve sağlık notunu görür, başvuru siler, ayarları ve kullanıcıları yönetir |
  | Editör | Faaliyet, başvuru (TC ve sağlık notu hariç) ve mesajlar |
  | İzleme | Yalnızca okur; tek yazma işlemi kendi parolasını değiştirmek |
- **İlk admin hesabı:** `php kdrk/database/create-user.php <e-posta> "<Ad Soyad>" admin`. Parola bir kez ekrana yazılır; ilk girişte değiştirilir.
- **Panel de mobil önceliklidir (§6a):** tablolar telefonda karta dönüşür, uzun formlarda kaydet çubuğu altta sabit durur.
- **Silme kuralı:** başvurusu olan faaliyet silinmez, yayından kaldırılır. Ayrılan kullanıcı silinmez, pasif yapılır.
- **Yeni kayıt formları açılır penceredir:** liste sayfasındaki "Yeni …" düğmesi `data-modal-link` taşır; form `?pencere=1` ile kenar çubuksuz açılır (`layouts/admin-modal`), kayıt bitince controller `done($url)` çağırır. JS kapalıyken aynı form normal sayfadır.
- **Uzun formlar bölünür:** `[data-steps="wizard"]` (yeni kayıt, son adım önizleme) ya da `[data-steps="tabs"]` (düzenleme). Sayfa aşağı doğru uzamaz.
- **Faaliyet formu sadedir:** ad, kategori, tarih (+bitiş), kısa açıklama, zorluk puanı, mesafe, yer + haritadan konum, plan metni, buluşma, kapak, tahmini ücret, kontenjan. Ödeme açıklaması ve kategoriye göre "Gerekli malzemeler ve kontroller" faaliyete yazılmaz; Genel Ayarlar → Faaliyet notları'ndan otomatik gelir.
- **Albüm faaliyetten açılır:** "Albüm oluştur" yalnız tamamlanan faaliyette görünür; ad, tarih, yer, kategori ve etiketler faaliyetten dolar. Albüm etiketleri panel ve site aramasında kullanılır.

---

# Backend Kuralları (PHP + MySQL)

Statik prototip PHP'ye taşınırken ve sonrasında **bu bölüm bağlayıcıdır.** Tasarım kuralları (§1–11) aynen geçerlidir; yalnızca "dosya/bağlantı" kuralları §13'teki URL yapısına uyarlanır.

## 12. Teknoloji yığını

| Katman | Seçim | Not |
|---|---|---|
| Dil | **PHP 8.4** (sunucu), kod en az 8.2 uyumlu | Her dosya `declare(strict_types=1);` ile başlar. PSR-12 biçim. |
| Veritabanı | **MariaDB 10.11** (sunucu), InnoDB, `utf8mb4` + `utf8mb4_turkish_ci` | SQLite kullanılmaz. Erişim yalnızca **PDO** ile. |
| Mimari | **Saf PHP + küçük çekirdek** (framework yok) | Router, DB, View, Auth, Csrf, Mailer, Image sınıfları `app/Core/` altında. |
| Sayfa üretimi | **Sunucu tarafı PHP şablonları** | Sayfalar JS kapalıyken de tam içerikle gelir. API'den çekip boş sayfa doldurma yok. |
| Tarayıcı JS | Mevcut vanilla JS (`assets/site.js` vb.) | Framework, derleme adımı, jQuery yok. Java kullanılmaz. |
| Bağımlılık | Composer ile **yalnızca PHPMailer** | Yeni paket eklemek kulüp onayı ister. `vendor/` FTP ile yüklenir. |
| E-posta | PHPMailer + hostingin **SMTP** hesabı | `mail()` kullanılmaz. |
| Görsel işleme | PHP **GD** (WebP desteğiyle) | Imagick'e bağımlı olma. |

Kod tanımlayıcıları (sınıf, fonksiyon, tablo, sütun) **İngilizce**; arayüz metni ve yorumlar **Türkçe**.

> Kök dizindeki `.html` dosyaları ile `assets/` ve `admin/` klasörleri artık yalnızca **tasarım referansıdır**. Canlı site `kdrk/` ve `public_html/` klasörlerinden çalışır; tasarım değişikliği `public_html/assets/` ve `kdrk/views/` içinde yapılır. Nav, drawer ve footer `kdrk/views/partials/` altında, menü `kdrk/app/menu.php` içindedir. §3'teki "her sayfada" kuralı bu tek partial ile sağlanır.

## 13. Klasör yapısı ve URL

```
kdrk/                         ← web kökü DIŞINDA (public_html'in yanında)
  app/Core/                   Router, DB, View, Auth, Csrf, Mailer, Image, Settings
  app/Controllers/            Site\*, Admin\*
  app/Models/                 Repository sınıfları (Activity, Application, Gallery, News, Banner…)
  views/layouts/              site.php, admin.php
  views/partials/             nav.php, drawer.php, footer.php, activity-card.php …
  views/pages/  views/admin/
  config/config.php           GİT'E VE FTP ÜZERİNE YAZMAYA KAPALI (örnek: config.example.php)
  database/migrations/        001_init.sql, 002_….sql
  storage/logs/  storage/cache/
  vendor/
public_html/                  ← web kökü
  index.php                   tek giriş noktası (front controller)
  .htaccess                   temiz URL, HTTPS, eski .html → 301, önbellek başlıkları
  assets/                     css, js, logolar
  uploads/                    yüklenen görseller — .htaccess ile PHP çalıştırma KAPALI
```

- Hosting `public_html` dışına yazmaya izin vermezse `kdrk/` klasörü `public_html` içinde kalır ve `Require all denied` ile kapatılır.
- **Nav, drawer ve footer tek partial'dır** (`views/partials/`). Sayfalara kopyalanmaz. Aktif menü öğesi controller'dan gelen değişkenle işaretlenir.
- Bileşenler (§5) partial olarak yazılır. Örneğin `.activity-card` tek bir partial'dan üretilir; Anasayfa, liste ve disiplin sayfası aynı partial'ı çağırır.

**URL yapısı (temiz URL, Türkçe karakter yok, küçük harf, tire):**

| Sayfa | URL |
|---|---|
| Anasayfa | `/` |
| Kurumsal | `/kurumsal/hakkimizda`, `/kurumsal/yonetim-kurulu`, `/kurumsal/emegi-gecenler` |
| Faaliyet takvimi / geçmiş | `/faaliyetler`, `/faaliyetler?tab=gecmis` |
| Faaliyet detayı | `/faaliyetler/{slug}` (ör. `/faaliyetler/kackar-zirve-2027`) |
| Disiplinler | `/disiplin/gunubirlik`, `/disiplin/dagcilik`, `/disiplin/spor-tirmanis`, `/disiplin/dag-kayagi`, `/disiplin/egitim`, `/disiplin/sosyal-kulturel`, `/disiplin/arama-kurtarma` |
| Galeri | `/galeri`, `/galeri/{slug}` |
| Duyuru & Haber | `/duyurular`, `/duyurular?tur=duyuru\|rapor` (duyuru = duyuru + haber; eski `?tur=haber` → 301), `/duyurular/{slug}` |
| Bilgi Bankası | `/bilgi-bankasi/{slug}` |
| İletişim | `/iletisim` |
| Yönetim paneli | `/yonetim` |

- Linkler elle yazılmaz; `url('faaliyet', $slug)` gibi bir yardımcıyla üretilir.
- Eski `.html` adreslerinin her biri `.htaccess` içinde **301** ile yeni adrese yönlendirilir.
- §10'daki denetim kuralı yeni yapıda da geçerlidir: her link bir route'a, her `#anchor` gerçek bir `id`'ye çıkmalıdır.

## 14. Veritabanı kuralları

- **Karakter seti açıkça belirtilir.** Sunucunun varsayılanı `latin1`'dir; belirtilmezse Türkçe karakterler bozulur.
  - Veritabanı cPanel'de oluşturulduktan sonra şu komut çalıştırılır: `ALTER DATABASE … CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci`.
  - Her `CREATE TABLE` sonuna `DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci` yazılır.
  - PDO DSN'inde `charset=utf8mb4` bulunur.
- Veritabanı ve kullanıcı adları cPanel önekini taşır (`rizekdrk_…`). Uygulama kullanıcısına yalnızca bu veritabanında yetki verilir.
- MariaDB'de `JSON` tipi aslında `LONGTEXT`'tir. JSON sütunlarına `CHECK (JSON_VALID(sütun))` eklenir. JSON yalnızca esnek ek veri için kullanılır; sorgulanan alanlar ayrı sütun olur.
- Tablo adları İngilizce, `snake_case`, çoğul: `activities`, `applications`, `gallery_albums`, `gallery_photos`, `news`, `banners`, `messages`, `users`, `settings`, `audit_log`.
- Her tabloda `id BIGINT UNSIGNED AUTO_INCREMENT`, `created_at`, `updated_at`. Bağlantılar **FOREIGN KEY** ile kurulur.
- Herkese açık kayıtlarda benzersiz `slug` bulunur.
- Sık sorgulanan sütunlara indeks eklenir: `activities(status, starts_at)`, `activities(category)`, `slug`.
- **Şema yalnızca migration ile değişir.** Dosyalar `database/migrations/NNN_aciklama.sql` adıyla yazılır. Uygulanmış bir migration düzenlenmez, yenisi yazılır. Uygulananlar `schema_migrations` tablosunda tutulur.
- Değer kümeleri tasarım kurallarıyla birebir aynıdır:
  - `status`: `open`, `few`, `full`, `closed`, `postponed`, `cancelled`, `done`
  - `difficulty_score`: `1–10` (panelde kaydırma çubuğuyla girilen). `difficulty`: `1–5` kademe, puandan türetilir (`Activity::levelOf`, migration 006). Eğitim kategorisinde `is_training = 1`; "zorluk yok" seçilirse ikisi de `NULL` olur ve rozet "Eğitim" ya da "Herkese açık" yazar.
  - `category`: `gunubirlik`, `dagcilik`, `spor-tirmanis`, `dag-kayagi`, `egitim`, `sosyal`, `arama-kurtarma`
- **Durum otomatik hesaplanır, elle tutulmaz:**
  - Tarihi geçen faaliyet `done` olur (yalnızca `cancelled` ve `postponed` korunur).
  - `few` ve `full`, kontenjan ile onaylı başvuru sayısından hesaplanır.
  - Elle girilen durumlar yalnızca `closed`, `postponed` ve `cancelled`'dır.
- **Kulüp rakamları** (`KDRK_STATS`) `settings` tablosuna taşınır. PHP bunları sayfaya basar; §9'daki "tek kaynak" ilkesi korunur.
- Bakım modu `settings.maintenance` alanından **sunucuda** kontrol edilir ve **503** döner. `assets/maintenance.js` kaldırılır.

## 15. Güvenlik (zorunlu)

- **SQL:** Yalnızca prepared statement kullanılır. Kullanıcı verisi SQL dizgesine birleştirilmez.
- **Çıktı:** Her dinamik değer `e()` yardımcısıyla (`htmlspecialchars`, `ENT_QUOTES`, UTF-8) basılır. Ham HTML yalnızca panelden gelen ve izinli etiketlerle süzülmüş makale gövdesinde kullanılabilir.
- **CSRF:** Her `POST` formunda token zorunludur. Kontrolü `Csrf` sınıfı yapar.
- **Oturum:**
  - Parolalar `password_hash()` (bcrypt/argon2id) ile saklanır.
  - Girişte `session_regenerate_id(true)` çağrılır.
  - Çerez ayarları `Secure`, `HttpOnly`, `SameSite=Lax`.
  - 15 dakikada 5 hatalı girişten sonra geçici kilit uygulanır.
- **Yetki:** Roller `admin`, `editor`, `viewer`. Her panel route'u yetkisini açıkça belirtir; varsayılan erişim **reddedilir**.
- **Dosya yükleme:**
  - Tür `finfo` ile doğrulanır; yalnızca jpg, png ve webp kabul edilir. Üst sınır 15 MB.
  - Görsel GD ile **yeniden kodlanır** (EXIF ve gömülü içerik silinir) ve rastgele bir adla kaydedilir.
  - Özgün dosya adı diske yazılmaz.
- **KVKK:**
  - TC kimlik no ve sağlık notu `sodium_crypto_secretbox` ile **şifrelenmiş** saklanır. Anahtar `config.php` içinde durur.
  - Panelde bu alanlar yalnızca `admin` rolüne açılır.
  - Başvuru formunda aydınlatma metni linki ve açık rıza onay kutusu zorunludur.
  - Saklama süresi `settings.kvkk_retention_days` ayarından okunur (**varsayılan 365 gün; kulüp değiştirebilir**). Süresi dolan başvurularda hassas alanlar cPanel cron görevi ile silinir; ad ve katılım kaydı kalır.
- **Hata yönetimi:** Canlıda `display_errors=0`; hatalar `storage/logs/` altına yazılır. Kullanıcıya teknik ayrıntı gösterilmez.
- **HTTP:** HTTPS zorunludur (`.htaccess` ile yönlendirilir). Şu başlıklar gönderilir: `X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options: SAMEORIGIN`.
- **Sırlar:** SMTP parolası, DB bilgileri ve şifreleme anahtarı yalnızca `config/config.php` içinde durur. Bu dosya depoya girmez.

## 16. Performans

- **Görseller:** Yüklenen her görselden WebP türevleri üretilir: 400, 900, 1600 ve 2400 px genişlik (§8 oranları). Sayfada `srcset` + `sizes` + `loading="lazy"` kullanılır; ilk ekrandaki hero görseli lazy olmaz. Özgün dosya sitede kullanılmaz.
- **Logolar:** Logolar ve statik görseller de bu kurala uyar. Tek bir dosya 300 KB'ı geçemez.
- **Statik dosya önbelleği:** CSS ve JS `?v=<filemtime>` ile sürümlenir. `.htaccess` ile 1 yıl önbellek ve gzip/brotli sıkıştırma açılır.
- **PHP:** OPcache açık olmalıdır (hosting panelinden kontrol edilir).
- **Sorgular:** Bir sayfa en fazla birkaç sorgu yapar. Döngü içinde sorgu (N+1) yazılmaz; liste sayfaları tek sorgu + JOIN ile çalışır.
- **Sayfa önbelleği:** Gerekirse herkese açık sayfalar `storage/cache/` altında dosya önbelleğine alınabilir. Panelde kayıt değişince ilgili önbellek silinir.

## 17. E-posta

- Başvuru ve iletişim mesajı **önce veritabanına kaydedilir, sonra** e-posta gönderilir. Gönderim başarısız olursa kayıt kaybolmaz ve panelde "gönderilemedi" olarak işaretlenir.
- Gönderen adres, hostingdeki gerçek alan adı hesabıdır (ör. `bildirim@kdrk.org.tr`). Kullanıcının adresi yalnızca `Reply-To` alanına yazılır.

## 18. Yayın (FTP/SFTP)

**Sunucu (cPanel, LiteSpeed, MultiPHP):**

| Adres | PHP | Klasör | Not |
|---|---|---|---|
| `kdrk.org.tr` | 7.2 | `/home/rizekdrk/public_html` | Eski site + canlı şenlik kayıt formu. **Dokunulmaz.** |
| `yeni.kdrk.org.tr` | 8.4 | `/home/rizekdrk/kdrk-yeni/public_html` | Yeni site. Uygulama dosyaları `/home/rizekdrk/kdrk-yeni/kdrk/` altında (web erişimi dışında). |

- Yeni site hazır olunca `kdrk.org.tr` yeni klasöre taşınır. Bundan önce şenlik projesi PHP 8'de test edilir.
- Hosting sertifikası `asral.veridyen.com` adına düzenlenmiştir. FTP/SFTP istemcisinde sunucu adı olarak bu ad kullanılır, düz FTP kullanılmaz.

Sunucuya yayın **SFTP** ile yapılır (düz FTP yerine SFTP tercih edilir). Her yayında şu sıra izlenir:

1. Yerelde `composer install --no-dev --optimize-autoloader` çalıştırılır.
2. Yeni migration varsa önce **veritabanı yedeği** alınır, sonra migration uygulanır (SSH: `php kdrk/database/migrate.php` ya da phpMyAdmin).
3. Değişen dosyalar yüklenir.
4. **Asla üzerine yazılmayacaklar:** `kdrk/config/config.php`, `public_html/uploads/`, `kdrk/storage/`.
5. Yayın sonrası kısa kontrol: Anasayfa, bir faaliyet detayı, başvuru formu ve panel girişi açılıyor mu?

- **Yerel geliştirme:** XAMPP MySQL'i başlat → `tools\dev.bat` (http://localhost:8000). Veritabanı `kdrk_web`; kurulum: `php kdrk/database/migrate.php` ve örnek veri için `--seed`.
- **Yüklemeden önce iki test de temiz geçmeli:**
  - `php -d extension=sodium tools/test.php`: sayfalar, linkler, iskelet kontrol listesi, durum hesabı, başvuru ve iletişim formları
  - `node tools/mobile-check.mjs [--shots]`: 390 ve 360 px'te yatay taşma, dokunma alanı ve form yazı boyutu
- Yeni sayfa veya bileşen eklendiğinde bu testlere de eklenir.
- Git deposu kaynak olarak kalır. Yalnızca SFTP ile sunucuda yapılmış bir değişiklik geçerli sayılmaz; değişiklik önce depoya girer.

## 19. SEO (sayfa hazırlarken)

Kapsamlı SEO çalışması (sitemap, robots, Schema.org, panel SEO alanları, Doğa Rehberi) yayın ve testlerden sonra yapılacak; plan ve kulübün raporu: `docs/seo-gereksinimleri.md`. O zamana kadar her yeni sayfa ve modül aşağıdaki kurallarla hazırlanır; böylece sonraki çalışma düzeltme değil ekleme olur.

- **Başlık ve açıklama:** Her sayfa controller'da `$page['title']` ve `$page['description']` verir; ikisi de sayfaya özgüdür, başka sayfayla aynı olmaz. Başlık ~60, açıklama ~155 karakteri geçmez. Açıklama içeriği anlatan doğal bir cümledir; kelime listesi değildir. Kısa ad kulüp onayına kadar "KDRK"dır ("Kaçkar Dağcılık Kulübü" kullanılmaz).
- **Başlık hiyerarşisi:** Sayfada tek `h1`; altında `h2` → `h3` sırası atlanmaz. Anasayfada `h1` yalnız ilk slayttadır (§4).
- **Canonical ve tekrar:** `$page['current']` + `currentParams` her sayfada verilir (canonical buradan üretilir). Filtreli ve sayfalı liste adresleri (`?yil=`, `?sayfa=`) ayrı sayfa sayılmaz; canonical yalın adrestir. Aynı içerik için ikinci URL açılmaz; eş anlamlı adres gerekiyorsa `$r->legacy()` / 301 ile mevcut sayfaya yönlenir.
- **URL:** Kalıcı adresler `slugify()` ile, Türkçe karaktersiz, küçük harf ve tireli üretilir. Yayınlanmış bir adres başlık değişince kendiliğinden değişmez; panelde "değiştirirsen eski link çalışmaz" uyarısı bulunur.
- **İçerik sunucuda:** Başlık, metin, tarih, yer ve görsel HTML'de gelir; JS yalnızca kolaylık katar (§12). Liste ve filtreler JS kapalıyken de çalışan GET formu ya da linktir.
- **Görseller:** İçerik görseli `img_tag($src, $sizes, $fallback, $alt)` ile basılır ve anlamlı bir `alt` alır (fotoğraf açıklaması; yoksa sayfa veya albüm başlığı + sıra). Yalnızca süs olan görselde `alt=""`. Sayfanın paylaşım görseli `$page['image']` ile verilir (og:image).
- **İç bağlantı:** İlgili içerikler birbirine bağlanır (disiplin ↔ faaliyet ↔ galeri albümü ↔ duyuru ↔ Bilgi Bankası). Bağlantılar `url()` ile üretilir; metni "buraya tıkla" değil, gittiği yeri anlatan ifadedir.
- **Doğal dil:** Anahtar kelime doldurma, gizli metin, `meta keywords` yok. Kelimeler metinde anlamlı yerde ve bir kez geçer.
- **Gerçek bilgi:** Doğrulanmamış iddia, adres, fiyat, puan, rota veya güvenlik bilgisi yazılmaz (§9). Kulübün yapmadığı faaliyet (ör. rafting, kulüp teyidi olmadan) hedeflenmez.
- **Boş sayfa yok:** İçeriği olmayan kayıt sitede görünmez (404 ya da listede yok). Örnek: fotoğrafsız albüm, metinsiz duyuru sayfası. Sadece anahtar kelime için sayfa açılmaz.
- **Yapılandırılmış veriye hazırlık:** Tarih, yer, organizatör, görsel gibi bilgiler ayrı veritabanı sütunlarında tutulur; metnin içine gömülmez. JSON-LD sonradan bu alanlardan üretilecek.
- **İndeksleme:** Panel (`/yonetim`), teşekkür ve hata sayfaları indekslenmez (`$page['robots']` ya da başlık). Deneme sunucusunda `config 'noindex' => true`; asıl alan adına geçişte `false` yapılır.
- **Test:** Yeni sayfa `tools/test.php` sayfa listesine eklenir; meta açıklama, `aria-current`, link ve iskelet denetiminden geçer.
