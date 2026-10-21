<?php
/*
 * Ana menü — tek şablon (CLAUDE.md §3). Masaüstü menü ve mobil drawer bu diziden üretilir.
 * group: aktif üst öğeyi belirler (sayfanın 'nav' değeri). Bir öğe:
 *   [etiket, route adı, route parametreleri] veya ['---', ara başlık]
 */
return [
    ['group' => 'home', 'label' => 'Anasayfa', 'route' => 'home'],
    ['group' => 'kurumsal', 'label' => 'Kurumsal', 'items' => [
        ['Hakkımızda', 'hakkimizda'],
        ['Yönetim Kurulu', 'yonetim-kurulu'],
        ['Emeği Geçenler', 'emegi-gecenler'],
    ]],
    ['group' => 'faaliyetler', 'label' => 'Faaliyetler', 'items' => [
        ['Faaliyet Takvimi', 'faaliyetler'],
        ['Geçmiş Faaliyetler', 'faaliyetler', ['tab' => 'gecmis']],
        ['---', 'Disiplinler'],
        ['Günübirlik Doğa Yürüyüşü', 'disiplin', ['slug' => 'gunubirlik']],
        ['Dağcılık', 'disiplin', ['slug' => 'dagcilik']],
        ['Spor Tırmanış', 'disiplin', ['slug' => 'spor-tirmanis']],
        ['Dağ Kayağı', 'disiplin', ['slug' => 'dag-kayagi']],
        ['Eğitim Programı', 'disiplin', ['slug' => 'egitim']],
        ['Sosyal-Kültürel', 'disiplin', ['slug' => 'sosyal-kulturel']],
        ['Arama Kurtarma', 'disiplin', ['slug' => 'arama-kurtarma']],
    ]],
    ['group' => 'galeri', 'label' => 'Fotoğraf Galerisi', 'route' => 'galeri'],
    ['group' => 'duyuru', 'label' => 'Duyuru & Haber', 'route' => 'duyurular'],
    ['group' => 'bilgi', 'label' => 'Bilgi Bankası', 'items' => [
        ['Kulüp Üyeliği', 'kulup-uyeligi'],
        ['Sporcu Lisansı', 'sporcu-lisansi'],
        ['---', 'Eğitim Notları'],
        ['Yaz Dağcılık', 'yaz-dagcilik'],
        ['Kış Dağcılık', 'kis-dagcilik'],
        ['Tek İp Boylu', 'tek-ip-boylu'],
        ['Çok İp Boylu', 'cok-ip-boylu'],
    ]],
    ['group' => 'iletisim', 'label' => 'İletişim', 'route' => 'iletisim'],
];
