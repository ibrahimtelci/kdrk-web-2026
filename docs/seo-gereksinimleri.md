# SEO gereksinimleri ve çalışma planı

**Durum (15 Ekim 2026):** Ertelendi. Sunucuya ilk yayın ve testler bittikten sonra ele alınacak.
Bu arada yeni sayfa ve modüller **CLAUDE.md §19**'daki kurallara göre hazırlanır, böylece SEO çalışması sıfırdan değil, hazır bir zemin üzerinden başlar.

Kulübün ilettiği raporun tam metni bu belgenin sonundadır (Ek).

---

## 1. Mevcut durum (15 Ekim 2026 itibarıyla)

| Konu | Durum |
|---|---|
| Başlık, meta açıklama, canonical; sayfalar sunucu tarafında tam HTML | Var |
| Temiz Türkçe karaktersiz URL, eski `.html` → 301, HTTPS, 404, link denetimi | Var, `tools/test.php` kontrol ediyor |
| WebP + `srcset` + lazy loading, mobil uyum | Var |
| Open Graph | Kısmen: başlık, açıklama, görsel var; `og:type`, `og:url`, X/Twitter etiketleri yok |
| `sitemap.xml`, `robots.txt` | Yok |
| Schema.org JSON-LD | Yok |
| Görsel ALT metinleri | Kısmen: galeri fotoğrafları açıklamayı kullanıyor; kart ve kapak görsellerinde boş |
| Panelde SEO alanları, anahtar kelime havuzu, SEO raporu | Yok |
| Deneme sunucusu arama motorlarına kapalı (`config 'noindex'`) | Var |

## 2. Rapordaki maddelerin değerlendirmesi

- **§4 URL listesi:** Çoğunun karşılığı zaten var. Raporun kendisi "aynı içerik için ikinci URL oluşturma" diyor; CLAUDE.md §13 de URL yapısını sabitliyor. Öneri: eş anlamlı adresler mevcut sayfaya **301** ile yönlenir.

  | Rapordaki adres | Mevcut karşılığı |
  |---|---|
  | `/dagcilik` | `/disiplin/dagcilik` |
  | `/etkinlikler` | `/faaliyetler` |
  | `/haberler` | `/duyurular` (liste sayfası henüz yok) |
  | `/kaya-tirmanisi` | `/disiplin/spor-tirmanis` |
  | `/dagcilik-egitimleri` | `/disiplin/egitim` |
  | `/doga-yuruyusleri` | `/disiplin/gunubirlik` |
  | `/kis-etkinlikleri` | `/disiplin/dag-kayagi` (+ Kış Dağcılık notu) |

  Gerçekten yeni içerik gerektirenler: `/kackar-daglari`, `/yaylalar`, `/flora-fauna`, `/trekking-rotalari`, `/kampcilik`. Bunlar ancak gerçek içerik yazıldığında yayına girer.
- **§8 Doğa Rehberi:** Altyapı ve şablon hazırlanabilir. Rota, bitki ve güvenlik bilgisi kulüpten gelir ya da kulüp onaylar; doğrulanmamış bilgi yazılmaz (CLAUDE.md §9).
- **Rafting:** Kulübün adında geçiyor ama sitede rafting faaliyeti yok. Faaliyet gerçekten yapılmıyorsa bu anahtar kelimeler hedeflenmez.
- **Yerel SEO:** Google İşletme Profili ve yerel Schema için doğrulanmış fiziksel adres gerekir.
- **Ana sayfa H1:** İlk slaytın başlığıdır ve panelden değiştirilir; kod değişikliği gerekmez.
- **Otomatik SEO önerileri:** Dış servis ya da yapay zekâ gerektirmez. Başlık, kategori, yer ve tarihten kurallı öneri üretilir. Yayınlanmış URL kendiliğinden değişmez (mevcut davranış).
- **AVIF:** Şimdilik yok. Sunucudaki GD desteği belirsiz, WebP yeterli.
- **SEO puanı:** Yalnızca iç kontrol listesi olarak gösterilir; Google sıralaması gibi sunulmaz.

## 3. Önerilen fazlar

1. **Teknik temel:** dinamik `sitemap.xml`, `robots.txt`, tam OG ve X etiketleri, JSON-LD (`SportsOrganization`, `Event`, `Article`, `BreadcrumbList`), anlamlı ALT metinleri, eş anlamlı adreslere 301. Testlere eklenir.
2. **Panel SEO alanları:** faaliyet, duyuru ve albüm için SEO başlığı, açıklama, paylaşım görseli ve ALT alanları (yeni migration); otomatik öneriler; anahtar kelime havuzu; eksik alan, yinelenen başlık ve bozuk link raporları.
3. **Doğa Rehberi:** Bilgi Bankası altyapısının genişletilmesi; sayfalar kulüp metinleri geldikçe yayına girer.
4. **Dış hesaplar (kulüp yapar):** Search Console doğrulaması (meta etiket ya da DNS kaydı; altyapı hazırlanır), Google İşletme Profili.

## 4. Kulübün vermesi gereken kararlar

1. §4'teki adresler mevcut sayfalara 301 ile mi yönlensin, yoksa ayrı sayfa mı olsun? (Öneri: 301)
2. Kulüp rafting faaliyeti yapıyor mu? Doğrulanmış bir adres var mı?
3. Başlıklarda kısa ad "Kaçkar Dağcılık Kulübü" kullanılabilir mi? Karar verilene kadar "KDRK" kullanılır.
4. Kanonik alan adı `www.kdrk.org.tr` mi, `kdrk.org.tr` mi? Diğeri 301 ile ona yönlenir.

---

## Ek: Kulübün ilettiği rapor (15 Ekim 2026)

### KDRK WEB SİTESİ – SEO, ARAMA MOTORU GÖRÜNÜRLÜĞÜ VE İÇERİK OPTİMİZASYONU TEKNİK RAPORU

**Proje:** Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü (KDRK)
**Web sitesi:** https://www.kdrk.org.tr
**Amaç:** Google ve diğer arama motorlarında organik görünürlüğü artırmak, kulübün faaliyetlerini daha geniş kitlelere ulaştırmak ve web sitesini bölgesel bir doğa sporları bilgi merkezine dönüştürmek.

#### 1. Genel talimat
Mevcut web sitesinin tasarımını, işlevlerini, veritabanı yapısını ve çalışan modüllerini koruyarak kapsamlı SEO geliştirmesi yap. Kulübümüzün yalnızca "dağcılık" kelimesiyle değil; doğa, yürüyüş, trekking, kamp, yayla, dağ, kar, kış, bahar, çiçek, böcek, doğa fotoğrafçılığı, kaya tırmanışı, rafting ve benzeri birçok arama sorgusuyla bulunabilir olmasını hedefliyoruz. Anahtar kelimeleri sayfalara rastgele doldurma. Google'ın güncel SEO prensiplerine uygun, doğal dil kullanan ve gerçekten faydalı içerikler oluştur. Mevcut SEO altyapısını önce analiz et. Çalışan özellikleri bozma, gereksiz tekrar oluşturma.

#### 2. Anahtar kelime havuzu
Konu gruplarına ayrılarak sisteme tanımlanmalı; içerik sınıflandırma, ilgili konu önerme ve SEO başlığı oluşturma amacıyla kullanılmalı; `meta keywords` SEO'nun temeli olarak kullanılmamalı. Havuz yeni içeriklerle genişletilebilir olmalı.

- **A. Dağcılık ve tırmanış:** dağcılık, dağcı, dağ kulübü, dağcılık kulübü, dağ sporları, dağ yürüyüşü, zirve, zirve tırmanışı, zirve yürüyüşü, kaya tırmanışı, spor tırmanış, geleneksel tırmanış, alpinizm, yüksek irtifa, yaz dağcılığı, kış dağcılığı, buz tırmanışı, dağcılık eğitimleri, temel dağcılık, ileri dağcılık, dağcılık malzemeleri, tırmanış teknikleri.
- **B. Doğa ve yürüyüş:** doğa, doğa gezisi, doğa yürüyüşü, trekking, hiking, doğa turu, yürüyüş grubu, yürüyüş kulübü, yürüyüş rotası, orman yürüyüşü, patika, yayla yürüyüşü, günübirlik gezi, hafta sonu doğa gezisi, açık hava etkinlikleri, outdoor, doğa keşfi, macera, keşif rotaları.
- **C. Kampçılık:** kamp, kampçılık, çadır kampı, doğa kampı, yayla kampı, dağ kampı, kış kampı, yaz kampı, kamp alanları, kamp malzemeleri, kamp eğitimi, doğada yaşam, kamp rotaları, doğa etkinlikleri.
- **D. Kış ve kar:** kış, kar, karlı dağlar, kar yürüyüşü, kış yürüyüşü, kış trekking, kar kampı, dağ kayağı, kayak, tur kayağı, kış sporları, buz, buzullar, çığ, çığ güvenliği, kar manzarası, kış fotoğrafçılığı.
- **E. İlkbahar, yaz ve sonbahar:** ilkbahar, bahar, bahar gezisi, bahar yürüyüşü, bahar çiçekleri, yaz gezisi, yaz yürüyüşü, sonbahar, sonbahar yürüyüşü, sonbahar renkleri, orman, yeşil yaylalar, doğanın uyanışı, mevsimlik doğa etkinlikleri.
- **F. Çiçekler, böcekler ve yaban hayatı:** çiçek, kır çiçekleri, dağ çiçekleri, yayla çiçekleri, endemik bitkiler, bitki örtüsü, böcekler, kelebekler, arılar, kuşlar, kuş gözlemciliği, yaban hayatı, doğa gözlemi, flora, fauna, ekosistem, biyolojik çeşitlilik, makro fotoğrafçılık.
- **G. Bölgesel aramalar:** Rize, Rize dağcılık, Rize trekking, Rize doğa yürüyüşü, Rize kamp, Kaçkar, Kaçkar Dağları, Kaçkar zirve, Kaçkar tırmanışı, Kaçkar yaylaları, Çamlıhemşin, Ayder, Kavrun, Yukarı Kavrun, Pokut, Sal Yaylası, Elevit, Verçenik, Altıparmak Dağları, Fırtına Vadisi, Karadeniz, Doğu Karadeniz, Karadeniz yaylaları, Karadeniz doğa gezileri.
- **H. Rafting ve macera:** rafting, Fırtına Deresi rafting, akarsu sporları, doğa sporları, macera sporları, outdoor sporları, doğa macerası.
- **I. Kulüp ve eğitim:** KDRK, Kaçkar Dağcılık Kulübü, Kaçkar Dağcılık Rafting Kayak İhtisas Spor Kulübü, Rize dağcılık kulübü, dağcılık federasyonu, TDF, dağcılık kursu, dağcılık eğitimi, kaya tırmanışı eğitimi, kulüp üyeliği, doğa sporları kulübü, dağcılık etkinlikleri.

#### 3. Ana sayfa SEO yapısı
- **SEO title:** Kaçkar Dağcılık Kulübü | Rize Trekking, Doğa ve Dağcılık
- **Meta description:** Kaçkar Dağcılık Kulübü (KDRK): Rize ve Kaçkarlar'da dağcılık, trekking, doğa yürüyüşü, kamp, kaya tırmanışı, eğitimler ve dört mevsim doğa etkinlikleri.
- **H1:** Kaçkar Dağcılık Kulübü – Doğayı Keşfet, Zirveye Ulaş

Ana sayfada kulübün tam resmî adı, faaliyet alanları, bulunduğu bölge ve güncel etkinliklere erişim bulunmalı. Entegre edilecek alanlar: yaklaşan etkinlikler, dağcılık eğitimleri, doğa yürüyüşleri ve trekking, Kaçkar Dağları ve yürüyüş rotaları, dört mevsim doğa etkinlikleri, son haberler ve doğa yazıları, fotoğraf galerileri. Mevcut bölümler varsa yeniden oluşturmak yerine geliştir.

#### 4. SEO uyumlu sayfa yapısı
`/dagcilik`, `/doga-yuruyusleri`, `/trekking-rotalari`, `/kampcilik`, `/kaya-tirmanisi`, `/kis-etkinlikleri`, `/dagcilik-egitimleri`, `/kackar-daglari`, `/yaylalar`, `/flora-fauna`, `/etkinlikler`, `/haberler`.
Mevcut sayfa ve URL'leri incele; aynı içerik için ikinci URL oluşturma; mevcut URL değişmek zorundaysa 301 uygula. Her sayfa benzersiz SEO title, meta description, H1 ve canonical içermeli. Sayfalar konuya uygun iç bağlantılarla bağlanmalı. Boş kategori sayfalarını yalnızca anahtar kelime hedeflemek için yayımlama.

#### 5. Otomatik SEO sistemi
Panelde haber, etkinlik, eğitim, rota veya galeri oluşturulduğunda otomatik önerilmeli: SEO başlığı, meta açıklaması, slug, ilişkili anahtar kelimeler, görsel ALT açıklaması, Open Graph başlığı ve açıklaması, sosyal medya paylaşım görseli, ilgili içerik ve iç bağlantı önerileri. Öneriler değiştirilebilir olmalı. Yayınlanmış URL'ler başlık düzenlendiğinde kendiliğinden değişmemeli.

#### 6. Yapılandırılmış veriler
Schema.org JSON-LD: `Organization` / `SportsOrganization`; gerçek fiziksel konum için yerel yapılandırma; etkinlikler için `Event` (gerçek ad, tarih, saat, konum, organizatör, görsel); gerçek rota bilgilerinde uygun tür; içerik sayfaları için `Article` / `BlogPosting`; `BreadcrumbList`. Kesinleşmemiş etkinlik bilgisi, uydurma puan, değerlendirme veya yanlış fiyat eklenmemeli.

#### 7. Teknik SEO
Dinamik `sitemap.xml`; doğru `robots.txt`; benzersiz canonical; başlık ve açıklamaların sunucu HTML'inde olması; Open Graph ve X/Twitter etiketleri; mobil uyum; hızlı yükleme ve Core Web Vitals; görsel boyutlandırma, WebP/AVIF, lazy loading; anlamlı ALT; Türkçe karakter uyumlu URL; doğru H1/H2/H3; 404 ve bozuk bağlantı kontrolü; HTTPS ve yönlendirme tutarlılığı; yinelenen sayfaların indekslenmesinin önlenmesi. Önemli içerik yalnızca JS çalıştıktan sonra ortaya çıkmamalı.

#### 8. Doğa rehberi ve içerik stratejisi
"Doğa Rehberi" bölümü. Örnekler: Kaçkar'da trekking rotaları; Rize'de hafta sonu yürüyüş rotaları; Kaçkar'da ilkbahar çiçekleri; Karadeniz yaylalarında sonbahar; kış dağcılığına başlangıç; kampçılığa yeni başlayanlar; Kaçkar'ın endemik bitkileri; doğa yürüyüşlerinde güvenlik; kaya tırmanışı temel bilgiler; Rize'de doğa fotoğrafçılığı. Gerçek bilgilere, doğrulanabilir kaynaklara ve kulüp deneyimine dayanmalı. Güvenlik, hava ve risk bilgileri uzman kontrolünden geçmeden kesin tavsiye olarak sunulmamalı.

#### 9. Yerel SEO ve kurumsal görünürlük
Kulübün Rize ve Kaçkar ile ilişkisi açıkça anlaşılmalı. Resmî ad, iletişim, varsa doğrulanmış adres ve sosyal medya hesapları tutarlı olmalı. Google İşletme Profili ve Search Console için doğrulama ve takip altyapısı hazırlanmalı; harici hesap erişimi gerekiyorsa raporda belirtilmeli.

#### 10. SEO yönetim paneli
Mevcut paneli bozmadan: merkezi anahtar kelime havuzu ve kategorileri; içerik bazında SEO başlığı ve açıklama düzenleme; eksik SEO alanları raporu; ALT'sız görseller listesi; yinelenen başlık ve açıklama kontrolü; bozuk bağlantı kontrolü; sitemap durumu; indekslenmemesi gereken sayfaların kontrolü; Search Console altyapısı. İçerik başına basit, yalnızca iç kalite amaçlı SEO kontrol puanı.

#### 11. Kabul kriterleri
Önemli sayfalarda benzersiz başlık ve açıklama; anahtar kelime havuzu entegre; otomatik öneriler çalışıyor; `sitemap.xml` erişilebilir ve geçerli; `robots.txt` önemli içeriği engellemiyor; yapılandırılmış veriler uygun; Google'ın gördüğü HTML'de içerik ve meta veriler var; mobil görünüm ve işlevler bozulmadı; URL'ler korunuyor veya doğru yönleniyor; paylaşım önizlemeleri düzgün; gereksiz anahtar kelime tekrarı ve gizli SEO metni yok.

#### 12. Çalışma sonunda raporlanacaklar
Tespit edilen eksikler; düzenlenen dosya ve modüller; eklenen sayfa ve kategoriler; otomatik SEO özellikleri; sitemap, robots ve Schema sonuçları; test edilen teknik gereksinimler; manuel yapılacak Search Console ve İşletme Profili işlemleri; tamamlanmamış veya risk taşıyan noktalar.

**Son talimat:** Geliştirme mevcut siteyi yeniden tasarlamadan ve çalışan özelliklerini bozmadan uygulanmalı. Birinci öncelik, kulübün gerçek faaliyetlerini ve Rize–Kaçkar coğrafyasını doğru temsil eden, sürdürülebilir ve kolay taranabilir teknik SEO altyapısıdır. Amaç, KDRK'nin zamanla bu konularda güvenilir bir dijital kaynak hâline gelmesidir.
