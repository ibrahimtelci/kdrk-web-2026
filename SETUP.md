# KDRK Web — Paralel Çalışma Kurulumu

Üç ortamda aynı proje üzerinde çalışmak için: **buradaki tasarım ortamı**, **VSCode + Claude Code**, **telefondaki Claude**.

Ortak kaynak: **bir GitHub deposu**. Üçü de aynı depoya bakar.

---

## 1. Projeyi bilgisayara indir

Sohbetteki indirme kartından `KDRK Web.zip` dosyasını indir, `kdrk-web-2026` klasörüne aç.

```
kdrk-web-2026/
├── CLAUDE.md              ← tasarım kuralları (Claude Code bunu otomatik okur)
├── SETUP.md               ← bu dosya
├── Anasayfa.html          ← 22 site sayfası
├── ...
├── assets/                ← CSS, JS, logolar, görseller
├── admin/                 ← yönetim paneli
└── uploads/               ← yüklenen görseller
```

---

## 2. Git deposu oluştur ve GitHub'a gönder

Terminalde proje klasöründe:

```bash
cd kdrk-web-2026
git init
git add .
git commit -m "KDRK web sitesi + yönetim paneli"
git branch -M main
```

GitHub'da yeni bir depo aç (örn. `kdrk-web`), sonra:

```bash
git remote add origin https://github.com/KULLANICI-ADIN/kdrk-web.git
git push -u origin main
```

**Depo gizli (private) olabilir** — Claude bağlantısı gizli depolarla da çalışır.

---

## 3. Üç ortamı bağla

| Ortam | Nasıl bağlanır |
|---|---|
| **Buradaki tasarım ortamı** | Sohbette "GitHub deposunu bağla" dediğinde açtığım formdan depoyu seç |
| **VSCode + Claude Code** | Klasörü aç, `claude` komutunu çalıştır — `CLAUDE.md` otomatik okunur |
| **Telefondaki Claude** | Aynı GitHub deposunu bağla |

---

## 4. Günlük akış

**Burada tasarım yaptıysam:**
```bash
# VSCode'da, çalışmaya başlamadan önce
git pull
```

**VSCode'da değişiklik yaptıysan:**
```bash
git add .
git commit -m "ne yaptığını yaz"
git push
```
Sonra burada sohbete **"senkronize et"** yaz — depodaki değişiklikleri alırım.

**Altın kural:** Her oturuma `git pull` ile başla, bitirince `git push` yap.

---

## 5. Yerelde çalıştırma

### Statik site (sadece HTML sayfaları)
Klasördeki `.html` dosyasına çift tıklamak yeterli. Ya da VSCode'da **Live Server** eklentisi:
> Sağ tık → *Open with Live Server*

### Yönetim paneli (PHP + veritabanı gerektiğinde)

Panel şu an tarayıcıda `localStorage` ile çalışan bir prototip. Gerçek PHP backend'e geçtiğinde:

```bash
# PHP yerleşik sunucusu — proje klasöründe
php -S localhost:8000
```
Tarayıcıda: `http://localhost:8000/admin/Panel.html`

**Veritabanı: MySQL / MariaDB** (karar verildi; SQLite kullanılmaz). Yerelde XAMPP/MAMP veya Docker ile MySQL çalıştır. Teknoloji, klasör yapısı ve yayın kuralları için bkz. **CLAUDE.md §12–18**.

---

## 6. Çakışma önleme kuralları

İki taraf aynı anda aynı dosyaya dokunursa git çakışma (conflict) verir. Önlemek için:

### Sorumluluk ayır
| Alan | Kim çalışsın |
|---|---|
| Tasarım, yeni sayfa, CSS | **Burada** (tasarım ortamı) |
| Backend, PHP, veritabanı, deploy | **VSCode + Claude Code** |
| Küçük metin düzeltmeleri | **Telefon** |

Aynı dosyada iki taraf birden çalışmasın — en sık çakışan dosyalar: `assets/system.css`, `assets/nav-footer.css`, `admin/admin.js`.

### Pratik kurallar
1. **Her oturum `git pull` ile başlar.** İstisna yok.
2. **Küçük ve sık commit at** — büyük commit'ler çakışmayı büyütür.
3. **Bir alanda çalışırken diğer ortamda aynı alana dokunma.** Burada bir sayfa tasarlarken VSCode'da o sayfayı açma.
4. **Push etmeden önce tekrar pull et:**
   ```bash
   git pull --rebase
   git push
   ```

### Çakışma olduysa
```bash
git pull
# CONFLICT uyarısı gelirse:
# VSCode çakışan satırları işaretler — "Accept Current / Incoming / Both" seç
git add .
git commit -m "çakışma çözüldü"
git push
```

Karmaşıklaşırsa Claude Code'a sor: *"Bu çakışmayı çöz, tasarım tarafını koru."*

### Yedek dal (branch) kullanımı
Büyük bir değişikliğe girişeceksen ayrı dalda çalış:
```bash
git checkout -b yeni-ozellik
# ...çalış, commit at...
git push -u origin yeni-ozellik
```
Bittiğinde GitHub'da Pull Request açıp `main`'e birleştir.

---

## 7. Tasarım kuralları

`CLAUDE.md` dosyası projenin bağlayıcı tasarım sistemidir: renk token'ları, tipografi, zorunlu sayfa iskeleti, bileşen kütüphanesi, bağlantı kuralları.

Claude Code bu dosyayı **otomatik okur** — yani VSCode'da yeni sayfa yaptırdığında da aynı kurallara uyar. Kural değiştirmek istersen `CLAUDE.md`'yi güncelle, her üç ortam da yeni kurala uyar.
