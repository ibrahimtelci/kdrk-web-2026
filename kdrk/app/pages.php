<?php
/*
 * İçeriği şablonda duran (henüz panelden yönetilmeyen) sayfalar.
 * route adı => [yol, görünüm, başlık, açıklama, ek CSS, açık üst (nav-on-light), menü grubu, eski .html adı]
 */
return [
    'hakkimizda' => ['/kurumsal/hakkimizda', 'pages/static/hakkimizda', 'Hakkımızda', '1998\'den beri Kaçkar\'da. KDRK\'nın hikayesi, misyonu ve değerleri.', ['corporate.css'], false, 'kurumsal', 'Hakkimizda.html'],
    'yonetim-kurulu' => ['/kurumsal/yonetim-kurulu', 'pages/static/yonetim-kurulu', 'Yönetim Kurulu', 'KDRK Yönetim Kurulu üyeleri ve görevleri.', ['corporate.css'], false, 'kurumsal', 'Yonetim Kurulu.html'],
    'emegi-gecenler' => ['/kurumsal/emegi-gecenler', 'pages/static/emegi-gecenler', 'Emeği Geçenler', 'KDRK\'ya emek veren gönüllüler, eğitmenler, liderler ve destekçiler.', ['corporate.css'], false, 'kurumsal', 'Emegi Gecenler.html'],
    'galeri' => ['/galeri', 'pages/static/galeri', 'Fotoğraf Galerisi', '1998’den bu yana kulübün anılarını faaliyet faaliyet keşfet. KDRK fotoğraf galerisi.', ['home.css', 'list.css', 'gallery.css'], true, 'galeri', 'Galeri.html'],
    'galeri-ornek' => ['/galeri/ornek-album', 'pages/static/galeri-ornek', 'Örnek Galeri Albümü', 'KDRK faaliyet fotoğrafları: sahadan kareler.', ['home.css', 'list.css', 'gallery.css', 'gallery-detail.css'], true, 'galeri', 'Galeri Detay.html'],
    'kulup-uyeligi' => ['/bilgi-bankasi/kulup-uyeligi', 'pages/static/kulup-uyeligi', 'Kulüp Üyeliği', 'KDRK üyesi olmak; faaliyetlere öncelikli katılım, eğitim indirimleri ve büyüyen bir topluluğun parçası olmak demektir.', ['article.css'], true, 'bilgi', 'Kulup Uyeligi.html'],
    'sporcu-lisansi' => ['/bilgi-bankasi/sporcu-lisansi', 'pages/static/sporcu-lisansi', 'Sporcu Lisansı', 'Resmi yarışmalara katılmak ve federasyon faaliyetlerinde yer almak için Türkiye Dağcılık Federasyonu (TDF) sporcu lisansı gerekir.', ['article.css'], true, 'bilgi', 'Sporcu Lisansi.html'],
    'yaz-dagcilik' => ['/bilgi-bankasi/yaz-dagcilik', 'pages/static/yaz-dagcilik', 'Yaz Dağcılık Notları', 'Yaz dağcılığının temelleri: kaya geçişleri, ip teknikleri, rota bulma ve yüksek irtifa kampçılığı.', ['article.css'], true, 'bilgi', 'Yaz Dagcilik.html'],
    'kis-dagcilik' => ['/bilgi-bankasi/kis-dagcilik', 'pages/static/kis-dagcilik', 'Kış Dağcılık Notları', 'Kar ve buzla tırmanışın esasları: krampon-balta tekniği, kar zemini değerlendirme ve çığ güvenliği.', ['article.css'], true, 'bilgi', 'Kis Dagcilik.html'],
    'tek-ip-boylu' => ['/bilgi-bankasi/tek-ip-boylu', 'pages/static/tek-ip-boylu', 'Tek İp Boylu Tırmanış', 'Single-pitch: tek ip uzunluğunda, yerden emniyetli kaya tırmanışının temel sistemi ve güvenlik kuralları.', ['article.css'], true, 'bilgi', 'Tek Ip Boylu.html'],
    'cok-ip-boylu' => ['/bilgi-bankasi/cok-ip-boylu', 'pages/static/cok-ip-boylu', 'Çok İp Boylu Tırmanış', 'Multi-pitch: birden fazla ip boyu üst üste tırmanılan, istasyon kurulumu ve ekip yönetimi gerektiren ileri sistem.', ['article.css'], true, 'bilgi', 'Cok Ip Boylu.html'],
    'iletisim' => ['/iletisim', 'pages/static/iletisim', 'İletişim', 'KDRK ile iletişime geç: adres, telefon, e-posta ve mesaj formu.', ['corporate.css', 'contact.css'], false, 'iletisim', 'Iletisim.html'],
];
