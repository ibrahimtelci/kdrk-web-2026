# KDRK V2 — ortak arayüz sözleşmesi

Bu belge yeni ve mevcut V2 yönetim ekranları için bağlayıcıdır. Amaç aynı işlevin her sayfada aynı yerde, aynı simgeyle ve aynı davranışla bulunmasıdır. Aktif uygulama `kdrk_v2/` + `public_htm_v2/` klasörleridir.

## Görsel yaklaşım

- Yönetim panelinde referansın yerleşim ve yüzey yaklaşımı izlenir; renkler KDRK kurumsal paletinde kalır: turuncu ana işlem `--accent`, kurumsal mavi ikincil vurgu ve aktif durum `--accent-2`. Açık/koyu tema değerleri `admin.css` tokenlarından gelir; yeni bir mor/indigo marka tonu eklenmez.
- Ana ekran kişisel ve eylem odaklıdır: kısa karşılama, dört özet sayacı, belirgin hızlı işlem ve yaklaşan işler/son başvurular panelleri. Bilgi yoğunluğu kart kümeleri ve kısa satırlarla düzenlenir; dekor yalnızca bilgiyi destekler.
- Masaüstünde sabit sol menü ve tek üst araç çubuğu; tablet/telefonda çekmece menüsü, içerik başlığı ve altta izinli en çok dört sık kullanılan hedef bulunur. Alttaki gezinme form kaydet çubuğunun üstünde kalır.
- Yüzeylerde 14–18px köşe, ince kenarlık ve çok hafif gölge kullan. Her kartı ayrı renk yapma; durum/kategori renkleri anlam taşıdığı yerde küçük vurgu olarak kalır.
- Üst çubukta görünüm, bildirim ve hesap erişimi tutarlı sıradadır. İşlevsiz global arama veya sahte bildirim sayısı eklenmez.

## Tek kaynaklar

| Sorumluluk | Kaynak |
|---|---|
| Tema renkleri | `assets/admin/admin.css` değişkenleri |
| Ortak ölçü, sekme, filtre, form, tablo, kart ve dialog stilleri | `public_htm_v2/assets/admin/components.css` |
| PHP ve JS simge çizimleri | `kdrk_v2/app/admin-icons.php` |
| İstemci bileşen yardımcıları | `assets/admin/ui.js` / `window.KdrkUI` |
| Liste başlığı ve oluşturma işlemi | `views/admin/_list-head.php` |
| Liste filtreleri ve arama | `views/admin/_list-toolbar.php` |
| Form alanı üretimi | `views/admin/_form.php` |
| Sekme davranışı | `assets/admin/panel.js`, `[data-steps]` |
| Albüm sekmeleri / fotoğraf davranışı | `assets/admin/album-workspace.js` |

`density.css` ve `controls.css` önceki kaynaklardır; layout bunları ayrı yüklemez. Güncel ortak kurallar `components.css` içinde tutulur. Yeni ortak kuralı eski dosyalara veya sayfa CSS'ine ekleme. `admin.css`, `panel.css`, `quick.css` mevcut temel/özellik stilleridir; ortak bileşenlerin son sözü `components.css` dosyasındadır.

## Yerleşim

1. Sayfa adı sabit üst çubuktadır; içerikte aynı başlığı tekrar etme.
2. Listede kısa açıklama solda; Yeni/Ekle ve varsa önizleme işlemleri üst sağda `_list-head` düzenindedir.
3. Bunun altında tek filtre çubuğu vardır. Tür/durum filtreleri solda, büyüteçli arama sağdadır. Kart/Tablo varsa aramanın sağında, tek kopya bulunur.
4. Veri listesi filtre çubuğunun hemen altında başlar. İkincil açıklamalar kısa tutulur; uzun yardımcı bilgiler `ui-help` içinde açılır.
5. Formda geri bağlantısı ve kayda bağlı ikincil işlemler üstte; bölüm sekmeleri alanlardan önce gelir.
6. Form işlemleri altta `.save-bar` içindedir: silme solda, değişiklik bilgisi ortada, Vazgeç ve ana Kaydet sağda. Çubuk kaydırırken erişilebilir kalır.
7. Normal form genişliği en fazla 1120px. Faaliyet düzenleyicisinde canlı önizleme için mevcut 1480px düzeni özel bir varyanttır.
8. Telefonda bileşenler sarılır; yatay sayfa taşması oluşmaz. Masaüstü kolonlarını telefona sıkıştırma. Tablet dahil 860px ve altında geniş tablolar etiketli satır kartlarına dönüşür. Uzun filtre sekmeleri yatay kaydırılabilir bir şerit olur; sayfanın tamamı yatay kaymaz.
9. 860px ve altında izinli hızlı hedefler alt gezinme çubuğundadır; formdaki yapışkan kaydet çubuğu bu gezinmenin üzerinde görünür kalır.

## Ölçüler ve renk anlamları

| Bileşen | Masaüstü | Mobil / dokunmatik |
|---|---:|---:|
| Gövde | 14px | 15px |
| Kontrol yazısı | 13–14px | alanlarda 16px |
| Ana buton | 36px | en az 44px |
| Küçük / simge butonu | 32–36px | en az 44px |
| Form alanı | 38px | en az 44px |
| Simge | 16px | 16–18px |
| Standart aralık | 12px | 10–12px |
| Panel/kart köşesi | 12px | 12px |

- Ana işlem: kurumsal turuncu `--accent`.
- Açık tema zemini yumuşak lavanta/nötr; metin koyu lacivert `--text`.
- Seçili sekme, yan menü, mobil gezinme, görünüm ve düzenleme vurgusu: kurumsal mavi `--ui-selected` / `--ui-on-selected` (`--accent-2` üzerinden).
- Başarı ve aktif durum: `--ok`; tehlikeli işlem: `--danger`; uyarı: `--warn`; pasif: `--text-2`.
- Kategori renkleri (Tanıtım/Faaliyet/Haber/Duyuru) içerik kategorisini anlatır; durum rozeti ayrıdır.
- Yeni hex renk veya sayfaya özel rastgele ölçü ekleme. Var olan tokenları kullan; gereken varyantı ortak dosyada tanımla.

## Sekmeler

- Form: `[data-steps="tabs"]` içinde `.steps-nav` ve `.step` kullan.
- Her `.step` benzersiz `id`, anlaşılır `data-title` ve kayıtlı `data-icon` taşır.
- Aktif sekme kurumsal mavi zemin + kontrast metin/simge taşır. İnce kenarlık veya yalnız metin rengi aktif durum için yeterli değildir.
- Sağ/Sol ile önceki/sonraki; Home/End ile ilk/son sekme seçilir. `aria-selected`, `aria-controls`, panel etiketi ve odak sırası korunur.
- Liste filtreleri sayfa bağlantılarıdır; gerçek tab gibi yanlış ARIA rolleri verilmez. `_list-toolbar` bunları simgeli ve aktif durumu açık bağlantılar olarak üretir.
- Albümde Fotoğraflar/Albüm Bilgileri aynı görünümü kullanır; fotoğraf sekmesinin kare ızgarası korunur.
- Mobil liste ekranları örnek galeri gibi taranabilir olmalıdır: üstte kısa açıklama ve ana işlem, yatay kaydırılabilen filtre/sekme şeridi, tam genişlik büyüteçli arama, ardından görünüm ve durum kontrolleri gelir. Yalnız içerik şeridi yatay kayar; sayfa yatay taşmaz.
- Mobil slayt galerisinde kart ızgarası iki sütundur; çok dar ekranlarda tek sütuna iner. Görsel, kategori ve aktif/pasif bilgisi ilk bakışta okunur; düzenle, sil ve sıralama işlemleri en az 44px dokunma alanına sahip olur. Albüm fotoğrafları kare ızgarasını korur.

```html
<div data-steps="tabs">
  <div class="steps-nav" aria-label="Form bölümleri"></div>
  <section class="step panel" id="record-basic" data-title="Temel bilgiler" data-icon="edit">…</section>
  <section class="step panel" id="record-publication" data-title="Yayın" data-icon="publish">…</section>
</div>
```

## Alanlar ve arama

- Alanı `.field`, etiketi gerçek `<label for>` ile üret. Placeholder etiketi yerine geçmez.
- Açılır tekli seçim sağda aşağı ok; `type="search"` solda büyüteç taşır. Boşluklar ortak stil tarafından ayrılır; metin simgenin altına girmez.
- Yeni arama alanı `type="search"` olmalıdır; ayrı SVG çizme. Çoklu seçim ve geniş liste kutusuna açılır ok ekleme.
- Hata mesajı alan altında, `aria-invalid` ve `aria-describedby` ile bağlıdır. Hatalı sekme gönderimde görünür olur.
- Telefon, e-posta, tarih ve sayı için uygun type/inputmode/autocomplete kullan.
- Zorunlu alanları metin/etiketle belirt; renge tek başına anlam yükleme.

## Butonlar, kartlar ve tablolar

- Aynı eylem aynı simgeyi kullanır: ekle `plus`, düzenle `edit`, incele `eye`, kaydet `check`, sil `trash`, geri `back`, arama `search`.
- Yalnız simgeli düğmenin `aria-label` ve görünür ipucu (`title`) bulunur. Simge dekoratiftir (`aria-hidden`).
- Kartların aralıkları ve köşeleri ortaktır; metin kırpma/taşma güvenli olmalıdır. Slayt/albüm kapakları 16:9; fotoğraf ızgarası kare kalır.
- Tablo başlığı ortak yüzey rengindedir; satır işlemleri sağ son sütundadır. Masaüstü `td` flex'e çevrilmez; mobilde mevcut `tbl-cards` dönüşümü kullanılır.
- Hover hafif yükselme/gölgedir; azaltılmış hareket tercihinde kapalıdır.
- Aktif/Pasif veya yayın durumu metinle yazılır; yalnız renk kullanılmaz. Slaytların görünürlüğü ve sırası kullanıcıya aittir; aktiflerin tümü döner.

## Popup ve kaydetme

- Yeni kayıtlar `[data-modal-link]` üzerinden açılır; kısa kullanıcı formlarında `data-modal-size="compact"` kullanılır.
- Standart boyutlar küçük 480px, orta 720px, geniş 980px. Mobil form popup'ı tam ekran olabilir.
- Popup başlığı üstte solda, kapatma sağda; Escape/iptal ve değişiklik koruması korunur.
- Başarılı modal kaydı `done()` ile ana listeye döner. Sunucu hatası modal kipini ve güvenli eski alanları korur.
- Kalıcı silmede açık onay gerekir; pasifleştirme içeriği silmez. Görünüm değişimi veri değiştirmez.
- CSRF, rol kontrolü, kayıt erişimi ve sunucu doğrulaması arayüz düzenlemesi için değiştirilmez.

## Sonraki değişikliklerin kabul ölçütleri

- Ortak parçayı tekrar kullan; yeni sekme/filtre/save-bar tasarımı uydurma.
- Sayfa CSS'i içerik geometrisini belirleyebilir; ortak sekme rengi, kontrol yüksekliği, form odağı veya buton düzenini ezemez.
- Gerçekten yeni ihtiyaç varsa önce burada ve `components.css` içinde anlamlı varyant tanımla.
- PHP ve JS sözdizimini doğrula; etkilenen sayfa, bir ortak liste, bir sekmeli form ve popup üzerinde görünümü/klavyeyi kontrol et. Değişiklik gerektiriyorsa küçük ekran ve koyu temayı da doğrula.
- Ziyaretçi sitesi kendi `system.css` tokenları ve mevcut CLAUDE.md bileşenleriyle çalışır; yönetim CSS'ini ziyaretçi sayfasına yükleme. Yazdırma çıktıları baskı düzenini korur.
