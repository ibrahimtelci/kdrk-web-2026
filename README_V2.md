# KDRK Yönetim V2

Mevcut `public_html` ve `kdrk` korunur. Yeni web kökü `public_htm_v2`, uygulama kodu web kökü dışında `kdrk_v2` klasöründedir. Bu iki klasör birlikte kullanılır.

## Kullanım

1. XAMPP MySQL açık olsun.
2. `tools_v2/dev-v2.bat` dosyasını çalıştırın.
3. http://localhost:8001/yonetim adresini açın. Mevcut hesabınız kopyalandığı için aynı hesapla giriş yapılabilir.

V2 ayrı `kdrk_web_v2` veritabanını kullanır; asıl `kdrk_web` veritabanına yazmaz. V2'ye eklenen kayıtlar eski sürüme otomatik aktarılmaz. Bu yerel deneme kurulumu XAMPP yönetici erişimiyle oluşturuldu; canlı yayın için hostingin uygulama hesabı ve yapılandırması kullanılmalıdır. Yapılandırma dosyasını ve şifreleme anahtarını sürüm kontrolüne eklemeyin, canlı yapılandırmanın üzerine yazmayın.

## Yapılanlar

- Faaliyet tanımlama ilk sekmeden kaydedilebilir. Varsayılan taslaktır. Yayınlama `Başvuru ve yayın` sekmesindedir.
- Kapak, ad, tarih, yer ve kısa açıklama aynı bölümde. Bitiş tarihi ve ileri alanlar isteğe bağlı açılır.
- `Benzerini oluştur` eski faaliyetin metin ve rota bilgilerini hazırlar; tarih, başvurular, sonuç, yayın ve slayt seçimi kopyalanmaz. Kapak da yeni kayıt için ayrıca seçilir.
- Ücret: henüz belli değil / ücretsiz / ücretli. Kontenjan için sınırlı seçimi. Eğitim ve sosyal etkinlikte zorluk yok önerisi.
- Büyük masaüstü ekranda yan tarafta canlı kart önizlemesi. Dar ekranlarda önizleme sekmesi.
- Kaydedilmemiş değişiklik göstergesi, çıkış uyarısı ve sekmelerde alan doğrulaması.
- Tarihi geçmek ile gerçekleşmek ayrıldı: `Tarihi geçti`; yönetici geçmiş faaliyetin gerçekleştiğini doğrulayınca `Tamamlandı`.
- Faaliyet raporundan, raporun asıl kaynağına yönlendiren duyuru taslağı oluşturulur.
- Faaliyet, albüm ve haber adresleri değişirse eski adres içerik kimliği üzerinden yeni adrese 301 yönlenir. İkinci adres değişikliğinde zincir oluşmaz.
- Duyurularda yazı / faaliyete yönlendirme / bağlantı paylaşma seçimi.
- Albümlerde bağlı faaliyetten alınan alanların tekrar girilmesi gerekmez; farklı bilgi isteğe bağlı açılır.
- Sitede görünen üç slayt gerçek yayın sırasıyla görsel olarak önizlenir. Otomatik sıralama varsayılandır; tüm kaynaklar ortak listede elle sıralanabilir. İlk kulüp tanıtımı başta kalır.
- Ana ekranda hızlı ekleme bağlantıları ve sonucu eksik geçmiş faaliyetler. Son başvurulardaki ad üzerinden örnek kayıt gizleme filtresi kaldırıldı.
- Başvurularda alternatif işlemler açılır alanda. Listede kontenjan doluluk göstergesi.
- Kısa menü adları, daha büyük form alanları, mobilde sarılan sekmeler, sabit kaydetme alanı. Ayarlarda bakım modu günlük bilgilerin altına taşındı.

## Veri ve geçiş

Yalnız V2 veritabanına `006_quick_panel` ve `007_activity_gear_extra` migration'ları uygulandı: `activities.completed_on` , faaliyete özel ek malzeme notu ve `content_redirects` tablosu. Mevcut kolonlar ve kayıtlar silinmedi. Geçmiş faaliyetler otomatik gerçekleşti sayılmaz; eski raporları görüntülenmeye devam eder.

Bu V2 davranışı, kök CLAUDE.md dosyasındaki tarih geçince otomatik tamamlandı ve eski adresin bozulması kurallarını kullanıcının iyileştirme isteği doğrultusunda değiştirir. Diğer tasarım, yetki, CSRF ve veri koruma kuralları korunur.

## Kontroller

`C:\xampp\php\php.exe -d extension=sodium tools_v2/test.php http://localhost:8001`

Test takımı geçici kayıtlar oluşturup temizler; yalnız yerel V2 ortamında çalıştırılmalıdır. 232 kontrol: sayfalar, linkler, başvuru/iletişim, roller, CSRF, görsel yükleme/kırpma, galeri, slayt, saklama süresi ve V2 akışları. PHP dosyaları ve JavaScript sözdizimi kontrol edildi. Hızlı form 360px ve 390px görünümünde incelendi. Faaliyet listesi, albüm formu, duyuru formu ve slayt sayfasında 360/390px genişliklerde yatay taşma ve 16px altı form yazısı saptanmadı. Tüm sayfalarda tam görsel/erişilebilirlik denetimi yapılmadı.

Kategori malzeme notu tek kaynaktan gelir; faaliyete özel ek notlar ayrıca girilebilir.

## Son kullanım düzeltmeleri — 17 Ekim 2026

- Kaydedilmemiş değişiklik göstergesi formun ilk değerleriyle karşılaştırma yapar. Değer geri alınınca gereksiz uyarı kalkar.
- Kapama uyarısına onay verildiğinde aynı değişiklik için ikinci çıkış uyarısı oluşması engellenir.
- Ücretli–ücretsiz–ücretli geçişinde girilen tutar form açık kaldığı sürece korunur. Sayfa yeniden açılınca kayıtlı değer kullanılır.
- Kapak kırpma, kapak seçiminden vazgeçme ve harita konumu değişiklikleri göstergenin ve önizlemenin güncellenmesini tetikler.
- Tarayıcıda faaliyet adını değiştirip geri alma, 1250,50 tutarının seçenek geçişinde korunması ve formun başlangıç durumuna geri dönmesi doğrulandı. Form gönderilmedi; kayıtlar değiştirilmedi.
- İki JavaScript dosyası sözdizimi kontrolünden geçti. Önceki 232 sunucu kontrolü bu yalnızca tarayıcı davranışı değişikliğinde tekrar çalıştırılmadı.

## Sosyal paylaşım ve rehberler — 20 Ekim 2026

- Faaliyet kartı, detay ve yönetim ekranındaki Paylaş: 1080 × 1350 PNG, açıklama düzenleme/kopyalama ve desteklenen cihazlarda dosya paylaşımı. Instagram'a otomatik yayın yapılmaz.
- Flama kimliği: büyük gerçek kulüp amblemi, lacivert/beyaz/turuncu, ölçeklenebilir izohips deseni. Desen PNG referanstan esinlenilerek hazırlanmıştır; özgün flama vektörü değildir.
- Kategori, tarih, yer, mesafe ve rehber telefonları faaliyetten gelir. Mesafe boşsa Belirtilmedi yazılır; değer uydurulmaz.
- Zorluk: 1–2 Kolay, 3–4 Kolay-Orta, 5–6 Orta, 7–8 Orta-Zor, 9–10 Zor. Kayıtlı 1–10 puan değişmez. Beş ayakkabı simgesiyle gösterilir.
- Yönetim → İletişim Bilgileri: ad-soyad ve telefon tanımlama/düzenleme. Faaliyet → Plan: iki rehber seçimi, listede Yeni kişi ekle ile hızlı pencere. Seçilen kişiler faaliyetin detayında ve gönderide görünür; genel kulüp telefonu gönderiye eklenmez.
- 008_activity_contacts migration'ı yeni guide_contacts tablosunu ve iki faaliyet bağlantısını ekler. Yalnız yerel kdrk_web_v2 üzerinde uygulandı; hosting geçişinde migration ayrıca uygulanmalıdır. Eski faaliyetlerin rehberleri otomatik doldurulmaz.
- Aynı kişi iki rehber alanına seçilemez. Formdan yayına alma sırasında iki rehber gerekir. Eski kayıtları bu formdan güncellerken rehberleri seçin. Bağlı kişinin adı/telefonu düzenlendiğinde faaliyetlerde de güncellenir.
- Yerel doğrulama: 18 sunucu kontrolü; tarayıcıda hızlı kişi oluşturma, otomatik seçim, faaliyet kaydetme ve gönderi oluşturma denendi. Gerçek telefonda Instagram'a aktarım ayrıca denenmelidir.

## Anasayfa görselleri ve kullanıcı dönüşümü - 20 Ekim 2026

- Anasayfa görsellerinde yayın sırası açık kart listesine taşındı. İlk üç görsel ile sırada bekleyenler ayrıldı. Kütüphaneye kaynak sekmeleri, arama ve düzenleme bağlantıları eklendi.
- Masaüstünde tarayıcı görünümü, kaynak geçişi, aramada sonuç bulunamaması ve aramayı temizleme kontrol edildi. 390px ölçümünde yatay taşma görülmedi. Veri değiştiren sıralama/ekleme işlemleri bu kontrolde gönderilmedi.
- Kullanıcı cep telefonu, görevli rolü ve rehberlerin kullanıcılara taşınması `tools_v2/user-directory-update` içinde hazırlandı. MySQL bağlantısı yürütme ortamında engellendiği için bu paket devreye alınmadı. Çalışan kullanıcı modülü değiştirilmedi. Paket README dosyası uygulama ve doğrulama ayrıntılarını içerir.

## Kullanıcılar devrede ve görseller tek liste - 20 Ekim 2026

- 009_user_directory migration'ı yalnızca kdrk_web_v2 üzerinde uygulandı. İletişim kişileri girişe kapalı kullanıcı hesaplarına aktarıldı; eski faaliyet bağlantıları korundu. Eksik kullanıcı veya faaliyet eşlemesi bulunmadı. E-posta/parola girilip etkinleştirilmeden aktarılmış kişi giriş yapamaz.
- Kullanıcılara cep telefonu, faaliyetlere kullanıcı üzerinden görevli ataması ve Görevli rolüne kayıt bazlı erişim eklendi. Kod dosyaları uygulandı ve yedeklendi.
- Anasayfa görselleri tek küçük kart listesine indirildi. Kart üzerinde Tanıtım/Faaliyet/Haber/Duyuru, gerçek yayın sırası ve yayın durumu görünür. Tekrarlanan alt kütüphane kaldırıldı. Üstte tür filtresi ve başlık araması var; mevcut içerik ekleme kapalı bir bölümde tutuluyor. Tarayıcıda faaliyet filtresi 2/6 sonucuyla doğrulandı.

## Slayt ekleme akışı ve onaylanan tasarım - 20 Ekim 2026

- Mor Tanıtım, mavi Faaliyet, turkuaz Haber ve amber Duyuru etiketleri; renkli kalem simgeli Düzenle butonları ve hover'da hafif yükselme/gölge/görsel yakınlaşması uygulandı. Azaltılmış hareket tercihinde animasyon kapalıdır. Kartların ikincil işlemleri üç nokta altında; üstte tür hapları, arama ve yayın durumu filtresi vardır.
- Görsel Ekle kısayolunun yanında Yeni Slayt Ekle menüsü: Görsel Ekle / Faaliyet Ekle / Haber & Duyuru Ekle. Görsel ekleme mevcut modal formu açar; diğerleri kategori, arama, görselli radyo seçimi ve onay düğmesi bulunan ayrı dialog açar. Seçilmeden ekleme kapalıdır. JS yokken seçim formları açılır bölümlerle kullanılabilir.
- Tanıtım slaytında iki buton için hazır seçim: Faaliyet Takvimi, Faaliyete Başvur, Faaliyet Ayrıntıları, Kulübe Üye Ol veya Gösterme. Başvur/ayrıntı için hedef faaliyet seçilir. Eski kaydın mevcut butonu değiştirilmedikçe korunur. Faaliyet ve haber slaytlarının butonları içerikten üretilir.
- Ayarlar > Genel > Anasayfa görselleri: Biten faaliyetleri otomatik kaldır seçeneği varsayılan açık. Bitiş tarihi varsa ona, yoksa başlangıç tarihine göre gün geçince listeden ve siteden düşer. Faaliyet ve slayt seçimi silinmez; ayar kapatılırsa geçmiş seçilmiş faaliyetler geri gelebilir. İptal/ertelenmiş veya yayında olmayan içerik yine gösterilmez.
- Slayta eklemede uygunluk sunucuda tekrar denetlenir. Haber listesi yalnız yayındaki kayıtları içerir; önceki 30 kayıt sınırı kaldırıldı.
- 24 yerel HTTP/veritabanı entegrasyon kontrolü geçti. PHP/JS sözdizimi kontrolleri geçti. Tarayıcıda faaliyet ve haber kategori filtreleri, seçilmeden/sonra Ekle düğmesi ve hazır butonun hedef alanını açması doğrulandı. 390px ölçümünde yatay taşma yoktu.

## Slayt türleri ve kart/liste düzeni — 20 Ekim 2026

- Yeni Slayt Ekle menüsü: Tanıtım Ekle, Faaliyet Ekle, Haber & Duyuru Ekle. Tanıtım formunda yalnızca Faaliyet Takvimi / Kulübe Üye Ol / Gösterme seçenekleri bulunur. Sunucu apply/details/keep seçimlerini reddeder; eski tanıtımlardaki faaliyet bağlantıları anasayfada gösterilmez. Haber ve duyuruda tek Devamını Oku butonu bulunur.
- Tek Haber & Duyuru filtresi; kategori renkleri, kalemli Düzenle ve yanındaki kırmızı çöp kutusu. Üç nokta menüsü kaldırıldı. Onay penceresi tanıtımın kalıcı silinmesini, faaliyet/haberin yalnızca slayttan kaldırılmasından ayırır.
- Kart / Resimli Liste seçimi tarayıcıda saklanır. Tutamaçla fare/dokunma sürüklemesi ve klavye için sıra okları vardır. İlk tanıtım sabittir; filtreli listede sürükleme kapalıdır. Sunucu tüm anahtarları ve sıra sürümünü doğrular; eski veya eksik sıra reddedilir.
- Doğrulama: PHP/JS sözdizimi ve veritabanı gerektirmeyen 10 CTA kontrolü geçti. Tarayıcıda liste, kart, birleşik filtre, tanıtım modal seçenekleri, kaldırma uyarısı, sürükleme sonrası kalıcı sıra ve okla geri alma doğrulandı. 390 px kart/liste görünümünde yatay taşma görülmedi. Gerçek içerik silinmedi.
- Genişletilmiş check-slider-workflow.php bu oturumda CLI üzerinden MySQL soket erişimi engellendiği için çalıştırılamadı; önceki 24 kontrolün üzerine yeni sıralama ve tür kısıtları eklendi.

## Kullanıcı popup ve masaüstü yoğunluğu — 20 Ekim 2026

- Kullanıcı listesinde Yeni Kullanıcı ve kullanıcı satırına tıklamak, 720px genişlikte popup açar. Güncelleme de `done()` akışını kullanır; başarılı kayıtta ana liste yenilenir. Normal sayfa erişimi ve JS kapalı bağlantılar korunur.
- Panel ve modal iskeletine ortak density.css eklendi. Fare kullanılan masaüstünde gövde 14px, butonlar 36px, küçük butonlar 32px, alanlar 38px; menü 232px ve üst çubuk 56px. Liste, tablo, kart ve form boşlukları azaltıldı. Ekran veya tarayıcı ölçeği değiştirilmez.
- Mobil/dokunmatik hedefler geniş kalır; mobil alan metinleri 16px korunur. 1366×768 ve 1920×1080 kullanıcı listesi/popup tarayıcıda incelendi; kullanıcı oluşturma ve düzenleme popup açılışı doğrulandı. Gerçek kullanıcı kaydı ve parola değiştirilmedi.

## Referans üst bar ve sınırsız manuel slayt yönetimi — 20 Ekim 2026

- Referans görseldeki yuvarlak, simgeli kategori hapları, arama ve Filtreleri Göster düğmesi uygulandı. Durum filtresi açılır; Aktif/Pasif seçilebilir. Kart ve Tablo düğmelerinde simge vardır.
- Üç slayt sınırı, bekleme sırası, otomatik sıraya dönüş ve ilk tanıtımı kilitleme kaldırıldı. Kullanıcı tüm slaytları sıralayabilir; aktiflerin tamamı aynı sırayla döner. Pasif slayt editör sırasındaki yerini korur. Yeni eklenenler listenin sonuna eklenir.
- Aktif/Pasif rozetleri, aktif/pasif sayaçları ve her kartta Pasif Yap/Aktif Yap işlemi eklendi. Pasif kartlar soluk görsel ve kesik çerçeveyle ayrılır. Faaliyet/haberin slaytta pasif olması içeriğin yayınını değiştirmez. Biten faaliyetleri otomatik kaldırma ayarı ve kaynak yayın uygunluğu korunur.
- 8 bağımsız model kontrolü geçti. Tarayıcıda anasayfadaki beş aktif slayt, haber pasifleştirilince dört / aktifleştirilince beş slayt, ilk slaytın taşınması ve tekrar eski yerine alınması doğrulandı. Pasif filtresi ve tablo görünümü kontrol edildi. Gerçek içerik silinmedi.


## Ortak yönetim arayüzü standardı - 20 Ekim 2026

- Panel ve popup iskeletleri ortak `admin/components.css` katmanını kullanır. Sekmeler, filtreler, açılır seçimler, arama alanları, kartlar, tablolar ve form işlem çubukları aynı ölçü ve renk kurallarına bağlandı. Giriş ekranı da ortak alan/düğme ölçülerini kullanır.
- Faaliyet, ayarlar ve albüm sekmelerinde anlamlı simgeler ve belirgin mavi aktif durum bulunur. Sekme gezinmesinde ok tuşları, Home ve End desteklenir.
- Faaliyet, galeri, duyuru ve mesaj listeleri ortak başlık/filtre parçalarını kullanır. Oluşturma üst sağda; filtreler solda, arama sağda; form kaydet/vazgeç işlemleri altta sağdadır. Kullanıcı popup akışı korunur.
- PHP ve JavaScript aynı simge kaynağını kullanır. Gelecek geliştirmeler için `UI_STANDARDS_V2.md`, `AGENTS.md` ve `CLAUDE.md` ortak bileşen sözleşmesini tanımlar.
- Doğrulama: 33 PHP dosyası ve üç JavaScript dosyasının sözdizimi kontrolü geçti. Tarayıcıda ayarlar/faaliyet/albüm sekmeleri, liste düzenleri, faaliyet araması ve yeni kullanıcı popup açılışı kontrol edildi. Sekme simgeleri ve masaüstü tablo işlem hücreleri doğrulandı; kontrol edilen sayfalarda konsol hatası görülmedi. Gerçek kayıt/parola değiştirilmedi.


## Mobil/tablet uyumluluğu ve yeni yönetim tasarım dili - 20 Ekim 2026

- `tools_v2/design-reference/image-10.png` panel için yerleşim yönünü belirler; renkler KDRK kurumsal paletinde tutulur: kurumsal mavi aktif gezinme ve turuncu ana işlem. Açık tema varsayılan oldu; koyu tema seçeneği çalışmaya devam eder.
- Yönetim ana ekranına kullanıcı adıyla karşılama alanı, dört istatistik kartı, simgeli hızlı işlemler ve paneller arasında tutarlı boşluk/kenarlık/gölge eklendi. Üst çubukta mesaja ve hesaba hızlı ulaşım var.
- Tablet/telefonda rol izinlerine uygun en çok dört bağlantılı alt gezinme gösterilir. Form kaydet çubuğu alttaki gezinmenin üstünde sabit durur. Slayt sıralama tutamacı telefonda 44×44px dokunma hedefidir.
- Mobil taramada 768px faaliyet tablosunda belirgin taşma bulundu; faaliyet tabloları artık 860px altı genişlikte etiketli kart satırlarına dönüşür.
- Tarayıcı denetimi: ana ekran, faaliyetler, faaliyet düzenleme/başvuru, galeri, albüm, duyurular, mesajlar, slaytlar, kullanıcılar, ayarlar, hesap sayfaları 320/390/768px'te açıldı; yatay belge taşması ve PHP istisnası yok. 320px kullanıcı popup'ı, sekme sarması, slayt sıralama tutamacı (44px), alt menü ve form kaydet çubuğu kontrol edildi. Koyu tema geçişi ve mobil çekmece Escape ile kapatma doğrulandı.
- 21 Ekim: örnek mobil galeri düzeni ortak mobil liste standardına eklendi. Slayt filtreleri yatay şerit, arama tam genişlik, görünüm/durum kontrolleri ayrı ve kartlar 390px'te iki sütun; 320px'te tek sütun. Kart işlemleri dokunmaya uygun boyutta kaldı.
